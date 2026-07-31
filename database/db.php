<?php

class Koneksi {
  private static $local = "localhost";
  private static $username = "root";
  private static $password = "bagas_tresna123";
  private static $dbname = "db_kas_kelas";
  public static $db = null;

  private static function connection()
  {
    $db = new mysqli(self::$local, self::$username, self::$password, self::$dbname);

    if ($db->connect_error) {
      die("Koneksi gagal" . $db->connect_error);
    }

    return $db;
  }

  public static function executeQuery($sql) 
  {
    $koneksi = self::connection();
    return $koneksi->query($sql);
  }

  /**
   * Query aman memakai prepared statement.
   * Contoh: Koneksi::q("SELECT * FROM siswa WHERE id = ?", [1])
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

  public static function Xss(string $string) 
  {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
  }

  /* ===== Flash message ===== */

  public static function setFlash(string $type, string $message)
  {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
  }

  public static function getFlash()
  {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
  }

  public static function renderFlash()
  {
    foreach (self::getFlash() as $f) {
      $cls = $f['type'] === 'success' ? 'flash-success'
           : ($f['type'] === 'error' ? 'flash-error'
           : 'flash-info');
      echo '<div class="flash ' . $cls . '">' . htmlspecialchars($f['message']) . '</div>';
    }
  }
}
