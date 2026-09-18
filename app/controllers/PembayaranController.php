 <?php

require_once __DIR__ . '/AuthController.php';

class PembayaranController
{
    public static function handle(): array
    {
        AuthController::requireAdmin();

        $db = new Koneksi();

        $selected_periode = $_GET['periode'] ?? '';
        $cari = trim($_GET['cari'] ?? '');
        $selected_periode_db = '';
        if (preg_match('/^(\d{4})-(\d{2})$/', $selected_periode, $m) && (int)$m[2] >= 1 && (int)$m[2] <= 12) {
            $selected_periode_db = $selected_periode;
        }

        $back_parts = [];
        if ($selected_periode !== '') $back_parts['periode'] = $selected_periode;
        if ($cari !== '') $back_parts['cari'] = $cari;
        $back = $back_parts ? '?' . http_build_query($back_parts) : '';

        /* ===== HAPUS ===== */
        if (isset($_POST['hapus'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
                header("Location: pemasukan.php" . $back);
                exit;
            }
            $id = (int)$_POST['hapus'];
            if ($id > 0) {
                $db::q("DELETE FROM pembayaran WHERE id = ?", [$id]);
                Koneksi::setFlash('success', 'Data pembayaran berhasil dihapus.');
            }
            header("Location: pemasukan.php" . $back);
            exit;
        }

        /* ===== TARGET KAS (kesepakatan kelas) ===== */
        if (isset($_POST['simpan_target'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
                header("Location: pemasukan.php" . $back);
                exit;
            }
            $t_periode = trim($_POST['t_periode'] ?? '');
            $t_target  = (float)preg_replace('/[^\d]/', '', trim($_POST['t_target'] ?? ''));
            $t_keterangan = trim($_POST['t_keterangan'] ?? '');
            $t_valid = preg_match('/^(\d{4})-(\d{2})$/', $t_periode, $tm) && (int)$tm[2] >= 1 && (int)$tm[2] <= 12;

            if (!$t_valid || $t_target <= 0) {
                Koneksi::setFlash('error', 'Isi bulan dan nominal kas per siswa yang valid.');
                header("Location: pemasukan.php" . $back);
                exit;
            }

            $db::q(
                "INSERT INTO target_kas (periode, per_siswa, keterangan) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE per_siswa = ?, keterangan = ?",
                [$t_periode, $t_target, $t_keterangan !== '' ? $t_keterangan : null, $t_target, $t_keterangan !== '' ? $t_keterangan : null]
            );
            Koneksi::setFlash('success', 'Target kas per siswa ' . Koneksi::rupiah($t_target) . ' untuk ' . Koneksi::periodeLabel($t_periode) . ' disimpan.');
            header("Location: pemasukan.php" . $back);
            exit;
        }

        if (isset($_POST['hapus_target'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
                header("Location: pemasukan.php" . $back);
                exit;
            }
            $t_periode = trim($_POST['hapus_target'] ?? '');
            if (preg_match('/^\d{4}-\d{2}$/', $t_periode)) {
                $db::q("DELETE FROM target_kas WHERE periode = ?", [$t_periode]);
                Koneksi::setFlash('success', 'Target ' . Koneksi::periodeLabel($t_periode) . ' dihapus.');
            }
            header("Location: pemasukan.php" . $back);
            exit;
        }

        /* ===== VERIFIKASI BUKTI TRANSFER ===== */
        if (isset($_POST['setuju_verifikasi'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token keamanan tidak valid.');
                header("Location: pemasukan.php" . $back);
                exit;
            }
            $id = (int)$_POST['setuju_verifikasi'];
            if ($id > 0) {
                $db::q("UPDATE pembayaran SET status = 'lunas', tanggal_bayar = NOW() WHERE id = ?", [$id]);
                Koneksi::setFlash('success', 'Pembayaran berhasil diverifikasi & disetujui (Status: Lunas).');
            }
            header("Location: pemasukan.php" . $back);
            exit;
        }

        if (isset($_POST['tolak_verifikasi'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token keamanan tidak valid.');
                header("Location: pemasukan.php" . $back);
                exit;
            }
            $id = (int)$_POST['tolak_verifikasi'];
            if ($id > 0) {
                $db::q("UPDATE pembayaran SET status = 'belum' WHERE id = ?", [$id]);
                Koneksi::setFlash('error', 'Konfirmasi pembayaran ditolak.');
            }
            header("Location: pemasukan.php" . $back);
            exit;
        }

        /* ===== TAMBAH / EDIT ===== */
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
                header("Location: pemasukan.php" . $back);
                exit;
            }
            $id        = (int)($_POST['id'] ?? 0);
            $siswa_id  = (int)($_POST['siswa_id'] ?? 0);
            $jumlah    = (float)($_POST['jumlah'] ?? 0);
            $status    = in_array($_POST['status'] ?? '', ['lunas', 'pending', 'belum']) ? $_POST['status'] : 'belum';
            $metode    = in_array($_POST['metode'] ?? '', ['langsung', 'qris', 'dana'], true) ? $_POST['metode'] : 'langsung';
            $tanggal   = trim($_POST['tanggal_bayar'] ?? '');
            $tanggal   = $tanggal !== '' ? $tanggal : null;

            $periode = date('Y-m');

            if ($siswa_id <= 0 || $jumlah <= 0) {
                Koneksi::setFlash('error', 'Data tidak lengkap: pilih siswa dan jumlah yang valid.');
                header("Location: pemasukan.php" . $back);
                exit;
            }

            $periode_db = $periode;

            if ($status === 'lunas' && $tanggal === null) {
                $tanggal = date('Y-m-d');
            }

            if ($id > 0) {
                $db::q(
                    "UPDATE pembayaran SET siswa_id = ?, periode = ?, jumlah = ?, status = ?, metode = ?, tanggal_bayar = ? WHERE id = ?",
                    [$siswa_id, $periode_db, $jumlah, $status, $metode, $tanggal, $id]
                );
                Koneksi::setFlash('success', 'Data pembayaran berhasil diperbarui.');
            } else {
                $db::q(
                    "INSERT INTO pembayaran (siswa_id, periode, jumlah, status, metode, tanggal_bayar) VALUES (?, ?, ?, ?, ?, ?)",
                    [$siswa_id, $periode_db, $jumlah, $status, $metode, $tanggal]
                );
                Koneksi::setFlash('success', 'Data pembayaran berhasil ditambahkan.');
            }
            header("Location: pemasukan.php" . $back);
            exit;
        }

        /* ===== TAMPIL ===== */
        $per_page = 10;
        $page = max(1, (int)($_GET['hal'] ?? 1));

        $sql = "SELECT p.id, p.siswa_id, p.periode, p.jumlah, p.status, p.metode, p.tanggal_bayar, p.bukti_transfer, p.catatan, s.nama, s.nomor_absen
                FROM pembayaran p
                INNER JOIN siswa s ON s.id = p.siswa_id";
        $conditions = [];
        $params = [];
        if ($selected_periode_db !== '') {
            $conditions[] = "p.periode = ?";
            $params[] = $selected_periode_db;
        }
        if ($cari !== '') {
            $conditions[] = "(s.nama LIKE ? OR p.periode LIKE ?)";
            $params[] = "%$cari%";
            $params[] = "%$cari%";
        }
        if ($conditions) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY p.id DESC";

        $jml = $db::q(
            "SELECT COUNT(*) AS jml FROM pembayaran p INNER JOIN siswa s ON s.id = p.siswa_id" . ($conditions ? " WHERE " . implode(" AND ", $conditions) : ''),
            $params
        )->fetch_assoc();
        $total_data = (int)($jml['jml'] ?? 0);
        $total_pages = max(1, (int)ceil($total_data / $per_page));
        $page = min($page, $total_pages);
        $offset = ($page - 1) * $per_page;

        $resData = $db::q($sql . " LIMIT ? OFFSET ?", array_merge($params, [$per_page, $offset]));
        $data = $resData ? $resData->fetch_all(MYSQLI_ASSOC) : [];

        $start_item = $total_data === 0 ? 0 : $offset + 1;
        $end_item = min($offset + count($data), $total_data);
        $pg_parts = [];
        if ($selected_periode !== '') $pg_parts['periode'] = $selected_periode;
        if ($cari !== '') $pg_parts['cari'] = $cari;
        $pg_query = http_build_query($pg_parts);

        $resSiswa = $db::q("SELECT id, nama, nomor_absen FROM siswa ORDER BY nomor_absen ASC");
        $daftar_siswa = $resSiswa ? $resSiswa->fetch_all(MYSQLI_ASSOC) : [];

        $pending_list = $db::q(
            "SELECT p.*, s.nama, s.nomor_absen
             FROM pembayaran p
             JOIN siswa s ON p.siswa_id = s.id
             WHERE p.status = 'pending'
             ORDER BY p.id DESC"
        )->fetch_all(MYSQLI_ASSOC);

        $target_map = Koneksi::targetMap();

        $target_collected = [];
        if ($target_map) {
            $tp_keys = array_keys($target_map);
            $resC = $db::q(
                "SELECT periode, COALESCE(SUM(CASE WHEN status = 'lunas' THEN jumlah END), 0) AS t
                 FROM pembayaran WHERE periode IN (" . implode(',', array_fill(0, count($tp_keys), '?')) . ")
                 GROUP BY periode",
                $tp_keys
            );
            if ($resC) {
                while ($row = $resC->fetch_assoc()) {
                    $target_collected[$row['periode']] = (float)$row['t'];
                }
            }
        }

        $jumlah_siswa = Koneksi::jumlahSiswa();
        $period_collected = 0.0;
        $period_target = null;
        $period_per_siswa = 0.0;
        if ($selected_periode_db !== '') {
            $aggP = $db::q(
                "SELECT COALESCE(SUM(CASE WHEN status = 'lunas' THEN jumlah END), 0) AS t FROM pembayaran WHERE periode = ?",
                [$selected_periode_db]
            )->fetch_assoc();
            $period_collected = (float)($aggP['t'] ?? 0);
            if (isset($target_map[$selected_periode_db])) {
                $period_per_siswa = (float)$target_map[$selected_periode_db]['per_siswa'];
                $period_target = Koneksi::totalTargetPeriod($period_per_siswa);
            }
        }

        /* Hero ringkasan target: disesuaikan dengan periode terpilih / total / kosong. */
        $target_total_all = 0.0;
        foreach ($target_map as $tv) { $target_total_all += Koneksi::totalTargetPeriod((float)$tv['per_siswa']); }
        $target_collected_all = 0.0;
        foreach ($target_collected as $tv) { $target_collected_all += (float)$tv; }

        $hero = ['mode' => 'none'];
        if ($period_target !== null) {
            $hero_pct = min(100, round($period_collected / $period_target * 100));
            $hero_reached = $period_collected >= $period_target;
            $hero = [
                'mode'    => 'period',
                'pct'     => $hero_pct,
                'reached' => $hero_reached,
                'label'   => 'Target ' . Koneksi::periodeLabel($selected_periode_db),
                'sub'     => Koneksi::rupiah($period_per_siswa) . ' per siswa &times; ' . $jumlah_siswa . ' siswa',
                'amount'  => Koneksi::rupiah($period_target),
                'stats'   => [
                    ['lbl' => 'Kas / Siswa', 'val' => Koneksi::rupiah($period_per_siswa), 'cls' => ''],
                    ['lbl' => 'Terkumpul', 'val' => Koneksi::rupiah($period_collected), 'cls' => 'success'],
                    ['lbl' => $hero_reached ? 'Status' : 'Sisa Target', 'val' => $hero_reached ? 'Tercapai' : Koneksi::rupiah(max(0, $period_target - $period_collected)), 'cls' => $hero_reached ? 'success' : 'warn'],
                ],
                'badge'     => $hero_reached ? '<i class="bi bi-check-circle-fill"></i> Target Tercapai' : '<i class="bi bi-hourglass-split"></i> Sedang Berjalan',
                'badge_cls' => $hero_reached ? 'achieved' : 'on-track',
            ];
        } elseif ($selected_periode_db !== '') {
            $hero['note'] = 'Belum ada target untuk ' . Koneksi::periodeLabel($selected_periode_db) . '. Atur lewat form di samping.';
        } elseif ($target_total_all > 0) {
            $hero_pct = min(100, round($target_collected_all / $target_total_all * 100));
            $hero_reached = $target_collected_all >= $target_total_all;
            $hero = [
                'mode'    => 'overall',
                'pct'     => $hero_pct,
                'reached' => $hero_reached,
                'label'   => 'Kesepakatan Kas Kelas',
                'sub'     => count($target_map) . ' bulan ditargetkan &times; ' . $jumlah_siswa . ' siswa',
                'amount'  => Koneksi::rupiah($target_total_all),
                'stats'   => [
                    ['lbl' => 'Total Terkumpul', 'val' => Koneksi::rupiah($target_collected_all), 'cls' => 'success'],
                    ['lbl' => 'Total Sisa', 'val' => Koneksi::rupiah(max(0, $target_total_all - $target_collected_all)), 'cls' => 'warn'],
                ],
                'badge'     => '<i class="bi bi-calendar3"></i> ' . count($target_map) . ' bulan ditargetkan',
                'badge_cls' => 'on-track',
            ];
        } else {
            $hero['note'] = 'Tentukan target per siswa lewat form di samping, lalu pantau progresnya di sini.';
        }

        return [
            'selected_periode'    => $selected_periode,
            'cari'                => $cari,
            'selected_periode_db' => $selected_periode_db,
            'back'                => $back,
            'page'                => $page,
            'total_data'          => $total_data,
            'total_pages'         => $total_pages,
            'offset'              => $offset,
            'data'                => $data,
            'start_item'          => $start_item,
            'end_item'            => $end_item,
            'pg_query'            => $pg_query,
            'daftar_siswa'        => $daftar_siswa,
            'pending_list'        => $pending_list,
            'target_map'          => $target_map,
            'target_collected'    => $target_collected,
            'jumlah_siswa'        => $jumlah_siswa,
            'period_collected'    => $period_collected,
            'period_target'       => $period_target,
            'period_per_siswa'    => $period_per_siswa,
            'target_total_all'    => $target_total_all,
            'target_collected_all'=> $target_collected_all,
            'hero'                => $hero,
        ];
    }
}
