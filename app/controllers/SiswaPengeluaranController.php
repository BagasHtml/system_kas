<?php

require_once __DIR__ . '/AuthController.php';

/**
 * Tanggung jawab: siapkan data daftar pengeluaran + pagination untuk siswa.
 */
class SiswaPengeluaranController
{
    public static function handle(): array
    {
        AuthController::requireSiswa();

        $db = new Koneksi();

        $agg = $db::q("SELECT COALESCE(SUM(jumlah), 0) AS total, COUNT(*) AS jml FROM pengeluaran")->fetch_assoc();
        $total = (float)($agg['total'] ?? 0);
        $total_transaksi = (int)($agg['jml'] ?? 0);

        $masuk = $db::q("SELECT COALESCE(SUM(CASE WHEN status = 'lunas' THEN jumlah END), 0) AS t FROM pembayaran")
            ->fetch_assoc();
        $pemasukan = (float)($masuk['t'] ?? 0);
        $saldo = $pemasukan - $total;

        $per_page = 8;
        $page = max(1, (int)($_GET['hal'] ?? 1));
        $total_pages = max(1, (int)ceil($total_transaksi / $per_page));
        $page = min($page, $total_pages);
        $offset = ($page - 1) * $per_page;

        $pengeluaran = $db::q(
            "SELECT keterangan, jumlah, tanggal FROM pengeluaran ORDER BY tanggal DESC, id DESC LIMIT ? OFFSET ?",
            [$per_page, $offset]
        )->fetch_all(MYSQLI_ASSOC);

        $start_item = $total_transaksi === 0 ? 0 : $offset + 1;
        $end_item = min($offset + count($pengeluaran), $total_transaksi);

        $pages = [];
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i === 1 || $i === $total_pages || abs($i - $page) <= 2) {
                if (($pages[count($pages) - 1] ?? 0) !== $i - 1 && $pages[count($pages) - 1] !== '...') {
                    $pages[] = '...';
                }
                $pages[] = $i;
            }
        }

        $siswa_nama  = htmlspecialchars($_SESSION['nama'] ?? 'Siswa');
        $siswa_absen = htmlspecialchars($_SESSION['siswa_absen'] ?? '-');

        return [
            'total'            => $total,
            'total_transaksi'  => $total_transaksi,
            'pemasukan'        => $pemasukan,
            'saldo'            => $saldo,
            'page'             => $page,
            'total_pages'      => $total_pages,
            'offset'           => $offset,
            'pengeluaran'      => $pengeluaran,
            'start_item'       => $start_item,
            'end_item'         => $end_item,
            'pages'            => $pages,
            'siswa_nama'       => $siswa_nama,
            'siswa_absen'      => $siswa_absen,
        ];
    }
}
