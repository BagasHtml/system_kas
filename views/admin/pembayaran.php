<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$title = 'Pembayaran - Admin';
$active = 'pembayaran';
include '../partials/header.php';
include '../partials/admin_sidebar.php';
include '../partials/helpers.php';
include_once '../../database/db.php';

$db = new Koneksi();

$selected_periode = $_GET['periode'] ?? '';
$cari = trim($_GET['cari'] ?? '');
$selected_periode_db = '';
if (preg_match('/^(\d{4})-(\d{2})$/', $selected_periode, $m) && (int)$m[2] >= 1 && (int)$m[2] <= 12) {
    $selected_periode_db = $selected_periode;
}

$back_parts = [];
if ($selected_periode !== '') $back_parts['periode'] = $selected_periode;
if ($cari !== '') $back_parts['cari'] = $cari;
$back = $back_parts ? '?' . http_build_query($back_parts) : '';

/* ===== HAPUS ===== */
if (isset($_POST['hapus'])) {
    if (!Koneksi::csrfCheck()) {
        Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
        header("Location: pembayaran.php" . $back);
        exit;
    }
    $id = (int)$_POST['hapus'];
    if ($id > 0) {
        $db::q("DELETE FROM pembayaran WHERE id = ?", [$id]);
        Koneksi::setFlash('success', 'Data pembayaran berhasil dihapus.');
    }
    header("Location: pembayaran.php" . $back);
    exit;
}

/* ===== TARGET KAS (kesepakatan kelas) ===== */
if (isset($_POST['simpan_target'])) {
    if (!Koneksi::csrfCheck()) {
        Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
        header("Location: pembayaran.php" . $back);
        exit;
    }
    $t_periode = trim($_POST['t_periode'] ?? '');
    $t_target  = (float)($_POST['t_target'] ?? 0);
    $t_keterangan = trim($_POST['t_keterangan'] ?? '');
    $t_valid = preg_match('/^(\d{4})-(\d{2})$/', $t_periode, $tm) && (int)$tm[2] >= 1 && (int)$tm[2] <= 12;

    if (!$t_valid || $t_target <= 0) {
        Koneksi::setFlash('error', 'Isi bulan dan target yang valid.');
        header("Location: pembayaran.php" . $back);
        exit;
    }

    $db::q(
        "INSERT INTO target_kas (periode, target, keterangan) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE target = ?, keterangan = ?",
        [$t_periode, $t_target, $t_keterangan !== '' ? $t_keterangan : null, $t_target, $t_keterangan !== '' ? $t_keterangan : null]
    );
    Koneksi::setFlash('success', 'Target kas untuk ' . Koneksi::periodeLabel($t_periode) . ' disimpan.');
    header("Location: pembayaran.php" . $back);
    exit;
}

if (isset($_POST['hapus_target'])) {
    if (!Koneksi::csrfCheck()) {
        Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
        header("Location: pembayaran.php" . $back);
        exit;
    }
    $t_periode = trim($_POST['hapus_target'] ?? '');
    if (preg_match('/^\d{4}-\d{2}$/', $t_periode)) {
        $db::q("DELETE FROM target_kas WHERE periode = ?", [$t_periode]);
        Koneksi::setFlash('success', 'Target ' . Koneksi::periodeLabel($t_periode) . ' dihapus.');
    }
    header("Location: pembayaran.php" . $back);
    exit;
}

