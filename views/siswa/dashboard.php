<?php
require_once __DIR__ . '/../../app/controllers/SiswaDashboardController.php';
extract(SiswaDashboardController::handle(), EXTR_SKIP);

$title = 'Dashboard - Siswa';

include '../partials/header.php';
include '../partials/helpers.php';

$dana_no  = Koneksi::pengaturan('nomor_dana', '0813-2175-0459');
$dana_nm  = Koneksi::pengaturan('atas_nama_dana', '-');
$qris_img = Koneksi::pengaturan('qris_path', 'assets/img/qris.png');
$nama_kelas = Koneksi::pengaturan('nama_kelas', 'Kelas');
$periode_now = date('Y-m');
$per_siswa_now = 0;
if (!empty($target_map)) {
    $latest = end($target_map);
    $per_siswa_now = (float)($latest['per_siswa'] ?? 0);
}

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
            <h1 class="dash-title">
                Hai, <?= $siswa_nama ?>
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
<<<<<<< HEAD
                            <span class="val num"><?= htmlspecialchars($dana_no) ?></span>
                        </div>
                        <div class="dash-pay-row">
                            <span class="lbl">Atas Nama</span>
                            <span class="val"><?= htmlspecialchars($dana_nm) ?></span>
=======
                            <span class="val num"><?= htmlspecialchars($setting['dana_nomor']) ?></span>
                        </div>
                        <div class="dash-pay-row">
                            <span class="lbl">Atas Nama</span>
                            <span class="val"><?= htmlspecialchars($setting['dana_nama']) ?></span>
>>>>>>> d67dedf (update layout and added new system for manage admin dashboard and added fix more bugs and update layout and added readme)
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
<<<<<<< HEAD
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($qris_img) ?>" alt="QRIS Kas Kelas">
=======
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($setting['qris_path']) ?>" alt="QRIS Kas Kelas">
>>>>>>> d67dedf (update layout and added new system for manage admin dashboard and added fix more bugs and update layout and added readme)
                    </div>
                    <div class="dash-pay-hint">Scan kode di atas setelah transfer</div>
                </div>
            </div>

            <div class="dash-pay-note">
                <i class="bi bi-info-circle"></i>
                Setelah transfer, konfirmasi &amp; upload bukti transfer di bawah ini.
            </div>
        </div>

