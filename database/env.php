<?php

function KoneksiLoadEnv(string $baseDir): void
{
    $envFile = rtrim($baseDir, '/') . '/.env';
    if (!is_file($envFile)) {
        return;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }

        $pos = strpos($line, '=');
        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));

        if (isset($value[0]) && ($value[0] === '"' || $value[0] === "'") && $value[strlen($value) - 1] === $value[0]) {
            $value = substr($value, 1, -1);
        }

        $current = getenv($key);
        if ($current === false || $current === '') {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

KoneksiLoadEnv(dirname(__DIR__));