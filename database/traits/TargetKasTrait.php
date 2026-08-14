<?php

/**
 * Tanggung jawab: data & perhitungan target kas (kesepakatan kelas).
 */

trait TargetKasTrait
{
    /**
     * Peta target kas per periode.
     *
     * @return array<string, array{target: float, keterangan: string|null}> periode => data
     */
    public static function targetMap(): array
    {
        try {
            return self::queryTargetMap();
        } catch (\Throwable $e) {
            // Buat tabel target_kas otomatis jika belum ada di database.
            self::q("CREATE TABLE IF NOT EXISTS target_kas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                periode VARCHAR(7) NOT NULL UNIQUE,
                target DECIMAL(12,2) NOT NULL DEFAULT 0,
                keterangan VARCHAR(255) DEFAULT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            return self::queryTargetMap();
        }
    }

    /**
     * Ambil data target dari tabel target_kas.
     */
    private static function queryTargetMap(): array
    {
        $map = [];
        $r = self::q("SELECT periode, target, keterangan FROM target_kas ORDER BY periode ASC");
        if ($r) {
            while ($row = $r->fetch_assoc()) {
                $map[$row['periode']] = [
                    'target' => (float)$row['target'],
                    'keterangan' => $row['keterangan'] ?? null,
                ];
            }
        }
        return $map;
    }

    /**
     * Jumlah seluruh target yang terdaftar.
     *
     * @param array $map Hasil dari Koneksi::targetMap().
     */
    public static function totalTarget(array $map): float
    {
        $t = 0.0;
        foreach ($map as $v) {
            $t += $v['target'];
        }
        return $t;
    }
}
