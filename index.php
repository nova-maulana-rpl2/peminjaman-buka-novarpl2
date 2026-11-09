<?php
/**
 * FRONT CONTROLLER + ROUTER
 * Semua request masuk lewat file ini:   index.php?r=<controller>/<method>
 * Contoh: index.php?r=buku/edit&id=3  ->  BukuController::edit()
 */
define('BASE_PATH', __DIR__);
define('VIEW_PATH', BASE_PATH . '/view');

require BASE_PATH . '/core/helpers.php';

date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));

spl_autoload_register(function ($class) {
    foreach (['core', 'controller', 'model'] as $dir) {
        $file = BASE_PATH . '/' . $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
session_name('PERPUSSESS');
session_start();

/** Tampilkan halaman error sederhana. */
function tampilkan_error($kode, $pesan, $detail = '')
{
    http_response_code($kode);
    $debug = (bool) config('app.debug', false);
    require VIEW_PATH . '/errors/error.php';
    exit;
}

$route = trim(isset($_GET['r']) ? (string) $_GET['r'] : '', '/');

if ($route === '') {
    redirect(Auth::check() ? 'dashboard' : 'auth/login');
}

$parts      = explode('/', $route);
$ctrlName   = $parts[0];
$methodName = isset($parts[1]) && $parts[1] !== '' ? $parts[1] : 'index';

if (!preg_match('/^[a-z]+$/', $ctrlName) || !preg_match('/^[a-z]+$/', $methodName)) {
    tampilkan_error(404, 'Halaman tidak ditemukan.');
}

$class = ucfirst($ctrlName) . 'Controller';

try {
    if (!class_exists($class)) {
        tampilkan_error(404, 'Halaman tidak ditemukan.');
    }
    $controller = new $class();
    if (!is_callable([$controller, $methodName])) {
        tampilkan_error(404, 'Halaman tidak ditemukan.');
    }
    $controller->$methodName();
} catch (PDOException $e) {
    error_log('[DB] ' . $e->getMessage());
    tampilkan_error(
        500,
        'Database tidak dapat diakses. Pastikan MySQL aktif dan file database/peminjaman_buku.sql sudah di-import.',
        $e->getMessage()
    );
} catch (Throwable $e) {
    error_log('[APP] ' . $e->getMessage());
    tampilkan_error(500, 'Terjadi kesalahan pada aplikasi.', $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')');
}
