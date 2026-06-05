<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\StokOpname;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void {
        // 1. Buat User per Role
        User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'Owner',
        ]);
        
        $kasir = User::create([
            'name' => 'Kasir Utama',
            'email' => 'kasir@example.com',
            'password' => bcrypt('password'),
            'role' => 'Kasir',
        ]);

        $gudang = User::create([
            'name' => 'Gudang Pusat',
            'email' => 'gudang@example.com',
            'password' => bcrypt('password'),
            'role' => 'Gudang',
        ]);

        User::create([
            'name' => 'SPV',
            'email' => 'spv@example.com',
            'password' => bcrypt('password'),
            'role' => 'Supervisor',
        ]);

            // 2. Buat daftar barang melalui proses pembelian 
            $barangs = collect();

            // 3. Simulasi Pembelian Masuk
            Pembelian::factory(3)->create(['user_id' => $gudang->id])->each(function ($po) use (&$barangs) {
                $totalBiaya = 0;
                $count = rand(2, 5);

                for ($i = 0; $i < $count; $i++) {
                    $qty = rand(10, 50);

                    $barang = Barang::factory()->create(['stok' => 0]);
                    $hargaBeli = $barang->harga_beli;
                    $itemSubtotal = $hargaBeli * $qty;
                    $totalBiaya += $itemSubtotal;

                    DetailPembelian::create([
                        'pembelian_id' => $po->id,
                        'barang_id' => $barang->id,
                        'harga_beli' => $hargaBeli,
                        'kuantitas' => $qty,
                        'subtotal' => $itemSubtotal
                    ]);

                    $barang->increment('stok', $qty);

                    $barangs->push($barang);
                }

                $po->update(['total_biaya' => round($totalBiaya, 2)]);
            });

            // 4. Simulasi Transaksi Penjualan
            Transaksi::factory(5)->create(['user_id' => $kasir->id])->each(function ($trx) use ($barangs) {
                $subtotal = 0;

                if ($barangs->isEmpty()) {
                    return;
                }

                $items = $barangs->random(min($barangs->count(), rand(1, 3)));

                foreach ($items as $item) {
                    $available = $item->stok;
                    if ($available <= 0) {
                        continue;
                    }
                    $maxQty = min(5, $available);
                    $qty = rand(1, $maxQty);

                    $itemSubtotal = $item->harga_jual * $qty;
                    $subtotal += $itemSubtotal;

                    DetailTransaksi::create([
                        'transaksi_id' => $trx->id,
                        'barang_id' => $item->id,
                        'kuantitas' => $qty,
                        'subtotal' => $itemSubtotal
                    ]);

                    $item->decrement('stok', $qty);
                }

                $trx->update(['total_harga' => round($subtotal * 1.11, 2)]);
            });

            // 5. Simulasi Riwayat Stok Opname
            foreach ($barangs->random(5) as $barang) {
                $stokSistem = $barang->stok;
                $stokFisik = $stokSistem + rand(-2, 2); 
                
                StokOpname::factory()->create([
                    'barang_id' => $barang->id,
                    'user_id' => $gudang->id,
                    'stok_sistem' => $stokSistem,
                    'stok_fisik' => $stokFisik,
                    'selisih' => $stokFisik - $stokSistem,
                    'keterangan' => 'Seeder Dummy'
                ]);
            }
        
    }
}