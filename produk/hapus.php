<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 1);

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("DELETE FROM produk WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: index.php?msg=hapus');
} catch (PDOException $e) {
    // Jika produk sudah pernah dipakai dalam transaksi, nonaktifkan saja
    $stmt = $pdo->prepare("UPDATE produk SET status = 'nonaktif' WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: index.php?msg=hapus');
}
exit;
