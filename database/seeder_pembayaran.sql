USE db_kas_kelas;

-- Seeder tabel pembayaran
-- Mengisi 3 periode kas (Mei, Juni, Juli 2026) untuk semua siswa
-- Jumlah kas per periode: Rp 5.000
-- Variasi status: Mei lunas semua, Juni sebagian kecil belum, Juli sebagian besar belum

-- INSERT IGNORE: aman dijalankan berulang, baris duplikat (siswa_id, periode) diabaikan
INSERT IGNORE INTO pembayaran (siswa_id, periode, jumlah, status, tanggal_bayar)
SELECT
    s.id,
    CASE p.n
        WHEN 1 THEN 'Mei 2026'
        WHEN 2 THEN 'Juni 2026'
        ELSE 'Juli 2026'
    END AS periode,
    5000 AS jumlah,
    CASE
        WHEN (p.n = 1) THEN 'lunas'
        WHEN (p.n = 2 AND s.id % 7 <> 0) THEN 'lunas'
        WHEN (p.n = 3 AND s.id % 3 = 1) THEN 'lunas'
        ELSE 'belum'
    END AS status,
    CASE
        WHEN (p.n = 1) THEN DATE_ADD('2026-05-01', INTERVAL s.id DAY)
        WHEN (p.n = 2 AND s.id % 7 <> 0) THEN DATE_ADD('2026-06-01', INTERVAL s.id DAY)
        WHEN (p.n = 3 AND s.id % 3 = 1) THEN DATE_ADD('2026-07-01', INTERVAL s.id DAY)
        ELSE NULL
    END AS tanggal_bayar
FROM siswa s
CROSS JOIN (SELECT 1 AS n UNION ALL SELECT 2 UNION ALL SELECT 3) p;
