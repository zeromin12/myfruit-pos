<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $input['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid, muat ulang halaman.']);
    exit;
}

$items = $input['items'] ?? [];
$subtotal = (float)($input['subtotal'] ?? 0);
$diskon = (float)($input['diskon'] ?? 0);
$total = (float)($input['total'] ?? 0);
$bayar = (float)($input['bayar'] ?? 0);
$metode = $input['metode'] ?? 'tunai';
$namaPelanggan = trim($input['nama_pelanggan'] ?? 'Umum') ?: 'Umum';

if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Keranjang kosong.']);
    exit;
}
if (!in_array($metode, ['tunai','qris','debit','transfer'])) {
    $metode = 'tunai';
}

try {
    $pdo->beginTransaction();

    // Kunci baris produk & validasi stok terkini (mencegah race condition)
    foreach ($items as $it) {
        $produkId = (int)$it['produk_id'];
        $qty = (float)$it['qty'];

        $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ? FOR UPDATE");
        $stmt->execute([$produkId]);
        $p = $stmt->fetch();

        if (!$p || $p['status'] !== 'aktif') {
            throw new Exception("Produk '{$it['nama']}' tidak tersedia.");
        }
        if ($qty <= 0 || $p['stok'] < $qty) {
            throw new Exception("Stok '{$p['nama_produk']}' tidak mencukupi (tersisa {$p['stok']} {$p['satuan']}).");
        }
    }

    $noTransaksi = generateNoTransaksi($pdo);
    $kembalian = max($bayar - $total, 0);

    $stmt = $pdo->prepare("INSERT INTO transaksi (no_transaksi, user_id, nama_pelanggan, total_belanja, diskon, total_akhir, bayar, kembalian, metode_bayar) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$noTransaksi, $_SESSION['user_id'], $namaPelanggan, $subtotal, $diskon, $total, $bayar, $kembalian, $metode]);
    $transaksiId = $pdo->lastInsertId();

    $stmtDetail = $pdo->prepare("INSERT INTO transaksi_detail (transaksi_id, produk_id, nama_produk, harga_jual, qty, subtotal) VALUES (?,?,?,?,?,?)");
    $stmtStok = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");

    foreach ($items as $it) {
        $produkId = (int)$it['produk_id'];
        $qty = (float)$it['qty'];
        $harga = (float)$it['harga'];
        $subItem = $harga * $qty;

        $stmtDetail->execute([$transaksiId, $produkId, $it['nama'], $harga, $qty, $subItem]);
        $stmtStok->execute([$qty, $produkId]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'transaksi_id' => $transaksiId, 'no_transaksi' => $noTransaksi]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
