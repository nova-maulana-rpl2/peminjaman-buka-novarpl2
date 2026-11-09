<?php /* Variabel: $daftar, $kategoriList, $q, $kategori, $stok, $max, $lamaPilihan */ ?>
<section class="page-banner">
    <h1>📖 Pinjam Buku</h1>
    <p>Centang maksimal <?= (int) $max ?> buku, pilih lama peminjaman, lalu tekan “Pinjam Sekarang”.</p>
</section>

<section class="panel">
    <form method="get" action="<?= e(url('pinjam')) ?>" class="filters">
        <input type="hidden" name="r" value="pinjam">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="🔍 Cari judul, penulis, penerbit...">
        <select name="kategori">
            <option value="">Semua kategori</option>
            <?php foreach ($kategoriList as $k): ?>
                <option value="<?= e($k) ?>" <?= $kategori === $k ? 'selected' : '' ?>><?= e($k) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="stok">
            <option value="">Semua stok</option>
            <option value="tersedia" <?= $stok === 'tersedia' ? 'selected' : '' ?>>Tersedia saja</option>
            <option value="habis" <?= $stok === 'habis' ? 'selected' : '' ?>>Habis</option>
        </select>
        <button type="submit" class="btn btn-primary">Cari</button>
        <a href="<?= e(url('pinjam')) ?>" class="btn btn-ghost">Reset</a>
    </form>

    <form method="post" action="<?= e(url('pinjam/proses')) ?>" id="formPinjam">
        <?= csrf_field() ?>

        <div class="book-list" id="bukuPicker" data-max="<?= (int) $max ?>">
            <?php if (!$daftar): ?>
                <p class="empty">Tidak ada buku yang cocok dengan pencarian.</p>
            <?php endif; ?>
            <?php foreach ($daftar as $b): ?>
                <?php $ada = (int) $b['stok'] > 0; ?>
                <label class="book-item<?= $ada ? '' : ' disabled' ?>">
                    <input type="checkbox" name="buku[]" value="<?= (int) $b['id_buku'] ?>" <?= $ada ? '' : 'disabled' ?>>
                    <span class="book-info">
                        <strong><?= e($b['judul_buku']) ?></strong>
                        <small><?= e($b['penulis']) ?> · <?= e($b['penerbit']) ?> (<?= e($b['tahun_terbit']) ?>) · <?= e($b['kategori']) ?></small>
                    </span>
                    <span class="stock <?= $ada ? '' : 'stock-out' ?>"><?= $ada ? 'Stok ' . (int) $b['stok'] : 'Habis' ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="borrow-bar">
            <div class="form-group inline">
                <label for="lama">Lama pinjam</label>
                <select id="lama" name="lama">
                    <?php foreach ($lamaPilihan as $l): ?>
                        <option value="<?= (int) $l ?>" <?= (int) $l === 7 ? 'selected' : '' ?>><?= (int) $l ?> hari</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="selected-info"><span id="selectedCount">0</span> / <?= (int) $max ?> buku dipilih</div>
            <button type="submit" class="btn btn-primary" id="btnPinjam" disabled>Pinjam Sekarang</button>
        </div>
    </form>
</section>
