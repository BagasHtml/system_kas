<?php
/* ===== Shared view helpers ===== */

if (!function_exists('ic')) {
    function ic(string $inner, int $size = 20): string
    {
        return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $inner . '</svg>';
    }
}

if (!function_exists('rupiah')) {
    function rupiah(float $n): string
    {
        return 'Rp ' . number_format($n, 0, ',', '.');
    }
}

if (!function_exists('shortnum')) {
    function shortnum(float $n): string
    {
        if ($n >= 1000000) return number_format($n / 1000000, 1, ',', '.') . ' jt';
        if ($n >= 1000) return number_format($n / 1000, 0, ',', '.') . ' rb';
        return number_format($n, 0, ',', '.');
    }
}

if (!function_exists('kpi')) {
    function kpi(array $k): string
    {
        $tone  = $k['tone'] ?? 'success';
        $extra = $tone === 'accent' ? ' dash-kpi-accent' : '';
        $note = '';
        if (!empty($k['note'])) {
            $note = '<span class="dash-kpi-note">' . htmlspecialchars($k['note']) . '</span>';
        }
        return '<div class="dash-kpi' . $extra . '">'
            . '<div class="dash-kpi-info">'
            . '<span class="dash-kpi-label">' . htmlspecialchars($k['label']) . '</span>'
            . '<span class="dash-kpi-value">' . $k['value'] . '</span>'
            . $note
            . '</div>'
            . '</div>';
    }
}

if (!function_exists('status_pill')) {
    function status_pill(string $status): string
    {
        if ($status === 'lunas') {
            return '<span class="text-status lunas">Terkumpul</span>';
        }
        return '<span class="text-status belum">Belum Terkumpul</span>';
    }
}
