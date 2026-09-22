<?php

trait ConnectionTrait
{
    /** @var mysqli|null Instance koneksi yang dipakai ulang selama request. */
    public static $db = null;

    private static $local = 'localhost';
    private static $dbname = 'db_kas_kelas';

    protected static function connection()
    {
        if (self::$db instanceof mysqli && !self::$db->connect_errno) {
            return self::$db;
        }

        $host = getenv('KAS_DB_HOST') ?: self::$local;
        $user = getenv('KAS_DB_USER') ?: 'root';
        $pass = getenv('KAS_DB_PASS') ?: '';
        $name = getenv('KAS_DB_NAME') ?: self::$dbname;

        $db = new mysqli($host, $user, $pass, $name);

        if ($db->connect_error) {
            error_log('[kas_system] DB connection failed: ' . $db->connect_error);
            die('Koneksi gagal: ' . $db->connect_error);
        }

        $db->set_charset('utf8mb4');
        self::$db = $db;

        return $db;
    }
}
