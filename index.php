<?php $title = 'Sistem Kas Kelas'; ?>
<?php include 'views/partials/header.php'; ?>
<script>document.documentElement.classList.add('js');</script>

<nav class="lp-nav">
    <div class="lp-nav-inner">
        <a href="#top" class="lp-brand">
            <span class="lp-brand-mark">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
            </span>
            Sistem Kas Kelas
        </a>
        <div class="lp-nav-links">
            <a href="#tentang">Tentang</a>
            <a href="#dasbor">Dasbor</a>
            <a href="#integrasi">Integrasi</a>
            <a href="#masuk" class="lp-btn lp-btn-dark" style="padding:8px 16px;">Masuk</a>
        </div>
    </div>
</nav>

<main id="top">

    <section class="lp-hero lp-container rv">
        <h1 class="lp-h1">Kas kelas dicatat rapi,<br>transparan ke <em>semua orang</em>.</h1>
        <p class="lp-lead">
            Sistem Kas Kelas membantu pengurus mencatat iuran tiap bulan, melacak siapa yang
            sudah atau belum bayar, dan menampilkan pengeluaran kas secara terbuka —
            ke guru, pengurus, dan seluruh siswa.
        </p>
        <div class="lp-hero-cta">
            <a href="views/siswa/index.php" class="lp-btn lp-btn-green">Masuk sebagai Siswa</a>
            <a href="views/admin/login.php" class="lp-btn lp-btn-dark">Masuk Admin</a>
        </div>
        <p class="lp-hero-note">Akun demo admin: admin / admin123 &middot; Siswa demo: Ahmad Fauzi, absen 1</p>
    </section>

    <section id="tentang" class="lp-section">
        <div class="lp-container">
            <h2 class="lp-h2 rv">Dibuat buat apa?</h2>
            <p class="lp-sec-lead rv">Iuran kas itu hal kecil, tapi kalau dicatat asal-asalan, bisa jadi rebutan. Tiga hal ini yang diberesin:</p>

            <div class="lp-points">
                <article class="lp-point rv">
                    <div class="lp-point-num">01</div>
                    <h3>Catat iuran per bulan</h3>
                    <p>Pembayaran kas dicatat per bulan. Status terkumpul atau belum terlihat per siswa, lengkap dengan tanggal bayarnya.</p>
                </article>
                <article class="lp-point rv">
                    <div class="lp-point-num">02</div>
                    <h3>Pengeluaran yang terbuka</h3>
                    <p>Setiap rupiah yang keluar tampil di dasbor siswa. Siswa tidak perlu bertanya ke pengurus lagi.</p>
                </article>
                <article class="lp-point rv">
                    <div class="lp-point-num">03</div>
                    <h3>Laporan ringkas</h3>
                    <p>Total masuk, total keluar, dan sisa saldo dihitung otomatis. Siap dicetak per bulan.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- SECTION DASBOR: IFRAME DIGANTI GAMBAR STATIS -->
    <section id="dasbor" class="lp-section" style="background:#fcfbf8;border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
        <div class="lp-container">
            <h2 class="lp-h2 rv">Lihat langsung dasbornya.</h2>
            <p class="lp-sec-lead rv">Ini bukan screenshot mentah — tiap preview di bawah diambil langsung dari halaman asli aplikasi.</p>

            <div class="preview-stack">
                <div class="preview rv" data-base="1180" data-height="720">
                    <div class="preview-bar">
                        <div class="preview-dots"><span></span><span></span><span></span></div>
                        <div class="preview-url">localhost/kas_system/views/admin/dashboard.php</div>
                    </div>
                    <div class="preview-screen">
                        <img
                            src="<?= BASE_URL ?>/assets/img/preview-admin.webp"
                            alt="Preview dasbor admin: ringkasan pemasukan, grafik, dan daftar pembayaran terakhir"
                            width="1180"
                            height="720"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                    <div class="preview-cap">
                        <h3>Dasbor Admin</h3>
                        <p>Ringkasan pemasukan dan saldo, grafik masuk-keluar, serta daftar pembayaran terakhir. Data siswa, pembayaran, dan pengeluaran bisa dicari langsung tanpa scroll.</p>
                    </div>
                </div>

                <div class="preview rv" data-base="1180" data-height="720">
                    <div class="preview-bar">
                        <div class="preview-dots"><span></span><span></span><span></span></div>
                        <div class="preview-url">localhost/kas_system/views/siswa/dashboard.php</div>
                    </div>
                    <div class="preview-screen">
                        <img
                            src="<?= BASE_URL ?>/assets/img/preview-siswa.webp"
                            alt="Preview dasbor siswa: status pembayaran pribadi dan daftar pengeluaran kelas"
                            width="1180"
                            height="720"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                    <div class="preview-cap">
                        <h3>Dasbor Siswa</h3>
                        <p>Status pembayaran pribadi per bulan, sisa saldo kas, dan daftar pengeluaran kelas — semua tampil apa adanya.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="integrasi" class="lp-section">
        <div class="lp-container">
            <h2 class="lp-h2 rv">Integrasi &amp; cara kerja</h2>
            <p class="lp-sec-lead rv">Metode bayar yang dipakai sehari-hari, dan data yang tetap di perangkat sendiri.</p>

            <div class="lp-integ">
                <div class="lp-integ-list rv">
                    <div class="lp-integ-item">
                        <div class="lp-integ-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/></svg>
                        </div>
                        <div>
                            <h3>Send Dana</h3>
                            <p>Bayar kas lewat transfer Dana ke nomor bendahara <code>0813-2175-0459</code>.</p>
                        </div>
                    </div>
                    <div class="lp-integ-item">
                        <div class="lp-integ-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h4v4h-4z"/></svg>
                        </div>
                        <div>
                            <h3>QRIS</h3>
                            <p>Scan dari e-wallet atau aplikasi bank mana pun, pembayaran langsung tercatat.</p>
                        </div>
                    </div>
                    <div class="lp-integ-item">
                        <div class="lp-integ-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                        </div>
                        <div>
                            <h3>Self-hosted</h3>
                            <p>Semua aset dan data berjalan di server sendiri, tanpa CDN atau layanan luar.</p>
                        </div>
                    </div>
                </div>

                <div class="preview rv" data-base="1180" data-height="560">
                    <div class="preview-bar">
                        <div class="preview-dots"><span></span><span></span><span></span></div>
                        <div class="preview-url">localhost/kas_system/views/siswa/_preview_bayar.php</div>
                    </div>
                    <div class="preview-screen">
                        <img
                            src="<?= BASE_URL ?>/assets/img/preview-bayar.webp"
                            alt="Preview halaman bayar kas online via Dana dan QRIS"
                            width="1180"
                            height="560"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="masuk" class="lp-section" style="background:#fcfbf8;border-top:1px solid var(--border);">
        <div class="lp-container">
            <h2 class="lp-h2 rv">Masuk</h2>
            <p class="lp-sec-lead rv">Pilih peran kamu.</p>

            <div class="lp-cta-grid">
                <div class="lp-cta-card rv">
                    <h3>Masuk sebagai Siswa</h3>
                    <p>Cukup isi nama dan nomor absen. Lihat status pembayaran dan pengeluaran kas kelas.</p>
                    <a href="views/siswa/index.php" class="lp-btn lp-btn-green">Masuk Siswa</a>
                </div>
                <div class="lp-cta-card rv">
                    <h3>Masuk Admin</h3>
                    <p>Kelola data siswa, catat pembayaran, atur pengeluaran, dan cetak laporan.</p>
                    <a href="views/admin/login.php" class="lp-btn lp-btn-dark">Masuk Admin</a>
                </div>
            </div>
        </div>
    </section>

