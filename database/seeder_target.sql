USE db_kas_kelas;

-- Contoh target kas dari kesepakatan kelas (bendahara bisa ubah via halaman Pembayaran).
-- Periode disimpan sebagai YYYY-MM.
INSERT IGNORE INTO target_kas (periode, target, keterangan) VALUES
('2026-05', 150000, 'Disepakati rapat kelas'),
('2026-06', 150000, 'Disepakati rapat kelas'),
('2026-07', 150000, 'Disepakati rapat kelas');
