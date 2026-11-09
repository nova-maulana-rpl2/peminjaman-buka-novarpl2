<?php
/** Siswa: melihat peminjaman aktif & mengembalikan buku (flowmap: Pengembalian buku). */
class KembaliController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->requireRole('siswa');
        $this->model = new PeminjamanModel();
    }

    public function index()
    {
        $title = 'Pengembalian Buku';
        $semua = $this->model->cari(['id_anggota' => Auth::anggotaId()]);
        $aktif = array_values(array_filter($semua, function ($r) {
            return $r['status'] === 'Dipinjam';
        }));
        $this->view('siswa/kembali', compact('title', 'aktif'), 'siswa');
    }

    /** Proses pengembalian (POST) */
    public function proses()
    {
        if (!$this->isPost()) {
            redirect('kembali');
        }
        $this->verifyCsrf();
        try {
            $this->model->kembalikan($this->idParam(), Auth::anggotaId());
            flash_set('success', 'Terima kasih! Buku berhasil dikembalikan.');
        } catch (RuntimeException $e) {
            flash_set('error', $e->getMessage());
        }
        redirect('kembali');
    }

    /** Batalkan peminjaman yang dibuat hari ini (POST) */
    public function batalkan()
    {
        if (!$this->isPost()) {
            redirect('kembali');
        }
        $this->verifyCsrf();
        try {
            $this->model->hapus($this->idParam(), Auth::anggotaId(), true);
            flash_set('success', 'Peminjaman berhasil dibatalkan dan stok dikembalikan.');
        } catch (RuntimeException $e) {
            flash_set('error', $e->getMessage());
        }
        redirect('kembali');
    }
}
