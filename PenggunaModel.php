<?php
/** Akses tabel `pengguna` (akun login). */
class PenggunaModel extends Model
{
    public function temukanByUsername($username)
    {
        return $this->fetchOne('SELECT * FROM pengguna WHERE username = ?', [$username]);
    }

    /** Cek username sudah dipakai (abaikan akun tertentu saat edit). */
    public function usernameTerpakai($username, $exceptId = null)
    {
        $sql    = 'SELECT COUNT(*) FROM pengguna WHERE username = ?';
        $params = [$username];
        if ($exceptId !== null) {
            $sql .= ' AND id_pengguna <> ?';
            $params[] = (int) $exceptId;
        }
        return (int) $this->fetchValue($sql, $params) > 0;
    }

    public function perbaruiHash($idPengguna, $hash)
    {
        $this->execute('UPDATE pengguna SET password = ? WHERE id_pengguna = ?', [$hash, (int) $idPengguna]);
    }
}
