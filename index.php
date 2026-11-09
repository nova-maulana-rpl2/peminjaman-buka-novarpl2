<?php /* Variabel: $daftar, $q, $status, $dari, $sampai */ ?>
<section class="panel">
    <div class="panel-head">
        <h2>Daftar Transaksi <span class="count"><?= count($daftar) ?></span></h2>
        <a class="btn btn-primary" href="<?= e(url('transaksi/tambah')) ?>">+ Tambah Transaksi</a>
    </div>

    <form method="get" action="<?= e(url('transaksi')) ?>" class="filters">
        <input type="hidden" name="r" value="transaksi">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="🔍 Cari nama anggota / judul buku...">
        <select name="status">
            <option value="">Semua status</option>
            <?php foreach (['Dipinjam', 'Terlambat', 'Dikembalikan'] as $s): ?>
                <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
        <label class="inline-label">Dari <input type="date" name="dari" value="<?= e($dari) ?>"></label>
        <label class="inline-label">Sampai <input type="date" name="sampai" value="<?= e($sampai) ?>"></label>
        <button type="submit" class="btn btn-primary">Cari</button>
        <a href="<?= e(url('transaksi')) ?>" class="btn btn-ghost">Reset</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Anggota</th><th>Buku</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Tgl Kembali</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (!$daftar): ?>
                <tr><td colspan="8" class="empty">Tidak ada transaksi yang cocok.</td></tr>
            <?php endif; ?>
            <?php foreach ($daftar as $r): ?>
                <?php $st = status_tampil($r); ?>
                <tr>
                    <td>#<?= (int) $r['id_peminjaman'] ?></td>
                    <td><strong><?= e($r['nama_anggota']) ?></strong><br><small class="muted"><?= e($r['kelas']) ?></small></td>
                    <td><?= e($r['daftar_buku']) ?></td>
                    <td><?= e(tgl($r['tanggal_pinjam'])) ?></td>
                    <td><?= e(tgl($r['tanggal_jatuh_tempo'])) ?></td>
                    <td><?= e(tgl($r['tanggal_kembali'])) ?></td>
                    <td><?= badge($st) ?><?php if ($st === 'Terlambat'): ?><br><small class="muted"><?= hari_terlambat($r) ?> hari</small><?php endif; ?></td>
                    <td class="actions">
                        <a class="btn btn-ghost btn-sm" href="<?= e(url('transaksi/detail', ['id' => $r['id_peminjaman']])) ?>">Detail</a>
                        <?php if ($r['status'] === 'Dipinjam'): ?>
                            <a class="btn btn-warning btn-sm" href="<?= e(url('transaksi/edit', ['id' => $r['id_peminjaman']])) ?>">Edit</a>
                            <form method="post" action="<?= e(url('transaksi/kembalikan')) ?>" data-confirm="Tandai buku sudah dikembalikan?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id_peminjaman'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Kembalikan</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= e(url('transaksi/hapus')) ?>" data-confirm="Hapus transaksi #<?= (int) $r['id_peminjaman'] ?>?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $r['id_peminjaman'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
