<?php
require_once __DIR__ . '/../../app/controllers/SiswaBelanjaController.php';
extract(SiswaBelanjaController::handle(), EXTR_SKIP);

$title = 'Target Belanja - Siswa';
$active = 'belanja';

include '../partials/header.php';
include '../partials/helpers.php';

$dana_no  = Koneksi::pengaturan('nomor_dana', '0813-XXXX-XXXX');
$dana_nm  = Koneksi::pengaturan('atas_nama_dana', '-');
$qris_img = Koneksi::pengaturan('qris_path', 'assets/img/qris.png');
?>

<div class="main-content student-page">
    <?php include '../partials/siswa_sidebar.php'; ?>

    <div class="dash-topbar">
        <div>
            <p class="student-eyebrow">Target Belanja</p>
            <h1 class="dash-title">Barang Kelas</h1>
            <p class="dash-subtitle">Progres pengumpulan dana untuk pembelian barang kelas</p>
        </div>
        <div class="dash-topbar-actions">
            <span class="dash-datechip">
                <?= ic('<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>', 14) ?>
                <?= date('d M Y') ?>
            </span>
            <div class="dash-user">
                <span class="dash-username"><?= $siswa_nama ?></span>
                <div class="dash-avatar"><?= strtoupper(mb_substr(trim($siswa_nama), 0, 1)) ?></div>
            </div>
        </div>
    </div>

    <?php Koneksi::renderFlash(); ?>

    <?php if (empty($items_my)): ?>
        <div class="dash-card">
            <div class="dash-empty">
                <div class="t">Belum ada target belanja</div>
                <div class="s">Bendahara belum menetapkan target belanja untuk kelas</div>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($items_my as $im):
        $it     = $im['item'];
        $myPaid = $im['my_paid'];
        $myShare= $im['my_share'];
        $myKurang = $im['my_kurang'];
        $myPct  = $im['my_pct'];
    ?>
    <div class="dash-charts">
        <div class="dash-card" id="bayar-<?= $it['id'] ?>">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title"><?= htmlspecialchars($it['nama_barang']) ?></div>
                    <div class="dash-card-sub"><?= $it['status_text'] ?> &middot; Target <?= rupiah($it['target']) ?></div>
                </div>
                <span class="dash-status-pill <?= $it['status_cls'] ?>">
                    <i class="bi bi-<?= $it['status'] === 'tercapai' ? 'check-circle-fill' : ($it['status'] === 'terbeli' ? 'check2-circle' : 'hourglass-split') ?>"></i>
                    <?= $it['status_text'] ?>
                </span>
            </div>

            <div class="dash-pay" style="grid-template-columns:1fr;">
                <div class="dash-pay-method">
                    <div class="dash-pay-info" style="width:100%;">
                        <!-- Progress Personal -->
                        <div style="margin-bottom:16px;">
                            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);font-weight:700;margin-bottom:4px;">Progres Kamu</div>
                            <div style="display:flex;align-items:baseline;gap:8px;">
                                <span style="font-size:28px;font-weight:800;letter-spacing:-.5px;color:var(--accent);"><?= $myPct ?>%</span>
                                <span style="font-size:12px;color:var(--text-muted);"><?= rupiah($myPaid) ?> / <?= rupiah($myShare) ?></span>
                            </div>
                            <div class="dash-progress" style="margin-top:6px;">
                                <div class="track">
                                    <div class="fill" style="width:<?= $myPct ?>%;"></div>
                                </div>
                            </div>
                            <?php if ($myKurang > 0): ?>
                            <div style="margin-top:4px;font-size:12px;color:var(--danger);font-weight:600;">
                                <i class="bi bi-exclamation-circle"></i> Masih kurang <?= rupiah($myKurang) ?>
                            </div>
                            <?php else: ?>
                            <div style="margin-top:4px;font-size:12px;color:var(--success);font-weight:600;">
                                <i class="bi bi-check-circle-fill"></i> Setoran kamu sudah lunas!
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Progress Kelas -->
                        <div style="padding:12px 14px;border-radius:14px;background:var(--body-bg);border:1px solid var(--border);">
                            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);font-weight:700;margin-bottom:4px;">Progres Kelas</div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                                <span>Terkumpul <?= rupiah($it['collected']) ?></span>
                                <span style="font-weight:700;"><?= $it['pct'] ?>%</span>
                            </div>
                            <div class="dash-progress" style="margin-top:0;">
                                <div class="track">
                                    <div class="fill" style="width:<?= $it['pct'] ?>%;"></div>
                                </div>
                            </div>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                                <?= rupiah($it['target']) ?> total target &middot; <?= $it['keterangan'] ? htmlspecialchars($it['keterangan']) : '' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Setoran (jika belum lunas dan status berlangsung/tercapai) -->
                <?php if ($myKurang > 0 && in_array($it['status'], ['berlangsung', 'tercapai'])): ?>
                <div class="dash-pay-method" style="margin-top:12px;">
                    <div class="dash-pay-brand dana">
                        <?= ic('<path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/>', 20) ?>
                        Setor ke <?= htmlspecialchars($it['nama_barang']) ?>
                    </div>
                    <div class="dash-pay-info" style="width:100%;">
                        <form action="" method="POST" style="display:flex;flex-direction:column;gap:12px;">
                            <?= Koneksi::csrfField() ?>
                            <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:4px;">Jumlah (Rp)</label>
                                <input type="number" class="form-control" name="jumlah" min="1000" max="<?= (int)$myKurang ?>" placeholder="Masukkan jumlah, maks <?= rupiah($myKurang) ?>" required
                                       style="border-radius:10px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:4px;">Metode</label>
                                <select class="form-select" name="metode" style="border-radius:10px;">
                                    <option value="langsung">Langsung (Cash)</option>
                                    <option value="qris">QRIS</option>
                                    <option value="dana">Send Dana</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:4px;">Catatan (Opsional)</label>
                                <input type="text" class="form-control" name="catatan" placeholder="Contoh: DP" style="border-radius:10px;">
                            </div>
                            <button type="submit" name="setor_belanja" class="dash-btn dash-btn-primary" style="width:100%;justify-content:center;">
                                <i class="bi bi-send"></i> Kirim Setoran
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include '../partials/siswa_mobile_nav.php'; ?>

<?php include '../partials/footer.php'; ?>