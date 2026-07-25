<?php
/**
 * =============================================
 * KONFIGURASI DATABASE - MYFRUIT OFFICIAL POS
 * =============================================
 * Silakan sesuaikan dengan kredensial hosting/server Anda.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'myfruit_pos');
define('DB_USER', 'root');
define('DB_PASS', '');

// Nama & alamat dasar aplikasi (untuk link, dsb)
define('BASE_URL', '/myfruit-pos/'); // sesuaikan jika folder berbeda

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal. Periksa konfigurasi di config/database.php. Detail: " . $e->getMessage());
}
