<?php

require_once __DIR__ . '/AuthController.php';

/**
 * Tanggung jawab: siapkan data dashboard siswa (status pembayaran pribadi,
 * progres target kas kelas, ringkasan pengeluaran).
 */
class SiswaDashboardController
{
    public static function handle(): array
    {
        AuthController::requireSiswa();

        $db = new Koneksi();

        $siswa_id    = (int)($_SESSION['siswa_id'] ?? 0);
        $siswa_nama  = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');
        $siswa_absen = htmlspecialchars($_SESSION['siswa_absen'] ?? '-');

        $pembayaran = [];
        if ($siswa_id > 0) {
            $pembayaran = $db::q(
                "SELECT id, periode, jumlah, status, metode, tanggal_bayar, bukti_transfer, catatan
                 FROM pembayaran WHERE siswa_id = ? ORDER BY id DESC LIMIT 24",
                [$siswa_id]
            )->fetch_all(MYSQLI_ASSOC);
        }

        $lunas = 0;
        $belum = 0;
        $periode_lunas = 0;
        foreach ($pembayaran as $p) {
            if ($p['status'] === 'lunas') {
                $lunas += (float)$p['jumlah'];
                $periode_lunas++;
            } else {
                $belum += (float)$p['jumlah'];
            }
        }

        /* Target kas kelas (kesepakatan kelas, diatur bendahara).
         * Terkumpul hanya dihitung dari pembayaran lunas pada periode yang ditargetkan,
         * konsisten dengan perhitungan di halaman pembayaran admin. */
        $target_map = Koneksi::targetMap();
        $total_target = Koneksi::totalTarget($target_map);
        $ada_target = $total_target > 0;
        $kelas_collected = 0.0;
        $kelas_remainder = 0.0;
        if ($ada_target) {
            $tp_keys = array_keys($target_map);
            $kc = $db::q(
                "SELECT COALESCE(SUM(jumlah), 0) t FROM pembayaran
                 WHERE status = 'lunas' AND periode IN (" . implode(',', array_fill(0, count($tp_keys), '?')) . ")",
                $tp_keys
            )->fetch_assoc();
            $kelas_collected = (float)($kc['t'] ?? 0);
            $kelas_remainder = max(0, $total_target - $kelas_collected);
        }
        $pct_kelas = $ada_target ? min(100, round($kelas_collected / $total_target * 100)) : 0;

        /* Progres target per periode (untuk grafik kotak di dashboard siswa). */
        $target_progress = [];
        $jml_t = max(1, (int)Koneksi::jumlahSiswa());
        if ($ada_target) {
            $collected_per = [];
            $r_c = $db::q(
                "SELECT periode, COALESCE(SUM(jumlah), 0) t FROM pembayaran
                 WHERE status = 'lunas' AND periode IN (" . implode(',', array_fill(0, count(array_keys($target_map)), '?')) . ")
                 GROUP BY periode",
                array_keys($target_map)
            );
            if ($r_c) {
                while ($row = $r_c->fetch_assoc()) {
                    $collected_per[$row['periode']] = (float)$row['t'];
                }
            }
            foreach ($target_map as $ym => $t) {
                $p_target = (float)$t['per_siswa'] * $jml_t;
                if ($p_target <= 0) continue;
                $p_collected = (float)($collected_per[$ym] ?? 0);
                $target_progress[] = [
                    'ym'     => $ym,
                    'label'  => Koneksi::periodeLabel($ym),
                    'target' => $p_target,
                    'collected' => $p_collected,
                    'pct'    => (int)min(100, round($p_collected / $p_target * 100)),
                ];
            }
            usort($target_progress, fn($a, $b) => strcmp($a['ym'], $b['ym']));
        }

        $pending_saya = (int)($db::q(
            "SELECT COUNT(*) c FROM pembayaran WHERE siswa_id = ? AND status = 'pending'",
            [$siswa_id]
        )->fetch_assoc()['c'] ?? 0);

        $jumlah_siswa = (int)Koneksi::jumlahSiswa();
        $siswa_kontribusi = (int)($db::q("SELECT COUNT(DISTINCT siswa_id) c FROM pembayaran WHERE status = 'lunas'")->fetch_assoc()['c'] ?? 0);

        /* Chart pemasukan per bulan (transparansi kas kelas). */
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

        $cur_m = (int)date('n');
        $prev_m = $cur_m - 1;
        $cur_val = $chart[$cur_m];
        $prev_val = $prev_m >= 1 ? $chart[$prev_m] : 0;
        $trend = $prev_val > 0 ? round(($cur_val - $prev_val) / $prev_val * 100) : 0;
        $trend_up = $trend >= 0;
        $trend_txt = $trend_up ? '+' . $trend : (string)$trend;

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

        /* Widget 2: Ringkasan Pengeluaran Kelas */
        $exp_total = $db::q("SELECT COALESCE(SUM(jumlah), 0) t, COUNT(*) c FROM pengeluaran")->fetch_assoc();
        $pengeluaran_total = (float)($exp_total['t'] ?? 0);
        $pengeluaran_count = (int)($exp_total['c'] ?? 0);
        $pengeluaran_terakhir = $db::q(
            "SELECT keterangan, jumlah, tanggal FROM pengeluaran ORDER BY tanggal DESC, id DESC LIMIT 3"
        )->fetch_all(MYSQLI_ASSOC);

        return [
            'siswa_id'              => $siswa_id,
            'siswa_nama'            => $siswa_nama,
            'siswa_absen'           => $siswa_absen,
            'pembayaran'            => $pembayaran,
            'lunas'                 => $lunas,
            'belum'                 => $belum,
            'periode_lunas'         => $periode_lunas,
            'target_map'            => $target_map,
            'total_target'          => $total_target,
            'ada_target'            => $ada_target,
            'kelas_collected'       => $kelas_collected,
            'kelas_remainder'       => $kelas_remainder,
            'pct_kelas'             => $pct_kelas,
            'pending_saya'          => $pending_saya,
            'jumlah_siswa'          => $jumlah_siswa,
            'siswa_kontribusi'      => $siswa_kontribusi,
            'pengeluaran_total'     => $pengeluaran_total,
            'pengeluaran_count'     => $pengeluaran_count,
            'pengeluaran_terakhir'  => $pengeluaran_terakhir,
            'chart'                 => $chart,
            'max_chart'             => $max_chart,
            'chart_any'             => $chart_any,
            'bulan'                 => $bulan,
            'cur_m'                 => $cur_m,
            'prev_m'                => $prev_m,
            'cur_val'               => $cur_val,
            'prev_val'              => $prev_val,
            'trend'                 => $trend,
            'trend_up'              => $trend_up,
            'trend_txt'             => $trend_txt,
            'CW'                    => $CW,
            'CH'                    => $CH,
            'CPL'                   => $CPL,
            'CPR'                   => $CPR,
            'CPT'                   => $CPT,
            'CPB'                   => $CPB,
            'pts'                   => $pts,
            'smooth_path'           => $smooth_path,
            'area_path'             => $area_path,
            'baseline'              => $baseline,
            'target_progress'       => $target_progress,
        ];
    }
}
