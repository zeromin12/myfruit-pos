<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 1);

$pageTitle = 'Manajemen Pengguna';
$depth = 1;
$msg = '';

// Tambah pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    csrfCheck();
    $username = trim($_POST['username']);
    $nama = trim($_POST['nama_lengkap']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($username === '' || $nama === '' || strlen($password) < 6) {
        $msg = 'danger:Lengkapi semua data. Password minimal 6 karakter.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?,?,?,?)");
            $stmt->execute([$username, $hash, $nama, $role]);
            $msg = 'success:Pengguna baru berhasil ditambahkan.';
        } catch (PDOException $e) {
            $msg = 'danger:Username sudah digunakan.';
        }
    }
}

// Edit pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'edit') {
    csrfCheck();
    $id = (int)$_POST['id'];
    $nama = trim($_POST['nama_lengkap']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $password = $_POST['password'];

    if ($password !== '') {
        if (strlen($password) < 6) {
            $msg = 'danger:Password baru minimal 6 karakter.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap=?, role=?, status=?, password=? WHERE id=?");
            $stmt->execute([$nama, $role, $status, $hash, $id]);
            $msg = 'success:Data pengguna berhasil diperbarui.';
        }
    } else {
        $stmt = $pdo->prepare("UPDATE users SET nama_lengkap=?, role=?, status=? WHERE id=?");
        $stmt->execute([$nama, $role, $status, $id]);
        $msg = 'success:Data pengguna berhasil diperbarui.';
    }
}

// Hapus pengguna
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id === (int)$_SESSION['user_id']) {
        $msg = 'danger:Anda tidak dapat menghapus akun sendiri.';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $msg = 'success:Pengguna berhasil dihapus.';
        } catch (PDOException $e) {
            // Jika pengguna sudah punya riwayat transaksi, nonaktifkan saja
            $stmt = $pdo->prepare("UPDATE users SET status='nonaktif' WHERE id=?");
            $stmt->execute([$id]);
            $msg = 'success:Pengguna memiliki riwayat transaksi, sehingga akun dinonaktifkan.';
        }
    }
}

$userList = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Manajemen Pengguna</h5>
    <button class="btn btn-mf btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="bi bi-plus-lg me-1"></i>Tambah Pengguna</button>
</div>

<?php if ($msg): [$type, $text] = explode(':', $msg, 2); ?>
    <div class="alert alert-<?= $type ?> alert-dismissible fade show small">
        <?= htmlspecialchars($text) ?><button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($userList as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                    <td><span class="badge bg-mf text-uppercase"><?= htmlspecialchars($u['role']) ?></span></td>
                    <td><?= $u['status'] === 'aktif' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $u['id'] ?>"><i class="bi bi-pencil"></i></button>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <a href="?hapus=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus pengguna ini?')"><i class="bi bi-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>

                <div class="modal fade" id="modalEdit<?= $u['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" class="modal-content">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="aksi" value="edit">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <div class="modal-header"><h6 class="modal-title">Edit Pengguna: <?= htmlspecialchars($u['username']) ?></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <div class="mb-2">
                                    <label class="form-label small">Nama Lengkap</label>
                                    <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($u['nama_lengkap']) ?>" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Role</label>
                                    <select name="role" class="form-select">
                                        <option value="kasir" <?= $u['role']==='kasir'?'selected':'' ?>>Kasir</option>
                                        <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Admin</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="aktif" <?= $u['status']==='aktif'?'selected':'' ?>>Aktif</option>
                                        <option value="nonaktif" <?= $u['status']==='nonaktif'?'selected':'' ?>>Nonaktif</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Password Baru <span class="text-muted">(kosongkan jika tidak diubah)</span></label>
                                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter">
                                </div>
                            </div>
                            <div class="modal-footer"><button type="submit" class="btn btn-mf btn-sm">Simpan</button></div>
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
            <div class="modal-header"><h6 class="modal-title">Tambah Pengguna</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Role</label>
                    <select name="role" class="form-select">
                        <option value="kasir">Kasir</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-mf btn-sm">Simpan</button></div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
