<?php
require_once __DIR__ . '/../../app/controllers/AuthController.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['siswa_id'])) {
    header("Location: ../../index.php");
    exit;
}
if (AuthController::isAdmin()) {
    header("Location: dashboard.php");
    exit;
}

$title = 'Masuk Bendahara';
include '../partials/header.php';
?>

<div class="auth-wrap auth-split">
    <aside class="auth-visual">
        <div class="auth-visual-top">
            <div class="auth-visual-logo">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1>Kelola Kas Kelas</h1>
            <p>Masuk ke dashboard bendahara untuk mengelola pembayaran, pengeluaran, dan laporan kas kelas secara transparan.</p>
        </div>
        <ul class="auth-visual-list">
            <li><i class="bi bi-check2-circle"></i> Kelola pembayaran kas siswa</li>
            <li><i class="bi bi-check2-circle"></i> Catat pengeluaran kas kelas</li>
            <li><i class="bi bi-check2-circle"></i> Cetak laporan keuangan</li>
        </ul>
    </aside>

    <main class="auth-panel">
        <div class="auth-card">
            <div class="auth-brand">
                <i class="bi bi-shield-lock-fill"></i>
            </div>

            <h2 class="auth-title">Login Bendahara</h2>
            <p class="auth-sub">Masukkan username dan password bendahara untuk masuk ke dashboard</p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="auth-error">
                    <i class="bi bi-exclamation-triangle-fill" style="margin-top:1px;"></i>
                    <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <form action="../../function/login.php" method="POST">
                <?php require_once __DIR__ . '/../../database/db.php'; echo Koneksi::csrfField(); ?>
                <div class="auth-field">
                    <label for="username">Username</label>
                    <div class="auth-input">
                        <i class="bi bi-person"></i>
                        <input type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="username">
                    </div>
                </div>
                <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="auth-input">
                        <i class="bi bi-key"></i>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                    </div>
                </div>
                <button type="submit" class="auth-submit">
                    Masuk Dashboard
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="text-center">
                <a href="../siswa/dashboard.php" class="auth-link">
                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard Siswa
                </a>
            </div>
        </div>
    </main>
</div>

<?php include '../partials/footer.php'; ?>
