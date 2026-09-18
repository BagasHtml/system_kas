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
        <a class="nav-link <?= $active == 'pemasukan' ? 'active' : '' ?>" href="pemasukan.php">
            <i class="bi bi-wallet-fill"></i> Pemasukan
        </a>
        <a class="nav-link <?= $active == 'pengeluaran' ? 'active' : '' ?>" href="pengeluaran.php">
            <i class="bi bi-cart-dash-fill"></i> Pengeluaran
        </a>
        <a class="nav-link <?= $active == 'belanja' ? 'active' : '' ?>" href="belanja.php">
            <i class="bi bi-bag-check-fill"></i> Target Belanja
        </a>
        <a class="nav-link <?= $active == 'pengaturan' ? 'active' : '' ?>" href="pengaturan.php">
            <i class="bi bi-gear-fill"></i> Pengaturan
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

<?php $mobile_active = $active ?? 'dashboard'; ?>
<?php $mobile_user = $username; ?>
<?php $mobile_user_role = 'Bendahara'; ?>
<?php $mobile_links = [
    ['key' => 'dashboard',   'href' => 'dashboard.php',     'icon' => 'bi-grid-1x2-fill', 'label' => 'Dashboard'],
    ['key' => 'siswa',       'href' => 'siswa.php',         'icon' => 'bi-people-fill',   'label' => 'Data Siswa'],
    ['key' => 'pemasukan',   'href' => 'pemasukan.php',     'icon' => 'bi-wallet-fill',   'label' => 'Pemasukan'],
    ['key' => 'pengeluaran', 'href' => 'pengeluaran.php',   'icon' => 'bi-cart-dash-fill','label' => 'Pengeluaran'],
    ['key' => 'belanja',     'href' => 'belanja.php',       'icon' => 'bi-bag-check-fill','label' => 'Target Belanja'],
    ['key' => 'pengaturan',  'href' => 'pengaturan.php',    'icon' => 'bi-gear-fill',     'label' => 'Pengaturan'],
    ['key' => 'laporan',     'href' => 'laporan.php',       'icon' => 'bi-file-text',     'label' => 'Laporan'],
]; ?>
<?php include __DIR__ . '/mobile_nav.php'; ?>