/* ===== TAMBAH / EDIT ===== */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!Koneksi::csrfCheck()) {
        Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
        header("Location: pembayaran.php" . $back);
        exit;
    }
    $id        = (int)($_POST['id'] ?? 0);
    $siswa_id  = (int)($_POST['siswa_id'] ?? 0);
    $periode   = trim($_POST['periode'] ?? '');
    $jumlah    = (float)($_POST['jumlah'] ?? 0);
    $status    = ($_POST['status'] ?? 'belum') === 'lunas' ? 'lunas' : 'belum';
    $tanggal   = trim($_POST['tanggal_bayar'] ?? '');
    $tanggal   = $tanggal !== '' ? $tanggal : null;

    $periode_valid = preg_match('/^(\d{4})-(\d{2})$/', $periode, $pm) && (int)$pm[2] >= 1 && (int)$pm[2] <= 12;

    if ($siswa_id <= 0 || !$periode_valid || $jumlah <= 0) {
        Koneksi::setFlash('error', 'Data tidak lengkap: pilih siswa, bulan, dan jumlah yang valid.');
        header("Location: pembayaran.php" . $back);
        exit;
    }

    $periode_db = $periode;

    if ($status === 'lunas' && $tanggal === null) {
        $tanggal = date('Y-m-d');
    }

    if ($id > 0) {
        $db::q(
            "UPDATE pembayaran SET siswa_id = ?, periode = ?, jumlah = ?, status = ?, tanggal_bayar = ? WHERE id = ?",
            [$siswa_id, $periode_db, $jumlah, $status, $tanggal, $id]
        );
        Koneksi::setFlash('success', 'Data pembayaran berhasil diperbarui.');
    } else {
        $dup = $db::q("SELECT id FROM pembayaran WHERE siswa_id = ? AND periode = ? LIMIT 1", [$siswa_id, $periode_db]);
        if ($dup && $dup->num_rows > 0) {
            Koneksi::setFlash('error', 'Siswa tersebut sudah tercatat pada bulan ' . Koneksi::periodeLabel($periode_db) . '.');
            header("Location: pembayaran.php" . $back);
            exit;
        }
        $db::q(
            "INSERT INTO pembayaran (siswa_id, periode, jumlah, status, tanggal_bayar) VALUES (?, ?, ?, ?, ?)",
            [$siswa_id, $periode_db, $jumlah, $status, $tanggal]
        );
        Koneksi::setFlash('success', 'Data pembayaran berhasil ditambahkan.');
    }
    header("Location: pembayaran.php" . $back);
    exit;
}

$per_page = 10;
$page = max(1, (int)($_GET['hal'] ?? 1));

$sql = "SELECT p.id, p.siswa_id, p.periode, p.jumlah, p.status, p.tanggal_bayar, s.nama, s.nomor_absen
        FROM pembayaran p
        INNER JOIN siswa s ON s.id = p.siswa_id";
$conditions = [];
$params = [];
if ($selected_periode_db !== '') {
    $conditions[] = "p.periode = ?";
    $params[] = $selected_periode_db;
}
if ($cari !== '') {
    $conditions[] = "(s.nama LIKE ? OR p.periode LIKE ?)";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
}
if ($conditions) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY p.periode DESC, s.nomor_absen ASC";

