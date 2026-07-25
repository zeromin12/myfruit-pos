<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin(1);

$pageTitle = 'Detail Transaksi';
$depth = 1;
$user = currentUser();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT t.*, u.nama_lengkap FROM transaksi t JOIN users u ON u.id = t.user_id WHERE t.id = ?");
$stmt->execute([$id]);
$trx = $stmt->fetch();

if (!$trx || ($user['role'] !== 'admin' && $trx['user_id'] != $user['id'])) {
    header('Location: riwayat.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM transaksi_detail WHERE transaksi_id = ?");
$stmt->execute([$id]);
$detail = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Detail Transaksi</h5>
    <div>
        <a href="riwayat.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        <a href="../kasir/struk.php?id=<?= $trx['id'] ?>" target="_blank" class="btn btn-sm btn-mf"><i class="bi bi-printer me-1"></i>Cetak Struk</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <table class="table table-borderless table-sm mb-0 small">
                <tr><td class="text-muted">No. Transaksi</td><td class="fw-semibold"><?= htmlspecialchars($trx['no_transaksi']) ?></td></tr>
                <tr><td class="text-muted">Tanggal</td><td><?= formatTanggal($trx['created_at']) ?></td></tr>
                <tr><td class="text-muted">Kasir</td><td><?= htmlspecialchars($trx['nama_lengkap']) ?></td></tr>
                <tr><td class="text-muted">Pelanggan</td><td><?= htmlspecialchars($trx['nama_pelanggan']) ?></td></tr>
                <tr><td class="text-muted">Metode Bayar</td><td><span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($trx['metode_bayar']) ?></span></td></tr>
            </table>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <table class="table table-borderless table-sm mb-0 small">
                <tr><td class="text-muted">Subtotal</td><td class="text-end"><?= formatRupiah($trx['total_belanja']) ?></td></tr>
                <tr><td class="text-muted">Diskon</td><td class="text-end">-<?= formatRupiah($trx['diskon']) ?></td></tr>
                <tr><td class="fw-bold">Total Akhir</td><td class="text-end fw-bold text-mf"><?= formatRupiah($trx['total_akhir']) ?></td></tr>
                <tr><td class="text-muted">Dibayar</td><td class="text-end"><?= formatRupiah($trx['bayar']) ?></td></tr>
                <tr><td class="text-muted">Kembalian</td><td class="text-end"><?= formatRupiah($trx['kembalian']) ?></td></tr>
            </table>
        </div></div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">Daftar Item</div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody>
            <?php foreach ($detail as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['nama_produk']) ?></td>
                    <td><?= formatRupiah($d['harga_jual']) ?></td>
                    <td><?= rtrim(rtrim(number_format($d['qty'],2,',','.'),'0'),',') ?></td>
                    <td class="text-end"><?= formatRupiah($d['subtotal']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
