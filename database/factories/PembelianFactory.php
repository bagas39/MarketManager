<?php

namespace Database\Factories;

use App\Models\Pembelian;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pembelian>
 */
class PembelianFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_pembelian' => 'PO-' . $this->faker->unique()->numerify('#####'),
            'nama_supplier' => $this->faker->company(),
            'user_id' => User::factory(),
            'total_biaya' => 0,
            'tanggal' => $this->faker->dateTimeBetween('-2 months', '-1 month')->format('Y-m-d'),
        ];
    }
}
