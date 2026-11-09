<?php
/** Siswa: riwayat peminjaman milik sendiri + pencarian (q, status, tanggal). */
class RiwayatController extends Controller
{
    public function __construct()
    {
        $this->requireRole('siswa');
    }

    public function index()
    {
        $q      = $this->getStr('q');
        $status = $this->getStr('status');
        $dari   = $this->getStr('dari');
        $sampai = $this->getStr('sampai');
        if (!in_array($status, ['Dipinjam', 'Terlambat', 'Dikembalikan'], true)) {
            $status = '';
        }

        $title  = 'Riwayat Peminjaman';
        $daftar = (new PeminjamanModel())->cari([
            'id_anggota' => Auth::anggotaId(),
            'q'          => $q,
            'status'     => $status,
            'dari'       => $dari,
            'sampai'     => $sampai,
        ]);
        $this->view('siswa/riwayat', compact('title', 'daftar', 'q', 'status', 'dari', 'sampai'), 'siswa');
    }
}
