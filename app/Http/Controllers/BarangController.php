<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;

class BarangController extends Controller
{
    public function getBarang()
    {
        $barangs = Barang::select('id', 'kode_barang', 'nama_barang', 'harga_beli', 'harga_jual', 'stok')
            ->orderBy('nama_barang', 'asc')
            ->get()
            ->map(function($item) {
                $item->id_barang = $item->id; 
                return $item;
            });
            
        return response()->json($barangs);
    }

    public function listStok(Request $request)
    {
        $limit = $request->query('limit', 15);
        $searchNama = $request->query('search_nama');

        $query = Barang::query();

        if ($searchNama) {
            $query->where('nama_barang', 'like', "%{$searchNama}%")
                  ->orWhere('kode_barang', 'like', "%{$searchNama}%");
        }

        $paginator = $query->orderBy('nama_barang', 'asc')->paginate($limit);

        $items = $paginator->getCollection()->map(function($item) {
            $item->id_barang = $item->id;
            return $item;
        });

        return response()->json([
            'items' => $items,
            'totalAvailableItems' => $paginator->total() 
        ]);
    }
}