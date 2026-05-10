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
        $items = Barang::select('id', 'nama_barang', 'stok')->get()->map(function($barang) {
            return [
                'id_barang'   => $barang->id,
                'nama_barang' => $barang->nama_barang,
                'stok_sistem' => $barang->stok, 
                'stok_fisik'  => $barang->stok,
                'selisih'     => 0,
            ];
        });

        return response()->json(['items' => $items]);
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

                    if ($selisih != 0) { 
                        StokOpname::create([
                            'barang_id' => $barang->id,
                            'user_id' => Auth::id() ?? 1,
                            'stok_sistem' => $stokSistem,
                            'stok_fisik' => $stokFisik,
                            'selisih' => $selisih,
                            'keterangan' => 'Opname Penyesuaian'
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
}