<?php
/**
 * Fungsi-fungsi otentikasi & helper umum.
 * Include setelah config/session.php dan config/database.php
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUser() {
    return [
        'id'     => $_SESSION['user_id']   ?? null,
        'nama'   => $_SESSION['nama']      ?? null,
        'role'   => $_SESSION['role']      ?? null,
        'username' => $_SESSION['username'] ?? null,
    ];
}

/**
 * Wajib login. Jika belum, redirect ke login.
 * $depth = jumlah level folder dari root (0 = root, 1 = satu folder di dalam, dst)
 */
function requireLogin($depth = 0) {
    if (!isLoggedIn()) {
        $prefix = str_repeat('../', $depth);
        header('Location: ' . $prefix . 'login.php');
        exit;
    }
}

/**
 * Wajib role tertentu (misal 'admin'). Jika bukan, tolak akses.
 */
function requireRole($role, $depth = 0) {
    requireLogin($depth);
    if ($_SESSION['role'] !== $role) {
        $prefix = str_repeat('../', $depth);
        header('Location: ' . $prefix . 'akses_ditolak.php');
        exit;
    }
}

function formatRupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function formatTanggal($tanggal) {
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($tanggal);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts) . ' ' . date('H:i', $ts);
}

function generateNoTransaksi($pdo) {
    $prefix = 'TRX' . date('Ymd');
    $stmt = $pdo->prepare("SELECT no_transaksi FROM transaksi WHERE no_transaksi LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    if ($last) {
        $urut = (int)substr($last, -4) + 1;
    } else {
        $urut = 1;
    }
    return $prefix . str_pad($urut, 4, '0', STR_PAD_LEFT);
}

function generateKodeProduk($pdo) {
    $stmt = $pdo->query("SELECT kode_produk FROM produk ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetchColumn();
    if ($last && preg_match('/(\d+)$/', $last, $m)) {
        $urut = (int)$m[1] + 1;
    } else {
        $urut = 1;
    }
    return 'FRT' . str_pad($urut, 4, '0', STR_PAD_LEFT);
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfCheck() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Sesi tidak valid (CSRF check gagal). Silakan muat ulang halaman dan coba lagi.');
    }
}
