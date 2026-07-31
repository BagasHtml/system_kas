<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$title = 'Pengeluaran - Admin';
$active = 'pengeluaran';
include '../partials/header.php';
include '../partials/admin_sidebar.php';
include_once '../../database/db.php';

$db = new Koneksi();

$cari = trim($_GET['cari'] ?? '');
$back = $cari !== '' ? '?cari=' . urlencode($cari) : '';

/* ===== HAPUS ===== */
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id > 0) {
        $db::q("DELETE FROM pengeluaran WHERE id = ?", [$id]);
        Koneksi::setFlash('success', 'Data pengeluaran berhasil dihapus.');
    }
    header("Location: pengeluaran.php" . $back);
    exit;
}

/* ===== TAMBAH / EDIT ===== */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $id         = (int)($_POST['id'] ?? 0);
    $tanggal    = trim($_POST['tanggal'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $jumlah     = (float)($_POST['jumlah'] ?? 0);

    if ($tanggal === '' || $keterangan === '' || $jumlah <= 0) {
        Koneksi::setFlash('error', 'Tanggal, keterangan, dan jumlah wajib diisi.');
        header("Location: pengeluaran.php" . $back);
        exit;
    }

    if ($id > 0) {
        $db::q("UPDATE pengeluaran SET tanggal = ?, keterangan = ?, jumlah = ? WHERE id = ?", [$tanggal, $keterangan, $jumlah, $id]);
        Koneksi::setFlash('success', 'Data pengeluaran berhasil diperbarui.');
    } else {
        $db::q("INSERT INTO pengeluaran (tanggal, keterangan, jumlah) VALUES (?, ?, ?)", [$tanggal, $keterangan, $jumlah]);
        Koneksi::setFlash('success', 'Data pengeluaran berhasil ditambahkan.');
    }
    header("Location: pengeluaran.php" . $back);
    exit;
}

/* ===== TAMPIL ===== */
$per_page = 10;
$page = max(1, (int)($_GET['hal'] ?? 1));

$where = '';
$params = [];
if ($cari !== '') {
    $where = " WHERE keterangan LIKE ? OR tanggal LIKE ?";
    $params = ["%$cari%", "%$cari%"];
}

$agg = $db::q(
    "SELECT COUNT(*) AS jml, COALESCE(SUM(jumlah), 0) AS total FROM pengeluaran" . $where,
    $params
)->fetch_assoc();
$total_data = (int)($agg['jml'] ?? 0);
$total = (float)($agg['total'] ?? 0);
$total_pages = max(1, (int)ceil($total_data / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$pengeluaran = $db::q(
    "SELECT * FROM pengeluaran" . $where . " ORDER BY tanggal DESC, id DESC LIMIT ? OFFSET ?",
    array_merge($params, [$per_page, $offset])
);
$pengeluaran = $pengeluaran ? $pengeluaran->fetch_all(MYSQLI_ASSOC) : [];

$start_item = $total_data === 0 ? 0 : $offset + 1;
$end_item = min($offset + count($pengeluaran), $total_data);
$pg_query = http_build_query(['cari' => $cari]);
?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h4>Pengeluaran Kas</h4>
            <div class="sub">Catat pengeluaran uang kas kelas</div>
        </div>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalPengeluaran">
            <i class="bi bi-plus-lg"></i> Tambah Pengeluaran
        </button>
    </div>

    <?php Koneksi::renderFlash(); ?>

    <div class="table-container">
        <div class="table-header">
            <h6>Daftar Pengeluaran</h6>
            <form class="table-search" method="get">
                <input type="search" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari keterangan atau tanggal">
                <button type="submit">Cari</button>
                <?php if ($cari !== ''): ?>
                    <a class="table-search-clear" href="pengeluaran.php" title="Reset">×</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th style="width:140px;">Tanggal</th>
                        <th>Keterangan</th>
                        <th style="width:180px;">Jumlah</th>
                        <th style="text-align:center;width:130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pengeluaran)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;padding:36px 0;color:var(--text-muted);">
                                <?= $cari !== '' ? 'Tidak ada pengeluaran yang cocok dengan "' . htmlspecialchars($cari) . '"' : 'Belum ada data pengeluaran' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; ?>
                        <?php foreach ($pengeluaran as $p): ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $no++ ?></td>
                                <td style="color:var(--text-secondary);"><?= date('d/m/Y', strtotime($p['tanggal'])) ?></td>
                                <td style="font-weight:600;"><?= htmlspecialchars($p['keterangan']) ?></td>
                                <td style="color:var(--danger);font-weight:700;">- Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
                                <td style="text-align:center;">
                                    <div style="display:flex;gap:4px;justify-content:center;">
                                        <button class="btn-outline-custom" style="padding:4px 10px;"
                                                data-bs-toggle="modal" data-bs-target="#modalPengeluaran"
                                                data-id="<?= $p['id'] ?>"
                                                data-keterangan="<?= htmlspecialchars($p['keterangan']) ?>"
                                                data-jumlah="<?= $p['jumlah'] ?>"
                                                data-tanggal="<?= $p['tanggal'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="?hapus=<?= $p['id'] ?><?= $cari !== '' ? '&cari=' . urlencode($cari) : '' ?>"
                                           class="btn-outline-custom" style="padding:4px 10px;color:var(--danger);"
                                           onclick="return confirmDelete(event, 'Yakin hapus pengeluaran ini?')">
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
                    Menampilkan <?= $start_item ?>–<?= $end_item ?> dari <?= $total_data ?> transaksi &middot; total Rp <?= number_format($total, 0, ',', '.') ?>
                </span>
                <?php include '../partials/pagination.php'; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalPengeluaran" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h6 class="modal-title" id="modalTitle">Tambah Pengeluaran</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" id="tanggal"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="keterangan" rows="3"
                                  placeholder="Deskripsi pengeluaran" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" class="form-control" name="jumlah" id="jumlah" min="1" required>
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
document.getElementById('modalPengeluaran')?.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const id = btn?.dataset.id;
    if (id) {
        document.getElementById('modalTitle').textContent = 'Edit Pengeluaran';
        document.getElementById('edit_id').value = id;
        document.getElementById('tanggal').value = btn.dataset.tanggal || '';
        document.getElementById('keterangan').value = btn.dataset.keterangan || '';
        document.getElementById('jumlah').value = btn.dataset.jumlah || '';
    } else {
        document.getElementById('modalTitle').textContent = 'Tambah Pengeluaran';
        document.getElementById('edit_id').value = '';
        document.getElementById('tanggal').value = '<?= date('Y-m-d') ?>';
        document.getElementById('keterangan').value = '';
        document.getElementById('jumlah').value = '';
    }
});
</script>

<?php include '../partials/footer.php'; ?>
