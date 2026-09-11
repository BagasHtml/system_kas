<?php
require_once __DIR__ . '/../../app/controllers/PengaturanController.php';
extract(PengaturanController::handle(), EXTR_SKIP);

$title = 'Pengaturan - Admin';
$active = 'pengaturan';

include '../partials/header.php';
include '../partials/admin_sidebar.php';
include '../partials/helpers.php';
?>

<div class="main-content dash-page">
    <div class="dash-topbar">
        <div>
            <h1 class="dash-title">Pengaturan</h1>
            <p class="dash-subtitle">Kelola tampilan dashboard siswa &amp; informasi kelas</p>
        </div>
    </div>

    <?php Koneksi::renderFlash(); ?>

    <div class="dash-card">
        <form action="" method="POST" enctype="multipart/form-data">
            <?= Koneksi::csrfField() ?>

            <div style="padding:20px 24px;">
                <div class="dash-card-title" style="margin-bottom:16px;">Informasi Kelas</div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Sekolah</label>
                        <input type="text" class="form-control" name="nama_sekolah" value="<?= htmlspecialchars($settings['nama_sekolah'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nama Kelas</label>
                        <input type="text" class="form-control" name="nama_kelas" value="<?= htmlspecialchars($settings['nama_kelas'] ?? '') ?>">
                    </div>
                </div>

                <div class="dash-card-title" style="margin-top:24px;margin-bottom:16px;">Pembayaran Online</div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nomor Dana</label>
                        <input type="text" class="form-control" name="nomor_dana" value="<?= htmlspecialchars($settings['nomor_dana'] ?? '') ?>" placeholder="0813-XXXX-XXXX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Atas Nama Dana</label>
                        <input type="text" class="form-control" name="atas_nama_dana" value="<?= htmlspecialchars($settings['atas_nama_dana'] ?? '') ?>">
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label">Gambar QRIS Saat Ini</label>
                        <?php
                            $qris = $settings['qris_path'] ?? 'assets/img/qris.png';
                            if ($qris && file_exists(dirname(__DIR__, 2) . '/' . $qris)):
                        ?>
                        <div style="margin-bottom:8px;">
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($qris) ?>" alt="QRIS" style="max-width:200px;border-radius:10px;border:1px solid var(--border);">
                        </div>
                        <?php else: ?>
                        <div style="margin-bottom:8px;padding:20px;text-align:center;background:var(--body-bg);border-radius:10px;border:1px dashed var(--border);color:var(--text-muted);font-size:12px;">
                            Belum ada gambar QRIS
                        </div>
                        <?php endif; ?>
                        <label class="form-label">Upload QRIS Baru</label>
                        <input type="file" class="form-control" name="qris_file" accept="image/*">
                    </div>
                </div>

                <div style="margin-top:24px;">
                    <button type="submit" name="simpan_pengaturan" class="dash-btn dash-btn-primary">
                        <i class="bi bi-check-lg"></i> Simpan Pengaturan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../partials/footer.php'; ?>