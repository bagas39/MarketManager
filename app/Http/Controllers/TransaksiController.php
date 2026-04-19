<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JsonDataService;

class TransaksiController extends Controller
{
    protected $db;

    public function __construct(JsonDataService $db)
    {
        $this->db = $db;
    }

    public function list(Request $request)
    {
        try {
            $searchId = $request->query('search_id');

            $mockSales = $this->db->getPenjualan();

            if ($searchId) {
                $mockSales = array_filter($mockSales, fn($s) => stripos((string)($s['id_penjualan'] ?? ''), $searchId) !== false);
            }

            return response()->json([
                'transactions' => array_values($mockSales),
                'totalAvailableTransactions' => count($mockSales)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function detail($id)
    {
        $history = $this->db->getPenjualan();
        $trx = collect($history)->firstWhere('id_penjualan', $id);

        if (!$trx) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        return response()->json([
            'header' => [
                'id_penjualan' => $trx['id_penjualan'] ?? $id,
                'nama_kasir' => $trx['nama_kasir'] ?? 'Kasir',
                'tanggal_penjualan' => $trx['tanggal_penjualan'] ?? now()->toDateTimeString()
            ],
            'items' => $trx['items'] ?? [],
            'summary' => [
                'total' => $trx['total_harga'] ?? 0
            ]
        ]);
    }

    public function edit(Request $request, $id)
    {
        $history = $this->db->getPenjualan();
        $index = collect($history)->search(fn($trx) => ($trx['id_penjualan'] ?? null) == $id);

        if ($index === false) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan'], 404);
        }

        $items = $request->input('items', []);
        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Transaksi minimal harus memiliki 1 item.'], 400);
        }

        $masterBarang = collect($this->db->getBarang());
        $totalBaru = 0;

        foreach ($items as &$item) {
            $idBarang = $item['id_barang'] ?? null;
            $barang = $idBarang ? $masterBarang->firstWhere('id_barang', $idBarang) : null;
            
            $hargaSatuan = $barang ? $barang['harga_jual'] : ($item['harga_jual'] ?? 3500); 
            $namaBarang = $barang ? $barang['nama_barang'] : ($item['nama_barang'] ?? 'Item Unknown');

            $item['id_barang'] = $idBarang;
            $item['nama_barang'] = $namaBarang;
            $item['harga_jual'] = $hargaSatuan;
            $item['subtotal'] = $hargaSatuan * ($item['jumlah'] ?? 1);
            
            $totalBaru += $item['subtotal'];
        }

        $history[$index]['items'] = $items;
        $history[$index]['total_harga'] = $totalBaru;

        $this->db->savePenjualan($history);

        return response()->json([
            'success' => true, 
            'message' => 'Transaksi #' . $id . ' berhasil diperbarui!'
        ]);
    }
}