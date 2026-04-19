<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JsonDataService;

class PembelianController extends Controller
{
    protected $db;

    public function __construct(JsonDataService $db)
    {
        $this->db = $db;
    }

    public function index() { return view('transaksi_pembelian'); }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'supplier' => 'required|string',
                'idGudang' => 'required|integer',
                'items'    => 'required|array'
            ]);

            $total = 0;
            $masterBarang = $this->db->getBarang();
            $itemsLengkap = [];

            foreach ($data['items'] as $item) {
                $idBarang = isset($item['id_barang']) ? (int)$item['id_barang'] : null;
                $jumlah = isset($item['jumlah']) ? (int)$item['jumlah'] : 1;
                $hargaBeliInput = isset($item['hargaBeli']) ? (float)$item['hargaBeli'] : 0;

                if (!$idBarang) continue;

                $barangIndex = null;
                foreach ($masterBarang as $idx => $mb) {
                    if ($mb['id_barang'] == $idBarang) {
                        $barangIndex = $idx; break;
                    }
                }
                
                if ($barangIndex !== null) {
                    $masterBarang[$barangIndex]['stok'] += $jumlah;
                    $masterBarang[$barangIndex]['harga_beli'] = $hargaBeliInput; 
                    $namaBarang = $masterBarang[$barangIndex]['nama_barang'];
                } else {
                    $namaBarang = $item['namaBarang'] ?? 'Barang Baru';
                    $idBarangBaru = $idBarang ?: rand(2000, 9999);

                    $barangBaru = [
                        'id_barang' => $idBarangBaru,
                        'nama_barang' => $namaBarang,
                        'kategori' => 'Lain-lain',
                        'harga_beli' => $hargaBeliInput,
                        'harga_jual' => $hargaBeliInput * 1.2,
                        'stok' => $jumlah
                    ];
                    array_unshift($masterBarang, $barangBaru);
                    $idBarang = $idBarangBaru;
                }

                $subtotal = $hargaBeliInput * $jumlah;
                $total += $subtotal;

                $itemsLengkap[] = [
                    'id_barang' => $idBarang,
                    'nama_barang' => $namaBarang,
                    'harga_beli' => $hargaBeliInput,
                    'jumlah' => $jumlah,
                    'subtotal' => $subtotal
                ];
            }

            $this->db->saveBarang($masterBarang);

            $newId = rand(10000, 99999);
            $history = $this->db->getPembelian();

            array_unshift($history, [
                'id_pembelian'      => $newId,
                'supplier'          => $data['supplier'],
                'total_beli'        => $total,
                'tanggal_pembelian' => now()->toDateTimeString(),
                'items'             => $itemsLengkap 
            ]);
            
            $this->db->savePembelian($history);

            return response()->json(['success' => true, 'id_pembelian' => $newId, 'message' => 'Stok berhasil diperbarui!']);

        } catch (\Exception $e) {
             return response()->json(['success' => false, 'message' => 'Error Server: ' . $e->getMessage()], 500);
        }
    }

    public function history(Request $request)
    {
        $history = $this->db->getPembelian();
        
        $search = $request->query('search_supplier');
        if ($search) {
            $history = array_filter($history, fn($item) => stripos(strtolower($item['supplier']), strtolower($search)) !== false);
        }

        return response()->json(['purchases' => array_values(array_slice($history, 0, 10))]);
    }
}