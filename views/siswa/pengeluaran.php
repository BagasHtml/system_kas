<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$title = 'Pengeluaran Kas - Siswa';
include '../partials/header.php';
include '../partials/helpers.php';
include_once '../../database/db.php';

if (!isset($_SESSION['siswa_id'])) {
    header("Location: index.php");
    exit;
}

$db = new Koneksi();

$agg = $db::q("SELECT COALESCE(SUM(jumlah), 0) AS total, COUNT(*) AS jml FROM pengeluaran")->fetch_assoc();
$total = (float)($agg['total'] ?? 0);
$total_transaksi = (int)($agg['jml'] ?? 0);

$masuk = $db::q("SELECT COALESCE(SUM(CASE WHEN status = 'lunas' THEN jumlah END), 0) AS t FROM pembayaran")
    ->fetch_assoc();
$pemasukan = (float)($masuk['t'] ?? 0);
$saldo = $pemasukan - $total;

$per_page = 8;
$page = max(1, (int)($_GET['hal'] ?? 1));
$total_pages = max(1, (int)ceil($total_transaksi / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$pengeluaran = $db::q(
    "SELECT keterangan, jumlah, tanggal FROM pengeluaran ORDER BY tanggal DESC, id DESC LIMIT ? OFFSET ?",
    [$per_page, $offset]
)->fetch_all(MYSQLI_ASSOC);

$start_item = $total_transaksi === 0 ? 0 : $offset + 1;
$end_item = min($offset + count($pengeluaran), $total_transaksi);

$pages = [];
for ($i = 1; $i <= $total_pages; $i++) {
    if ($i === 1 || $i === $total_pages || abs($i - $page) <= 2) {
        if (($pages[count($pages) - 1] ?? 0) !== $i - 1 && $pages[count($pages) - 1] !== '...') {
            $pages[] = '...';
        }
        $pages[] = $i;
    }
}

$siswa_nama  = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');
$siswa_absen = htmlspecialchars($_SESSION['siswa_absen'] ?? '-');

$kpis = [
    ['label' => 'Sisa Saldo Kas', 'value' => rupiah($saldo), 'tone' => 'accent'],
    ['label' => 'Total Pengeluaran', 'value' => rupiah($total), 'note' => $total_transaksi . ' transaksi', 'tone' => 'danger'],
    ['label' => 'Total Transaksi', 'value' => (string)$total_transaksi, 'tone' => 'info'],
    ['label' => 'Rata-rata / Transaksi', 'value' => rupiah($total_transaksi > 0 ? $total / $total_transaksi : 0), 'tone' => 'warn'],
];
?>

<div class="main-content student-page">
    <div class="dash-topbar">
        <div>
            <p class="student-eyebrow">Pengeluaran Kas</p>
            <h1 class="dash-title">Catatan Pengeluaran</h1>
            <p class="dash-subtitle">Semua penggunaan uang kas kelas dicatat transparan di sini</p>
        </div>
        <div class="dash-topbar-actions">
            <a href="dashboard.php#bayar" class="dash-btn dash-btn-primary">
                Bayar Kas Online
            </a>
            <a href="dashboard.php" class="dash-btn dash-btn-light">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="dash-notice">
        <div>
            <b>Transparan.</b>
            Uang kas kelas dikelola pengurus dan dipakai untuk keperluan bersama. Setiap pengeluaran ditampilkan di sini.
        </div>
    </div>

    <div class="dash-kpis">
        <?php foreach ($kpis as $k): ?>
            <?= kpi($k) ?>
        <?php endforeach; ?>
    </div>

    <div class="dash-card">
        <div class="dash-card-head">
            <div>
                <div class="dash-card-title">Daftar Pengeluaran</div>
                <div class="dash-card-sub">
                    <?= $total_transaksi > 0 ? "Menampilkan $start_item&ndash;$end_item dari $total_transaksi transaksi &middot; total " . rupiah($total) : 'Belum ada transaksi' ?>
                </div>
            </div>
        </div>

        <?php if (empty($pengeluaran)): ?>
            <div class="dash-empty">
                <div class="t">Belum ada pengeluaran</div>
                <div class="s">Belum ada catatan pengeluaran kas kelas</div>
            </div>
        <?php else: ?>
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">No</th>
                            <th style="width:140px;">Tanggal</th>
                            <th>Keterangan</th>
                            <th style="text-align:right;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = $offset + 1; ?>
                        <?php foreach ($pengeluaran as $p): ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $no++ ?></td>
                                <td style="color:var(--text-secondary);"><?= date('d/m/Y', strtotime($p['tanggal'])) ?></td>
                                <td><span style="font-weight:600;"><?= htmlspecialchars($p['keterangan']) ?></span></td>
                                <td style="text-align:right;"><span class="dash-amount" style="color:var(--danger);">- <?= rupiah((float)$p['jumlah']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="dash-pagination">
                    <span class="dash-pg-info">Halaman <?= $page ?> dari <?= $total_pages ?></span>
                    <div class="dash-pg-nav">
                        <a href="?hal=<?= max(1, $page - 1) ?>" class="dash-pg-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                            <?= ic('<path d="M15 6l-6 6 6 6"/>', 14) ?>
                            Sebelumnya
                        </a>
                        <?php foreach ($pages as $pg): ?>
                            <?php if ($pg === '...'): ?>
                                <span class="dash-pg-ellipsis">...</span>
                            <?php else: ?>
                                <a href="?hal=<?= $pg ?>" class="dash-pg-btn <?= $pg === $page ? 'active' : '' ?>"><?= $pg ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <a href="?hal=<?= min($total_pages, $page + 1) ?>" class="dash-pg-btn <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            Berikutnya
                            <?= ic('<path d="M9 6l6 6-6 6"/>', 14) ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<nav class="mobile-nav">
    <a href="dashboard.php">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
    <a class="active" href="pengeluaran.php">
        <i class="bi bi-cart-dash-fill"></i> Pengeluaran
    </a>
    <a href="../../function/logout.php" class="quit">
        <i class="bi bi-box-arrow-right"></i> Keluar
    </a>
</nav>

<?php include '../partials/footer.php'; ?>
