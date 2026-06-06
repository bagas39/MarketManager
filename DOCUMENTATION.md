# Dokumentasi Project MarketManager

Dokumentasi ini berisi panduan cepat menjalankan, menguji, dan memahami fitur utama aplikasi MarketManager.

## Ringkasan
- Teknologi: Laravel (PHP), Tailwind CSS, Vanilla JS, Fetch API
- Database: MySQL (migrations tersedia di `database/migrations`)
- Tujuan: Sistem manajemen stok, transaksi penjualan/pembelian, laporan, prediksi stok, dan pembayaran digital via Xendit.

## Fitur Utama
- CRUD Barang (manajemen stok)
- Transaksi penjualan (Kasir) — tunai dan QRIS via Xendit
- Transaksi pembelian
- Stok opname
- Manajemen pengguna (Auth + CRUD)
- Prediksi stok
- Laporan keuangan + export PDF
- API internal untuk frontend via Fetch/AJAX

## Struktur Penting
- Controllers: `app/Http/Controllers`
- Models: `app/Models`
- Services: `app/Services` (termasuk `XenditService.php`)
- Migrations: `database/migrations`
- Frontend JS: `resources/js` (mis. `kasir.js`, `manajemen_stok.js`)
- Tests: `tests/Feature`, `tests/Unit`

## Instalasi & Setup (lokal, XAMPP)
1. Clone repository
2. Install dependensi PHP dan Node

```bash
composer install
npm install
```

3. Salin file `.env.example` ke `.env` dan atur koneksi database serta Xendit

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_pass

XENDIT_SECRET_KEY=xnd_development_...
XENDIT_WEBHOOK_TOKEN=your_webhook_token
```

4. Generate app key dan jalankan migrasi

```bash
php artisan key:generate
php artisan migrate --seed
```

5. Build assets

```bash
npm run build
```

6. Jalankan server

```bash
# Opsi 1: php artisan
php artisan serve

# Opsi 2: via XAMPP — letakkan repo di htdocs dan akses melalui http://localhost/MarketManager/public
```

## Pembayaran QRIS (Xendit)

Fitur pembayaran QRIS menggunakan Xendit Invoice API. Setelah kasir menekan **Bayar via Xendit**, sistem membuat invoice dan membuka halaman pembayaran Xendit di tab baru. Pembayaran dikonfirmasi melalui webhook.

### Setup Xendit untuk development dengan ngrok

```bash
# Terminal 1 — jalankan aplikasi
php artisan serve

# Terminal 2 — expose ke internet
ngrok http 8000
```

Daftarkan URL webhook di [dashboard.xendit.co](https://dashboard.xendit.co) → Settings → Webhooks:
```
https://xxxx.ngrok-free.app/api/xendit/webhook
```

Update `.env`:
```env
APP_URL=https://xxxx.ngrok-free.app
XENDIT_WEBHOOK_TOKEN=token_dari_dashboard_xendit
```

### Alur Pembayaran QRIS
1. Kasir pilih metode **QRIS** → klik **Bayar via Xendit**
2. Sistem buat transaksi pending + Invoice Xendit
3. Halaman pembayaran Xendit terbuka di tab baru
4. Pelanggan bayar (GoPay, OVO, Dana, m-banking, dll)
5. Xendit kirim webhook `POST /api/xendit/webhook`
6. Server kurangi stok → tandai transaksi `paid`
7. Kasir otomatis menerima konfirmasi (polling setiap 3 detik)

## Menjalankan Test

```bash
# Jalankan semua test
php artisan test

# Jalankan test spesifik
php artisan test --filter KasirTest
```

## API Endpoints Penting

### Kasir & Transaksi
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/api/barang` | List barang untuk kasir/pembelian |
| POST | `/api/transaksi` | Simpan transaksi tunai |
| GET | `/api/transaksi_penjualan` | List transaksi penjualan |
| GET | `/api/transaksi_detail/{id}` | Detail transaksi |

### Xendit QRIS
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/api/xendit/create-invoice` | Buat invoice + transaksi pending |
| GET | `/api/xendit/qr-status/{noTransaksi}` | Cek status pembayaran (polling) |
| DELETE | `/api/xendit/cancel-qr/{noTransaksi}` | Batalkan transaksi pending |
| POST | `/api/xendit/webhook` | Callback dari Xendit (publik) |

### Lainnya
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/api/manajemen_stok` | List stok (params: `page`, `limit`, `search_nama`) |
| DELETE | `/api/barang/{id}` | Hapus barang |
| POST | `/pembelian/store` | Simpan pembelian |
| GET | `/api/stok_opname/data` | Data stok opname |
| GET/POST/PUT/DELETE | `/api/users` | Manajemen pengguna |

> Untuk detail lengkap, lihat controller terkait di `app/Http/Controllers`.
