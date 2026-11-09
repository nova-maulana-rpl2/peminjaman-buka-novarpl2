<?php
/** Kelas dasar semua Controller: guard akses, CSRF, dan render view. */
abstract class Controller
{
    protected function isPost()
    {
        return (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET') === 'POST';
    }

    protected function verifyCsrf()
    {
        if (!csrf_valid()) {
            flash_set('error', 'Sesi formulir tidak valid atau sudah kedaluwarsa. Silakan ulangi.');
            redirect(Auth::check() ? 'dashboard' : 'auth/login');
        }
    }

    protected function requireLogin()
    {
        if (!Auth::check()) {
            flash_set('error', 'Silakan login terlebih dahulu.');
            redirect('auth/login');
        }
    }

    protected function requireRole($role)
    {
        $this->requireLogin();
        if (Auth::role() !== $role) {
            flash_set('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            redirect('dashboard');
        }
    }

    /** Ambil string dari $_GET dan rapikan. */
    protected function getStr($key)
    {
        return isset($_GET[$key]) && is_string($_GET[$key]) ? trim($_GET[$key]) : '';
    }

    /** Ambil string dari $_POST dan rapikan. */
    protected function postStr($key)
    {
        return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
    }

    protected function idParam()
    {
        $v = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : 0);
        return is_scalar($v) ? (int) $v : 0;
    }

    /**
     * Render view di dalam layout.
     * $data diubah menjadi variabel lokal (mis. ['title' => 'X'] -> $title).
     */
    protected function view($__view, array $__data = [], $__layout = 'admin')
    {
        extract($__data, EXTR_SKIP);
        ob_start();
        require VIEW_PATH . '/' . $__view . '.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layout/' . $__layout . '.php';
    }
}
