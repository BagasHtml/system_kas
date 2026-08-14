<?php
require_once __DIR__ . '/../../app/controllers/AdminDashboardController.php';
extract(AdminDashboardController::handle(), EXTR_SKIP);

$title = 'Dashboard - Admin';
$active = 'dashboard';

include '../partials/header.php';
include '../partials/admin_sidebar.php';
include '../partials/helpers.php';

$banner = [
    ['icon' => 'bi bi-wallet2', 'label' => 'Total Pemasukan', 'value' => rupiah($pemasukan), 'note' => $lunas_count . ' catatan terkumpul'],
    ['icon' => 'bi bi-people-fill', 'label' => 'Total Siswa', 'value' => (string)$total_siswa, 'note' => 'terdaftar di kelas'],
    ['icon' => 'bi bi-cart-dash', 'label' => 'Total Pengeluaran', 'value' => rupiah($pengeluaran_total), 'note' => $pengeluaran_count . ' transaksi'],
    ['icon' => 'bi bi-piggy-bank', 'label' => 'Saldo Kas', 'value' => rupiah($saldo), 'note' => 'sisa kas kelas'],
];

$kpis = [
    ['label' => 'Total Pemasukan Kas', 'value' => rupiah($pemasukan), 'note' => 'dari ' . $lunas_count . ' pembayaran lunas', 'tone' => 'success'],
    ['label' => 'Total Pengeluaran Kas', 'value' => rupiah($pengeluaran_total), 'note' => $pengeluaran_count . ' transaksi keluar', 'tone' => 'danger'],
    ['label' => 'Sisa Saldo Kas Kelas', 'value' => rupiah($saldo), 'note' => $saldo >= 0 ? 'Saldo kas aman' : 'Perlu evaluasi kas', 'tone' => 'accent'],
    ['label' => 'Total Siswa', 'value' => (string)$total_siswa, 'note' => 'rata-rata ' . rupiah($rata_rata) . '/siswa', 'tone' => 'info'],
];
?>

