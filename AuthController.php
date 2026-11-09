<?php
/** Login (admin/siswa), pendaftaran anggota, dan logout. Sesuai flowmap SPK. */
class AuthController extends Controller
{
    /** Halaman + proses login. Parameter ?role=admin|siswa memilih jenis login. */
    public function login()
    {
        if (Auth::check()) {
            redirect('dashboard');
        }

        $role = $this->getStr('role');
        if ($role === '') {
            $role = $this->postStr('role');
        }
        $role     = ($role === 'admin') ? 'admin' : 'siswa';
        $error    = null;
        $username = '';

        if ($this->isPost()) {
            $this->verifyCsrf();
            $username = $this->postStr('username');
            $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';

            if ($username === '' || $password === '') {
                $error = 'Username dan password wajib diisi.';
            } else {
                $pm   = new PenggunaModel();
                $user = $pm->temukanByUsername($username);

                if ($user && $user['role'] === $role && password_verify($password, $user['password'])) {
                    $nama      = $user['username'];
                    $idAnggota = 0;

                    if ($role === 'siswa') {
                        $anggota = (new AnggotaModel())->temukanByPengguna($user['id_pengguna']);
                        if ($anggota) {
                            $nama      = $anggota['nama_anggota'];
                            $idAnggota = (int) $anggota['id_anggota'];
                        } else {
                            $error = 'Data anggota untuk akun ini tidak ditemukan. Hubungi admin.';
                        }
                    }

                    if ($error === null) {
                        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                            $pm->perbaruiHash($user['id_pengguna'], password_hash($password, PASSWORD_DEFAULT));
                        }
                        Auth::login([
                            'id_pengguna' => (int) $user['id_pengguna'],
                            'id_anggota'  => $idAnggota,
                            'username'    => $user['username'],
                            'nama'        => $nama,
                            'role'        => $user['role'],
                        ]);
                        flash_set('success', 'Selamat datang, ' . $nama . '!');
                        redirect('dashboard');
                    }
                } else {
                    $error = 'Username, password, atau jenis akun tidak sesuai.';
                }
            }
        }

        $title = 'Login';
        $this->view('auth/login', compact('title', 'role', 'error', 'username'), 'auth');
    }

    /** Pendaftaran anggota baru (publik) -> setelah berhasil diarahkan ke login siswa. */
    public function daftar()
    {
        if (Auth::check()) {
            redirect('dashboard');
        }

        $form   = ['nama_anggota' => '', 'username' => '', 'kelas' => '', 'no_telp' => '', 'alamat' => ''];
        $errors = [];

        if ($this->isPost()) {
            $this->verifyCsrf();
            foreach ($form as $k => $v) {
                $form[$k] = $this->postStr($k);
            }
            $password   = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
            $konfirmasi = isset($_POST['konfirmasi']) && is_string($_POST['konfirmasi']) ? $_POST['konfirmasi'] : '';

            $errors = Validator::anggota($form, $password, $konfirmasi, true);

            if (!$errors) {
                try {
                    (new AnggotaModel())->tambah($form, password_hash($password, PASSWORD_DEFAULT));
                    flash_set('success', 'Pendaftaran berhasil! Silakan login dengan akun Anda.');
                    redirect('auth/login', ['role' => 'siswa']);
                } catch (PDOException $e) {
                    if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                        $errors['username'] = 'Username sudah digunakan.';
                    } else {
                        throw $e;
                    }
                }
            }
        }

        $title = 'Daftar Anggota';
        $this->view('auth/daftar', compact('title', 'form', 'errors'), 'auth');
    }

    public function logout()
    {
        if (!$this->isPost()) {
            redirect('dashboard');
        }
        $this->verifyCsrf();
        Auth::logout();
        flash_set('success', 'Anda berhasil keluar.');
        redirect('auth/login');
    }
}
