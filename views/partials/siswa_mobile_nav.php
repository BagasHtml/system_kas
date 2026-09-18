<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<?php
$mobile_active = $active ?? 'dashboard';
$mobile_user   = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');
$mobile_user_role = !empty($_SESSION['is_bendahara']) ? 'Bendahara' : 'Siswa';
$mobile_links  = [
    ['key' => 'dashboard', 'href' => 'dashboard.php',            'icon' => 'bi-grid-1x2-fill', 'label' => 'Dashboard'],
    ['key' => 'riwayat',   'href' => 'dashboard.php#riwayat',    'icon' => 'bi-clock-history', 'label' => 'Riwayat Saya'],
    ['key' => 'pengeluaran','href' => 'pengeluaran.php',         'icon' => 'bi-cart-dash-fill', 'label' => 'Pengeluaran'],
    ['key' => 'belanja',   'href' => 'belanja.php',              'icon' => 'bi-bag-check-fill', 'label' => 'Target Belanja'],
];
if (!empty($_SESSION['is_bendahara'])) {
    $mobile_links[] = ['key' => 'admin', 'href' => '../admin/dashboard.php', 'icon' => 'bi-shield-lock-fill', 'label' => 'Dashboard Bendahara'];
}
include __DIR__ . '/mobile_nav.php';
?>