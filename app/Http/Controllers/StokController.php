<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StokController extends Controller
{
    public function index()
    {
        return view('manajemen_stok');
    }

    public function data(Request $request)
    {
        $allData = [];
        $kategori = ['Makanan', 'Minuman', 'Sembako', 'Kebutuhan Mandi', 'Cemilan'];
        
        for ($i = 1; $i <= 35; $i++) {
            $allData[] = [
                'id_barang'   => 1000 + $i,
                'nama_barang' => 'Produk Dummy ' . $i,
                'kategori'    => $kategori[array_rand($kategori)],
                'harga_beli'  => rand(5, 50) * 1000,
                'harga_jual'  => rand(6, 60) * 1000,
                'stok'        => rand(0, 100)
            ];
        }

        $searchNama = $request->query('search_nama');
        $offset = $request->query('offset', 0);
        $limit = $request->query('limit', 15);

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