$jml = $db::q(
    "SELECT COUNT(*) AS jml FROM pembayaran p INNER JOIN siswa s ON s.id = p.siswa_id" . ($conditions ? " WHERE " . implode(" AND ", $conditions) : ''),
    $params
)->fetch_assoc();
$total_data = (int)($jml['jml'] ?? 0);
$total_pages = max(1, (int)ceil($total_data / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$resData = $db::q($sql . " LIMIT ? OFFSET ?", array_merge($params, [$per_page, $offset]));
$data = $resData ? $resData->fetch_all(MYSQLI_ASSOC) : [];

$start_item = $total_data === 0 ? 0 : $offset + 1;
$end_item = min($offset + count($data), $total_data);
$pg_parts = [];
if ($selected_periode !== '') $pg_parts['periode'] = $selected_periode;
if ($cari !== '') $pg_parts['cari'] = $cari;
$pg_query = http_build_query($pg_parts);

$resSiswa = $db::q("SELECT id, nama, nomor_absen FROM siswa ORDER BY nomor_absen ASC");
$daftar_siswa = $resSiswa ? $resSiswa->fetch_all(MYSQLI_ASSOC) : [];

$target_map = Koneksi::targetMap();

$period_collected = 0.0;
$period_target = null;
if ($selected_periode_db !== '') {
    $aggP = $db::q(
        "SELECT COALESCE(SUM(CASE WHEN status = 'lunas' THEN jumlah END), 0) AS t FROM pembayaran WHERE periode = ?",
        [$selected_periode_db]
    )->fetch_assoc();
    $period_collected = (float)($aggP['t'] ?? 0);
    if (isset($target_map[$selected_periode_db])) {
        $period_target = $target_map[$selected_periode_db]['target'];
    }
}
?>

<div class="main-content dash-page">
    <div class="dash-topbar">
        <div>
            <h1 class="dash-title">Pembayaran Kas</h1>
            <p class="dash-subtitle">Kelola status pembayaran kas per bulan</p>
        </div>
        <div class="dash-topbar-actions">
            <form action="" method="GET">
                <input type="month" name="periode"
                       value="<?= htmlspecialchars($selected_periode) ?>"
                       style="font-size:12px;padding:8px 14px;border:1px solid var(--border);border-radius:999px;font-family:'Inter',sans-serif;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.03);"
                       onchange="this.form.submit()">
            </form>
            <button class="dash-btn dash-btn-primary" data-bs-toggle="modal" data-bs-target="#modalPembayaran">
                <?= ic('<path d="M12 5v14M5 12h14"/>', 15) ?> Tambah
            </button>
        </div>
    </div>

    <?php Koneksi::renderFlash(); ?>

    <div class="dash-card" style="margin-bottom:22px;">
        <div class="dash-card-head">
            <div>
                <div class="dash-card-title">Target Kas Kelas</div>
                <div class="dash-card-sub">Besaran kas yang disepakati kelas untuk tiap bulan</div>
            </div>
        </div>
        <div>
            <form method="post" action="" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <?= Koneksi::csrfField() ?>
                <div>
                    <label class="form-label">Bulan</label>
                    <input type="month" class="form-control" name="t_periode"
                           value="<?= htmlspecialchars($selected_periode_db) ?>" style="min-width:150px;" required>
                </div>
                <div>
                    <label class="form-label">Target (Rp)</label>
                    <input type="number" class="form-control" name="t_target" min="1" step="500"
                           placeholder="mis. 50000" style="min-width:130px;" required>
                </div>
                <div style="flex:1;min-width:180px;">
                    <label class="form-label">Keterangan (opsional)</label>
                    <input type="text" class="form-control" name="t_keterangan" placeholder="mis. disepakati rapat kelas">
                </div>
                <button type="submit" name="simpan_target" value="1" class="dash-btn dash-btn-primary">Simpan Target</button>
            </form>

            <?php if ($period_target !== null): ?>
                <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
                    <div style="display:flex;justify-content:space-between;align-items:baseline;font-size:12px;margin-bottom:8px;">
                        <span>Target <?= htmlspecialchars(Koneksi::periodeLabel($selected_periode_db)) ?></span>
                        <span>
                            <b>Sudah dibayar <?= rupiah($period_collected) ?></b>
                            dari <?= rupiah($period_target) ?>
                            (<?= min(100, round($period_collected / $period_target * 100)) ?>%)
                        </span>
                    </div>
                    <div class="dash-progress" style="margin-top:0;">
                        <div class="track">
                            <div class="fill" style="width:<?= min(100, round($period_collected / $period_target * 100)) ?>%"></div>
                        </div>
                    </div>
                    <?php if ($period_collected >= $period_target): ?>
                        <div style="margin-top:10px;font-size:12px;color:var(--success);font-weight:600;">Target bulan ini tercapai.</div>
                    <?php else: ?>
                        <div style="margin-top:10px;font-size:12px;color:var(--text-secondary);">
                            Tersisa <?= rupiah(max(0, $period_target - $period_collected)) ?> menuju target kelas.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($target_map): ?>
                <div class="dash-table-wrap" style="margin-top:16px;">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th style="text-align:right;">Target</th>
                                <th>Keterangan</th>
                                <th style="text-align:center;width:80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($target_map as $tp => $tv): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars(Koneksi::periodeLabel($tp)) ?></td>
                                    <td style="text-align:right;"><span class="dash-amount"><?= rupiah($tv['target']) ?></span></td>
                                    <td><?= $tv['keterangan'] !== null ? htmlspecialchars($tv['keterangan']) : '-' ?></td>
                                    <td style="text-align:center;">
                                        <form method="post" action="" style="display:inline;">
                                            <?= Koneksi::csrfField() ?>
                                            <input type="hidden" name="hapus_target" value="<?= htmlspecialchars($tp) ?>">
                                            <button type="submit" class="dash-btn dash-btn-light" style="padding:6px 12px;color:var(--danger);">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="dash-card">
        <div class="dash-card-head">
            <div>
                <div class="dash-card-title">Bulan: <?= $selected_periode_db !== '' ? htmlspecialchars(Koneksi::periodeLabel($selected_periode_db)) : 'Semua Bulan' ?></div>
                <div class="dash-card-sub"><?= $total_data ?> catatan pembayaran</div>
            </div>
            <form class="table-search" method="get">
                <?php if ($selected_periode !== ''): ?>
                    <input type="hidden" name="periode" value="<?= htmlspecialchars($selected_periode) ?>">
                <?php endif; ?>
                <input type="search" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari nama atau bulan">
                <button type="submit">Cari</button>
                <?php if ($cari !== ''): ?>
                    <a class="table-search-clear" href="?<?= $selected_periode !== '' ? 'periode=' . urlencode($selected_periode) : '' ?>" title="Reset">×</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="dash-table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Nama Siswa</th>
                        <th>Bulan</th>
                        <th>Jumlah</th>
                        <th>Tanggal Bayar</th>
                        <th>Status</th>
                        <th style="text-align:center;width:130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="dash-empty">
                                    <div class="t"><?= $cari !== '' ? 'Tidak ada hasil untuk "' . htmlspecialchars($cari) . '"' : 'Belum ada data pembayaran' ?></div>
                                    <div class="s"><?= $cari === '' ? 'Catat pembayaran siswa melalui tombol Tambah' : 'Coba kata kunci lain' ?></div>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; ?>
                        <?php foreach ($data as $p): ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $no++ ?></td>
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
                                <td><span class="dash-amount"><?= rupiah((float)$p['jumlah']) ?></span></td>
                                <td style="color:var(--text-secondary);"><?= $p['tanggal_bayar'] ? date('d/m/Y', strtotime($p['tanggal_bayar'])) : '-' ?></td>
                                <td><?= status_pill($p['status']) ?></td>
                                <td style="text-align:center;">
                                    <div style="display:flex;gap:6px;justify-content:center;">
                                        <button class="dash-btn dash-btn-light" style="padding:6px 12px;"
                                                data-bs-toggle="modal" data-bs-target="#modalPembayaran"
                                                data-id="<?= $p['id'] ?>"
                                                data-siswa_id="<?= $p['siswa_id'] ?>"
                                                data-periode="<?= htmlspecialchars($p['periode']) ?>"
                                                data-jumlah="<?= $p['jumlah'] ?>"
                                                data-status="<?= $p['status'] ?>"
                                                data-tanggal="<?= $p['tanggal_bayar'] ?? '' ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="post" action="pembayaran.php<?= $back ?>" style="display:inline;"
                                              onsubmit="return confirmDelete(event, 'Yakin hapus pembayaran <?= htmlspecialchars($p['nama']) ?> bulan <?= htmlspecialchars(Koneksi::periodeLabel($p['periode'])) ?>?')">
                                            <?= Koneksi::csrfField() ?>
                                            <input type="hidden" name="hapus" value="<?= $p['id'] ?>">
                                            <button type="submit" class="dash-btn dash-btn-light" style="padding:6px 12px;color:var(--danger);">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total_data > 0): ?>
            <div class="dash-pagination">
                <span class="dash-pg-info">
                    Menampilkan <?= $start_item ?>–<?= $end_item ?> dari <?= $total_data ?> data
                </span>
                <?php include '../partials/pagination.php'; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalPembayaran" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST">
                <?= Koneksi::csrfField() ?>
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h6 class="modal-title" id="modalTitle">Tambah Pembayaran</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Siswa</label>
                        <select class="form-select" name="siswa_id" id="siswa_id" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php foreach ($daftar_siswa as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= htmlspecialchars($s['nama']) ?> (Absen <?= (int)$s['nomor_absen'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bulan</label>
                        <input type="month" class="form-control" name="periode" id="periode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" class="form-control" name="jumlah" id="jumlah" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="status" onchange="toggleTanggal()">
                            <option value="belum">Belum Terkumpul</option>
                            <option value="lunas">Terkumpul</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Bayar</label>
                        <input type="date" class="form-control" name="tanggal_bayar" id="tanggal_bayar">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="dash-btn dash-btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="dash-btn dash-btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleTanggal() {
    const status = document.getElementById('status').value;
    const tg = document.getElementById('tanggal_bayar');
    if (status === 'lunas' && !tg.value) {
        tg.value = new Date().toISOString().slice(0, 10);
    }
}

document.getElementById('modalPembayaran')?.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const id = btn?.dataset.id;
    if (id) {
        document.getElementById('modalTitle').textContent = 'Edit Pembayaran';
        document.getElementById('edit_id').value = id;
        document.getElementById('siswa_id').value = btn.dataset.siswa_id || '';
        document.getElementById('periode').value = btn.dataset.periode || '';
        document.getElementById('jumlah').value = btn.dataset.jumlah || '';
        document.getElementById('status').value = btn.dataset.status || 'belum';
        document.getElementById('tanggal_bayar').value = btn.dataset.tanggal || '';
    } else {
        document.getElementById('modalTitle').textContent = 'Tambah Pembayaran';
        document.getElementById('edit_id').value = '';
        document.getElementById('siswa_id').value = '';
        document.getElementById('periode').value = '';
        document.getElementById('jumlah').value = '';
        document.getElementById('status').value = 'belum';
        document.getElementById('tanggal_bayar').value = '';
    }
});
</script>

<?php include '../partials/footer.php'; ?>
