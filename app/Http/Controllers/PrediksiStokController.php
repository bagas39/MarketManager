<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\DetailTransaksi;

class PrediksiStokController extends Controller
{
    public function index() { return view('prediksi_stok'); }

    public function getBarang() {
        return response()->json(Barang::all()->map(function($item) {
            $item->id_barang = $item->id;
            return $item;
        }));
    }

    public function hitung(Request $request) 
    {
        $barangId = $request->barangId;
        $periode = 30; 

        $barang = Barang::find($barangId);
        if (!$barang) return response()->json(['error' => 'Barang tidak ditemukan'], 404);

        $tanggalMulai = now()->subDays($periode)->startOfDay();

        // Database langsung menghitung total terjual dalam periode tersebut
        $totalTerjual = DetailTransaksi::where('barang_id', $barangId)
            ->whereHas('transaksi', function($query) use ($tanggalMulai) {
                $query->whereDate('tanggal', '>=', $tanggalMulai->toDateString());
            })->sum('kuantitas');

        if ($totalTerjual <= 0) {
            return response()->json([
                'can_predict'       => false,
                'message'           => 'Data riwayat penjualan tidak cukup untuk diprediksi',
                'nama_barang'       => $barang->nama_barang,
                'stok_saat_ini'     => $barang->stok,
                'rata_rata_harian'  => 0,
                'hari_bertahan'     => null,
                'total_terjual'     => 0,
                'periode_analisis'  => $periode
            ]);
        }

        $rataRataHarian = $totalTerjual / $periode;
        $stokSekarang = $barang->stok;
        $hariBertahan = floor($stokSekarang / $rataRataHarian);

        return response()->json([
            'can_predict'       => true,
            'nama_barang'      => $barang->nama_barang,
            'stok_saat_ini'    => $stokSekarang,
            'rata_rata_harian' => round($rataRataHarian, 2),
            'hari_bertahan'    => (int) $hariBertahan,
            'total_terjual'    => (int) $totalTerjual,
            'periode_analisis' => $periode
        ]);
    }
}
