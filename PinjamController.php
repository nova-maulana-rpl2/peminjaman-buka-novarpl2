<?php
/** Siswa: mencari buku & melakukan peminjaman (flowmap: Peminjaman buku). */
class PinjamController extends Controller
{
    private $bukuModel;

    public function __construct()
    {
        $this->requireRole('siswa');
        $this->bukuModel = new BukuModel();
    }

    /** Daftar buku + pencarian: q, kategori, stok */
    public function index()
    {
        $q        = $this->getStr('q');
        $kategori = $this->getStr('kategori');
        $stok     = $this->getStr('stok');

        $title        = 'Pinjam Buku';
        $daftar       = $this->bukuModel->cari($q, $kategori, $stok);
        $kategoriList = $this->bukuModel->daftarKategori();
        $max          = (int) config('app.max_buku_per_transaksi', 3);
        $lamaPilihan  = config('app.lama_pinjam_pilihan', [3, 7, 14]);
        $this->view('siswa/pinjam', compact('title', 'daftar', 'kategoriList', 'q', 'kategori', 'stok', 'max', 'lamaPilihan'), 'siswa');
    }

    /** Proses peminjaman (POST) */
    public function proses()
    {
        if (!$this->isPost()) {
            redirect('pinjam');
        }
        $this->verifyCsrf();

        $max   = (int) config('app.max_buku_per_transaksi', 3);
        $lamaP = config('app.lama_pinjam_pilihan', [3, 7, 14]);
        $lama  = (int) $this->postStr('lama');

        $raw = isset($_POST['buku']) && is_array($_POST['buku']) ? $_POST['buku'] : [];
        $ids = [];
        foreach ($raw as $v) {
            if (is_scalar($v) && (int) $v > 0) {
                $ids[] = (int) $v;
            }
        }
        $ids = array_values(array_unique($ids));

        if (count($ids) < 1) {
            flash_set('error', 'Pilih minimal 1 buku yang ingin dipinjam.');
            redirect('pinjam');
        }
        if (count($ids) > $max) {
            flash_set('error', 'Maksimal ' . $max . ' buku dalam satu kali peminjaman.');
            redirect('pinjam');
        }
        if (!in_array($lama, $lamaP, true)) {
            flash_set('error', 'Lama peminjaman tidak valid.');
            redirect('pinjam');
        }

        $pinjam = today();
        $tempo  = date('Y-m-d', strtotime('+' . $lama . ' days'));

        try {
            (new PeminjamanModel())->buat(Auth::anggotaId(), $ids, $pinjam, $tempo);
            flash_set('success', 'Peminjaman berhasil! Harap kembalikan sebelum ' . tgl($tempo) . '.');
            redirect('kembali');
        } catch (RuntimeException $e) {
            flash_set('error', $e->getMessage());
            redirect('pinjam');
        }
    }
}
