<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\StokOpname as StokOpnameModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\InteractsWithRoles;
use Tests\TestCase;

class StokOpnameTest extends TestCase
{
    use RefreshDatabase, InteractsWithRoles;

    public function test_case_1_opening_stok_opname_page_loads_table_data_via_ajax(): void
    {
        $this->actingAsGudang();

        $barang = Barang::factory()->create([
            'kode_barang' => 'OPN-001',
            'nama_barang' => 'Kopi Bubuk',
            'stok' => 20,
        ]);

        $viewSource = File::get(resource_path('views/stok_opname.blade.php'));
        $this->assertStringContainsString('Kode Produk', $viewSource);
        $this->assertStringContainsString('Nama Produk', $viewSource);
        $this->assertStringContainsString('Stok Sistem', $viewSource);
        $this->assertStringContainsString('Stok Fisik', $viewSource);
        $this->assertStringContainsString('Keterangan', $viewSource);
        $this->assertStringContainsString('Selisih', $viewSource);

        $response = $this->getJson('/api/stok_opname/data?page=1&limit=10');
        $response->assertOk();
        $response->assertJsonFragment([
            'id_barang' => $barang->id,
            'kode_barang' => 'OPN-001',
            'nama_barang' => 'Kopi Bubuk',
            'stok_sistem' => 20,
            'stok_fisik' => 20,
            'selisih' => 0,
        ]);
    }