<div class="main-content dash-page">
    <div class="dash-topbar">
        <div>
            <h1 class="dash-title">Dashboard</h1>
            <p class="dash-subtitle">Selamat datang, <?= $username ?> &middot; ringkasan keuangan kas kelas</p>
        </div>
        <div class="dash-topbar-actions">
            <div class="dash-datechip">
                <?= ic('<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>', 14) ?>
                <div class="dash-user">
            <span class="dash-username"><?= $username ?></span>
            <div class="dash-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
        </div>
    </div>

    <?php if ($pending_count > 0): ?>
        <div class="alert alert-warning" style="border-radius:12px;display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;padding:14px 18px;background:#fffcf5;border:1px solid #fef3c7;">
            <div style="display:flex;align-items:center;gap:12px;">
                <i class="bi bi-bell-fill" style="font-size:22px;color:#b45309;"></i>
                <div>
                    <b style="color:#92400e;">Ada <?= $pending_count ?> pembayaran masuk dari siswa yang perlu verifikasi!</b>
                    <div style="font-size:12px;color:#b45309;">Siswa telah mengirim bukti transfer dan menunggu persetujuan Anda.</div>
                </div>
            </div>
            <a href="pembayaran.php" class="dash-btn dash-btn-primary" style="background:#b45309;border-color:#b45309;padding:7px 16px;font-size:12px;white-space:nowrap;">
                <i class="bi bi-check2-square"></i> Ke Halaman Verifikasi
            </a>
        </div>
    <?php endif; ?>

    <div class="dash-banner">
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
                    <div class="dash-card-title">Pemasukan per Bulan</div>
                    <div class="dash-card-sub">Total kas terkumpul tahun <?= date('Y') ?></div>
                </div>
                <div class="dash-trends">
                    <span class="dash-trend <?= $trend_up ? 'up' : 'down' ?>"><?= $trend_up ? '&uarr;' : '&darr;' ?> <?= $trend_txt ?>%</span>
                    <span class="dash-year-label">Tahun <?= date('Y') ?></span>
                </div>
            </div>

            <?php if (!$chart_any): ?>
                <div class="dash-chart-svg">
                    <div class="dash-empty-note">Belum ada pembayaran tercatat</div>
                </div>
            <?php else: ?>
                <div class="dash-chart-svg">
                    <svg viewBox="0 0 <?= $CW ?> <?= $CH ?>" role="img" aria-label="Grafik pemasukan per bulan">
                        <defs>
                            <linearGradient id="dashAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#00A37A" stop-opacity="0.22"/>
                                <stop offset="100%" stop-color="#00A37A" stop-opacity="0.02"/>
                            </linearGradient>
                        </defs>
                        <?php for ($i = 0; $i <= 4; $i++): ?>
                            <?php $gy = $CPT + ($CH - $CPT - $CPB) * ($i / 4); ?>
                            <line class="dash-grid-line" x1="<?= $CPL ?>" y1="<?= round($gy, 1) ?>" x2="<?= $CW - $CPR ?>" y2="<?= round($gy, 1) ?>"/>
                            <text class="dash-y-label" x="<?= $CPL - 8 ?>" y="<?= round($gy, 1) + 3 ?>" text-anchor="end"><?= shortnum($max_chart * (1 - $i / 4)) ?></text>
                        <?php endfor; ?>
                        <path class="dash-line-area" d="<?= $area_path ?>"/>
                        <path class="dash-line" d="<?= $smooth_path ?>"/>
                        <?php foreach ($pts as $idx => $pt): ?>
                            <circle class="dash-dot" cx="<?= $pt[0] ?>" cy="<?= $pt[1] ?>" r="3.5">
                                <title><?= $bulan[$idx] ?>: Rp <?= number_format($chart[$idx + 1], 0, ',', '.') ?></title>
                            </circle>
                        <?php endforeach; ?>
                        <?php foreach ($pts as $idx => $pt): ?>
                            <text class="dash-x-label" x="<?= $pt[0] ?>" y="<?= $CH - 10 ?>" text-anchor="middle"><?= $bulan[$idx] ?></text>
                        <?php endforeach; ?>
                    </svg>
                </div>
            <?php endif; ?>
        </div>

        <div class="dash-stack">
            <div class="dash-card">
                <div class="dash-card-head">
                    <div>
                        <div class="dash-card-title">Status Pembayaran</div>
                        <div class="dash-card-sub"><?= $ada_target && $kas_per_siswa !== null ? 'Target kelas = ' . rupiah($kas_per_siswa) . ' per siswa' : 'Perbandingan nominal kas' ?></div>
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
                            <span class="name">Terkumpul<span class="sub"><?= $lunas_count ?> bulan</span></span>
                            <span class="val"><?= rupiah($pemasukan) ?></span>
                        </div>
                        <div class="dash-legend-item">
                            <span class="sw" style="background:var(--yellow);"></span>
                            <span class="name">Belum Terkumpul<span class="sub"><?= $ada_target ? 'sisa target' : $belum_count . ' bulan' ?></span></span>
                            <span class="val"><?= rupiah($belum) ?></span>
                        </div>
                    </div>
                </div>

                <div class="dash-progress">
                    <div class="top">
                        <span class="lbl">Terkumpul</span>
                        <span class="val"><?= $pct_lunas ?>%</span>
                    </div>
                    <div class="track">
                        <div class="fill" style="width:<?= $pct_lunas ?>%;"></div>
                    </div>
                </div>
            </div>

            <div class="dash-promo">
                <h4>Butuh laporan lengkap?</h4>
                <p>Rekap pembayaran per siswa dan saldo kas siap dicetak dalam satu klik.</p>
                <a href="laporan.php" class="dash-promo-btn">
                    Lihat Laporan <?= ic('<path d="M9 6l6 6-6 6"/>', 14) ?>
                </a>
            </div>
        </div>
    </div>

    <div class="dash-charts">
        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Pembayaran Terakhir</div>
                    <div class="dash-card-sub"><?= $lunas_count + $belum_count ?> total catatan pembayaran</div>
                </div>
                <a href="pembayaran.php" class="dash-btn dash-btn-light">
                    Lihat semua
                </a>
            </div>

            <?php if (empty($recent)): ?>
                <div class="dash-empty">
                    <div class="t">Belum ada pembayaran</div>
                    <div class="s">Catat pembayaran siswa melalui menu Pembayaran</div>
                </div>
            <?php else: ?>
                <div class="dash-table-wrap">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Siswa</th>
                                <th>Bulan</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th style="text-align:right;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $p): ?>
                                <tr>
                                    <td>
                                        <div class="dash-cell-name">
                                            <div class="dash-cell-avatar" style="background:var(--accent-soft);color:var(--accent);">
                                                <?= strtoupper(substr($p['nama'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <span class="nm"><?= htmlspecialchars($p['nama']) ?></span>
                                                <span class="ab">Absen <?= (int)$p['nomor_absen'] ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars(Koneksi::periodeLabel($p['periode'])) ?></td>
                                    <td><?= $p['tanggal_bayar'] ? date('d/m/Y', strtotime($p['tanggal_bayar'])) : '-' ?></td>
                                    <td><?= status_pill($p['status']) ?></td>
                                    <td style="text-align:right;"><span class="dash-amount"><?= rupiah((float)$p['jumlah']) ?></span></td>
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
                    <div class="dash-card-title">Ringkasan Saldo</div>
                    <div class="dash-card-sub">Rekap keuangan kas</div>
                </div>
            </div>

            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-secondary);font-weight:700;margin-bottom:6px;">
                Saldo Kas
            </div>
            <div style="font-size:28px;font-weight:800;letter-spacing:-.5px;margin-bottom:18px;color:var(--accent);">
                <?= rupiah($saldo) ?>
            </div>

            <div class="dash-progress" style="margin-top:0;">
                <div class="top">
                    <span class="lbl">Terkumpul</span>
                    <span class="val"><?= $pct_lunas ?>%</span>
                </div>
                <div class="track">
                    <div class="fill" style="width:<?= $pct_lunas ?>%;"></div>
                </div>
            </div>

            <div style="margin-top:18px;border-top:1px solid var(--border);padding-top:14px;display:flex;flex-direction:column;gap:12px;font-size:13px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:var(--text-secondary);">Total Pemasukan</span>
                    <span style="font-weight:700;color:var(--success);"><?= rupiah($pemasukan) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:var(--text-secondary);">Kesepakatan Kas Kelas</span>
                    <span style="font-weight:700;"><?= $ada_target ? rupiah($total_target) : 'Belum ditetapkan' ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:var(--text-secondary);">Kas per Siswa</span>
                    <span style="font-weight:700;"><?= $kas_per_siswa !== null ? rupiah($kas_per_siswa) . ' x ' . $total_siswa . ' siswa' : 'Belum ditetapkan' ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:var(--text-secondary);">Total Pengeluaran</span>
                    <span style="font-weight:700;color:var(--danger);"><?= rupiah($pengeluaran_total) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:var(--text-secondary);">Rata-rata per Siswa</span>
                    <span style="font-weight:700;"><?= rupiah($rata_rata) ?></span>
                </div>
            </div>

            <a href="pengeluaran.php" class="dash-btn dash-btn-primary" style="margin-top:20px;width:100%;justify-content:center;">
                Kelola Pengeluaran
            </a>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
