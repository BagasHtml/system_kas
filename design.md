# Design System & UI Specification: Sistem Kas Kelas

**Inspirasi:** [Lessa CMS Dashboard Design (Dribbble)](https://dribbble.com/shots/21553922-Lessa-Cms-Dashboard-Design)  
**Dokumen Version:** 2.0.0  
**Status:** Terimplementasi (CSS di `assets/css/style.css`, halaman: `views/admin/*`, `views/siswa/*`)

---

## 1. Overview & Brand Identity

* **Produk:** Sistem Kas Kelas (dashboard admin bendahara + siswa)
* **Visual Style:** Soft Elevated Cards, Minimalis Modern, High-Contrast Accent
* **Theme Mode:** Light Mode (canvas abu-abu `#F4F5F7` dengan aksen hijau emerald dan oranye hangat)
* **Karakter UI:** *Large border radius*, *ambient shadow* lembut, hirarki visual berpusat pada keterbacaan metrik keuangan.
* **Bahasa UI:** Bahasa Indonesia, istilah "Periode" ditampilkan sebagai **"Bulan"** (kolom DB / param URL tetap `periode`).

---

## 2. Design Tokens & Color Palette (CSS `:root`)

### A. Primary (Emerald Green)
| Token | Value | Penggunaan |
| :--- | :--- | :--- |
| `--accent` | `#00A37A` | Banner utama, tombol utama, nav aktif, garis grafik, donut, progress |
| `--accent-dark` | `#008062` | Hover tombol primary |
| `--accent-soft` | `#E6F5F1` | Badge/bg status sekunder, avatar cell |
| `--success` | `#00A37A` | Nilai "Terkumpul", trend naik |
| `--success-bg` | `#E6F5F1` | Pill status "Lunas" |

### B. Accent (Warm Orange)
| Token | Value | Penggunaan |
| :--- | :--- | :--- |
| `--yellow` / `--warning` | `#FF8C32` | Promo banner, logo mark, nilai "Belum" di donut |
| `--yellow-bg` / `--warn-bg` | `#FFF1E6` | Pill status menunggu/warning |

### C. Neutral & Surface
| Token | Value | Penggunaan |
| :--- | :--- | :--- |
| `--body-bg` / canvas | `#F4F5F7` | Background seluruh dashboard |
| `--card-bg` / surface | `#FFFFFF` | Card, sidebar |
| `--text-primary` | `#1A1D1E` | Judul, angka metrik besar |
| `--text-secondary` | `#8A94A6` | Label menu, subtitle |
| `--text-muted` | `#A3ACB8` | Caption, nomor urut tabel |
| `--border` | `#EAECEF` | Border card/garis pemisah |

### D. Feedback Colors
* **Danger (`--danger`):** `#EB5757` / bg `#FDEBEA` — nilai "Belum Terkumpul", trend turun, tombol keluar.
* **Gradient Logo / Promo:** `linear-gradient(135deg, #FF9A45, #FF8C32)`.

---

## 3. Typography Architecture

* **Font Family:** `Inter` (fallback system sans-serif). Body base `13px`.

| Scale Level | Font Size | Weight | Usage Context |
| :--- | :--- | :--- | :--- |
| **Stat Display** | `24px` | 800 | Nilai banner (`.dash-banner-value`) |
| **Saldo Big** | `28px` | 800 | Saldo besar di Ringkasan Saldo |
| **Page Title** | `22px` | 700 | Judul halaman (`.dash-title`) |
| **Card Title** | `15px` | 700 | `.dash-card-title` |
| **Body / Nav** | `13px` | 500–600 | Menu, isi tombol, isi card |
| **Label / Caption** | `11px` | 400–700 | `.dash-banner-label`, `.dash-card-sub`, badge |

---

## 4. Spacing, Elevation & Corner Radius

### Corner Radius
* **Pill (`999px`):** tombol pill, badge status, avatar.
* **Medium (`14px`–`16px`):** card, menu item, progress track.
* **Large (`20px`–`24px`):** banner emerald, promo orange, donut container.
* **X-Large (`28px`):** `main-content` corner (jika sidebar admin).

### Shadows & Elevation
* **Card:** `0 2px 8px rgba(0,0,0,0.06)` (ringan) s.d. `0 10px 30px rgba(0,0,0,0.04)`.
* **Active Emerald Glow:** `0 8px 20px rgba(0, 163, 122, 0.25)` (nav aktif, banner).
* **Orange Banner Glow:** `0 8px 20px rgba(255, 140, 50, 0.22)` (promo).
* **Logo Glow:** `0 8px 20px rgba(255, 140, 50, 0.28)`.

---

## 5. Layout Grid & Structure

### Sidebar (Admin)
* Fixed kiri, dua panel: **action bar** `60px` + **nav** `220px` (total `280px`).
* Action bar: logo orange gradient, badge class, tombol aksi icon.
* Nav: brand "Kas Kelas", view-switcher pill, nav link (icon + label, radius `12px`), **active = solid emerald pill putih**.
* Bottom: tombol Keluar dipisah border-top.

### Halaman Dashboard (Admin `views/admin/dashboard.php`)
1. **Top Bar:** kiri = judul + subtitle; kanan = date-chip + avatar inisial.
2. **Banner Statistik (`.dash-banner`, default 4 kolom):** card emerald solid, 4 kolom (Total Pemasukan, Total Siswa, Total Pengeluaran, Saldo Kas), pemisah garis putih transparan.
3. **Row 2 (`.dash-charts` = grid 2 kolom, kiri 1.15fr / kanan 1fr):**
   * Kiri: **Pemasukan per Bulan** — SVG smooth spline chart (Catmull-Rom → Bezier, line `#00A37A` 3px, area gradient, grid dashed, dot tooltip) + trend badge bulan ini vs bulan lalu.
   * Kanan (`.dash-stack`): **Status Pembayaran** (donut + legend + progress) + **Promo Oranye** ("Butuh laporan lengkap?").
4. **Row 3 (`.dash-charts`):**
   * Kiri: **Pembayaran Terakhir** — tabel (Siswa w/ avatar inisial + absen, Bulan, Tanggal, Status pill, Jumlah).
   * Kanan: **Ringkasan Saldo** — saldo besar, progress, list breakdown (Pemasukan, Kesepakatan, Pengeluaran, Rata-rata/siswa), tombol `Kelola Pengeluaran` full-width.

### Halaman Siswa (`views/siswa/dashboard.php`)
1. **Top Bar:** greeting + nomor absen; kanan = tombol `Pengeluaran Uang Kas kelas` (light) + `Keluar` (danger).
2. **Banner (`.dash-banner.cols-3`):** 3 kolom (Total Dibayar, Belum Terkumpul, Bulan Terkumpul).
3. **Row 2 (`.dash-charts`):**
   * Kiri: **Bayar Kas Online** — split 2 method: Send Dana (ikon paper-plane, rincian nomor/atas nama/bulan) + QRIS (gambar qr + hint).
   * Kanan (`.dash-stack`): **Target Kas Kelas** (list breakdown + progress + notice) + **Promo Oranye** ("Uang kas dipakai untuk apa?").
4. **Row 3 (`.dash-charts`):**
   * Kiri: **Riwayat Pembayaran** — tabel (No, Bulan, Jumlah, Tanggal, Status).
   * Kanan: **Progres Pembayaran** — donut pribadi + legend, progress, breakdown Total Tagihan/Sudah/Belum.

---

## 6. Detailed Component Specifications

### A. Banner Statistik (`.dash-banner`)
* Background solid emerald `#00A37A`, radius `24px`, padding `20px 24px`.
* Grid: `repeat(auto-fit, minmax(180px, 1fr))`; `.cols-3` = `repeat(3, 1fr)`.
* Kolom: icon dalam lingkaran semi-transparan putih, label `11px` putih 60%, value `24px/800`, note `11px` putih 70%.
* Pemisah antar kolom: `border-left: 1px solid rgba(255,255,255,0.2)`.

### B. Card (`.dash-card`)
* Surface putih, radius `16px`, padding `20px`, shadow halus `0 2px 8px rgba(0,0,0,0.06)`.
* Header (`.dash-card-head`): flex; kiri title `15px/700` + sub `11px` secondary; kanan aksi (`.dash-btn`, `.dash-year-label`, `.dash-trends`).

### C. SVG Line Chart (`.dash-chart-svg`)
* Path emerald `#00A37A`, stroke-width `3px`, smooth curve.
* Area fill: `linearGradient` `#00A37A` opacity `0.22 → 0.02`.
* Grid: `.dash-grid-line` dashed `#EAECEF`; label Y (`.dash-y-label`)/X (`.dash-x-label`) `10px` muted.
* Data point: `.dash-dot` lingkaran kecil emerald dengan `<title>` tooltip.
* State kosong: `.dash-empty-note` centered.

### D. Donut Chart (`.dash-donut`)
* Conic-gradient, CSS var `--p` persen, `--c1` accent `#00A37A`, `--c2` yellow `#FF8C32`.
* Pusat: persentase `20px/800`. Dampingan `.dash-legend` item: swatch, name+sub, value.
* Layout `.dash-donut-wrap`: flex, gap `16px`.

### E. Progress Bar (`.dash-progress`)
* `.top` = flex label + persen `12px/700`; `.track` radius pill `#EAECEF`; `.fill` emerald, radius pill, transition.

### F. Promo Banner Oranye (`.dash-promo`)
* Gradient `#FF8C32 → #F78B2A`, radius `20px`, padding `22px`, text putih, glow orange.
* `h4` `16px/700`, `p` `12px` putih 85%.
* CTA `.dash-promo-btn`: pill putih, text oranye `12px/700`, chevron icon.

### G. Tabel (`.dash-table`)
* Wrap `.dash-table-wrap` scroll horizontal (`min-width: 560px` pada mobile).
* `thead` `11px` uppercase muted; baris `border-top: 1px solid var(--border)`.
* Cell nama siswa: `.dash-cell-name` avatar inisial `32px` pill + nama `13px/600` + absen `11px` muted.
* Jumlah: `.dash-amount` `13px/700`. Status: `status_pill()` → pill `Lunas` hijau soft / `Belum` orange soft / lainnya.

### H. Tombol (`.dash-btn`)
* Pill radius, `13px/700`, gap icon.
* `--primary`: solid emerald, putih, glow emerald. `--light`: putih border `#EAECEF`. `--danger`: soft merah (`#FDEBEA` bg, `#EB5757` text).

### I. Bayar Online (`.dash-pay`)
* `.dash-pay`: grid 2 kolom (`1fr 220px`), gap.
* `.dash-pay-brand`: pill icon + label; varian `.dana` (biru `#066DE8`) dan `.qris` (putih + border).
* `.dash-pay-info`: baris label/value (`.dash-pay-row`); `.val.num` = tabular number.
* `.dash-pay-qris`: img putih padding, radius. `.dash-pay-hint`: `11px` muted centered.

### J. Empty & Notice
* `.dash-empty`: centered; `.t` `14px/700`, `.s` `12px` secondary.
* `.dash-notice`: bg `var(--accent-soft)`, text emerald-dark, radius `12px`, padding `10px 12px`.

### K. Status Pill
* `.status-badge`/`status_pill()`: radius `999px`, `11px/700`, padding `3px 10px`.
  * `Lunas` → `--success-bg`/`--success`; `Belum` → `--warn-bg`/`--warning`.

---

## 7. Responsiveness & Breakpoints

* **Desktop (> 1100px):** `.dash-charts` 2 kolom; banner 3–4 kolom.
* **Tablet (≤ 1100px):**
  * `.dash-charts` → 1 kolom (tumpuk).
  * `.dash-banner` → `repeat(2, 1fr)`.
  * `.dash-kpis` → 2 kolom.
* **Mobile (≤ 700px):**
  * Padding page `20px 16px`; `.dash-banner` → 1 kolom (pemisah pindah ke `border-top`).
  * `.dash-topbar-actions` full-width, tombol `flex:1`; datechip & avatar disembunyikan.
  * `.dash-donut-wrap`, `.dash-chart-wrap` → kolom; `.dash-y-axis` hidden.
  * `.dash-pay` → 1 kolom (QRIS di bawah).
  * Tabel scroll horizontal (`min-width: 560px`).

---

## 8. Catatan Implementasi

* **"Bulan" vs "Periode":** UI menampilkan "Bulan"/"bulan" (helper `Koneksi::periodeLabel()`); nama kolom/param `periode` dipertahankan.
* **Nilai target:** bila target kas kelas ditetapkan, KPI "Belum Terkumpul" = sisa target (`max(0, target - terkumpul)`), progress dibatasi 100%.
* **Persen KPI:** `pct = min(100, round(collected/target*100))`.
* **Grafik admin:** data bulanan tahun berjalan (`MONTH(tanggal_bayar)`), trend dihitung bulan ini vs bulan lalu.
* **Donut siswa (pribadi):** `grand = lunas + belum`, `pct = lunas/grand*100`.
