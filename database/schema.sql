CREATE DATABASE IF NOT EXISTS db_kas_kelas;
USE db_kas_kelas;

CREATE TABLE siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nomor_absen INT NOT NULL,
    role ENUM('siswa', 'bendahara') NOT NULL DEFAULT 'siswa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_absen (nomor_absen)
);

CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    periode VARCHAR(7) NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('lunas', 'belum', 'pending') NOT NULL DEFAULT 'belum',
    metode ENUM('langsung', 'qris', 'dana') NOT NULL DEFAULT 'langsung',
    tanggal_bayar DATE NULL,
    bukti_transfer VARCHAR(255) NULL,
    catatan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    INDEX idx_siswa_id (siswa_id)
);

CREATE TABLE pengeluaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keterangan TEXT NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL,
    kategori VARCHAR(50) NOT NULL DEFAULT 'Lainnya',
    bukti_nota VARCHAR(255) NULL,
    tanggal DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE target_kas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    periode VARCHAR(7) NOT NULL UNIQUE,
    target DECIMAL(12,2) NOT NULL DEFAULT 0,
    per_siswa DECIMAL(12,2) DEFAULT NULL,
    keterangan VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE setting (
    kkey VARCHAR(60) NOT NULL PRIMARY KEY,
    kvalue TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);