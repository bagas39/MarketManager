<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JsonDataService;

class KasirController extends Controller
{
    protected $db;

    public function __construct(JsonDataService $db)
    {
        $this->db = $db;
    }

    public function index() { return view('kasir'); }

    public function getBarang() {
        return response()->json($this->db->getBarang());
    }

    public function storeTransaksi(Request $request)
    {
        try {
            $items = $request->input('items', []); 
            
            if (empty($items)) {
                return response()->json(['success' => false, 'message' => 'Item tidak boleh kosong!'], 400);
            }

            $masterBarang = $this->db->getBarang();
            $enrichedItems = [];
            $total = 0;

            foreach($items as $item) {
                $idBarang = $item['id_barang'] ?? $item['idBarang'] ?? null;
                $jumlah = isset($item['jumlah']) ? (int)$item['jumlah'] : 1;

                if (!$idBarang) continue;

                $barangDitemukan = false;

                foreach ($masterBarang as $idx => &$mb) {
                    if ($mb['id_barang'] == $idBarang) {
                        $hargaJual = $mb['harga_jual'];
                        $namaBarang = $mb['nama_barang'];
                        
                        if ($mb['stok'] < $jumlah) {
                             return response()->json(['success' => false, 'message' => "Stok $namaBarang tidak mencukupi!"], 400);
                        }

                        $mb['stok'] -= $jumlah; 
                        $barangDitemukan = true;
                        break;
                    }
                }

                if (!$barangDitemukan) {
                    return response()->json(['success' => false, 'message' => "Barang dengan ID $idBarang tidak ditemukan di database!"], 404);
                }

                $subtotal = $hargaJual * $jumlah;

                $enrichedItems[] = [
                    'id_barang' => $idBarang,
                    'nama_barang' => $namaBarang,
                    'harga_jual' => $hargaJual,
                    'jumlah' => $jumlah,
                    'subtotal' => $subtotal
                ];

                $total += $subtotal;
            }

            $this->db->saveBarang($masterBarang);

            $newTransaction = [
                'id_penjualan' => rand(5000, 9999),
                'tanggal_penjualan' => now()->toDateTimeString(),
                'nama_kasir' => session('user_name', 'Kasir Umum'),
                'total_harga' => $total,
                'items' => $enrichedItems 
            ];

            $history = $this->db->getPenjualan();
            array_unshift($history, $newTransaction); 
            $this->db->savePenjualan($history);

            return response()->json([
                'success' => true,
                'id_penjualan' => $newTransaction['id_penjualan'],
                'message' => 'Transaksi berhasil & stok gudang telah dikurangi!'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error Server: ' . $e->getMessage()], 500);
        }
    }
}