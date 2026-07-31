<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$title = 'Data Siswa - Admin';
$active = 'siswa';
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
        $db::q("DELETE FROM siswa WHERE id = ?", [$id]);
        Koneksi::setFlash('success', 'Data siswa berhasil dihapus.');
    }
    header("Location: siswa.php" . $back);
    exit;
}

/* ===== TAMBAH / EDIT ===== */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $nomor_absen = (int)($_POST['nomor_absen'] ?? 0);

    if ($nama === '' || $nomor_absen <= 0) {
        Koneksi::setFlash('error', 'Nama dan nomor absen wajib diisi.');
        header("Location: siswa.php" . $back);
        exit;
    }

    $dup = $db::q("SELECT id FROM siswa WHERE nomor_absen = ? AND id <> ? LIMIT 1", [$nomor_absen, $id]);
    if ($dup && $dup->num_rows > 0) {
        Koneksi::setFlash('error', 'Nomor absen ' . $nomor_absen . ' sudah dipakai siswa lain.');
        header("Location: siswa.php" . $back);
        exit;
    }

    if ($id > 0) {
        $db::q("UPDATE siswa SET nama = ?, nomor_absen = ? WHERE id = ?", [$nama, $nomor_absen, $id]);
        Koneksi::setFlash('success', 'Data siswa berhasil diperbarui.');
    } else {
        $db::q("INSERT INTO siswa (nama, nomor_absen) VALUES (?, ?)", [$nama, $nomor_absen]);
        Koneksi::setFlash('success', 'Data siswa berhasil ditambahkan.');
    }
    header("Location: siswa.php" . $back);
    exit;
}

/* ===== TAMPIL ===== */
$per_page = 10;
$page = max(1, (int)($_GET['hal'] ?? 1));

$where = '';
$params = [];
if ($cari !== '') {
    $where = " WHERE nama LIKE ? OR nomor_absen LIKE ?";
    $params = ["%$cari%", "%$cari%"];
}

$jml = $db::q("SELECT COUNT(*) AS jml FROM siswa" . $where, $params)->fetch_assoc();
$total_data = (int)($jml['jml'] ?? 0);
$total_pages = max(1, (int)ceil($total_data / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$res = $db::q(
    "SELECT * FROM siswa" . $where . " ORDER BY nomor_absen ASC LIMIT ? OFFSET ?",
    array_merge($params, [$per_page, $offset])
);
$siswa = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

$start_item = $total_data === 0 ? 0 : $offset + 1;
$end_item = min($offset + count($siswa), $total_data);
$pg_query = http_build_query(['cari' => $cari]);
?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h4>Data Siswa</h4>
            <div class="sub">Kelola data siswa kelas</div>
        </div>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalSiswa">
            <i class="bi bi-plus-lg"></i> Tambah Siswa
        </button>
    </div>

    <?php Koneksi::renderFlash(); ?>

    <div class="table-container">
        <div class="table-header">
            <h6>Daftar Siswa</h6>
            <form class="table-search" method="get">
                <input type="search" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari nama atau no. absen">
                <button type="submit">Cari</button>
                <?php if ($cari !== ''): ?>
                    <a class="table-search-clear" href="siswa.php" title="Reset">×</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="text-align: center;width:60px;">No</th>
                        <th>Nama Siswa</th>
                        <th style="width:120px;">Nomor Absen</th>
                        <th style="width:180px;">Tanggal Daftar</th>
                        <th style="text-align:center;width:130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($siswa)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;padding:36px 0;color:var(--text-muted);">
                                <?= $cari !== '' ? 'Tidak ada siswa yang cocok dengan "' . htmlspecialchars($cari) . '"' : 'Belum ada data siswa' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; ?>
                        <?php foreach ($siswa as $s): ?>
                            <tr>
                                <td style="text-align:center;color:var(--text-muted);"><?= $no++ ?></td>
                                <td style="font-weight:600;"><?= htmlspecialchars($s['nama']) ?></td>
                                <td style="font-weight:600;"><?= (int)$s['nomor_absen'] ?></td>
                                <td style="color:var(--text-secondary);"><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                                <td style="text-align:center;">
                                    <div style="display:flex;gap:4px;justify-content:center;">
                                        <button class="btn-outline-custom" style="padding:4px 10px;"
                                                data-bs-toggle="modal" data-bs-target="#modalSiswa"
                                                data-id="<?= $s['id'] ?>"
                                                data-nama="<?= htmlspecialchars($s['nama']) ?>"
                                                data-absen="<?= $s['nomor_absen'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="?hapus=<?= $s['id'] ?><?= $cari !== '' ? '&cari=' . urlencode($cari) : '' ?>"
                                           class="btn-outline-custom" style="padding:4px 10px;color:var(--danger);"
                                           onclick="return confirmDelete(event, 'Yakin hapus <?= htmlspecialchars($s['nama']) ?>? Pembayaran terkait ikut terhapus.')">
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
                    Menampilkan <?= $start_item ?>–<?= $end_item ?> dari <?= $total_data ?> siswa
                </span>
                <?php include '../partials/pagination.php'; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalSiswa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h6 class="modal-title" id="modalTitle">Tambah Siswa</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Absen</label>
                        <input type="number" class="form-control" id="nomor_absen" name="nomor_absen" min="1" required>
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
document.getElementById('modalSiswa')?.addEventListener('show.bs.modal', (e) => {
    const btn = e.relatedTarget;
    const id = btn?.dataset.id;
    if (id) {
        document.getElementById('modalTitle').textContent = 'Edit Siswa';
        document.getElementById('edit_id').value = id;
        document.getElementById('nama').value = btn.dataset.nama || '';
        document.getElementById('nomor_absen').value = btn.dataset.absen || '';
    } else {
        document.getElementById('modalTitle').textContent = 'Tambah Siswa';
        document.getElementById('edit_id').value = '';
        document.getElementById('nama').value = '';
        document.getElementById('nomor_absen').value = '';
    }
});
window.addEventListener('DOMContentLoaded', () => {
    if (new URLSearchParams(window.location.search).has('tambah')) {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSiswa')).show();
    }
});
</script>

<?php include '../partials/footer.php'; ?>
