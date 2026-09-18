<?php
require_once __DIR__ . '/../../app/controllers/PengaturanController.php';
extract(PengaturanController::handle(), EXTR_SKIP);

$title = 'Pengaturan - Admin';
$active = 'pengaturan';

include '../partials/header.php';
include '../partials/admin_sidebar.php';
include '../partials/helpers.php';

$qris = $settings['qris_path'] ?? 'assets/img/qris.png';
$qris_exists = $qris && file_exists(dirname(__DIR__, 2) . '/' . $qris);
?>

<div class="main-content dash-page">
    <div class="dash-topbar">
        <div>
            <h1 class="dash-title">Pengaturan</h1>
            <p class="dash-subtitle">Kelola informasi kelas &amp; tampilan pembayaran online di dashboard siswa</p>
        </div>
    </div>

    <?php Koneksi::renderFlash(); ?>

    <form id="formPengaturan" action="" method="POST" enctype="multipart/form-data">
        <?= Koneksi::csrfField() ?>

        <div class="cms-card">
            <div class="cms-card-head">
                <div class="cms-ic"><i class="bi bi-mortarboard-fill"></i></div>
                <div>
                    <div class="cms-card-title">Identitas Kelas</div>
                    <div class="cms-card-sub">Informasi sekolah &amp; kelas yang tampil di dashboard siswa</div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="cms-label">Nama Sekolah</label>
                    <div class="cms-input">
                        <i class="bi bi-buildings"></i>
                        <input type="text" class="form-control" name="nama_sekolah"
                               value="<?= htmlspecialchars($settings['nama_sekolah'] ?? '') ?>"
                               placeholder="mis. SMK Taruna Bangsa">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="cms-label">Nama Kelas</label>
                    <div class="cms-input">
                        <i class="bi bi-people-fill"></i>
                        <input type="text" class="form-control" name="nama_kelas"
                               value="<?= htmlspecialchars($settings['nama_kelas'] ?? '') ?>"
                               placeholder="mis. XII RPL 5">
                    </div>
                </div>
            </div>
        </div>

        <div class="cms-card">
            <div class="cms-card-head">
                <div class="cms-ic"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="cms-card-title">Pembayaran Online</div>
                    <div class="cms-card-sub">Nomor tujuan saat siswa kirim kas lewat Send Dana</div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="cms-label">Nomor Dana</label>
                    <div class="cms-input">
                        <i class="bi bi-phone-fill"></i>
                        <input type="text" class="form-control" name="nomor_dana" id="nomor_dana"
                               value="<?= htmlspecialchars($settings['nomor_dana'] ?? '') ?>"
                               placeholder="0813-XXXX-XXXX">
                    </div>
                    <div class="cms-hint">Nomor aktif untuk transfer kas kelas</div>
                </div>
                <div class="col-md-6">
                    <label class="cms-label">Atas Nama Dana</label>
                    <div class="cms-input">
                        <i class="bi bi-person-badge-fill"></i>
                        <input type="text" class="form-control" name="atas_nama_dana"
                               value="<?= htmlspecialchars($settings['atas_nama_dana'] ?? '') ?>"
                               placeholder="Nama pemilik rekening Dana">
                    </div>
                    <div class="cms-hint">Ditampilkan agar siswa yakin nomor tujuan benar</div>
                </div>
            </div>
        </div>

        <div class="cms-card">
            <div class="cms-card-head">
                <div class="cms-ic"><i class="bi bi-qr-code"></i></div>
                <div>
                    <div class="cms-card-title">Gambar QRIS Kelas</div>
                    <div class="cms-card-sub">QRIS yang dipindai siswa di dashboard untuk membayar kas</div>
                </div>
            </div>
            <div class="cms-qris">
                <div class="cms-qris-preview">
                    <span class="cms-qris-label">QRIS Saat Ini</span>
                    <div class="cms-qris-img">
                        <?php if ($qris_exists): ?>
                            <img id="qrisPreviewImg" src="<?= BASE_URL . '/' . htmlspecialchars($qris) ?>" alt="QRIS Kas Kelas">
                        <?php else: ?>
                            <div id="qrisPreviewImg" class="cms-qris-none"><i class="bi bi-qr-code"></i></div>
                        <?php endif; ?>
                    </div>
                    <span class="cms-qris-name" id="qrisPreviewName"><?= $qris_exists ? htmlspecialchars(ucfirst(pathinfo($qris, PATHINFO_BASENAME))) : 'Belum ada gambar QRIS' ?></span>
                </div>
                <div class="cms-qris-upload">
                    <label class="cms-drop" for="qris_file" id="cmsDrop">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span class="t">Pilih gambar QRIS baru</span>
                        <span class="s">Klik untuk memilih file</span>
                        <small>JPG, PNG, atau WebP &middot; maks 5MB</small>
                    </label>
                    <input type="file" id="qris_file" name="qris_file" accept="image/*" hidden>
                    <p class="cms-drop-tip">
                        <i class="bi bi-info-circle"></i>
                        QRIS ini juga dipakai di halaman petunjuk pembayaran. Ganti di sini dan otomatis ter-update di sisi siswa.
                    </p>
                </div>
            </div>
        </div>

        <div class="cms-savebar">
            <div class="cms-savebar-info">
                <i class="bi bi-check2-circle"></i>
                <span>Perubahan langsung tampil di dashboard siswa</span>
            </div>
            <div class="cms-savebar-actions">
                <button type="reset" class="dash-btn dash-btn-light"><i class="bi bi-arrow-counterclockwise"></i> Batal</button>
                <button type="submit" name="simpan_pengaturan" value="1" class="dash-btn dash-btn-primary">
                    <i class="bi bi-check-lg"></i> Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    const fileInput = document.getElementById('qris_file');
    const dropZone  = document.getElementById('cmsDrop');
    if (!fileInput || !dropZone) return;

    const img  = document.getElementById('qrisPreviewImg');
    const name = document.getElementById('qrisPreviewName');

    dropZone.addEventListener('click', function () { fileInput.click(); });
    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropZone.classList.add('drag');
    });
    dropZone.addEventListener('dragleave', function () {
        dropZone.classList.remove('drag');
    });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.classList.remove('drag');
        if (e.dataTransfer && e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFile(e.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener('change', function () {
        if (fileInput.files.length) handleFile(fileInput.files[0]);
    });

    function handleFile(file) {
        if (!file) return;
        if (!/\.(jpe?g|png|webp)$/i.test(file.name)) {
            dropZone.classList.add('err');
            setTimeout(function () { dropZone.classList.remove('err'); }, 1600);
            fileInput.value = '';
            return;
        }
        const url = URL.createObjectURL(file);
        if (img && img.tagName === 'IMG') {
            img.src = url;
        }
        if (name) name.textContent = file.name;
    }
})();
</script>

