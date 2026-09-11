<?php
require_once __DIR__ . '/AuthController.php';

class PengaturanController
{
    private static $keys = [
        'nama_sekolah'   => 'Nama Sekolah',
        'nama_kelas'     => 'Nama Kelas',
        'nomor_dana'     => 'Nomor Dana (Send Dana)',
        'atas_nama_dana' => 'Atas Nama Dana',
        'qris_path'      => 'Path Gambar QRIS',
    ];

    public static function handle(): array
    {
        AuthController::requireAdmin();
        $db = new Koneksi();

        if (isset($_POST['simpan_pengaturan'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token tidak valid.');
                header("Location: pengaturan.php");
                exit;
            }

            foreach (self::$keys as $key => $label) {
                if (isset($_POST[$key])) {
                    $val = trim($_POST[$key]);
                    if ($val !== '') {
                        Koneksi::setPengaturan($key, $val);
                    }
                }
            }

            // Handle QRIS upload
            if (isset($_FILES['qris_file']) && $_FILES['qris_file']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['qris_file']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $filename = 'qris_' . time() . '.' . $ext;
                    $targetDir = dirname(__DIR__, 2) . '/assets/uploads/qris/';
                    if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
                    $targetFile = $targetDir . $filename;
                    if (move_uploaded_file($_FILES['qris_file']['tmp_name'], $targetFile)) {
                        Koneksi::setPengaturan('qris_path', 'assets/uploads/qris/' . $filename);
                    }
                }
            }

            Koneksi::setFlash('success', 'Pengaturan berhasil disimpan.');
            header("Location: pengaturan.php");
            exit;
        }

        $settings = Koneksi::allPengaturan();
        return ['settings' => $settings];
    }
}