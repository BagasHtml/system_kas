<?php

trait BelanjaTrait
{
    abstract protected static function q($sql, $params = []);

    /* ===== TARGET BELANJA ===== */

    public static function kategoriBelanja(): string
    {
        return 'Belanja Kelas';
    }

    public static function targetBelanjaList(): array
    {
        $items = [];
        $r = self::q("SELECT * FROM target_belanja ORDER BY id DESC");
        if ($r) {
            while ($row = $r->fetch_assoc()) {
                $items[] = self::decorateBelanja($row);
            }
        }
        usort($items, function ($a, $b) {
            $ord = ['berlangsung' => 0, 'tercapai' => 1, 'terbeli' => 2];
            return ($ord[$a['status']] ?? 3) <=> ($ord[$b['status']] ?? 3);
        });
        return $items;
    }

    public static function targetBelanjaItem(int $id): ?array
    {
        $r = self::q("SELECT * FROM target_belanja WHERE id = ?", [$id]);
        $row = $r ? $r->fetch_assoc() : null;
        return $row ? self::decorateBelanja($row) : null;
    }

    public static function decorateBelanja(array $row): array
    {
        $id     = (int)$row['id'];
        $target = (float)$row['target'];

        $collected = (float)(self::q(
            "SELECT COALESCE(SUM(jumlah), 0) t FROM setoran_belanja WHERE target_belanja_id = ?", [$id]
        )->fetch_assoc()['t'] ?? 0);

        $purchased = (float)(self::q(
            "SELECT COALESCE(SUM(jumlah), 0) t FROM pengeluaran WHERE target_belanja_id = ?", [$id]
        )->fetch_assoc()['t'] ?? 0);

        $sisa = $collected - $purchased;

        $status = $row['status'];
        if ($status === 'berlangsung' && $target > 0 && $collected >= $target) {
            $status = 'tercapai';
            self::q(
                "UPDATE target_belanja SET status = 'tercapai' WHERE id = ? AND status = 'berlangsung'",
                [$id]
            );
        }

        $n = max(1, self::jumlahSiswa());
        $per_siswa = (float)($row['per_siswa'] ?? 0);
        if ($per_siswa <= 0 && $target > 0 && $n > 0) {
            $per_siswa = round($target / $n);
        }

        $pct = $target > 0 ? min(100, (int)round($collected / $target * 100)) : 0;

        return [
            'id'          => $id,
            'nama_barang' => $row['nama_barang'],
            'keterangan'  => $row['keterangan'] ?? null,
            'target'      => $target,
            'per_siswa'   => $per_siswa,
            'status'      => $status,
            'created_at'  => $row['created_at'] ?? null,
            'collected'   => $collected,
            'purchased'   => $purchased,
            'sisa'        => $sisa,
            'pct'         => $pct,
            'status_text' => self::belanjaStatusText($status),
            'status_cls'  => self::belanjaStatusCls($status),
        ];
    }

    public static function belanjaStatusText(string $s): string
    {
        return match ($s) {
            'tercapai' => 'Target Tercapai',
            'terbeli'  => 'Sudah Dibeli',
            default    => 'Dikumpulkan',
        };
    }

    public static function belanjaStatusCls(string $s): string
    {
        return match ($s) {
            'tercapai' => 'success',
            'terbeli'  => 'muted',
            default    => 'warn',
        };
    }

