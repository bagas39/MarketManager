<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\StokOpname;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StokOpnameController extends Controller
{
    public function index() { return view('stok_opname'); }

    public function data(Request $request)
    {
        $limit = (int) $request->query('limit', 10);
        $page = (int) $request->query('page', 1);
        $limit = max(1, min($limit, 100));
        $page = max(1, $page);

        $paginator = Barang::select('id', 'kode_barang', 'nama_barang', 'stok')
            ->orderBy('nama_barang', 'asc')
            ->paginate($limit, ['*'], 'page', $page);

        $items = $paginator->getCollection()->map(function($barang) {
            return [
                'id_barang'   => $barang->id,
                'kode_barang' => $barang->kode_barang,
                'nama_barang' => $barang->nama_barang,
                'stok_sistem' => $barang->stok, 
                'stok_fisik'  => $barang->stok,
                'selisih'     => 0,
                    'keterangan'  => '',
            ];
        });

        return response()->json([
            'items' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function simpan(Request $request)
    {
        $items = $request->input('items', []); 
        
        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                $barang = Barang::where('id', $item['id_barang'])->lockForUpdate()->first();
                if ($barang) {
                    $stokSistem = $barang->stok;
                    $stokFisik = (int)$item['stok_fisik'];
                    $selisih = $stokFisik - $stokSistem;
                        $keterangan = trim((string)($item['keterangan'] ?? ''));

                    if ($selisih != 0) { 
                        StokOpname::create([
                            'barang_id' => $barang->id,
                            'user_id' => Auth::id() ?? 1,
                            'stok_sistem' => $stokSistem,
                            'stok_fisik' => $stokFisik,
                            'selisih' => $selisih,
                                'keterangan' => $keterangan !== '' ? $keterangan : 'Opname Penyesuaian'
                        ]);

                        $barang->stok = $stokFisik;
                        $barang->save();
                    }
                }
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil diperbarui sesuai hasil fisik!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memproses Opname: ' . $e->getMessage()], 500);
        }
    }

    public function history(Request $request)
    {
        $limit = (int) $request->query('limit', 50);
        $limit = max(1, min($limit, 200));

        $query = StokOpname::with(['barang:id,nama_barang,kode_barang', 'user:id,name']);

        $history = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'kode_barang' => $item->barang->kode_barang ?? '-',
                    'nama_barang' => $item->barang->nama_barang ?? '-',
                    'diubah_oleh' => $item->user->name ?? 'Unknown',
                    'stok_sistem' => $item->stok_sistem,
                    'stok_fisik' => $item->stok_fisik,
                    'selisih' => $item->selisih,
                    'keterangan' => $item->keterangan,
                    'waktu' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json(['items' => $history]);
    }
}