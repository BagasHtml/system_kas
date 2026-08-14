<?php
require_once __DIR__ . '/db.php';

function run_migrations() {
    $mysqli = Koneksi::executeQuery("SELECT 1");
    if (!$mysqli) return;

    $dbName = getenv('KAS_DB_NAME') ?: 'db_kas_kelas';
    
    // 1. Update status column in pembayaran table to include 'pending'
    try {
        Koneksi::executeQuery("ALTER TABLE pembayaran MODIFY COLUMN status ENUM('lunas', 'belum', 'pending') NOT NULL DEFAULT 'belum'");
    } catch (\Throwable $e) {}

    // 2. Add bukti_transfer & catatan to pembayaran
    $checkPembayaran = Koneksi::executeQuery("SHOW COLUMNS FROM pembayaran LIKE 'bukti_transfer'");
    if ($checkPembayaran && $checkPembayaran->num_rows === 0) {
        Koneksi::executeQuery("ALTER TABLE pembayaran ADD COLUMN bukti_transfer VARCHAR(255) NULL DEFAULT NULL AFTER tanggal_bayar");
    }
    
    $checkCatatan = Koneksi::executeQuery("SHOW COLUMNS FROM pembayaran LIKE 'catatan'");
    if ($checkCatatan && $checkCatatan->num_rows === 0) {
        Koneksi::executeQuery("ALTER TABLE pembayaran ADD COLUMN catatan TEXT NULL DEFAULT NULL AFTER bukti_transfer");
    }

    // 3. Add kategori & bukti_nota to pengeluaran
    $checkKategori = Koneksi::executeQuery("SHOW COLUMNS FROM pengeluaran LIKE 'kategori'");
    if ($checkKategori && $checkKategori->num_rows === 0) {
        Koneksi::executeQuery("ALTER TABLE pembayaran ADD COLUMN kategori VARCHAR(50) DEFAULT 'Lainnya'");
    }

    $checkPengeluaranKategori = Koneksi::executeQuery("SHOW COLUMNS FROM pengeluaran LIKE 'kategori'");
    if ($checkPengeluaranKategori && $checkPengeluaranKategori->num_rows === 0) {
        Koneksi::executeQuery("ALTER TABLE pengeluaran ADD COLUMN kategori VARCHAR(50) NOT NULL DEFAULT 'Lainnya' AFTER jumlah");
    }

    $checkPengeluaranNota = Koneksi::executeQuery("SHOW COLUMNS FROM pengeluaran LIKE 'bukti_nota'");
    if ($checkPengeluaranNota && $checkPengeluaranNota->num_rows === 0) {
        Koneksi::executeQuery("ALTER TABLE pengeluaran ADD COLUMN bukti_nota VARCHAR(255) NULL DEFAULT NULL AFTER kategori");
    }

    // Create uploads directories if they don't exist
    $baseUploadDir = str_replace('\\', '/', dirname(__DIR__)) . '/assets/uploads';
    if (!is_dir($baseUploadDir)) {
        @mkdir($baseUploadDir, 0777, true);
    }
    if (!is_dir($baseUploadDir . '/bukti_transfer')) {
        @mkdir($baseUploadDir . '/bukti_transfer', 0777, true);
    }
    if (!is_dir($baseUploadDir . '/bukti_nota')) {
        @mkdir($baseUploadDir . '/bukti_nota', 0777, true);
    }
}

run_migrations();
