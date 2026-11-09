<?php
/** CRUD Transaksi peminjaman (admin) + pencarian multi-kondisi. */
class TransaksiController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->requireRole('admin');
        $this->model = new PeminjamanModel();
    }

    /** READ + pencarian: q, status, dari, sampai */
    public function index()
    {
        $q      = $this->getStr('q');
        $status = $this->getStr('status');
        $dari   = $this->getStr('dari');
        $sampai = $this->getStr('sampai');
        if (!in_array($status, ['Dipinjam', 'Terlambat', 'Dikembalikan'], true)) {
            $status = '';
        }

        $title  = 'Transaksi Peminjaman';
        $daftar = $this->model->cari(['q' => $q, 'status' => $status, 'dari' => $dari, 'sampai' => $sampai]);
        $this->view('admin/transaksi/index', compact('title', 'daftar', 'q', 'status', 'dari', 'sampai'), 'admin');
    }

    public function detail()
    {
        $t = $this->model->temukan($this->idParam());
        if (!$t) {
            flash_set('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }
        $title  = 'Detail Transaksi #' . $t['id_peminjaman'];
        $buku   = $this->model->detail($t['id_peminjaman']);
        $this->view('admin/transaksi/detail', compact('title', 't', 'buku'), 'admin');
    }

    /** CREATE */
    public function tambah()
    {
        $max    = (int) config('app.max_buku_per_transaksi', 3);
        $form   = [
            'id_anggota'          => '',
            'tanggal_pinjam'      => today(),
            'tanggal_jatuh_tempo' => date('Y-m-d', strtotime('+7 days')),
            'buku'                => [],
        ];
        $errors = [];

        if ($this->isPost()) {
            $this->verifyCsrf();
            $form['id_anggota']          = $this->postStr('id_anggota');
            $form['tanggal_pinjam']      = $this->postStr('tanggal_pinjam');
            $form['tanggal_jatuh_tempo'] = $this->postStr('tanggal_jatuh_tempo');
            $form['buku']                = $this->ambilBuku();

            $errors = Validator::tanggalTransaksi($form['tanggal_pinjam'], $form['tanggal_jatuh_tempo']);
            if (!(new AnggotaModel())->temukan((int) $form['id_anggota'])) {
                $errors['id_anggota'] = 'Pilih anggota peminjam.';
            }
            if (count($form['buku']) < 1) {
                $errors['buku'] = 'Pilih minimal 1 buku.';
            } elseif (count($form['buku']) > $max) {
                $errors['buku'] = 'Maksimal ' . $max . ' buku per transaksi.';
            }

            if (!$errors) {
                try {
                    $this->model->buat((int) $form['id_anggota'], $form['buku'], $form['tanggal_pinjam'], $form['tanggal_jatuh_tempo']);
                    flash_set('success', 'Transaksi peminjaman berhasil dicatat.');
                    redirect('transaksi');
                } catch (RuntimeException $e) {
                    $errors['buku'] = $e->getMessage();
                }
            }
        }

        $title       = 'Tambah Transaksi';
        $mode        = 'tambah';
        $idTransaksi = 0;
        $anggotaList = (new AnggotaModel())->semuaUntukPilihan();
        $bukuList    = (new BukuModel())->cari();
        $bukuTerpilih = [];
        $this->view('admin/transaksi/form', compact('title', 'mode', 'idTransaksi', 'form', 'errors', 'anggotaList', 'bukuList', 'bukuTerpilih', 'max'), 'admin');
    }

    /** UPDATE (anggota & tanggal; hanya transaksi berstatus Dipinjam) */
    public function edit()
    {
        $id = $this->idParam();
        $t  = $this->model->temukan($id);
        if (!$t) {
            flash_set('error', 'Transaksi tidak ditemukan.');
            redirect('transaksi');
        }
        if ($t['status'] !== 'Dipinjam') {
            flash_set('error', 'Transaksi yang sudah dikembalikan tidak dapat diubah.');
            redirect('transaksi');
        }

        $form = [
            'id_anggota'          => $t['id_anggota'],
            'tanggal_pinjam'      => $t['tanggal_pinjam'],
            'tanggal_jatuh_tempo' => $t['tanggal_jatuh_tempo'],
            'buku'                => [],
        ];
        $errors = [];

        if ($this->isPost()) {
            $this->verifyCsrf();
            $form['id_anggota']          = $this->postStr('id_anggota');
            $form['tanggal_pinjam']      = $this->postStr('tanggal_pinjam');
            $form['tanggal_jatuh_tempo'] = $this->postStr('tanggal_jatuh_tempo');

            $errors = Validator::tanggalTransaksi($form['tanggal_pinjam'], $form['tanggal_jatuh_tempo']);
            if (!(new AnggotaModel())->temukan((int) $form['id_anggota'])) {
                $errors['id_anggota'] = 'Pilih anggota peminjam.';
            }

            if (!$errors) {
                try {
                    $this->model->ubah($id, (int) $form['id_anggota'], $form['tanggal_pinjam'], $form['tanggal_jatuh_tempo']);
                    flash_set('success', 'Transaksi berhasil diperbarui.');
                    redirect('transaksi');
                } catch (RuntimeException $e) {
                    $errors['id_anggota'] = $e->getMessage();
                }
            }
        }

        $title        = 'Edit Transaksi #' . $id;
        $mode         = 'edit';
        $idTransaksi  = $id;
        $anggotaList  = (new AnggotaModel())->semuaUntukPilihan();
        $bukuList     = [];
        $bukuTerpilih = $this->model->detail($id);
        $max          = (int) config('app.max_buku_per_transaksi', 3);
        $this->view('admin/transaksi/form', compact('title', 'mode', 'idTransaksi', 'form', 'errors', 'anggotaList', 'bukuList', 'bukuTerpilih', 'max'), 'admin');
    }

    /** Tandai buku dikembalikan (POST) */
    public function kembalikan()
    {
        if (!$this->isPost()) {
            redirect('transaksi');
        }
        $this->verifyCsrf();
        try {
            $this->model->kembalikan($this->idParam());
            flash_set('success', 'Pengembalian buku berhasil dicatat. Stok telah diperbarui.');
        } catch (RuntimeException $e) {
            flash_set('error', $e->getMessage());
        }
        redirect('transaksi');
    }

    /** DELETE (POST) */
    public function hapus()
    {
        if (!$this->isPost()) {
            redirect('transaksi');
        }
        $this->verifyCsrf();
        try {
            $this->model->hapus($this->idParam());
            flash_set('success', 'Transaksi berhasil dihapus.');
        } catch (RuntimeException $e) {
            flash_set('error', $e->getMessage());
        }
        redirect('transaksi');
    }

    /** Ambil daftar id buku dari checkbox buku[]. */
    private function ambilBuku()
    {
        $raw = isset($_POST['buku']) && is_array($_POST['buku']) ? $_POST['buku'] : [];
        $ids = [];
        foreach ($raw as $v) {
            if (is_scalar($v) && (int) $v > 0) {
                $ids[] = (int) $v;
            }
        }
        return array_values(array_unique($ids));
    }
}