<style>
.cms-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 22px 24px;
    margin-bottom: 22px;
    box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
}
.cms-card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.cms-ic {
    flex: 0 0 auto;
    width: 42px; height: 42px;
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 19px;
    background: var(--accent-soft);
    color: var(--accent);
}
.cms-card-title { font-size: 15px; font-weight: 800; color: var(--text-primary); }
.cms-card-sub  { font-size: 12px; color: var(--text-muted); margin-top: 1px; }
.cms-label {
    display: block;
    font-size: 12px; font-weight: 700;
    color: var(--text-secondary);
    margin-bottom: 6px;
}
.cms-hint { font-size: 11.5px; color: var(--text-muted); margin-top: 6px; }

.cms-input { position: relative; }
.cms-input > i {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted); font-size: 15px;
    pointer-events: none;
}
.cms-input .form-control {
    padding: 12px 14px 12px 40px;
    border-radius: 12px;
    border: 1.5px solid var(--border);
    font-size: 13.5px;
    font-weight: 500;
    background: #fff;
}
.cms-input .form-control:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 4px var(--accent-soft);
}

.cms-qris { display: flex; gap: 22px; flex-wrap: wrap; }
.cms-qris-preview {
    flex: 0 0 auto;
    width: 210px;
    display: flex; flex-direction: column; align-items: center;
    gap: 8px;
    padding: 14px;
    border: 1px solid var(--border);
    border-radius: 16px;
    background: #fafafc;
}
.cms-qris-label {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .05em; color: var(--text-muted);
}
.cms-qris-img {
    width: 160px; height: 160px;
    display: flex; align-items: center; justify-content: center;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
}
.cms-qris-img img { width: 100%; height: 100%; object-fit: contain; }
.cms-qris-none { font-size: 48px; color: var(--border); }
.cms-qris-name {
    max-width: 100%;
    font-size: 11.5px; color: var(--text-muted);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.cms-qris-upload { flex: 1; min-width: 240px; }
.cms-drop {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 4px;
    padding: 26px 16px;
    border: 1.5px dashed var(--border);
    border-radius: 14px;
    background: #fcfcfd;
    cursor: pointer;
    text-align: center;
    transition: border-color .2s ease, background .2s ease;
}
.cms-drop:hover, .cms-drop.drag {
    border-color: var(--accent);
    background: var(--accent-soft);
}
.cms-drop.err { border-color: var(--danger); background: var(--danger-bg); }
.cms-drop i { font-size: 26px; color: var(--accent); margin-bottom: 4px; }
.cms-drop .t { font-weight: 700; font-size: 13.5px; color: var(--text-primary); }
.cms-drop .s { font-size: 12px; color: var(--text-muted); }
.cms-drop small { font-size: 11px; color: var(--text-muted); margin-top: 6px; }
.cms-drop-tip {
    display: flex; align-items: flex-start; gap: 6px;
    margin-top: 10px;
    font-size: 11.5px; line-height: 1.5; color: var(--text-muted);
}
.cms-drop-tip i { font-size: 14px; margin-top: 1px; }

.cms-savebar {
    position: sticky; bottom: 14px;
    display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 10px 30px -12px rgba(16, 24, 40, 0.25);
}
.cms-savebar-info {
    display: flex; align-items: center; gap: 7px;
    font-size: 12px; color: var(--text-muted);
}
.cms-savebar-info i { color: var(--success); font-size: 14px; }
.cms-savebar-actions { display: flex; gap: 8px; }

@media (max-width: 540px) {
    .cms-qris { flex-direction: column; }
    .cms-qris-preview { width: 100%; }
}
</style>

<?php include '../partials/footer.php'; ?>