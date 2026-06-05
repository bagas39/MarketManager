# Project Report - MarketManager

## 1. Identitas Project
- Nama project: MarketManager
- Jenis aplikasi: Website manajemen toko / kasir
- Framework utama: Laravel
- Fokus utama: CRUD data, transaksi, stok, laporan, dan interaksi frontend berbasis AJAX

## 2. Ringkasan Project
MarketManager adalah aplikasi web untuk membantu pengelolaan operasional toko. Aplikasi ini mencakup manajemen barang, transaksi penjualan, transaksi pembelian, stok opname, prediksi stok, manajemen pengguna, dan laporan keuangan.

Project ini dirancang agar data tersimpan di database, proses berjalan dinamis tanpa reload pada beberapa fitur utama, dan tampilan tetap nyaman digunakan di desktop maupun mobile.

## 3. Fitur Utama
- Login dan register user
- Manajemen pengguna berdasarkan role
- CRUD barang dan stok
- Transaksi penjualan / kasir
- Transaksi pembelian
- Stok opname
- Prediksi stok
- Laporan keuangan
- Export PDF untuk laporan

## 4. Kesesuaian dengan Rubrik Penilaian PAW

### 4.1 Functional Requirements
- Create, Read, Update, dan Delete tersedia pada fitur utama yang relevan.
- Validasi input dasar sudah diterapkan pada proses penting seperti login, register, pembelian, transaksi, dan manajemen pengguna.
- Data berubah sesuai proses yang dilakukan dan tersimpan ke database.

### 4.2 Database & Backend
- Data utama disimpan di database melalui migration dan model Eloquent.
- Struktur backend dipisahkan ke controller, model, service, dan migration.
- Query data menggunakan relasi dan operasi database yang sesuai kebutuhan fitur.

### 4.3 Frontend & Interaktivitas
- Implementasi Fetch API digunakan pada beberapa fitur utama.
- Ada manipulasi DOM untuk menampilkan data, pesan status, tabel, dan modal.
- Layout memakai utility CSS responsif sehingga tetap nyaman di mobile dan desktop.

### 4.4 Testing
- Tersedia Feature Test untuk beberapa fitur utama.
- Pengujian black box dapat dilakukan pada alur login, transaksi, pembelian, stok, dan manajemen pengguna.
- Dokumentasi hasil testing dapat dilampirkan dalam bentuk screenshot saat presentasi atau pengumpulan.

### 4.5 Code Quality
- Struktur project mengikuti pola Laravel.
- Controller, model, view, dan asset frontend dipisahkan sesuai tanggung jawabnya.
- Penggunaan komponen view dan file JavaScript terpisah membantu menjaga kerapian kode.

## 5. Teknologi yang Digunakan
- PHP 8.2+
- Laravel 12
- MySQL
- Vite
- Tailwind CSS
- JavaScript Fetch API
- DomPDF untuk export PDF

## 6. Struktur Project
- `app/Http/Controllers` - logika backend
- `app/Models` - model database
- `database/migrations` - struktur tabel
- `database/seeders` - data awal
- `resources/views` - halaman Blade
- `resources/js` - interaksi frontend
- `routes/web.php` - routing web dan API internal
- `tests/Feature` - pengujian fitur

## 7. Cara Menjalankan Project
### Persiapan
```bash
composer install
npm install
```

### Konfigurasi environment
- Salin `.env.example` menjadi `.env`
- Atur koneksi database MySQL
- Jalankan generate key

```bash
php artisan key:generate
```

### Migrasi database
```bash
php artisan migrate --seed
```

### Menjalankan frontend asset
```bash
npm run dev
```

### Menjalankan aplikasi
```bash
php artisan serve
```

Jika memakai XAMPP, project juga bisa dijalankan melalui folder `htdocs` sesuai konfigurasi lokal.

## 8. Pengujian
Perintah test:
```bash
php artisan test
```

Contoh area yang diuji:
- Login dan register
- Kasir / transaksi penjualan
- Pembelian
- Stok opname
- Manajemen pengguna
- Laporan keuangan

Saran bukti pengumpulan:
- Screenshot hasil `php artisan test`
- Screenshot halaman utama fitur
- Screenshot hasil transaksi dan perubahan data

## 9. Catatan Demo
Saat demo, urutan yang disarankan:
1. Login ke aplikasi
2. Tunjukkan manajemen barang atau stok
3. Tunjukkan transaksi penjualan atau pembelian
4. Tunjukkan perubahan data di database atau tampilan daftar
5. Tunjukkan hasil testing dan dokumentasi

## 10. Penutup
Project MarketManager dibuat untuk memenuhi kebutuhan pengelolaan toko berbasis web dengan fokus pada CRUD, database, interaktivitas frontend, testing, dan dokumentasi project yang jelas.
