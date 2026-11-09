<?php /* Variabel: $stat, $terbaru */ ?>
<div class="stats">
    <div class="stat-card"><span class="stat-icon">📚</span><div><h3>Total Buku</h3><div class="stat-num"><?= (int) $stat['buku'] ?></div></div></div>
    <div class="stat-card"><span class="stat-icon">👥</span><div><h3>Total Anggota</h3><div class="stat-num"><?= (int) $stat['anggota'] ?></div></div></div>
    <div class="stat-card"><span class="stat-icon">📖</span><div><h3>Sedang Dipinjam</h3><div class="stat-num"><?= (int) $stat['dipinjam'] ?></div></div></div>
    <div class="stat-card stat-warn"><span class="stat-icon">⏰</span><div><h3>Terlambat</h3><div class="stat-num"><?= (int) $stat['terlambat'] ?></div></div></div>
</div>

<section class="panel">
    <div class="panel-head">
        <h2>Transaksi Terbaru</h2>
        <a class="btn btn-primary btn-sm" href="<?= e(url('transaksi/tambah')) ?>">+ Transaksi Baru</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>No</th><th>Anggota</th><th>Buku</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (!$terbaru): ?>
                <tr><td colspan="6" class="empty">Belum ada transaksi.</td></tr>
            <?php endif; ?>
            <?php foreach ($terbaru as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($r['nama_anggota']) ?></td>
                    <td><?= e($r['daftar_buku']) ?></td>
                    <td><?= e(tgl($r['tanggal_pinjam'])) ?></td>
                    <td><?= e(tgl($r['tanggal_jatuh_tempo'])) ?></td>
                    <td><?= badge(status_tampil($r)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="quick-links">
    <a href="<?= e(url('buku')) ?>">📚 Kelola Data Buku</a>
    <a href="<?= e(url('anggota')) ?>">👥 Kelola Anggota</a>
    <a href="<?= e(url('transaksi', ['status' => 'Terlambat'])) ?>">⏰ Lihat yang Terlambat</a>
</section>
