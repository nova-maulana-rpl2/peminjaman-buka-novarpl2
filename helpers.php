<?php
/**
 * FUNGSI BANTU GLOBAL (dipakai oleh controller & view)
 * Kompatibel PHP 7.3+.
 */

/** Ambil nilai konfigurasi dengan notasi titik, contoh: config('db.host'). */
function config($key, $default = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require BASE_PATH . '/config/config.php';
    }
    $val = $cfg;
    foreach (explode('.', $key) as $part) {
        if (!is_array($val) || !array_key_exists($part, $val)) {
            return $default;
        }
        $val = $val[$part];
    }
    return $val;
}

/** Escape output HTML (anti XSS). */
function e($value)
{
    return htmlspecialchars((string) ($value === null ? '' : $value), ENT_QUOTES, 'UTF-8');
}

/** Folder tempat index.php berada, relatif terhadap domain (tanpa slash akhir). */
function app_base()
{
    $dir = str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : ''));
    return ($dir === '/' || $dir === '.') ? '' : rtrim($dir, '/');
}

function asset($path)
{
    return app_base() . '/assets/' . ltrim($path, '/');
}

/** Bangun URL ke sebuah route, contoh: url('buku/edit', ['id' => 3]). */
function url($route = '', array $params = [])
{
    $qs = ($route !== '') ? 'r=' . $route : '';
    $clean = [];
    foreach ($params as $k => $v) {
        if ($v !== null && $v !== '') {
            $clean[$k] = $v;
        }
    }
    if ($clean) {
        $qs .= ($qs !== '' ? '&' : '') . http_build_query($clean);
    }
    return app_base() . '/index.php' . ($qs !== '' ? '?' . $qs : '');
}

function redirect($route, array $params = [])
{
    header('Location: ' . url($route, $params));
    exit;
}

/** Nama controller dari route aktif (untuk menandai menu aktif). */
function current_controller()
{
    $r = isset($_GET['r']) ? trim((string) $_GET['r'], '/') : '';
    $parts = explode('/', $r);
    return $parts[0] !== '' ? $parts[0] : 'dashboard';
}

/* ---------- Flash message ---------- */
function flash_set($type, $message)
{
    $_SESSION['flash'][$type] = $message;
}

function flash_get($type)
{
    $msg = isset($_SESSION['flash'][$type]) ? $_SESSION['flash'][$type] : null;
    unset($_SESSION['flash'][$type]);
    return $msg;
}

/* ---------- CSRF ---------- */
function csrf_token()
{
    if (empty($_SESSION['_token'])) {
        $_SESSION['_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid()
{
    $sent  = isset($_POST['_token']) ? $_POST['_token'] : '';
    $saved = isset($_SESSION['_token']) ? $_SESSION['_token'] : '';
    return is_string($sent) && $saved !== '' && hash_equals($saved, $sent);
}

/* ---------- Tanggal & status ---------- */
function today()
{
    return date('Y-m-d');
}

function valid_date($d)
{
    if (!is_string($d) || $d === '') {
        return false;
    }
    $x = DateTime::createFromFormat('Y-m-d', $d);
    return $x && $x->format('Y-m-d') === $d;
}

/** Format tanggal Indonesia singkat: 20 Okt 2026. */
function tgl($date)
{
    if (!$date || $date === '0000-00-00') {
        return '-';
    }
    $ts = strtotime($date);
    if (!$ts) {
        return '-';
    }
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Status yang ditampilkan: 'Dipinjam' yang lewat jatuh tempo menjadi 'Terlambat'. */
function status_tampil(array $row)
{
    if ($row['status'] === 'Dipinjam' && $row['tanggal_jatuh_tempo'] < today()) {
        return 'Terlambat';
    }
    return $row['status'];
}

function hari_terlambat(array $row)
{
    if ($row['status'] !== 'Dipinjam' || $row['tanggal_jatuh_tempo'] >= today()) {
        return 0;
    }
    $a = new DateTime($row['tanggal_jatuh_tempo']);
    $b = new DateTime(today());
    return (int) $a->diff($b)->days;
}

function badge($status)
{
    $map = ['Dipinjam' => 'badge-info', 'Dikembalikan' => 'badge-ok', 'Terlambat' => 'badge-late'];
    $cls = isset($map[$status]) ? $map[$status] : 'badge-info';
    return '<span class="badge ' . $cls . '">' . e($status) . '</span>';
}

function strlen_u($s)
{
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
}

/* ---------- Helper form ---------- */
/** Nilai field form yang sudah di-escape. */
function fv(array $form, $key)
{
    return isset($form[$key]) ? e($form[$key]) : '';
}

/** Pesan error di bawah field. */
function field_error(array $errors, $key)
{
    return isset($errors[$key]) ? '<small class="field-error">' . e($errors[$key]) . '</small>' : '';
}

/** Kelas CSS tambahan jika field error. */
function err_class(array $errors, $key)
{
    return isset($errors[$key]) ? ' input-error' : '';
}
