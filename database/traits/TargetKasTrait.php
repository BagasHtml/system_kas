<?php

/**
 * Tanggung jawab: data & perhitungan target kas (kesepakatan kelas).
 *
 * Model target per siswa: nominal kas per siswa per bulan (kolom per_siswa).
 * Total target kelas dihitung dinamis = per_siswa x jumlah siswa,
 * sehingga saat siswa membayar, angka terkumpul otomatis ter-update.
 */

trait TargetKasTrait
{
    /**
     * Disediakan oleh QueryTrait (dipenuhi saat trait dikomposisi
     * ke dalam class Koneksi).
     */
    abstract protected static function q($sql, $params = []);

    /**
     * Jumlah siswa yang terdaftar (dipakai untuk menghitung total target kelas).
     */
    public static function jumlahSiswa(): int
    {
        $r = self::q("SELECT COUNT(*) c FROM siswa");
        return (int)($r ? $r->fetch_assoc()['c'] ?? 0 : 0);
    }

    /**
     * Total target kelas untuk satu periode = per_siswa x jumlah siswa.
     */
    public static function totalTargetPeriod(float $per_siswa): float
    {
        return $per_siswa * self::jumlahSiswa();
    }

    /**
     * Peta target kas per periode.
     *
     * @return array<string, array{per_siswa: float, keterangan: string|null}> periode => data
     */
    public static function targetMap(): array
    {
        try {
            self::ensurePerSiswaColumn();
            return self::queryTargetMap();
        } catch (\Throwable $e) {
            // Buat tabel target_kas otomatis jika belum ada di database.
            self::q("CREATE TABLE IF NOT EXISTS target_kas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                periode VARCHAR(7) NOT NULL UNIQUE,
                target DECIMAL(12,2) NOT NULL DEFAULT 0,
                per_siswa DECIMAL(12,2) DEFAULT NULL,
                keterangan VARCHAR(255) DEFAULT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            self::ensurePerSiswaColumn();
            return self::queryTargetMap();
        }
    }

    /**
     * Pastikan kolom per_siswa tersedia (migrasi otomatis bila tabel lama).
     */
    private static function ensurePerSiswaColumn(): void
    {
        $r = self::q("SHOW COLUMNS FROM target_kas LIKE 'per_siswa'");
        if (!$r || $r->num_rows === 0) {
            self::q("ALTER TABLE target_kas ADD COLUMN per_siswa DECIMAL(12,2) DEFAULT NULL AFTER target");
        }
        $c = self::q("SELECT COUNT(*) c FROM target_kas WHERE per_siswa IS NULL OR per_siswa <= 0");
        if ($c && (int)$c->fetch_assoc()['c'] > 0) {
            self::backfillPerSiswa();
        }
    }

    /**
     * Konversi data lama: per_siswa = target total / jumlah siswa.
     */
    private static function backfillPerSiswa(): void
    {
        $jml = self::jumlahSiswa();
        if ($jml > 0) {
            self::q("UPDATE target_kas SET per_siswa = ROUND(target / $jml) WHERE per_siswa IS NULL OR per_siswa <= 0");
        }
    }

    /**
     * Ambil data target dari tabel target_kas.
     */
    private static function queryTargetMap(): array
    {
        $map = [];
        $r = self::q("SELECT periode, target, per_siswa, keterangan FROM target_kas ORDER BY periode ASC");
        if ($r) {
            while ($row = $r->fetch_assoc()) {
                $map[$row['periode']] = [
                    'per_siswa' => (float)$row['per_siswa'],
                    'keterangan' => $row['keterangan'] ?? null,
                ];
            }
        }
        return $map;
    }

    /**
     * Jumlah seluruh target yang terdaftar (per_siswa x jumlah siswa per periode).
     *
     * @param array $map Hasil dari Koneksi::targetMap().
     */
    public static function totalTarget(array $map): float
    {
        $jml = self::jumlahSiswa();
        $t = 0.0;
        foreach ($map as $v) {
            $t += (float)$v['per_siswa'] * $jml;
        }
        return $t;
    }
}
