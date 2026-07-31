<?php
session_name('preview_sid');
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['username'] = 'admin';
include 'dashboard.php';
