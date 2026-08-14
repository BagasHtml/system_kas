<?php
require_once __DIR__ . '/../../app/controllers/SiswaController.php';
extract(SiswaController::handle(), EXTR_SKIP);

$title = 'Data Siswa - Admin';
$active = 'siswa';

include '../partials/header.php';
include '../partials/admin_sidebar.php';
include '../partials/helpers.php';
?>

<div class="main-content dash-page">
    <div class="dash-topbar">
        <div>
            <h1 class="dash-title">Data Siswa</h1>
            <p class="dash-subtitle">Kelola data siswa kelas</p>
        </div>
        <div class="dash-topbar-actions">
            <button class="dash-btn dash-btn-primary" data-bs-toggle="modal" data-bs-target="#modalSiswa">
                <?= ic('<path d="M12 5v14M5 12h14"/>', 15) ?> Tambah Siswa
            </button>
        </div>
    </div>

    <?php Koneksi::renderFlash(); ?>

    <div class="dash-card">
        <div class="dash-card-head">
            <div>
                <div class="dash-card-title">Daftar Siswa</div>
                <div class="dash-card-sub"><?= $total_data ?> siswa terdaftar</div>
            </div>
            <form class="table-search" method="get">
                <input type="search" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari nama atau no. absen">
                <button type="submit">Cari</button>
                <?php if ($cari !== ''): ?>
                    <a class="table-search-clear" href="siswa.php" title="Reset">×</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="dash-table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Nama Siswa</th>
                        <th style="width:120px;">Nomor Absen</th>
                        <th style="width:160px;">Tanggal Daftar</th>
                        <th style="text-align:center;width:130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($siswa)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="dash-empty">
                                    <div class="t"><?= $cari !== '' ? 'Tidak ada hasil untuk "' . htmlspecialchars($cari) . '"' : 'Belum ada data siswa' ?></div>
                                    <div class="s"><?= $cari === '' ? 'Tambah siswa melalui tombol Tambah Siswa' : 'Coba kata kunci lain' ?></div>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; ?>
                        <?php foreach ($siswa as $s): ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $no++ ?></td>
                                <td>
                                    <div class="dash-cell-name">
                                        <div class="dash-cell-avatar" style="background:var(--accent-soft);color:var(--accent);">
                                            <?= strtoupper(substr($s['nama'], 0, 1)) ?>
                                        </div>
                                        <span class="nm"><?= htmlspecialchars($s['nama']) ?></span>
                                    </div>
                                </td>
                                <td><span class="dash-amount"><?= (int)$s['nomor_absen'] ?></span></td>
                                <td style="color:var(--text-secondary);"><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                                <td style="text-align:center;">
                                    <div style="display:flex;gap:6px;justify-content:center;">
                                        <button class="dash-btn dash-btn-light" style="padding:6px 12px;"
                                                data-bs-toggle="modal" data-bs-target="#modalSiswa"
                                                data-id="<?= $s['id'] ?>"
                                                data-nama="<?= htmlspecialchars($s['nama']) ?>"
                                                data-absen="<?= $s['nomor_absen'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="post" action="siswa.php<?= $back ?>" style="display:inline;"
                                              onsubmit="return confirmDelete(event, 'Yakin hapus <?= htmlspecialchars($s['nama']) ?>? Pembayaran terkait ikut terhapus.')">
                                            <?= Koneksi::csrfField() ?>
                                            <input type="hidden" name="hapus" value="<?= $s['id'] ?>">
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
                <?= Koneksi::csrfField() ?>
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
                    <button type="button" class="dash-btn dash-btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="dash-btn dash-btn-primary">Simpan</button>
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
