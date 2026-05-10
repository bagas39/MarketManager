<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\DetailPembelian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembelianController extends Controller
{
    public function index() { return view('transaksi_pembelian'); }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validate([
                'supplier' => 'required|string',
                'items'    => 'required|array'
            ]);

            $total = 0;
            $noPembelian = 'PO-' . rand(10000, 99999);
            
            $pembelian = Pembelian::create([
                'no_pembelian' => $noPembelian,
                'nama_supplier' => $data['supplier'],
                'user_id' => Auth::id() ?? 1,
                'total_biaya' => 0,
                'tanggal' => now()->format('Y-m-d')
            ]);

            foreach ($data['items'] as $item) {
                $idBarang = isset($item['id_barang']) ? (int)$item['id_barang'] : null;
                $jumlah = (int)($item['jumlah'] ?? 1);
                $hargaBeliInput = (float)($item['hargaBeli'] ?? 0);

                $barang = $idBarang ? Barang::find($idBarang) : null;

                if ($barang) {
                    $barang->stok += $jumlah;
                    $barang->harga_beli = $hargaBeliInput;
                    $barang->save();
                } else {
                    $barang = Barang::create([
                        'kode_barang' => 'BRG-' . rand(1000, 9999),
                        'nama_barang' => $item['namaBarang'] ?? 'Barang Baru',
                        'harga_beli' => $hargaBeliInput,
                        'harga_jual' => $hargaBeliInput * 1.2,
                        'stok' => $jumlah
                    ]);
                }

                $subtotal = $hargaBeliInput * $jumlah;
                $total += $subtotal;

                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $barang->id,
                    'harga_beli' => $hargaBeliInput,
                    'kuantitas' => $jumlah,
                    'subtotal' => $subtotal
                ]);
            }

            $pembelian->update(['total_biaya' => $total]);
            DB::commit();

            return response()->json(['success' => true, 'id_pembelian' => $pembelian->no_pembelian, 'message' => 'Stok berhasil diperbarui!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function history(Request $request)
    {
        $search = $request->query('search_supplier');
        $query = Pembelian::orderBy('created_at', 'desc');
        
        if ($search) $query->where('nama_supplier', 'like', "%{$search}%");

        $history = $query->take(10)->get()->map(function($po) {
            return [
                'id_pembelian' => $po->no_pembelian,
                'supplier' => $po->nama_supplier,
                'total_beli' => $po->total_biaya,
                'tanggal_pembelian' => $po->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json(['purchases' => $history]);
    }
}