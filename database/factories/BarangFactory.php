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
        $commonNames = [
            'sapu', 'pel', 'mie instan', 'beras', 'gula', 'garam', 'minyak goreng', 'sabun mandi',
            'shampo', 'sikat gigi', 'odol', 'susu', 'telur', 'kopi', 'teh', 'kerupuk', 'tempe', 'tahu',
            'beras merah', 'tepung', 'kaleng sarden', 'kertas tisu', 'pembersih lantai', 'pasta gigi'
        ];

        $nama = $this->faker->randomElement($commonNames);

        $map = [
            'sapu' => 'Kebutuhan Rumah Tangga',
            'pel' => 'Kebutuhan Rumah Tangga',
            'mie instan' => 'Makanan',
            'beras' => 'Sembako',
            'beras merah' => 'Sembako',
            'gula' => 'Sembako',
            'garam' => 'Sembako',
            'minyak goreng' => 'Sembako',
            'sabun mandi' => 'Kebutuhan Rumah Tangga',
            'shampo' => 'Kebutuhan Rumah Tangga',
            'sikat gigi' => 'Kebutuhan Rumah Tangga',
            'odol' => 'Kebutuhan Rumah Tangga',
            'susu' => 'Minuman',
            'telur' => 'Makanan',
            'kopi' => 'Minuman',
            'teh' => 'Minuman',
            'kerupuk' => 'Snack',
            'tempe' => 'Makanan',
            'tahu' => 'Makanan',
            'tepung' => 'Sembako',
            'kaleng sarden' => 'Makanan',
            'kertas tisu' => 'Kebutuhan Rumah Tangga',
            'pembersih lantai' => 'Kebutuhan Rumah Tangga',
            'pasta gigi' => 'Kebutuhan Rumah Tangga',
        ];

        $lower = strtolower($nama);
        if (array_key_exists($lower, $map)) {
            $kategori = $map[$lower];
        }

        return [
            'kode_barang' => 'BRG-' . now()->format('Ymd') . '-' . str_pad(self::$urutan++, 4, '0', STR_PAD_LEFT),
            'nama_barang' => ucfirst($nama),
            'kategori' => $kategori,
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaBeli + ($hargaBeli * 0.2),
            'stok' => $this->faker->numberBetween(20, 150),
        ];
    }
}
