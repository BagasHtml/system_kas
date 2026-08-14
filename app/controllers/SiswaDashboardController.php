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

        /* ===== HANDLE UPLOAD BUKTI TRANSFER ===== */
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['upload_bukti'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token tidak valid. Silakan coba lagi.');
                header("Location: dashboard.php#bayar");
                exit;
            }

            $periode = trim($_POST['periode'] ?? '');
            $jumlah  = (float)($_POST['jumlah'] ?? 0);
            $catatan = trim($_POST['catatan'] ?? '');

            if (!preg_match('/^\d{4}-\d{2}$/', $periode) || $jumlah <= 0) {
                Koneksi::setFlash('error', 'Pilih bulan dan jumlah pembayaran yang valid.');
                header("Location: dashboard.php#bayar");
                exit;
            }

            $bukti_path = null;
            if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['bukti_transfer']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($ext, $allowed)) {
                    $filename = 'bukti_' . time() . '_' . rand(100, 999) . '.' . $ext;
                    $targetDir = str_replace('\\', '/', dirname(__DIR__, 2)) . '/assets/uploads/bukti_transfer/';
                    if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
                    $targetFile = $targetDir . $filename;
                    if (move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $targetFile)) {
                        $bukti_path = 'assets/uploads/bukti_transfer/' . $filename;
                    }
                } else {
                    Koneksi::setFlash('error', 'Format gambar harus JPG, PNG, atau WEBP.');
                    header("Location: dashboard.php#bayar");
                    exit;
                }
            }

            if (!$bukti_path) {
                Koneksi::setFlash('error', 'File bukti transfer wajib diupload.');
                header("Location: dashboard.php#bayar");
                exit;
            }

            $db::q(
                "INSERT INTO pembayaran (siswa_id, periode, jumlah, status, tanggal_bayar, bukti_transfer, catatan)
                 VALUES (?, ?, ?, 'pending', NOW(), ?, ?)
                 ON DUPLICATE KEY UPDATE jumlah = ?, status = 'pending', tanggal_bayar = NOW(), bukti_transfer = ?, catatan = ?",
                [$siswa_id, $periode, $jumlah, $bukti_path, $catatan, $jumlah, $bukti_path, $catatan]
            );

            Koneksi::setFlash('success', 'Konfirmasi pembayaran berhasil dikirim! Menunggu verifikasi dari bendahara.');
            header("Location: dashboard.php#riwayat");
            exit;
        }

        $pembayaran = [];
        if ($siswa_id > 0) {
            $pembayaran = $db::q(
                "SELECT id, periode, jumlah, status, tanggal_bayar, bukti_transfer, catatan
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

        /* Target kas kelas (kesepakatan kelas, diatur bendahara). */
        $target_map = Koneksi::targetMap();
        $total_target = Koneksi::totalTarget($target_map);
        $ada_target = $total_target > 0;
        $kelas_collected = 0.0;
        $kelas_remainder = 0.0;
        if ($ada_target) {
            $kc = $db::q("SELECT COALESCE(SUM(jumlah), 0) t FROM pembayaran WHERE status = 'lunas'")->fetch_assoc();
            $kelas_collected = (float)($kc['t'] ?? 0);
            $kelas_remainder = max(0, $total_target - $kelas_collected);
        }
        $pct_kelas = $ada_target ? min(100, round($kelas_collected / $total_target * 100)) : 0;

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
        ];
    }
}
