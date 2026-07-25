<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 1);

$pageTitle = 'Stok Masuk';
$depth = 1;
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $produkId = (int)$_POST['produk_id'];
    $jumlah = (float)$_POST['jumlah'];
    $hargaBeli = (float)str_replace(['.', ','], ['', '.'], $_POST['harga_beli']);
    $keterangan = trim($_POST['keterangan']);

    if ($produkId && $jumlah > 0) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO stok_masuk (produk_id, jumlah, harga_beli, keterangan, user_id) VALUES (?,?,?,?,?)");
            $stmt->execute([$produkId, $jumlah, $hargaBeli, $keterangan, $_SESSION['user_id']]);

            $stmt = $pdo->prepare("UPDATE produk SET stok = stok + ?" . ($hargaBeli > 0 ? ", harga_beli = ?" : "") . " WHERE id = ?");
            if ($hargaBeli > 0) {
                $stmt->execute([$jumlah, $hargaBeli, $produkId]);
            } else {
                $stmt->execute([$jumlah, $produkId]);
            }

            $pdo->commit();
            $msg = 'Stok berhasil ditambahkan.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = 'error:Gagal menambahkan stok.';
        }
    }
}

$produkList = $pdo->query("SELECT * FROM produk WHERE status='aktif' ORDER BY nama_produk")->fetchAll();
$riwayat = $pdo->query("
    SELECT sm.*, p.nama_produk, p.satuan, u.nama_lengkap
    FROM stok_masuk sm
    JOIN produk p ON p.id = sm.produk_id
    JOIN users u ON u.id = sm.user_id
    ORDER BY sm.id DESC LIMIT 30
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Stok Masuk (Restock)</h5>
</div>

<?php if ($msg): ?>
    <div class="alert alert-<?= str_starts_with($msg,'error:') ? 'danger' : 'success' ?> alert-dismissible fade show small">
        <?= htmlspecialchars(str_replace('error:', '', $msg)) ?>
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Tambah Stok</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Produk</label>
                        <select name="produk_id" class="form-select" required>
                            <option value="">- Pilih Produk -</option>
                            <?php foreach ($produkList as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_produk']) ?> (stok: <?= $p['stok'] ?> <?= $p['satuan'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jumlah Masuk</label>
                        <input type="number" step="0.01" name="jumlah" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Harga Beli Baru (Rp) <span class="text-muted">(opsional)</span></label>
                        <input type="text" name="harga_beli" class="form-control" placeholder="Kosongkan jika tidak berubah">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Misal: pembelian dari supplier A"></textarea>
                    </div>
                    <button type="submit" class="btn btn-mf w-100"><i class="bi bi-box-arrow-in-down me-1"></i>Simpan Stok Masuk</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Riwayat Stok Masuk Terbaru</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle table-responsive-font">
                    <thead><tr><th>Produk</th><th>Jumlah</th><th>Harga Beli</th><th>Oleh</th><th>Waktu</th></tr></thead>
                    <tbody>
                    <?php if (!$riwayat): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada riwayat</td></tr>
                    <?php endif; ?>
                    <?php foreach ($riwayat as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['nama_produk']) ?></td>
                            <td>+<?= $r['jumlah'] ?> <?= htmlspecialchars($r['satuan']) ?></td>
                            <td><?= formatRupiah($r['harga_beli']) ?></td>
                            <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
                            <td class="text-muted small"><?= formatTanggal($r['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
