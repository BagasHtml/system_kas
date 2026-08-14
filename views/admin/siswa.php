<!-- ============ CSS (bisa ditaruh di file CSS / <style> halaman) ============ -->
<style>
    .pay-card {
        max-width: 720px;
        margin: 24px auto;
        padding: 28px;
        background: var(--card, #fff);
        border: 1px solid var(--border, #e6eaf2);
        border-radius: 20px;
        box-shadow: 0 12px 40px rgba(20, 30, 55, .08);
    }

    .pay-head {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        margin-bottom: 24px;
    }

    .pay-head-icon {
        flex: 0 0 auto;
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: var(--accent-soft, rgba(10, 160, 110, .12));
        color: var(--accent, #0aa06e);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pay-head h2 {
        margin: 0 0 4px;
        font-size: 19px;
        font-weight: 700;
        color: var(--text, #1b2434);
    }

    .pay-head p {
        margin: 0;
        font-size: 13.5px;
        line-height: 1.55;
        color: var(--text-secondary, #5c6675);
    }

    .pay-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media(max-width:600px) {
        .pay-grid {
            grid-template-columns: 1fr;
        }
    }

    .pay-field {
        margin-bottom: 18px;
    }

    .pay-field>label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text, #1b2434);
    }

    .pay-field .opt {
        color: var(--text-muted, #9aa3b0);
        font-weight: 500;
    }

    .pay-input,
    .pay-select {
        width: 100%;
        padding: 12px 14px;
        outline: none;
        border: 1.5px solid var(--border, #e2e7ef);
        border-radius: 12px;
        background: #fbfcfe;
        font-size: 14px;
        color: var(--text, #1b2434);
        transition: border-color .15s, box-shadow .15s, background .15s;
    }

    .pay-input:focus,
    .pay-select:focus {
        border-color: var(--accent, #0aa06e);
        background: #fff;
        box-shadow: 0 0 0 4px var(--accent-soft, rgba(10, 160, 110, .14));
    }

    .pay-input::placeholder {
        color: var(--text-muted, #a3abb8);
    }

    .select-wrap {
        position: relative;
    }

    .select-wrap>svg {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: var(--text-muted);
    }

    .pay-select {
        appearance: none;
        -webkit-appearance: none;
        padding-right: 38px;
        cursor: pointer;
    }

    .amount-wrap {
        position: relative;
    }

    .amount-wrap .prefix {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 13px;
        font-weight: 700;
        color: var(--text-muted);
    }

    .amount-wrap .pay-input {
        padding-left: 44px;
        font-weight: 600;
    }

    /* ---- dropzone upload ---- */
    .dropzone {
        display: block;
        padding: 22px;
        text-align: center;
        cursor: pointer;
        border: 1.5px dashed #cfd6e2;
        border-radius: 14px;
        background: #fafbfe;
        transition: border-color .15s, background .15s;
    }

    .dropzone:hover,
    .dropzone.drag {
        border-color: var(--accent);
        background: var(--accent-soft, rgba(10, 160, 110, .08));
    }

    .dz-icon {
        width: 44px;
        height: 44px;
        margin: 0 auto 10px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid var(--border, #e2e7ef);
        color: var(--accent, #0aa06e);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(20, 30, 55, .06);
    }

    .dz-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text, #1b2434);
    }

    .dz-title span {
        color: var(--text-muted);
        font-weight: 500;
    }

    .dz-hint {
        margin-top: 4px;
        font-size: 12px;
        color: var(--text-muted);
    }

    .dz-preview {
        display: flex;
        align-items: center;
        gap: 12px;
        text-align: left;
    }

    .dz-preview img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid var(--border, #e2e7ef);
    }

    .dz-meta {
        flex: 1;
        min-width: 0;
    }

    .dz-name {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text, #1b2434);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dz-size {
        margin-top: 2px;
        font-size: 12px;
        color: var(--text-muted);
    }

    .dz-remove {
        flex: 0 0 auto;
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        background: #fdecec;
        color: var(--danger, #e5484d);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dz-remove:hover {
        background: #fbdcdc;
    }

    .dz-error {
        margin-top: 8px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--danger, #e5484d);
    }

    .pay-submit {
        width: 100%;
        padding: 14px 18px;
        border: none;
        border-radius: 13px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        background: linear-gradient(135deg, var(--accent, #0aa06e), #078a5e);
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        box-shadow: 0 8px 20px rgba(10, 160, 110, .28);
        transition: transform .15s, box-shadow .15s, filter .15s;
    }

    .pay-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(10, 160, 110, .34);
        filter: brightness(1.03);
    }

    .pay-submit:active {
        transform: translateY(0);
    }
</style>

<!-- ============ FORM ============ -->
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
        <div class="pay-field" style="margin-bottom:0;">
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
        <div class="pay-field" style="margin-bottom:0;">
            <label for="jumlahTampil">Jumlah yang ditransfer</label>
            <div class="amount-wrap">
                <span class="prefix">Rp</span>
                <input class="pay-input" type="text" id="jumlahTampil" inputmode="numeric" value="20000"
                    autocomplete="off">
                <input type="hidden" id="jumlah" name="jumlah" value="20000">
            </div>
        </div>
    </div>

    <div class="pay-field" style="margin-top:18px;">
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
        /* --- format ribuan pada jumlah (backend tetap terima angka polos) --- */
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

        /* --- dropzone upload --- */
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