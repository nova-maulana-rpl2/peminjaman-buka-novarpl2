<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kesalahan <?= (int) $kode ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body class="auth-page">
    <main class="auth-card" style="text-align:center">
        <div class="auth-logo">⚠️</div>
        <h1>Kesalahan <?= (int) $kode ?></h1>
        <p class="subtitle"><?= e($pesan) ?></p>
        <?php if ($debug && $detail !== ''): ?>
            <pre class="debug-box"><?= e($detail) ?></pre>
        <?php endif; ?>
        <p><a class="btn btn-primary" href="<?= e(url('')) ?>">Kembali ke Beranda</a></p>
    </main>
</body>
</html>
