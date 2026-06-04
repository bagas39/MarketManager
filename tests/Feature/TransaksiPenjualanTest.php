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

class TransaksiPenjualanTest extends TestCase
{
    use RefreshDatabase, InteractsWithRoles;

    public function test_case_1_lists_and_filters_sales_history_by_transaction_id(): void
    {
        $this->actingAsSupervisor();

        $user = User::factory()->create([
            'role' => 'Kasir',
        ]);

        $target = Transaksi::factory()->create([
            'no_transaksi' => 'TRX-20260529-0001',
            'user_id' => $user->id,
            'total_harga' => 111000,
            'tanggal' => now()->format('Y-m-d'),
        ]);

        Transaksi::factory()->create([
            'no_transaksi' => 'TRX-20260529-0002',
            'user_id' => $user->id,
            'total_harga' => 222000,
            'tanggal' => now()->format('Y-m-d'),
        ]);

        $page = $this->get('/transaksi_penjualan');
        $page->assertOk();
        $page->assertSee('Cari ID Transaksi...');

        $response = $this->getJson('/api/transaksi_penjualan?search_id=0001&limit=15');
        $response->assertOk();
        $response->assertJsonStructure([
            'transactions' => [
                '*' => [
                    'id_penjualan',
                    'id',
                    'nama_kasir',
                    'tanggal_penjualan',
                    'total_harga',
                ],
            ],
            'totalAvailableTransactions',
        ]);

        $transactions = $response->json('transactions');
        $this->assertCount(1, $transactions);
        $this->assertSame($target->no_transaksi, $transactions[0]['id_penjualan']);
        $this->assertEquals(111000, (float) $transactions[0]['total_harga']);

        $script = File::get(resource_path('js/penjualan.js'));
        $this->assertStringContainsString("searchBtn?.addEventListener('click', () => loadSales(1));", $script);
        $this->assertStringContainsString("searchInput?.addEventListener('keyup', (e) => { if (e.key === 'Enter') loadSales(1); });", $script);
        $this->assertStringContainsString("nextButton?.addEventListener('click', () => {", $script);
    }

    public function test_case_2_view_detail_modal_shows_comprehensive_transaction_detail(): void
    {
        $this->actingAsSupervisor();

        $kasir = User::factory()->create([
            'role' => 'Kasir',
            'name' => 'Kasir A',
        ]);

        $barang = Barang::factory()->create([
            'nama_barang' => 'Indomie Goreng',
            'harga_jual' => 5000,
            'stok' => 20,
        ]);

        $transaksi = Transaksi::factory()->create([
            'no_transaksi' => 'TRX-20260529-0100',
            'user_id' => $kasir->id,
            'total_harga' => 11100,
            'tanggal' => now()->format('Y-m-d'),
        ]);

        DetailTransaksi::factory()->create([
            'transaksi_id' => $transaksi->id,
            'barang_id' => $barang->id,
            'kuantitas' => 2,
            'subtotal' => 10000,
        ]);

        $response = $this->getJson('/api/transaksi_detail/TRX-20260529-0100');
        $response->assertOk()
            ->assertJson([
                'header' => [
                    'id_penjualan' => 'TRX-20260529-0100',
                    'nama_kasir' => 'Kasir A',
                ],
                'summary' => [
                    'total' => 11100,
                ],
            ]);

        $response->assertJsonFragment([
            'id_barang' => $barang->id,
            'nama_barang' => 'Indomie Goreng',
            'jumlah' => 2,
            'subtotal' => 10000,
        ]);

        $script = File::get(resource_path('js/penjualan.js'));
        $this->assertStringContainsString("window.viewDetail = async function(id)", $script);
        $this->assertStringContainsString('fetch(`/api/transaksi_detail/${id}`)', $script);
        $this->assertStringContainsString('Detail Transaksi', $script);
        $this->assertStringContainsString('Total Bayar', $script);
    }

