<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\InteractsWithRoles;
use Tests\TestCase;

class TransaksiPembelianTest extends TestCase
{
    use RefreshDatabase, InteractsWithRoles;

    public function test_case_1_adding_existing_item_matches_master_by_sku_or_name_and_enters_temporary_list(): void
    {
        $this->actingAsGudang();

        $script = File::get(resource_path('js/pembelian.js'));

        $this->assertStringContainsString('let product = allProducts.find(p =>', $script);
        $this->assertStringContainsString('(p.kode_barang && p.kode_barang.toString() === nameOrSku)', $script);
        $this->assertStringContainsString('(p.nama_barang && p.nama_barang.toLowerCase() === nameOrSku.toLowerCase())', $script);
        $this->assertStringContainsString('items.push({ id_barang: id, namaBarang: name, kategori, hargaBeli: price, jumlah: qty });', $script);
        $this->assertStringContainsString('renderCart();', $script);
    }

    public function test_case_2_adding_new_item_creates_new_product_with_incremental_code_on_submit(): void
    {
        $this->actingAsGudang();

        $today = now()->format('Ymd');

        Barang::factory()->create([
            'kode_barang' => 'BRG-' . $today . '-0005',
            'nama_barang' => 'Barang Lama',
            'stok' => 10,
        ]);

        $response = $this->postJson('/pembelian/store', [
            'supplier' => 'PT Sumber Makmur',
            'items' => [
                [
                    'id_barang' => null,
                    'namaBarang' => 'Keripik Singkong Premium',
                    'kategori' => 'Snack',
                    'hargaBeli' => 10000,
                    'jumlah' => 4,
                ],
            ],
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
        ]);

        $barangBaru = Barang::where('nama_barang', 'Keripik Singkong Premium')->first();
        $this->assertNotNull($barangBaru);
        $this->assertSame('BRG-' . $today . '-0006', $barangBaru->kode_barang);

        $this->assertDatabaseHas('barangs', [
            'id' => $barangBaru->id,
            'kategori' => 'Snack',
            'harga_beli' => 10000,
            'harga_jual' => 12000,
            'stok' => 4,
        ]);

        $this->assertDatabaseHas('detail_pembelians', [
            'barang_id' => $barangBaru->id,
            'harga_beli' => 10000,
            'kuantitas' => 4,
            'subtotal' => 40000,
        ]);
    }

    public function test_case_3_submit_without_supplier_is_blocked_by_frontend_validation_message(): void
    {
        $this->actingAsGudang();

        $script = File::get(resource_path('js/pembelian.js'));

        $this->assertStringContainsString("if(!sup) return modal(\"Validasi Gagal\", \"Supplier wajib diisi\")", $script);
    }

    public function test_case_4_valid_purchase_submit_sets_processing_state_saves_transaction_and_clears_supplier(): void
    {
        $this->actingAsGudang();

        $barang = Barang::factory()->create([
            'kode_barang' => 'SKU-EXIST-01',
            'nama_barang' => 'Susu Bubuk',
            'stok' => 7,
            'harga_beli' => 9000,
        ]);

        $response = $this->postJson('/pembelian/store', [
            'supplier' => 'PT Sejahtera Abadi',
            'items' => [
                [
                    'id_barang' => $barang->id,
                    'namaBarang' => 'Susu Bubuk',
                    'kategori' => 'Minuman',
                    'hargaBeli' => 11000,
                    'jumlah' => 3,
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertStringStartsWith('PO-' . now()->format('Ymd') . '-', $response->json('id_pembelian'));

        $this->assertDatabaseHas('pembelians', [
            'no_pembelian' => $response->json('id_pembelian'),
            'nama_supplier' => 'PT Sejahtera Abadi',
            'total_biaya' => 33000,
        ]);

        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
            'stok' => 10,
            'harga_beli' => 11000,
            'kategori' => 'Minuman',
        ]);

        $this->assertDatabaseHas('detail_pembelians', [
            'barang_id' => $barang->id,
            'harga_beli' => 11000,
            'kuantitas' => 3,
            'subtotal' => 33000,
        ]);

        $script = File::get(resource_path('js/pembelian.js'));
        $this->assertStringContainsString('this.innerHTML = "Menyimpan...";', $script);
        $this->assertStringContainsString('Nomor Faktur (PO): #${json.id_pembelian}', $script);
        $this->assertStringContainsString("document.getElementById('supplier-input').value = ''", $script);
    }

    public function test_case_5_history_is_loaded_and_filter_by_supplier_works(): void
    {
        $user = $this->actingAsGudang();

        Pembelian::factory()->create([
            'no_pembelian' => 'PO-' . now()->format('Ymd') . '-0001',
            'nama_supplier' => 'PT Alpha Supplier',
            'user_id' => $user->id,
            'total_biaya' => 150000,
            'tanggal' => now()->format('Y-m-d'),
        ]);

        Pembelian::factory()->create([
            'no_pembelian' => 'PO-' . now()->format('Ymd') . '-0002',
            'nama_supplier' => 'PT Beta Supplier',
            'user_id' => $user->id,
            'total_biaya' => 250000,
            'tanggal' => now()->format('Y-m-d'),
        ]);

        $allHistory = $this->getJson('/pembelian/history');
        $allHistory->assertOk()->assertJsonStructure([
            'purchases' => [
                '*' => [
                    'id_pembelian',
                    'supplier',
                    'total_beli',
                    'tanggal_pembelian',
                ],
            ],
        ]);

        $this->assertGreaterThanOrEqual(2, count($allHistory->json('purchases')));

        $filtered = $this->getJson('/pembelian/history?search_supplier=Alpha');
        $filtered->assertOk();

        $purchases = $filtered->json('purchases');
        $this->assertCount(1, $purchases);
        $this->assertSame('PT Alpha Supplier', $purchases[0]['supplier']);
        $this->assertSame(150000.0, (float) $purchases[0]['total_beli']);

        $script = File::get(resource_path('js/pembelian.js'));
        $this->assertStringContainsString('fetch(`/pembelian/history?search_supplier=${search}`)', $script);
    }
}
