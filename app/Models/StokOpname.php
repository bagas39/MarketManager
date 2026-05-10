<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOpname extends Model
{
    /** @use HasFactory<\Database\Factories\StokOpnameFactory> */
    use HasFactory;

    protected $fillable = [
        'barang_id',
        'user_id',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'keterangan'
    ];

    public function barang() {
        return $this->belongsTo(Barang::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

}
