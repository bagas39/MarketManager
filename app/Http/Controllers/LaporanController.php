<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JsonDataService;

class LaporanController extends Controller
{
    protected $db;

    public function __construct(JsonDataService $db)
    {
        $this->db = $db;
    }

    public function index()
    {
        return view('laporan_keuangan');
    }

    public function getLaporan(Request $request)
    {
        $penjualan = collect($this->db->getPenjualan());
        $pembelian = collect($this->db->getPembelian());

        $totalMasuk = $penjualan->sum('total_harga');
        $totalKeluar = $pembelian->sum('total_beli');

        $detail = [];

        foreach ($penjualan as $p) {
            $detail[] = [
                'tanggal' => $p['tanggal_penjualan'],
                'keterangan' => 'Penjualan Transaksi #' . $p['id_penjualan'],
                'tipe' => 'Masuk',
                'jumlah' => $p['total_harga']
            ];
        }

        foreach ($pembelian as $p) {
            $detail[] = [
                'tanggal' => $p['tanggal_pembelian'],
                'keterangan' => 'Pembelian dari ' . $p['supplier'],
                'tipe' => 'Keluar',
                'jumlah' => $p['total_beli']
            ];
        }

        usort($detail, fn($a, $b) => strtotime($b['tanggal']) - strtotime($a['tanggal']));

        return response()->json([
            'ringkasan' => [
                'total_masuk' => $totalMasuk, 
                'total_keluar' => $totalKeluar,
                'saldo_akhir' => $totalMasuk - $totalKeluar
            ],
            'detail' => $detail
        ]);
    }
}