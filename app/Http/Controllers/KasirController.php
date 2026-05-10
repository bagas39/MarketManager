<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KasirController extends Controller
{
    public function index() { return view('kasir'); }

    public function getBarang() {
        return response()->json(Barang::all()->map(function($item) {
            $item->id_barang = $item->id;
            return $item;
        }));
    }

    public function storeTransaksi(Request $request)
    {
        // 1. VALIDASI 
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id_barang' => 'required|integer|exists:barangs,id', // Harus ada di tabel barangs
            'items.*.jumlah' => 'required|integer|min:1' // Tidak minus
        ], [
            'items.*.id_barang.exists' => 'Salah satu barang yang discan tidak valid/tidak ditemukan di database.',
            'items.*.jumlah.min' => 'Jumlah barang minimal 1.'
        ]);

        DB::beginTransaction();
        try {
            $total = 0;
            $noTransaksi = 'TRX-' . rand(10000, 99999);

            $transaksi = Transaksi::create([
                'no_transaksi' => $noTransaksi,
                'user_id' => Auth::id() ?? 1,
                'total_harga' => 0,
                'tanggal' => now()->format('Y-m-d')
            ]);

            foreach($validated['items'] as $item) {
                // Lock for update mencegah error jika ada 2 kasir check-out barang sama bersamaan
                $barang = Barang::where('id', $item['id_barang'])->lockForUpdate()->first();
                
                if ($barang->stok < $item['jumlah']) {
                    throw new \Exception("Stok {$barang->nama_barang} tidak mencukupi! (Sisa: {$barang->stok})");
                }

                $barang->stok -= $item['jumlah']; 
                $barang->save();

                $subtotal = $barang->harga_jual * $item['jumlah'];
                $total += $subtotal;

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id' => $barang->id,
                    'kuantitas' => $item['jumlah'],
                    'subtotal' => $subtotal
                ]);
            }

            $transaksi->update(['total_harga' => $total]);
            DB::commit();

            return response()->json([
                'success' => true,
                'id_penjualan' => $transaksi->no_transaksi,
                'message' => 'Transaksi berhasil & stok gudang telah dikurangi!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}