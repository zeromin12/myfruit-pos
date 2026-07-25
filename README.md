# MyFruit Official - Sistem POS & Manajemen Toko

Sistem kasir (POS) dan manajemen toko buah berbasis **PHP native + MySQL**, dilengkapi
manajemen produk, kategori, stok, transaksi, laporan, dan pengguna (role admin/kasir).

## Fitur Utama
- Login dengan 2 role: **Admin** dan **Kasir**
- Dashboard dengan statistik penjualan, grafik, produk terlaris, stok menipis
- Manajemen produk & kategori (CRUD, upload gambar, stok minimum)
- Kasir (POS): pencarian produk, keranjang belanja real-time, diskon, hitung kembalian otomatis
- Cetak struk thermal (58/80mm) siap print
- Manajemen stok masuk (restock) dengan riwayat
- Riwayat & detail transaksi
- Laporan penjualan (per periode, produk terlaris, rekap kasir) + cetak laporan
- Laporan stok & estimasi nilai stok
- Manajemen pengguna (tambah/edit/nonaktifkan akun kasir & admin)
- Pengaturan identitas toko (nama, alamat, footer struk)
- Desain responsif untuk HP (sudah dioptimalkan tampilan mobile)
- Proteksi CSRF token & transaksi database atomic (anti race-condition stok)

## Kebutuhan Server
- PHP 7.4 ke atas (disarankan PHP 8.x), dengan ekstensi **PDO MySQL**
- MySQL / MariaDB
- Web server: Apache (XAMPP/Laragon) atau Nginx

## Cara Instalasi (Local - XAMPP/Laragon)

1. **Salin folder** `myfruit-pos` ke folder `htdocs` (XAMPP) atau `www` (Laragon).

2. **Buat database**:
   - Buka phpMyAdmin, import file `database.sql` (akan otomatis membuat database
     `myfruit_pos` beserta semua tabel dan data awal).

3. **Konfigurasi koneksi database**:
   - Buka `config/database.php`, sesuaikan jika perlu:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'myfruit_pos');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

4. **Akses aplikasi** melalui browser:
   ```
   http://localhost/myfruit-pos/
   ```

5. **Login dengan akun default**:
   | Username | Password  | Role  |
   |----------|-----------|-------|
   | admin    | admin123  | admin |
   | kasir1   | admin123  | kasir |

   ⚠️ **Segera ganti password default** melalui menu Manajemen Pengguna setelah login pertama.

## Cara Deploy ke Hosting (cPanel dsb.)
1. Upload seluruh isi folder `myfruit-pos` ke `public_html` (atau subfolder).
2. Buat database MySQL baru + user melalui cPanel, lalu import `database.sql`.
3. Edit `config/database.php` sesuai kredensial database hosting.
4. Set folder `assets/img` agar writable (permission 755/775) untuk upload gambar produk.
5. Akses domain Anda, login dan mulai gunakan.

## Struktur Folder
```
myfruit-pos/
├── config/            # Koneksi database & session
├── includes/          # Header, sidebar, footer, fungsi auth
├── assets/            # CSS, JS, gambar produk
├── produk/            # CRUD produk
├── kategori/          # CRUD kategori
├── kasir/             # Halaman POS, proses transaksi, cetak struk
├── transaksi/         # Riwayat & detail transaksi
├── laporan/           # Laporan penjualan & stok
├── pengguna/          # Manajemen pengguna
├── stok/              # Stok masuk (restock)
├── database.sql       # Skema database lengkap
├── login.php / logout.php / dashboard.php / pengaturan.php
```

## Tips Printer Thermal
Halaman struk (`kasir/struk.php`) sudah didesain lebar 280px cocok untuk kertas
thermal 58mm/80mm. Gunakan tombol **Cetak Struk** lalu pilih printer thermal Anda
di dialog print browser (nonaktifkan header/footer browser di pengaturan print
untuk hasil rapi).

## Keamanan
- Password di-hash dengan `password_hash()` (bcrypt)
- Semua query menggunakan prepared statement (PDO) — aman dari SQL Injection
- Form penting dilindungi CSRF token
- Transaksi kasir menggunakan `FOR UPDATE` lock untuk mencegah stok minus akibat
  transaksi bersamaan (race condition)
- Session otomatis logout setelah 2 jam tidak aktif

## Kustomisasi Lanjutan
- Warna tema bisa diubah di `assets/css/style.css` (variabel `--mf-primary`, dst)
- Untuk menambah metode pembayaran, edit enum `metode_bayar` di tabel `transaksi`
  serta dropdown terkait di `kasir/pos.php`
