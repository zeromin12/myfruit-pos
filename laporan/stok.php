<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 1);

$pageTitle = 'Laporan Stok';
$depth = 1;

$filter = $_GET['filter'] ?? 'semua';
$sql = "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON k.id = p.kategori_id WHERE p.status='aktif'";
if ($filter === 'menipis') {
    $sql .= " AND p.stok <= p.stok_minimum";
}
$sql .= " ORDER BY p.stok ASC";
$produkList = $pdo->query($sql)->fetchAll();

$totalNilaiStok = 0;
foreach ($produkList as $p) { $totalNilaiStok += $p['stok'] * $p['harga_beli']; }

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Laporan Stok Produk</h5>
    <div class="btn-group btn-group-sm">
        <a href="?filter=semua" class="btn btn-outline-secondary <?= $filter==='semua'?'active':'' ?>">Semua</a>
        <a href="?filter=menipis" class="btn btn-outline-danger <?= $filter==='menipis'?'active':'' ?>">Stok Menipis</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <span class="text-muted small">Estimasi Nilai Total Stok (berdasarkan harga beli)</span>
        <span class="fw-bold text-mf fs-5"><?= formatRupiah($totalNilaiStok) ?></span>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle table-responsive-font">
            <thead><tr><th>Kode</th><th>Produk</th><th>Kategori</th><th>Stok</th><th>Stok Minimum</th><th>Nilai Stok</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (!$produkList): ?><tr><td colspan="7" class="text-center text-muted py-3">Tidak ada data</td></tr><?php endif; ?>
            <?php foreach ($produkList as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['kode_produk']) ?></td>
                    <td><?= htmlspecialchars($p['nama_produk']) ?></td>
                    <td><?= htmlspecialchars($p['nama_kategori'] ?? '-') ?></td>
                    <td><?= rtrim(rtrim(number_format($p['stok'],2,',','.'),'0'),',') ?> <?= htmlspecialchars($p['satuan']) ?></td>
                    <td><?= rtrim(rtrim(number_format($p['stok_minimum'],2,',','.'),'0'),',') ?> <?= htmlspecialchars($p['satuan']) ?></td>
                    <td><?= formatRupiah($p['stok'] * $p['harga_beli']) ?></td>
                    <td>
                        <?php if ($p['stok'] <= $p['stok_minimum']): ?>
                            <span class="badge badge-stok-rendah">Perlu Restock</span>
                        <?php else: ?>
                            <span class="badge bg-success">Aman</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