    /**
     * Progres per siswa: [siswa_id, nama, absen, share, paid, kurang, pct]
     */
    public static function targetBelanjaProgress(int $id, float $per_siswa): array
    {
        $paid_map = [];
        $pr = self::q(
            "SELECT siswa_id, COALESCE(SUM(jumlah), 0) t FROM setoran_belanja WHERE target_belanja_id = ? GROUP BY siswa_id",
            [$id]
        );
        if ($pr) {
            while ($r = $pr->fetch_assoc()) {
                $paid_map[(int)$r['siswa_id']] = (float)$r['t'];
            }
        }

        $rows = [];
        $res = self::q("SELECT id, nama, nomor_absen FROM siswa ORDER BY nomor_absen ASC");
        if ($res) {
            while ($s = $res->fetch_assoc()) {
                $sid  = (int)$s['id'];
                $paid = $paid_map[$sid] ?? 0.0;
                $rows[] = [
                    'siswa_id' => $sid,
                    'nama'     => $s['nama'],
                    'absen'    => (int)$s['nomor_absen'],
                    'share'    => $per_siswa,
                    'paid'     => $paid,
                    'kurang'   => max(0, $per_siswa - $paid),
                    'pct'      => $per_siswa > 0 ? min(100, (int)round($paid / $per_siswa * 100)) : 0,
                ];
            }
        }
        return $rows;
    }

    /* ===== SALDO KAS UTAMA (tanpa belanja) ===== */

    public static function saldoKasUtama(): float
    {
        $masuk = (float)(self::q(
            "SELECT COALESCE(SUM(jumlah), 0) t FROM pembayaran WHERE status = 'lunas'"
        )->fetch_assoc()['t'] ?? 0);
        $keluar = (float)(self::q(
            "SELECT COALESCE(SUM(jumlah), 0) t FROM pengeluaran WHERE target_belanja_id IS NULL"
        )->fetch_assoc()['t'] ?? 0);
        return $masuk - $keluar;
    }

    public static function totalPemasukanUtama(): float
    {
        return (float)(self::q(
            "SELECT COALESCE(SUM(jumlah), 0) t FROM pembayaran WHERE status = 'lunas'"
        )->fetch_assoc()['t'] ?? 0);
    }

    public static function totalPengeluaranUtama(): float
    {
        return (float)(self::q(
            "SELECT COALESCE(SUM(jumlah), 0) t FROM pengeluaran WHERE target_belanja_id IS NULL"
        )->fetch_assoc()['t'] ?? 0);
    }

    /* ===== PENGATURAN CMS ===== */

    public static function pengaturan(string $key, ?string $default = null): ?string
    {
        try {
            $r = self::q("SELECT `value` FROM pengaturan WHERE `key` = ?", [$key]);
            if ($r && $r->num_rows > 0) {
                return $r->fetch_assoc()['value'] ?? $default;
            }
        } catch (\Throwable $e) {
            self::ensurePengaturanTable();
            return $default;
        }
        return $default;
    }

    public static function setPengaturan(string $key, ?string $value): void
    {
        try {
            self::q(
                "INSERT INTO pengaturan (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?",
                [$key, $value, $value]
            );
        } catch (\Throwable $e) {
            self::ensurePengaturanTable();
            self::q(
                "INSERT INTO pengaturan (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?",
                [$key, $value, $value]
            );
        }
    }

    public static function allPengaturan(): array
    {
        try {
            $map = [];
            $r = self::q("SELECT `key`, `value` FROM pengaturan ORDER BY `key` ASC");
            if ($r) {
                while ($row = $r->fetch_assoc()) {
                    $map[$row['key']] = $row['value'];
                }
            }
            return $map;
        } catch (\Throwable $e) {
            self::ensurePengaturanTable();
            return [];
        }
    }

    private static function ensurePengaturanTable(): void
    {
        self::q("CREATE TABLE IF NOT EXISTS pengaturan (
            `key` VARCHAR(100) NOT NULL PRIMARY KEY,
            `value` TEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $defaults = [
            'nama_sekolah'  => 'SMA Negeri 1 Contoh',
            'nama_kelas'    => 'XII RPL 1',
            'nomor_dana'    => '0813-2175-0459',
            'atas_nama_dana'=> 'Bagas Tresna Nanda MS',
            'qris_path'     => 'assets/img/qris.png',
        ];
        foreach ($defaults as $k => $v) {
            self::q("INSERT IGNORE INTO pengaturan (`key`, `value`) VALUES (?, ?)", [$k, $v]);
        }
    }
}