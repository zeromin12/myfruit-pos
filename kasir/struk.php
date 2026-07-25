<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin(1);

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT t.*, u.nama_lengkap FROM transaksi t JOIN users u ON u.id = t.user_id WHERE t.id = ?");
$stmt->execute([$id]);
$trx = $stmt->fetch();
if (!$trx) { die('Transaksi tidak ditemukan.'); }

$stmt = $pdo->prepare("SELECT * FROM transaksi_detail WHERE transaksi_id = ?");
$stmt->execute([$id]);
$detail = $stmt->fetchAll();

$toko = $pdo->query("SELECT * FROM pengaturan LIMIT 1")->fetch();
if (!$toko) { $toko = ['nama_toko' => 'MyFruit Official', 'alamat' => '', 'telepon' => '', 'footer_struk' => 'Terima kasih!']; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - <?= htmlspecialchars($trx['no_transaksi']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-3">
    <div class="d-flex justify-content-center gap-2 mb-3 no-print">
        <button class="btn btn-mf btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Cetak Struk</button>
        <a href="pos.php" class="btn btn-outline-secondary btn-sm">Transaksi Baru</a>
        <a href="../transaksi/riwayat.php" class="btn btn-outline-secondary btn-sm">Riwayat Transaksi</a>
    </div>

    <div class="struk bg-white p-3 shadow-sm">
        <div class="text-center">
            <strong style="font-size:14px;"><?= htmlspecialchars($toko['nama_toko']) ?></strong><br>
            <?php if ($toko['alamat']): ?><?= htmlspecialchars($toko['alamat']) ?><br><?php endif; ?>
            <?php if ($toko['telepon']): ?>Telp: <?= htmlspecialchars($toko['telepon']) ?><?php endif; ?>
        </div>
        <hr>
        No: <?= htmlspecialchars($trx['no_transaksi']) ?><br>
        Tgl: <?= formatTanggal($trx['created_at']) ?><br>
        Kasir: <?= htmlspecialchars($trx['nama_lengkap']) ?><br>
        Pelanggan: <?= htmlspecialchars($trx['nama_pelanggan']) ?>
        <hr>
        <?php foreach ($detail as $d): ?>
            <div><?= htmlspecialchars($d['nama_produk']) ?></div>
            <div class="d-flex justify-content-between">
                <span><?= rtrim(rtrim(number_format($d['qty'],2,',','.'),'0'),',') ?> x <?= number_format($d['harga_jual'],0,',','.') ?></span>
                <span><?= number_format($d['subtotal'],0,',','.') ?></span>
            </div>
        <?php endforeach; ?>
        <hr>
        <div class="d-flex justify-content-between"><span>Subtotal</span><span><?= number_format($trx['total_belanja'],0,',','.') ?></span></div>
        <?php if ($trx['diskon'] > 0): ?>
        <div class="d-flex justify-content-between"><span>Diskon</span><span>-<?= number_format($trx['diskon'],0,',','.') ?></span></div>
        <?php endif; ?>
        <div class="d-flex justify-content-between fw-bold"><span>TOTAL</span><span><?= number_format($trx['total_akhir'],0,',','.') ?></span></div>
        <div class="d-flex justify-content-between"><span>Bayar (<?= strtoupper($trx['metode_bayar']) ?>)</span><span><?= number_format($trx['bayar'],0,',','.') ?></span></div>
        <div class="d-flex justify-content-between"><span>Kembali</span><span><?= number_format($trx['kembalian'],0,',','.') ?></span></div>
        <hr>
        <div class="text-center"><?= htmlspecialchars($toko['footer_struk']) ?></div>
    </div>
</div>

<style>@media print { .no-print { display: none !important; } body { background: #fff !important; } }</style>
</body>
</html>
