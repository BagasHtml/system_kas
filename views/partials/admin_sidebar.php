<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once '../../database/db.php';

$username = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Bendahara';
?>
<nav class="admin-sidebar">
    <div class="sidebar-action">
        <div class="logo"><i class="bi bi-diamond-fill"></i></div>
        <div class="badge-h">K</div>
        <a href="siswa.php?tambah=1" class="action-btn" title="Tambah Siswa">
            <i class="bi bi-plus-lg"></i>
        </a>
        <div class="spacer"></div>
        <a href="../../function/logout.php" class="action-btn" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>

    <div class="sidebar-nav">
        <div class="brand">Kas Kelas</div>

        <div class="view-switcher">
            <span class="active">General</span>
            <span>Laporan</span>
        </div>

        <div class="nav-section">Menu</div>

        <a class="nav-link <?= $active == 'dashboard' ? 'active' : '' ?>" href="dashboard.php">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        <a class="nav-link <?= $active == 'siswa' ? 'active' : '' ?>" href="siswa.php">
            <i class="bi bi-people-fill"></i> Data Siswa
        </a>
        <a class="nav-link <?= $active == 'pembayaran' ? 'active' : '' ?>" href="pembayaran.php">
            <i class="bi bi-wallet-fill"></i> Pembayaran
        </a>
        <a class="nav-link <?= $active == 'pengeluaran' ? 'active' : '' ?>" href="pengeluaran.php">
            <i class="bi bi-cart-dash-fill"></i> Pengeluaran
        </a>
        <a class="nav-link <?= $active == 'laporan' ? 'active' : '' ?>" href="laporan.php">
            <i class="bi bi-file-text"></i> Laporan
        </a>

        <div class="nav-bottom">
            <a class="nav-link" href="#">
                <i class="bi bi-person-circle"></i> <?= $username ?>
            </a>
        </div>
    </div>
</nav>

<nav class="mobile-nav">
    <a class="<?= $active == 'dashboard' ? 'active' : '' ?>" href="dashboard.php">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
    <a class="<?= $active == 'siswa' ? 'active' : '' ?>" href="siswa.php">
        <i class="bi bi-people-fill"></i> Siswa
    </a>
    <a class="<?= $active == 'pembayaran' ? 'active' : '' ?>" href="pembayaran.php">
        <i class="bi bi-wallet-fill"></i> Bayar
    </a>
    <a class="<?= $active == 'pengeluaran' ? 'active' : '' ?>" href="pengeluaran.php">
        <i class="bi bi-cart-dash-fill"></i> Keluar Kas
    </a>
    <a class="<?= $active == 'laporan' ? 'active' : '' ?>" href="laporan.php">
        <i class="bi bi-file-text"></i> Laporan
    </a>
</nav>
