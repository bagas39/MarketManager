<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class JsonDataService
{
    //direktori storage/app/
    private $folder = 'mock_json'; 


    private function readJson($filename, $defaultDataCallback)
    {
        $filePath = $this->folder . '/' . $filename;
        
        if (!Storage::exists($filePath)) {
            $data = $defaultDataCallback();
            $this->writeJson($filename, $data);
            return $data;
        }

        return json_decode(Storage::get($filePath), true);
    }

    private function writeJson($filename, $data)
    {
        $filePath = $this->folder . '/' . $filename;
        Storage::put($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    // MANAJEMEN PENGGUNA

    public function getUsers()
    {
        return $this->readJson('users.json', function () {
            return [
                ['id' => 1, 'username' => 'owner', 'password' => '12345', 'nama' => 'Owner', 'role' => 'Owner'],
                ['id' => 2, 'username' => 'kasir', 'password' => '12345', 'nama' => 'Kasir', 'role' => 'Kasir'],
                ['id' => 3, 'username' => 'gudang', 'password' => '12345', 'nama' => 'Gudang', 'role' => 'Gudang'],
                ['id' => 4, 'username' => 'spv', 'password' => '12345', 'nama' => 'SPV', 'role' => 'Supervisor'],
            ];
        });
    }

    public function saveUsers($data) { 
        $this->writeJson('users.json', $data); 
    }

    // MANAJEMEN BARANG & STOK

    public function getBarang()
    {
        return $this->readJson('barang.json', function () {
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
            return $data;
        });
    }

    public function saveBarang($data) { 
        $this->writeJson('barang.json', $data); 
    }

    // MANAJEMEN TRANSAKSI PENJUALAN dan PEMBELIAN

    public function getPenjualan() { 
        return $this->readJson('penjualan.json', fn() => []); 
    }
    
    public function savePenjualan($data) { 
        $this->writeJson('penjualan.json', $data); 
    }

    public function getPembelian() { 
        return $this->readJson('pembelian.json', fn() => []); 
    }
    
    public function savePembelian($data) { 
        $this->writeJson('pembelian.json', $data); 
    }
}