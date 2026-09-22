<?php

trait SettingTrait
{
    private static $appSettingDefaults = [
        'dana_nomor' => '0813-2175-0459',
        'dana_nama'  => 'Bagas Tresna Nanda MS',
        'qris_path'  => 'assets/img/qris.png',
    ];

    /**
     * Pasangan key => value seluruh pengaturan (default + nilai dari DB).
     */
    public static function allSettings(): array
    {
        self::ensureSettingTable();
        $values = self::$appSettingDefaults;
        $r = self::q("SELECT kkey, kvalue FROM setting ORDER BY kkey ASC");
        if ($r) {
            while ($row = $r->fetch_assoc()) {
                $values[$row['kkey']] = $row['kvalue'];
            }
        }
        return $values;
    }

    /**
     * Ambil satu pengaturan; fallback ke $default bila kosong / belum ada.
     */
    public static function getSetting(string $key, string $default = ''): string
    {
        $values = self::allSettings();
        if (array_key_exists($key, $values) && $values[$key] !== '') {
            return (string)$values[$key];
        }
        return $default;
    }

    /**
     * Simpan satu pengaturan (insert atau update).
     */
    public static function setSetting(string $key, string $value): void
    {
        if (trim($key) === '' || trim($value) === '') {
            return;
        }
        self::ensureSettingTable();
        self::q(
            "INSERT INTO setting (kkey, kvalue) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE kvalue = VALUES(kvalue)",
            [$key, $value]
        );
    }

    private static function ensureSettingTable(): void
    {
        self::q("CREATE TABLE IF NOT EXISTS setting (
            kkey VARCHAR(60) NOT NULL PRIMARY KEY,
            kvalue TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}