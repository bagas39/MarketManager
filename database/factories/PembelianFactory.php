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
    protected static $urutan = 1;

    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(100000, 1000000);

        return [
            // Format rapi dan urut!
            'no_pembelian' => 'PO-' . now()->format('Ymd') . '-' . str_pad(self::$urutan++, 4, '0', STR_PAD_LEFT),
            'nama_supplier' => $this->faker->company(),
            'user_id' => User::factory(),
            'total_biaya' => $subtotal,
            'tanggal' => $this->faker->dateTimeBetween('-2 months', '-1 month')->format('Y-m-d'),
        ];
    }
}
