<?php
/* Migrasi target per siswa: tambah kolom per_siswa pada target_kas (aman dijalankan ulang). */
include __DIR__ . '/db.php';

$r = Koneksi::q("SHOW COLUMNS FROM target_kas LIKE 'per_siswa'");
if ($r && $r->num_rows === 0) {
    if (Koneksi::q("ALTER TABLE target_kas ADD COLUMN per_siswa DECIMAL(12,2) DEFAULT NULL AFTER target")) {
        echo "Kolom per_siswa ditambahkan.\n";
    } else {
        echo "Gagal menambah kolom per_siswa.\n";
        exit(1);
    }
} else {
    echo "Kolom per_siswa sudah ada.\n";
}

$jml = (int)(Koneksi::q("SELECT COUNT(*) c FROM siswa")->fetch_assoc()['c'] ?? 0);
if ($jml > 0) {
    Koneksi::q("UPDATE target_kas SET per_siswa = ROUND(target / $jml) WHERE per_siswa IS NULL OR per_siswa <= 0");
    echo "Data lama dikonversi: per_siswa = target total / $jml siswa.\n";
} else {
    echo "Tidak ada siswa terdaftar, konversi data dilewati.\n";
}

echo "Selesai. Target kas kelas kini dihitung otomatis = per_siswa x jumlah siswa.\n";
