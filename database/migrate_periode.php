<?php
/* Migrasi sekali jalan: ubah format periode "Mei 2026" menjadi "2026-05"
   agar pengurutan kronologis bekerja. Bisa dijalankan ulang dengan aman. */
include __DIR__ . '/db.php';

$map = [
    'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
    'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
    'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
];

$rows = Koneksi::q("SELECT id, periode FROM pembayaran");
if (!$rows || $rows->num_rows === 0) {
    echo "Tidak ada data pembayaran.\n";
    exit;
}

$updated = 0;
while ($r = $rows->fetch_assoc()) {
    $parts = explode(' ', trim($r['periode']));
    if (count($parts) === 2 && isset($map[$parts[0]]) && ctype_digit($parts[1])) {
        $ym = $parts[1] . '-' . $map[$parts[0]];
        if ($ym !== $r['periode']) {
            Koneksi::q("UPDATE pembayaran SET periode = ? WHERE id = ?", [$ym, (int)$r['id']]);
            $updated++;
        }
    }
}
echo "Migrasi selesai. $updated baris diperbarui.\n";
