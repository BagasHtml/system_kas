<?php
/**
 * Handler: siswa mengirim uang kas (upload bukti transfer).
 * Tanpa batasan: bisa bayar kapan saja, berapa saja, berkali-kali.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../database/db.php';

if (empty($_SESSION['siswa_id'])) {
    header("Location: ../views/siswa/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['kontribusi_submit'])) {
    header("Location: ../views/siswa/dashboard.php");
    exit;
}

if (!Koneksi::csrfCheck()) {
    Koneksi::setFlash('error', 'Token keamanan tidak valid.');
    header("Location: ../views/siswa/dashboard.php");
    exit;
}

$siswa_id = (int)$_SESSION['siswa_id'];
$jumlah   = (float)($_POST['jumlah'] ?? 0);
$metode   = in_array($_POST['metode'] ?? '', ['qris','dana'], true) ? $_POST['metode'] : 'langsung';
$catatan  = trim($_POST['catatan'] ?? '');

$periode = date('Y-m');

if ($jumlah <= 0) {
    Koneksi::setFlash('error', 'Jumlah setoran harus lebih dari Rp 0.');
    header("Location: ../views/siswa/dashboard.php");
    exit;
}

$isTunai  = $metode === 'langsung';
$bukti_path = null;

$has_file = isset($_FILES['bukti_transfer'])
    && (int)($_FILES['bukti_transfer']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;

if (!$isTunai && !$has_file) {
    Koneksi::setFlash('error', 'Wajib upload foto bukti transfer untuk metode DANA/QRIS.');
    header("Location: ../views/siswa/dashboard.php");
    exit;
}

if ($has_file) {
    $file = $_FILES['bukti_transfer'];
    $tmp  = $file['tmp_name'];
    if (!is_uploaded_file($tmp) || (int)$file['size'] <= 0) {
        Koneksi::setFlash('error', 'File bukti kosong atau tidak valid.');
        header("Location: ../views/siswa/dashboard.php");
        exit;
    }
    if ((int)$file['size'] > 5 * 1024 * 1024) {
        Koneksi::setFlash('error', 'Ukuran foto maksimal 5MB.');
        header("Location: ../views/siswa/dashboard.php");
        exit;
    }
    $info = @getimagesize($tmp);
    if ($info === false) {
        Koneksi::setFlash('error', 'File yang diupload bukan foto/gambar yang valid.');
        header("Location: ../views/siswa/dashboard.php");
        exit;
    }
    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = $info['mime'];
    if (!isset($extMap[$mime])) {
        Koneksi::setFlash('error', 'Format foto harus JPG, PNG, atau WebP.');
        header("Location: ../views/siswa/dashboard.php");
        exit;
    }
    $filename = 'bukti_' . $siswa_id . '_' . time() . '_' . rand(100, 999) . '.' . $extMap[$mime];
    $targetDir = dirname(__DIR__) . '/assets/uploads/bukti_transfer/';
    if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
    $targetFile = $targetDir . $filename;
    if (!move_uploaded_file($tmp, $targetFile)) {
        Koneksi::setFlash('error', 'Gagal menyimpan foto. Coba kembali.');
        header("Location: ../views/siswa/dashboard.php");
        exit;
    }
    $bukti_path = 'assets/uploads/bukti_transfer/' . $filename;
}

Koneksi::q(
    "INSERT INTO pembayaran (siswa_id, periode, jumlah, status, metode, tanggal_bayar, bukti_transfer, catatan)
     VALUES (?, ?, ?, 'pending', ?, NOW(), ?, ?)",
    [$siswa_id, $periode, $jumlah, $metode, $bukti_path, $catatan !== '' ? $catatan : null]
);

Koneksi::setFlash('success', 'Kontribusi kas berhasil dikirim! Status: Pending (menunggu verifikasi bendahara).');
header("Location: ../views/siswa/dashboard.php");
exit;