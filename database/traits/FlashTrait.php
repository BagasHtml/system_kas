<?php

/**
 * Tanggung jawab: pesan flash (notifikasi sekali tampil).
 */

trait FlashTrait
{
    /**
     * Simpan pesan flash.
     *
     * @param string $type    'success' | 'error' | lainnya dianggap 'info'.
     * @param string $message Isi pesan.
     */
    public static function setFlash(string $type, string $message)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    /**
     * Ambil seluruh pesan flash, lalu kosongkan dari sesi.
     */
    public static function getFlash()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }

    /**
     * Render semua pesan flash sebagai elemen <div class="flash ...">.
     */
    public static function renderFlash()
    {
        foreach (self::getFlash() as $f) {
            $cls = $f['type'] === 'success' ? 'flash-success'
                 : ($f['type'] === 'error' ? 'flash-error'
                 : 'flash-info');
            echo '<div class="flash ' . $cls . '">' . htmlspecialchars($f['message']) . '</div>';
        }
    }
}
