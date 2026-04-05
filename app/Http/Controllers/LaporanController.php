<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan_keuangan');
    }

    public function getLaporan(Request $request)
{
    $startDate = $request->query('start_date');
    $endDate = $request->query('end_date');


    return response()->json([
        'ringkasan' => [
            'total_masuk' => 1500000, 
            'total_keluar' => 500000,
            'saldo_akhir' => 1000000
        ],
        'detail' => [
            [
                'tanggal' => $startDate ?? date('Y-m-d'), 
                'keterangan' => 'Penjualan Toko', 
                'tipe' => 'Masuk', 
                'jumlah' => 1500000
            ],
            [
                'tanggal' => $endDate ?? date('Y-m-d'), 
                'keterangan' => 'Beli Stok Indomie', 
                'tipe' => 'Keluar', 
                'jumlah' => 500000
            ],
        ]
    ]);
}
}