<?php /* Variabel: $form, $errors. Layout auth lebar. */ $wide = true; ?>
<div class="auth-logo">📝</div>
<h1>Daftar Anggota Baru</h1>
<p class="subtitle">Isi data berikut untuk membuat akun perpustakaan</p>

<form method="post" action="<?= e(url('auth/daftar')) ?>" novalidate>
    <?= csrf_field() ?>
    <?php require VIEW_PATH . '/admin/anggota/_fields.php'; ?>
    <button type="submit" class="btn btn-primary btn-block">Daftar Sekarang</button>
</form>

<p class="footnote">Sudah punya akun? <a href="<?= e(url('auth/login', ['role' => 'siswa'])) ?>">Login di sini</a></p>
