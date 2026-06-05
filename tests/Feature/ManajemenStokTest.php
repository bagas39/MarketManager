<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\InteractsWithRoles;
use Tests\TestCase;

class ManajemenStokTest extends TestCase
{
    use RefreshDatabase, InteractsWithRoles;

    public function test_case_1_opening_stock_page_loads_table_and_ajax_data_structure(): void
    {
        $this->actingAsKasir();

        Barang::factory()->create([
            'kode_barang' => 'SKU-001',
            'nama_barang' => 'Beras Premium',
            'kategori' => 'Sembako',
            'harga_beli' => 10000,
            'harga_jual' => 12000,
            'stok' => 25,
        ]);

        $page = $this->get('/manajemen_stok');
        $page->assertOk();
        $page->assertSee('ID/SKU');
        $page->assertSee('Nama Barang');
        $page->assertSee('Kategori');
        $page->assertSee('Harga Beli');
        $page->assertSee('Harga Jual');
        $page->assertSee('Stok');

        $api = $this->getJson('/api/manajemen_stok');
        $api->assertOk()
            ->assertJsonStructure([
                'items' => [
                    '*' => [
                        'id',
                        'id_barang',
                        'kode_barang',
                        'nama_barang',
                        'kategori',
                        'harga_beli',
                        'harga_jual',
                        'stok',
                    ],
                ],
                'totalAvailableItems',
            ]);
    }

    public function test_case_2_search_by_item_name_filters_results(): void
    {
        $this->actingAsKasir();

        Barang::factory()->create([
            'kode_barang' => 'SKU-APEL',
            'nama_barang' => 'Apel Fuji',
            'kategori' => 'Buah',
        ]);

        Barang::factory()->create([
            'kode_barang' => 'SKU-BERAS',
            'nama_barang' => 'Beras Ramos',
            'kategori' => 'Sembako',
        ]);

        $response = $this->getJson('/api/manajemen_stok?search_nama=Apel');
        $response->assertOk();

        $items = $response->json('items');
        $this->assertCount(1, $items);
        $this->assertSame('Apel Fuji', $items[0]['nama_barang']);
    }

    public function test_case_3_search_by_item_code_filters_results(): void
    {
        $this->actingAsKasir();

        Barang::factory()->create([
            'kode_barang' => 'KD-001-X',
            'nama_barang' => 'Gula Pasir',
        ]);

        Barang::factory()->create([
            'kode_barang' => 'KD-002-Y',
            'nama_barang' => 'Teh Hijau',
        ]);

        $response = $this->getJson('/api/manajemen_stok?search_nama=KD-001-X');
        $response->assertOk();

        $items = $response->json('items');
        $this->assertCount(1, $items);
        $this->assertSame('KD-001-X', $items[0]['kode_barang']);
    }

    public function test_case_4_unavailable_keyword_returns_empty_data_and_frontend_empty_message_exists(): void
    {
        $this->actingAsKasir();

        Barang::factory()->count(2)->create();

        $response = $this->getJson('/api/manajemen_stok?search_nama=TidakAdaKeywordSamaSekali');
        $response->assertOk();
        $this->assertCount(0, $response->json('items'));

        $script = File::get(resource_path('js/manajemen_stok.js'));
        $this->assertStringContainsString('Tidak ada data barang yang ditemukan.', $script);
    }

    public function test_case_5_clearing_search_returns_to_initial_stock_list(): void
    {
        $this->actingAsKasir();

        Barang::factory()->create([
            'kode_barang' => 'MIE-01',
            'nama_barang' => 'Mie Instan Goreng',
        ]);

        Barang::factory()->create([
            'kode_barang' => 'SAB-01',
            'nama_barang' => 'Sabun Mandi',
        ]);

        $filtered = $this->getJson('/api/manajemen_stok?search_nama=Mie');
        $filtered->assertOk();
        $this->assertCount(1, $filtered->json('items'));

        $reset = $this->getJson('/api/manajemen_stok');
        $reset->assertOk();
        $this->assertGreaterThanOrEqual(2, count($reset->json('items')));
    }

    public function test_case_6_clicking_next_page_loads_next_stock_data_and_total_info(): void
    {
        $this->actingAsKasir();

        Barang::factory()->count(20)->create();

        $pageOne = $this->getJson('/api/manajemen_stok?page=1&limit=15');
        $pageOne->assertOk();
        $this->assertCount(15, $pageOne->json('items'));
        $this->assertSame(20, $pageOne->json('totalAvailableItems'));

        $pageTwo = $this->getJson('/api/manajemen_stok?page=2&limit=15');
        $pageTwo->assertOk();
        $this->assertCount(5, $pageTwo->json('items'));
        $this->assertSame(20, $pageTwo->json('totalAvailableItems'));

        $script = File::get(resource_path('js/manajemen_stok.js'));
        $this->assertStringContainsString("nextButton.addEventListener('click'", $script);
        $this->assertStringContainsString('fetchStok(currentPage + 1)', $script);
    }

    public function test_case_7_clicking_previous_page_returns_to_previous_stock_data(): void
    {
        $this->actingAsKasir();

        Barang::factory()->count(20)->create();

        $pageTwo = $this->getJson('/api/manajemen_stok?page=2&limit=15');
        $pageTwo->assertOk();
        $this->assertCount(5, $pageTwo->json('items'));

        $pageOne = $this->getJson('/api/manajemen_stok?page=1&limit=15');
        $pageOne->assertOk();
        $this->assertCount(15, $pageOne->json('items'));

        $script = File::get(resource_path('js/manajemen_stok.js'));
        $this->assertStringContainsString("prevButton.addEventListener('click'", $script);
        $this->assertStringContainsString('fetchStok(currentPage - 1)', $script);
    }
}
