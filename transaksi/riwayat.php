<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin(1);

$pageTitle = 'Riwayat Transaksi';
$depth = 1;
$user = currentUser();

$dari = $_GET['dari'] ?? date('Y-m-d');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

$sql = "SELECT t.*, u.nama_lengkap FROM transaksi t JOIN users u ON u.id = t.user_id
        WHERE DATE(t.created_at) BETWEEN ? AND ?";
$params = [$dari, $sampai];

// Kasir hanya lihat transaksinya sendiri, admin lihat semua
if ($user['role'] !== 'admin') {
    $sql .= " AND t.user_id = ?";
    $params[] = $user['id'];
}
$sql .= " ORDER BY t.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksiList = $stmt->fetchAll();

$totalPeriode = array_sum(array_column($transaksiList, 'total_akhir'));

include __DIR__ . '/../includes/header.php';
?>

<h5 class="fw-bold mb-3">Riwayat Transaksi</h5>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small">Dari Tanggal</label>
                <input type="date" name="dari" class="form-control form-control-sm" value="<?= htmlspecialchars($dari) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control form-control-sm" value="<?= htmlspecialchars($sampai) ?>">
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-sm btn-mf w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <span class="text-muted small">Total <?= count($transaksiList) ?> transaksi pada periode ini</span>
        <span class="fw-bold text-mf fs-5"><?= formatRupiah($totalPeriode) ?></span>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle table-responsive-font">
            <thead><tr><th>No. Transaksi</th><th>Pelanggan</th><th>Kasir</th><th>Metode</th><th>Total</th><th>Waktu</th><th></th></tr></thead>
            <tbody>
            <?php if (!$transaksiList): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada transaksi pada periode ini</td></tr>
            <?php endif; ?>
            <?php foreach ($transaksiList as $t): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($t['no_transaksi']) ?></td>
                    <td><?= htmlspecialchars($t['nama_pelanggan']) ?></td>
                    <td><?= htmlspecialchars($t['nama_lengkap']) ?></td>
                    <td><span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($t['metode_bayar']) ?></span></td>
                    <td><?= formatRupiah($t['total_akhir']) ?></td>
                    <td class="text-muted small"><?= formatTanggal($t['created_at']) ?></td>
                    <td>
                        <a href="detail.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        <a href="../kasir/struk.php?id=<?= $t['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
