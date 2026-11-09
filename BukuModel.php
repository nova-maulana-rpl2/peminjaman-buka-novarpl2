<?php
/** Akses tabel `buku`. */
class BukuModel extends Model
{
    /**
     * Pencarian buku.
     * @param string $q        kata kunci judul / penulis / penerbit
     * @param string $kategori filter kategori ('' = semua)
     * @param string $stok     'tersedia' | 'habis' | '' (semua)
     */
    public function cari($q = '', $kategori = '', $stok = '')
    {
        $sql    = 'SELECT * FROM buku WHERE 1=1';
        $params = [];
        if ($q !== '') {
            $like = '%' . $q . '%';
            $sql .= ' AND (judul_buku LIKE ? OR penulis LIKE ? OR penerbit LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        if ($kategori !== '') {
            $sql .= ' AND kategori = ?';
            $params[] = $kategori;
        }
        if ($stok === 'tersedia') {
            $sql .= ' AND stok > 0';
        } elseif ($stok === 'habis') {
            $sql .= ' AND stok = 0';
        }
        $sql .= ' ORDER BY judul_buku ASC';
        return $this->fetchAll($sql, $params);
    }

    public function temukan($id)
    {
        return $this->fetchOne('SELECT * FROM buku WHERE id_buku = ?', [(int) $id]);
    }

    public function daftarKategori()
    {
        $rows = $this->fetchAll('SELECT DISTINCT kategori FROM buku ORDER BY kategori ASC');
        return array_column($rows, 'kategori');
    }

    public function tambah(array $d)
    {
        $this->execute(
            'INSERT INTO buku (judul_buku, penulis, penerbit, tahun_terbit, kategori, stok) VALUES (?, ?, ?, ?, ?, ?)',
            [$d['judul_buku'], $d['penulis'], $d['penerbit'], (int) $d['tahun_terbit'], $d['kategori'], (int) $d['stok']]
        );
        return (int) $this->db->lastInsertId();
    }

    public function ubah($id, array $d)
    {
        $this->execute(
            'UPDATE buku SET judul_buku = ?, penulis = ?, penerbit = ?, tahun_terbit = ?, kategori = ?, stok = ? WHERE id_buku = ?',
            [$d['judul_buku'], $d['penulis'], $d['penerbit'], (int) $d['tahun_terbit'], $d['kategori'], (int) $d['stok'], (int) $id]
        );
    }

    public function hapus($id)
    {
        $this->execute('DELETE FROM buku WHERE id_buku = ?', [(int) $id]);
    }

    public function hitung()
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM buku');
    }

    /** Buku yang pernah/sedang dipinjam tidak boleh dihapus (menjaga riwayat). */
    public function punyaTransaksi($id)
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM detail_peminjaman WHERE id_buku = ?', [(int) $id]) > 0;
    }
}
