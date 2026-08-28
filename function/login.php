<?php
// Login admin terpisah sudah dihapus. Semua login kini lewat form siswa
// (nama + nomor absen, dipanggil via function/search.php).
// Endpoint ini hanya mengalihkan kembali ke halaman utama.
if (session_status() === PHP_SESSION_NONE) session_start();
header("Location: ../index.php");
exit;
