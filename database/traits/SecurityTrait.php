<?php

/**
 * Tanggung jawab: sanitasi output (XSS) dan proteksi CSRF.
 */

trait SecurityTrait
{
    /**
     * Bersihkan string sebelum ditampilkan di HTML (trim + escape).
     */
    public static function Xss(string $string)
    {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }

    /* ===== CSRF ===== */

    /**
     * Ambil token CSRF untuk sesi berjalan; buat baru jika belum ada.
     */
    public static function csrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    /**
     * Render input hidden berisi token CSRF untuk dipakai di dalam <form>.
     */
    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf" value="' . self::csrfToken() . '">';
    }

    /**
     * Validasi token CSRF dari $_POST terhadap token di sesi.
     */
    public static function csrfCheck(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_POST['csrf'])
            && isset($_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], $_POST['csrf']);
    }
}
