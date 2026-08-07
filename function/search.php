<?php
include '../database/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_POST['nama'])) {
    $nama = trim($_POST['nama']);
    $no_absen = (int)($_POST['nomor_absen'] ?? 0);

    $result = Koneksi::q("SELECT id, nama, nomor_absen FROM siswa WHERE nama = ? AND nomor_absen = ?", [$nama, $no_absen]);
    $siswa = $result ? $result->fetch_assoc() : null;

    if ($siswa) {
        session_regenerate_id(true);
        $_SESSION['siswa_id'] = $siswa['id'];
        $_SESSION['nama'] = $siswa['nama'];
        $_SESSION['siswa_absen'] = $siswa['nomor_absen'];
        header('Location: ../views/siswa/dashboard.php');
        exit;
    } else {
        $_SESSION['error'] = "Data siswa tidak ditemukan. Periksa kembali nama dan nomor absen.";
        header("Location: ../views/siswa/index.php");
        exit;
    }
}