<?php
/* Migrasi sekali jalan: ubah hash password admin lama (SHA2) ke password_hash().
   Jika akun admin memiliki password lain selain 'admin123', ganti baris
   username di bawah ini terlebih dahulu. */
include __DIR__ . '/db.php';

$rows = Koneksi::q("SELECT id, username, password FROM admin");
if (!$rows || $rows->num_rows === 0) {
    echo "Tidak ada akun admin.\n";
    exit;
}

while ($a = $rows->fetch_assoc()) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    Koneksi::q("UPDATE admin SET password = ? WHERE id = ?", [$hash, (int)$a['id']]);
    echo "Admin '{$a['username']}' (id {$a['id']}) password di-set ke password_hash('admin123').\n";
}
echo "Selesai.\n";
