<?php

trait FormatTrait
{
    private static function bulanLabel(): array
    {
        return [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];
    }

    private static function bulanPendekLabel(): array
    {
        return [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
        ];
    }

    public static function periodeLabel(string $ym): string
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $ym, $m)) {
            $bulan = self::bulanLabel();
            if (isset($bulan[$m[2]])) {
                return $bulan[$m[2]] . ' ' . $m[1];
            }
        }
        return $ym;
    }

    public static function periodeShortLabel(string $ym): string
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $ym, $m)) {
            $pendek = self::bulanPendekLabel();
            if (isset($pendek[$m[2]])) {
                return $pendek[$m[2]] . ' ' . $m[1];
            }
        }
        return $ym;
    }
}
