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
            'pengeluaran_total'     => $pengeluaran_total,
            'pengeluaran_count'     => $pengeluaran_count,
            'pengeluaran_terakhir'  => $pengeluaran_terakhir,
        ];
    }
}
