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
        $kuantitas = $this->faker->numberBetween(10, 50);
        $hargaBeli = $this->faker->numberBetween(5, 50) * 1000;

        return [
            'pembelian_id' => Pembelian::factory(),
            'barang_id' => Barang::factory(),
            'harga_beli' => $hargaBeli, 
            'kuantitas' => $kuantitas,
            'subtotal' => $kuantitas * $hargaBeli, 
        ];
    }
}
