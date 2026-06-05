<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PrediksiStokController;
use App\Http\Controllers\StokOpnameController;
use App\Http\Controllers\XenditController;

// 1. AREA GUEST

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Rute Register
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});


// 2. AREA AUTH

Route::middleware('auth')->group(function () {

    // Rute Umum
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/api/me', function () {
        return response()->json(['nama' => Auth::user()->name, 'role' => Auth::user()->role]);
    });

    //  KASIR ( Kasir, Supervisor)
    Route::middleware('role:Kasir,Supervisor')->group(function () {
        Route::get('/', [KasirController::class, 'index']);
        Route::get('/api/barang', [BarangController::class, 'getBarang']);
        Route::post('/api/transaksi', [KasirController::class, 'storeTransaksi']);
    });

    //  TRANSAKSI PENJUALAN ( Supervisor)
    Route::middleware('role:Supervisor')->group(function () {
        Route::view('/transaksi_penjualan', 'transaksi_penjualan');
        Route::get('/api/transaksi_penjualan', [TransaksiController::class, 'list']);
        Route::get('/api/transaksi_detail/{id}', [TransaksiController::class, 'detail']);
        Route::put('/api/transaksi/{id}', [TransaksiController::class, 'edit']);
    });

    //  MANAJEMEN STOK ( Kasir, Gudang, Supervisor)
    Route::middleware('role:Kasir,Gudang,Supervisor')->group(function () {
        Route::get('/manajemen_stok', [StokController::class, 'index']);
        Route::get('/api/manajemen_stok', [BarangController::class, 'listStok']);
        Route::delete('/api/barang/{id}', [BarangController::class, 'destroy']);
    });

    //  TRANSAKSI PEMBELIAN ( Gudang, Supervisor)
    Route::middleware('role:Gudang,Supervisor')->group(function () {
        Route::get('/transaksi_pembelian', [PembelianController::class, 'index']);
        Route::post('/pembelian/store', [PembelianController::class, 'store']);
        Route::get('/pembelian/history', [PembelianController::class, 'history']);
        Route::get('/pembelian/{no_pembelian}/detail', [PembelianController::class, 'detail']);
        Route::delete('/pembelian/{no_pembelian}', [PembelianController::class, 'destroy']);
    });

    //  STOK OPNAME ( Gudang, Supervisor)
    Route::middleware('role:Gudang,Supervisor')->group(function () {
        Route::get('/stok_opname', [StokOpnameController::class, 'index']);
        Route::get('/api/stok_opname/data', [StokOpnameController::class, 'data']);
        Route::get('/api/stok_opname/history', [StokOpnameController::class, 'history']);
        Route::post('/api/stok_opname/simpan', [StokOpnameController::class, 'simpan']);
    });

    //  PREDIKSI STOK ( Gudang, Supervisor)
    Route::middleware('role:Gudang,Supervisor')->group(function () {
        Route::get('/prediksi_stok', [PrediksiStokController::class, 'index']);
        Route::get('/api/prediksi_stok/barang', [PrediksiStokController::class, 'getBarang']);
        Route::post('/api/prediksi_stok/stok', [PrediksiStokController::class, 'hitung']);
    });

    //  MANAJEMEN PENGGUNA ( Owner, Supervisor)
    Route::middleware('role:Owner,Supervisor')->group(function () {
        Route::get('/manajemen_pengguna', [PenggunaController::class, 'index']);
        Route::get('/api/users', [PenggunaController::class, 'listUsers']);
        Route::post('/api/users', [PenggunaController::class, 'store']);
        Route::put('/api/users/{id}', [PenggunaController::class, 'update']);
        Route::delete('/api/users/{id}', [PenggunaController::class, 'destroy']);
    });

    //  LAPORAN KEUANGAN ( Owner)
    Route::middleware('role:Owner')->group(function () {
        Route::get('/laporan_keuangan', [LaporanController::class, 'index']);
        Route::get('/api/laporan_keuangan', [LaporanController::class, 'getLaporan']);
        Route::get('/laporan_keuangan/export-pdf', [LaporanController::class, 'exportPdf']);
    });

    // XENDIT QRIS
    Route::middleware('role:Kasir,Supervisor')->group(function () {
        Route::post('/api/xendit/create-invoice', [XenditController::class, 'createInvoice']);
        Route::get('/api/xendit/qr-status/{noTransaksi}', [XenditController::class, 'checkStatus']);
        Route::delete('/api/xendit/cancel-qr/{noTransaksi}', [XenditController::class, 'cancelQr']);

    });
});

// Xendit Webhook
Route::post('/api/xendit/webhook', [XenditController::class, 'webhook']);
