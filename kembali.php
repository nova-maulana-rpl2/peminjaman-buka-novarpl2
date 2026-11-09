<?php /* Variabel: $aktif */ ?>
<section class="page-banner">
    <h1>↩️ Pengembalian Buku</h1>
    <p>Daftar buku yang sedang Anda pinjam. Tekan “Kembalikan” setelah buku diserahkan.</p>
</section>

<section class="panel">
    <?php if (!$aktif): ?>
        <p class="empty">Tidak ada buku yang perlu dikembalikan. 🎉</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Buku</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($aktif as $r): ?>
                    <?php $st = status_tampil($r); ?>
                    <tr>
                        <td><strong><?= e($r['daftar_buku']) ?></strong></td>
                        <td><?= e(tgl($r['tanggal_pinjam'])) ?></td>
                        <td><?= e(tgl($r['tanggal_jatuh_tempo'])) ?></td>
                        <td><?= badge($st) ?><?php if ($st === 'Terlambat'): ?> <small class="muted"><?= hari_terlambat($r) ?> hari</small><?php endif; ?></td>
                        <td class="actions">
                            <form method="post" action="<?= e(url('kembali/proses')) ?>" data-confirm="Kembalikan buku pada transaksi #<?= (int) $r['id_peminjaman'] ?>?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id_peminjaman'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Kembalikan</button>
                            </form>
                            <?php if ($r['tanggal_pinjam'] === today()): ?>
                                <form method="post" action="<?= e(url('kembali/batalkan')) ?>" data-confirm="Batalkan peminjaman #<?= (int) $r['id_peminjaman'] ?>? Buku dianggap tidak jadi dipinjam.">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $r['id_peminjaman'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm">Batalkan</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
