<?php
/**
 * Manajemen session global.
 * File ini WAJIB di-include paling atas di setiap halaman terproteksi.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto logout setelah 2 jam tidak aktif
$timeout = 7200;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/produk/') !== false || strpos($_SERVER['PHP_SELF'], '/kategori/') !== false || strpos($_SERVER['PHP_SELF'], '/kasir/') !== false || strpos($_SERVER['PHP_SELF'], '/transaksi/') !== false || strpos($_SERVER['PHP_SELF'], '/laporan/') !== false || strpos($_SERVER['PHP_SELF'], '/pengguna/') !== false || strpos($_SERVER['PHP_SELF'], '/stok/') !== false ? '../login.php?expired=1' : 'login.php?expired=1'));
    exit;
}
$_SESSION['last_activity'] = time();
