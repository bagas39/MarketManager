<?php

namespace Database\Factories;

use App\Models\DetailPembelian;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Barang;
use App\Models\Pembelian;

/**
 * @extends Factory<DetailPembelian>
 */
class DetailPembelianFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pembelian_id' => Pembelian::factory(),
            'barang_id' => Barang::factory(),
            'harga_beli' => 0, 
            'kuantitas' => $this->faker->numberBetween(10, 50),
            'subtotal' => 0, 
        ];
    }
}
