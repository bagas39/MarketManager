<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KasirController; 
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\PenggunaController;
use App\Http\Middleware\CekLogin;


// Auth Routes
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);
Route::get('/api/me', function () {
    if (!session()->has('user_role')) return response()->json(null);
    return response()->json([
        'nama' => session('user_name'),
        'role' => session('user_role')
    ]);
});

Route::middleware([CekLogin::class])->group(function () {

// Kasir Routes
Route::get('/', [KasirController::class, 'index']); 
Route::get('/api/barang', [BarangController::class, 'getBarang']);
Route::post('/api/transaksi', [KasirController::class, 'storeTransaksi']);

// Transaksi Pembelian Routes
Route::get('/transaksi_pembelian', [PembelianController::class, 'index']);
Route::post('/pembelian/store', [PembelianController::class, 'store']);
Route::get('/pembelian/history', [PembelianController::class, 'history']);

// Manajemen Stok Routes
Route::get('/manajemen_stok', [StokController::class, 'index']);
Route::get('/api/manajemen_stok', [BarangController::class, 'listStok']);

// Transaksi Penjualan Routes
Route::view('/transaksi_penjualan', 'transaksi_penjualan');
Route::get('/api/transaksi_penjualan', [TransaksiController::class, 'list']);
Route::get('/api/transaksi_detail/{id}', [TransaksiController::class, 'detail']);
Route::put('/api/transaksi/{id}', [TransaksiController::class, 'edit']);

// Manajemen Pengguna Routes
Route::get('/manajemen_pengguna', [PenggunaController::class, 'index']);
Route::get('/api/users', [PenggunaController::class, 'listUsers']);
Route::post('/api/users', [PenggunaController::class, 'store']);
Route::put('/api/users/{id}', [PenggunaController::class, 'update']);
Route::delete('/api/users/{id}', [PenggunaController::class, 'destroy']);

// Laporan Keuangan Routes

// Prediksi Stok

// Stok Opname

});

// static
Route::view('/welcome', 'welcome');

// flush
Route::get('/flush', function () {
    \Illuminate\Support\Facades\Session::flush();
    return redirect('/login')->with('error', 'berhasil reset session');
});