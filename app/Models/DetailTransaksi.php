<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class DetailTransaksi extends Model
{
    /** @use HasFactory<\Database\Factories\DetailTransaksiFactory> */
    use HasFactory;

    protected $fillable = [
        'transaksi_id',
        'barang_id',
        'kuantitas',
        'subtotal'
    ];

    public function transaksi() {
        return $this->belongsTo(Transaksi::class);
    }

    public function barang() {
        return $this->belongsTo(Barang::class);
    }

}
