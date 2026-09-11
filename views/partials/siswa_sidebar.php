<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="admin-sidebar">
    <div class="sidebar-action">
        <div class="logo"><i class="bi bi-diamond-fill"></i></div>
        <div class="badge-h">S</div>
        <div class="spacer"></div>
        <a href="../../function/logout.php" class="action-btn" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>

    <div class="sidebar-nav">
        <div class="brand">Kas Kelas</div>

        <div class="nav-section">Menu</div>

        <a class="nav-link <?= ($active ?? 'dashboard') == 'dashboard' ? 'active' : '' ?>" href="dashboard.php">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        <a class="nav-link" href="dashboard.php#riwayat">
            <i class="bi bi-clock-history"></i> Riwayat Saya
        </a>
        <a class="nav-link <?= ($active ?? '') == 'pengeluaran' ? 'active' : '' ?>" href="pengeluaran.php">
            <i class="bi bi-cart-dash-fill"></i> Pengeluaran
        </a>
        <a class="nav-link <?= ($active ?? '') == 'belanja' ? 'active' : '' ?>" href="belanja.php">
            <i class="bi bi-bag-check-fill"></i> Target Belanja
        </a>

        <?php if (!empty($_SESSION['is_bendahara'])): ?>
        <div class="nav-section">Admin</div>
        <a class="nav-link <?= ($active ?? '') == 'admin' ? 'active' : '' ?>" href="../admin/dashboard.php">
            <i class="bi bi-shield-lock-fill"></i> Dashboard Bendahara
        </a>
        <?php endif; ?>

        <div class="nav-bottom">
            <a class="nav-link" href="#">
                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['nama'] ?? 'Siswa') ?>
            </a>
        </div>
    </div>
</nav>
