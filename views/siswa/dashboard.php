<?php
require_once __DIR__ . '/../../app/controllers/SiswaDashboardController.php';
extract(SiswaDashboardController::handle(), EXTR_SKIP);

$title = 'Dashboard - Siswa';

include '../partials/header.php';
include '../partials/helpers.php';

$tunggakan_bulan = get_tunggakan_siswa($siswa_id);

$banner = [
    ['icon' => 'bi bi-wallet2', 'label' => 'Total Kontribusi Kamu', 'value' => rupiah($lunas), 'note' => $periode_lunas . ' bulan kontribusi terkumpul'],
    ['icon' => 'bi bi-hourglass-split', 'label' => 'Sisa Kontribusi', 'value' => rupiah($belum), 'note' => $belum > 0 ? 'masih ada target uang kas yang belum terpenuhi' : 'tidak ada target uang kas hari ini'],
    ['icon' => 'bi bi-calendar-check', 'label' => 'Bulan Lengkap', 'value' => (string)$periode_lunas, 'note' => 'dari ' . count($pembayaran) . ' bulan tercatat'],
];
?>

<div class="main-content student-page">
    <?php $active = 'dashboard'; include '../partials/siswa_sidebar.php'; ?>
    <div class="dash-topbar">
        <div>
            <p class="student-eyebrow">Dashboard Siswa</p>
            <h1 class="dash-title" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                Hai, <?= $siswa_nama ?>
                <?php if ($tunggakan_bulan > 0): ?>
                <?php else: ?>
                <?php endif; ?>
            </h1>
            <p class="dash-subtitle">Nomor absen <?= $siswa_absen ?> &middot; yuk pantau status kas kamu di sini</p>
        </div>
        <div class="dash-topbar-actions">
            <span class="dash-datechip">
                <?= ic('<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>', 14) ?>
                <?= date('d M Y') ?>
            </span>
            <div class="dash-user">
                <span class="dash-username"><?= htmlspecialchars($siswa_nama) ?></span>
                <div class="dash-avatar"><?= strtoupper(mb_substr(trim($siswa_nama), 0, 1)) ?></div>
            </div>
        </div>
    </div>

    <?php Koneksi::renderFlash(); ?>

    <div class="dash-banner cols-3">
        <?php foreach ($banner as $b): ?>
            <div class="dash-banner-col">
                <div class="dash-banner-icon"><i class="<?= $b['icon'] ?>"></i></div>
                <div>
                    <span class="dash-banner-label"><?= $b['label'] ?></span>
                    <span class="dash-banner-value"><?= $b['value'] ?></span>
                    <span class="dash-banner-note"><?= $b['note'] ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="dash-charts">
        <div class="dash-card" id="bayar">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Kirim Kontribusi Kas</div>
                    <div class="dash-card-sub">Salurkan uang kas kelas lewat Send Dana atau scan QRIS</div>
                </div>
                <div class="dash-year-label" style="display:flex;flex-direction:column;gap:2px;align-items:flex-end;">
                    <?= htmlspecialchars(Koneksi::periodeLabel(date('Y-m'))) ?>
                    <?php if ($ada_target && !empty($target_map)): ?>
                        <small style="font-size:11px;color:var(--accent);font-weight:700;"><?= rupiah((float)end($target_map)['per_siswa']) ?>/siswa</small>
                    <?php endif; ?>
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
                        <div class="dash-pay-row">
                            <span class="lbl">Bulan Kontribusi</span>
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

            <div class="dash-pay-note">
                <i class="bi bi-info-circle"></i>
                Setelah transfer, konfirmasi &amp; status pembayaran kamu diproses dan diverifikasi oleh bendahara.
            </div>
        </div>

        <div class="dash-right-col">
        <div class="dash-card dash-target-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Target Kas Kelas</div>
                    <div class="dash-card-sub">Jumlah kas yang disepakati kelas untuk dikumpulkan bersama</div>
                </div>
                <?php if (!$ada_target): ?>
                    <span class="dash-status-pill muted"><i class="bi bi-dash-circle"></i> Belum Ditentukan</span>
                <?php elseif ($kelas_remainder <= 0): ?>
                    <span class="dash-status-pill success"><i class="bi bi-check-circle-fill"></i> Target Tercapai</span>
                <?php else: ?>
                    <span class="dash-status-pill warn"><i class="bi bi-hourglass-split"></i> <?= $pct_kelas ?>% Terkumpul</span>
                <?php endif; ?>
            </div>

            <?php if (!$ada_target): ?>
                <div class="dash-empty">
                    <div class="t">Target belum ditetapkan</div>
                    <div class="s">Bendahara belum menetapkan target kas untuk kelas</div>
                </div>
            <?php else: ?>
                <div class="dash-progress dash-progress-lg">
                    <div class="top">
                        <span class="lbl">Terkumpul dari target</span>
                        <span class="val"><?= $pct_kelas ?>%</span>
                    </div>
                    <div class="track">
                        <div class="fill" style="width:<?= $pct_kelas ?>%;"></div>
                    </div>
                </div>

                <div class="dash-target-stats">
                    <div class="tstat">
                        <span class="lbl">Target Kelas</span>
                        <span class="val"><?= rupiah($total_target) ?></span>
                    </div>
                    <div class="tstat">
                        <span class="lbl">Terkumpul</span>
                        <span class="val acc"><?= rupiah($kelas_collected) ?></span>
                    </div>
                    <div class="tstat">
                        <span class="lbl">Siswa Berpartisipasi</span>
                        <span class="val"><?= $siswa_kontribusi ?><em> / <?= $jumlah_siswa ?></em></span>
                    </div>
                    <div class="tstat">
                        <span class="lbl">Sisa Target</span>
                        <span class="val <?= $kelas_remainder <= 0 ? 'up' : 'warn' ?>"><?= $kelas_remainder <= 0 ? 'Tercapai' : rupiah($kelas_remainder) ?></span>
                    </div>
                </div>

                <?php if ($pending_saya > 0): ?>
                    <div style="margin-top:14px;padding:10px 14px;border-radius:10px;background:#fffcf5;border:1px solid #fef3c7;font-size:12px;color:#92400e;display:flex;align-items:center;gap:8px;">
                        <i class="bi bi-clock-history"></i> <?= $pending_saya ?> konfirmasi kas kamu menunggu verifikasi bendahara dan belum dihitung ke target.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="dash-card dash-target-chart">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Progres Target per Bulan</div>
                    <div class="dash-card-sub">Realisasi pemasukan kas kelas tiap periode</div>
                </div>
            </div>

            <?php if (empty($target_progress)): ?>
                <div class="dash-empty" style="flex:1;">
                    <div class="t">Belum ada data target</div>
                    <div class="s">Bendahara belum menetapkan target kas</div>
                </div>
            <?php else: ?>
                <div class="dash-bars-box">
                    <?php foreach ($target_progress as $tp): ?>
                        <?php $tp_pct = $tp['pct'] ?? 0; ?>
                        <div class="dash-box-col">
                            <span class="dash-box-pct"><?= $tp_pct ?>%</span>
                            <div class="dash-box-bar <?= $tp_pct > 0 ? '' : 'empty' ?>" style="height:<?= max(6, min(150, round(($tp['target'] > 0 ? $tp['collected'] / $tp['target'] : 0) * 150))) ?>px;">
                                <span class="tip"><?= rupiah($tp['collected']) ?> / <?= rupiah($tp['target']) ?></span>
                            </div>
                            <span class="dash-box-label"><?= htmlspecialchars($tp['label']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </div>

    <div class="dash-charts">
        <div class="dash-card" id="riwayat">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Riwayat Kontribusi Saya</div>
                    <div class="dash-card-sub">Status kontribusi kas kamu per bulan</div>
                </div>
                <a href="pengeluaran.php" class="dash-btn dash-btn-light">
                    Lihat Pengeluaran
                </a>
            </div>

            <?php if (empty($pembayaran)): ?>
                    <div class="dash-empty">
                        <div class="t">Belum ada riwayat kontribusi</div>
                        <div class="s">Belum ada kontribusi tercatat. Yuk kirim kontribusi kas kamu!</div>
                    </div>
            <?php else: ?>
                <div class="dash-table-wrap">
                    <table class="dash-table">
                        <thead>
                                <tr>
                                    <th style="width:50px;">No</th>
                                    <th>Bulan</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Metode</th>
                                    <th style="text-align:center;">Bukti</th>
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
                                    <td><?= metode_pill($p['metode'] ?? null) ?></td>
                                    <td style="text-align:center;">
                                        <?php if (!empty($p['bukti_transfer'])): ?>
                                            <button class="dash-btn dash-btn-light" style="padding:4px 8px;font-size:12px;"
                                                    data-bs-toggle="modal" data-bs-target="#modalBukti"
                                                    data-img="<?= BASE_URL . '/' . htmlspecialchars($p['bukti_transfer']) ?>"
                                                    data-title="Bukti Transfer <?= htmlspecialchars(Koneksi::periodeLabel($p['periode'])) ?>"
                                                    data-catatan="<?= htmlspecialchars($p['catatan'] ?? '') ?>">
                                                <i class="bi bi-image"></i> Lihat
                                            </button>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted);font-size:12px;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="modal fade" id="modalBukti" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="modalBuktiTitle">Bukti Transfer</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="modalBuktiImg" src="" alt="Bukti Transfer" style="max-width:100%;max-height:400px;border-radius:8px;object-fit:contain;">
                        <p id="modalBuktiCatatan" style="margin-top:12px;font-size:13px;color:var(--text-secondary);"></p>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.getElementById('modalBukti')?.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('modalBuktiTitle').textContent = btn?.dataset.title || 'Bukti Transfer';
            document.getElementById('modalBuktiImg').src = btn?.dataset.img || '';
            const cat = btn?.dataset.catatan;
            document.getElementById('modalBuktiCatatan').textContent = cat ? 'Catatan: ' + cat : '';
        });
        </script>

        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Ringkasan Pengeluaran Kelas</div>
                    <div class="dash-card-sub">Total pengeluaran &middot; <?= $pengeluaran_count ?> transaksi</div>
                </div>
            </div>

            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-secondary);font-weight:700;margin-bottom:6px;">
                Total Pengeluaran
            </div>
            <div style="font-size:28px;font-weight:800;letter-spacing:-.5px;margin-bottom:18px;color:var(--danger);">
                <?= rupiah($pengeluaran_total) ?>
            </div>

            <?php if (empty($pengeluaran_terakhir)): ?>
                <div class="dash-empty">
                    <div class="t">Belum ada pengeluaran</div>
                    <div class="s">Pengurus kelas belum mencatat pengeluaran kas</div>
                </div>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;">
                    <?php foreach ($pengeluaran_terakhir as $e): ?>
                        <div style="display:flex;justify-content:space-between;gap:12px;padding:11px 0;border-top:1px solid var(--border);">
                            <div style="min-width:0;">
                                <div style="font-weight:600;font-size:13px;line-height:1.35;"><?= htmlspecialchars($e['keterangan']) ?></div>
                                <div style="font-size:11px;color:var(--text-muted);margin-top:2px;"><?= date('d M Y', strtotime($e['tanggal'])) ?></div>
                            </div>
                            <div style="font-weight:700;font-size:13px;color:var(--danger);white-space:nowrap;">- <?= rupiah((float)$e['jumlah']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <a href="pengeluaran.php" class="dash-btn dash-btn-primary" style="margin-top:18px;width:100%;justify-content:center;">
                Cek Rincian Pengeluaran Lengkap <?= ic('<path d="M9 6l6 6-6 6"/>', 14) ?>
            </a>
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
