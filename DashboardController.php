<?php
/** Dashboard admin & dashboard siswa (dipilih berdasarkan role). */
class DashboardController extends Controller
{
    public function __construct()
    {
        $this->requireLogin();
    }

    public function index()
    {
        $pm = new PeminjamanModel();

        if (Auth::role() === 'admin') {
            $title = 'Dashboard Admin';
            $stat  = [
                'buku'      => (new BukuModel())->hitung(),
                'anggota'   => (new AnggotaModel())->hitung(),
                'dipinjam'  => $pm->hitungAktif(),
                'terlambat' => $pm->hitungTerlambat(),
            ];
            $terbaru = $pm->terbaru(6);
            $this->view('admin/dashboard', compact('title', 'stat', 'terbaru'), 'admin');
            return;
        }

        $idAnggota = Auth::anggotaId();
        $title     = 'Beranda';
        $stat      = [
            'dipinjam'  => $pm->hitungAktif($idAnggota),
            'terlambat' => $pm->hitungTerlambat($idAnggota),
            'riwayat'   => $pm->hitungSemua($idAnggota),
        ];
        $aktif = $pm->cari(['id_anggota' => $idAnggota, 'status' => '']);
        $aktif = array_values(array_filter($aktif, function ($r) {
            return $r['status'] === 'Dipinjam';
        }));
        $this->view('siswa/dashboard', compact('title', 'stat', 'aktif'), 'siswa');
    }
}
