<?php
$__ctl  = current_controller();
$__user = Auth::user();
$__menu = [
    'dashboard' => ['Dashboard', '🏠'],
    'buku'      => ['Data Buku', '📚'],
    'anggota'   => ['Kelola Anggota', '👥'],
    'transaksi' => ['Transaksi', '🔄'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(isset($title) ? $title : 'Admin') ?> — <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body class="app-admin">

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">📚 Perpustakaan<small>Admin Panel</small></div>
    <nav>
        <?php foreach ($__menu as $key => $m): ?>
            <a href="<?= e(url($key)) ?>" class="<?= $__ctl === $key ? 'active' : '' ?>">
                <span><?= $m[1] ?></span> <?= e($m[0]) ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <form method="post" action="<?= e(url('auth/logout')) ?>" class="sidebar-logout">
        <?= csrf_field() ?>
        <button type="submit">🚪 Keluar</button>
    </form>
</aside>

<div class="main">
    <header class="page-head">
        <button type="button" class="menu-toggle" id="menuToggle" aria-label="Buka menu">☰</button>
        <div class="page-title">
            <h1><?= e(isset($title) ? $title : '') ?></h1>
            <p><?= e(config('app.name')) ?></p>
        </div>
        <div class="profile">👤 <?= e($__user['nama']) ?> <span class="role-pill">Admin</span></div>
    </header>

    <?php require VIEW_PATH . '/layout/_flash.php'; ?>

    <?= $content ?>
</div>

<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
