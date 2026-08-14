<?php
require_once __DIR__ . '/../../app/controllers/PembayaranController.php';
extract(PembayaranController::handle(), EXTR_SKIP);

$title = 'Pembayaran - Admin';
$active = 'pembayaran';

include '../partials/header.php';
include '../partials/admin_sidebar.php';
include '../partials/helpers.php';
?>

<div class="main-content dash-page">
    <div class="dash-topbar">
        <div>
            <h1 class="dash-title">Pembayaran Kas</h1>
            <p class="dash-subtitle">Catat dan kelola iuran kas bulanan siswa</p>
        </div>
        <div class="dash-topbar-actions">
            </form>
        </div>
    </div>

    <?php Koneksi::renderFlash(); ?>

    <div class="dash-card target-card" style="margin-bottom:22px;">
        <div class="dash-card-head" style="margin-bottom:0;">
            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                <div class="target-head-icon">
                    <i class="bi bi-bullseye"></i>
                </div>
                <div style="flex:1;min-width:180px;">
                    <div class="dash-card-title">Target Kas Kelas</div>
                    <div class="dash-card-sub">Besaran kas yang disepakati kelas untuk tiap bulan</div>
                </div>
                <button type="button" class="dash-btn dash-btn-primary" onclick="focusTargetForm()">
                    <i class="bi bi-plus-circle"></i> Atur Target
                </button>
            </div>
        </div>

        <div class="target-layout">
            <div>
                <?php if ($hero['mode'] === 'period' || $hero['mode'] === 'overall'): ?>
                    <div class="target-hero <?= $hero['reached'] ? 'done' : '' ?>">
                        <div class="dash-donut" style="--p:<?= $hero['pct'] ?>%;">
                            <div class="dash-donut-center"><span class="pct"><?= $hero['pct'] ?>%</span></div>
                        </div>
                        <div class="target-hero-main">
                            <div class="target-hero-label"><?= htmlspecialchars($hero['label']) ?></div>
                            <div class="target-hero-amount"><?= $hero['amount'] ?></div>
                            <?php if (!empty($hero['sub'])): ?>
                                <div class="target-hero-sub"><?= $hero['sub'] ?></div>
                            <?php endif; ?>
                            <div class="target-hero-stats">
                                <?php foreach ($hero['stats'] as $st): ?>
                                    <div class="target-stat">
                                        <span class="ts-val <?= $st['cls'] ?>"><?= $st['val'] ?></span>
                                        <span class="ts-lbl"><?= htmlspecialchars($st['lbl']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <span class="target-badge <?= $hero['badge_cls'] ?>"><?= $hero['badge'] ?></span>
                    </div>
                <?php else: ?>
                    <div class="target-hero empty">
                        <div class="target-empty-icon">
                            <i class="bi bi-bullseye"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;"><?= $target_map ? 'Pilih bulan untuk lihat progres' : 'Belum ada target kas kelas' ?></div>
                            <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;line-height:1.5;"><?= htmlspecialchars($hero['note'] ?? '') ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <form class="target-form" method="post" action="">
                <div class="target-form-title">
                    <i class="bi bi-gear-fill"></i>
                    <span id="targetFormTitle">Atur Target Baru</span>
                </div>
                <?= Koneksi::csrfField() ?>
                <div class="form-grid">
                    <div>
                        <label class="form-label">Bulan</label>
                        <input type="month" class="form-control" name="t_periode" id="t_periode"
                               value="<?= htmlspecialchars($selected_periode_db) ?>" required>
                    </div>
                    <div>
                        <label class="form-label">Kas per Siswa (Rp)</label>
                        <input type="text" inputmode="numeric" class="form-control" name="t_target" id="t_target"
                               placeholder="mis. 5.000" oninput="updateTargetHint()" required>
                    </div>
                    <div class="full">
                        <label class="form-label">Keterangan (opsional)</label>
                        <input type="text" class="form-control" name="t_keterangan" id="t_keterangan"
                               placeholder="mis. disepakati rapat kelas">
                    </div>
                </div>
                <div class="target-form-hint" id="targetTotalHint">
                    Total target kelas dihitung otomatis dari jumlah siswa
                </div>
                <div class="target-form-actions">
                    <button type="submit" name="simpan_target" value="1" class="dash-btn dash-btn-primary" id="btnSimpanTarget">
                        <i class="bi bi-check-circle"></i> Simpan Target
                    </button>
                    <button type="button" class="dash-btn dash-btn-light" id="btnBatalEdit" style="display:none;" onclick="resetTargetForm()">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                </div>
            </form>
        </div>

        <?php if ($target_map): ?>
            <div class="target-table-title">
                <span><i class="bi bi-table"></i> Daftar Target Bulanan</span>
                <span class="target-table-count"><?= count($target_map) ?> bulan ditargetkan</span>
            </div>
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th style="text-align:right;">Kas / Siswa</th>
                            <th style="text-align:right;">Total Target</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th style="text-align:center;width:170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($target_map as $tp => $tv): ?>
                            <?php
                            $tc = $target_collected[$tp] ?? 0.0;
                            $tps = (float)$tv['per_siswa'];
                            $tt = Koneksi::totalTargetPeriod($tps);
                            $tpct = $tt > 0 ? min(100, round($tc / $tt * 100)) : 0;
                            $reached = $tt > 0 && $tc >= $tt;
                            ?>
                            <tr>
                                <td style="font-weight:600;"><?= htmlspecialchars(Koneksi::periodeLabel($tp)) ?></td>
                                <td style="text-align:right;"><span class="dash-amount"><?= rupiah($tps) ?></span></td>
                                <td style="text-align:right;"><span class="dash-amount"><?= rupiah($tt) ?></span></td>
                                <td><?= $tv['keterangan'] !== null ? htmlspecialchars($tv['keterangan']) : '-' ?></td>
                                <td>
                                    <?php if ($reached): ?>
                                        <span class="dash-status-pill success"><i class="bi bi-check-circle-fill"></i> Target Tercapai</span>
                                    <?php else: ?>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <span class="dash-status-pill warn"><i class="bi bi-hourglass-split"></i> <?= $tpct ?>%</span>
                                        </div>
                                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                                            Terkumpul <?= rupiah($tc) ?> dari <?= rupiah($tt) ?>
                                        </div>
                                        <div class="target-mini-track"><div class="target-mini-fill" style="width:<?= $tpct ?>%;"></div></div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;">
                                    <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                                        <button type="button" class="dash-btn dash-btn-warn dash-btn-sm"
                                                data-tp="<?= htmlspecialchars($tp) ?>"
                                                data-per-siswa="<?= (float)$tps ?>"
                                                data-ket="<?= htmlspecialchars($tv['keterangan'] ?? '') ?>"
                                                onclick="editTarget(this)">
                                            <i class="bi bi-pencil"></i> Ubah
                                        </button>
                                        <form method="post" action="" style="display:inline;"
                                              onsubmit="return confirmDelete(event, 'Yakin hapus target <?= htmlspecialchars(Koneksi::periodeLabel($tp)) ?>?')">
                                            <?= Koneksi::csrfField() ?>
                                            <input type="hidden" name="hapus_target" value="<?= htmlspecialchars($tp) ?>">
                                            <button type="submit" class="dash-btn dash-btn-danger dash-btn-sm">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="dash-empty" style="padding:20px 16px 6px;">
                <div class="t">Belum ada target bulanan</div>
                <div class="s">Gunakan form di atas untuk menetapkan target kas tiap bulan</div>
            </div>
        <?php endif; ?>
    </div>

    <div class="dash-card">
        <div class="dash-card-head">
            <div>
                <div class="dash-card-title">Bulan: <?= $selected_periode_db !== '' ? htmlspecialchars(Koneksi::periodeLabel($selected_periode_db)) : 'Semua Bulan' ?></div>
                <div class="dash-card-sub"><?= $total_data ?> catatan pembayaran</div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <button class="dash-btn dash-btn-primary" data-bs-toggle="modal" data-bs-target="#modalPembayaran">
                    <?= ic('<path d="M12 5v14M5 12h14"/>', 15) ?> Tambah
                </button>
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
                                    <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                                        <button class="dash-btn dash-btn-light dash-btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#modalPembayaran"
                                                data-id="<?= $p['id'] ?>"
                                                data-siswa_id="<?= $p['siswa_id'] ?>"
                                                data-periode="<?= htmlspecialchars($p['periode']) ?>"
                                                data-jumlah="<?= $p['jumlah'] ?>"
                                                data-status="<?= $p['status'] ?>"
                                                data-tanggal="<?= $p['tanggal_bayar'] ?? '' ?>">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <form method="post" action="pembayaran.php<?= $back ?>" style="display:inline;"
                                              onsubmit="return confirmDelete(event, 'Yakin hapus pembayaran <?= htmlspecialchars($p['nama']) ?> bulan <?= htmlspecialchars(Koneksi::periodeLabel($p['periode'])) ?>?')">
                                            <?= Koneksi::csrfField() ?>
                                            <input type="hidden" name="hapus" value="<?= $p['id'] ?>">
                                            <button type="submit" class="dash-btn dash-btn-danger dash-btn-sm">
                                                <i class="bi bi-trash"></i> Hapus
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
                            <option value="pending">Menunggu Verifikasi (Pending)</option>
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

<div class="modal fade" id="modalBuktiVerifikasi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="modalBuktiVerifikasiTitle">Bukti Transfer</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalBuktiVerifikasiImg" src="" alt="Bukti Transfer" style="max-width:100%;max-height:400px;border-radius:8px;object-fit:contain;">
                <p id="modalBuktiVerifikasiCatatan" style="margin-top:12px;font-size:13px;color:var(--text-secondary);"></p>
            </div>
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

document.getElementById('modalBuktiVerifikasi')?.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('modalBuktiVerifikasiTitle').textContent = btn?.dataset.title || 'Bukti Transfer';
    document.getElementById('modalBuktiVerifikasiImg').src = btn?.dataset.img || '';
    const cat = btn?.dataset.catatan;
    document.getElementById('modalBuktiVerifikasiCatatan').textContent = cat ? 'Catatan Siswa: ' + cat : '';
});

