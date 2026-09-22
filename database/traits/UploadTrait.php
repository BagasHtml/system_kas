<?php

trait UploadTrait
{
    /**
     * Simpan file upload gambar (bukti transfer / nota) dengan validasi
     * tipe asli lewat getimagesize(), batas ukuran, dan nama acak.
     *
     * @param array  $file     Entri $_FILES[...].
     * @param string $subdir   Subdirektori di bawah assets/uploads/.
     * @param string $prefix   Awalan nama file.
     * @param int    $maxBytes Batas ukuran file (default 2 MB).
     * @return array{ok: bool, path: string|null, error: string|null}
     */
    public static function saveImageUpload(array $file, string $subdir, string $prefix, int $maxBytes = 2097152): array
    {
        if (!isset($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'path' => null, 'error' => 'File gambar wajib diupload.'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'path' => null, 'error' => 'Gagal mengunggah file (kode ' . (int)$file['error'] . ').'];
        }
        if ((int)$file['size'] <= 0 || (int)$file['size'] > $maxBytes) {
            return ['ok' => false, 'path' => null, 'error' => 'Ukuran file maksimal ' . round($maxBytes / 1048576) . ' MB.'];
        }

        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            return ['ok' => false, 'path' => null, 'error' => 'File harus berupa gambar asli (JPG, PNG, atau WEBP).'];
        }

        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        $mime = $info['mime'] ?? '';
        if (!isset($mimeMap[$mime])) {
            return ['ok' => false, 'path' => null, 'error' => 'Format gambar harus JPG, PNG, atau WEBP.'];
        }

        $ext = $mimeMap[$mime];
        $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetDir = str_replace('\\', '/', dirname(__DIR__, 2)) . '/assets/uploads/' . $subdir . '/';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
            return ['ok' => false, 'path' => null, 'error' => 'Gagal menyimpan file. Silakan coba lagi.'];
        }

        return ['ok' => true, 'path' => 'assets/uploads/' . $subdir . '/' . $filename, 'error' => null];
    }
}