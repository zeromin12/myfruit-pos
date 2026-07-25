<?php
/**
 * Header HTML utama (dipanggil setelah <body>).
 * Variabel yang dibutuhkan sebelum include:
 * $pageTitle (string), $depth (int, jumlah folder dari root)
 */
$depth = $depth ?? 0;
$prefix = str_repeat('../', $depth);
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'MyFruit Official') ?> - MyFruit Official POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= $prefix ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<?php include __DIR__ . '/sidebar.php'; ?>

<div id="content-wrapper">
    <nav id="topbar" class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" id="btnToggleSidebar">
                <i class="bi bi-list"></i>
            </button>
            <span class="fw-semibold text-mf d-none d-md-inline"><?= htmlspecialchars($pageTitle ?? '') ?></span>
        </div>
        <div class="dropdown">
            <button class="btn btn-sm btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle fs-5"></i>
                <span class="d-none d-sm-inline"><?= htmlspecialchars($user['nama']) ?></span>
                <span class="badge bg-mf d-none d-sm-inline text-uppercase" style="font-size:.65rem;"><?= htmlspecialchars($user['role']) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= $prefix ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
            </ul>
        </div>
    </nav>
    <main id="page-content">
