<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 1);

$pageTitle = 'Data Produk';
$depth = 1;

$cari = trim($_GET['cari'] ?? '');
$kategoriFilter = $_GET['kategori'] ?? '';

$sql = "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON k.id = p.kategori_id WHERE 1=1";
$params = [];
if ($cari !== '') {
    $sql .= " AND (p.nama_produk LIKE ? OR p.kode_produk LIKE ?)";
    $params[] = "%$cari%"; $params[] = "%$cari%";
}
if ($kategoriFilter !== '') {
    $sql .= " AND p.kategori_id = ?";
    $params[] = $kategoriFilter;
}
$sql .= " ORDER BY p.nama_produk ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produkList = $stmt->fetchAll();

$kategoriList = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();

$msg = $_GET['msg'] ?? '';
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h5 class="fw-bold mb-0">Data Produk</h5>
    <a href="tambah.php" class="btn btn-mf btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Produk</a>
</div>

<?php if ($msg === 'sukses'): ?>
    <div class="alert alert-success alert-dismissible fade show small">Data produk berhasil disimpan.<button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'hapus'): ?>
    <div class="alert alert-success alert-dismissible fade show small">Produk berhasil dihapus.<button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-12 col-md-5">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari nama atau kode produk..." value="<?= htmlspecialchars($cari) ?>">
            </div>
            <div class="col-8 col-md-4">
                <select name="kategori" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $kategoriFilter == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-4 col-md-3">
                <button class="btn btn-sm btn-outline-secondary w-100" type="submit"><i class="bi bi-search"></i> Cari</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle table-responsive-font">
            <thead>
                <tr><th>Kode</th><th>Nama Produk</th><th>Kategori</th><th>Harga Jual</th><th>Stok</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
            <?php if (!$produkList): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada produk ditemukan</td></tr>
            <?php endif; ?>
            <?php foreach ($produkList as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['kode_produk']) ?></td>
                    <td><?= htmlspecialchars($p['nama_produk']) ?></td>
                    <td><?= htmlspecialchars($p['nama_kategori'] ?? '-') ?></td>
                    <td><?= formatRupiah($p['harga_jual']) ?></td>
                    <td>
                        <?= rtrim(rtrim(number_format($p['stok'],2,',','.'), '0'), ',') ?> <?= htmlspecialchars($p['satuan']) ?>
                        <?php if ($p['stok'] <= $p['stok_minimum']): ?>
                            <span class="badge badge-stok-rendah ms-1">Menipis</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['status'] === 'aktif'): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <a href="hapus.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus produk ini?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
