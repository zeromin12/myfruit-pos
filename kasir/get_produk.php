<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin(1);

header('Content-Type: application/json');

$produk = $pdo->query("
    SELECT id, kode_produk, nama_produk, kategori_id, harga_jual, stok, satuan
    FROM produk
    WHERE status = 'aktif'
    ORDER BY nama_produk ASC
")->fetchAll();

echo json_encode($produk);
