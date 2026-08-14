<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../database/db.php';

$title = 'Konfirmasi &amp; Upload Bukti Pembayaran';
include '../partials/header.php';
include '../partials/helpers.php';
?>

<!-- ============ FORM (style-nya di assets/css/style.css) ============ -->
<form class="pay-card" method="post" enctype="multipart/form-data" action="">
    <?= Koneksi::csrfField() ?>

    <div class="pay-head">
        <div class="pay-head-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 15V4" />
                <path d="m8 8 4-4 4 4" />
                <path d="M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3" />
            </svg>
        </div>
        <div>
            <h2>Konfirmasi &amp; Upload Bukti Pembayaran</h2>
            <p>Sudah transfer via DANA atau QRIS? Upload foto bukti transfer di bawah agar bendahara dapat
                memverifikasi.</p>
        </div>
    </div>

    <div class="pay-grid">
        <div class="pay-field">
            <label for="bulan">Bulan iuran yang dibayar</label>
            <div class="select-wrap">
                <select class="pay-select" id="bulan" name="bulan" required>
                    <!-- opsi bulan dari PHP Anda -->
                    <option value="2026-08" selected>Agustus 2026</option>
                </select>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m6 9 6 6 6-6" />
                </svg>
            </div>
        </div>
        <div class="pay-field">
            <label for="jumlahTampil">Jumlah yang ditransfer</label>
            <div class="amount-wrap">
                <span class="prefix">Rp</span>
                <input class="pay-input" type="text" id="jumlahTampil" inputmode="numeric" value="20000"
                    autocomplete="off">
                <input type="hidden" id="jumlah" name="jumlah" value="20000">
            </div>
        </div>
    </div>

    <div class="pay-field">
        <label>Upload Foto Bukti Transfer</label>
        <label class="dropzone" id="dropzone" for="bukti">
            <input type="file" id="bukti" name="bukti" accept=".jpg,.jpeg,.png,.webp" hidden required>
            <div id="dzIdle">
                <div class="dz-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="3" />
                        <circle cx="9" cy="9" r="2" />
                        <path d="m21 15-4-4-8 8" />
                    </svg>
                </div>
                <div class="dz-title">Klik untuk pilih file <span>atau seret &amp; letakkan</span></div>
                <div class="dz-hint">JPG, PNG, WEBP • maks 5 MB</div>
            </div>
            <div class="dz-preview" id="dzPreview" hidden>
                <img id="dzImg" alt="Preview bukti">
                <div class="dz-meta">
                    <div class="dz-name" id="dzName"></div>
                    <div class="dz-size" id="dzSize"></div>
                </div>
                <button type="button" class="dz-remove" id="dzRemove" title="Hapus file">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </label>
        <div class="dz-error" id="dzError" hidden></div>
    </div>

    <div class="pay-field">
        <label for="catatan">Catatan / Nama Rekening Pengirim <span class="opt">(opsional)</span></label>
        <input class="pay-input" type="text" id="catatan" name="catatan" maxlength="150"
            placeholder="Contoh: Transfer dari DANA a.n Ahmad">
    </div>

    <button type="submit" class="pay-submit">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="m22 2-7 20-4-9-9-4Z" />
            <path d="M22 2 11 13" />
        </svg>
        Kirim Konfirmasi Pembayaran
    </button>
</form>

<!-- ============ JS ============ -->
<script>
    (function () {
        var tampil = document.getElementById('jumlahTampil');
        var hidden = document.getElementById('jumlah');
        function fmt(n) { return n.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
        tampil.addEventListener('input', function () {
            var raw = tampil.value.replace(/\D/g, '').slice(0, 9);
            hidden.value = raw;
            tampil.value = raw ? fmt(raw) : '';
        });
        hidden.value = tampil.value.replace(/\D/g, '');
        tampil.value = hidden.value ? fmt(hidden.value) : '';

        var input = document.getElementById('bukti');
        var zone = document.getElementById('dropzone');
        var idle = document.getElementById('dzIdle');
        var prev = document.getElementById('dzPreview');
        var img = document.getElementById('dzImg');
        var nm = document.getElementById('dzName');
        var sz = document.getElementById('dzSize');
        var err = document.getElementById('dzError');
        var MAX = 5 * 1024 * 1024;
        var OK = ['image/jpeg', 'image/png', 'image/webp'];

        function humanSize(b) {
            return b >= 1048576 ? (b / 1048576).toFixed(2) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB';
        }
        function handleFile(f) {
            err.hidden = true;
            if (!f) return;
            if (OK.indexOf(f.type) === -1) { err.textContent = 'Format harus JPG, PNG, atau WEBP.'; err.hidden = false; input.value = ''; return; }
            if (f.size > MAX) { err.textContent = 'Ukuran maksimal 5 MB.'; err.hidden = false; input.value = ''; return; }
            nm.textContent = f.name;
            sz.textContent = humanSize(f.size);
            img.src = URL.createObjectURL(f);
            idle.hidden = true;
            prev.hidden = false;
        }

        input.addEventListener('change', function () { handleFile(input.files[0]); });

        document.getElementById('dzRemove').addEventListener('click', function (e) {
            e.preventDefault(); e.stopPropagation();
            input.value = '';
            prev.hidden = true;
            idle.hidden = false;
            err.hidden = true;
        });

        ['dragenter', 'dragover'].forEach(function (ev) {
            zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('drag'); });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.remove('drag'); });
        });
        zone.addEventListener('drop', function (e) {
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                handleFile(input.files[0]);
            }
        });
    })();
</script>

<?php include '../partials/footer.php'; ?>
