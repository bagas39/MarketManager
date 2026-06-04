# Dokumentasi Project MarketManager

Dokumentasi ini berisi panduan cepat menjalankan, menguji, dan memahami fitur utama aplikasi MarketManager.

## Ringkasan
- Teknologi: Laravel (PHP), Tailwind/Vanilla JS, Fetch API
- Database: MySQL (migrations tersedia di `database/migrations`)
- Tujuan: Sistem manajemen stok, transaksi penjualan/pembelian, laporan, dan prediksi stok.

## Fitur Utama
- CRUD Barang (manajemen stok)
- Transaksi penjualan (Kasir)
- Transaksi pembelian
- Stok opname
- Manajemen pengguna (Auth + CRUD)
- API internal untuk frontend via Fetch/AJAX

## Struktur penting
- Controllers: `app/Http/Controllers`
- Models: `app/Models`
- Migrations: `database/migrations`
- Frontend JS: `resources/js` (mis. `manajemen_stok.js`, `penjualan.js`)
- Tests: `tests/Feature`, `tests/Unit`

## Instalasi & Setup (lokal, XAMPP)
1. Clone repository
2. Install dependensi PHP dan Node

```bash
composer install
npm install
```

3. Salin file `.env.example` ke `.env` dan atur koneksi database (MySQL XAMPP)
4. Generate app key

```bash
php artisan key:generate

# migrasi dan seeder
php artisan migrate --seed
```

5. Build assets (development)

```bash
npm run dev
```

6. Jalankan server (opsi)

```bash
# Opsi 1: php built-in (development cepat)
php artisan serve

# Opsi 2: via XAMPP — letakkan repo di htdocs dan akses melalui http://localhost/MarketManager/public
```

## Menjalankan Test
Project sudah menyertakan beberapa Feature test.

```bash
# Jalankan semua test
php artisan test

# Jalankan test spesifik
php artisan test --filter KasirTest
```

Simpan screenshot hasil `php artisan test` di `docs/screenshots` sebelum submit nilai.

## API Endpoints Penting (ringkas)
- GET `/api/manajemen_stok` — list stok (params: `page`, `limit`, `search_nama`, `start_date`, `end_date`)
- DELETE `/api/barang/{id}` — hapus barang
- GET `/api/barang` — list barang untuk kasir/pembelian
- POST `/api/transaksi` — simpan transaksi penjualan
- GET `/api/transaksi_penjualan` — list transaksi
- GET `/api/transaksi_detail/{id}` — detail transaksi
- POST `/pembelian/store` — simpan pembelian
- GET `/api/stok_opname/data` — data stok opname
- API users: `/api/users` (GET/POST/PUT/DELETE)

> Untuk detail lengkap, lihat controller terkait di `app/Http/Controllers`.
