<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class KasirController extends Controller
{
    public function index() { return view('kasir'); }

    public function getBarang() {
        return response()->json(Session::get('master_barang', []));
    }

    public function storeTransaksi(Request $request)
    {
        try {
            $items = $request->input('items', []); 
            
            if (empty($items)) {
                return response()->json(['success' => false, 'message' => 'Item tidak boleh kosong!'], 400);
            }

            $masterBarang = Session::get('master_barang', []);
            if (!is_array($masterBarang)) { $masterBarang = []; }
            
            $enrichedItems = [];
            $total = 0;

            foreach($items as $item) {
                $idBarang = $item['id_barang'] ?? $item['idBarang'] ?? null;
                $jumlah = isset($item['jumlah']) ? (int)$item['jumlah'] : 1;

                if (!$idBarang) continue;

                $hargaJual = 3500;
                $namaBarang = 'Item Unknown';

                foreach ($masterBarang as $idx => $mb) {
                    if ($mb['id_barang'] == $idBarang) {
                        $hargaJual = $mb['harga_jual'];
                        $namaBarang = $mb['nama_barang'];

                        $masterBarang[$idx]['stok'] -= $jumlah; 

                        break;
                    }
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

            Session::put('master_barang', $masterBarang);

            $newTransaction = [
                'id_penjualan' => rand(5000, 9999),
                'tanggal_penjualan' => now()->toDateTimeString(),
                'nama_kasir' => session('user_name', 'Kasir Umum'),
                'total_harga' => $total,
                'items' => $enrichedItems 
            ];

            $history = Session::get('history_penjualan', []);
            if (!is_array($history)) { $history = []; }
            array_unshift($history, $newTransaction); 
            Session::put('history_penjualan', $history);

            return response()->json([
                'success' => true,
                'id_penjualan' => $newTransaction['id_penjualan'],
                'message' => 'Transaksi berhasil & stok gudang telah dikurangi!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error Server: ' . $e->getMessage()
            ], 500);
        }
    }
}