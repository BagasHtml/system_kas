<?php

/**
 * Tanggung jawab: eksekusi query SQL, termasuk dukungan prepared statement.
 */

trait QueryTrait
{
    /**
     * Jalankan query tanpa parameter.
     *
     * @param string $sql
     * @return mysqli_result|bool
     */
    public static function executeQuery($sql)
    {
        $koneksi = self::connection();
        return $koneksi->query($sql);
    }

    /**
     * Jalankan query dengan dukungan parameter (prepared statement).
     *
     * Gunakan ? sebagai placeholder di SQL, lalu beri nilai berurutan di $params.
     * Tipe bind ditentukan otomatis dari tipe nilai (int, float, atau string).
     *
     * @param string $sql    Query yang akan dijalankan.
     * @param array  $params Nilai untuk placeholder '?', urut sesuai posisinya.
     * @return mysqli_result|bool
     */
    public static function q($sql, $params = [])
    {
        $koneksi = self::connection();

        if (empty($params)) {
            return $koneksi->query($sql);
        }

        $stmt = $koneksi->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $types = '';
        $values = [];
        foreach ($params as $p) {
            $types .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's');
            $values[] = $p;
        }

        $stmt->bind_param($types, ...$values);
        if (!$stmt->execute()) {
            return false;
        }

        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
}
