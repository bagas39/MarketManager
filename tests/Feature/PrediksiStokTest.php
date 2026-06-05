<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\DetailTransaksi;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\InteractsWithRoles;
use Tests\TestCase;

class PrediksiStokTest extends TestCase
{
    use RefreshDatabase, InteractsWithRoles;

    public function test_case_1_opening_prediksi_stok_loads_products_to_dropdown_via_ajax(): void
    {
        $this->actingAsGudang();

        $barang = Barang::factory()->create([
            'nama_barang' => 'Minyak Goreng',
        ]);

        $page = $this->get('/prediksi_stok');
        $page->assertOk();

        $response = $this->getJson('/api/prediksi_stok/barang');
        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $barang->id,
            'id_barang' => $barang->id,
            'nama_barang' => 'Minyak Goreng',
        ]);
    }

    public function test_case_2_before_selecting_product_calculate_button_is_disabled(): void
    {
        $this->actingAsGudang();

        $page = $this->get('/prediksi_stok');
        $page->assertOk();
        $page->assertSee('id="btn-hitung"', false);
        $page->assertSee('disabled', false);

        $script = File::get(resource_path('js/prediksi_stok.js'));
        $this->assertStringContainsString('btnHitung.disabled = !selectBarang.value;', $script);
    }

    public function test_case_3_selecting_product_enables_button_and_period_remains_30_hari_terakhir(): void
    {
        $this->actingAsGudang();

        $page = $this->get('/prediksi_stok');
        $page->assertOk();
        $page->assertSee('30', false);
        $page->assertSee('Hari Terakhir', false);

        $script = File::get(resource_path('js/prediksi_stok.js'));
        $this->assertStringContainsString('addEventListener("change", updateTombolHitung)', $script);
        $this->assertStringContainsString('btnHitung.disabled = !selectBarang.value;', $script);
    }

    public function test_case_4_pressing_hitung_analisis_shows_loading_analysis_text(): void
    {
        $this->actingAsGudang();

        $script = File::get(resource_path('js/prediksi_stok.js'));
        $this->assertStringContainsString('Menganalisis data ${periode} hari terakhir...', $script);
    }

    public function test_case_5_predicting_product_with_sales_history_returns_analysis_result_fields(): void
    {
        $this->actingAsGudang();

        $barang = Barang::factory()->create([
            'nama_barang' => 'Gula Kristal',
            'stok' => 40,
        ]);

        $transaksi = Transaksi::factory()->create([
            'tanggal' => now()->subDays(5)->format('Y-m-d'),
        ]);

        DetailTransaksi::factory()->create([
            'transaksi_id' => $transaksi->id,
            'barang_id' => $barang->id,
            'kuantitas' => 30,
            'subtotal' => 300000,
        ]);

        $response = $this->postJson('/api/prediksi_stok/stok', [
            'barangId' => $barang->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'can_predict' => true,
                'nama_barang' => 'Gula Kristal',
                'stok_saat_ini' => 40,
                'rata_rata_harian' => 1,
                'hari_bertahan' => 40,
                'total_terjual' => 30,
                'periode_analisis' => 30,
            ]);

        $script = File::get(resource_path('js/prediksi_stok.js'));
        $this->assertStringContainsString('HASIL ANALISIS', $script);
        $this->assertStringContainsString('STOK AMAN', $script);
        $this->assertStringContainsString('RE-STOCK', $script);
        $this->assertStringContainsString('STOK HABIS', $script);
    }

    public function test_case_6_predicting_product_without_sales_history_returns_data_tidak_cukup(): void
    {
        $this->actingAsGudang();

        $barang = Barang::factory()->create([
            'nama_barang' => 'Susu UHT',
            'stok' => 12,
        ]);

        $response = $this->postJson('/api/prediksi_stok/stok', [
            'barangId' => $barang->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'can_predict' => false,
                'nama_barang' => 'Susu UHT',
                'stok_saat_ini' => 12,
                'rata_rata_harian' => 0,
                'hari_bertahan' => null,
                'total_terjual' => 0,
                'periode_analisis' => 30,
                'message' => 'Data riwayat penjualan tidak cukup untuk diprediksi',
            ]);

        $script = File::get(resource_path('js/prediksi_stok.js'));
        $this->assertStringContainsString('DATA TIDAK CUKUP', $script);
        $this->assertStringContainsString('Data riwayat penjualan tidak cukup untuk diprediksi', $script);
    }
}
