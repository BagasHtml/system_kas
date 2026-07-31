<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$title = 'Dashboard - Admin';
$active = 'dashboard';
include '../partials/header.php';
include '../partials/admin_sidebar.php';
include '../partials/helpers.php';

$db = new Koneksi();

$total_siswa = (int)($db::q("SELECT COUNT(*) c FROM siswa")->fetch_assoc()['c'] ?? 0);

$agg = $db::q(
    "SELECT
        COUNT(CASE WHEN status = 'lunas' THEN 1 END) AS lunas_count,
        COUNT(CASE WHEN status = 'belum' THEN 1 END) AS belum_count,
        COALESCE(SUM(CASE WHEN status = 'lunas' THEN jumlah END), 0) AS pemasukan,
        COALESCE(SUM(CASE WHEN status = 'belum' THEN jumlah END), 0) AS belum
     FROM pembayaran"
)->fetch_assoc();

$pemasukan = (float)$agg['pemasukan'];
$belum     = (float)$agg['belum'];
$lunas_count = (int)$agg['lunas_count'];
$belum_count = (int)$agg['belum_count'];

$keluar = $db::q("SELECT COALESCE(SUM(jumlah), 0) t, COUNT(*) c FROM pengeluaran")->fetch_assoc();
$pengeluaran_total = (float)$keluar['t'];
$pengeluaran_count = (int)$keluar['c'];

$saldo = $pemasukan - $pengeluaran_total;
$total_tagihan = $pemasukan + $belum;
$pct_lunas = $total_tagihan > 0 ? round($pemasukan / $total_tagihan * 100) : 0;
$rata_rata = $total_siswa > 0 ? round($pemasukan / $total_siswa) : 0;

$recent = $db::q(
    "SELECT p.periode, p.jumlah, p.status, p.tanggal_bayar, s.nama, s.nomor_absen
     FROM pembayaran p
     JOIN siswa s ON s.id = p.siswa_id
     ORDER BY COALESCE(p.tanggal_bayar, p.created_at) DESC, p.id DESC
     LIMIT 6"
)->fetch_all(MYSQLI_ASSOC);

$chart = array_fill(1, 12, 0.0);
foreach ($db::q(
    "SELECT MONTH(tanggal_bayar) m, COALESCE(SUM(jumlah), 0) t
     FROM pembayaran
     WHERE status = 'lunas' AND tanggal_bayar IS NOT NULL AND YEAR(tanggal_bayar) = YEAR(CURDATE())
     GROUP BY MONTH(tanggal_bayar)"
) as $r) {
    $chart[(int)$r['m']] = (float)$r['t'];
}
$max_chart = max(1, max($chart));
$chart_any = array_sum($chart) > 0;

$bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$username = htmlspecialchars($_SESSION['username'] ?? 'Admin');

$kpis = [
    ['label' => 'Total Pemasukan', 'value' => rupiah($pemasukan), 'note' => $lunas_count . ' catatan lunas', 'tone' => 'success'],
    ['label' => 'Total Siswa', 'value' => (string)$total_siswa, 'tone' => 'info'],
    ['label' => 'Total Pengeluaran', 'value' => rupiah($pengeluaran_total), 'note' => $pengeluaran_count . ' transaksi', 'tone' => 'warn'],
    ['label' => 'Saldo Kas', 'value' => rupiah($saldo), 'tone' => 'accent'],
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
                <?= date('d M Y') ?>
            </div>
            <div class="dash-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
        </div>
    </div>

    <div class="dash-kpis">
        <?php foreach ($kpis as $k): ?>
            <?= kpi($k) ?>
        <?php endforeach; ?>
    </div>

    <div class="dash-charts">
        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Pemasukan per Bulan</div>
                    <div class="dash-card-sub">Total pembayaran lunas tahun <?= date('Y') ?></div>
                </div>
                <span class="dash-year-label">Tahun <?= date('Y') ?></span>
            </div>

            <div class="dash-chart-wrap">
                <div class="dash-y-axis">
                    <?php for ($i = 4; $i >= 0; $i--): ?>
                        <span><?= shortnum($max_chart * $i / 4) ?></span>
                    <?php endfor; ?>
                </div>
                <div class="dash-chart-body">
                    <div class="dash-bars">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <?php $h = $chart[$i] > 0 ? max(4, round($chart[$i] / $max_chart * 100)) : 4; ?>
                            <div class="dash-bar <?= $chart[$i] == 0 ? 'empty' : '' ?>" style="height:<?= $h ?>%;">
                                <div class="tip">Rp <?= number_format($chart[$i], 0, ',', '.') ?></div>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <?php if (!$chart_any): ?>
                        <div class="dash-empty-note">Belum ada pembayaran tercatat</div>
                    <?php endif; ?>
                    <div class="dash-x-labels">
                        <?php foreach ($bulan as $b): ?>
                            <span><?= $b ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Status Pembayaran</div>
                    <div class="dash-card-sub">Perbandingan nominal kas</div>
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
                        <span class="name">Lunas<span class="sub"><?= $lunas_count ?> periode</span></span>
                        <span class="val"><?= rupiah($pemasukan) ?></span>
                    </div>
                    <div class="dash-legend-item">
                        <span class="sw" style="background:#d9a241;"></span>
                        <span class="name">Belum<span class="sub"><?= $belum_count ?> periode</span></span>
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
                                <th>Periode</th>
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
                                    <td><?= htmlspecialchars($p['periode']) ?></td>
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

            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);font-weight:600;margin-bottom:6px;">
                Saldo Kas
            </div>
            <div style="font-size:28px;font-weight:800;letter-spacing:-.5px;margin-bottom:18px;">
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
