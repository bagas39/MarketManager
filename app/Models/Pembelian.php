<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    /** @use HasFactory<\Database\Factories\PembelianFactory> */
    use HasFactory;

    protected $fillable = [
        'no_pembelian',
        'nama_supplier',
        'user_id',
        'total_biaya',
        'tanggal'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function detailPembelians() {
        return $this->hasMany(DetailPembelian::class);
    }
    
}
