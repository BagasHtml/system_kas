<?php

require_once __DIR__ . '/AuthController.php';

/**
 * Tanggung jawab: proses tambah/edit/hapus data siswa
 * serta data daftar siswa (cari + pagination) admin.
 */
class SiswaController
{
    public static function handle(): array
    {
        AuthController::requireAdmin();

        $db = new Koneksi();

        $cari = trim($_GET['cari'] ?? '');
        $back = $cari !== '' ? '?cari=' . urlencode($cari) : '';

        /* ===== HAPUS ===== */
        if (isset($_POST['hapus'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
                header("Location: siswa.php" . $back);
                exit;
            }
            $id = (int)$_POST['hapus'];
            if ($id > 0) {
                $db::q("DELETE FROM siswa WHERE id = ?", [$id]);
                Koneksi::setFlash('success', 'Data siswa berhasil dihapus.');
            }
            header("Location: siswa.php" . $back);
            exit;
        }

        /* ===== TAMBAH / EDIT ===== */
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
                header("Location: siswa.php" . $back);
                exit;
            }
            $id          = (int)($_POST['id'] ?? 0);
            $nama        = trim($_POST['nama'] ?? '');
            $nomor_absen = (int)($_POST['nomor_absen'] ?? 0);
            $role        = ($_POST['role'] ?? 'siswa') === (AuthController::ROLE_BENDAHARA) ? AuthController::ROLE_BENDAHARA : AuthController::ROLE_SISWA;

            if ($id > 0 && $id === (int)($_SESSION['siswa_id'] ?? 0)) {
                Koneksi::setFlash('error', 'Tidak dapat mengubah role akun sendiri.');
                header("Location: siswa.php" . $back);
                exit;
            }

            if ($nama === '' || $nomor_absen <= 0) {
                Koneksi::setFlash('error', 'Nama dan nomor absen wajib diisi.');
                header("Location: siswa.php" . $back);
                exit;
            }

            $dup = $db::q("SELECT id FROM siswa WHERE nomor_absen = ? AND id <> ? LIMIT 1", [$nomor_absen, $id]);
            if ($dup && $dup->num_rows > 0) {
                Koneksi::setFlash('error', 'Nomor absen ' . $nomor_absen . ' sudah dipakai siswa lain.');
                header("Location: siswa.php" . $back);
                exit;
            }

            if ($id > 0) {
                $db::q("UPDATE siswa SET nama = ?, nomor_absen = ?, role = ? WHERE id = ?", [$nama, $nomor_absen, $role, $id]);
                Koneksi::setFlash('success', 'Data siswa berhasil diperbarui.');
            } else {
                $db::q("INSERT INTO siswa (nama, nomor_absen, role) VALUES (?, ?, ?)", [$nama, $nomor_absen, $role]);
                Koneksi::setFlash('success', 'Data siswa berhasil ditambahkan.');
            }
            header("Location: siswa.php" . $back);
            exit;
        }

        /* ===== TAMPIL ===== */
        $per_page = 10;
        $page = max(1, (int)($_GET['hal'] ?? 1));

        $where = '';
        $params = [];
        if ($cari !== '') {
            $where = " WHERE nama LIKE ? OR nomor_absen LIKE ?";
            $params = ["%$cari%", "%$cari%"];
        }

        $jml = $db::q("SELECT COUNT(*) AS jml FROM siswa" . $where, $params)->fetch_assoc();
        $total_data = (int)($jml['jml'] ?? 0);
        $total_pages = max(1, (int)ceil($total_data / $per_page));
        $page = min($page, $total_pages);
        $offset = ($page - 1) * $per_page;

        $res = $db::q(
            "SELECT * FROM siswa" . $where . " ORDER BY nomor_absen ASC LIMIT ? OFFSET ?",
            array_merge($params, [$per_page, $offset])
        );
        $siswa = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        $start_item = $total_data === 0 ? 0 : $offset + 1;
        $end_item = min($offset + count($siswa), $total_data);
        $pg_query = http_build_query(['cari' => $cari]);

        return [
            'cari'        => $cari,
            'back'        => $back,
            'page'        => $page,
            'total_data'  => $total_data,
            'total_pages' => $total_pages,
            'offset'      => $offset,
            'siswa'       => $siswa,
            'start_item'  => $start_item,
            'end_item'    => $end_item,
            'pg_query'    => $pg_query,
        ];
    }
}