</main>

<footer class="lp-footer">
    <div class="lp-footer-inner">
        <a href="#top" class="lp-brand">
            <span class="lp-brand-mark">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
            </span>
            Sistem Kas Kelas
        </a>
        <span>Admin demo: admin / admin123</span>
        <a href="#top">Kembali ke atas &uarr;</a>
    </div>
</footer>

<!-- TAMBAHKAN INI DI FILE CSS UTAMA ANDA -->
<style>
    /* Performance: isolasi rendering preview dari layout utama */
    .preview-screen {
        contain: strict;
        overflow: hidden;
        background: #f5f5f0;
        border-radius: 0 0 8px 8px;
    }

    .preview-screen img {
        display: block;
        width: 100%;
        height: auto;
    }

    .rv {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
    }
</style>

<script>
(function () {
    'use strict';

    // Anchor scroll dengan offset navbar (tanpa animasi lambat/lenis)
    var NAV_OFFSET = 76;
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var hash = a.getAttribute('href');
            if (!hash || hash.length < 2) return;
            var el = document.querySelector(hash);
            if (!el) return;
            e.preventDefault();
            var elementPosition = el.getBoundingClientRect().top + window.pageYOffset;
            window.scrollTo({
                top: elementPosition - NAV_OFFSET,
                behavior: 'auto'
            });
        });
    });

    // Nav shadow on scroll
    var nav = document.querySelector('.lp-nav');
    function onScroll() {
        if (nav) {
            nav.classList.toggle('scrolled', (window.scrollY || window.pageYOffset) > 8);
        }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

})();
</script>

<?php include 'views/partials/footer.php'; ?>