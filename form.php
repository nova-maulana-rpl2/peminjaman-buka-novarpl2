<?php /* Variabel: $mode, $idTransaksi, $form, $errors, $anggotaList, $bukuList, $bukuTerpilih, $max */ ?>
<section class="panel form-panel">
    <form method="post" action="<?= e($mode === 'edit' ? url('transaksi/edit', ['id' => $idTransaksi]) : url('transaksi/tambah')) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="id_anggota">Anggota Peminjam</label>
            <select id="id_anggota" name="id_anggota" class="<?= err_class($errors, 'id_anggota') ?>" required>
                <option value="">-- Pilih anggota --</option>
                <?php foreach ($anggotaList as $a): ?>
                    <option value="<?= (int) $a['id_anggota'] ?>" <?= (string) $form['id_anggota'] === (string) $a['id_anggota'] ? 'selected' : '' ?>>
                        <?= e($a['nama_anggota']) ?> — <?= e($a['kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= field_error($errors, 'id_anggota') ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="tanggal_pinjam">Tanggal Pinjam</label>
                <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" class="<?= err_class($errors, 'tanggal_pinjam') ?>" value="<?= fv($form, 'tanggal_pinjam') ?>" required>
                <?= field_error($errors, 'tanggal_pinjam') ?>
            </div>
            <div class="form-group">
                <label for="tanggal_jatuh_tempo">Jatuh Tempo</label>
                <input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" class="<?= err_class($errors, 'tanggal_jatuh_tempo') ?>" value="<?= fv($form, 'tanggal_jatuh_tempo') ?>" required>
                <?= field_error($errors, 'tanggal_jatuh_tempo') ?>
            </div>
        </div>

        <?php if ($mode === 'tambah'): ?>
            <div class="form-group">
                <label>Pilih Buku <small class="muted">(maksimal <?= (int) $max ?>, hanya yang stoknya tersedia)</small></label>
                <input type="search" class="book-filter" placeholder="🔍 Cari judul buku..." data-target="#bukuPicker">
                <div class="book-list<?= isset($errors['buku']) ? ' input-error' : '' ?>" id="bukuPicker" data-max="<?= (int) $max ?>">
                    <?php foreach ($bukuList as $b): ?>
                        <?php $ada = (int) $b['stok'] > 0; ?>
                        <label class="book-item<?= $ada ? '' : ' disabled' ?>" data-judul="<?= e(strtolower($b['judul_buku'] . ' ' . $b['penulis'])) ?>">
                            <input type="checkbox" name="buku[]" value="<?= (int) $b['id_buku'] ?>"
                                <?= $ada ? '' : 'disabled' ?>
                                <?= in_array((int) $b['id_buku'], array_map('intval', $form['buku']), true) ? 'checked' : '' ?>>
                            <span class="book-info"><strong><?= e($b['judul_buku']) ?></strong><small><?= e($b['penulis']) ?> · <?= e($b['kategori']) ?></small></span>
                            <span class="stock <?= $ada ? '' : 'stock-out' ?>">Stok <?= (int) $b['stok'] ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?= field_error($errors, 'buku') ?>
            </div>
        <?php else: ?>
            <div class="form-group">
                <label>Buku dalam transaksi <small class="muted">(tidak dapat diubah; hapus & buat ulang jika salah)</small></label>
                <ul class="plain-list">
                    <?php foreach ($bukuTerpilih as $b): ?>
                        <li>📖 <?= e($b['judul_buku']) ?> <small class="muted">— <?= e($b['penulis']) ?></small></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-actions">
            <a href="<?= e(url('transaksi')) ?>" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary"><?= $mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Transaksi' ?></button>
        </div>
    </form>
</section>
