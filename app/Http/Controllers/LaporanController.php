<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pembelian;

class LaporanController extends Controller
{
    public function index() { return view('laporan_keuangan'); }

    public function getLaporan(Request $request)
    {
        $penjualan = Transaksi::select('no_transaksi', 'total_harga', 'created_at')->get();
        $pembelian = Pembelian::select('no_pembelian', 'nama_supplier', 'total_biaya', 'created_at')->get();

        $totalMasuk = $penjualan->sum('total_harga');
        $totalKeluar = $pembelian->sum('total_biaya');

        $detail = [];

        foreach ($penjualan as $p) {
            $detail[] = [
                'tanggal' => $p->created_at->format('Y-m-d H:i:s'),
                'keterangan' => 'Penjualan Transaksi #' . $p->no_transaksi,
                'tipe' => 'Masuk',
                'jumlah' => $p->total_harga
            ];
        }

        foreach ($pembelian as $p) {
            $detail[] = [
                'tanggal' => $p->created_at->format('Y-m-d H:i:s'),
                'keterangan' => 'Pembelian dari ' . $p->nama_supplier,
                'tipe' => 'Keluar',
                'jumlah' => $p->total_biaya
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