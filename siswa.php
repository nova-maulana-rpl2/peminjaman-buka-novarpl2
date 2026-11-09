<?php
$__ctl  = current_controller();
$__user = Auth::user();
$__menu = [
    'dashboard' => 'Beranda',
    'pinjam'    => 'Pinjam Buku',
    'kembali'   => 'Pengembalian',
    'riwayat'   => 'Riwayat',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(isset($title) ? $title : 'Siswa') ?> — <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body class="app-siswa">

<header class="topbar">
    <div class="topbar-inner">
        <div class="brand">📚 <?= e(config('app.name')) ?></div>
        <nav class="topnav">
            <?php foreach ($__menu as $key => $label): ?>
                <a href="<?= e(url($key)) ?>" class="<?= $__ctl === $key ? 'active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <form method="post" action="<?= e(url('auth/logout')) ?>" class="topbar-user">
            <?= csrf_field() ?>
            <span>👤 <?= e($__user['nama']) ?></span>
            <button type="submit" class="btn btn-ghost btn-sm">Keluar</button>
        </form>
    </div>
</header>

<main class="container">
    <?php require VIEW_PATH . '/layout/_flash.php'; ?>
    <?= $content ?>
</main>

<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
