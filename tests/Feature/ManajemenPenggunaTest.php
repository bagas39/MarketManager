<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Concerns\InteractsWithRoles;
use Tests\TestCase;

class ManajemenPenggunaTest extends TestCase
{
    use RefreshDatabase, InteractsWithRoles;

    public function test_case_1_displays_users_table_with_roles(): void
    {
        $this->actingAsOwner();

        $userA = User::factory()->create([
            'name' => 'Kasir Satu',
            'email' => 'kasir1@example.com',
            'role' => 'Kasir',
        ]);

        $userB = User::factory()->create([
            'name' => 'Supervisor Satu',
            'email' => 'supervisor1@example.com',
            'role' => 'Supervisor',
        ]);

        $page = $this->get('/manajemen_pengguna');
        $page->assertOk();

        $response = $this->getJson('/api/users');
        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $userA->id,
            'username' => $userA->email,
            'nama' => $userA->name,
            'role' => $userA->role,
        ]);

        $response->assertJsonFragment([
            'id' => $userB->id,
            'username' => $userB->email,
            'nama' => $userB->name,
            'role' => $userB->role,
        ]);
    }

    public function test_case_2_add_user_modal_with_valid_data_creates_user(): void
    {
        $this->actingAsOwner();

        $payload = [
            'username' => 'pegawai.baru@example.com',
            'nama' => 'Pegawai Baru',
            'password' => 'secret123',
            'role' => 'Gudang',
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'User berhasil ditambahkan!',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'pegawai.baru@example.com',
            'name' => 'Pegawai Baru',
            'role' => 'Gudang',
        ]);
    }

    public function test_case_3_duplicate_email_shows_validation_error_message(): void
    {
        $this->actingAsOwner();

        User::factory()->create([
            'email' => 'duplikat@example.com',
        ]);

        $response = $this->postJson('/api/users', [
            'username' => 'duplikat@example.com',
            'nama' => 'User Duplikat',
            'password' => 'secret123',
            'role' => 'Kasir',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Username/Email sudah digunakan!',
            ]);
    }

    public function test_case_4_edit_user_with_empty_password_keeps_old_password(): void
    {
        $this->actingAsOwner();

        $user = User::factory()->create([
            'name' => 'Nama Lama',
            'email' => 'lama@example.com',
            'password' => bcrypt('passwordlama'),
            'role' => 'Kasir',
        ]);

        $oldPasswordHash = $user->password;

        $response = $this->putJson('/api/users/' . $user->id, [
            'username' => 'baru@example.com',
            'nama' => 'Nama Baru',
            'password' => '',
            'role' => 'Supervisor',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'User berhasil diperbarui!',
            ]);

        $user->refresh();

        $this->assertSame('baru@example.com', $user->email);
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('Supervisor', $user->role);
        $this->assertSame($oldPasswordHash, $user->password);
        $this->assertTrue(Hash::check('passwordlama', $user->password));
    }

    public function test_case_5_delete_user_flow_has_confirmation_and_removes_user(): void
    {
        $this->actingAsOwner();

        $targetUser = User::factory()->create([
            'email' => 'hapus@example.com',
        ]);

        $script = File::get(resource_path('js/pengguna.js'));
        $this->assertStringContainsString('confirm(`Yakin ingin menghapus user', $script);

        $response = $this->deleteJson('/api/users/' . $targetUser->id);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'User berhasil dihapus!',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);
    }
}
