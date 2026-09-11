CREATE DATABASE IF NOT EXISTS db_kas_kelas;
USE db_kas_kelas;

CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('bendahara') NOT NULL DEFAULT 'bendahara',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nomor_absen INT NOT NULL,
    role ENUM('siswa', 'bendahara') NOT NULL DEFAULT 'siswa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    periode VARCHAR(20) NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('lunas', 'belum') NOT NULL DEFAULT 'belum',
    tanggal_bayar DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    INDEX idx_siswa_id (siswa_id)
);

CREATE TABLE pengeluaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keterangan TEXT NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL,
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