function focusTargetForm() {
    document.getElementById('t_periode').scrollIntoView({ behavior: 'smooth', block: 'center' });
    document.getElementById('t_target').focus();
}

function editTarget(btn) {
    document.getElementById('t_periode').value = btn.dataset.tp || '';
    document.getElementById('t_target').value = btn.dataset.perSiswa || '';
    document.getElementById('t_keterangan').value = btn.dataset.ket || '';
    document.getElementById('targetFormTitle').textContent = 'Ubah Target';
    document.getElementById('btnSimpanTarget').innerHTML = '<i class="bi bi-check-circle"></i> Simpan Perubahan';
    document.getElementById('btnBatalEdit').style.display = '';
    updateTargetHint();
    focusTargetForm();
}

function resetTargetForm() {
    document.getElementById('t_periode').value = <?= json_encode($selected_periode_db) ?>;
    document.getElementById('t_target').value = '';
    document.getElementById('t_keterangan').value = '';
    document.getElementById('targetFormTitle').textContent = 'Atur Target Baru';
    document.getElementById('btnSimpanTarget').innerHTML = '<i class="bi bi-check-circle"></i> Simpan Target';
    document.getElementById('btnBatalEdit').style.display = 'none';
    updateTargetHint();
}

function updateTargetHint() {
    const el = document.getElementById('targetTotalHint');
    if (!el) return;
    const jml = <?= (int)$jumlah_siswa ?>;
    const raw = (document.getElementById('t_target').value || '').replace(/\D/g, '');
    const per = parseFloat(raw) || 0;
    el.textContent = per > 0
        ? 'Total target kelas = Rp ' + (per * jml).toLocaleString('id-ID') + ' (Rp ' + per.toLocaleString('id-ID') + ' x ' + jml + ' siswa)'
        : 'Total target kelas dihitung otomatis dari jumlah siswa';
}
updateTargetHint();

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
