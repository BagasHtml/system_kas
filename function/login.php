<?php
include '../database/db.php';

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

