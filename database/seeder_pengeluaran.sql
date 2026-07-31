USE db_kas_kelas;

-- Hapus data lama agar seeder bisa dijalankan berulang tanpa duplikat
DELETE FROM pengeluaran;

INSERT INTO pengeluaran (keterangan, jumlah, tanggal) VALUES
('Beli spidol whiteboard', 15000, '2026-06-02'),
('Fotokopi soal ujian', 25000, '2026-06-10'),
('Konsumsi rapat kelas', 40000, '2026-06-28'),
('Hadiah lomba 17 Agustus', 75000, '2026-07-12'),
('Sapu dan alat kebersihan', 30000, '2026-07-20'),
('Cetak foto kelas', 20000, '2026-07-25');
