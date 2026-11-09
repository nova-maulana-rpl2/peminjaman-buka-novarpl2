<?php
/** CRUD Data Buku (khusus admin) + pencarian & filter. */
class BukuController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->requireRole('admin');
        $this->model = new BukuModel();
    }

    /** READ + pencarian: ?q=...&kategori=...&stok=tersedia|habis */
    public function index()
    {
        $q        = $this->getStr('q');
        $kategori = $this->getStr('kategori');
        $stok     = $this->getStr('stok');

        $title        = 'Data Buku';
        $daftar       = $this->model->cari($q, $kategori, $stok);
        $kategoriList = $this->model->daftarKategori();
        $this->view('admin/buku/index', compact('title', 'daftar', 'kategoriList', 'q', 'kategori', 'stok'), 'admin');
    }

    /** CREATE */
    public function tambah()
    {
        $form   = ['judul_buku' => '', 'penulis' => '', 'penerbit' => '', 'tahun_terbit' => date('Y'), 'kategori' => '', 'stok' => '1'];
        $errors = [];

        if ($this->isPost()) {
            $this->verifyCsrf();
            $form   = $this->ambilForm();
            $errors = Validator::buku($form);
            if (!$errors) {
                $this->model->tambah($form);
                flash_set('success', 'Buku "' . $form['judul_buku'] . '" berhasil ditambahkan.');
                redirect('buku');
            }
        }

        $title        = 'Tambah Buku';
        $mode         = 'tambah';
        $idBuku       = 0;
        $kategoriList = $this->model->daftarKategori();
        $this->view('admin/buku/form', compact('title', 'mode', 'idBuku', 'form', 'errors', 'kategoriList'), 'admin');
    }

    /** UPDATE */
    public function edit()
    {
        $id   = $this->idParam();
        $buku = $this->model->temukan($id);
        if (!$buku) {
            flash_set('error', 'Data buku tidak ditemukan.');
            redirect('buku');
        }

        $form   = $buku;
        $errors = [];

        if ($this->isPost()) {
            $this->verifyCsrf();
            $form   = $this->ambilForm();
            $errors = Validator::buku($form);
            if (!$errors) {
                $this->model->ubah($id, $form);
                flash_set('success', 'Data buku berhasil diperbarui.');
                redirect('buku');
            }
        }

        $title        = 'Edit Buku';
        $mode         = 'edit';
        $idBuku       = $id;
        $kategoriList = $this->model->daftarKategori();
        $this->view('admin/buku/form', compact('title', 'mode', 'idBuku', 'form', 'errors', 'kategoriList'), 'admin');
    }

    /** DELETE (hanya via POST) */
    public function hapus()
    {
        if (!$this->isPost()) {
            redirect('buku');
        }
        $this->verifyCsrf();

        $id   = $this->idParam();
        $buku = $this->model->temukan($id);
        if (!$buku) {
            flash_set('error', 'Data buku tidak ditemukan.');
        } elseif ($this->model->punyaTransaksi($id)) {
            flash_set('error', 'Buku "' . $buku['judul_buku'] . '" tidak dapat dihapus karena sudah tercatat di transaksi peminjaman.');
        } else {
            $this->model->hapus($id);
            flash_set('success', 'Buku "' . $buku['judul_buku'] . '" berhasil dihapus.');
        }
        redirect('buku');
    }

    private function ambilForm()
    {
        return [
            'judul_buku'   => $this->postStr('judul_buku'),
            'penulis'      => $this->postStr('penulis'),
            'penerbit'     => $this->postStr('penerbit'),
            'tahun_terbit' => $this->postStr('tahun_terbit'),
            'kategori'     => $this->postStr('kategori'),
            'stok'         => $this->postStr('stok'),
        ];
    }
}
