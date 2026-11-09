<?php
/** Validasi input terpusat. Setiap method mengembalikan array [field => pesan]. */
class Validator
{
    public static function anggota(array $f, $password, $konfirmasi, $passwordWajib, $exceptPenggunaId = null)
    {
        $err = [];

        if ($f['nama_anggota'] === '') {
            $err['nama_anggota'] = 'Nama lengkap wajib diisi.';
        } elseif (strlen_u($f['nama_anggota']) > 100) {
            $err['nama_anggota'] = 'Nama maksimal 100 karakter.';
        }

        if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $f['username'])) {
            $err['username'] = 'Username 3-30 karakter: huruf, angka, titik, atau underscore.';
        } else {
            $pm = new PenggunaModel();
            if ($pm->usernameTerpakai($f['username'], $exceptPenggunaId)) {
                $err['username'] = 'Username sudah digunakan.';
            }
        }

        if ($f['kelas'] === '') {
            $err['kelas'] = 'Kelas wajib diisi.';
        } elseif (strlen_u($f['kelas']) > 30) {
            $err['kelas'] = 'Kelas maksimal 30 karakter.';
        }

        if (!preg_match('/^[0-9+\- ]{8,20}$/', $f['no_telp'])) {
            $err['no_telp'] = 'No. telepon 8-20 karakter (angka, +, -, spasi).';
        }

        if (strlen_u($f['alamat']) > 500) {
            $err['alamat'] = 'Alamat maksimal 500 karakter.';
        }

        if ($passwordWajib || $password !== '') {
            if (strlen($password) < 6) {
                $err['password'] = 'Password minimal 6 karakter.';
            } elseif ($password !== $konfirmasi) {
                $err['konfirmasi'] = 'Konfirmasi password tidak sama.';
            }
        }

        return $err;
    }

    public static function buku(array $f)
    {
        $err = [];
        $tahunMax = (int) date('Y') + 1;

        if ($f['judul_buku'] === '') {
            $err['judul_buku'] = 'Judul wajib diisi.';
        } elseif (strlen_u($f['judul_buku']) > 150) {
            $err['judul_buku'] = 'Judul maksimal 150 karakter.';
        }
        foreach (['penulis', 'penerbit'] as $k) {
            if ($f[$k] === '') {
                $err[$k] = ucfirst($k) . ' wajib diisi.';
            } elseif (strlen_u($f[$k]) > 100) {
                $err[$k] = ucfirst($k) . ' maksimal 100 karakter.';
            }
        }
        if (!preg_match('/^[0-9]{4}$/', $f['tahun_terbit']) || (int) $f['tahun_terbit'] < 1900 || (int) $f['tahun_terbit'] > $tahunMax) {
            $err['tahun_terbit'] = 'Tahun terbit harus 4 digit antara 1900 - ' . $tahunMax . '.';
        }
        if ($f['kategori'] === '') {
            $err['kategori'] = 'Kategori wajib diisi.';
        } elseif (strlen_u($f['kategori']) > 50) {
            $err['kategori'] = 'Kategori maksimal 50 karakter.';
        }
        if (!preg_match('/^[0-9]{1,6}$/', $f['stok'])) {
            $err['stok'] = 'Stok harus berupa angka 0 atau lebih.';
        }
        return $err;
    }

    /** Validasi tanggal transaksi (admin). */
    public static function tanggalTransaksi($pinjam, $tempo)
    {
        $err = [];
        if (!valid_date($pinjam)) {
            $err['tanggal_pinjam'] = 'Tanggal pinjam tidak valid.';
        }
        if (!valid_date($tempo)) {
            $err['tanggal_jatuh_tempo'] = 'Tanggal jatuh tempo tidak valid.';
        }
        if (!$err && $tempo < $pinjam) {
            $err['tanggal_jatuh_tempo'] = 'Jatuh tempo tidak boleh sebelum tanggal pinjam.';
        }
        return $err;
    }
}
