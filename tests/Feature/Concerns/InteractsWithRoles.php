<?php

namespace Tests\Feature\Concerns;

use App\Models\User;

trait InteractsWithRoles
{
    protected function actingAsRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => $role,
        ], $attributes));

        $this->actingAs($user);

        return $user;
    }

    protected function actingAsKasir(array $attributes = []): User
    {
        return $this->actingAsRole('Kasir', $attributes);
    }

    protected function actingAsGudang(array $attributes = []): User
    {
        return $this->actingAsRole('Gudang', $attributes);
    }

    protected function actingAsSupervisor(array $attributes = []): User
    {
        return $this->actingAsRole('Supervisor', $attributes);
    }

    protected function actingAsOwner(array $attributes = []): User
    {
        return $this->actingAsRole('Owner', $attributes);
    }
}
