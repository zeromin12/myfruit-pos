<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
function navActive($page, $dir, $matchPage, $matchDir = null) {
    if ($matchDir) return ($page === $matchPage && $dir === $matchDir) ? 'active' : '';
    return $page === $matchPage ? 'active' : '';
}
?>
<aside id="sidebar">
    <div class="brand">
        <i class="bi bi-basket3-fill fs-4"></i>
        <span>MyFruit Official</span>
    </div>
    <ul class="nav flex-column py-2">

        <li class="nav-item">
            <a class="nav-link <?= navActive($currentPage, $currentDir, 'dashboard.php') ?>" href="<?= $prefix ?>dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?= navActive($currentPage, $currentDir, 'pos.php', 'kasir') ?>" href="<?= $prefix ?>kasir/pos.php">
                <i class="bi bi-cash-coin me-2"></i>Kasir (POS)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= navActive($currentPage, $currentDir, 'riwayat.php', 'transaksi') ?>" href="<?= $prefix ?>transaksi/riwayat.php">
                <i class="bi bi-receipt me-2"></i>Riwayat Transaksi
            </a>
        </li>

        <?php if ($user['role'] === 'admin'): ?>
        <div class="nav-heading">Manajemen Produk</div>
        <li class="nav-item">
            <a class="nav-link <?= navActive($currentPage, $currentDir, 'index.php', 'produk') ?>" href="<?= $prefix ?>produk/index.php">
                <i class="bi bi-box-seam me-2"></i>Data Produk
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= navActive($currentPage, $currentDir, 'index.php', 'kategori') ?>" href="<?= $prefix ?>kategori/index.php">
                <i class="bi bi-tags me-2"></i>Kategori
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= navActive($currentPage, $currentDir, 'index.php', 'stok') ?>" href="<?= $prefix ?>stok/index.php">
                <i class="bi bi-box-arrow-in-down me-2"></i>Stok Masuk
            </a>
        </li>

        <div class="nav-heading">Laporan</div>
        <li class="nav-item">
            <a class="nav-link <?= navActive($currentPage, $currentDir, 'penjualan.php', 'laporan') ?>" href="<?= $prefix ?>laporan/penjualan.php">
                <i class="bi bi-graph-up me-2"></i>Laporan Penjualan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= navActive($currentPage, $currentDir, 'stok.php', 'laporan') ?>" href="<?= $prefix ?>laporan/stok.php">
                <i class="bi bi-clipboard-data me-2"></i>Laporan Stok
            </a>
        </li>

        <div class="nav-heading">Administrasi</div>
        <li class="nav-item">
            <a class="nav-link <?= navActive($currentPage, $currentDir, 'index.php', 'pengguna') ?>" href="<?= $prefix ?>pengguna/index.php">
                <i class="bi bi-people me-2"></i>Manajemen Pengguna
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= navActive($currentPage, $currentDir, 'pengaturan.php') ?>" href="<?= $prefix ?>pengaturan.php">
                <i class="bi bi-gear me-2"></i>Pengaturan Toko
            </a>
        </li>
        <?php endif; ?>
    </ul>
</aside>
