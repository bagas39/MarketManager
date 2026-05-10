<?php

namespace Database\Factories;

use App\Models\StokOpname;
use App\Models\Barang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StokOpname>
 */
class StokOpnameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'barang_id' => Barang::factory(),
            'user_id' => User::factory(),
            'stok_sistem' => 0,
            'stok_fisik' => 0, 
            'selisih' => 0,
            'keterangan' => $this->faker->randomElement(['Hilang', 'Rusak', 'Lebih', 'Sesuai']),
        ];
    }
}
