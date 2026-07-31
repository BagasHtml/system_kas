<?php
session_name('preview_sid');
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['siswa_id'] = 1;
$_SESSION['nama'] = 'Ahmad Fauzi';
$_SESSION['siswa_absen'] = 1;
include 'dashboard.php';
