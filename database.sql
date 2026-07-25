-- =============================================
-- MYFRUIT OFFICIAL - DATABASE SCHEMA
-- Sistem POS Toko Buah
-- =============================================

CREATE DATABASE IF NOT EXISTS myfruit_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE myfruit_pos;

-- =============================================
-- TABEL USERS (Pengguna: admin & kasir)
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin','kasir') NOT NULL DEFAULT 'kasir',
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Password default: admin123 (akan di-hash oleh PHP saat seed)
INSERT INTO users (username, password, nama_lengkap, role) VALUES
('admin', '$2y$10$HRVLZhuDzlurY9QR8CzrIeIfkKc9Dg2q0ux7dWC9Th4LInMxJjgPW', 'Administrator', 'admin'),
('kasir1', '$2y$10$HRVLZhuDzlurY9QR8CzrIeIfkKc9Dg2q0ux7dWC9Th4LInMxJjgPW', 'Kasir Satu', 'kasir');
-- Password untuk kedua akun di atas: admin123 (silakan ganti setelah login pertama)

-- =============================================
-- TABEL KATEGORI PRODUK
-- =============================================
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO kategori (nama_kategori) VALUES
('Buah Lokal'), ('Buah Import'), ('Buah Potong'), ('Sayuran'), ('Lainnya');

-- =============================================
-- TABEL PRODUK
-- =============================================
CREATE TABLE produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_produk VARCHAR(30) NOT NULL UNIQUE,
    nama_produk VARCHAR(150) NOT NULL,
    kategori_id INT,
    satuan VARCHAR(20) NOT NULL DEFAULT 'kg',
    harga_beli DECIMAL(12,2) NOT NULL DEFAULT 0,
    harga_jual DECIMAL(12,2) NOT NULL DEFAULT 0,
    stok DECIMAL(10,2) NOT NULL DEFAULT 0,
    stok_minimum DECIMAL(10,2) NOT NULL DEFAULT 5,
    gambar VARCHAR(255) DEFAULT NULL,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================
-- TABEL TRANSAKSI (HEADER)
-- =============================================
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi VARCHAR(30) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    nama_pelanggan VARCHAR(100) DEFAULT 'Umum',
    total_belanja DECIMAL(12,2) NOT NULL DEFAULT 0,
    diskon DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_akhir DECIMAL(12,2) NOT NULL DEFAULT 0,
    bayar DECIMAL(12,2) NOT NULL DEFAULT 0,
    kembalian DECIMAL(12,2) NOT NULL DEFAULT 0,
    metode_bayar ENUM('tunai','qris','debit','transfer') NOT NULL DEFAULT 'tunai',
    status ENUM('selesai','dibatalkan') NOT NULL DEFAULT 'selesai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- =============================================
-- TABEL DETAIL TRANSAKSI
-- =============================================
CREATE TABLE transaksi_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    produk_id INT NOT NULL,
    nama_produk VARCHAR(150) NOT NULL,
    harga_jual DECIMAL(12,2) NOT NULL,
    qty DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id)
) ENGINE=InnoDB;

-- =============================================
-- TABEL STOK MASUK (Riwayat Restock/Pembelian)
-- =============================================
CREATE TABLE stok_masuk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produk_id INT NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    harga_beli DECIMAL(12,2) NOT NULL DEFAULT 0,
    keterangan VARCHAR(255) DEFAULT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produk_id) REFERENCES produk(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- =============================================
-- TABEL PENGATURAN TOKO
-- =============================================
CREATE TABLE pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_toko VARCHAR(150) NOT NULL DEFAULT 'MyFruit Official',
    alamat TEXT,
    telepon VARCHAR(30),
    footer_struk VARCHAR(255) DEFAULT 'Terima kasih telah berbelanja!',
    logo VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

INSERT INTO pengaturan (nama_toko, alamat, telepon, footer_struk) VALUES
('MyFruit Official', 'Jl. Contoh No. 123, Jakarta', '0812-3456-7890', 'Terima kasih telah berbelanja! Buah segar setiap hari.');
