USE db_kas_kelas;

-- Contoh target kas dari kesepakatan kelas (bendahara bisa ubah via halaman Pembayaran).
-- Model target per siswa: per_siswa x jumlah siswa = total target kelas.
-- Periode disimpan sebagai YYYY-MM.
INSERT IGNORE INTO target_kas (periode, per_siswa, keterangan) VALUES
('2026-05', 5000, 'Disepakati rapat kelas'),
('2026-06', 5000, 'Disepakati rapat kelas'),
('2026-07', 5000, 'Disepakati rapat kelas');
