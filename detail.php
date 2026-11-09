<?php /* Variabel: $t, $buku */ $st = status_tampil($t); ?>
<section class="panel form-panel">
    <dl class="detail-grid">
        <dt>ID Transaksi</dt><dd>#<?= (int) $t['id_peminjaman'] ?></dd>
        <dt>Anggota</dt><dd><?= e($t['nama_anggota']) ?> (<?= e($t['kelas']) ?>)</dd>
        <dt>Tanggal Pinjam</dt><dd><?= e(tgl($t['tanggal_pinjam'])) ?></dd>
        <dt>Jatuh Tempo</dt><dd><?= e(tgl($t['tanggal_jatuh_tempo'])) ?></dd>
        <dt>Tanggal Kembali</dt><dd><?= e(tgl($t['tanggal_kembali'])) ?></dd>
        <dt>Status</dt><dd><?= badge($st) ?><?php if ($st === 'Terlambat'): ?> <small class="muted">(<?= hari_terlambat($t) ?> hari)</small><?php endif; ?></dd>
    </dl>

    <h3 class="sub-title">Buku yang dipinjam</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>No</th><th>Judul</th><th>Penulis</th><th>Jumlah</th></tr></thead>
            <tbody>
            <?php foreach ($buku as $i => $b): ?>
                <tr><td><?= $i + 1 ?></td><td><?= e($b['judul_buku']) ?></td><td><?= e($b['penulis']) ?></td><td><?= (int) $b['jumlah'] ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="form-actions">
        <a href="<?= e(url('transaksi')) ?>" class="btn btn-ghost">← Kembali</a>
    </div>
</section>
