<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JsonDataService;

class PrediksiStokController extends Controller
{
    protected $db;

    public function __construct(JsonDataService $db)
    {
        $this->db = $db;
    }

    public function index() {
        return view('prediksi_stok');
    }

    public function getBarang() {
        return response()->json($this->db->getBarang());
    }

    public function hitung(Request $request) 
    {
        $barangId = $request->barangId;
        $periode = (int) ($request->periode ?? 30); 

        $masterBarang = collect($this->db->getBarang());
        $barang = $masterBarang->firstWhere('id_barang', $barangId);

        if (!$barang) {
            return response()->json(['error' => 'Barang tidak ditemukan'], 404);
        }

        $riwayatPenjualan = $this->db->getPenjualan();
        $tanggalMulai = now()->subDays($periode)->startOfDay();
        $totalTerjual = 0;

        foreach ($riwayatPenjualan as $transaksi) {
            $tglTransaksi = \Carbon\Carbon::parse($transaksi['tanggal_penjualan']);
            
            if ($tglTransaksi->gte($tanggalMulai)) {
                foreach ($transaksi['items'] as $item) {
                    if ($item['id_barang'] == $barangId) {
                        $totalTerjual += (int) $item['jumlah'];
                    }
                }
            }
        }

        $rataRataHarian = $totalTerjual / $periode;
        $stokSekarang = (int) $barang['stok'];
        
        $hariBertahan = ($rataRataHarian > 0) ? floor($stokSekarang / $rataRataHarian) : 999;

        return response()->json([
            'nama_barang'      => $barang['nama_barang'],
            'stok_saat_ini'    => $stokSekarang,
            'rata_rata_harian' => round($rataRataHarian, 2),
            'hari_bertahan'    => (int) $hariBertahan,
            'total_terjual'    => $totalTerjual,
            'periode_analisis' => $periode
        ]);
    }
}