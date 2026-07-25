<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin(0);

$pageTitle = 'Dashboard';
$depth = 0;
$user = currentUser();

// Statistik hari ini
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) jml, COALESCE(SUM(total_akhir),0) total FROM transaksi WHERE DATE(created_at) = ? AND status = 'selesai'");
$stmt->execute([$today]);
$statHariIni = $stmt->fetch();

// Statistik bulan ini
$bulanIni = date('Y-m');
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_akhir),0) total FROM transaksi WHERE DATE_FORMAT(created_at,'%Y-%m') = ? AND status = 'selesai'");
$stmt->execute([$bulanIni]);
$totalBulanIni = $stmt->fetchColumn();

// Total produk & stok menipis
$totalProduk = $pdo->query("SELECT COUNT(*) FROM produk WHERE status='aktif'")->fetchColumn();
$stokMenipis = $pdo->query("SELECT COUNT(*) FROM produk WHERE stok <= stok_minimum AND status='aktif'")->fetchColumn();

// Produk terlaris (30 hari terakhir)
$produkTerlaris = $pdo->query("
    SELECT td.nama_produk, SUM(td.qty) total_qty
    FROM transaksi_detail td
    JOIN transaksi t ON t.id = td.transaksi_id
    WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND t.status='selesai'
    GROUP BY td.nama_produk
    ORDER BY total_qty DESC
    LIMIT 5
")->fetchAll();

// Grafik penjualan 7 hari terakhir
$grafik = $pdo->query("
    SELECT DATE(created_at) tgl, COALESCE(SUM(total_akhir),0) total
    FROM transaksi
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 DAY) AND status='selesai'
    GROUP BY DATE(created_at)
")->fetchAll(PDO::FETCH_KEY_PAIR);

$labels7 = []; $data7 = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $labels7[] = date('d/m', strtotime($d));
    $data7[] = isset($grafik[$d]) ? (float)$grafik[$d] : 0;
}

// Transaksi terbaru
$transaksiTerbaru = $pdo->query("
    SELECT t.*, u.nama_lengkap FROM transaksi t
    JOIN users u ON u.id = t.user_id
    ORDER BY t.id DESC LIMIT 6
")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Halo, <?= htmlspecialchars($user['nama']) ?> 👋</h5>
    <span class="text-muted small"><?= date('l, d F Y') ?></span>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#1e8a4c,#146339);">
            <div class="stat-label"><i class="bi bi-cash-stack me-1"></i>Penjualan Hari Ini</div>
            <div class="stat-value"><?= formatRupiah($statHariIni['total']) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#ff9f1c,#e07c00);">
            <div class="stat-label"><i class="bi bi-receipt me-1"></i>Transaksi Hari Ini</div>
            <div class="stat-value"><?= (int)$statHariIni['jml'] ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#3498db,#2471a3);">
            <div class="stat-label"><i class="bi bi-box-seam me-1"></i>Total Produk Aktif</div>
            <div class="stat-value"><?= (int)$totalProduk ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b);">
            <div class="stat-label"><i class="bi bi-exclamation-triangle me-1"></i>Stok Menipis</div>
            <div class="stat-value"><?= (int)$stokMenipis ?></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Grafik Penjualan 7 Hari Terakhir</div>
            <div class="card-body"><canvas id="chartPenjualan" height="110"></canvas></div>
        </div>

        <div class="card mt-3">
            <div class="card-header">Transaksi Terbaru</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead><tr><th>No. Transaksi</th><th>Kasir</th><th>Total</th><th>Waktu</th></tr></thead>
                    <tbody>
                    <?php if (!$transaksiTerbaru): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Belum ada transaksi</td></tr>
                    <?php endif; ?>
                    <?php foreach ($transaksiTerbaru as $t): ?>
                        <tr>
                            <td><a href="transaksi/detail.php?id=<?= $t['id'] ?>" class="text-mf fw-semibold text-decoration-none"><?= htmlspecialchars($t['no_transaksi']) ?></a></td>
                            <td><?= htmlspecialchars($t['nama_lengkap']) ?></td>
                            <td><?= formatRupiah($t['total_akhir']) ?></td>
                            <td class="text-muted small"><?= formatTanggal($t['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Produk Terlaris (30 hari)</div>
            <div class="card-body">
                <?php if (!$produkTerlaris): ?>
                    <p class="text-muted small mb-0">Belum ada data.</p>
                <?php endif; ?>
                <?php foreach ($produkTerlaris as $i => $p): ?>
                    <div class="d-flex justify-content-between align-items-center py-2 <?= $i < count($produkTerlaris)-1 ? 'border-bottom' : '' ?>">
                        <span class="small"><?= $i+1 ?>. <?= htmlspecialchars($p['nama_produk']) ?></span>
                        <span class="badge bg-mf"><?= (float)$p['total_qty'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">Ringkasan Bulan Ini</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Penjualan</span>
                    <span class="fw-bold text-mf"><?= formatRupiah($totalBulanIni) ?></span>
                </div>
                <?php if ($user['role'] === 'admin'): ?>
                <a href="laporan/penjualan.php" class="btn btn-sm btn-outline-success w-100 mt-2">Lihat Laporan Lengkap</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
new Chart(document.getElementById('chartPenjualan'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels7) ?>,
        datasets: [{
            label: 'Penjualan (Rp)',
            data: <?= json_encode($data7) ?>,
            borderColor: '#1e8a4c',
            backgroundColor: 'rgba(30,138,76,.12)',
            fill: true,
            tension: .35,
            pointRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } } }
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
