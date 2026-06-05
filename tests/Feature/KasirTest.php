<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\InteractsWithRoles;
use Tests\TestCase;

class KasirTest extends TestCase
{
    use RefreshDatabase, InteractsWithRoles;

    public function test_case_1_shows_validation_message_for_empty_sku_or_invalid_qty(): void
    {
        $this->actingAsKasir();

        $response = $this->get('/');
        $response->assertOk();

        $kasirJsPath = resource_path('js/kasir.js');
        $this->assertTrue(File::exists($kasirJsPath));

        $script = File::get($kasirJsPath);

        $this->assertStringContainsString('Pastikan SKU dan Jumlah diisi dengan benar.', $script);
        $this->assertStringContainsString("showMessage('Input Tidak Valid'", $script);
    }

    public function test_case_2_rejects_add_item_when_stock_is_not_enough(): void
    {
        $this->actingAsKasir();

        $barang = Barang::factory()->create([
            'stok' => 3,
            'harga_jual' => 10000,
        ]);

        $response = $this->postJson('/api/transaksi', [
            'items' => [
                [
                    'id_barang' => $barang->id,
                    'jumlah' => 5,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertStringContainsString('tidak mencukupi', strtolower($response->json('message')));
        $this->assertStringContainsString('Sisa: 3', $response->json('message'));

        $this->assertDatabaseCount('transaksis', 0);
        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
            'stok' => 3,
        ]);
    }

    public function test_case_3_valid_item_checkout_persists_data_and_calculates_total_with_11_percent_tax(): void
    {
        $this->actingAsKasir();

        $barang = Barang::factory()->create([
            'harga_jual' => 10000,
            'stok' => 20,
        ]);

        $response = $this->postJson('/api/transaksi', [
            'items' => [
                [
                    'id_barang' => $barang->id,
                    'jumlah' => 2,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('detail_transaksis', [
            'barang_id' => $barang->id,
            'kuantitas' => 2,
            'subtotal' => 20000,
        ]);

        $transaksi = Transaksi::first();
        $this->assertNotNull($transaksi);
        $this->assertEquals(22200.0, (float) $transaksi->total_harga);

        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
            'stok' => 18,
        ]);
    }

    public function test_case_4_remove_item_handler_exists_and_triggers_total_recalculation(): void
    {
        $this->actingAsKasir();

        $response = $this->get('/');
        $response->assertOk();

        $script = File::get(resource_path('js/kasir.js'));

        $this->assertStringContainsString('window.removeFromCart = function(id)', $script);
        $this->assertStringContainsString('delete cart[id];', $script);
        $this->assertStringContainsString('renderCart();', $script);
        $this->assertStringContainsString('updateTotals();', $script);
    }

    public function test_case_5_blocks_checkout_when_payment_amount_is_less_than_total(): void
    {
        $this->actingAsKasir();

        $response = $this->get('/');
        $response->assertOk();

        $script = File::get(resource_path('js/kasir.js'));

        $this->assertStringContainsString('if (amountPaid < currentTotal)', $script);
        $this->assertStringContainsString('Jumlah bayar tidak mencukupi untuk total belanja.', $script);
    }

    public function test_case_6_valid_checkout_returns_success_and_frontend_reset_flow_is_defined(): void
    {
        $this->actingAsKasir();

        $barang = Barang::factory()->create([
            'harga_jual' => 5000,
            'stok' => 10,
        ]);

        $response = $this->postJson('/api/transaksi', [
            'items' => [
                [
                    'id_barang' => $barang->id,
                    'jumlah' => 2,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Transaksi berhasil & stok gudang telah dikurangi!',
            ]);

        $script = File::get(resource_path('js/kasir.js'));
        $this->assertStringContainsString("showMessage('Transaksi Berhasil'", $script);
        $this->assertStringContainsString('resetSistem();', $script);
    }
}
