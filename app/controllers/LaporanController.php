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

    /**
     * Ekspor laporan pengeluaran ke CSV (mengikuti filter rentang tanggal yang
     * sama dengan halaman laporan). Mengakhiri script setelah mengirim file.
     */
    public static function export()
    {
        AuthController::requireAdmin();

        date_default_timezone_set('Asia/Jakarta');

        $db = new Koneksi();

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
        $res = $db::q(
            "SELECT id, keterangan, jumlah, kategori, tanggal
             FROM pengeluaran $whereSql ORDER BY tanggal DESC, id DESC",
            $params
        );
        if ($res) {
            $pengeluaran = $res->fetch_all(MYSQLI_ASSOC);
        }

        $pengeluaran_total = 0.0;
        foreach ($pengeluaran as $e) {
            $pengeluaran_total += (float)$e['jumlah'];
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="laporan_pengeluaran_' . substr($dari, 0, 10) . '_' . substr($sampai, 0, 10) . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM untuk Excel

        fputcsv($output, ['LAPORAN PENGELUARAN KAS KELAS']);
        fputcsv($output, ['Periode', date('d/m/Y', strtotime($dari)) . ' - ' . date('d/m/Y', strtotime($sampai))]);
        fputcsv($output, ['Tanggal Ekspor', date('d/m/Y H:i')]);
        fputcsv($output, []);
        fputcsv($output, ['No', 'Tanggal', 'Kategori', 'Keterangan', 'Jumlah (Rp)']);

        $no = 1;
        foreach ($pengeluaran as $e) {
            fputcsv($output, [
                $no++,
                date('d/m/Y', strtotime($e['tanggal'])),
                $e['kategori'] ?? 'Lainnya',
                $e['keterangan'],
                (float)$e['jumlah'],
            ]);
        }

        fputcsv($output, []);
        fputcsv($output, ['Total Pengeluaran', '', '', '', $pengeluaran_total]);

        fclose($output);
        exit;
    }
}
