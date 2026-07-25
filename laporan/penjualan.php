<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 1);

$pageTitle = 'Laporan Penjualan';
$depth = 1;

$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT t.*, u.nama_lengkap FROM transaksi t JOIN users u ON u.id = t.user_id
    WHERE DATE(t.created_at) BETWEEN ? AND ? AND t.status='selesai'
    ORDER BY t.created_at ASC
");
$stmt->execute([$dari, $sampai]);
$transaksiList = $stmt->fetchAll();

$totalOmzet = array_sum(array_column($transaksiList, 'total_akhir'));
$totalTransaksi = count($transaksiList);
$rataRata = $totalTransaksi > 0 ? $totalOmzet / $totalTransaksi : 0;

// Produk terlaris pada periode
$stmt = $pdo->prepare("
    SELECT td.nama_produk, SUM(td.qty) total_qty, SUM(td.subtotal) total_omzet
    FROM transaksi_detail td
    JOIN transaksi t ON t.id = td.transaksi_id
    WHERE DATE(t.created_at) BETWEEN ? AND ? AND t.status='selesai'
    GROUP BY td.nama_produk
    ORDER BY total_omzet DESC
    LIMIT 10
");
$stmt->execute([$dari, $sampai]);
$produkTerlaris = $stmt->fetchAll();

// Rekap per kasir
$stmt = $pdo->prepare("
    SELECT u.nama_lengkap, COUNT(t.id) jml_trx, COALESCE(SUM(t.total_akhir),0) total
    FROM transaksi t JOIN users u ON u.id = t.user_id
    WHERE DATE(t.created_at) BETWEEN ? AND ? AND t.status='selesai'
    GROUP BY u.id
    ORDER BY total DESC
");
$stmt->execute([$dari, $sampai]);
$rekapKasir = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h5 class="fw-bold mb-0">Laporan Penjualan</h5>
    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Cetak Laporan</button>
</div>

<div class="card mb-3 no-print">
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
                <button type="submit" class="btn btn-sm btn-mf w-100">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="text-center mb-3 d-none d-print-block">
    <h5 class="fw-bold">Laporan Penjualan MyFruit Official</h5>
    <p class="text-muted">Periode: <?= date('d/m/Y', strtotime($dari)) ?> - <?= date('d/m/Y', strtotime($sampai)) ?></p>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#1e8a4c,#146339);">
            <div class="stat-label">Total Omzet</div>
            <div class="stat-value" style="font-size:1.3rem;"><?= formatRupiah($totalOmzet) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#ff9f1c,#e07c00);">
            <div class="stat-label">Jumlah Transaksi</div>
            <div class="stat-value"><?= $totalTransaksi ?></div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#3498db,#2471a3);">
            <div class="stat-label">Rata-rata / Transaksi</div>
            <div class="stat-value" style="font-size:1.3rem;"><?= formatRupiah($rataRata) ?></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Produk Terlaris (Berdasarkan Omzet)</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Produk</th><th>Qty Terjual</th><th class="text-end">Omzet</th></tr></thead>
                    <tbody>
                    <?php if (!$produkTerlaris): ?><tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data</td></tr><?php endif; ?>
                    <?php foreach ($produkTerlaris as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nama_produk']) ?></td>
                            <td><?= (float)$p['total_qty'] ?></td>
                            <td class="text-end"><?= formatRupiah($p['total_omzet']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Rekap per Kasir</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Kasir</th><th>Jml Transaksi</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                    <?php if (!$rekapKasir): ?><tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data</td></tr><?php endif; ?>
                    <?php foreach ($rekapKasir as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
                            <td><?= $r['jml_trx'] ?></td>
                            <td class="text-end"><?= formatRupiah($r['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">Detail Transaksi Periode Ini</div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0 table-responsive-font">
            <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Kasir</th><th>Metode</th><th class="text-end">Total</th></tr></thead>
            <tbody>
            <?php if (!$transaksiList): ?><tr><td colspan="5" class="text-center text-muted py-3">Tidak ada transaksi</td></tr><?php endif; ?>
            <?php foreach ($transaksiList as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['no_transaksi']) ?></td>
                    <td><?= formatTanggal($t['created_at']) ?></td>
                    <td><?= htmlspecialchars($t['nama_lengkap']) ?></td>
                    <td class="text-uppercase"><?= htmlspecialchars($t['metode_bayar']) ?></td>
                    <td class="text-end"><?= formatRupiah($t['total_akhir']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<style>@media print { .no-print { display:none !important; } #sidebar, #topbar, footer { display:none !important; } #content-wrapper { margin-left:0 !important; } }</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
