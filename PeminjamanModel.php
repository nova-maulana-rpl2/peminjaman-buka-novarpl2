<?php
/**
 * Akses tabel `peminjaman` + `detail_peminjaman`.
 * Semua perubahan stok dilakukan di dalam transaksi database (aman dari stok minus).
 * Kesalahan aturan bisnis dilempar sebagai RuntimeException berpesan ramah pengguna.
 */
class PeminjamanModel extends Model
{
    /**
     * Pencarian transaksi dengan banyak kondisi.
     * Kunci $f: id_anggota, q, status (Dipinjam|Terlambat|Dikembalikan), dari, sampai.
     */
    public function cari(array $f = [])
    {
        $today  = today();
        $sql    = 'SELECT p.id_peminjaman, p.id_anggota, p.tanggal_pinjam, p.tanggal_jatuh_tempo,
                          p.tanggal_kembali, p.status, a.nama_anggota, a.kelas,
                          (SELECT GROUP_CONCAT(b.judul_buku ORDER BY b.judul_buku SEPARATOR \', \')
                             FROM detail_peminjaman d JOIN buku b ON b.id_buku = d.id_buku
                            WHERE d.id_peminjaman = p.id_peminjaman) AS daftar_buku
                     FROM peminjaman p
                     JOIN anggota a ON a.id_anggota = p.id_anggota
                    WHERE 1=1';
        $params = [];

        if (!empty($f['id_anggota'])) {
            $sql .= ' AND p.id_anggota = ?';
            $params[] = (int) $f['id_anggota'];
        }
        if (!empty($f['q'])) {
            $like = '%' . $f['q'] . '%';
            $sql .= ' AND (a.nama_anggota LIKE ? OR EXISTS (
                        SELECT 1 FROM detail_peminjaman d2 JOIN buku b2 ON b2.id_buku = d2.id_buku
                         WHERE d2.id_peminjaman = p.id_peminjaman AND b2.judul_buku LIKE ?))';
            array_push($params, $like, $like);
        }
        if (!empty($f['status'])) {
            if ($f['status'] === 'Dipinjam') {
                $sql .= " AND p.status = 'Dipinjam' AND p.tanggal_jatuh_tempo >= ?";
                $params[] = $today;
            } elseif ($f['status'] === 'Terlambat') {
                $sql .= " AND p.status = 'Dipinjam' AND p.tanggal_jatuh_tempo < ?";
                $params[] = $today;
            } elseif ($f['status'] === 'Dikembalikan') {
                $sql .= " AND p.status = 'Dikembalikan'";
            }
        }
        if (!empty($f['dari']) && valid_date($f['dari'])) {
            $sql .= ' AND p.tanggal_pinjam >= ?';
            $params[] = $f['dari'];
        }
        if (!empty($f['sampai']) && valid_date($f['sampai'])) {
            $sql .= ' AND p.tanggal_pinjam <= ?';
            $params[] = $f['sampai'];
        }

