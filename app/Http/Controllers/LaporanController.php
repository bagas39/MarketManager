<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pembelian;

class LaporanController extends Controller
{
    public function index() { return view('laporan_keuangan'); }

    public function getLaporan(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $penjualanQuery = Transaksi::query()->select('no_transaksi', 'total_harga', 'tanggal', 'created_at');
        $pembelianQuery = Pembelian::query()->select('no_pembelian', 'nama_supplier', 'total_biaya', 'tanggal', 'created_at');

        if ($startDate) {
            $startRange = Carbon::parse($startDate)->startOfDay();
            $penjualanQuery->where('created_at', '>=', $startRange);
            $pembelianQuery->where('created_at', '>=', $startRange);
        }

        if ($endDate) {
            $endRange = Carbon::parse($endDate)->endOfDay();
            $penjualanQuery->where('created_at', '<=', $endRange);
            $pembelianQuery->where('created_at', '<=', $endRange);
        }

        $penjualan = $penjualanQuery->get();
        $pembelian = $pembelianQuery->get();

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


    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $penjualanQuery = Transaksi::query()->select('no_transaksi', 'total_harga', 'tanggal', 'created_at');
        $pembelianQuery = Pembelian::query()->select('no_pembelian', 'nama_supplier', 'total_biaya', 'tanggal', 'created_at');

        if ($startDate) {
            $startRange = Carbon::parse($startDate)->startOfDay();
            $penjualanQuery->where('created_at', '>=', $startRange);
            $pembelianQuery->where('created_at', '>=', $startRange);
        }

        if ($endDate) {
            $endRange = Carbon::parse($endDate)->endOfDay();
            $penjualanQuery->where('created_at', '<=', $endRange);
            $pembelianQuery->where('created_at', '<=', $endRange);
        }

        $penjualan = $penjualanQuery->get();
        $pembelian = $pembelianQuery->get();

        $totalMasuk = $penjualan->sum('total_harga');
        $totalKeluar = $pembelian->sum('total_biaya');
        $saldoAkhir = $totalMasuk - $totalKeluar;

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

        $pdf = Pdf::loadView('pdf.laporan', compact('detail', 'totalMasuk', 'totalKeluar', 'saldoAkhir', 'startDate', 'endDate'));
        
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Laporan_Keuangan_Swalayan_Segar.pdf');
    }
}