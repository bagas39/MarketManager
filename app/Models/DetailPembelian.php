<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPembelian extends Model
{
    /** @use HasFactory<\Database\Factories\DetailPembelianFactory> */
    use HasFactory;


    protected $fillable = [
        'pembelian_id',
        'barang_id',
        'harga_beli',
        'kuantitas',
        'subtotal'
    ];

    public function barang() {
        return $this->belongsTo(Barang::class);
    }

    public function pembelian() {
        return $this->belongsTo(Pembelian::class);
    }
    
}
