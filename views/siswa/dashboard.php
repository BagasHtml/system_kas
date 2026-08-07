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

/* Target kas kelas (kesepakatan kelas, diatur bendahara). */
$target_map = Koneksi::targetMap();
$total_target = Koneksi::totalTarget($target_map);
$ada_target = $total_target > 0;
$kelas_collected = 0.0;
$kelas_remainder = 0.0;
if ($ada_target) {
    $kc = $db::q("SELECT COALESCE(SUM(jumlah), 0) t FROM pembayaran WHERE status = 'lunas'")->fetch_assoc();
    $kelas_collected = (float)($kc['t'] ?? 0);
    $kelas_remainder = max(0, $total_target - $kelas_collected);
}
$pct_kelas = $ada_target ? min(100, round($kelas_collected / $total_target * 100)) : 0;

$banner = [
    ['icon' => 'bi bi-wallet2', 'label' => 'Total Dibayar', 'value' => rupiah($lunas), 'note' => $periode_lunas . ' bulan terkumpul'],
    ['icon' => 'bi bi-hourglass-split', 'label' => 'Belum Terkumpul', 'value' => rupiah($belum), 'note' => $belum > 0 ? 'pembayaranmu yang belum tercatat' : 'semua sudah lunas'],
    ['icon' => 'bi bi-calendar-check', 'label' => 'Bulan Terkumpul', 'value' => (string)$periode_lunas, 'note' => 'dari ' . count($pembayaran) . ' bulan'],
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

    <div class="dash-banner cols-3">
        <?php foreach ($banner as $b): ?>
            <div class="dash-banner-col">
                <div class="dash-banner-icon"><i class="bi <?= $b['icon'] ?>"></i></div>
                <div>
                    <span class="dash-banner-label"><?= $b['label'] ?></span>
                    <span class="dash-banner-value"><?= $b['value'] ?></span>
                    <span class="dash-banner-note"><?= $b['note'] ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="dash-charts">
        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Bayar Kas Online</div>
                    <div class="dash-card-sub">Transfer Send Dana atau scan QRIS untuk membayar kas kelas</div>
                </div>
                <span class="dash-year-label"><?= htmlspecialchars(Koneksi::periodeLabel(date('Y-m'))) ?></span>
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
                        <div class="dash-pay-row">
                            <span class="lbl">Bulan Kas</span>
                            <span class="val"><?= htmlspecialchars(Koneksi::periodeLabel(date('Y-m'))) ?></span>
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

        <div class="dash-stack">
            <div class="dash-card">
                <div class="dash-card-head">
                    <div>
                        <div class="dash-card-title">Target Kas Kelas</div>
                        <div class="dash-card-sub">Besaran kas yang disepakati kelas bersama</div>
                    </div>
                </div>

                <?php if (!$ada_target): ?>
                    <div class="dash-empty">
                        <div class="t">Target belum ditetapkan</div>
                        <div class="s">Bendahara belum menetapkan target kas untuk kelas</div>
                    </div>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="color:var(--text-secondary);">Kesepakatan kelas</span>
                            <span style="font-weight:700;"><?= rupiah($total_target) ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="color:var(--text-secondary);">Terkumpul</span>
                            <span style="font-weight:700;color:var(--success);"><?= rupiah($kelas_collected) ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="color:var(--text-secondary);">Belum terkumpul</span>
                            <span style="font-weight:700;color:var(--danger);"><?= rupiah($kelas_remainder) ?></span>
                        </div>
                    </div>

                    <div class="dash-progress" style="margin-top:14px;">
                        <div class="top">
                            <span class="lbl">Terkumpul dari target</span>
                            <span class="val"><?= $pct_kelas ?>%</span>
                        </div>
                        <div class="track">
                            <div class="fill" style="width:<?= $pct_kelas ?>%;"></div>
                        </div>
                    </div>

                    <?php if ($kelas_remainder <= 0): ?>
                        <div class="dash-notice" style="margin-top:14px;">
                            <div><b>Target kas kelas tercapai.</b> Terima kasih atas partisipasi teman-teman!</div>
                        </div>
                    <?php else: ?>
                        <div class="dash-notice" style="margin-top:14px;">
                            <div>Sudah terkumpul <b><?= rupiah($kelas_collected) ?></b> dari target kelas. Terima kasih sudah ikut berpartisipasi!</div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="dash-promo">
                <h4>Uang kas dipakai untuk apa?</h4>
                <p>Semua pengeluaran kelas dicatat transparan. Cek rinciannya kapan saja.</p>
                <a href="pengeluaran.php" class="dash-promo-btn">
                    Lihat Pengeluaran <?= ic('<path d="M9 6l6 6-6 6"/>', 14) ?>
                </a>
            </div>
        </div>
    </div>

    <div class="dash-charts">
        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Riwayat Pembayaran</div>
                    <div class="dash-card-sub">Cek status pembayaran kas setiap bulan</div>
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
                                <th>Bulan</th>
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
                                    <td><span style="font-weight:600;"><?= htmlspecialchars(Koneksi::periodeLabel($p['periode'])) ?></span></td>
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
                <div class="dash-donut" style="--p:<?= $pct_lunas ?>%;--c1:var(--accent);--c2:var(--yellow);">
                    <div class="dash-donut-center">
                        <span class="pct"><?= $pct_lunas ?>%</span>
                    </div>
                </div>
                <div class="dash-legend">
                    <div class="dash-legend-item">
                        <span class="sw" style="background:var(--accent);"></span>
                        <span class="name">Terkumpul<span class="sub"><?= $periode_lunas ?> bulan</span></span>
                        <span class="val"><?= rupiah($lunas) ?></span>
                    </div>
                    <div class="dash-legend-item">
                        <span class="sw" style="background:var(--yellow);"></span>
                        <span class="name">Belum Terkumpul<span class="sub"><?= count($pembayaran) - $periode_lunas ?> bulan</span></span>
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

            <div style="margin-top:18px;border-top:1px solid var(--border);padding-top:14px;display:flex;flex-direction:column;gap:12px;font-size:13px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:var(--text-secondary);">Total Tagihan</span>
                    <span style="font-weight:700;"><?= rupiah($grand) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:var(--text-secondary);">Sudah Dibayar</span>
                    <span style="font-weight:700;color:var(--success);"><?= rupiah($lunas) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:var(--text-secondary);">Belum Dibayar</span>
                    <span style="font-weight:700;color:var(--danger);"><?= rupiah($belum) ?></span>
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
