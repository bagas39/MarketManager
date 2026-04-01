<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BarangController extends Controller
{
    private function getMasterData()
    {
        if (!Session::has('master_barang')) {
            $data = [
                ['id_barang' => 1001, 'nama_barang' => 'Indomie Goreng Spesial', 'kategori' => 'Makanan', 'harga_beli' => 3000, 'harga_jual' => 3500, 'stok' => 150],
                ['id_barang' => 1002, 'nama_barang' => 'Telur Ayam 1kg', 'kategori' => 'Sembako', 'harga_beli' => 25000, 'harga_jual' => 28000, 'stok' => 50],
                ['id_barang' => 1003, 'nama_barang' => 'Beras Makmur 5kg', 'kategori' => 'Sembako', 'harga_beli' => 60000, 'harga_jual' => 65000, 'stok' => 20],
                ['id_barang' => 1004, 'nama_barang' => 'Sabun Mandi Cair', 'kategori' => 'Kebutuhan Mandi', 'harga_beli' => 15000, 'harga_jual' => 18000, 'stok' => 0],
            ];

            $kategori = ['Minuman', 'Cemilan', 'Bumbu Dapur'];
            for ($i = 5; $i <= 25; $i++) {
                $data[] = [
                    'id_barang'   => 1000 + $i,
                    'nama_barang' => 'Produk Dummy ' . $i,
                    'kategori'    => $kategori[array_rand($kategori)],
                    'harga_beli'  => rand(5, 50) * 1000,
                    'harga_jual'  => rand(6, 60) * 1000,
                    'stok'        => rand(5, 100)
                ];
            }

            Session::put('master_barang', $data);
        }

        return Session::get('master_barang');
    }

    public function getBarang()
    {
        return response()->json($this->getMasterData());
    }

    public function listStok(Request $request)
    {
        $offset = $request->query('offset', 0);
        $limit = $request->query('limit', 15);
        $searchNama = $request->query('search_nama');

        $allData = $this->getMasterData();

        if ($searchNama) {
            $allData = array_filter($allData, function ($item) use ($searchNama) {
                $matchNama = stripos(strtolower($item['nama_barang']), strtolower($searchNama)) !== false;
                $matchId = stripos((string)$item['id_barang'], $searchNama) !== false;
                return $matchNama || $matchId;
            });
        }

        $allData = array_values($allData);
        $totalAvailable = count($allData);

        $pagedData = array_slice($allData, $offset, $limit);

        return response()->json([
            'items' => $pagedData,
            'totalAvailableItems' => $totalAvailable
        ]);
    }
}