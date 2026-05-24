<?php

namespace Database\Factories;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaksi>
 */
class TransaksiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected static $urutan = 1;

    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(50000, 500000);

        return [
            // Format rapi dan urut!
            'no_transaksi' => 'TRX-' . now()->format('Ymd') . '-' . str_pad(self::$urutan++, 4, '0', STR_PAD_LEFT),
            'user_id' => User::factory(), 
            'total_harga' => round($subtotal * 1.11, 2), 
            'tanggal' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
        ];
    }
}
