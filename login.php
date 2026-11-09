<?php /* Variabel: $role, $error, $username */ ?>
<div class="auth-logo">📚</div>
<h1>Perpustakaan Sekolah Digital</h1>
<p class="subtitle">Silakan masuk untuk melanjutkan</p>

<div class="role-tabs">
    <a href="<?= e(url('auth/login', ['role' => 'siswa'])) ?>" class="role-tab <?= $role === 'siswa' ? 'active' : '' ?>">🎓 Login Siswa</a>
    <a href="<?= e(url('auth/login', ['role' => 'admin'])) ?>" class="role-tab <?= $role === 'admin' ? 'active' : '' ?>">🛠️ Login Admin</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(url('auth/login')) ?>" autocomplete="on">
    <?= csrf_field() ?>
    <input type="hidden" name="role" value="<?= e($role) ?>">

    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" placeholder="Masukkan username" required autofocus>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Masuk sebagai <?= $role === 'admin' ? 'Admin' : 'Siswa' ?></button>
</form>

<?php if ($role === 'siswa'): ?>
    <p class="footnote">Belum menjadi anggota? <a href="<?= e(url('auth/daftar')) ?>">Daftar di sini</a></p>
<?php else: ?>
    <p class="footnote">Login admin hanya untuk petugas perpustakaan.</p>
<?php endif; ?>
