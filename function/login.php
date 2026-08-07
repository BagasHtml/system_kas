<?php
include '../database/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $result = Koneksi::q("SELECT * FROM admin WHERE username = ?", [$username]);
    $row = $result ? $result->fetch_assoc() : null;

    if ($row && password_verify($password, $row['password'])) {
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
