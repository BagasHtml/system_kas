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
include '../partials/helpers.php';
include_once '../../database/db.php';

$db = new Koneksi();

$cari = trim($_GET['cari'] ?? '');
$kat_filter = trim($_GET['kategori'] ?? '');
$back_params = [];
if ($cari !== '') $back_params['cari'] = $cari;
if ($kat_filter !== '') $back_params['kategori'] = $kat_filter;
$back = $back_params ? '?' . http_build_query($back_params) : '';

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
    $kategori   = trim($_POST['kategori'] ?? 'Lainnya');

    if ($tanggal === '' || $keterangan === '' || $jumlah <= 0) {
        Koneksi::setFlash('error', 'Tanggal, keterangan, dan jumlah wajib diisi.');
        header("Location: pengeluaran.php" . $back);
        exit;
    }

    // Handle Upload Bukti Nota
    $nota_path = null;
    if (isset($_FILES['bukti_nota']) && $_FILES['bukti_nota']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['bukti_nota']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = 'nota_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $targetDir = str_replace('\\', '/', dirname(__DIR__, 2)) . '/assets/uploads/bukti_nota/';
            if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
            $targetFile = $targetDir . $filename;
            if (move_uploaded_file($_FILES['bukti_nota']['tmp_name'], $targetFile)) {
                $nota_path = 'assets/uploads/bukti_nota/' . $filename;
            }
        }
    }

    if ($id > 0) {
        if ($nota_path) {
            $db::q("UPDATE pengeluaran SET tanggal = ?, keterangan = ?, jumlah = ?, kategori = ?, bukti_nota = ? WHERE id = ?", [$tanggal, $keterangan, $jumlah, $kategori, $nota_path, $id]);
        } else {
            $db::q("UPDATE pengeluaran SET tanggal = ?, keterangan = ?, jumlah = ?, kategori = ? WHERE id = ?", [$tanggal, $keterangan, $jumlah, $kategori, $id]);
        }
        Koneksi::setFlash('success', 'Data pengeluaran berhasil diperbarui.');
    } else {
        $db::q("INSERT INTO pengeluaran (tanggal, keterangan, jumlah, kategori, bukti_nota) VALUES (?, ?, ?, ?, ?)", [$tanggal, $keterangan, $jumlah, $kategori, $nota_path]);
        Koneksi::setFlash('success', 'Data pengeluaran berhasil ditambahkan.');
    }
    header("Location: pengeluaran.php" . $back);
    exit;
}

/* ===== TAMPIL & KALKULASI SALDO ===== */
$masuk_agg = $db::q("SELECT COALESCE(SUM(jumlah), 0) AS total FROM pembayaran WHERE status = 'lunas'")->fetch_assoc();
$total_pemasukan = (float)($masuk_agg['total'] ?? 0);

$exp_all = $db::q("SELECT COALESCE(SUM(jumlah), 0) AS total FROM pengeluaran")->fetch_assoc();
$total_pengeluaran_all = (float)($exp_all['total'] ?? 0);

$sisa_saldo_kas = $total_pemasukan - $total_pengeluaran_all;

$per_page = 10;
$page = max(1, (int)($_GET['hal'] ?? 1));

$where_clauses = [];
$params = [];
if ($cari !== '') {
    $where_clauses[] = "(keterangan LIKE ? OR tanggal LIKE ?)";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
}
if ($kat_filter !== '') {
    $where_clauses[] = "kategori = ?";
    $params[] = $kat_filter;
}

$where = $where_clauses ? " WHERE " . implode(" AND ", $where_clauses) : '';

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

$kpis = [
    ['label' => 'Total Pemasukan Kas', 'value' => rupiah($total_pemasukan), 'note' => 'Iuran siswa lunas', 'tone' => 'success'],
    ['label' => 'Total Pengeluaran Kas', 'value' => rupiah($total_pengeluaran_all), 'note' => 'Total semua transaksi keluar', 'tone' => 'danger'],
    ['label' => 'Sisa Saldo Kas Kelas', 'value' => rupiah($sisa_saldo_kas), 'note' => $sisa_saldo_kas >= 0 ? 'Saldo kas positif' : 'Warning: Saldo defisit!', 'tone' => 'accent'],
];
?>

