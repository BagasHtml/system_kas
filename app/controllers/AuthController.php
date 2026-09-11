<?php

require_once __DIR__ . '/../../database/db.php';

/**
 * Tanggung jawab: otentikasi (guard halaman) dan proses login/logout.
 *
 * Arsitektur SINGLE LOGIN + RBAC:
 * Hanya ada SATU form login: nama + nomor absen (siswa).
 * Peran (role) ditentukan dari kolom `role` pada tabel `siswa`:
 *   - 'bendahara' : siswa yang berhak mengelola dashboard admin.
 *   - 'siswa'     : siswa biasa, hanya melihat status kas miliknya.
 *
 * Siswa dengan role 'bendahara' tetap login lewat form yang sama dan,
 * selain dashboard siswa, bisa membuka dashboard admin dari sidebar.
 */
class AuthController
{
    public const ROLE_BENDAHARA = 'bendahara';
    public const ROLE_SISWA     = 'siswa';

    /**
     * Guard RBAC sentral. Menghentikan akses bila peran sesi tidak termasuk
     * $roles, lalu alihkan ke $redirectTo.
     */
    public static function requireRole(array $roles, string $redirectTo = 'index.php'): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $role = $_SESSION['role'] ?? null;
        if (!in_array($role, $roles, true)) {
            header("Location: " . $redirectTo);
            exit;
        }
    }

    /**
     * Guard halaman siswa. Semua siswa (termasuk bendahara) boleh lewat,
     * karena bendahara juga seorang siswa.
     */
    public static function requireSiswa(): void
    {
        self::requireRole([self::ROLE_SISWA, self::ROLE_BENDAHARA], 'index.php');
    }

    /**
     * Guard halaman dashboard bendahara.
     *
     * Peran ini bersifat dua lapis:
     *  1. User harus sudah login sebagai siswa (sesi `siswa_id` ada).
     *  2. User harus sudah melewati login bendahara (username + password,
     *     ditandai dengan flag `is_admin` di sesi).
     *
     * Bila lapis pertama gagal, arahkan ke halaman utama. Bila hanya belum
     * login bendahara, arahkan ke form login bendahara.
     */
    public static function requireAdmin(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['siswa_id'])) {
            header("Location: ../../index.php");
            exit;
        }
        if (empty($_SESSION['is_admin'])) {
            header("Location: login.php");
            exit;
        }
    }

    /**
     * Cek apakah sesi telah login sebagai bendahara (username + password).
     */
    public static function isAdmin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return !empty($_SESSION['is_admin']);
    }

    /**
     * Cek apakah sesi memiliki peran tertentu.
     */
    public static function hasRole(string $role): bool
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return ($_SESSION['role'] ?? null) === $role;
    }

    /**
     * Proses login bendahara (dipanggil dari function/login.php).
     * Memvalidasi username + password terhadap tabel `admin`.
     * Dipisah dari login siswa (nama + absen) karena dasbor bendahara
     * memerlukan otentikasi tersendiri.
     */
    public static function adminLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        /* Harus berasal dari sesi siswa yang aktif. */
        if (empty($_SESSION['siswa_id'])) {
            header("Location: ../../index.php");
            exit;
        }

        if (isset($_POST['username'])) {
            if (!Koneksi::csrfCheck()) {
                $_SESSION['error'] = "Sesi tidak valid. Silakan coba lagi.";
                header("Location: ../admin/login.php");
                exit;
            }

            $username = trim($_POST['username']);
            $password = (string)($_POST['password'] ?? '');

            $result = Koneksi::q(
                "SELECT id, username, password FROM admin WHERE username = ? AND is_active = 1",
                [$username]
            );
            $admin = $result ? $result->fetch_assoc() : null;

            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['is_admin']    = true;
                $_SESSION['admin_id']    = (int)$admin['id'];
                $_SESSION['username']    = $admin['username'];
                if (empty($_SESSION['nama'])) {
                    $_SESSION['nama'] = $admin['username'];
                }
                Koneksi::q("UPDATE admin SET last_login = NOW() WHERE id = ?", [(int)$admin['id']]);

                header("Location: ../views/admin/dashboard.php");
                exit;
            }

            $_SESSION['error'] = "Username atau password bendahara salah.";
            header("Location: ../admin/login.php");
            exit;
        }
    }

    /**
     * Pastikan tabel `siswa` memiliki kolom `role` (migrasi DB lama).
     * Tidak menetapkan bendahara secara hardcode di sini; peran bendahara
     * diatur melalui database.
     */
    private static function ensureSiswaRoleColumn(): void
    {
        $cols = Koneksi::q("SHOW COLUMNS FROM siswa");
        $existing = [];
        if ($cols) {
            while ($c = $cols->fetch_assoc()) {
                $existing[] = $c['Field'];
            }
        }
        if (!in_array('role', $existing, true)) {
            Koneksi::q("ALTER TABLE siswa ADD COLUMN role ENUM('siswa', 'bendahara') NOT NULL DEFAULT 'siswa' AFTER nomor_absen");
        }
    }

    /**
     * Proses login (dipanggil dari function/search.php). Satu-satunya cara masuk.
     * Cocokkan nama + nomor absen, lalu muat peran dari database.
     */
    public static function siswaLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        self::ensureSiswaRoleColumn();

        if (isset($_POST['nama'])) {
            if (!Koneksi::csrfCheck()) {
                $_SESSION['error'] = "Sesi tidak valid. Silakan coba lagi.";
                header("Location: ../views/siswa/index.php");
                exit;
            }

            $nama = trim($_POST['nama']);
            $no_absen = (int)($_POST['nomor_absen'] ?? 0);

            $result = Koneksi::q("SELECT id, nama, nomor_absen, role FROM siswa WHERE nama = ? AND nomor_absen = ?", [$nama, $no_absen]);
            $siswa = $result ? $result->fetch_assoc() : null;

            if ($siswa) {
                $role = ($siswa['role'] ?? self::ROLE_SISWA) === self::ROLE_BENDAHARA
                    ? self::ROLE_BENDAHARA
                    : self::ROLE_SISWA;

                session_regenerate_id(true);
                $_SESSION['siswa_id']    = (int)$siswa['id'];
                $_SESSION['nama']        = $siswa['nama'];
                $_SESSION['siswa_absen'] = (int)$siswa['nomor_absen'];
                $_SESSION['role']        = $role;
                $_SESSION['is_bendahara'] = ($role === self::ROLE_BENDAHARA);

                header('Location: ../views/siswa/dashboard.php');
                exit;
            } else {
                $_SESSION['error'] = "Data siswa tidak ditemukan. Periksa kembali nama dan nomor absen.";
                header("Location: ../views/siswa/index.php");
                exit;
            }
        }
    }

    /**
     * Proses logout (dipanggil dari function/logout.php).
     */
    public static function adminLogout(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_unset();
        session_destroy();
        header('location: ../index.php');
    }
}
