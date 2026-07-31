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
include_once '../../database/db.php';

$db = new Koneksi();

$bulan_id = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$bulan_rev = array_flip($bulan_id);

function periodeKeYm(string $periode, array $bulan_rev): string
{
    $parts = explode(' ', trim($periode));
    if (count($parts) === 2 && isset($bulan_rev[$parts[0]]) && ctype_digit($parts[1])) {
        return $parts[1] . '-' . str_pad((string)$bulan_rev[$parts[0]], 2, '0', STR_PAD_LEFT);
    }
    return '';
}

$selected_periode = $_GET['periode'] ?? '';
$cari = trim($_GET['cari'] ?? '');
$selected_periode_db = '';
if (preg_match('/^\d{4}-\d{2}$/', $selected_periode)) {
    $t = explode('-', $selected_periode);
    $bln = (int)$t[1];
    if ($bln >= 1 && $bln <= 12) {
        $selected_periode_db = $bulan_id[$bln] . ' ' . $t[0];
    }
}

$back_parts = [];
if ($selected_periode !== '') $back_parts['periode'] = $selected_periode;
if ($cari !== '') $back_parts['cari'] = $cari;
$back = $back_parts ? '?' . http_build_query($back_parts) : '';

/* ===== HAPUS ===== */
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id > 0) {
        $db::q("DELETE FROM pembayaran WHERE id = ?", [$id]);
        Koneksi::setFlash('success', 'Data pembayaran berhasil dihapus.');
    }
    header("Location: pembayaran.php" . $back);
    exit;
}

/* ===== TAMBAH / EDIT ===== */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $id        = (int)($_POST['id'] ?? 0);
    $siswa_id  = (int)($_POST['siswa_id'] ?? 0);
    $periode   = trim($_POST['periode'] ?? '');
    $jumlah    = (float)($_POST['jumlah'] ?? 0);
    $status    = ($_POST['status'] ?? 'belum') === 'lunas' ? 'lunas' : 'belum';
    $tanggal   = trim($_POST['tanggal_bayar'] ?? '');
    $tanggal   = $tanggal !== '' ? $tanggal : null;

    if ($siswa_id <= 0 || !preg_match('/^\d{4}-\d{2}$/', $periode) || $jumlah <= 0) {
        Koneksi::setFlash('error', 'Data tidak lengkap: pilih siswa, periode, dan jumlah yang valid.');
        header("Location: pembayaran.php" . $back);
        exit;
    }

    [$thn, $bln] = explode('-', $periode);
    $periode_db = $bulan_id[(int)$bln] . ' ' . $thn;

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
            Koneksi::setFlash('error', 'Siswa tersebut sudah tercatat pada periode ' . $periode_db . '.');
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

/* ===== TAMPIL ===== */
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
?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h4>Pembayaran Kas</h4>
            <div class="sub">Kelola status pembayaran kas per periode</div>
        </div>
        <div style="display:flex;gap:8px;">
            <form action="" method="GET">
                <input type="month" name="periode"
                       value="<?= htmlspecialchars($selected_periode) ?>"
                       style="font-size:12px;padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-family:'Inter',sans-serif;"
                       onchange="this.form.submit()">
            </form>
            <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalPembayaran">
                <i class="bi bi-plus-lg"></i> Tambah
            </button>
        </div>
    </div>

    <?php Koneksi::renderFlash(); ?>

    <div class="table-container">
        <div class="table-header">
            <h6>Periode: <?= $selected_periode_db !== '' ? htmlspecialchars($selected_periode_db) : 'Semua Periode' ?></h6>
            <form class="table-search" method="get">
                <?php if ($selected_periode !== ''): ?>
                    <input type="hidden" name="periode" value="<?= htmlspecialchars($selected_periode) ?>">
                <?php endif; ?>
                <input type="search" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari nama atau periode">
                <button type="submit">Cari</button>
                <?php if ($cari !== ''): ?>
                    <a class="table-search-clear" href="?<?= $selected_periode !== '' ? 'periode=' . urlencode($selected_periode) : '' ?>" title="Reset">×</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Nama Siswa</th>
                        <th>Periode</th>
                        <th>Jumlah</th>
                        <th>Tanggal Bayar</th>
                        <th>Status</th>
                        <th style="text-align:center;width:130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;padding:36px 0;color:var(--text-muted);">
                                <?= $cari !== '' ? 'Tidak ada pembayaran yang cocok dengan "' . htmlspecialchars($cari) . '"' : 'Belum ada data pembayaran pada periode ini' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; ?>
                        <?php foreach ($data as $p): ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $no++ ?></td>
                                <td style="font-weight:600;"><?= htmlspecialchars($p['nama']) ?></td>
                                <td><?= htmlspecialchars($p['periode']) ?></td>
                                <td>Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
                                <td style="color:var(--text-secondary);"><?= $p['tanggal_bayar'] ? date('d/m/Y', strtotime($p['tanggal_bayar'])) : '-' ?></td>
                                <td>
                                    <?php if ($p['status'] == 'lunas'): ?>
                                        <span class="text-status lunas">Lunas</span>
                                    <?php else: ?>
                                        <span class="text-status belum">Belum</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;">
                                    <div style="display:flex;gap:4px;justify-content:center;">
                                        <button class="btn-outline-custom" style="padding:4px 10px;"
                                                data-bs-toggle="modal" data-bs-target="#modalPembayaran"
                                                data-id="<?= $p['id'] ?>"
                                                data-siswa_id="<?= $p['siswa_id'] ?>"
                                                data-periode="<?= htmlspecialchars(periodeKeYm($p['periode'], $bulan_rev)) ?>"
                                                data-jumlah="<?= $p['jumlah'] ?>"
                                                data-status="<?= $p['status'] ?>"
                                                data-tanggal="<?= $p['tanggal_bayar'] ?? '' ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="?hapus=<?= $p['id'] ?><?= $back !== '' ? '&' . ltrim($back, '?') : '' ?>"
                                           class="btn-outline-custom" style="padding:4px 10px;color:var(--danger);"
                                           onclick="return confirmDelete(event, 'Yakin hapus pembayaran <?= htmlspecialchars($p['nama']) ?> periode <?= htmlspecialchars($p['periode']) ?>?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total_data > 0): ?>
            <div class="table-pagination">
                <span class="table-count">
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
                        <label class="form-label">Periode</label>
                        <input type="month" class="form-control" name="periode" id="periode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" class="form-control" name="jumlah" id="jumlah" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="status" onchange="toggleTanggal()">
                            <option value="belum">Belum Lunas</option>
                            <option value="lunas">Lunas</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Bayar</label>
                        <input type="date" class="form-control" name="tanggal_bayar" id="tanggal_bayar">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-custom" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-custom">Simpan</button>
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
