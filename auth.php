<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(isset($title) ? $title : 'Masuk') ?> — <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body class="auth-page">
    <main class="auth-card<?= !empty($wide) ? ' wide' : '' ?>">
        <?php require VIEW_PATH . '/layout/_flash.php'; ?>
        <?= $content ?>
    </main>
    <script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
