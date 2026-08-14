<?php

require_once __DIR__ . '/../../database/db.php';

/**
 * Tanggung jawab: otentikasi (guard halaman) dan proses login/logout
 * untuk admin maupun siswa.
 */
class AuthController
{
    /**
     * Guard halaman admin: wajib sudah login sebagai admin.
     */
    public static function requireAdmin(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['username'])) {
            header("Location: login.php");
            exit;
        }
    }

    /**
     * Guard halaman siswa: wajib sudah "login" lewat nama + nomor absen.
     */
    public static function requireSiswa(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['siswa_id'])) {
            header("Location: index.php");
            exit;
        }
    }

    /**
     * Proses login admin (dipanggil dari function/login.php).
     * Termasuk inisialisasi tabel admin + akun default bila belum ada.
     */
    public static function adminLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Pastikan tabel admin ada dan akun default admin tersedia jika tabel masih kosong
        try {
            $checkTable = Koneksi::q("SELECT 1 FROM admin LIMIT 1");
        } catch (\Throwable $e) {
            $checkTable = false;
        }

        if (!$checkTable) {
            Koneksi::q("CREATE TABLE IF NOT EXISTS admin (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
        }

        $countAdmin = Koneksi::q("SELECT COUNT(*) as total FROM admin");
        if ($countAdmin && ($rowC = $countAdmin->fetch_assoc()) && (int)$rowC['total'] === 0) {
            $defaultHash = password_hash('admin123', PASSWORD_DEFAULT);
            Koneksi::q("INSERT INTO admin (username, password) VALUES (?, ?)", ['admin', $defaultHash]);
        }

        if (isset($_POST['username'])) {
            $username = trim($_POST['username']);
            $password = $_POST['password'];

            $result = Koneksi::q("SELECT * FROM admin WHERE username = ?", [$username]);
            $row = $result ? $result->fetch_assoc() : null;

            $loginSuccess = false;

            if ($row) {
                if (password_verify($password, $row['password'])) {
                    $loginSuccess = true;
                } elseif ($row['password'] === $password || hash_equals($row['password'], md5($password)) || hash_equals($row['password'], hash('sha256', $password))) {
                    // Update otomatis ke format password_hash modern jika sebelumnya plain text / hash lama
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    Koneksi::q("UPDATE admin SET password = ? WHERE id = ?", [$newHash, (int)$row['id']]);
                    $loginSuccess = true;
                }
            }

            if ($loginSuccess) {
                session_regenerate_id(true);
                $_SESSION['username'] = $row['username'];
                header("Location: ../views/admin/dashboard.php");
                exit;
            } else {
                $_SESSION['error'] = "Username / password salah!";
                header("Location: ../views/admin/login.php");
                exit;
            }
        }
    }

    /**
     * Proses logout admin (dipanggil dari function/logout.php).
     */
    public static function adminLogout(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_unset();
        session_destroy();
        header('location: ../index.php');
    }

    /**
     * Proses "login" siswa: cocokkan nama + nomor absen (dipanggil dari function/search.php).
     */
    public static function siswaLogin(): void
    {
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
    }
}
