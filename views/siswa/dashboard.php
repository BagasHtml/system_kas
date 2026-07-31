<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$title = 'Dashboard - Siswa';
include '../partials/header.php';
include '../partials/helpers.php';
include_once '../../database/db.php';

$db = new Koneksi();

$siswa_id    = (int)($_SESSION['siswa_id'] ?? 0);
$siswa_nama  = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');
$siswa_absen = htmlspecialchars($_SESSION['siswa_absen'] ?? '-');

$pembayaran = [];
if ($siswa_id > 0) {
    $pembayaran = $db::q(
        "SELECT periode, jumlah, status, tanggal_bayar
         FROM pembayaran WHERE siswa_id = ? ORDER BY id DESC LIMIT 24",
        [$siswa_id]
    )->fetch_all(MYSQLI_ASSOC);
}

$lunas = 0;
$belum = 0;
$periode_lunas = 0;
foreach ($pembayaran as $p) {
    if ($p['status'] === 'lunas') {
        $lunas += (float)$p['jumlah'];
        $periode_lunas++;
    } else {
        $belum += (float)$p['jumlah'];
    }
}
$grand = $lunas + $belum;
$pct_lunas = $grand > 0 ? round($lunas / $grand * 100) : 0;

$kpis = [
    ['label' => 'Total Dibayar', 'value' => rupiah($lunas), 'note' => $periode_lunas . ' periode', 'tone' => 'success'],
    ['label' => 'Sisa Tunggakan', 'value' => rupiah($belum), 'tone' => 'danger'],
    ['label' => 'Periode Lunas', 'value' => (string)$periode_lunas, 'tone' => 'info'],
    ['label' => 'Tingkat Kelunasan', 'value' => $pct_lunas . '%', 'tone' => 'accent'],
];
?>

<div class="main-content student-page">
    <div class="dash-topbar">
        <div>
            <p class="student-eyebrow">Dashboard Siswa</p>
            <h1 class="dash-title">Hai, <?= $siswa_nama ?></h1>
            <p class="dash-subtitle">Nomor absen <?= $siswa_absen ?> &middot; pantau status kas kamu</p>
        </div>
        <div class="dash-topbar-actions">
            <a href="pengeluaran.php" class="dash-btn dash-btn-light">
                Pengeluaran Uang Kas kelas
            </a>
            <a href="../../function/logout.php" class="dash-btn dash-btn-danger">
                Keluar
            </a>
        </div>
    </div>

    <div class="dash-kpis">
        <?php foreach ($kpis as $k): ?>
            <?= kpi($k) ?>
        <?php endforeach; ?>
    </div>

    <div class="dash-card dash-pay-card" id="bayar">
        <div class="dash-card-head">
            <div>
                <div class="dash-card-title">Bayar Kas Online</div>
                <div class="dash-card-sub">Transfer Send Dana atau scan QRIS untuk membayar kas kelas</div>
            </div>
        </div>

        <div class="dash-pay">
            <div class="dash-pay-method">
                <div class="dash-pay-brand dana">
                    <?= ic('<path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/>', 20) ?>
                    Send Dana
                </div>
                <div class="dash-pay-info">
                    <div class="dash-pay-row">
                        <span class="lbl">Nomor Dana</span>
                        <span class="val num">0813-2175-0459</span>
                    </div>
                    <div class="dash-pay-row">
                        <span class="lbl">Atas Nama</span>
                        <span class="val">Bagas Tresna Nanda MS</span>
                    </div>
                </div>
            </div>

            <div class="dash-pay-method">
                <div class="dash-pay-brand qris">
                    <?= ic('<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h4v4h-4zM19 19h2v2h-2z"/>', 20) ?>
                    QRIS
                </div>
                <div class="dash-pay-qris">
                    <img src="<?= BASE_URL ?>/assets/img/qris.png" alt="QRIS Kas Kelas">
                </div>
                <div class="dash-pay-hint">Scan kode di atas setelah transfer</div>
            </div>
        </div>
    </div>

    <div class="dash-charts">
        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Riwayat Pembayaran</div>
                    <div class="dash-card-sub">Cek status pembayaran kas setiap periode</div>
                </div>
            </div>

            <?php if (empty($pembayaran)): ?>
                <div class="dash-empty">
                    <div class="t">Belum ada riwayat pembayaran</div>
                    <div class="s">Segera lakukan pembayaran kas ke pengurus kelas</div>
                </div>
            <?php else: ?>
                <div class="dash-table-wrap">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Periode</th>
                                <th>Jumlah</th>
                                <th>Tanggal Bayar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($pembayaran as $p): ?>
                                <tr>
                                    <td style="color:var(--text-muted);"><?= $no++ ?></td>
                                    <td><span style="font-weight:600;"><?= htmlspecialchars($p['periode']) ?></span></td>
                                    <td><span class="dash-amount"><?= rupiah((float)$p['jumlah']) ?></span></td>
                                    <td><?= $p['tanggal_bayar'] ? date('d/m/Y', strtotime($p['tanggal_bayar'])) : '-' ?></td>
                                    <td><?= status_pill($p['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Progres Pembayaran</div>
                    <div class="dash-card-sub">Ringkasan kas pribadi kamu</div>
                </div>
            </div>

            <div class="dash-donut-wrap">
                <div class="dash-donut" style="--p:<?= $pct_lunas ?>%;--c1:var(--accent);--c2:#d9a241;">
                    <div class="dash-donut-center">
                        <span class="pct"><?= $pct_lunas ?>%</span>
                    </div>
                </div>
                <div class="dash-legend">
                    <div class="dash-legend-item">
                        <span class="sw" style="background:var(--accent);"></span>
                        <span class="name">Lunas<span class="sub"><?= $periode_lunas ?> periode</span></span>
                        <span class="val"><?= rupiah($lunas) ?></span>
                    </div>
                    <div class="dash-legend-item">
                        <span class="sw" style="background:#d9a241;"></span>
                        <span class="name">Belum<span class="sub"><?= count($pembayaran) - $periode_lunas ?> periode</span></span>
                        <span class="val"><?= rupiah($belum) ?></span>
                    </div>
                </div>
            </div>

            <div class="dash-progress">
                <div class="top">
                    <span class="lbl">Progres pembayaran</span>
                    <span class="val"><?= $pct_lunas ?>%</span>
                </div>
                <div class="track">
                    <div class="fill" style="width:<?= $pct_lunas ?>%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<nav class="mobile-nav">
    <a class="active" href="dashboard.php">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
    <a href="pengeluaran.php">
        <i class="bi bi-cart-dash-fill"></i> Pengeluaran
    </a>
    <a href="../../function/logout.php" class="quit">
        <i class="bi bi-box-arrow-right"></i> Keluar
    </a>
</nav>

<?php include '../partials/footer.php'; ?>
