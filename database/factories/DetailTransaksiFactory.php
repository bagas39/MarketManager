<?php

namespace Database\Factories;

use App\Models\DetailTransaksi;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Barang;
use App\Models\Transaksi;

/**
 * @extends Factory<DetailTransaksi>
 */
class DetailTransaksiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaksi_id' => Transaksi::factory(),
            'barang_id' => Barang::factory(),
            'kuantitas' => $this->faker->numberBetween(1, 5),
            'subtotal' => 0, 
        ];
    }
}
