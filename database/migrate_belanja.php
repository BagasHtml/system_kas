<?php
/* Migrasi fitur Target Belanja & Pengaturan CMS.
   Tabel baru: target_belanja, setoran_belanja, pengaturan.
   Kolom baru: pengeluaran.target_belanja_id. Aman dijalankan ulang. */

function run_migration_belanja() {
    $db = new Koneksi();

    $db::q("CREATE TABLE IF NOT EXISTS target_belanja (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_barang VARCHAR(100) NOT NULL,
        keterangan TEXT NULL,
        target DECIMAL(12,2) NOT NULL DEFAULT 0,
        per_siswa DECIMAL(12,2) NULL DEFAULT NULL,
        status ENUM('berlangsung','tercapai','terbeli') NOT NULL DEFAULT 'berlangsung',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $db::q("CREATE TABLE IF NOT EXISTS setoran_belanja (
        id INT AUTO_INCREMENT PRIMARY KEY,
        target_belanja_id INT NOT NULL,
        siswa_id INT NOT NULL,
        jumlah DECIMAL(12,2) NOT NULL DEFAULT 0,
        metode ENUM('langsung','qris','dana') NOT NULL DEFAULT 'langsung',
        catatan TEXT NULL,
        tanggal DATE NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_sb_target (target_belanja_id),
        INDEX idx_sb_siswa (siswa_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $db::q("CREATE TABLE IF NOT EXISTS pengaturan (
        `key` VARCHAR(100) NOT NULL PRIMARY KEY,
        `value` TEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // kolom target_belanja_id pada pengeluaran
    $c = $db::q("SHOW COLUMNS FROM pengeluaran LIKE 'target_belanja_id'");
    if ($c && $c->num_rows === 0) {
        $db::q("ALTER TABLE pengeluaran ADD COLUMN target_belanja_id INT NULL DEFAULT NULL AFTER kategori");
    }

    // seed pengaturan default (aman dijalankan ulang)
    $defaults = [
        'nama_sekolah'  => 'SMA Negeri 1 Contoh',
        'nama_kelas'    => 'XII RPL 1',
        'nomor_dana'    => '0813-2175-0459',
        'atas_nama_dana'=> 'Bagas Tresna Nanda MS',
        'qris_path'     => 'assets/img/qris.png',
    ];
    foreach ($defaults as $k => $v) {
        $db::q("INSERT IGNORE INTO pengaturan (`key`, `value`) VALUES (?, ?)", [$k, $v]);
    }
}

try { run_migration_belanja(); } catch (\Throwable $e) {
    error_log('[kas_system] Migrasi belanja: ' . $e->getMessage());
}