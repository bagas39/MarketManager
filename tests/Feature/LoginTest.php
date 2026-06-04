<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_case_1_empty_email_and_password_shows_required_messages(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'Email wajib diisi!',
            'password' => 'Password wajib diisi!',
        ]);
    }

    public function test_case_2_invalid_email_format_shows_format_error(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'salah-format',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'Format email tidak valid!',
        ]);
    }

    public function test_case_3_wrong_password_shows_error_and_denies_access(): void
    {
        $user = User::factory()->create([
            'email' => 'kasir@swalayan.com',
            'password' => Hash::make('password-benar'),
            'role' => 'Kasir',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password-salah',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'Email atau Password salah!');
        $this->assertGuest();
    }

    public function test_case_4_successful_login_redirects_by_role(): void
    {
        $cases = [
            ['role' => 'Gudang', 'expected' => '/manajemen_stok'],
            ['role' => 'Owner', 'expected' => '/laporan_keuangan'],
            ['role' => 'Supervisor', 'expected' => '/transaksi_penjualan'],
        ];

        foreach ($cases as $case) {
            $user = User::factory()->create([
                'email' => strtolower($case['role']) . '@swalayan.com',
                'password' => Hash::make('password123'),
                'role' => $case['role'],
            ]);

            $response = $this->from('/login')->post('/login', [
                'email' => $user->email,
                'password' => 'password123',
            ]);

            $response->assertRedirect($case['expected']);
            $this->assertAuthenticatedAs($user);
            $this->app['auth']->logout();
            $this->app['session']->invalidate();
            $this->app['session']->regenerateToken();
        }
    }
}
