<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    private function registerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Pegawai Baru',
            'email' => 'pegawai@swalayan.com',
            'role' => 'Kasir',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_case_1_empty_full_name_shows_required_message(): void
    {
        $response = $this->from('/register')->post('/register', $this->registerPayload([
            'name' => '',
        ]));

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'name' => 'Nama lengkap wajib diisi!',
        ]);
    }

    public function test_case_2_duplicate_email_shows_error(): void
    {
        User::factory()->create([
            'name' => 'Admin Lama',
            'email' => 'duplikat@swalayan.com',
            'password' => Hash::make('password123'),
            'role' => 'Kasir',
        ]);

        $response = $this->from('/register')->post('/register', $this->registerPayload([
            'email' => 'duplikat@swalayan.com',
            'role' => 'Gudang',
        ]));

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'email' => 'Email sudah terdaftar, gunakan yang lain!',
        ]);
    }

    public function test_case_3_password_too_short_shows_min_length_message(): void
    {
        $response = $this->from('/register')->post('/register', $this->registerPayload([
            'email' => 'baru@swalayan.com',
            'password' => '123',
            'password_confirmation' => '123',
        ]));

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'password' => 'Password minimal 6 karakter!',
        ]);
    }

    public function test_case_4_password_confirmation_mismatch_shows_error(): void
    {
        $response = $this->from('/register')->post('/register', $this->registerPayload([
            'email' => 'konfirmasi@swalayan.com',
            'role' => 'Gudang',
            'password_confirmation' => 'password456',
        ]));

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'password' => 'Konfirmasi password tidak cocok!',
        ]);
    }

    public function test_case_5_valid_registration_creates_user_and_redirects_to_login(): void
    {
        $response = $this->from('/register')->post('/register', $this->registerPayload([
            'email' => 'valid@swalayan.com',
            'role' => 'Gudang',
        ]));

        $response->assertRedirect('/login');
        $response->assertSessionHas('success', 'Registrasi berhasil! Silakan login.');

        $this->assertDatabaseHas('users', [
            'name' => 'Pegawai Baru',
            'email' => 'valid@swalayan.com',
            'role' => 'Gudang',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'valid@swalayan.com',
            'password' => 'password123',
        ]);
    }
}
