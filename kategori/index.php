<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 1);

$pageTitle = 'Manajemen Kategori';
$depth = 1;
$msg = '';

// Tambah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    csrfCheck();
    $nama = trim($_POST['nama_kategori']);
    if ($nama !== '') {
        $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
        $stmt->execute([$nama]);
        $msg = 'success:Kategori berhasil ditambahkan.';
    }
}
// Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    csrfCheck();
    $id = (int)$_POST['id'];
    $nama = trim($_POST['nama_kategori']);
    if ($nama !== '') {
        $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = ? WHERE id = ?");
        $stmt->execute([$nama, $id]);
        $msg = 'success:Kategori berhasil diperbarui.';
    }
}
// Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM kategori WHERE id = ?");
        $stmt->execute([$id]);
        $msg = 'success:Kategori berhasil dihapus.';
    } catch (PDOException $e) {
        $msg = 'danger:Kategori tidak dapat dihapus (masih dipakai produk).';
    }
}

$kategoriList = $pdo->query("
    SELECT k.*, (SELECT COUNT(*) FROM produk p WHERE p.kategori_id = k.id) jumlah_produk
    FROM kategori k ORDER BY k.nama_kategori ASC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Manajemen Kategori</h5>
    <button class="btn btn-mf btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg me-1"></i>Tambah Kategori
    </button>
</div>

<?php if ($msg): [$type, $text] = explode(':', $msg, 2); ?>
    <div class="alert alert-<?= $type ?> alert-dismissible fade show small" role="alert">
        <?= htmlspecialchars($text) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>#</th><th>Nama Kategori</th><th>Jumlah Produk</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php if (!$kategoriList): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada kategori</td></tr>
            <?php endif; ?>
            <?php foreach ($kategoriList as $i => $k): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($k['nama_kategori']) ?></td>
                    <td><span class="badge bg-secondary"><?= $k['jumlah_produk'] ?></span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $k['id'] ?>"><i class="bi bi-pencil"></i></button>
                        <a href="?hapus=<?= $k['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kategori ini?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>

                <div class="modal fade" id="modalEdit<?= $k['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" class="modal-content">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="aksi" value="edit">
                            <input type="hidden" name="id" value="<?= $k['id'] ?>">
                            <div class="modal-header"><h6 class="modal-title">Edit Kategori</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <label class="form-label small">Nama Kategori</label>
                                <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($k['nama_kategori']) ?>" required>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-mf btn-sm">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="aksi" value="tambah">
            <div class="modal-header"><h6 class="modal-title">Tambah Kategori</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label small">Nama Kategori</label>
                <input type="text" name="nama_kategori" class="form-control" required autofocus>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-mf btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
