<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$title = 'Masuk - Siswa';
include '../partials/header.php';
?>

<div class="auth-wrap auth-split">
    <aside class="auth-visual">
        <div class="auth-visual-top">
            <div class="auth-visual-logo">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <h1>Pantau Kas Kamu</h1>
            <p>Lihat status pembayaran kas dan riwayat penggunaan uang kelas secara transparan.</p>
        </div>
        <ul class="auth-visual-list">
            <li><i class="bi bi-check2-circle"></i> Cek status pembayaran per bulan</li>
            <li><i class="bi bi-check2-circle"></i> Lihat pengeluaran kas kelas</li>
            <li><i class="bi bi-check2-circle"></i> Bayar kas secara online</li>
        </ul>
    </aside>

    <main class="auth-panel">
        <div class="auth-card">
            <div class="auth-brand">
                <i class="bi bi-person-badge-fill"></i>
            </div>

            <h2 class="auth-title">Halo, Siswa!</h2>
            <p class="auth-sub">Masukkan nama dan nomor absen kamu untuk melihat status kas</p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="auth-error">
                    <i class="bi bi-exclamation-triangle-fill" style="margin-top:1px;"></i>
                    <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <form action="../../function/search.php" method="POST">
                <div class="auth-field">
                    <label for="nama">Nama Lengkap</label>
                    <div class="auth-input">
                        <i class="bi bi-person"></i>
                        <input type="text" id="nama" name="nama" placeholder="Masukkan nama kamu" required>
                    </div>
                </div>
                <div class="auth-field">
                    <label for="nomor_absen">Nomor Absen</label>
                    <div class="auth-input">
                        <i class="bi bi-123"></i>
                        <input type="number" id="nomor_absen" name="nomor_absen" placeholder="Contoh: 1" min="1" required>
                    </div>
                </div>
                <button type="submit" class="auth-submit">
                    Lihat Status Kas
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
