<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;

class BarangController extends Controller
{
    public function getBarang()
    {
        $barangs = Barang::select('id', 'kode_barang', 'nama_barang', 'kategori', 'harga_beli', 'harga_jual', 'stok')
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
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Barang::query();

        if ($searchNama) {
            $query->where(function ($subQuery) use ($searchNama) {
                $subQuery->where('nama_barang', 'like', "%{$searchNama}%")
                    ->orWhere('kode_barang', 'like', "%{$searchNama}%")
                    ->orWhere('kategori', 'like', "%{$searchNama}%");
            });
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
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

    public function destroy(Request $request, $id)
    {
        try {
            $barang = Barang::find($id);

            if (!$barang) {
                return response()->json(['success' => false, 'message' => 'Barang tidak ditemukan.'], 404);
            }

            if (($barang->stok ?? 0) > 0) {
                return response()->json(['success' => false, 'message' => 'Barang masih memiliki stok, tidak dapat dihapus.'], 400);
            }

            $barang->delete();

            return response()->json(['success' => true, 'message' => 'Barang berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}