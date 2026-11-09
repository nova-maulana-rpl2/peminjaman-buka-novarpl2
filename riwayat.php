<?php /* Variabel: $daftar, $q, $status, $dari, $sampai */ ?>
<section class="page-banner">
    <h1>🕘 Riwayat Peminjaman</h1>
    <p>Semua transaksi peminjaman atas nama Anda.</p>
</section>

<section class="panel">
    <form method="get" action="<?= e(url('riwayat')) ?>" class="filters">
        <input type="hidden" name="r" value="riwayat">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="🔍 Cari judul buku...">
        <select name="status">
            <option value="">Semua status</option>
            <?php foreach (['Dipinjam', 'Terlambat', 'Dikembalikan'] as $s): ?>
                <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
        <label class="inline-label">Dari <input type="date" name="dari" value="<?= e($dari) ?>"></label>
        <label class="inline-label">Sampai <input type="date" name="sampai" value="<?= e($sampai) ?>"></label>
        <button type="submit" class="btn btn-primary">Cari</button>
        <a href="<?= e(url('riwayat')) ?>" class="btn btn-ghost">Reset</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Buku</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Tgl Kembali</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (!$daftar): ?>
                <tr><td colspan="6" class="empty">Belum ada riwayat yang cocok.</td></tr>
            <?php endif; ?>
            <?php foreach ($daftar as $r): ?>
                <?php $st = status_tampil($r); ?>
                <tr>
                    <td>#<?= (int) $r['id_peminjaman'] ?></td>
                    <td><?= e($r['daftar_buku']) ?></td>
                    <td><?= e(tgl($r['tanggal_pinjam'])) ?></td>
                    <td><?= e(tgl($r['tanggal_jatuh_tempo'])) ?></td>
                    <td><?= e(tgl($r['tanggal_kembali'])) ?></td>
                    <td><?= badge($st) ?><?php if ($st === 'Terlambat'): ?> <small class="muted"><?= hari_terlambat($r) ?> hari</small><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
