<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JsonDataService;

class StokOpnameController extends Controller
{
    protected $db;

    public function __construct(JsonDataService $db)
    {
        $this->db = $db;
    }

    public function index()
    {
        return view('stok_opname');
    }

    public function data(Request $request)
    {
        $masterBarang = $this->db->getBarang();

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
        
        $masterBarang = $this->db->getBarang();

        foreach ($items as $item) {
            foreach ($masterBarang as &$barang) {
                if ($barang['id_barang'] == $item['id_barang']) {
                    $barang['stok'] = $item['stok_fisik'];
                    break;
                }
            }
        }

        $this->db->saveBarang($masterBarang);

        return response()->json([
            'success' => true,
            'message' => 'Stok berhasil diperbarui sesuai hasil fisik!'
        ]);
    }
}