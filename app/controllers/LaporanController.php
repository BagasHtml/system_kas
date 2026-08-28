<?php

require_once __DIR__ . '/AuthController.php';

/**
 * Tanggung jawab: siapkan data laporan pengeluaran (semua transaksi + filter
 * rentang tanggal) untuk admin. Matriks pembayaran tidak lagi ada di sini;
 * laporan berfokus pada pengeluaran.
 */
class LaporanController
{
    public static function handle(): array
    {
        AuthController::requireAdmin();

        date_default_timezone_set('Asia/Jakarta');

        $db = new Koneksi();

        /* Filter rentang tanggal (opsional). Default: otomatis dari pengeluaran
         * pertama hingga hari ini, sehingga tanggal selalu terisi saat mencetak. */
        $dari   = trim($_GET['dari'] ?? '');
        $sampai = trim($_GET['sampai'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari))   $dari = '';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) $sampai = '';

        if ($dari === '' || $sampai === '') {
            $range = $db::q("SELECT MIN(tanggal) AS mn, MAX(tanggal) AS mx FROM pengeluaran");
            $rn = $range ? $range->fetch_assoc() : null;
            if ($dari === '')   $dari = ($rn && $rn['mn']) ? $rn['mn'] : date('Y-m-d');
            if ($sampai === '') $sampai = ($rn && $rn['mx']) ? $rn['mx'] : date('Y-m-d');
        }

        $where  = [];
        $params = [];
        if ($dari !== '') {
            $where[]  = 'tanggal >= ?';
            $params[] = $dari;
        }
        if ($sampai !== '') {
            $where[]  = 'tanggal <= ?';
            $params[] = $sampai;
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $pengeluaran = [];
        if (empty($params)) {
            $res = $db::q(
                "SELECT id, keterangan, jumlah, kategori, tanggal, bukti_nota
                 FROM pengeluaran ORDER BY tanggal DESC, id DESC"
            );
        } else {
            $res = $db::q(
                "SELECT id, keterangan, jumlah, kategori, tanggal, bukti_nota
                 FROM pengeluaran $whereSql ORDER BY tanggal DESC, id DESC",
                $params
            );
        }
        if ($res) {
            $pengeluaran = $res->fetch_all(MYSQLI_ASSOC);
        }

        $pengeluaran_total = 0.0;
        $pengeluaran_count = count($pengeluaran);
        foreach ($pengeluaran as $e) {
            $pengeluaran_total += (float)$e['jumlah'];
        }

        /* Ringkasan saldo (dukungan untuk judul laporan). */
        $pemasukan = (float)($db::q(
            "SELECT IFNULL(SUM(jumlah), 0) t FROM pembayaran WHERE status = 'lunas'"
        )->fetch_assoc()['t'] ?? 0);
        $pengeluaran_all_total = (float)($db::q(
            "SELECT IFNULL(SUM(jumlah), 0) t FROM pengeluaran"
        )->fetch_assoc()['t'] ?? 0);
        $saldo = $pemasukan - $pengeluaran_all_total;

        return [
            'pengeluaran'          => $pengeluaran,
            'pengeluaran_total'    => $pengeluaran_total,
            'pengeluaran_count'    => $pengeluaran_count,
            'pengeluaran_all_total'=> $pengeluaran_all_total,
            'pemasukan'            => $pemasukan,
            'saldo'                => $saldo,
            'dari'                 => $dari,
            'sampai'               => $sampai,
            'total_kas_siswa'      => (int)Koneksi::jumlahSiswa(),
        ];
    }
}
