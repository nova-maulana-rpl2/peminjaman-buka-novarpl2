<?php
/** Akses tabel `anggota` (+ akun `pengguna` yang terkait). */
class AnggotaModel extends Model
{
    public function cari($q = '', $kelas = '')
    {
        $sql = 'SELECT a.*, p.username FROM anggota a JOIN pengguna p ON p.id_pengguna = a.id_pengguna WHERE 1=1';
        $params = [];
        if ($q !== '') {
            $like = '%' . $q . '%';
            $sql .= ' AND (a.nama_anggota LIKE ? OR p.username LIKE ? OR a.no_telp LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        if ($kelas !== '') {
            $sql .= ' AND a.kelas = ?';
            $params[] = $kelas;
        }
        $sql .= ' ORDER BY a.nama_anggota ASC';
        return $this->fetchAll($sql, $params);
    }

    public function temukan($id)
    {
        return $this->fetchOne(
            'SELECT a.*, p.username FROM anggota a JOIN pengguna p ON p.id_pengguna = a.id_pengguna WHERE a.id_anggota = ?',
            [(int) $id]
        );
    }

    public function temukanByPengguna($idPengguna)
    {
        return $this->fetchOne('SELECT * FROM anggota WHERE id_pengguna = ?', [(int) $idPengguna]);
    }

    public function daftarKelas()
    {
        $rows = $this->fetchAll('SELECT DISTINCT kelas FROM anggota ORDER BY kelas ASC');
        return array_column($rows, 'kelas');
    }

    public function semuaUntukPilihan()
    {
        return $this->fetchAll('SELECT id_anggota, nama_anggota, kelas FROM anggota ORDER BY nama_anggota ASC');
    }

    /** Buat akun pengguna + data anggota dalam satu transaksi. */
    public function tambah(array $d, $passwordHash)
    {
        $this->db->beginTransaction();
        try {
            $this->execute(
                'INSERT INTO pengguna (username, password, role) VALUES (?, ?, ?)',
                [$d['username'], $passwordHash, 'siswa']
            );
            $idPengguna = (int) $this->db->lastInsertId();
            $this->execute(
                'INSERT INTO anggota (id_pengguna, nama_anggota, kelas, alamat, no_telp) VALUES (?, ?, ?, ?, ?)',
                [$idPengguna, $d['nama_anggota'], $d['kelas'], $d['alamat'], $d['no_telp']]
            );
            $idAnggota = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $idAnggota;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** Ubah data anggota + username; password hanya diganti jika $passwordHash tidak null. */
    public function ubah($id, array $d, $passwordHash = null)
    {
        $a = $this->temukan($id);
        if (!$a) {
            throw new RuntimeException('Anggota tidak ditemukan.');
        }
        $this->db->beginTransaction();
        try {
            $this->execute(
                'UPDATE anggota SET nama_anggota = ?, kelas = ?, alamat = ?, no_telp = ? WHERE id_anggota = ?',
                [$d['nama_anggota'], $d['kelas'], $d['alamat'], $d['no_telp'], (int) $id]
            );
            if ($passwordHash !== null) {
                $this->execute(
                    'UPDATE pengguna SET username = ?, password = ? WHERE id_pengguna = ?',
                    [$d['username'], $passwordHash, (int) $a['id_pengguna']]
                );
            } else {
                $this->execute(
                    'UPDATE pengguna SET username = ? WHERE id_pengguna = ?',
                    [$d['username'], (int) $a['id_pengguna']]
                );
            }
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** Hapus akun pengguna; baris anggota ikut terhapus lewat ON DELETE CASCADE. */
    public function hapus($id)
    {
        $a = $this->temukan($id);
        if ($a) {
            $this->execute('DELETE FROM pengguna WHERE id_pengguna = ?', [(int) $a['id_pengguna']]);
        }
    }

    public function hitung()
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM anggota');
    }

    public function punyaTransaksi($id)
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM peminjaman WHERE id_anggota = ?', [(int) $id]) > 0;
    }
}