    public function test_case_2_when_stok_fisik_equals_stok_sistem_difference_is_zero(): void
    {
        $this->actingAsGudang();

        $barang = Barang::factory()->create([
            'stok' => 15,
        ]);

        $response = $this->postJson('/api/stok_opname/simpan', [
            'items' => [
                [
                    'id_barang' => $barang->id,
                    'stok_fisik' => 15,
                    'keterangan' => 'Sesuai',
                ],
            ],
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseCount('stok_opnames', 0);

        $script = File::get(resource_path('js/stok_opname.js'));
        $this->assertStringContainsString('const selisih = fisik - sistem;', $script);
    }

    public function test_case_3_when_stok_fisik_is_higher_difference_is_positive(): void
    {
        $this->actingAsGudang();

        $barang = Barang::factory()->create([
            'stok' => 10,
        ]);

        $response = $this->postJson('/api/stok_opname/simpan', [
            'items' => [
                [
                    'id_barang' => $barang->id,
                    'stok_fisik' => 14,
                    'keterangan' => 'Ada tambahan barang di rak',
                ],
            ],
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('stok_opnames', [
            'barang_id' => $barang->id,
            'stok_sistem' => 10,
            'stok_fisik' => 14,
            'selisih' => 4,
        ]);

        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
            'stok' => 14,
        ]);
    }

    public function test_case_4_when_stok_fisik_is_lower_difference_is_negative(): void
    {
        $this->actingAsGudang();

        $barang = Barang::factory()->create([
            'stok' => 10,
        ]);

        $response = $this->postJson('/api/stok_opname/simpan', [
            'items' => [
                [
                    'id_barang' => $barang->id,
                    'stok_fisik' => 7,
                    'keterangan' => 'Barang rusak',
                ],
            ],
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('stok_opnames', [
            'barang_id' => $barang->id,
            'stok_sistem' => 10,
            'stok_fisik' => 7,
            'selisih' => -3,
        ]);

        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
            'stok' => 7,
        ]);
    }

    public function test_case_5_notes_are_saved_for_items_with_stock_difference(): void
    {
        $this->actingAsGudang();

        $barang = Barang::factory()->create([
            'stok' => 30,
        ]);

        $response = $this->postJson('/api/stok_opname/simpan', [
            'items' => [
                [
                    'id_barang' => $barang->id,
                    'stok_fisik' => 25,
                    'keterangan' => 'Kehilangan saat display',
                ],
            ],
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('stok_opnames', [
            'barang_id' => $barang->id,
            'keterangan' => 'Kehilangan saat display',
            'selisih' => -5,
        ]);
    }

    public function test_case_6_save_button_process_text_and_success_message_contract_exist(): void
    {
        $this->actingAsGudang();

        $script = File::get(resource_path('js/stok_opname.js'));

        $this->assertStringContainsString('saveButton.innerText = "Memproses...";', $script);
        $this->assertStringContainsString('Hasil stok opname berhasil disimpan. Stok produk telah diperbarui.', $script);
        $this->assertStringContainsString('saveButton.innerText = "Simpan Hasil Opname Halaman Ini";', $script);
    }

    public function test_case_7_cancel_on_confirmation_stops_save_flow_contract_exist(): void
    {
        $this->actingAsGudang();

        $script = File::get(resource_path('js/stok_opname.js'));
        $viewSource = File::get(resource_path('views/stok_opname.blade.php'));

        $this->assertStringContainsString('const confirmSave = await showConfirm(', $script);
        $this->assertStringContainsString('if (!confirmSave) {', $script);
        $this->assertStringContainsString('return;', $script);
        $this->assertStringContainsString('resolveOpnameConfirm(false)', $viewSource);
    }

    public function test_case_8_confirm_on_modal_continues_save_flow_via_ajax_contract_exist(): void
    {
        $this->actingAsGudang();

        $script = File::get(resource_path('js/stok_opname.js'));
        $viewSource = File::get(resource_path('views/stok_opname.blade.php'));

        $this->assertStringContainsString('resolveOpnameConfirm(true)', $viewSource);
        $this->assertStringContainsString("method: 'POST'", $script);
        $this->assertStringContainsString("fetch('/api/stok_opname/simpan'", $script);
        $this->assertStringContainsString('saveButton.innerText = "Memproses...";', $script);
    }

    public function test_case_9_successful_save_updates_stock_and_returns_success_response(): void
    {
        $this->actingAsGudang();

        $barang = Barang::factory()->create([
            'stok' => 50,
        ]);

        $response = $this->postJson('/api/stok_opname/simpan', [
            'items' => [
                [
                    'id_barang' => $barang->id,
                    'stok_fisik' => 47,
                    'keterangan' => 'Selisih audit harian',
                ],
            ],
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
            'message' => 'Stok berhasil diperbarui sesuai hasil fisik!',
        ]);

        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
            'stok' => 47,
        ]);

        $this->assertDatabaseHas('stok_opnames', [
            'barang_id' => $barang->id,
            'selisih' => -3,
            'keterangan' => 'Selisih audit harian',
        ]);
    }

    public function test_case_10_open_history_modal_loads_history_data_from_server(): void
    {
        $user = $this->actingAsGudang();

        $barang = Barang::factory()->create([
            'kode_barang' => 'OPN-HIS-01',
            'nama_barang' => 'Sarden Kaleng',
        ]);

        StokOpnameModel::factory()->create([
            'barang_id' => $barang->id,
            'user_id' => $user->id,
            'stok_sistem' => 22,
            'stok_fisik' => 20,
            'selisih' => -2,
            'keterangan' => 'Rusak kemasan',
        ]);

        $response = $this->getJson('/api/stok_opname/history?limit=100');
        $response->assertOk();

        $response->assertJsonStructure([
            'items' => [
                '*' => [
                    'id',
                    'kode_barang',
                    'nama_barang',
                    'diubah_oleh',
                    'stok_sistem',
                    'stok_fisik',
                    'selisih',
                    'keterangan',
                    'waktu',
                ],
            ],
        ]);

        $response->assertJsonFragment([
            'kode_barang' => 'OPN-HIS-01',
            'nama_barang' => 'Sarden Kaleng',
            'diubah_oleh' => $user->name,
            'stok_sistem' => 22,
            'stok_fisik' => 20,
            'selisih' => -2,
            'keterangan' => 'Rusak kemasan',
        ]);

        $script = File::get(resource_path('js/stok_opname.js'));
        $this->assertStringContainsString('window.openHistoryModal = async function()', $script);
        $this->assertStringContainsString("fetch('/api/stok_opname/history?limit=100')", $script);
    }
}
