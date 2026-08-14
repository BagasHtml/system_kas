<?php

require_once __DIR__ . '/AuthController.php';

/**
 * Tanggung jawab: siapkan data laporan (matriks pembayaran + ringkasan) admin.
 */
class LaporanController
{
    public static function handle(): array
    {
        AuthController::requireAdmin();

        $db = new Koneksi();

        $resSiswa = $db::q("SELECT id, nama, nomor_absen FROM siswa ORDER BY nomor_absen ASC");
        $siswa = $resSiswa ? $resSiswa->fetch_all(MYSQLI_ASSOC) : [];

        $resPeriode = $db::q("SELECT DISTINCT periode FROM pembayaran ORDER BY periode ASC");
        $periode_list = [];
        if ($resPeriode) {
            while ($r = $resPeriode->fetch_assoc()) {
                $periode_list[] = $r['periode'];
            }
        }

        $map = [];
        $resMap = $db::q("SELECT siswa_id, periode, status FROM pembayaran");
        if ($resMap) {
            while ($r = $resMap->fetch_assoc()) {
                $map[$r['siswa_id']][$r['periode']] = $r['status'];
            }
        }

        $resSum = $db::q("SELECT
            IFNULL(SUM(CASE WHEN status = 'lunas' THEN jumlah ELSE 0 END), 0) AS pemasukan,
            IFNULL(SUM(jumlah), 0) AS total_tagihan,
            IFNULL(SUM(CASE WHEN status = 'belum' THEN jumlah ELSE 0 END), 0) AS belum_bayar
            FROM pembayaran");
        $sum = $resSum ? $resSum->fetch_assoc() : ['pemasukan' => 0, 'total_tagihan' => 0, 'belum_bayar' => 0];

        $resOut = $db::q("SELECT IFNULL(SUM(jumlah), 0) AS total FROM pengeluaran");
        $pengeluaran_total = $resOut ? (float)$resOut->fetch_assoc()['total'] : 0;

        $saldo = (float)$sum['pemasukan'] - $pengeluaran_total;

        /* Target kas (kesepakatan kelas): sisa target bila target sudah ditetapkan. */
        $target_map = Koneksi::targetMap();
        $total_target = Koneksi::totalTarget($target_map);
        $ada_target = $total_target > 0;
        $belum_bayar = $ada_target
            ? max(0, $total_target - (float)$sum['pemasukan'])
            : (float)$sum['belum_bayar'];

        $jumlah_siswa = Koneksi::jumlahSiswa();
        $kas_per_siswa = null;
        if ($ada_target && $target_map) {
            $latest_tp = max(array_keys($target_map));
            $kas_per_siswa = (float)$target_map[$latest_tp]['per_siswa'];
        }

        return [
            'siswa'              => $siswa,
            'periode_list'       => $periode_list,
            'map'                => $map,
            'sum'                => $sum,
            'pengeluaran_total'  => $pengeluaran_total,
            'saldo'              => $saldo,
            'target_map'         => $target_map,
            'total_target'       => $total_target,
            'ada_target'         => $ada_target,
            'belum_bayar'        => $belum_bayar,
            'jumlah_siswa'       => $jumlah_siswa,
            'kas_per_siswa'      => $kas_per_siswa,
        ];
    }
}
