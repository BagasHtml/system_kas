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

// Periode otomatis bulan ini (untuk tracking, bukan batasan)
$periode = date('Y-m');

if ($jumlah <= 0) {
    Koneksi::setFlash('error', 'Jumlah setoran harus lebih dari Rp 0.');
    header("Location: ../views/siswa/dashboard.php");
    exit;
}

// Handle upload bukti transfer
$bukti_path = null;
if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['bukti_transfer']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed)) {
        Koneksi::setFlash('error', 'Format foto harus JPG, PNG, atau WebP.');
        header("Location: ../views/siswa/dashboard.php");
        exit;
    }
    if ($_FILES['bukti_transfer']['size'] > 5 * 1024 * 1024) {
        Koneksi::setFlash('error', 'Ukuran foto maksimal 5MB.');
        header("Location: ../views/siswa/dashboard.php");
        exit;
    }
    $filename = 'bukti_' . $siswa_id . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
    $targetDir = dirname(__DIR__, 2) . '/assets/uploads/bukti_transfer/';
    if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
    $targetFile = $targetDir . $filename;
    if (move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $targetFile)) {
        $bukti_path = 'assets/uploads/bukti_transfer/' . $filename;
    }
}

Koneksi::q(
    "INSERT INTO pembayaran (siswa_id, periode, jumlah, status, metode, tanggal_bayar, bukti_transfer, catatan)
     VALUES (?, ?, ?, 'pending', ?, NOW(), ?, ?)",
    [$siswa_id, $periode, $jumlah, $metode, $bukti_path, $catatan !== '' ? $catatan : null]
);

Koneksi::setFlash('success', 'Kontribusi kas berhasil dikirim! Status: Pending (menunggu verifikasi bendahara).');
header("Location: ../views/siswa/dashboard.php");
exit;