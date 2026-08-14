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
        if (class_exists('Koneksi')) {
            return Koneksi::rupiah($n);
        }
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
        if ($status === 'pending') {
            return '<span class="text-status pending" style="background:#fff8e6;color:#b45309;border:1px solid #fef3c7;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><i class="bi bi-clock-history"></i> Pending</span>';
        }
        return '<span class="text-status belum">Belum Terkumpul</span>';
    }
}

if (!function_exists('get_tunggakan_siswa')) {
    function get_tunggakan_siswa(int $siswa_id): int
    {
        if ($siswa_id <= 0) return 0;
        $db = new Koneksi();
        $target_map = $db::targetMap();
        if (empty($target_map)) return 0;

        $cur_month = date('Y-m');
        $unpaid = 0;

        $paid_res = $db::q("SELECT periode FROM pembayaran WHERE siswa_id = ? AND status = 'lunas'", [$siswa_id]);
        $paid_periodes = [];
        if ($paid_res) {
            while ($r = $paid_res->fetch_assoc()) {
                $paid_periodes[] = $r['periode'];
            }
        }

        foreach ($target_map as $p => $v) {
            if ($p <= $cur_month && !in_array($p, $paid_periodes)) {
                $unpaid++;
            }
        }
        return $unpaid;
    }
}

