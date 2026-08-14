<?php

require_once __DIR__ . '/AuthController.php';

/**
 * Tanggung jawab: siapkan data ringkasan dashboard admin.
 */
class AdminDashboardController
{
    public static function handle(): array
    {
        AuthController::requireAdmin();

        $db = new Koneksi();

        $total_siswa = (int)($db::q("SELECT COUNT(*) c FROM siswa")->fetch_assoc()['c'] ?? 0);

        $pending_count = (int)($db::q("SELECT COUNT(*) c FROM pembayaran WHERE status = 'pending'")->fetch_assoc()['c'] ?? 0);

        $agg = $db::q(
            "SELECT
                COUNT(CASE WHEN status = 'lunas' THEN 1 END) AS lunas_count,
                COUNT(CASE WHEN status = 'belum' THEN 1 END) AS belum_count,
                COALESCE(SUM(CASE WHEN status = 'lunas' THEN jumlah END), 0) AS pemasukan,
                COALESCE(SUM(CASE WHEN status = 'belum' THEN jumlah END), 0) AS belum
             FROM pembayaran"
        )->fetch_assoc();

        $pemasukan = (float)$agg['pemasukan'];
        $belum     = (float)$agg['belum'];
        $lunas_count = (int)$agg['lunas_count'];
        $belum_count = (int)$agg['belum_count'];

        $keluar = $db::q("SELECT COALESCE(SUM(jumlah), 0) t, COUNT(*) c FROM pengeluaran")->fetch_assoc();
        $pengeluaran_total = (float)$keluar['t'];
        $pengeluaran_count = (int)$keluar['c'];

        $saldo = $pemasukan - $pengeluaran_total;

        /* Target kas (kesepakatan kelas): "Belum Terkumpul" jadi sisa target bila target ada. */
        $target_map = Koneksi::targetMap();
        $total_target = Koneksi::totalTarget($target_map);
        $ada_target = $total_target > 0;
        if ($ada_target) {
            $belum = max(0, $total_target - $pemasukan);
        }

        $kas_per_siswa = null;
        if ($ada_target && $target_map) {
            $latest_tp = max(array_keys($target_map));
            $kas_per_siswa = (float)$target_map[$latest_tp]['per_siswa'];
        }

        $pct_lunas = $ada_target
            ? min(100, round($pemasukan / $total_target * 100))
            : min(100, round($pemasukan / max(1, $pemasukan + $belum) * 100));
        $rata_rata = $total_siswa > 0 ? round($pemasukan / $total_siswa) : 0;

        $recent = $db::q(
            "SELECT p.periode, p.jumlah, p.status, p.tanggal_bayar, s.nama, s.nomor_absen
             FROM pembayaran p
             INNER JOIN siswa s ON s.id = p.siswa_id
             ORDER BY p.id DESC LIMIT 5"
        )->fetch_all(MYSQLI_ASSOC);

        $chart = array_fill(1, 12, 0.0);
        foreach ($db::q(
            "SELECT MONTH(tanggal_bayar) m, COALESCE(SUM(jumlah), 0) t
             FROM pembayaran
             WHERE status = 'lunas' AND tanggal_bayar IS NOT NULL AND YEAR(tanggal_bayar) = YEAR(CURDATE())
             GROUP BY MONTH(tanggal_bayar)"
        ) as $r) {
            $chart[(int)$r['m']] = (float)$r['t'];
        }
        $max_chart = max(1, max($chart));
        $chart_any = array_sum($chart) > 0;

        $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $username = htmlspecialchars($_SESSION['username'] ?? 'Admin');

        $cur_m = (int)date('n');
        $prev_m = $cur_m - 1;
        $cur_val = $chart[$cur_m];
        $prev_val = $prev_m >= 1 ? $chart[$prev_m] : 0;
        $trend = $prev_val > 0 ? round(($cur_val - $prev_val) / $prev_val * 100) : 0;
        $trend_up = $trend >= 0;
        $trend_txt = $trend_up ? '+' . $trend : (string)$trend;

        /* ---- Build smooth line chart (Catmull-Rom -> Bezier) ---- */
        $CW = 640; $CH = 240; $CPL = 46; $CPR = 14; $CPT = 22; $CPB = 34;
        $pts = [];
        for ($i = 1; $i <= 12; $i++) {
            $x = $CPL + ($i - 1) * (($CW - $CPL - $CPR) / 11);
            $y = $chart[$i] > 0 ? $CPT + ($CH - $CPT - $CPB) * (1 - $chart[$i] / $max_chart) : ($CH - $CPB);
            $pts[] = [round($x, 2), round($y, 2)];
        }
        $smooth_path = "M {$pts[0][0]},{$pts[0][1]}";
        for ($i = 0; $i < 11; $i++) {
            $p0 = $pts[max(0, $i - 1)];
            $p1 = $pts[$i];
            $p2 = $pts[$i + 1];
            $p3 = $pts[min(11, $i + 2)];
            $c1x = round($p1[0] + ($p2[0] - $p0[0]) / 6, 2); $c1y = round($p1[1] + ($p2[1] - $p0[1]) / 6, 2);
            $c2x = round($p2[0] - ($p3[0] - $p1[0]) / 6, 2); $c2y = round($p2[1] - ($p3[1] - $p1[1]) / 6, 2);
            $smooth_path .= " C {$c1x},{$c1y} {$c2x},{$c2y} {$p2[0]},{$p2[1]}";
        }
        $baseline = $CH - $CPB;
        $area_path = $smooth_path . " L {$pts[11][0]},{$baseline} L {$pts[0][0]},{$baseline} Z";

        return [
            'total_siswa'         => $total_siswa,
            'pending_count'       => $pending_count,
            'pemasukan'           => $pemasukan,
            'belum'               => $belum,
            'lunas_count'         => $lunas_count,
            'belum_count'         => $belum_count,
            'pengeluaran_total'   => $pengeluaran_total,
            'pengeluaran_count'   => $pengeluaran_count,
            'saldo'               => $saldo,
            'target_map'          => $target_map,
            'total_target'        => $total_target,
            'ada_target'          => $ada_target,
            'kas_per_siswa'       => $kas_per_siswa,
            'pct_lunas'           => $pct_lunas,
            'rata_rata'           => $rata_rata,
            'recent'              => $recent,
            'chart'               => $chart,
            'max_chart'           => $max_chart,
            'chart_any'           => $chart_any,
            'bulan'               => $bulan,
            'username'            => $username,
            'cur_m'               => $cur_m,
            'prev_m'              => $prev_m,
            'cur_val'             => $cur_val,
            'prev_val'            => $prev_val,
            'trend'               => $trend,
            'trend_up'            => $trend_up,
            'trend_txt'           => $trend_txt,
            'CW'                  => $CW,
            'CH'                  => $CH,
            'CPL'                 => $CPL,
            'CPR'                 => $CPR,
            'CPT'                 => $CPT,
            'CPB'                 => $CPB,
            'pts'                 => $pts,
            'smooth_path'         => $smooth_path,
            'area_path'           => $area_path,
            'baseline'            => $baseline,
        ];
    }
}
