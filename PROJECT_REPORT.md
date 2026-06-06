# Project Report - MarketManager

## 1. Identitas Project
- Nama project: MarketManager
- Jenis aplikasi: Website manajemen toko / kasir
- Framework utama: Laravel 12
- Fokus utama: CRUD data, transaksi, stok, laporan, interaksi frontend berbasis AJAX, dan integrasi pembayaran digital

## 2. Ringkasan Project
MarketManager adalah aplikasi web untuk membantu pengelolaan operasional toko. Aplikasi ini mencakup manajemen barang, transaksi penjualan dengan dukungan pembayaran tunai dan QRIS via Xendit, transaksi pembelian, stok opname, prediksi stok, manajemen pengguna, dan laporan keuangan.

Project ini dirancang agar data tersimpan di database, proses berjalan dinamis tanpa reload penuh pada fitur utama, dan tampilan tetap nyaman digunakan di desktop maupun mobile.

## 3. Fitur Utama
- Login dan register user
- Manajemen pengguna berdasarkan role (Kasir, Gudang, Supervisor, Owner)
- CRUD barang dan stok
- Transaksi penjualan / kasir (tunai dan QRIS via Xendit)
- Transaksi pembelian
- Stok opname
- Prediksi stok
- Laporan keuangan
- Export PDF untuk laporan

## 4. Integrasi Xendit QRIS

### 4.1 Gambaran Umum
Fitur pembayaran QRIS menggunakan **Xendit Invoice API**. Saat kasir memilih metode QRIS dan mengklik tombol bayar, sistem:
1. Membuat transaksi berstatus `pending` di database
2. Memanggil Xendit API untuk membuat Invoice
3. Membuka halaman pembayaran Xendit di tab baru
4. Menunggu konfirmasi pembayaran melalui webhook

### 4.2 Komponen yang Terlibat

| Komponen | File | Fungsi |
|---|---|---|
| Service | `app/Services/XenditService.php` | Wrapper HTTP call ke Xendit API |
| Controller | `app/Http/Controllers/XenditController.php` | Endpoint create invoice, cek status, cancel, webhook |
| Model | `app/Models/Transaksi.php` | Kolom `payment_method`, `xendit_qr_id`, `status` |
| Migration | `database/migrations/2026_06_06_..._add_xendit_columns_to_transaksis_table.php` | Tambah kolom Xendit ke tabel transaksis |
| Frontend | `resources/js/kasir.js` | Toggle metode bayar, buka Xendit, polling status |
| View | `resources/views/kasir.blade.php` | UI toggle Tunai/QRIS dan modal menunggu pembayaran |

### 4.3 Alur Teknis

```
Kasir → POST /api/xendit/create-invoice
      ← { invoice_url, no_transaksi, amount }
Kasir → window.open(invoice_url)
Kasir → polling GET /api/xendit/qr-status/{no_transaksi} setiap 3 detik

Pelanggan bayar di halaman Xendit
Xendit → POST /api/xendit/webhook
Laravel → kurangi stok + update status = 'paid'
Kasir (polling) → status paid → konfirmasi sukses
```

### 4.4 Keamanan Webhook
- Route webhook dikecualikan dari CSRF middleware
- Verifikasi menggunakan `x-callback-token` header dari Xendit
- Proses pembayaran menggunakan database transaction + `lockForUpdate` untuk mencegah race condition

## 5. Fitur Lain

### 5.1 Functional Requirements
- Create, Read, Update, dan Delete tersedia pada fitur utama yang relevan.
- Validasi input diterapkan pada proses penting: login, register, pembelian, transaksi, dan manajemen pengguna.
- Data berubah sesuai proses yang dilakukan dan tersimpan ke database.

### 5.2 Database & Backend
- Data utama disimpan di database melalui migration dan model Eloquent.
- Struktur backend dipisahkan ke controller, model, service, dan migration.
- Integrasi pihak ketiga (Xendit) dipisahkan ke layer `Services`.
- Query menggunakan relasi Eloquent dan database transaction untuk operasi kritis.

### 5.3 Frontend & Interaktivitas
- Fetch API digunakan pada seluruh fitur utama (kasir, stok, pembelian, dll).
- Manipulasi DOM dinamis untuk tabel, modal, status, dan konfirmasi.
- Polling otomatis untuk deteksi pembayaran QRIS tanpa reload halaman.
- Layout responsif dengan Tailwind CSS untuk desktop dan mobile.

### 5.4 Testing
- Tersedia Feature Test untuk seluruh fitur utama (55+ assertions).
- Pengujian mencakup: login, register, kasir, pembelian, stok opname, prediksi stok, manajemen pengguna, laporan keuangan, transaksi penjualan.

### 5.5 Code Quality
- Struktur project mengikuti konvensi Laravel.
- Controller, model, view, service, dan asset frontend dipisahkan sesuai tanggung jawabnya.
- Integrasi eksternal (Xendit) terisolasi di `XenditService` sehingga mudah diganti atau diuji.

## 6. Teknologi yang Digunakan

| Teknologi | Versi | Kegunaan |
|---|---|---|
| PHP | 8.2+ | Backend |
| Laravel | 12 | Framework |
| MySQL | - | Database |
| Vite | 7 | Bundler asset |
| Tailwind CSS | 4 | Styling |
| JavaScript Fetch API | - | Komunikasi frontend-backend |
| DomPDF | 3.x | Export PDF laporan |
| Xendit | Invoice API | Pembayaran QRIS |
| ngrok | - | Expose localhost untuk webhook (development) |

## 7. Struktur Project

```
app/
├── Http/Controllers/
│   ├── KasirController.php       — transaksi tunai
│   ├── XenditController.php      — transaksi QRIS + webhook
│   └── ...
├── Models/                       — Eloquent models
└── Services/
    └── XenditService.php         — Xendit API wrapper

database/migrations/              — skema tabel
resources/
├── js/kasir.js                   — logika kasir (tunai + QRIS)
└── views/kasir.blade.php         — UI kasir
routes/web.php                    — routing + API internal
tests/Feature/                    — feature tests
```

## 8. Cara Menjalankan Project

### Persiapan
```bash
composer install
npm install
```

### Konfigurasi environment
Salin `.env.example` menjadi `.env`, atur database dan Xendit:
```env
DB_CONNECTION=mysql
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_pass

XENDIT_SECRET_KEY=xnd_development_...
XENDIT_WEBHOOK_TOKEN=your_webhook_token
```

### Migrasi dan build
```bash
php artisan key:generate
php artisan migrate --seed
npm run build
```

### Menjalankan aplikasi
```bash
php artisan serve
```

### Webhook QRIS (development)
```bash
ngrok http 8000
# Daftarkan https://xxxx.ngrok-free.app/api/xendit/webhook di dashboard Xendit
```

## 9. Pengujian

```bash
php artisan test
```

Area yang diuji:
- Login dan register
- Kasir / transaksi penjualan (tunai)
- Transaksi pembelian
- Stok opname
- Manajemen pengguna
- Laporan keuangan
- Prediksi stok
- Transaksi penjualan (CRUD)


## 10. Penutup
Project MarketManager dibuat untuk memenuhi kebutuhan pengelolaan toko berbasis web dengan fokus pada CRUD, database, interaktivitas frontend, integrasi pembayaran digital (Xendit QRIS), testing, dan dokumentasi yang jelas.
