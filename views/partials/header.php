<?php
if (!defined('BASE_URL')) {
    $rootPath = realpath(dirname(__DIR__, 2));
    $docRoot  = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    if ($docRoot && strpos($rootPath, $docRoot) === 0) {
        define('BASE_URL', rtrim(str_replace('\\', '/', substr($rootPath, strlen($docRoot))), '/'));
    } else {
        define('BASE_URL', '');
    }
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
