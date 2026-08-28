<?php
require_once __DIR__ . '/../../app/controllers/AuthController.php';
AuthController::requireAdmin();

require_once '../../database/db.php';
$db = new Koneksi();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan_kas_kelas_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header Title
fputcsv($output, ['LAPORAN REKAPITULASI KAS KELAS']);
fputcsv($output, ['Tanggal Ekspor', date('d/m/Y H:i')]);
fputcsv($output, []);

// Summary Financials
$pemasukan = (float)($db::q("SELECT COALESCE(SUM(jumlah), 0) t FROM pembayaran WHERE status = 'lunas'")->fetch_assoc()['t'] ?? 0);
$pengeluaran = (float)($db::q("SELECT COALESCE(SUM(jumlah), 0) t FROM pengeluaran")->fetch_assoc()['t'] ?? 0);
$saldo = $pemasukan - $pengeluaran;

fputcsv($output, ['RINGKASAN KEUANGAN']);
fputcsv($output, ['Total Pemasukan (Lunas)', $pemasukan]);
fputcsv($output, ['Total Pengeluaran', $pengeluaran]);
fputcsv($output, ['Sisa Saldo Kas', $saldo]);
fputcsv($output, []);

// Matrix Payments Table
fputcsv($output, ['MATRIKS PEMBAYARAN SISWA']);
$resPeriode = $db::q("SELECT DISTINCT periode FROM pembayaran ORDER BY periode ASC");
$periode_list = [];
if ($resPeriode) {
    while ($r = $resPeriode->fetch_assoc()) {
        $periode_list[] = $r['periode'];
    }
}

$headerRow = ['No', 'Nomor Absen', 'Nama Siswa'];
foreach ($periode_list as $p) {
    $headerRow[] = Koneksi::periodeLabel($p);
}
fputcsv($output, $headerRow);

$resSiswa = $db::q("SELECT id, nama, nomor_absen FROM siswa ORDER BY nomor_absen ASC");
$siswa = $resSiswa ? $resSiswa->fetch_all(MYSQLI_ASSOC) : [];

$map = [];
$resMap = $db::q("SELECT siswa_id, periode, status FROM pembayaran");
if ($resMap) {
    while ($r = $resMap->fetch_assoc()) {
        $map[$r['siswa_id']][$r['periode']] = $r['status'];
    }
}

$no = 1;
foreach ($siswa as $s) {
    $row = [$no++, $s['nomor_absen'], $s['nama']];
    foreach ($periode_list as $p) {
        $st = $map[$s['id']][$p] ?? 'belum';
        $row[] = ($st === 'lunas') ? 'LUNAS' : (($st === 'pending') ? 'PENDING' : 'BELUM');
    }
    fputcsv($output, $row);
}

fputcsv($output, []);
fputcsv($output, ['DAFTAR PENGELUARAN KAS']);
fputcsv($output, ['No', 'Tanggal', 'Kategori', 'Keterangan', 'Jumlah (Rp)']);

$resExp = $db::q("SELECT tanggal, kategori, keterangan, jumlah FROM pengeluaran ORDER BY tanggal DESC");
if ($resExp) {
    $expNo = 1;
    while ($e = $resExp->fetch_assoc()) {
        fputcsv($output, [
            $expNo++,
            date('d/m/Y', strtotime($e['tanggal'])),
            $e['kategori'] ?? 'Lainnya',
            $e['keterangan'],
            (float)$e['jumlah']
        ]);
    }
}

fclose($output);
exit;