    public function test_case_3_edit_transaction_form_loads_old_items_and_recalculates_totals_live(): void
    {
        $this->actingAsSupervisor();

        $barang = Barang::factory()->create([
            'nama_barang' => 'Susu UHT',
            'harga_jual' => 8000,
            'stok' => 30,
        ]);

        $transaksi = Transaksi::factory()->create([
            'no_transaksi' => 'TRX-20260529-0200',
            'total_harga' => 8880,
            'tanggal' => now()->format('Y-m-d'),
        ]);

        $detail = DetailTransaksi::factory()->create([
            'transaksi_id' => $transaksi->id,
            'barang_id' => $barang->id,
            'kuantitas' => 1,
            'subtotal' => 8000,
        ]);

        $response = $this->getJson('/api/transaksi_detail/TRX-20260529-0200');
        $response->assertOk();
        $response->assertJsonFragment([
            'id_barang' => $barang->id,
            'nama_barang' => 'Susu UHT',
            'jumlah' => 1,
            'subtotal' => 8000,
        ]);

        $script = File::get(resource_path('js/penjualan.js'));
        $this->assertStringContainsString('window.openEditModal = async function(id)', $script);
        $this->assertStringContainsString('currentEditItems = data.items.map(i => ({', $script);
        $this->assertStringContainsString('window.renderEditItems = function()', $script);
        $this->assertStringContainsString('window.updateItemQty = function(index, val)', $script);
        $this->assertStringContainsString('window.addNewItemToEdit = function()', $script);
        $this->assertStringContainsString('document.getElementById(\'edit-total-display\').textContent = formatIDR(total);', $script);
    }

    public function test_case_4_saving_edited_transaction_updates_stock_and_total_with_db_transaction(): void
    {
        $this->actingAsSupervisor();

        $barangLama = Barang::factory()->create([
            'nama_barang' => 'Biskuit',
            'harga_jual' => 6000,
            'stok' => 10,
        ]);

        $barangBaru = Barang::factory()->create([
            'nama_barang' => 'Teh Botol',
            'harga_jual' => 4000,
            'stok' => 20,
        ]);

        $transaksi = Transaksi::factory()->create([
            'no_transaksi' => 'TRX-20260529-0300',
            'total_harga' => 6660,
            'tanggal' => now()->format('Y-m-d'),
        ]);

        DetailTransaksi::factory()->create([
            'transaksi_id' => $transaksi->id,
            'barang_id' => $barangLama->id,
            'kuantitas' => 1,
            'subtotal' => 6000,
        ]);

        $response = $this->putJson('/api/transaksi/TRX-20260529-0300', [
            'items' => [
                [
                    'id_barang' => $barangLama->id,
                    'jumlah' => 2,
                ],
                [
                    'id_barang' => $barangBaru->id,
                    'jumlah' => 1,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Transaksi berhasil diperbarui!',
            ]);

        $transaksi->refresh();
        $this->assertEquals(17760.0, (float) $transaksi->total_harga);

        $this->assertDatabaseHas('barangs', [
            'id' => $barangLama->id,
            'stok' => 9,
        ]);

        $this->assertDatabaseHas('barangs', [
            'id' => $barangBaru->id,
            'stok' => 19,
        ]);

        $this->assertDatabaseHas('detail_transaksis', [
            'transaksi_id' => $transaksi->id,
            'barang_id' => $barangLama->id,
            'kuantitas' => 2,
            'subtotal' => 12000,
        ]);

        $this->assertDatabaseHas('detail_transaksis', [
            'transaksi_id' => $transaksi->id,
            'barang_id' => $barangBaru->id,
            'kuantitas' => 1,
            'subtotal' => 4000,
        ]);

        $script = File::get(resource_path('js/penjualan.js'));
        $this->assertStringContainsString('window.saveEditTransaction = async function()', $script);
        $this->assertStringContainsString('fetch(`/api/transaksi/${id}`', $script);
        $this->assertStringContainsString('btn.textContent = "Menyimpan...";', $script);
        $this->assertStringContainsString('alert("Berhasil memperbarui transaksi!")', $script);
    }
}
