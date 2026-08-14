<?php
if (!defined('BASE_URL')) {
    $rootDir = str_replace('\\', '/', dirname(__DIR__, 2));
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    if ($docRoot && strpos($rootDir, $docRoot) === 0) {
        $baseUrl = substr($rootDir, strlen($docRoot));
    } else {
        $baseUrl = '/system_kas';
    }
    define('BASE_URL', rtrim($baseUrl, '/'));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistem Kas Kelas' ?></title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
