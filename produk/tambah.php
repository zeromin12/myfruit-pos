<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 1);

$pageTitle = 'Tambah Produk';
$depth = 1;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $nama = trim($_POST['nama_produk']);
    $kategoriId = $_POST['kategori_id'] ?: null;
    $satuan = trim($_POST['satuan']);
    $hargaBeli = (float)str_replace(['.', ','], ['', '.'], $_POST['harga_beli']);
    $hargaJual = (float)str_replace(['.', ','], ['', '.'], $_POST['harga_jual']);
    $stok = (float)$_POST['stok'];
    $stokMin = (float)$_POST['stok_minimum'];

    if ($nama === '' || $hargaJual <= 0) {
        $error = 'Nama produk dan harga jual wajib diisi dengan benar.';
    } else {
        $kode = trim($_POST['kode_produk']) ?: generateKodeProduk($pdo);

        // Upload gambar (opsional)
        $namaFile = null;
        if (!empty($_FILES['gambar']['name'])) {
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $namaFile = 'produk_' . time() . '_' . rand(100,999) . '.' . $ext;
                $target = __DIR__ . '/../assets/img/' . $namaFile;
                move_uploaded_file($_FILES['gambar']['tmp_name'], $target);
            }
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO produk (kode_produk, nama_produk, kategori_id, satuan, harga_beli, harga_jual, stok, stok_minimum, gambar) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$kode, $nama, $kategoriId, $satuan, $hargaBeli, $hargaJual, $stok, $stokMin, $namaFile]);
            header('Location: index.php?msg=sukses');
            exit;
        } catch (PDOException $e) {
            $error = 'Kode produk sudah digunakan, silakan gunakan kode lain.';
        }
    }
}

$kategoriList = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$kodeSaran = generateKodeProduk($pdo);

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Tambah Produk</h5>
    <a href="index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger small"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kode Produk</label>
                    <input type="text" name="kode_produk" class="form-control" placeholder="<?= htmlspecialchars($kodeSaran) ?> (otomatis jika kosong)">
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-semibold">Nama Produk *</label>
                    <input type="text" name="nama_produk" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kategori</label>
                    <select name="kategori_id" class="form-select">
                        <option value="">- Pilih Kategori -</option>
                        <?php foreach ($kategoriList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Satuan</label>
                    <select name="satuan" class="form-select">
                        <option value="kg">Kilogram (kg)</option>
                        <option value="pcs">Pieces (pcs)</option>
                        <option value="ikat">Ikat</option>
                        <option value="pack">Pack</option>
                        <option value="box">Box</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Gambar Produk</label>
                    <input type="file" name="gambar" class="form-control" accept="image/*">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Harga Beli (Rp)</label>
                    <input type="text" name="harga_beli" class="form-control" placeholder="0" value="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Harga Jual (Rp) *</label>
                    <input type="text" name="harga_jual" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Stok Awal</label>
                    <input type="number" step="0.01" name="stok" class="form-control" value="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Stok Minimum</label>
                    <input type="number" step="0.01" name="stok_minimum" class="form-control" value="5">
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-mf"><i class="bi bi-save me-1"></i>Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
