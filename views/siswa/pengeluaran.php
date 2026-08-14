<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$title = 'Pengeluaran Kas - Siswa';
include '../partials/header.php';
include '../partials/helpers.php';
include_once '../../database/db.php';

if (!isset($_SESSION['siswa_id'])) {
    header("Location: index.php");
    exit;
}

$db = new Koneksi();

$cari = trim($_GET['cari'] ?? '');
$kat_filter = trim($_GET['kategori'] ?? '');

$masuk = $db::q("SELECT COALESCE(SUM(jumlah), 0) AS t FROM pembayaran WHERE status = 'lunas'")->fetch_assoc();
$pemasukan = (float)($masuk['t'] ?? 0);

$exp_all = $db::q("SELECT COALESCE(SUM(jumlah), 0) AS total, COUNT(*) AS jml FROM pengeluaran")->fetch_assoc();
$total_pengeluaran_all = (float)($exp_all['total'] ?? 0);
$total_transaksi_all   = (int)($exp_all['jml'] ?? 0);

$saldo = $pemasukan - $total_pengeluaran_all;

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

$agg = $db::q("SELECT COALESCE(SUM(jumlah), 0) AS total, COUNT(*) AS jml FROM pengeluaran" . $where, $params)->fetch_assoc();
$total = (float)($agg['total'] ?? 0);
$total_transaksi = (int)($agg['jml'] ?? 0);

$per_page = 8;
$page = max(1, (int)($_GET['hal'] ?? 1));
$total_pages = max(1, (int)ceil($total_transaksi / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$pengeluaran = $db::q(
    "SELECT * FROM pengeluaran" . $where . " ORDER BY tanggal DESC, id DESC LIMIT ? OFFSET ?",
    array_merge($params, [$per_page, $offset])
)->fetch_all(MYSQLI_ASSOC);

$start_item = $total_transaksi === 0 ? 0 : $offset + 1;
$end_item = min($offset + count($pengeluaran), $total_transaksi);

$pages = [];
for ($i = 1; $i <= $total_pages; $i++) {
    if ($i === 1 || $i === $total_pages || abs($i - $page) <= 2) {
        if (($pages[count($pages) - 1] ?? 0) !== $i - 1 && $pages[count($pages) - 1] !== '...') {
            $pages[] = '...';
        }
        $pages[] = $i;
    }
}

$siswa_nama  = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');
$siswa_absen = htmlspecialchars($_SESSION['siswa_absen'] ?? '-');

$kpis = [
    ['label' => 'Total Pengeluaran', 'value' => rupiah($total_pengeluaran_all), 'note' => $total_transaksi_all . ' transaksi', 'tone' => 'danger'],
    ['label' => 'Sisa Saldo Kas Kelas', 'value' => rupiah($saldo), 'tone' => 'accent'],
    ['label' => 'Total Pemasukan Kas', 'value' => rupiah($pemasukan), 'tone' => 'success'],
];
?>

<div class="main-content student-page">
    <?php $active = 'pengeluaran'; include '../partials/siswa_sidebar.php'; ?>
    <div class="dash-topbar">
        <div>
            <p class="student-eyebrow">Pengeluaran Kas</p>
            <h1 class="dash-title">Catatan Pengeluaran</h1>
            <p class="dash-subtitle">Semua penggunaan uang kas kelas dicatat transparan untuk teman-teman</p>
        </div>
        <div class="dash-topbar-actions">
            <a href="dashboard.php#bayar" class="dash-btn dash-btn-primary">
                Bayar Kas Online
            </a>
            <a href="dashboard.php" class="dash-btn dash-btn-light">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="dash-notice">
        <div>
            <b>Transparan.</b>
            Uang kas kelas dikelola pengurus dan dipakai untuk keperluan bersama. Setiap pengeluaran ditampilkan di sini.
        </div>
    </div>

    <div class="dash-kpis">
        <?php foreach ($kpis as $k): ?>
            <?= kpi($k) ?>
        <?php endforeach; ?>
    </div>

    <div class="dash-card">
        <div class="dash-card-head" style="flex-wrap:wrap;gap:12px;">
            <div>
                <div class="dash-card-title">Daftar Pengeluaran</div>
                <div class="dash-card-sub">
                    <?= $total_transaksi > 0 ? "Menampilkan $start_item&ndash;$end_item dari $total_transaksi transaksi &middot; total " . rupiah($total) : 'Belum ada transaksi' ?>
                </div>
            </div>
            <form class="table-search" method="get" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <select name="kategori" class="form-select form-select-sm" style="width:auto;border-radius:8px;" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php foreach (['Kebersihan', 'Acara', 'ATK', 'Perlengkapan', 'Lainnya'] as $kt): ?>
                        <option value="<?= $kt ?>" <?= $kat_filter === $kt ? 'selected' : '' ?>><?= $kt ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="search" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari pengeluaran...">
                <button type="submit">Cari</button>
                <?php if ($cari !== '' || $kat_filter !== ''): ?>
                    <a class="table-search-clear" href="pengeluaran.php" title="Reset">×</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($pengeluaran)): ?>
            <div class="dash-empty">
                <div class="t"><?= ($cari !== '' || $kat_filter !== '') ? 'Tidak ada hasil pengeluaran' : 'Belum ada pengeluaran' ?></div>
                <div class="s"><?= ($cari === '' && $kat_filter === '') ? 'Belum ada catatan pengeluaran kas kelas' : 'Coba ubah kata kunci atau filter' ?></div>
            </div>
        <?php else: ?>
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
                        </tr>
                    </thead>
                    <tbody>
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
                                <td><span style="font-weight:600;"><?= htmlspecialchars($p['keterangan']) ?></span></td>
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
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="dash-pagination">
                    <span class="dash-pg-info">Halaman <?= $page ?> dari <?= $total_pages ?></span>
                    <div class="dash-pg-nav">
                        <a href="?hal=<?= max(1, $page - 1) ?>&cari=<?= urlencode($cari) ?>&kategori=<?= urlencode($kat_filter) ?>" class="dash-pg-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                            <?= ic('<path d="M15 6l-6 6 6 6"/>', 14) ?>
                            Sebelumnya
                        </a>
                        <?php foreach ($pages as $pg): ?>
                            <?php if ($pg === '...'): ?>
                                <span class="dash-pg-ellipsis">...</span>
                            <?php else: ?>
                                <a href="?hal=<?= $pg ?>&cari=<?= urlencode($cari) ?>&kategori=<?= urlencode($kat_filter) ?>" class="dash-pg-btn <?= $pg === $page ? 'active' : '' ?>"><?= $pg ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <a href="?hal=<?= min($total_pages, $page + 1) ?>&cari=<?= urlencode($cari) ?>&kategori=<?= urlencode($kat_filter) ?>" class="dash-pg-btn <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            Berikutnya
                            <?= ic('<path d="M9 6l6 6-6 6"/>', 14) ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
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
</script>

<nav class="mobile-nav">
    <a href="dashboard.php">
        <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
    <a class="active" href="pengeluaran.php">
        <i class="bi bi-cart-dash-fill"></i> Pengeluaran
    </a>
    <a href="../../function/logout.php" class="quit">
        <i class="bi bi-box-arrow-right"></i> Keluar
    </a>
</nav>

<?php include '../partials/footer.php'; ?>
