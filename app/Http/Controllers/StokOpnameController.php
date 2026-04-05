<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class StokOpnameController extends Controller
{
    public function index()
    {
        return view('stok_opname');
    }

    public function data(Request $request)
    {
        $masterBarang = Session::get('master_barang', []);

        if (empty($masterBarang)) {
            $masterBarang = [
                ['id_barang' => 1001, 'nama_barang' => 'Minyak Goreng', 'stok' => 10],
                ['id_barang' => 1002, 'nama_barang' => 'Beras 5kg', 'stok' => 5],
            ];
            Session::put('master_barang', $masterBarang);
        }

        $allData = [];
        foreach ($masterBarang as $barang) {
            $allData[] = [
                'id_barang'   => $barang['id_barang'],
                'nama_barang' => $barang['nama_barang'],
                'stok_sistem' => $barang['stok'], 
                'stok_fisik'  => $barang['stok'],
                'selisih'     => 0,
            ];
        }

        return response()->json([
            'items' => array_values($allData)
        ]);
    }

    public function simpan(Request $request){
        $items = $request->input('items'); 
        
        $masterBarang = Session::get('master_barang', []);

        foreach ($items as $item) {
            foreach ($masterBarang as &$barang) {
                if ($barang['id_barang'] == $item['id_barang']) {
                    $barang['stok'] = $item['stok_fisik'];
                    break;
                }
            }
        }

        Session::put('master_barang', $masterBarang);

        return response()->json([
            'success' => true,
            'message' => 'Stok berhasil diperbarui sesuai hasil fisik!'
        ]);
    }
}