<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $u = $stmt->fetch();

        if ($u && $u['status'] === 'aktif' && password_verify($password, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $u['id'];
            $_SESSION['username'] = $u['username'];
            $_SESSION['nama']     = $u['nama_lengkap'];
            $_SESSION['role']     = $u['role'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah, atau akun nonaktif.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MyFruit Official POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
    <div class="card login-card shadow-lg">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-basket3-fill text-mf" style="font-size:2.5rem;"></i>
                <h4 class="fw-bold mt-2 mb-0">MyFruit Official</h4>
                <p class="text-muted small">Sistem Kasir & Manajemen Toko</p>
            </div>

            <?php if (isset($_GET['expired'])): ?>
                <div class="alert alert-warning py-2 small">Sesi Anda telah berakhir, silakan login kembali.</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-mf w-100 fw-semibold">Masuk</button>
            </form>
        </div>
        <div class="card-footer text-center small text-muted py-2">
            &copy; <?= date('Y') ?> MyFruit Official
        </div>
    </div>
</div>
</body>
</html>
