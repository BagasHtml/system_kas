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

/* ===== HANDLE UPLOAD BUKTI TRANSFER ===== */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['upload_bukti'])) {
    if (!Koneksi::csrfCheck()) {
        Koneksi::setFlash('error', 'Token tidak valid. Silakan coba lagi.');
        header("Location: dashboard.php#bayar");
        exit;
    }

    $periode = trim($_POST['periode'] ?? '');
    $jumlah  = (float)($_POST['jumlah'] ?? 0);
    $catatan = trim($_POST['catatan'] ?? '');

    if (!preg_match('/^\d{4}-\d{2}$/', $periode) || $jumlah <= 0) {
        Koneksi::setFlash('error', 'Pilih bulan dan jumlah pembayaran yang valid.');
        header("Location: dashboard.php#bayar");
        exit;
    }

    $bukti_path = null;
    if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['bukti_transfer']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = 'bukti_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $targetDir = str_replace('\\', '/', dirname(__DIR__, 2)) . '/assets/uploads/bukti_transfer/';
            if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
            $targetFile = $targetDir . $filename;
            if (move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $targetFile)) {
                $bukti_path = 'assets/uploads/bukti_transfer/' . $filename;
            }
        } else {
            Koneksi::setFlash('error', 'Format gambar harus JPG, PNG, atau WEBP.');
            header("Location: dashboard.php#bayar");
            exit;
        }
    }

    if (!$bukti_path) {
        Koneksi::setFlash('error', 'File bukti transfer wajib diupload.');
        header("Location: dashboard.php#bayar");
        exit;
    }

    $db::q(
        "INSERT INTO pembayaran (siswa_id, periode, jumlah, status, tanggal_bayar, bukti_transfer, catatan)
         VALUES (?, ?, ?, 'pending', NOW(), ?, ?)
         ON DUPLICATE KEY UPDATE jumlah = ?, status = 'pending', tanggal_bayar = NOW(), bukti_transfer = ?, catatan = ?",
        [$siswa_id, $periode, $jumlah, $bukti_path, $catatan, $jumlah, $bukti_path, $catatan]
    );

    Koneksi::setFlash('success', 'Konfirmasi pembayaran berhasil dikirim! Menunggu verifikasi dari bendahara.');
    header("Location: dashboard.php#riwayat");
    exit;
}

$tunggakan_bulan = get_tunggakan_siswa($siswa_id);

