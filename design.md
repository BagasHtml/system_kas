# Design System & UI Specification: Lessa CMS Dashboard Design

**Designer:** Fitra Purwaka  
**Source:** [Dribbble - Lessa CMS Dashboard Design](https://dribbble.com/shots/21553922-Lessa-Cms-Dashboard-Design)  
**Dokumen Version:** 1.0.0  

---

## 1. Overview & Brand Identity

* **Nama Produk:** Lessa (CMS & E-Commerce Admin Dashboard)
* **Visual Style:** Soft Neumorphic / Elevated Cards, Minimalis Modern, High-Contrast Accent
* **Theme Mode:** Light Mode (Dominasi latar putih & abu-abu netral dengan aksen hijau emerald dan oranye hangat)
* **Karakter UI:** Lengkungan sudut yang lebar (*large border radius*), *ambient shadow* yang lembut, hirarki visual berpusat pada keterbacaan data metrik.

---

## 2. Design Tokens & Color Palette

### A. Primary Colors (Emerald Green)
* **Primary Main (`--color-primary`):** `#00A37A` (Digunakan pada tombol utama, navigasi aktif, banner statistik utama, dan garis grafik)
* **Primary Hover (`--color-primary-dark`):** `#008062`
* **Primary Soft (`--color-primary-light`):** `#E6F5F1` (Background badge, status aktif sekunder)

### B. Accent Colors (Warm Orange)
* **Accent Main (`--color-accent`):** `#FF8C32` (Digunakan pada logo mark, banner promo CTA "Need more information?")
* **Accent Dark (`--color-accent-dark`):** `#E0751A`

### C. Neutral & Surface Colors
* **Canvas Background (`--color-bg-canvas`):** `#F4F5F7` (Background dasar seluruh dashboard)
* **Card Surface (`--color-bg-surface`):** `#FFFFFF` (Latar belakang container, card, dan sidebar)
* **Text Primary (`--color-text-primary`):** `#1A1D1E` (Judul utama, angka metrik besar)
* **Text Secondary (`--color-text-secondary`):** `#8A94A6` (Label menu, subtitle, teks keterangan)
* **Border & Line (`--color-border`):** `#EAECEF`

### D. Indicator & Feedback Colors
* **Success Trend (`--color-success`):** `#00A37A` (Indikator kenaikan persentase `+23.22%`)
* **Danger Trend (`--color-danger`):** `#EB5757` (Indikator penurunan persentase `-3.31%`)

---

## 3. Typography Architecture

* **Font Family:** `Plus Jakarta Sans`, `Inter`, atau `Outfit` (Sans-Serif Modern)

| Scale Level | Font Size | Weight | Line Height | Usage Context |
| :--- | :--- | :--- | :--- | :--- |
| **Display / H1** | `24px` | Bold (`700`) | `130%` | Greeting header ("Good morning Liz 👋") |
| **Heading 2** | `20px` | Bold (`700`) | `130%` | Section title ("Dashboard", "Summary Revenue") |
| **Heading 3** | `16px` | SemiBold (`600`) | `140%` | Card titles, banner headings |
| **Stat Display** | `22px` - `28px` | Bold (`700`) | `120%` | Angka metrik ("321k", "678k", "7,89", "211k") |
| **Body Base** | `14px` | Medium (`500`) | `150%` | Navigasi menu, isi tombol, nama produk |
| **Caption / Small**| `12px` | Regular (`400`) | `140%` | Date filter, subtitle ("Last update last week") |

---

## 4. Spacing, Elevation & Corner Radius

### Corner Radius (Border Radius)
* **Small (`--radius-sm`):** `10px` - `12px` (Tombol biasa, pill tag, badge)
* **Medium (`--radius-md`):** `16px` - `20px` (Card biasa, item produk)
* **Large (`--radius-lg`):** `24px` (Main metric banner, container utama dashboard)
* **Full Pill (`--radius-full`):** `9999px` (Search bar, avatar frame, status indicator)

### Shadows & Elevation
* **Card Elevation:** `0px 10px 30px rgba(0, 0, 0, 0.04)`
* **Active Emerald Glow:** `0px 8px 20px rgba(0, 163, 122, 0.25)`
* **Orange Banner Glow:** `0px 8px 20px rgba(255, 140, 50, 0.20)`

---

## 5. Layout Grid & Structure

### Structure Breakdown
* **Sidebar Navigation:** Fixed width `240px` (Left fixed)
* **Main Container:** Fluid flex/grid dengan internal padding `28px` - `32px`

### Dashboard Content Area Architecture
1. **Top Header Row:**
   * Left: Greeting Title & Subtitle.
   * Right: Profile status pill, balance tag (`Your balance $566.55`), date filter (`This year`), & CTA Button (`Download report`).
2. **Top Metric Section:**
   * Single integrated banner card berwarna hijau emerald solid (`#00A37A`) terbagi menjadi 4 grid kolom sejajar (Sales, Visitor, Cvr, Orders).
3. **Main Content Grid (2 Columns):**
   * **Left Column (65% width):** Card Grafik `Summary Revenue` dengan statistik tren + Spline Line Chart.
   * **Right Column (35% width):** Stack vertikal berisi Promo Banner Oranye ("Need more information?") dan Carousel/List "Best Seller" produk.

---

## 6. Detailed Component Specifications

### A. Sidebar Component
* **Logo Block:** Box icon oranye bertuliskan `G` + Teks "lessa" (`18px Bold`).
* **Menu Group:**
  * Category Header: `"Menu"` (`11px`, Uppercase, color: `#8A94A6`).
  * **Active Item:** `Dashboard` (Background solid `#00A37A`, text putih, icon putih, pill radius).
  * **Inactive Items:** `Product`, `Analytics`, `Sale`, `Review`, `Chat` (Icon & teks abu-abu, chevron arrow indicator di sebelah kanan).
* **Sidebar Promo Widget (Bottom):**
  * 2 Card promo bertumpuk dengan *background image* interior/furniture dan overlay hijau.
  * Teks penawaran: *"Office furniture GET DISCOUNT 65%"* dan *55%*.

### B. Integrated Top Metric Bar
* **Container:** Background solid `#00A37A`, radius `24px`, padding `20px 24px`.
* **Grid Divider:** Garis vertikal tipis transparan (`rgba(255, 255, 255, 0.2)`) memisahkan antar kolom.
* **Kolom Detail:**
  1. **Total Sales:** Icon circular + Teks "Total sales" + Value `321k`
  2. **Visitor:** Icon circular + Teks "Visitor" + Value `678k`
  3. **Cvr:** Icon circular + Teks "Cvr" + Value `7,89`
  4. **Total Orders:** Icon circular + Teks "Total orders" + Value `211k`

### C. Revenue Summary Chart Card
* **Header Bar:**
  * Title: "Summary Revenue"
  * Subtitle: "Last update last week"
  * Trend Badges: `↑ 23,22%` (Hijau) dan `↓ 3,31%` (Merah).
* **Chart Area:**
  * Type: Smooth Spline / Curvaceous Line Chart.
  * Line Color: Green Mint (`#00A37A`) dengan ketebalan line `3px`.
  * Grid Lines: Horizontal dashed lines transparan.
  * Axis X: Tanggal (`2 Dec`, `3 Dec`, `4 Dec`, `5 Dec` [Active State], `6 Dec`, `7 Dec`).
  * Axis Y: Range nilai (`10`, `20`, `30`, `40`, `50`, `60`, `70`).

### D. Promotional Banner (Right Column)
* **Background:** Gradient Warm Orange (`#FF8C32` ke `#F78B2A`) dengan gambar latar belakang interior kantor.
* **Content:**
  * Title: "Need more information?" (White, `18px Bold`)
  * Body: "Present information in a visually appealing way" (White soft, `12px`)
  * CTA Button: Rounded White Pill Button `"See more >"` (Text `#FF8C32`, `12px Bold`).

### E. Best Seller Furniture Section
* **Header:** Title "Best Seller" + Navigation Arrow (`>`).
* **Card Grid (3 Cards Horizontal):**
  * Card item individual warna putih (*soft elevation*).
  * Item 1: `Wooden Chair` | Rating `4.8/5 ★`
  * Item 2: `Dining Chair` | Rating `4.8/5 ★`
  * Item 3: `Eames Chairs` | Rating `4.8/5 ★`

---

## 7. Responsiveness & Breakpoints

* **Desktop Wide (> 1440px):** Layout persis seperti desain asli (Full 2-column dashboard).
* **Desktop Standard (1024px - 1439px):**
  * Sidebar menyusut (*collapsed*) menjadi icon-only (`80px`).
  * Grid kanan & kiri menyesuaikan proporsi (`60%` : `40%`).
* **Tablet (768px - 1023px):**
  * Top Metric Bar berubah menjadi Grid 2x2.
  * Layout utama berubah menjadi **Single Column** (Summary Revenue Chart di atas, Promo Banner & Best Seller di bawahnya).
* **Mobile (< 767px):**
  * Navigation menggunakan Bottom Navigation Bar atau Hamburger Drawer Menu.
  * Top Metric Bar bertumpuk vertikal / horizontal scroll view.

  ---

## 4. Rincian Komponen Baru yang Disarankan

### Widget 1: Riwayat Pembayaran Saya (*Personal History*)
* **Posisi:** Di bawah box QRIS & Send Dana (mengisi ruang kosong kiri bawah).
* **Komponen:**
  * Header: `Riwayat Pembayaran Saya` + Badge `3 Transaksi`.
  * Item List (Tabel / Card List Ringkas):
    * Bulan (misal: *Agustus 2026*)
    * Nominal (*Rp 5.000*)
    * Metode (*QRIS / Send Dana / Cash*)
    * Status Badge (*Lunas - Hijau*)
  * Action: Button link `Lihat Semua Riwayat >`.

### Widget 2: Transparency Widget / Pengeluaran Terakhir (*Class Expenses*)
* **Posisi:** Menggantikan/memperbaiki banner orange "Uang kas dipakai untuk apa?" di kanan bawah.
* **Komponen:**
  * Header: `Pengeluaran Kas Terakhir`
  * Total Terpakai: `Total Pengeluaran: Rp 120.000`
  * Mini List (3 Item):
    * 🖊️ *Beli Spidol & Penghapus Boardmaker* — `Rp 15.000` (2 Agu)
    * 🧹 *Alat Kebersihan Kelas* — `Rp 35.000` (28 Jul)
  * CTA Button: Button solid/outline `Cek Rincian Pengeluaran Lengkap`.

### Widget 3: Status Konfirmasi / Upload Bukti Transfer (Opsional)
* **Posisi:** Di samping/di bawah QRIS.
* **Fungsi:** Jika siswa sudah melakukan transfer via QRIS, sediakan form/button upload bukti transfer (*Konfirmasi Pembayaran*) jika sistem belum otomatis terintegrasi dengan payment gateway/mutation webhook.

---

## 5. Layout Grid & Structural Updates

1. **Atur Alignment Container Utama:**
   * Ubah `max-width` wrapper dari fixed centered ke `1280px` atau tambahkan **Sidebar Navigation Panel** fixed di sebelah kiri (`width: 240px`).
2. **Equal Height Grid:**
   * Pastikan kolom kiri (*Bayar Kas + Riwayat Saya*) dan kolom kanan (*Target Kas + Pengeluaran*) menggunakan `grid-template-rows` atau Flexbox `align-items: stretch` agar ujung bawah card kanan dan kiri selalu sejajar presisi (*seamless bottom edge*).
"""

## Ringkasan Waframe Baru

+-------------------------------------------------------------------------------------------------------+
| SIDEBAR (NEW) | HEADER: Hai, MUHAMMAD DAFYAN LESMANA                       [Pengeluaran] [Keluar]     |
|               +---------------------------------------------------------------------------------------+
| • Dashboard   | [ METRIC 1: Rp 15.000 ]  [ METRIC 2: Rp 0 ]  [ METRIC 3: 3 Bulan ]                    |
| • Riwayat Saya+---------------------------------------------------+-----------------------------------|
| • Laporan Kas | BAYAR KAS ONLINE (Send Dana & QRIS)               | TARGET KAS KELAS                  |
| • Pengeluaran |                                                   | Progress: 100%                    |
|               +---------------------------------------------------+-----------------------------------|
|               | (NEW WIDGET 1)                                    | (NEW WIDGET 2)                    |
|               | RIWAYAT PEMBAYARAN SAYA                           | RINGKASAN PENGELUARAN KELAS       |
|               | • Agu 2026 - Rp 5.000 (Lunas - QRIS)              | • Beli Spidol & Penghapus (15k)   |
|               | • Jul 2026 - Rp 5.000 (Lunas - Transfer)          | • Alat Kebersihan Kelas (35k)     |
|               | • Jun 2026 - Rp 5.000 (Lunas - Cash)              | [Lihat Semua Pengeluaran >]       |
+---------------+---------------------------------------------------+-----------------------------------+