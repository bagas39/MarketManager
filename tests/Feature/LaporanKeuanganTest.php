<?php

namespace Tests\Feature;

use App\Models\Pembelian;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\InteractsWithRoles;
use Tests\TestCase;

class LaporanKeuanganTest extends TestCase
{
    use RefreshDatabase, InteractsWithRoles;

    private function seedFinancialRow(Carbon $date, array $overrides = []): void
    {
        $user = User::factory()->create([
            'role' => 'Kasir',
        ]);

        $pembelianUser = User::factory()->create([
            'role' => 'Gudang',
        ]);

        $transaksi = Transaksi::factory()->create(array_merge([
            'user_id' => $user->id,
            'total_harga' => 150000,
            'tanggal' => $date->format('Y-m-d'),
        ], $overrides['transaksi'] ?? []));

        $pembelian = Pembelian::factory()->create(array_merge([
            'user_id' => $pembelianUser->id,
            'total_biaya' => 50000,
            'tanggal' => $date->format('Y-m-d'),
            'nama_supplier' => 'PT Supplier Utama',
        ], $overrides['pembelian'] ?? []));

        DB::table('transaksis')->where('id', $transaksi->id)->update([
            'created_at' => $date->copy()->setTime(10, 0, 0),
            'updated_at' => $date->copy()->setTime(10, 0, 0),
        ]);

        DB::table('pembelians')->where('id', $pembelian->id)->update([
            'created_at' => $date->copy()->setTime(11, 0, 0),
            'updated_at' => $date->copy()->setTime(11, 0, 0),
        ]);
    }

    public function test_case_1_filter_button_without_date_shows_validation_message_contract(): void
    {
        $this->actingAsOwner();

        $page = $this->get('/laporan_keuangan');
        $page->assertOk();
        $page->assertSee('Cari Laporan');
        $page->assertSee('Download PDF');

        $script = File::get(resource_path('js/laporan_keuangan.js'));
        $this->assertStringContainsString('Harap pilih kedua tanggal!', $script);
        $this->assertStringContainsString("if (!start || !end) {", $script);
    }

    public function test_case_2_valid_date_filter_updates_summary_and_detail_table(): void
    {
        $this->actingAsOwner();

        $this->seedFinancialRow(Carbon::parse('2026-05-20'));
        $this->seedFinancialRow(Carbon::parse('2026-05-25'), [
            'transaksi' => ['total_harga' => 200000],
            'pembelian' => ['total_biaya' => 75000, 'nama_supplier' => 'PT Mitra Dagang'],
        ]);
        $this->seedFinancialRow(Carbon::parse('2026-04-15'), [
            'transaksi' => ['total_harga' => 999000],
            'pembelian' => ['total_biaya' => 100000, 'nama_supplier' => 'PT Lama Sekali'],
        ]);

        $response = $this->getJson('/api/laporan_keuangan?start_date=2026-05-01&end_date=2026-05-31');
        $response->assertOk();

        $response->assertJson([
            'ringkasan' => [
                'total_masuk' => 350000,
                'total_keluar' => 125000,
                'saldo_akhir' => 225000,
            ],
        ]);

        $this->assertGreaterThanOrEqual(4, count($response->json('detail')));
        $types = array_column($response->json('detail'), 'tipe');
        $this->assertContains('Masuk', $types);
        $this->assertContains('Keluar', $types);
    }

    public function test_case_3_filter_without_transactions_returns_zero_and_empty_state(): void
    {
        $this->actingAsOwner();

        $this->seedFinancialRow(Carbon::parse('2026-05-10'));

        $response = $this->getJson('/api/laporan_keuangan?start_date=2026-06-01&end_date=2026-06-30');
        $response->assertOk();

        $response->assertJson([
            'ringkasan' => [
                'total_masuk' => 0,
                'total_keluar' => 0,
                'saldo_akhir' => 0,
            ],
        ]);

        $this->assertCount(0, $response->json('detail'));

        $script = File::get(resource_path('js/laporan_keuangan.js'));
        $this->assertStringContainsString('Tidak ada data untuk periode ini', $script);
    }

    public function test_case_4_export_pdf_downloads_report_for_current_period(): void
    {
        $this->actingAsOwner();

        $this->seedFinancialRow(Carbon::parse('2026-05-20'));

        $response = $this->get('/laporan_keuangan/export-pdf?start_date=2026-05-01&end_date=2026-05-31');

        $response->assertOk();
        $response->assertHeader('content-type');
        $response->assertHeader('content-disposition');

        $contentDisposition = $response->headers->get('content-disposition');
        $this->assertNotNull($contentDisposition);
        $this->assertStringContainsString('attachment', strtolower($contentDisposition));
        $this->assertStringContainsString('Laporan_Keuangan_Swalayan_Segar.pdf', $contentDisposition);

        $script = File::get(resource_path('js/laporan_keuangan.js'));
        $this->assertStringContainsString("window.open(url, '_blank');", $script);
        $this->assertStringContainsString("let url = '/laporan_keuangan/export-pdf';", $script);
    }
}
