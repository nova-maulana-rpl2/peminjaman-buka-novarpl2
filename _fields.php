<?php
/**
 * Partial field anggota — dipakai oleh form pendaftaran publik (auth/daftar)
 * dan form tambah/edit anggota admin.
 * Variabel: $form, $errors, (opsional) $mode = 'tambah' | 'edit'
 */
$__edit = isset($mode) && $mode === 'edit';
?>
<div class="form-group">
    <label for="nama_anggota">Nama Lengkap</label>
    <input type="text" id="nama_anggota" name="nama_anggota" maxlength="100" class="<?= err_class($errors, 'nama_anggota') ?>" value="<?= fv($form, 'nama_anggota') ?>" required>
    <?= field_error($errors, 'nama_anggota') ?>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="kelas">Kelas</label>
        <input type="text" id="kelas" name="kelas" maxlength="30" placeholder="Contoh: XI RPL 1" class="<?= err_class($errors, 'kelas') ?>" value="<?= fv($form, 'kelas') ?>" required>
        <?= field_error($errors, 'kelas') ?>
    </div>
    <div class="form-group">
        <label for="no_telp">No. Telepon</label>
        <input type="text" id="no_telp" name="no_telp" maxlength="20" placeholder="08xxxxxxxxxx" class="<?= err_class($errors, 'no_telp') ?>" value="<?= fv($form, 'no_telp') ?>" required>
        <?= field_error($errors, 'no_telp') ?>
    </div>
</div>

<div class="form-group">
    <label for="alamat">Alamat</label>
    <textarea id="alamat" name="alamat" rows="2" maxlength="500" class="<?= err_class($errors, 'alamat') ?>"><?= fv($form, 'alamat') ?></textarea>
    <?= field_error($errors, 'alamat') ?>
</div>

<div class="form-group">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" maxlength="30" autocomplete="username" class="<?= err_class($errors, 'username') ?>" value="<?= fv($form, 'username') ?>" required>
    <?= field_error($errors, 'username') ?>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="password">Password<?= $__edit ? ' (kosongkan jika tidak diubah)' : '' ?></label>
        <input type="password" id="password" name="password" minlength="6" autocomplete="new-password" class="<?= err_class($errors, 'password') ?>" <?= $__edit ? '' : 'required' ?>>
        <?= field_error($errors, 'password') ?>
    </div>
    <div class="form-group">
        <label for="konfirmasi">Konfirmasi Password</label>
        <input type="password" id="konfirmasi" name="konfirmasi" minlength="6" autocomplete="new-password" class="<?= err_class($errors, 'konfirmasi') ?>" <?= $__edit ? '' : 'required' ?>>
        <?= field_error($errors, 'konfirmasi') ?>
    </div>
</div>
