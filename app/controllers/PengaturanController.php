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
            $upload_state = 'none';
            if (isset($_FILES['qris_file'])) {
                if ($_FILES['qris_file']['error'] === UPLOAD_ERR_OK) {
                    $upload_state = 'file';
                    $ext = strtolower(pathinfo($_FILES['qris_file']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $filename = 'qris_' . time() . '.' . $ext;
                        $targetDir = dirname(__DIR__, 2) . '/assets/uploads/qris/';
                        if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
                        $targetFile = $targetDir . $filename;
                        if (is_writable($targetDir) && move_uploaded_file($_FILES['qris_file']['tmp_name'], $targetFile)) {
                            Koneksi::setPengaturan('qris_path', 'assets/uploads/qris/' . $filename);
                            $upload_state = 'ok';
                        }
                    } else {
                        $upload_state = 'bad_type';
                    }
                } elseif ($_FILES['qris_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $upload_state = 'upload_fail';
                }
            }

            if ($upload_state === 'ok') {
                Koneksi::setFlash('success', 'Pengaturan disimpan & gambar QRIS baru berhasil diupload.');
            } elseif ($upload_state === 'bad_type') {
                Koneksi::setFlash('error', 'Gambar QRIS dilewati: format harus JPG, PNG, atau WebP. Pengaturan lain tetap disimpan.');
            } elseif ($upload_state === 'upload_fail') {
                Koneksi::setFlash('error', 'Gambar QRIS gagal diupload. Pastikan folder assets/uploads/qris bisa ditulis server. Pengaturan lain tetap disimpan.');
            } else {
                Koneksi::setFlash('success', 'Pengaturan berhasil disimpan.');
            }
            header("Location: pengaturan.php");
            exit;
        }

        $settings = Koneksi::allPengaturan();
        return ['settings' => $settings];
    }
}