<!-- Form Upload Bukti Transfer -->
        <div class="dash-card dash-upload" id="upload-bukti">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Kontribusi & Upload Bukti</div>
                    <div class="dash-card-sub">Upload bukti transfer untuk periode <?= htmlspecialchars(Koneksi::periodeLabel($periode_now)) ?></div>
                </div>
            </div>

            <?php
            $jml_setoran  = count($setoran_ini);
            $setoran_lunas = $setoran_pending = $setoran_belum = 0;
            $setoran_total = 0.0;
            foreach ($setoran_ini as $_s) {
                $setoran_total += (float)$_s['jumlah'];
                if ($_s['status'] === 'lunas')      $setoran_lunas++;
                elseif ($_s['status'] === 'pending') $setoran_pending++;
                else                                 $setoran_belum++;
            }
            if ($setoran_pending > 0) {
                $ringkas = ['txt' => 'Menunggu Verifikasi', 'cls' => 'warn', 'icon' => 'bi-clock-history'];
            } elseif ($setoran_lunas > 0 && $setoran_belum === 0) {
                $ringkas = ['txt' => 'Semua Sudah Disetorkan', 'cls' => 'success', 'icon' => 'bi-check-circle-fill'];
            } elseif ($setoran_lunas > 0) {
                $ringkas = ['txt' => 'Sebagian Sudah Disetorkan', 'cls' => 'warn', 'icon' => 'bi-hourglass-split'];
            } else {
                $ringkas = ['txt' => 'Belum Disetorkan', 'cls' => 'muted', 'icon' => 'bi-dash-circle'];
            }
            ?>

            <div class="dash-setoran">
                <div class="dash-setoran-top">
                    <div class="dash-setoran-head">
                        <span class="dash-setoran-title"><i class="bi bi-clipboard-check"></i> Status Kiriman Bulan Ini</span>
                        <?php if ($jml_setoran > 0): ?>
                            <span class="dash-status-pill <?= $ringkas['cls'] ?>"><i class="bi <?= $ringkas['icon'] ?>"></i> <?= $ringkas['txt'] ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($jml_setoran > 0): ?>
                        <div class="dash-setoran-sub">
                            <span><?= $jml_setoran . ($jml_setoran === 1 ? ' kiriman tercatat' : ' kiriman tercatat') ?></span>
                            <span class="dot">&middot;</span>
                            <span>Total <b class="tot"><?= rupiah($setoran_total) ?></b></span>
                        </div>
                    <?php else: ?>
                        <div class="dash-setoran-sub">Pantau kiriman bukti kas kamu bulan ini</div>
                    <?php endif; ?>
                </div>

                <?php if ($setoran_ini): ?>
                    <div class="dash-setoran-list">
                        <?php foreach ($setoran_ini as $s): ?>
                            <?php
                            $metode_label = ['langsung' => 'Tunai', 'dana' => 'Send DANA', 'qris' => 'QRIS'][$s['metode'] ?? 'langsung'] ?? 'Tunai';
                            $tgl_kirim = $s['tanggal_bayar'] ? date('d M Y', strtotime($s['tanggal_bayar'])) : (isset($s['created_at']) ? date('d M Y', strtotime($s['created_at'])) : '-');
                            ?>
                            <div class="dash-setoran-item">
                                <div class="dash-setoran-ic <?= $s['status'] === 'lunas' ? 'ok' : ($s['status'] === 'pending' ? 'wait' : 'no') ?>">
                                    <i class="bi <?= $s['status'] === 'lunas' ? 'bi-check-lg' : ($s['status'] === 'pending' ? 'bi-clock-history' : 'bi-dash-lg') ?>"></i>
                                </div>
                                <div class="dash-setoran-main">
                                    <span class="dash-setoran-amt"><?= rupiah((float)$s['jumlah']) ?></span>
                                    <span class="dash-setoran-meta"><?= $tgl_kirim ?> &middot; <?= htmlspecialchars($metode_label) ?></span>
                                </div>
                                <div class="dash-setoran-act">
                                    <?= status_pill($s['status']) ?>
                                    <?php if (!empty($s['bukti_transfer'])): ?>
                                        <button class="dash-btn dash-btn-light dash-setoran-bukti"
                                                data-bs-toggle="modal" data-bs-target="#modalBukti"
                                                data-img="<?= BASE_URL . '/' . htmlspecialchars($s['bukti_transfer']) ?>"
                                                data-title="Bukti Transfer <?= htmlspecialchars(Koneksi::periodeLabel($s['periode'])) ?>"
                                                data-catatan="<?= htmlspecialchars($s['catatan'] ?? '') ?>">
                                            <i class="bi bi-image"></i> Lihat
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($setoran_pending > 0): ?>
                        <div class="dash-setoran-note wait">
                            <i class="bi bi-clock-history"></i>
                            <span><strong><?= $setoran_pending ?></strong> kiriman masih menunggu verifikasi bendahara. Statusnya otomatis jadi <strong>Lunas</strong> setelah diverifikasi.</span>
                        </div>
                    <?php elseif ($setoran_lunas > 0 && $setoran_belum > 0): ?>
                        <div class="dash-setoran-note warn">
                            <i class="bi bi-hourglass-split"></i>
                            <span>Sebagian kirimanmu belum disetorkan — begitu ditransfer, upload buktinya di bawah biar statusnya ter-update.</span>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="dash-setoran-empty">
                        <i class="bi bi-inbox"></i>
                        <div>
                            <span class="t">Belum ada kiriman bulan ini</span>
                            <span class="s">Setelah transfer, upload bukti di bawah supaya setoran tercatat &amp; statusnya bisa kamu pantau.</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <form class="dash-payform" id="formSetoran" action="<?= BASE_URL ?>/function/setor_bayar.php" method="POST" enctype="multipart/form-data" novalidate>
                <?= Koneksi::csrfField() ?>
                <input type="hidden" name="periode" value="<?= $periode_now ?>">

                <div class="dash-payform-head">
                    <span class="ic"><i class="bi bi-send-plus"></i></span>
                    <span>Kirim Setoran Baru</span>
                </div>

                <div class="dash-payform-grid">
                    <div class="pay-field">
                        <label class="pay-label" for="jumlah">Jumlah Setoran</label>
                        <div class="pay-amount">
                            <span class="pay-currency">Rp</span>
                            <input type="number" id="jumlah" name="jumlah" min="1" step="1000"
                                   placeholder="0" inputmode="numeric">
                        </div>
                        <?php if ($per_siswa_now > 0): ?>
                            <div class="pay-chips">
                                <span class="pay-chip-tag">Cepat pilih</span>
                                <?php for ($i = 1; $i <= 3; $i++): ?>
                                    <button type="button" class="pay-chip" data-amount="<?= (int)round($per_siswa_now * $i) ?>">
                                        <?= rupiah($per_siswa_now * $i) ?>
                                    </button>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="pay-field">
                        <label class="pay-label">Metode Pembayaran</label>
                        <div class="pay-methods" id="payMethods">
                            <label class="pay-method active" data-m="dana">
                                <input type="radio" name="metode" value="dana" checked hidden>
                                <i class="bi bi-send"></i><span>DANA</span>
                            </label>
                            <label class="pay-method" data-m="qris">
                                <input type="radio" name="metode" value="qris" hidden>
                                <i class="bi bi-qr-code"></i><span>QRIS</span>
                            </label>
                            <label class="pay-method" data-m="langsung">
                                <input type="radio" name="metode" value="langsung" hidden>
                                <i class="bi bi-cash-coin"></i><span>Tunai</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pay-field">
                    <label class="pay-label">Bukti Transfer</label>
                    <label class="pay-drop" id="payDrop" for="bukti_transfer">
                        <span class="pay-drop-ic"><i class="bi bi-cloud-arrow-up"></i></span>
                        <span class="t">Upload foto bukti transfer</span>
                        <span class="s" id="payDropSub">Klik untuk pilih &middot; tarik &amp; lepas file</span>
                    </label>
                    <input type="file" class="pay-file-input" id="bukti_transfer" name="bukti_transfer" accept="image/">
                    <div class="pay-file" id="payFile" hidden>
                        <img id="payFileImg" alt="Preview bukti">
                        <div class="pay-file-meta">
                            <span class="n" id="payFileName">-</span>
                            <span class="s" id="payFileSize"></span>
                        </div>
                        <button type="button" class="pay-file-clear" id="payFileClear" title="Hapus file"><i class="bi bi-x"></i></button>
                    </div>
                    <div class="pay-feedback" id="payFeedback" hidden>
                        <i class="bi bi-exclamation-circle"></i><span id="payFeedbackText"></span>
                    </div>
                </div>

                <div class="pay-field">
                    <label class="pay-label" for="catatan">Catatan <em>(opsional)</em></label>
                    <div class="pay-note-input">
                        <i class="bi bi-chat-left-text"></i>
                        <input type="text" id="catatan" name="catatan" placeholder="Contoh: Transfer via BCA, scan QRIS">
                    </div>
                </div>

                <button type="submit" name="kontribusi_submit" class="pay-submit">
                    <span id="paySubmitTxt">Kirim Kontribusi &amp; Upload Bukti</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
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
                                    <th>Catatan</th>
                                    <th style="text-align:center;">Bukti</th>
                                    <th style="text-align:center;">Struk</th>
                                </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($pembayaran as $p): ?>
                                <tr>
                                    <td style="color:var(--text-muted);"><?= $no++ ?></td>
                                    <td><span style="font-weight:600;"><?= htmlspecialchars(Koneksi::periodeLabel($p['periode'])) ?></span></td>
                                    <td><span class="dash-amount"><?= rupiah((float)$p['jumlah']) ?></span></td>
                                    <td><?= $p['tanggal_bayar'] ? date('d/m/Y', strtotime($p['tanggal_bayar'])) : '<span style="font-size:11px;color:var(--warning);">Menunggu verifikasi</span>' ?></td>
                                    <td><?= status_pill($p['status']) ?></td>
                                    <td><?= metode_pill($p['metode'] ?? null) ?></td>
                                    <td style="font-size:12px;color:var(--text-secondary);"><?= $p['catatan'] ? htmlspecialchars($p['catatan']) : '-' ?></td>
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
                                    <td style="text-align:center;">
                                        <?php if ($p['status'] === 'lunas'): ?>
                                            <a class="dash-btn dash-btn-light" style="padding:4px 8px;font-size:12px;"
                                               href="struk.php?id=<?= (int)$p['id'] ?>" title="Lihat / unduh struk pembayaran">
                                                <i class="bi bi-receipt"></i> Struk
                                            </a>
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

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form      = document.getElementById('formSetoran');
            const methods   = document.getElementById('payMethods');
            const jumlahInp = document.getElementById('jumlah');
            const fileInp   = document.getElementById('bukti_transfer');
            const drop      = document.getElementById('payDrop');
            const payFile   = document.getElementById('payFile');
            const fileImg   = document.getElementById('payFileImg');
            const fileName  = document.getElementById('payFileName');
            const fileSize  = document.getElementById('payFileSize');
            const fileClear = document.getElementById('payFileClear');
            const feedback  = document.getElementById('payFeedback');
            const feedbackTxt = document.getElementById('payFeedbackText');

            if (!form || !methods) return;

            const dropSub     = document.getElementById('payDropSub');
            const submitTxt   = document.getElementById('paySubmitTxt');
            const submitBtn   = form.querySelector('.pay-submit');

            function isTunai() {
                const act = methods.querySelector('.pay-method.active');
                return act ? act.dataset.m === 'langsung' : false;
            }
            function refreshMetodeUI() {
                const tunai = isTunai();
                fileInp.required = !tunai;
                drop.classList.toggle('pay-drop-optional', tunai);
                if (dropSub) {
                    dropSub.textContent = tunai
                        ? 'Opsional untuk setoran tunai langsung ke bendahara'
                        : 'Klik untuk pilih \u00B7 tarik & lepas file';
                }
                if (submitTxt) {
                    submitTxt.textContent = tunai
                        ? 'Konfirmasi Setoran Tunai'
                        : 'Kirim Kontribusi & Upload Bukti';
                }
                if (tunai) hideFile();
            }

            methods.addEventListener('click', function (e) {
                const pill = e.target.closest('.pay-method');
                if (!pill) return;
                methods.querySelectorAll('.pay-method').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                const radio = pill.querySelector('input[name="metode"]');
                if (radio) radio.checked = true;
                refreshMetodeUI();
            });

            document.querySelectorAll('.pay-chip').forEach(chip => {
                chip.addEventListener('click', function () {
                    jumlahInp.value = this.dataset.amount;
                    jumlahInp.focus();
                });
            });

            refreshMetodeUI();

            function resetDropDrag() { drop.classList.remove('dragover'); }
            function hideFile() {
                payFile.hidden = true;
                drop.hidden = false;
                fileInp.value = '';
                fileImg.removeAttribute('src');
                resetDropDrag();
            }
            function showFeedback(msg) {
                feedbackTxt.textContent = msg;
                feedback.hidden = false;
                feedback.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
            function hideFeedback() { feedback.hidden = true; }
            function fmtBytes(bytes) {
                if (!bytes) return '';
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1048576) return (bytes / 1024).toFixed(0) + ' KB';
                return (bytes / 1048576).toFixed(1) + ' MB';
            }

            function loadPreview() {
                hideFeedback();
                resetDropDrag();
                const file = fileInp.files && fileInp.files[0];
                if (!file) return;
                const isImgExt = /\.(jpe?g|png|webp)$/i.test(file.name);
                const isImgMime = file.type ? file.type.startsWith('image/') : true;
                if (!isImgExt) {
                    showFeedback('Ekstensi file harus .jpg, .png, atau .webp.');
                    hideFile();
                    return;
                }
                if (!isImgMime) {
                    showFeedback('File harus berupa foto/gambar, bukan file lain.');
                    hideFile();
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    showFeedback('Ukuran file maksimal 5MB. Pilih foto yang lebih kecil.');
                    hideFile();
                    return;
                }
                fileName.textContent = file.name;
                fileSize.textContent = fmtBytes(file.size);
                const reader = new FileReader();
                reader.onload = function (ev) {
                    const img = new Image();
                    img.onload = function () {
                        fileImg.src = ev.target.result;
                        payFile.hidden = false;
                        drop.hidden = true;
                    };
                    img.onerror = function () {
                        showFeedback('File "valid" tapi tidak terbaca sebagai foto — gunakan JPG/PNG/WebP asli.');
                        hideFile();
                    };
                    img.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            }

            fileInp.addEventListener('change', loadPreview);
            fileClear.addEventListener('click', hideFile);

            drop.addEventListener('dragover', function (e) { e.preventDefault(); drop.classList.add('dragover'); });
            drop.addEventListener('dragleave', resetDropDrag);
            drop.addEventListener('drop', function (e) {
                e.preventDefault();
                resetDropDrag();
                if (e.dataTransfer.files.length) {
                    fileInp.files = e.dataTransfer.files;
                    loadPreview();
                }
            });

            form.addEventListener('submit', function (e) {
                const jumlah = parseFloat(jumlahInp.value);
                if (!jumlah || jumlah <= 0) {
                    e.preventDefault();
                    showFeedback('Isi dulu jumlah setorannya.');
                    jumlahInp.focus();
                    return;
                }
                if (!isTunai() && (!fileInp.files || !fileInp.files[0])) {
                    e.preventDefault();
                    showFeedback('Pilih dulu foto bukti transfernya.');
                    return;
                }
            });
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

<?php include '../partials/siswa_mobile_nav.php'; ?>

<?php include '../partials/footer.php'; ?>
