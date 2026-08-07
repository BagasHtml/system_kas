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
if (isset($_POST['hapus'])) {
    if (!Koneksi::csrfCheck()) {
        Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
        header("Location: pengeluaran.php" . $back);
        exit;
    }
    $id = (int)$_POST['hapus'];
    if ($id > 0) {
        $db::q("DELETE FROM pengeluaran WHERE id = ?", [$id]);
        Koneksi::setFlash('success', 'Data pengeluaran berhasil dihapus.');
    }
    header("Location: pengeluaran.php" . $back);
    exit;
}

/* ===== TAMBAH / EDIT ===== */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!Koneksi::csrfCheck()) {
        Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
        header("Location: pengeluaran.php" . $back);
        exit;
    }
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

<div class="main-content dash-page">
    <div class="dash-topbar">
        <div>
            <h1 class="dash-title">Pengeluaran Kas</h1>
            <p class="dash-subtitle">Catat pengeluaran uang kas kelas</p>
        </div>
        <div class="dash-topbar-actions">
            <button class="dash-btn dash-btn-primary" data-bs-toggle="modal" data-bs-target="#modalPengeluaran">
                <?= ic('<path d="M12 5v14M5 12h14"/>', 15) ?> Tambah Pengeluaran
            </button>
        </div>
    </div>

    <?php Koneksi::renderFlash(); ?>

    <div class="dash-card">
        <div class="dash-card-head">
            <div>
                <div class="dash-card-title">Daftar Pengeluaran</div>
                <div class="dash-card-sub">
                    <?= $total_data > 0 ? "Menampilkan $start_item&ndash;$end_item dari $total_data transaksi &middot; total " . rupiah($total) : 'Belum ada transaksi' ?>
                </div>
            </div>
            <form class="table-search" method="get">
                <input type="search" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari keterangan atau tanggal">
                <button type="submit">Cari</button>
                <?php if ($cari !== ''): ?>
                    <a class="table-search-clear" href="pengeluaran.php" title="Reset">×</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="dash-table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th style="width:140px;">Tanggal</th>
                        <th>Keterangan</th>
                        <th style="text-align:right;">Jumlah</th>
                        <th style="text-align:center;width:130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pengeluaran)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="dash-empty">
                                    <div class="t"><?= $cari !== '' ? 'Tidak ada hasil untuk "' . htmlspecialchars($cari) . '"' : 'Belum ada data pengeluaran' ?></div>
                                    <div class="s"><?= $cari === '' ? 'Catat pengeluaran melalui tombol Tambah Pengeluaran' : 'Coba kata kunci lain' ?></div>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; ?>
                        <?php foreach ($pengeluaran as $p): ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $no++ ?></td>
                                <td style="color:var(--text-secondary);"><?= date('d/m/Y', strtotime($p['tanggal'])) ?></td>
                                <td style="font-weight:600;"><?= htmlspecialchars($p['keterangan']) ?></td>
                                <td style="text-align:right;"><span class="dash-amount" style="color:var(--danger);">- <?= rupiah((float)$p['jumlah']) ?></span></td>
                                <td style="text-align:center;">
                                    <div style="display:flex;gap:6px;justify-content:center;">
                                        <button class="dash-btn dash-btn-light" style="padding:6px 12px;"
                                                data-bs-toggle="modal" data-bs-target="#modalPengeluaran"
                                                data-id="<?= $p['id'] ?>"
                                                data-keterangan="<?= htmlspecialchars($p['keterangan']) ?>"
                                                data-jumlah="<?= $p['jumlah'] ?>"
                                                data-tanggal="<?= $p['tanggal'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="post" action="pengeluaran.php<?= $back ?>" style="display:inline;"
                                              onsubmit="return confirmDelete(event, 'Yakin hapus pengeluaran ini?')">
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
                    Menampilkan <?= $start_item ?>–<?= $end_item ?> dari <?= $total_data ?> transaksi &middot; total <?= rupiah($total) ?>
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
                <?= Koneksi::csrfField() ?>
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
                    <button type="button" class="dash-btn dash-btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="dash-btn dash-btn-primary">Simpan</button>
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
