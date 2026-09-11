<?php
require_once __DIR__ . '/AuthController.php';

class SiswaBelanjaController
{
    public static function handle(): array
    {
        AuthController::requireSiswa();
        $db = new Koneksi();
        $siswa_id = (int)($_SESSION['siswa_id'] ?? 0);

        /* ===== SETOR BELANJA (siswa submit) ===== */
        if (isset($_POST['setor_belanja'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token tidak valid.');
                header("Location: belanja.php");
                exit;
            }
            $item_id = (int)($_POST['item_id'] ?? 0);
            $jumlah  = (float)($_POST['jumlah'] ?? 0);
            $metode  = in_array($_POST['metode'] ?? '', ['langsung','qris','dana'], true) ? $_POST['metode'] : 'langsung';
            $catatan = trim($_POST['catatan'] ?? '');

            $item = Koneksi::targetBelanjaItem($item_id);
            if (!$item || $item['status'] === 'terbeli') {
                Koneksi::setFlash('error', 'Item tidak tersedia.');
                header("Location: belanja.php");
                exit;
            }
            if ($jumlah <= 0) {
                Koneksi::setFlash('error', 'Jumlah setoran minimal Rp 1.');
                header("Location: belanja.php");
                exit;
            }

            $db::q(
                "INSERT INTO setoran_belanja (target_belanja_id, siswa_id, jumlah, metode, catatan, tanggal) VALUES (?, ?, ?, ?, ?, CURDATE())",
                [$item_id, $siswa_id, $jumlah, $metode, $catatan !== '' ? $catatan : null]
            );
            Koneksi::setFlash('success', 'Setoran berhasil dikirim. Menunggu konfirmasi.');
            header("Location: belanja.php");
            exit;
        }

        /* ===== DATA TAMPILAN ===== */
        $items = Koneksi::targetBelanjaList();
        $siswa_nama  = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');
        $siswa_absen = htmlspecialchars($_SESSION['siswa_absen'] ?? '-');

        $items_my = [];
        foreach ($items as $it) {
            $pr = $db::q(
                "SELECT COALESCE(SUM(jumlah), 0) t FROM setoran_belanja WHERE target_belanja_id = ? AND siswa_id = ?",
                [$it['id'], $siswa_id]
            );
            $my_paid = (float)($pr->fetch_assoc()['t'] ?? 0);
            $share   = (float)$it['per_siswa'];
            $items_my[] = [
                'item'    => $it,
                'my_paid' => $my_paid,
                'my_share'=> $share,
                'my_kurang' => max(0, $share - $my_paid),
                'my_pct'  => $share > 0 ? min(100, (int)round($my_paid / $share * 100)) : 0,
            ];
        }

        return [
            'items_my'     => $items_my,
            'siswa_id'     => $siswa_id,
            'siswa_nama'   => $siswa_nama,
            'siswa_absen'  => $siswa_absen,
        ];
    }
}