<?php

require_once __DIR__ . '/AuthController.php';

/**
 * Tanggung jawab: proses tambah/edit/hapus pengeluaran + data daftar pengeluaran admin.
 */
class PengeluaranController
{
    public static function handle(): array
    {
        AuthController::requireAdmin();

        $db = new Koneksi();

        $cari = trim($_GET['cari'] ?? '');
        $kat_filter = trim($_GET['kategori'] ?? '');
        $back_params = [];
        if ($cari !== '') $back_params['cari'] = $cari;
        if ($kat_filter !== '') $back_params['kategori'] = $kat_filter;
        $back = $back_params ? '?' . http_build_query($back_params) : '';

        /* ===== HAPUS ===== */
        if (isset($_POST['hapus'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
                header("Location: pengeluaran.php" . $back);
                exit;
            }
            $id = (int)$_POST['hapus'];
            if ($id > 0) {
                $db::q("DELETE FROM pengeluaran WHERE id = ?", [$id]);
                Koneksi::setFlash('success', 'Data pengeluaran berhasil dihapus.');
            }
            header("Location: pengeluaran.php" . $back);
            exit;
        }

        /* ===== TAMBAH / EDIT ===== */
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
                header("Location: pengeluaran.php" . $back);
                exit;
            }
            $id         = (int)($_POST['id'] ?? 0);
            $tanggal    = trim($_POST['tanggal'] ?? '');
            $keterangan = trim($_POST['keterangan'] ?? '');
            $jumlah     = (float)($_POST['jumlah'] ?? 0);
            $kategori   = trim($_POST['kategori'] ?? 'Lainnya');

            if ($tanggal === '' || $keterangan === '' || $jumlah <= 0) {
                Koneksi::setFlash('error', 'Tanggal, keterangan, dan jumlah wajib diisi.');
                header("Location: pengeluaran.php" . $back);
                exit;
            }

            // Proyeksi saldo untuk notifikasi "saldo tidak mencukupi"
            $income_total = (float)$db::q("SELECT COALESCE(SUM(jumlah),0) t FROM pembayaran WHERE status='lunas'")->fetch_assoc()['t'];
            $exp_total = (float)$db::q("SELECT COALESCE(SUM(jumlah),0) t FROM pengeluaran WHERE target_belanja_id IS NULL")->fetch_assoc()['t'];
            $old_jumlah = 0;
            if ($id > 0) {
                $old_res = $db::q("SELECT jumlah FROM pengeluaran WHERE id = ? AND target_belanja_id IS NULL", [$id]);
                if ($old_res) { $old_r = $old_res->fetch_assoc(); $old_jumlah = (float)($old_r['jumlah'] ?? 0); }
            }
            $projected_exp = $exp_total - $old_jumlah + $jumlah;
            $bal_insufficient = $income_total < $projected_exp;

            if ($bal_insufficient) {
                Koneksi::setFlash('error', 'Saldo tidak mencukupi: pengeluaran ' . number_format($jumlah, 0, ',', '.') . ' melebihi saldo kas yang tersedia. Transaksi dibatalkan.');
                header("Location: pengeluaran.php" . $back);
                exit;
            }

            // Handle Upload Bukti Nota
            $nota_path = null;
            if (isset($_FILES['bukti_nota']) && $_FILES['bukti_nota']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload = Koneksi::saveImageUpload($_FILES['bukti_nota'], 'bukti_nota', 'nota');
                if (!$upload['ok']) {
                    Koneksi::setFlash('error', $upload['error']);
                    header("Location: pengeluaran.php" . $back);
                    exit;
                }
                $nota_path = $upload['path'];
            }

            if ($id > 0) {
                if ($nota_path) {
                    $db::q("UPDATE pengeluaran SET tanggal = ?, keterangan = ?, jumlah = ?, kategori = ?, bukti_nota = ? WHERE id = ?", [$tanggal, $keterangan, $jumlah, $kategori, $nota_path, $id]);
                } else {
                    $db::q("UPDATE pengeluaran SET tanggal = ?, keterangan = ?, jumlah = ?, kategori = ? WHERE id = ?", [$tanggal, $keterangan, $jumlah, $kategori, $id]);
                }
                Koneksi::setFlash('success', 'Data pengeluaran berhasil diperbarui.');
            } else {
                $db::q("INSERT INTO pengeluaran (tanggal, keterangan, jumlah, kategori, bukti_nota) VALUES (?, ?, ?, ?, ?)", [$tanggal, $keterangan, $jumlah, $kategori, $nota_path]);
                Koneksi::setFlash('success', 'Data pengeluaran berhasil ditambahkan.');
            }
            header("Location: pengeluaran.php" . $back);
            exit;
        }

        /* ===== TAMPIL & KALKULASI SALDO ===== */
        $masuk_agg = $db::q("SELECT COALESCE(SUM(jumlah), 0) AS total FROM pembayaran WHERE status = 'lunas'")->fetch_assoc();
        $total_pemasukan = (float)($masuk_agg['total'] ?? 0);

        $exp_all = $db::q("SELECT COALESCE(SUM(jumlah), 0) AS total FROM pengeluaran WHERE target_belanja_id IS NULL")->fetch_assoc();
        $total_pengeluaran_all = (float)($exp_all['total'] ?? 0);

        $sisa_saldo_kas = $total_pemasukan - $total_pengeluaran_all;

        /* ===== TAMPIL ===== */
        $per_page = 10;
        $page = max(1, (int)($_GET['hal'] ?? 1));

        $where_clauses = ["target_belanja_id IS NULL"];
        $params = [];
        if ($cari !== '') {
            $where_clauses[] = "(keterangan LIKE ? OR tanggal LIKE ?)";
            $params[] = "%$cari%";
            $params[] = "%$cari%";
        }
        if ($kat_filter !== '') {
            $where_clauses[] = "kategori = ?";
            $params[] = $kat_filter;
        }

        $where = $where_clauses ? " WHERE " . implode(" AND ", $where_clauses) : '';

        $agg = $db::q(
            "SELECT COUNT(*) AS jml, COALESCE(SUM(jumlah), 0) AS total FROM pengeluaran" . $where,
            $params
        )->fetch_assoc();
        $total_data = (int)($agg['jml'] ?? 0);
        $total = (float)($agg['total'] ?? 0);
        $total_pages = max(1, (int)ceil($total_data / $per_page));
        $page = min($page, $total_pages);
        $offset = ($page - 1) * $per_page;

        $res = $db::q(
            "SELECT * FROM pengeluaran" . $where . " ORDER BY tanggal DESC, id DESC LIMIT ? OFFSET ?",
            array_merge($params, [$per_page, $offset])
        );
        $pengeluaran = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        $start_item = $total_data === 0 ? 0 : $offset + 1;
        $end_item = min($offset + count($pengeluaran), $total_data);
        $pg_query = http_build_query($back_params);

        return [
            'cari'                  => $cari,
            'kat_filter'            => $kat_filter,
            'back'                  => $back,
            'page'                  => $page,
            'total_data'            => $total_data,
            'total'                 => $total,
            'total_pages'           => $total_pages,
            'offset'                => $offset,
            'pengeluaran'           => $pengeluaran,
            'start_item'            => $start_item,
            'end_item'              => $end_item,
            'pg_query'              => $pg_query,
            'total_pemasukan'       => $total_pemasukan,
            'total_pengeluaran_all' => $total_pengeluaran_all,
            'sisa_saldo_kas'        => $sisa_saldo_kas,
        ];
    }
}
