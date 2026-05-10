<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    /** @use HasFactory<\Database\Factories\TransaksiFactory> */
    use HasFactory;

    protected $fillable = [
        'no_transaksi',
        'user_id',
        'total_harga',
        'tanggal'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function detailTransaksis() {
        return $this->hasMany(DetailTransaksi::class);
    }

}
