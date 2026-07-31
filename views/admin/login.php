<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}

$title = 'Login Admin';
include '../partials/header.php';
?>

<div class="auth-wrap auth-split">
    <aside class="auth-visual">
        <div class="auth-visual-top">
            <div class="auth-visual-logo">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1>Sistem Kas Kelas</h1>
            <p>Kelola pembayaran, siswa, dan pengeluaran kas kelas dalam satu panel yang rapi dan transparan.</p>
        </div>
        <ul class="auth-visual-list">
            <li><i class="bi bi-check2-circle"></i> Catat pembayaran kas per periode</li>
            <li><i class="bi bi-check2-circle"></i> Pantau saldo dan pengeluaran kelas</li>
            <li><i class="bi bi-check2-circle"></i> Cetak laporan kapan saja</li>
        </ul>
    </aside>

    <main class="auth-panel">
        <div class="auth-card">
            <div class="auth-brand">
                <i class="bi bi-shield-lock-fill"></i>
            </div>

            <h2 class="auth-title">Selamat datang kembali</h2>
            <p class="auth-sub">Masuk ke panel administrasi untuk mengelola kas kelas</p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="auth-error">
                    <i class="bi bi-exclamation-triangle-fill" style="margin-top:1px;"></i>
                    <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <form action="../../function/login.php" method="POST">
                <div class="auth-field">
                    <label for="username">Username</label>
                    <div class="auth-input">
                        <i class="bi bi-person"></i>
                        <input type="text" id="username" name="username" placeholder="Masukkan username" required>
                    </div>
                </div>
                <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="auth-input">
                        <i class="bi bi-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>
                <button type="submit" class="auth-submit">
                    Masuk
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="text-center">
                <a href="<?= BASE_URL ?>/index.php" class="auth-link">
                    <i class="bi bi-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </main>
</div>

<?php include '../partials/footer.php'; ?>
