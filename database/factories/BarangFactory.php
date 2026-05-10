<?php

namespace Database\Factories;

use App\Models\Barang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Barang>
 */
class BarangFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Harga beli kelipatan 1000 antara 5.000 - 50.000
        $hargaBeli = $this->faker->numberBetween(5, 50) * 1000; 
        
        return [
            'kode_barang' => 'BRG-' . $this->faker->unique()->numerify('####'),
            'nama_barang' => ucfirst($this->faker->words(2, true)),
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaBeli + ($hargaBeli * 0.2), // Untung 20%
            'stok' => $this->faker->numberBetween(20, 150),
        ];
    }
}
