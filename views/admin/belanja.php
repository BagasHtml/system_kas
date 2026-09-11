<?php
require_once __DIR__ . '/../../app/controllers/BelanjaController.php';
extract(BelanjaController::handle(), EXTR_SKIP);

$title = 'Target Belanja - Admin';
$active = 'belanja';

include '../partials/header.php';
include '../partials/admin_sidebar.php';
include '../partials/helpers.php';
?>

<div class="main-content dash-page">
    <div class="dash-topbar">
        <div>
            <h1 class="dash-title">Target Belanja Kelas</h1>
            <p class="dash-subtitle">Kelola target belanja barang kelas &amp; progres setoran siswa</p>
        </div>
        <div class="dash-topbar-actions">
            <button class="dash-btn dash-btn-primary" data-bs-toggle="modal" data-bs-target="#modalBelanja">
                <i class="bi bi-plus-lg"></i> Tambah Target
            </button>
        </div>
    </div>

    <?php Koneksi::renderFlash(); ?>

    <div class="dash-banner">
        <div class="dash-banner-col">
            <div class="dash-banner-icon"><i class="bi bi-bullseye"></i></div>
            <div>
                <span class="dash-banner-label">Total Target</span>
                <span class="dash-banner-value"><?= rupiah($total_pemasukan) ?></span>
                <span class="dash-banner-note">pemasukan kas utama</span>
            </div>
        </div>
        <div class="dash-banner-col">
            <div class="dash-banner-icon"><i class="bi bi-piggy-bank"></i></div>
            <div>
                <span class="dash-banner-label">Saldo Kas</span>
                <span class="dash-banner-value"><?= rupiah($saldo_kas) ?></span>
                <span class="dash-banner-note">sisa kas kelas utama</span>
            </div>
        </div>
    </div>

    <?php if (empty($items_progress)): ?>
        <div class="dash-card">
            <div class="dash-empty">
                <div class="t">Belum ada target belanja</div>
                <div class="s">Klik "Tambah Target" untuk membuat target belanja baru</div>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($items_progress as $ip):
        $it = $ip['item'];
        $students = $ip['students'];
    ?>
    <div class="dash-card" id="item-<?= $it['id'] ?>" style="margin-bottom:20px;">
        <div class="dash-card-head">
            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                <div style="width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,#00A37A,#00B98A);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;box-shadow:0 8px 18px rgba(0,163,122,0.28);flex-shrink:0;">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div style="flex:1;min-width:180px;">
                    <div class="dash-card-title"><?= htmlspecialchars($it['nama_barang']) ?></div>
                    <div class="dash-card-sub"><?= $it['keterangan'] ? htmlspecialchars($it['keterangan']) : 'Target per siswa: ' . rupiah($it['per_siswa']) ?></div>
                </div>
                <span class="dash-status-pill <?= $it['status_cls'] ?>"><i class="bi bi-<?= $it['status'] === 'tercapai' ? 'check-circle-fill' : ($it['status'] === 'terbeli' ? 'check2-circle' : 'hourglass-split') ?>"></i> <?= $it['status_text'] ?></span>
                <div style="display:flex;gap:6px;">
                    <button class="dash-btn dash-btn-light" style="padding:6px 10px;"
                            data-bs-toggle="modal" data-bs-target="#modalBelanja"
                            data-id="<?= $it['id'] ?>"
                            data-nama="<?= htmlspecialchars($it['nama_barang']) ?>"
                            data-keterangan="<?= htmlspecialchars($it['keterangan'] ?? '') ?>"
                            data-target="<?= $it['target'] ?>"
                            data-persiswa="<?= $it['per_siswa'] ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form method="post" style="display:inline;" onsubmit="return confirmDelete(event, 'Hapus target <?= htmlspecialchars($it['nama_barang']) ?>? Setoran dan pengeluaran terkait juga akan terhapus.')">
                        <?= Koneksi::csrfField() ?>
                        <input type="hidden" name="hapus_belanja" value="<?= $it['id'] ?>">
                        <button type="submit" class="dash-btn dash-btn-light" style="padding:6px 10px;color:var(--danger);">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div style="padding:0 20px 20px;">
            <div class="dash-progress" style="margin-top:0;">
                <div class="top">
                    <span class="lbl">Terkumpul</span>
                    <span class="val"><?= $it['pct'] ?>%</span>
                </div>
                <div class="track">
                    <div class="fill" style="width:<?= $it['pct'] ?>%;"></div>
                </div>
            </div>

            <div class="dash-target-stats">
                <div class="tstat">
                    <span class="lbl">Target</span>
                    <span class="val"><?= rupiah($it['target']) ?></span>
                </div>
                <div class="tstat">
                    <span class="lbl">Terkumpul</span>
                    <span class="val acc"><?= rupiah($it['collected']) ?></span>
                </div>
                <div class="tstat">
                    <span class="lbl">Sudah Dibeli</span>
                    <span class="val"><?= rupiah($it['purchased']) ?></span>
                </div>
                <div class="tstat">
                    <span class="lbl">Sisa Dana</span>
                    <span class="val <?= $it['sisa'] >= 0 ? 'up' : 'warn' ?>"><?= rupiah($it['sisa']) ?></span>
                </div>
            </div>
        </div>

        <!-- Tabel Progres Per Siswa -->
        <div style="padding:0 20px 20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
                <div style="font-weight:700;font-size:13px;color:var(--text-primary);">Progres Per Siswa (<?= count($students) ?> siswa)</div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input type="text" class="form-control" style="padding:6px 10px;font-size:12px;border-radius:8px;width:180px;"
                           placeholder="Cari nama siswa..." oninput="filterSiswa(this, 'students-<?= $it['id'] ?>')">
                    <button class="dash-btn dash-btn-primary" style="padding:5px 12px;font-size:12px;"
                            data-bs-toggle="modal" data-bs-target="#modalSetoran"
                            data-item-id="<?= $it['id'] ?>">
                        <i class="bi bi-plus-circle"></i> Catat Setoran
                    </button>
                </div>
            </div>

            <?php if ($it['status'] === 'terbeli'): ?>
                <div style="padding:12px 16px;border-radius:12px;background:#f0fdf4;border:1px solid #bbf7d0;font-size:13px;color:#166534;margin-bottom:14px;">
                    <i class="bi bi-check-circle-fill"></i> Barang sudah dibeli seharga <?= rupiah($it['purchased']) ?>. Setoran ditutup.
                </div>
            <?php endif; ?>

            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">No</th>
                            <th>Nama Siswa</th>
                            <th>Bagian</th>
                            <th style="text-align:right;">Disetor</th>
                            <th style="text-align:right;">Kurang</th>
                            <th style="text-align:center;width:120px;">Progres</th>
                        </tr>
                    </thead>
                    <tbody id="students-<?= $it['id'] ?>">
                        <?php $no = 1; ?>
                        <?php foreach ($students as $s): ?>
                        <tr>
                            <td style="color:var(--text-muted);"><?= $no++ ?></td>
                            <td>
                                <div class="dash-cell-name">
                                    <div class="dash-cell-avatar" style="background:var(--accent-soft);color:var(--accent);">
                                        <?= strtoupper(substr($s['nama'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <span class="nm"><?= htmlspecialchars($s['nama']) ?></span>
                                        <span class="ab">Absen <?= $s['absen'] ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="dash-amount"><?= rupiah($s['share']) ?></span></td>
                            <td style="text-align:right;"><span class="dash-amount" style="color:var(--success);"><?= rupiah($s['paid']) ?></span></td>
                            <td style="text-align:right;"><?= $s['kurang'] > 0 ? '<span class="dash-amount" style="color:var(--danger);">' . rupiah($s['kurang']) . '</span>' : '<span style="color:var(--success);font-size:12px;font-weight:600;">Lunas</span>' ?></td>
                            <td style="text-align:center;">
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <div style="flex:1;height:6px;border-radius:4px;background:#EEF0F3;overflow:hidden;">
                                        <div style="height:100%;border-radius:4px;background:linear-gradient(90deg,#00A37A,#00B98A);width:<?= $s['pct'] ?>%;transition:width 0.3s;"></div>
                                    </div>
                                    <span style="font-size:11px;font-weight:700;color:var(--accent);min-width:32px;text-align:right;"><?= $s['pct'] ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (count($students) > 10): ?>
            <div style="text-align:center;margin-top:8px;">
                <button type="button" class="dash-btn dash-btn-light" style="font-size:12px;"
                        id="students-<?= $it['id'] ?>-more"
                        data-tbody="students-<?= $it['id'] ?>" data-expanded="0"
                        onclick="toggleMoreSiswa(this)">
                    <i class="bi bi-chevron-down"></i> Tampilkan Semua
                </button>
            </div>
            <script>
            (function() {
                const rows = document.getElementById('students-<?= $it['id'] ?>')?.querySelectorAll('tr') || [];
                let c = 0;
                rows.forEach(r => { c++; if (c > 10) r.style.display = 'none'; });
            })();
            </script>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal Tambah/Edit Target -->
<div class="modal fade" id="modalBelanja" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST">
                <?= Koneksi::csrfField() ?>
                <input type="hidden" name="id" id="edit_id_belanja">
                <div class="modal-header">
                    <h6 class="modal-title" id="modalBelanjaTitle">Tambah Target Belanja</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" class="form-control" name="nama_barang" id="nama_barang" placeholder="Contoh: Kaos Kelas XII RPL" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" name="keterangan" id="keterangan_belanja" rows="2" placeholder="Deskripsi singkat"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Total (Rp)</label>
                        <input type="number" class="form-control" name="target" id="target_belanja" min="1" placeholder="3000000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Per Siswa (Rp, opsional)</label>
                        <input type="number" class="form-control" name="per_siswa" id="per_siswa_belanja" min="0" placeholder="Otomatis: target / jumlah siswa">
                        <div class="form-text">Kosongkan untuk otomatis dibagi rata</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="dash-btn dash-btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_belanja" class="dash-btn dash-btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Catat Setoran -->
<div class="modal fade" id="modalSetoran" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST">
                <?= Koneksi::csrfField() ?>
                <input type="hidden" name="item_id" id="setoran_item_id">
                <div class="modal-header">
                    <h6 class="modal-title">Catat Setoran Siswa</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Siswa</label>
                        <select class="form-select" name="siswa_id" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php foreach ($daftar_siswa as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?> (No. <?= $s['nomor_absen'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" class="form-control" name="jumlah" min="1" placeholder="100000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Metode</label>
                        <select class="form-select" name="metode">
                            <option value="langsung">Langsung (Cash)</option>
                            <option value="qris">QRIS</option>
                            <option value="dana">Send Dana</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal_setor" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <input type="text" class="form-control" name="catatan" placeholder="Contoh: DP awal">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="dash-btn dash-btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_setoran" class="dash-btn dash-btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('modalBelanja')?.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const id = btn?.dataset.id;
    if (id) {
        document.getElementById('modalBelanjaTitle').textContent = 'Edit Target Belanja';
        document.getElementById('edit_id_belanja').value = id;
        document.getElementById('nama_barang').value = btn.dataset.nama || '';
        document.getElementById('keterangan_belanja').value = btn.dataset.keterangan || '';
        document.getElementById('target_belanja').value = btn.dataset.target || '';
        document.getElementById('per_siswa_belanja').value = btn.dataset.persiswa || '';
    } else {
        document.getElementById('modalBelanjaTitle').textContent = 'Tambah Target Belanja';
        document.getElementById('edit_id_belanja').value = '';
        document.getElementById('nama_barang').value = '';
        document.getElementById('keterangan_belanja').value = '';
        document.getElementById('target_belanja').value = '';
        document.getElementById('per_siswa_belanja').value = '';
    }
});

document.getElementById('modalSetoran')?.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('setoran_item_id').value = btn?.dataset.itemId || '';
});

function filterSiswa(input, tbodyId) {
    const q = input.value.toLowerCase();
    const rows = document.getElementById(tbodyId)?.querySelectorAll('tr') || [];
    let shown = 0;
    rows.forEach(r => {
        if (r.classList.contains('show-more-row')) return;
        const name = r.querySelector('.nm')?.textContent?.toLowerCase() || '';
        const match = !q || name.includes(q);
        r.style.display = match ? '' : 'none';
        if (match) shown++;
    });
    const moreBtn = document.getElementById(tbodyId + '-more');
    if (moreBtn) moreBtn.style.display = shown > 10 ? '' : 'none';
}

function toggleMoreSiswa(btn) {
    const tbodyId = btn.dataset.tbody;
    const rows = document.getElementById(tbodyId)?.querySelectorAll('tr') || [];
    const expanding = btn.dataset.expanded !== '1';
    let count = 0;
    rows.forEach(r => {
        if (r.classList.contains('show-more-row')) return;
        count++;
        if (count > 10) r.style.display = expanding ? 'none' : '';
    });
    btn.dataset.expanded = expanding ? '1' : '0';
    btn.innerHTML = expanding
        ? '<i class="bi bi-chevron-down"></i> Tampilkan Semua'
        : '<i class="bi bi-chevron-up"></i> Sembunyikan';
}
</script>

<?php include '../partials/footer.php'; ?>