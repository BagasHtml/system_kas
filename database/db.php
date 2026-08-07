<?php

class Koneksi {
  private static $local = "localhost";
  private static $username = "root";
  private static $password = "bagas_tresna123";
  private static $dbname = "db_kas_kelas";

  /**
   * Koneksi mysqli di-cache per-request.
   * Statis di PHP hanya hidup satu request, jadi ini aman dipakai ulang antar query.
   */
  public static $db = null;

  private static function connection()
  {
    if (self::$db instanceof mysqli && !self::$db->connect_errno) {
      return self::$db;
    }

    $host = getenv('KAS_DB_HOST') ?: self::$local;
    $user = getenv('KAS_DB_USER') ?: self::$username;
    $pass = getenv('KAS_DB_PASS') ?: self::$password;
    $name = getenv('KAS_DB_NAME') ?: self::$dbname;

    $db = new mysqli($host, $user, $pass, $name);

    if ($db->connect_error) {
      error_log('[kas_system] DB connection failed: ' . $db->connect_error);
      die("Koneksi gagal" . $db->connect_error);
    }

    $db->set_charset('utf8mb4');
    self::$db = $db;
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

  /* ===== CSRF ===== */

  public static function csrfToken(): string
  {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf'])) {
      $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
  }

  public static function csrfField(): string
  {
    return '<input type="hidden" name="csrf" value="' . self::csrfToken() . '">';
  }

  public static function csrfCheck(): bool
  {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_POST['csrf'])
      && isset($_SESSION['csrf'])
      && hash_equals($_SESSION['csrf'], $_POST['csrf']);
  }

  /* ===== Format periode (disimpan sebagai YYYY-MM) ===== */

  private static function bulanLabel(): array
  {
    return [
      '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
      '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
      '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
  }

  /** '2026-05' -> 'Mei 2026'; dikembalikan apa adanya jika format tidak dikenali. */
  public static function periodeLabel(string $ym): string
  {
    if (preg_match('/^(\d{4})-(\d{2})$/', $ym, $m)) {
      $bulan = self::bulanLabel();
      if (isset($bulan[$m[2]])) {
        return $bulan[$m[2]] . ' ' . $m[1];
      }
    }
    return $ym;
  }

  /** '2026-05' -> 'Mei 2026' (bulan pendek, tahun penuh) untuk header matriks. */
  public static function periodeShortLabel(string $ym): string
  {
    if (preg_match('/^(\d{4})-(\d{2})$/', $ym, $m)) {
      $pendek = [
        '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun',
        '07' => 'Jul', '08' => 'Ags', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
      ];
      return $pendek[$m[2]] . ' ' . $m[1];
    }
    return $ym;
  }

  /* ===== Target kas (kesepakatan kelas) ===== */

  /** periode => ['target' => float, 'keterangan' => string|null] */
  public static function targetMap(): array
  {
    $map = [];
    $r = self::q("SELECT periode, target, keterangan FROM target_kas ORDER BY periode ASC");
    if ($r) {
      while ($row = $r->fetch_assoc()) {
        $map[$row['periode']] = [
          'target' => (float)$row['target'],
          'keterangan' => $row['keterangan'] ?? null,
        ];
      }
    }
    return $map;
  }

  /** Jumlah seluruh target yang terdaftar. */
  public static function totalTarget(array $map): float
  {
    $t = 0.0;
    foreach ($map as $v) {
      $t += $v['target'];
    }
    return $t;
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
