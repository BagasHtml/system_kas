<?php

/**
 * Smoke test ringan: memverifikasi koneksi database, keberadaan tabel inti,
 * kolom penting, dan keberadaan status 'pending' tanpa framework pengujian.
 *
 * Cara pakai dari root proyek:
 *   php tests/smoke.php
 *
 * Keluar dengan kode 0 bila semua lolos, selain itu 1.
 */

require_once __DIR__ . '/../database/db.php';

$fail = 0;

function check(string $label, bool $ok): void
{
    global $fail;
    if (!$ok) {
        $fail++;
    }
    echo ($ok ? 'OK  ' : 'FAIL') . " - {$label}\n";
}

function tableExists(Koneksi $db, string $table): bool
{
    if (!preg_match('/^[a-z_]+$/', $table)) return false;
    $r = $db::q("SHOW TABLES LIKE '{$table}'");
    return $r && $r->num_rows > 0;
}

function columnExists(Koneksi $db, string $table, string $col): bool
{
    if (!preg_match('/^[a-z_]+$/', $table) || !preg_match('/^[a-z_]+$/', $col)) return false;
    $r = $db::q("SHOW COLUMNS FROM {$table} LIKE '{$col}'");
    return $r && $r->num_rows > 0;
}

echo "Smoke test sistem kas kelas\n";
echo str_repeat('-', 40) . "\n";

$db = new Koneksi();

Koneksi::allSettings();

$tables = ['siswa', 'pembayaran', 'pengeluaran', 'target_kas', 'setting'];
foreach ($tables as $t) {
    check("tabel {$t} tersedia", tableExists($db, $t));
}

check("pembayaran -> kolom metode", columnExists($db, 'pembayaran', 'metode'));
check("pembayaran -> kolom bukti_transfer", columnExists($db, 'pembayaran', 'bukti_transfer'));
check("pembayaran -> kolom catatan", columnExists($db, 'pembayaran', 'catatan'));
check("pengeluaran -> kolom kategori", columnExists($db, 'pengeluaran', 'kategori'));
check("pengeluaran -> kolom bukti_nota", columnExists($db, 'pengeluaran', 'bukti_nota'));
check("siswa -> kolom role", columnExists($db, 'siswa', 'role'));
check("pembayaran -> tanpa kolom kategori (bug lama)", !columnExists($db, 'pembayaran', 'kategori'));

$enum = $db::q("SHOW COLUMNS FROM pembayaran LIKE 'status'")->fetch_assoc()['Type'] ?? '';
check("status ENUM memuat 'pending'", strpos((string)$enum, 'pending') !== false);

check("koneksi piranti keras berfungsi", $db::q("SELECT 1") !== false);

echo str_repeat('-', 40) . "\n";
if ($fail > 0) {
    echo "GAGAL: {$fail} pemeriksaan tidak lolos.\n";
    exit(1);
}
echo "SELURUHNYA LOLOS.\n";
exit(0);