<?php
/** CRUD Kelola Anggota (khusus admin) + pencarian & filter kelas. */
class AnggotaController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->requireRole('admin');
        $this->model = new AnggotaModel();
    }

    public function index()
    {
        $q     = $this->getStr('q');
        $kelas = $this->getStr('kelas');

        $title      = 'Kelola Anggota';
        $daftar     = $this->model->cari($q, $kelas);
        $kelasList  = $this->model->daftarKelas();
        $this->view('admin/anggota/index', compact('title', 'daftar', 'kelasList', 'q', 'kelas'), 'admin');
    }

    /** CREATE (admin mendaftarkan anggota) */
    public function tambah()
    {
        $form   = ['nama_anggota' => '', 'username' => '', 'kelas' => '', 'no_telp' => '', 'alamat' => ''];
        $errors = [];

        if ($this->isPost()) {
            $this->verifyCsrf();
            $form       = $this->ambilForm();
            $password   = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
            $konfirmasi = isset($_POST['konfirmasi']) && is_string($_POST['konfirmasi']) ? $_POST['konfirmasi'] : '';
            $errors     = Validator::anggota($form, $password, $konfirmasi, true);

            if (!$errors) {
                try {
                    $this->model->tambah($form, password_hash($password, PASSWORD_DEFAULT));
                    flash_set('success', 'Anggota "' . $form['nama_anggota'] . '" berhasil ditambahkan.');
                    redirect('anggota');
                } catch (PDOException $e) {
                    if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                        $errors['username'] = 'Username sudah digunakan.';
                    } else {
                        throw $e;
                    }
                }
            }
        }

        $title = 'Tambah Anggota';
        $mode  = 'tambah';
        $idAnggota = 0;
        $this->view('admin/anggota/form', compact('title', 'mode', 'idAnggota', 'form', 'errors'), 'admin');
    }

    /** UPDATE */
    public function edit()
    {
        $id      = $this->idParam();
        $anggota = $this->model->temukan($id);
        if (!$anggota) {
            flash_set('error', 'Data anggota tidak ditemukan.');
            redirect('anggota');
        }

        $form   = $anggota;
        $errors = [];

        if ($this->isPost()) {
            $this->verifyCsrf();
            $form       = $this->ambilForm();
            $password   = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
            $konfirmasi = isset($_POST['konfirmasi']) && is_string($_POST['konfirmasi']) ? $_POST['konfirmasi'] : '';
            $errors     = Validator::anggota($form, $password, $konfirmasi, false, (int) $anggota['id_pengguna']);

            if (!$errors) {
                try {
                    $hash = ($password !== '') ? password_hash($password, PASSWORD_DEFAULT) : null;
                    $this->model->ubah($id, $form, $hash);
                    flash_set('success', 'Data anggota berhasil diperbarui.');
                    redirect('anggota');
                } catch (PDOException $e) {
                    if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                        $errors['username'] = 'Username sudah digunakan.';
                    } else {
                        throw $e;
                    }
                }
            }
        }

        $title     = 'Edit Anggota';
        $mode      = 'edit';
        $idAnggota = $id;
        $this->view('admin/anggota/form', compact('title', 'mode', 'idAnggota', 'form', 'errors'), 'admin');
    }

    /** DELETE (hanya via POST) */
    public function hapus()
    {
        if (!$this->isPost()) {
            redirect('anggota');
        }
        $this->verifyCsrf();

        $id      = $this->idParam();
        $anggota = $this->model->temukan($id);
        if (!$anggota) {
            flash_set('error', 'Data anggota tidak ditemukan.');
        } elseif ($this->model->punyaTransaksi($id)) {
            flash_set('error', 'Anggota "' . $anggota['nama_anggota'] . '" tidak dapat dihapus karena memiliki riwayat peminjaman.');
        } else {
            $this->model->hapus($id);
            flash_set('success', 'Anggota "' . $anggota['nama_anggota'] . '" berhasil dihapus.');
        }
        redirect('anggota');
    }

    private function ambilForm()
    {
        return [
            'nama_anggota' => $this->postStr('nama_anggota'),
            'username'     => $this->postStr('username'),
            'kelas'        => $this->postStr('kelas'),
            'no_telp'      => $this->postStr('no_telp'),
            'alamat'       => $this->postStr('alamat'),
        ];
    }
}
