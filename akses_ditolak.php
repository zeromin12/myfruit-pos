<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin(0);
$pageTitle = 'Akses Ditolak';
$depth = 0;
include __DIR__ . '/includes/header.php';
?>
<div class="text-center py-5">
    <i class="bi bi-shield-lock text-danger" style="font-size:3.5rem;"></i>
    <h4 class="fw-bold mt-3">Akses Ditolak</h4>
    <p class="text-muted">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
    <a href="dashboard.php" class="btn btn-mf">Kembali ke Dashboard</a>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
