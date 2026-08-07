<?php
/* Migrasi sekali jalan: buat tabel target_kas (aman dijalankan ulang). */
include __DIR__ . '/db.php';

$sql = "CREATE TABLE IF NOT EXISTS target_kas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    periode VARCHAR(7) NOT NULL UNIQUE,
    target DECIMAL(12,2) NOT NULL DEFAULT 0,
    keterangan VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (Koneksi::q($sql)) {
    echo "Tabel target_kas siap.\n";
} else {
    echo "Gagal membuat tabel target_kas.\n";
}
