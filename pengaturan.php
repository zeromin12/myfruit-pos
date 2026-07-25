<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requireRole('admin', 0);

$pageTitle = 'Pengaturan Toko';
$depth = 0;
$msg = '';

$toko = $pdo->query("SELECT * FROM pengaturan LIMIT 1")->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $nama = trim($_POST['nama_toko']);
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['telepon']);
    $footer = trim($_POST['footer_struk']);

    if ($toko) {
        $stmt = $pdo->prepare("UPDATE pengaturan SET nama_toko=?, alamat=?, telepon=?, footer_struk=? WHERE id=?");
        $stmt->execute([$nama, $alamat, $telepon, $footer, $toko['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO pengaturan (nama_toko, alamat, telepon, footer_struk) VALUES (?,?,?,?)");
        $stmt->execute([$nama, $alamat, $telepon, $footer]);
    }
    $msg = 'Pengaturan berhasil disimpan.';
    $toko = $pdo->query("SELECT * FROM pengaturan LIMIT 1")->fetch();
}

include __DIR__ . '/includes/header.php';
?>

<h5 class="fw-bold mb-3">Pengaturan Toko</h5>

<?php if ($msg): ?>
    <div class="alert alert-success alert-dismissible fade show small"><?= htmlspecialchars($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Nama Toko</label>
                <input type="text" name="nama_toko" class="form-control" value="<?= htmlspecialchars($toko['nama_toko'] ?? 'MyFruit Official') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Alamat</label>
                <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($toko['alamat'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Telepon</label>
                <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($toko['telepon'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Catatan Kaki Struk</label>
                <input type="text" name="footer_struk" class="form-control" value="<?= htmlspecialchars($toko['footer_struk'] ?? 'Terima kasih telah berbelanja!') ?>">
            </div>
            <button type="submit" class="btn btn-mf"><i class="bi bi-save me-1"></i>Simpan Pengaturan</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