<div class="main-content dash-page">
    <div class="dash-topbar">
        <div>
            <h1 class="dash-title">Pengeluaran Kas</h1>
            <p class="dash-subtitle">Catat pengeluaran uang kas kelas secara terukur dan transparan</p>
        </div>
        <div class="dash-topbar-actions">
            <button class="dash-btn dash-btn-primary" data-bs-toggle="modal" data-bs-target="#modalPengeluaran">
                <?= ic('<path d="M12 5v14M5 12h14"/>', 15) ?> Tambah Pengeluaran
            </button>
        </div>
    </div>

    <?php Koneksi::renderFlash(); ?>

    <div class="dash-kpis" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:20px;">
        <?php foreach ($kpis as $k): ?>
            <?= kpi($k) ?>
        <?php endforeach; ?>
    </div>

    <div class="dash-card">
        <div class="dash-card-head" style="flex-wrap:wrap;gap:12px;">
            <div>
                <div class="dash-card-title">Daftar Pengeluaran</div>
                <div class="dash-card-sub">
                    <?= $total_data > 0 ? "Menampilkan $start_item&ndash;$end_item dari $total_data transaksi &middot; total " . rupiah($total) : 'Belum ada transaksi' ?>
                </div>
            </div>
            <form class="table-search" method="get" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <select name="kategori" class="form-select form-select-sm" style="width:auto;border-radius:8px;" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php foreach (['Kebersihan', 'Acara', 'ATK', 'Perlengkapan', 'Lainnya'] as $kt): ?>
                        <option value="<?= $kt ?>" <?= $kat_filter === $kt ? 'selected' : '' ?>><?= $kt ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="search" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari keterangan/tanggal">
                <button type="submit">Cari</button>
                <?php if ($cari !== '' || $kat_filter !== ''): ?>
                    <a class="table-search-clear" href="pengeluaran.php" title="Reset">×</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="dash-table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th style="width:110px;">Tanggal</th>
                        <th style="width:120px;">Kategori</th>
                        <th>Keterangan</th>
                        <th style="text-align:center;width:80px;">Nota</th>
                        <th style="text-align:right;">Jumlah</th>
                        <th style="text-align:center;width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pengeluaran)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="dash-empty">
                                    <div class="t"><?= ($cari !== '' || $kat_filter !== '') ? 'Tidak ada hasil pengeluaran' : 'Belum ada data pengeluaran' ?></div>
                                    <div class="s"><?= ($cari === '' && $kat_filter === '') ? 'Catat pengeluaran melalui tombol Tambah Pengeluaran' : 'Coba ubah kata kunci atau filter' ?></div>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; ?>
                        <?php foreach ($pengeluaran as $p): ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $no++ ?></td>
                                <td style="color:var(--text-secondary);"><?= date('d/m/Y', strtotime($p['tanggal'])) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border" style="font-size:11px;font-weight:600;padding:4px 8px;border-radius:12px;">
                                        <?= htmlspecialchars($p['kategori'] ?? 'Lainnya') ?>
                                    </span>
                                </td>
                                <td style="font-weight:600;"><?= htmlspecialchars($p['keterangan']) ?></td>
                                <td style="text-align:center;">
                                    <?php if (!empty($p['bukti_nota'])): ?>
                                        <button class="dash-btn dash-btn-light" style="padding:4px 8px;font-size:12px;"
                                                data-bs-toggle="modal" data-bs-target="#modalNota"
                                                data-img="<?= BASE_URL . '/' . htmlspecialchars($p['bukti_nota']) ?>"
                                                data-title="Nota: <?= htmlspecialchars($p['keterangan']) ?>">
                                            <i class="bi bi-receipt"></i> Nota
                                        </button>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);font-size:12px;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;"><span class="dash-amount" style="color:var(--danger);">- <?= rupiah((float)$p['jumlah']) ?></span></td>
                                <td style="text-align:center;">
                                    <div style="display:flex;gap:6px;justify-content:center;">
                                        <button class="dash-btn dash-btn-light" style="padding:6px 10px;"
                                                data-bs-toggle="modal" data-bs-target="#modalPengeluaran"
                                                data-id="<?= $p['id'] ?>"
                                                data-keterangan="<?= htmlspecialchars($p['keterangan']) ?>"
                                                data-jumlah="<?= $p['jumlah'] ?>"
                                                data-kategori="<?= htmlspecialchars($p['kategori'] ?? 'Lainnya') ?>"
                                                data-tanggal="<?= $p['tanggal'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="post" action="pengeluaran.php<?= $back ?>" style="display:inline;"
                                              onsubmit="return confirmDelete(event, 'Yakin hapus pengeluaran ini?')">
                                            <?= Koneksi::csrfField() ?>
                                            <input type="hidden" name="hapus" value="<?= $p['id'] ?>">
                                            <button type="submit" class="dash-btn dash-btn-light" style="padding:6px 10px;color:var(--danger);">
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
            <form action="" method="POST" enctype="multipart/form-data">
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
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="kategori" id="kategori">
                            <option value="Kebersihan">Kebersihan</option>
                            <option value="Acara">Acara</option>
                            <option value="ATK">ATK</option>
                            <option value="Perlengkapan">Perlengkapan</option>
                            <option value="Lainnya" selected>Lainnya</option>
                        </select>
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
                    <div class="mb-3">
                        <label class="form-label">Foto / Scan Nota Pembelian (Opsional)</label>
                        <input type="file" class="form-control" name="bukti_nota" accept="image/*">
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

<div class="modal fade" id="modalNota" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="modalNotaTitle">Bukti Nota</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalNotaImg" src="" alt="Bukti Nota" style="max-width:100%;max-height:450px;border-radius:8px;object-fit:contain;">
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('modalNota')?.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('modalNotaTitle').textContent = btn?.dataset.title || 'Bukti Nota';
    document.getElementById('modalNotaImg').src = btn?.dataset.img || '';
});

document.getElementById('modalPengeluaran')?.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const id = btn?.dataset.id;
    if (id) {
        document.getElementById('modalTitle').textContent = 'Edit Pengeluaran';
        document.getElementById('edit_id').value = id;
        document.getElementById('tanggal').value = btn.dataset.tanggal || '';
        document.getElementById('kategori').value = btn.dataset.kategori || 'Lainnya';
        document.getElementById('keterangan').value = btn.dataset.keterangan || '';
        document.getElementById('jumlah').value = btn.dataset.jumlah || '';
    } else {
        document.getElementById('modalTitle').textContent = 'Tambah Pengeluaran';
        document.getElementById('edit_id').value = '';
        document.getElementById('tanggal').value = '<?= date('Y-m-d') ?>';
        document.getElementById('kategori').value = 'Lainnya';
        document.getElementById('keterangan').value = '';
        document.getElementById('jumlah').value = '';
    }
});
</script>

<?php include '../partials/footer.php'; ?>
