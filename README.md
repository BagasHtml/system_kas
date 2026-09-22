# KasKu — Sistem Kas Kelas Digital

Aplikasi web sederhana untuk mencatat pemasukan (iuran siswa), pengeluaran, dan laporan kas kelas secara transparan. Dibangun dengan PHP murni (native, tanpa framework) + MySQL dan antarmuka Tailwind CSS.

## Fitur

- **Mode Siswa** — lihat status kontribusi per bulan, riwayat setoran, target belanja, dan cetak struk.
- **Mode Bendahara** — dashboard pemasukan vs pengeluaran (grafik 6 bulan), kelola pembayaran, konfirmasi setoran, kelola target belanja, laporan, dan ekspor Excel.
- **Upload bukti setoran** — pembayaran via DANA/QRIS wajib melampirkan bukti transfer; tunai langsung tanpa bukti.
- **Serba otomatis** — pembuatan periode bulanan otomatis saat bulan berganti, struktur organisasi kelas (bendahara/wakil/seksi), konfigurasi QRIS & nomor DANA pada halaman Pengaturan.
- **Buku panduan** — halaman manualbook untuk siswa dan bendahara di dalam aplikasi.
- **Data riwayat (seeder)** — isi bulan-bulan sebelumnya dengan data dummy agar grafik terisi penuh.

## Tech Stack

- PHP 8+ (native, tanpa framework)
- MySQL/MariaDB (XAMPP / Lampp)
- Tailwind CSS (via CDN) + Lucide icons + Bootstrap Icons

## Struktur Folder

```
├── index.php                 # Landing page (publik)
├── app/controllers/          # Controller per modul
├── function/                 # Entry untuk login siswa & admin
├── views/
│   ├── partials/             # header, footer, sidebar, helper
│   ├── admin/                # dashboard, belanja, laporan, akun, dsb.
│   └── siswa/                # dashboard, struk, belanja, manualbook
├── database/
│   ├── db.php                # koneksi + pemanggilan migrasi
│   ├── schema.sql            # skema database
│   ├── migrate_*.php         # migrasi idempotent (auto-jalan)
│   └── seed_riwayat.php      # seeder data riwayat (CLI)
├── assets/                   # css, js, uploads
└── tests/smoke.php           # smoke test otomatis
```

## Cara Menjalankan

1. Salin folder project ke `htdocs` (XAMPP/LaMPP): `/opt/lampp/htdocs/system_kas`.
2. Nyalakan Apache + MySQL.
3. Import `database/schema.sql` ke database (nama db: `db_kas_kelas`).
4. Impor seeder awal (opsional):
   ```bash
   mysql -u root db_kas_kelas < database/seeder_siswa.sql
   mysql -u root db_kas_kelas < database/seeder_target.sql
   mysql -u root db_kas_kelas < database/seeder_pembayaran.sql
   ```
5. Konfigurasi `.env` di folder `database/` (host `localhost` untuk server web; gunakan `127.0.0.1` saat diakses via CLI PHP).
6. Buka `http://localhost/system_kas`.

> Kolom migrasi (`migrate_v2`, `migrate_periode`, `migrate_belanja`, dsb.) otomatis diterapkan setiap kali `database/db.php` di-load, jadi tabel baru ikut tercipta sendiri.

## Login

| Peran | Cara masuk |
|---|---|
| **Siswa** | Masukkan nama + nomor absen di landing / halaman login siswa |
| **Bendahara** | Login nama + nomor absen siswa yang ber-role bendahara, lalu *login bendahara* dengan username & password |
| User bawaan | `username: admin` / `password: admin123` |

## Seeder Data Riwayat

Agar grafik 6 bulan terisi data bulan-bulan sebelumnya (dummy, semua siswa kontribusi sama rata):

```bash
cd /opt/lampp/htdocs/system_kas
KAS_DB_HOST=127.0.0.1 php database/seed_riwayat.php
```

Aman dijalankan berulang: data `(periode, siswa_id)` yang sudah lunas tidak digandakan.

## Uji Cepat

```bash
KAS_DB_HOST=127.0.0.1 php tests/smoke.php
```

## Lisensi

Proyek pembelajaran — silakan diulik dan dikembangkan lebih lanjut.