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
    protected static $urutan = 1;

    public function definition(): array
    {
        $hargaBeli = $this->faker->numberBetween(5, 50) * 1000; 
        $kategori = $this->faker->randomElement([
            'Makanan',
            'Minuman',
            'Sembako',
            'Snack',
            'Kebutuhan Rumah Tangga',
        ]);
        
        return [
            // Format rapi dan urut!
            'kode_barang' => 'BRG-' . now()->format('Ymd') . '-' . str_pad(self::$urutan++, 4, '0', STR_PAD_LEFT),
            'nama_barang' => ucfirst($this->faker->words(2, true)),
            'kategori' => $kategori,
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaBeli + ($hargaBeli * 0.2),
            'stok' => $this->faker->numberBetween(20, 150),
        ];
    }
}