        $sql .= ' ORDER BY p.id_peminjaman DESC';
        return $this->fetchAll($sql, $params);
    }

    public function terbaru($limit = 5)
    {
        $limit = max(1, (int) $limit);
        return $this->fetchAll(
            'SELECT p.id_peminjaman, p.tanggal_pinjam, p.tanggal_jatuh_tempo, p.tanggal_kembali, p.status,
                    a.nama_anggota,
                    (SELECT GROUP_CONCAT(b.judul_buku ORDER BY b.judul_buku SEPARATOR \', \')
                       FROM detail_peminjaman d JOIN buku b ON b.id_buku = d.id_buku
                      WHERE d.id_peminjaman = p.id_peminjaman) AS daftar_buku
               FROM peminjaman p JOIN anggota a ON a.id_anggota = p.id_anggota
              ORDER BY p.id_peminjaman DESC LIMIT ' . $limit
        );
    }

    public function temukan($id)
    {
        return $this->fetchOne(
            'SELECT p.*, a.nama_anggota, a.kelas FROM peminjaman p
               JOIN anggota a ON a.id_anggota = p.id_anggota WHERE p.id_peminjaman = ?',
            [(int) $id]
        );
    }

    public function detail($id)
    {
        return $this->fetchAll(
            'SELECT d.id_buku, d.jumlah, b.judul_buku, b.penulis
               FROM detail_peminjaman d JOIN buku b ON b.id_buku = d.id_buku
              WHERE d.id_peminjaman = ? ORDER BY b.judul_buku',
            [(int) $id]
        );
    }

    /* ---------- Statistik (null = semua anggota) ---------- */
    public function hitungSemua($idAnggota = null)
    {
        if ($idAnggota === null) {
            return (int) $this->fetchValue('SELECT COUNT(*) FROM peminjaman');
        }
        return (int) $this->fetchValue('SELECT COUNT(*) FROM peminjaman WHERE id_anggota = ?', [(int) $idAnggota]);
    }

    public function hitungAktif($idAnggota = null)
    {
        $sql = "SELECT COUNT(*) FROM peminjaman WHERE status = 'Dipinjam'";
        $params = [];
        if ($idAnggota !== null) {
            $sql .= ' AND id_anggota = ?';
            $params[] = (int) $idAnggota;
        }
        return (int) $this->fetchValue($sql, $params);
    }

    public function hitungTerlambat($idAnggota = null)
    {
        $sql = "SELECT COUNT(*) FROM peminjaman WHERE status = 'Dipinjam' AND tanggal_jatuh_tempo < ?";
        $params = [today()];
        if ($idAnggota !== null) {
            $sql .= ' AND id_anggota = ?';
            $params[] = (int) $idAnggota;
        }
        return (int) $this->fetchValue($sql, $params);
    }

    /** Apakah anggota masih meminjam buku ini (belum dikembalikan)? */
    public function sudahMeminjamAktif($idAnggota, $idBuku, $kecualiPeminjaman = 0)
    {
        return (int) $this->fetchValue(
            "SELECT COUNT(*) FROM peminjaman p JOIN detail_peminjaman d ON d.id_peminjaman = p.id_peminjaman
              WHERE p.id_anggota = ? AND d.id_buku = ? AND p.status = 'Dipinjam' AND p.id_peminjaman <> ?",
            [(int) $idAnggota, (int) $idBuku, (int) $kecualiPeminjaman]
        ) > 0;
    }

    /* ---------- CREATE ---------- */
    /**
     * Buat transaksi peminjaman baru dan kurangi stok.
     * @return int id_peminjaman baru
     */
    public function buat($idAnggota, array $idBuku, $tglPinjam, $tglTempo)
    {
        $idBuku = array_values(array_unique(array_map('intval', $idBuku)));
        sort($idBuku); // urutan konsisten -> mencegah deadlock
        if (!$idBuku) {
            throw new RuntimeException('Pilih minimal satu buku.');
        }

        $this->db->beginTransaction();
        try {
            foreach ($idBuku as $idB) {
                $b = $this->fetchOne('SELECT id_buku, judul_buku, stok FROM buku WHERE id_buku = ? FOR UPDATE', [$idB]);
                if (!$b) {
                    throw new RuntimeException('Salah satu buku yang dipilih tidak ditemukan.');
                }
                if ((int) $b['stok'] < 1) {
                    throw new RuntimeException('Stok buku "' . $b['judul_buku'] . '" sedang habis.');
                }
                if ($this->sudahMeminjamAktif($idAnggota, $idB)) {
                    throw new RuntimeException('Buku "' . $b['judul_buku'] . '" masih dipinjam oleh anggota ini dan belum dikembalikan.');
                }
            }

            $this->execute(
                "INSERT INTO peminjaman (id_anggota, tanggal_pinjam, tanggal_jatuh_tempo, status) VALUES (?, ?, ?, 'Dipinjam')",
                [(int) $idAnggota, $tglPinjam, $tglTempo]
            );
            $idP = (int) $this->db->lastInsertId();

            foreach ($idBuku as $idB) {
                $this->execute(
                    'INSERT INTO detail_peminjaman (id_peminjaman, id_buku, jumlah) VALUES (?, ?, 1)',
                    [$idP, $idB]
                );
                $this->execute('UPDATE buku SET stok = stok - 1 WHERE id_buku = ?', [$idB]);
            }

            $this->db->commit();
            return $idP;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /* ---------- UPDATE ---------- */
    /** Ubah anggota & tanggal transaksi yang masih berstatus Dipinjam (daftar buku tidak berubah). */
    public function ubah($id, $idAnggota, $tglPinjam, $tglTempo)
    {
        $this->db->beginTransaction();
        try {
            $p = $this->fetchOne('SELECT * FROM peminjaman WHERE id_peminjaman = ? FOR UPDATE', [(int) $id]);
            if (!$p) {
                throw new RuntimeException('Transaksi tidak ditemukan.');
            }
            if ($p['status'] !== 'Dipinjam') {
                throw new RuntimeException('Transaksi yang sudah dikembalikan tidak dapat diubah.');
            }
            if ((int) $p['id_anggota'] !== (int) $idAnggota) {
                foreach ($this->detail($id) as $d) {
                    if ($this->sudahMeminjamAktif($idAnggota, $d['id_buku'], $id)) {
                        throw new RuntimeException('Anggota tujuan masih meminjam buku "' . $d['judul_buku'] . '".');
                    }
                }
            }
            $this->execute(
                'UPDATE peminjaman SET id_anggota = ?, tanggal_pinjam = ?, tanggal_jatuh_tempo = ? WHERE id_peminjaman = ?',
                [(int) $idAnggota, $tglPinjam, $tglTempo, (int) $id]
            );
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Pengembalian buku: stok bertambah, status menjadi Dikembalikan.
     * @param int|null $idAnggotaPemilik jika diisi, transaksi harus milik anggota ini (dipakai siswa)
     */
    public function kembalikan($id, $idAnggotaPemilik = null)
    {
        $this->db->beginTransaction();
        try {
            $p = $this->fetchOne('SELECT * FROM peminjaman WHERE id_peminjaman = ? FOR UPDATE', [(int) $id]);
            if (!$p) {
                throw new RuntimeException('Transaksi tidak ditemukan.');
            }
            if ($idAnggotaPemilik !== null && (int) $p['id_anggota'] !== (int) $idAnggotaPemilik) {
                throw new RuntimeException('Transaksi ini bukan milik Anda.');
            }
            if ($p['status'] === 'Dikembalikan') {
                throw new RuntimeException('Buku pada transaksi ini sudah dikembalikan.');
            }

            $rows = $this->fetchAll(
                'SELECT id_buku, jumlah FROM detail_peminjaman WHERE id_peminjaman = ? ORDER BY id_buku',
                [(int) $id]
            );
            foreach ($rows as $r) {
                $this->execute('UPDATE buku SET stok = stok + ? WHERE id_buku = ?', [(int) $r['jumlah'], (int) $r['id_buku']]);
            }
            $this->execute(
                "UPDATE peminjaman SET status = 'Dikembalikan', tanggal_kembali = ? WHERE id_peminjaman = ?",
                [today(), (int) $id]
            );
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /* ---------- DELETE ---------- */
    /**
     * Hapus transaksi. Jika belum dikembalikan, stok dikembalikan lebih dulu.
     * Admin: hapus($id). Siswa (pembatalan): hapus($id, $idAnggota, true)
     * -> hanya milik sendiri, status Dipinjam, dan dibuat pada hari yang sama.
     */
    public function hapus($id, $idAnggotaPemilik = null, $hanyaHariIni = false)
    {
        $this->db->beginTransaction();
        try {
            $p = $this->fetchOne('SELECT * FROM peminjaman WHERE id_peminjaman = ? FOR UPDATE', [(int) $id]);
            if (!$p) {
                throw new RuntimeException('Transaksi tidak ditemukan.');
            }
            if ($idAnggotaPemilik !== null && (int) $p['id_anggota'] !== (int) $idAnggotaPemilik) {
                throw new RuntimeException('Transaksi ini bukan milik Anda.');
            }
            if ($hanyaHariIni) {
                if ($p['status'] !== 'Dipinjam') {
                    throw new RuntimeException('Hanya peminjaman yang masih aktif yang dapat dibatalkan.');
                }
                if ($p['tanggal_pinjam'] !== today()) {
                    throw new RuntimeException('Pembatalan hanya bisa dilakukan pada hari peminjaman. Gunakan tombol Kembalikan.');
                }
            }
            if ($p['status'] === 'Dipinjam') {
                $rows = $this->fetchAll(
                    'SELECT id_buku, jumlah FROM detail_peminjaman WHERE id_peminjaman = ? ORDER BY id_buku',
                    [(int) $id]
                );
                foreach ($rows as $r) {
                    $this->execute('UPDATE buku SET stok = stok + ? WHERE id_buku = ?', [(int) $r['jumlah'], (int) $r['id_buku']]);
                }
            }
            $this->execute('DELETE FROM peminjaman WHERE id_peminjaman = ?', [(int) $id]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