$pembayaran = [];
if ($siswa_id > 0) {
    $pembayaran = $db::q(
        "SELECT id, periode, jumlah, status, tanggal_bayar, bukti_transfer, catatan
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
    ['icon' => 'bi bi-wallet2', 'label' => 'Total Sudah Dibayar', 'value' => rupiah($lunas), 'note' => $periode_lunas . ' bulan sudah kamu bayar'],
    ['icon' => 'bi bi-hourglass-split', 'label' => 'Belum Dibayar', 'value' => rupiah($belum), 'note' => $belum > 0 ? 'masih ada target uang kas yang belum terpenuhi' : 'tidak ada target uang kas hari ini'],
    ['icon' => 'bi bi-calendar-check', 'label' => 'Bulan Lunas', 'value' => (string)$periode_lunas, 'note' => 'dari ' . count($pembayaran) . ' bulan tercatat'],
];

/* Widget 2: Ringkasan Pengeluaran Kelas */
$exp_total = $db::q("SELECT COALESCE(SUM(jumlah), 0) t, COUNT(*) c FROM pengeluaran")->fetch_assoc();
$pengeluaran_total = (float)($exp_total['t'] ?? 0);
$pengeluaran_count = (int)($exp_total['c'] ?? 0);
$pengeluaran_terakhir = $db::q(
    "SELECT keterangan, jumlah, tanggal FROM pengeluaran ORDER BY tanggal DESC, id DESC LIMIT 3"
)->fetch_all(MYSQLI_ASSOC);
?>

<div class="main-content student-page">
    <?php $active = 'dashboard'; include '../partials/siswa_sidebar.php'; ?>
    <div class="dash-topbar">
        <div>
            <p class="student-eyebrow">Dashboard Siswa</p>
            <h1 class="dash-title" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                Hai, <?= $siswa_nama ?>
                <?php if ($tunggakan_bulan > 0): ?>
                    <span class="badge bg-danger" style="font-size:12px;font-weight:600;padding:5px 10px;border-radius:20px;">
                        <i class="bi bi-exclamation-triangle-fill"></i> Menunggak <?= $tunggakan_bulan ?> Bulan
                    </span>
                <?php else: ?>
                    <span class="badge bg-success" style="font-size:12px;font-weight:600;padding:5px 10px;border-radius:20px;">
                        <i class="bi bi-check-circle-fill"></i> Iuran Lunas
                    </span>
                <?php endif; ?>
            </h1>
            <p class="dash-subtitle">Nomor absen <?= $siswa_absen ?> &middot; yuk pantau status kas kamu di sini</p>
        </div>
        <div class="dash-topbar-actions">
        </div>
    </div>

    <?php Koneksi::renderFlash(); ?>

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
        <div class="dash-card" id="bayar">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Bayar Kas Online</div>
                    <div class="dash-card-sub">Bayar iuran kas kelas lewat Send Dana atau scan QRIS</div>
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
                            <span class="lbl">Pembayaran Bulan</span>
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

            <div class="dash-pay-confirm" style="margin-top:20px;padding-top:20px;border-top:1px dashed var(--border);">
                <div style="font-weight:700;font-size:15px;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-upload" style="color:var(--accent);"></i> Konfirmasi &amp; Upload Bukti Pembayaran
                </div>
                <p style="font-size:13px;color:var(--text-secondary);margin-bottom:14px;">
                    Sudah transfer via DANA atau QRIS? Upload foto bukti transfer di bawah agar bendahara dapat memverifikasi.
                </p>
                <form action="dashboard.php" method="POST" enctype="multipart/form-data" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <?= Koneksi::csrfField() ?>
                    <input type="hidden" name="upload_bukti" value="1">
                    <div>
                        <label class="form-label" style="font-size:12px;font-weight:600;">Bulan iuran yang dibayar</label>
                        <select name="periode" class="form-select form-select-sm" required style="border-radius:8px;">
                            <?php if (empty($target_map)): ?>
                                <option value="<?= date('Y-m') ?>"><?= Koneksi::periodeLabel(date('Y-m')) ?></option>
                            <?php else: ?>
                                <?php foreach ($target_map as $p => $info): ?>
                                    <option value="<?= $p ?>" <?= $p === date('Y-m') ? 'selected' : '' ?>>
                                        <?= Koneksi::periodeLabel($p) ?> (<?= rupiah($info['target']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-size:12px;font-weight:600;">Jumlah yang ditransfer (Rp)</label>
                        <input type="number" name="jumlah" class="form-control form-control-sm" placeholder="20000" min="1000" required style="border-radius:8px;">
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Upload Foto Bukti Transfer (JPG/PNG/WEBP)</label>
                        <input type="file" name="bukti_transfer" class="form-control form-control-sm" accept="image/*" required style="border-radius:8px;">
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label class="form-label" style="font-size:12px;font-weight:600;">Catatan / Nama Rekening Pengirim (Opsional)</label>
                        <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Contoh: Transfer dari DANA a.n Ahmad" style="border-radius:8px;">
                    </div>
                    <div style="grid-column: 1 / -1;margin-top:4px;">
                        <button type="submit" class="dash-btn dash-btn-primary" style="width:100%;justify-content:center;">
                            <i class="bi bi-send-check"></i> Kirim Konfirmasi Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Target Kas Kelas</div>
                    <div class="dash-card-sub">Jumlah kas yang disepakati kelas untuk dikumpulkan bersama</div>
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
                        <span style="color:var(--text-secondary);">Target kesepakatan</span>
                        <span style="font-weight:700;"><?= rupiah($total_target) ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:var(--text-secondary);">Sudah terkumpul</span>
                        <span style="font-weight:700;color:var(--success);"><?= rupiah($kelas_collected) ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:var(--text-secondary);">Masih kurang</span>
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
                        <div><b>Target kas kelas tercapai.</b> Terima kasih sudah berpartisipasi, teman-teman!</div>
                    </div>
                <?php else: ?>
                    <div class="dash-notice" style="margin-top:14px;">
                        <div>Kas kelas sudah terkumpul <b><?= rupiah($kelas_collected) ?></b> dari target. Terima kasih atas partisipasinya!</div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="dash-charts">
        <div class="dash-card" id="riwayat">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title">Riwayat Pembayaran Saya</div>
                    <div class="dash-card-sub">Status pembayaran kas kamu per bulan</div>
                </div>
                <a href="pengeluaran.php" class="dash-btn dash-btn-light">
                    Lihat Pengeluaran
                </a>
            </div>

            <?php if (empty($pembayaran)): ?>
                    <div class="dash-empty">
                        <div class="t">Belum ada riwayat pembayaran</div>
                        <div class="s">Belum ada pembayaran tercatat. Yuk segera bayar kas kamu!</div>
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
