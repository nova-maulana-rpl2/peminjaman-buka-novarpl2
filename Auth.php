<?php
/** Pengelola sesi login. Data user disimpan di $_SESSION['user']. */
class Auth
{
    public static function check()
    {
        return !empty($_SESSION['user']);
    }

    public static function user()
    {
        return self::check() ? $_SESSION['user'] : null;
    }

    public static function role()
    {
        return self::check() ? $_SESSION['user']['role'] : null;
    }

    public static function id()
    {
        return self::check() ? (int) $_SESSION['user']['id_pengguna'] : 0;
    }

    /** id_anggota milik siswa yang sedang login (0 jika admin). */
    public static function anggotaId()
    {
        return self::check() ? (int) $_SESSION['user']['id_anggota'] : 0;
    }

    public static function login(array $user)
    {
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
    }

    public static function logout()
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }
}
