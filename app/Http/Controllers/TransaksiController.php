<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{

    public function list(Request $request)
    {
        try {
            $searchId = $request->query('search_id');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $limit = $request->query('limit', 15); // Ambil parameter limit (default 15)

            $query = Transaksi::with('user');

            if ($searchId) {
                $query->where('no_transaksi', 'like', "%{$searchId}%");
            }

            if ($startDate) {
                $query->whereDate('tanggal', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('tanggal', '<=', $endDate);
            }

            // Native Pagination
            $paginator = $query->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc')->paginate($limit);

            $transactions = $paginator->getCollection()->map(function($trx) {
                return [
                    'id_penjualan' => $trx->no_transaksi,
                    'id' => $trx->id,
                    'nama_kasir' => $trx->user->name ?? 'Kasir',
                    'tanggal_penjualan' => $trx->tanggal ? Carbon::parse($trx->tanggal)->format('Y-m-d') : $trx->created_at->format('Y-m-d'),
                    'total_harga' => $trx->total_harga
                ];
            });

            return response()->json([
                'transactions' => $transactions,
                'totalAvailableTransactions' => $paginator->total()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function detail($id)
    {
        $trx = Transaksi::with(['user', 'detailTransaksis.barang'])->where('no_transaksi', $id)->first();
        if (!$trx) return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);

        $items = $trx->detailTransaksis->map(function($detail) {
            return [
                'id_barang' => $detail->barang_id,
                'nama_barang' => $detail->barang->nama_barang,
                'harga_jual' => $detail->subtotal / $detail->kuantitas,
                'jumlah' => $detail->kuantitas,
                'subtotal' => $detail->subtotal
            ];
        });

        return response()->json([
            'header' => [
                'id_penjualan' => $trx->no_transaksi,
                'nama_kasir' => $trx->user->name ?? 'Kasir',
                'tanggal_penjualan' => $trx->tanggal ? Carbon::parse($trx->tanggal)->format('Y-m-d') : $trx->created_at->format('Y-m-d')
            ],
            'items' => $items,
            'summary' => [
                'total' => $trx->total_harga
            ]
        ]);
    }

    public function edit(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $trx = Transaksi::with('detailTransaksis')->where('no_transaksi', $id)->firstOrFail();
            $items = $request->input('items', []);
            if (empty($items)) throw new \Exception('Transaksi minimal harus memiliki 1 item.');

            // Kembalikan stok lama
            foreach($trx->detailTransaksis as $detail) {
                $barang = Barang::find($detail->barang_id);
                if ($barang) {
                    $barang->stok += $detail->kuantitas;
                    $barang->save();
                }
            }
            $trx->detailTransaksis()->delete();

            $subtotalBaru = 0;
            foreach ($items as $item) {
                $idBarang = $item['id_barang'] ?? null;
                $jumlah = (int)($item['jumlah'] ?? 1);

                $barang = Barang::findOrFail($idBarang);
                if ($barang->stok < $jumlah) throw new \Exception("Stok {$barang->nama_barang} tidak mencukupi!");

                $barang->stok -= $jumlah;
                $barang->save();

                $subtotal = $barang->harga_jual * $jumlah;
                $subtotalBaru += $subtotal;

                DetailTransaksi::create([
                    'transaksi_id' => $trx->id,
                    'barang_id' => $barang->id,
                    'kuantitas' => $jumlah,
                    'subtotal' => $subtotal
                ]);
            }

            $trx->update(['total_harga' => round($subtotalBaru * 1.11, 2)]);
            DB::commit();

            return response()->json(['success' => true, 'message' => "Transaksi berhasil diperbarui!"]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}