<?php
/** Koneksi database tunggal (PDO + prepared statement). */
class Database
{
    private static $pdo = null;

    public static function pdo()
    {
        if (self::$pdo === null) {
            $c   = config('db');
            $dsn = 'mysql:host=' . $c['host'] . ';dbname=' . $c['name'] . ';charset=' . $c['charset'];
            self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }
}
