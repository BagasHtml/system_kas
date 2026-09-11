<?php
require_once __DIR__ . '/AuthController.php';

class BelanjaController
{
    public static function handle(): array
    {
        AuthController::requireAdmin();
        $db = new Koneksi();

        // Auto-create tables if not exist
        try {
            $db::q("SELECT 1 FROM target_belanja LIMIT 1");
        } catch (\Throwable $e) {
            run_migration_belanja();
        }

        /* ===== SIMPAN TARGET BELANJA ===== */
        if (isset($_POST['simpan_belanja'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token keamanan tidak valid.');
                header("Location: belanja.php");
                exit;
            }
            $id         = (int)($_POST['id'] ?? 0);
            $nama_barang = trim($_POST['nama_barang'] ?? '');
            $keterangan  = trim($_POST['keterangan'] ?? '');
            $target      = (float)preg_replace('/[^\d]/', '', (string)($_POST['target'] ?? ''));
            $ps_raw      = trim($_POST['per_siswa'] ?? '');
            $per_siswa   = $ps_raw === '' ? null : (float)preg_replace('/[^\d]/', '', $ps_raw);

            if ($nama_barang === '' || $target <= 0) {
                Koneksi::setFlash('error', 'Nama barang dan target wajib diisi.');
                header("Location: belanja.php");
                exit;
            }

            $n = Koneksi::jumlahSiswa();
            if ($per_siswa === null || $per_siswa <= 0) {
                $per_siswa = $n > 0 ? round($target / $n) : $target;
            }

            if ($id > 0) {
                $db::q(
                    "UPDATE target_belanja SET nama_barang = ?, keterangan = ?, target = ?, per_siswa = ? WHERE id = ?",
                    [$nama_barang, $keterangan !== '' ? $keterangan : null, $target, $per_siswa, $id]
                );
                Koneksi::setFlash('success', 'Target belanja berhasil diperbarui.');
            } else {
                $db::q(
                    "INSERT INTO target_belanja (nama_barang, keterangan, target, per_siswa) VALUES (?, ?, ?, ?)",
                    [$nama_barang, $keterangan !== '' ? $keterangan : null, $target, $per_siswa]
                );
                Koneksi::setFlash('success', 'Target belanja berhasil ditambahkan.');
            }
            header("Location: belanja.php");
            exit;
        }

        /* ===== HAPUS TARGET BELANJA ===== */
        if (isset($_POST['hapus_belanja'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token tidak valid.');
                header("Location: belanja.php");
                exit;
            }
            $id = (int)$_POST['hapus_belanja'];
            if ($id > 0) {
                $db::q("DELETE FROM target_belanja WHERE id = ?", [$id]);
                Koneksi::setFlash('success', 'Target belanja berhasil dihapus.');
            }
            header("Location: belanja.php");
            exit;
        }

        /* ===== CATAT SETORAN ===== */
        if (isset($_POST['simpan_setoran'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token tidak valid.');
                header("Location: belanja.php");
                exit;
            }
            $item_id  = (int)($_POST['item_id'] ?? 0);
            $siswa_id = (int)($_POST['siswa_id'] ?? 0);
            $jumlah   = (float)($_POST['jumlah'] ?? 0);
            $metode   = in_array($_POST['metode'] ?? '', ['langsung','qris','dana'], true) ? $_POST['metode'] : 'langsung';
            $catatan  = trim($_POST['catatan'] ?? '');
            $tanggal  = trim($_POST['tanggal_setor'] ?? '') ?: date('Y-m-d');

            $item = Koneksi::targetBelanjaItem($item_id);
            if (!$item || $item['status'] === 'terbeli') {
                Koneksi::setFlash('error', 'Item tidak valid atau sudah dibeli.');
                header("Location: belanja.php");
                exit;
            }
            if ($siswa_id <= 0 || $jumlah <= 0) {
                Koneksi::setFlash('error', 'Pilih siswa dan isi jumlah yang valid.');
                header("Location: belanja.php");
                exit;
            }

            $db::q(
                "INSERT INTO setoran_belanja (target_belanja_id, siswa_id, jumlah, metode, catatan, tanggal) VALUES (?, ?, ?, ?, ?, ?)",
                [$item_id, $siswa_id, $jumlah, $metode, $catatan !== '' ? $catatan : null, $tanggal]
            );
            Koneksi::setFlash('success', 'Setoran berhasil dicatat.');
            header("Location: belanja.php#item-" . $item_id);
            exit;
        }

        /* ===== HAPUS SETORAN ===== */
        if (isset($_POST['hapus_setoran'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token tidak valid.');
                header("Location: belanja.php");
                exit;
            }
            $id = (int)$_POST['hapus_setoran'];
            if ($id > 0) {
                $db::q("DELETE FROM setoran_belanja WHERE id = ?", [$id]);
                Koneksi::setFlash('success', 'Setoran berhasil dihapus.');
            }
            header("Location: belanja.php");
            exit;
        }

        /* ===== CATAT PEMBELIAN (no minus) ===== */
        if (isset($_POST['beli_belanja'])) {
            if (!Koneksi::csrfCheck()) {
                Koneksi::setFlash('error', 'Token tidak valid.');
                header("Location: belanja.php");
                exit;
            }
            $item_id  = (int)$_POST['beli_belanja'];
            $item     = Koneksi::targetBelanjaItem($item_id);

            if (!$item) {
                Koneksi::setFlash('error', 'Item tidak ditemukan.');
                header("Location: belanja.php");
                exit;
            }
            if ($item['status'] !== 'tercapai') {
                Koneksi::setFlash('error', 'Belum bisa dibeli: target belum terkumpul.');
                header("Location: belanja.php");
                exit;
            }

            $jumlah_beli = (float)($_POST['jumlah_beli'] ?? 0);
            if ($jumlah_beli <= 0) $jumlah_beli = $item['target'];
            $tanggal_beli = trim($_POST['tanggal_beli'] ?? '') ?: date('Y-m-d');

            if ($jumlah_beli > $item['sisa']) {
                Koneksi::setFlash('error', 'Jumlah pembelian melebihi dana tersisa (' . Koneksi::rupiah($item['sisa']) . ').');
                header("Location: belanja.php");
                exit;
            }

            $keterangan = 'Pembelian ' . $item['nama_barang'];
            $db::q(
                "INSERT INTO pengeluaran (tanggal, keterangan, jumlah, kategori, target_belanja_id) VALUES (?, ?, ?, 'Belanja Kelas', ?)",
                [$tanggal_beli, $keterangan, $jumlah_beli, $item_id]
            );
            $db::q("UPDATE target_belanja SET status = 'terbeli' WHERE id = ?", [$item_id]);
            Koneksi::setFlash('success', 'Pembelian ' . $item['nama_barang'] . ' berhasil dicatat.');
            header("Location: belanja.php");
            exit;
        }

        /* ===== DATA TAMPILAN ===== */
        $items           = Koneksi::targetBelanjaList();
        $items_progress  = [];
        foreach ($items as $it) {
            $items_progress[] = [
                'item'     => $it,
                'students' => Koneksi::targetBelanjaProgress($it['id'], $it['per_siswa']),
            ];
        }

        $daftar_siswa = [];
        $rs = $db::q("SELECT id, nama, nomor_absen FROM siswa ORDER BY nomor_absen ASC");
        if ($rs) $daftar_siswa = $rs->fetch_all(MYSQLI_ASSOC);

        $saldo_kas       = Koneksi::saldoKasUtama();
        $total_pemasukan = Koneksi::totalPemasukanUtama();

        $recent_setoran = $db::q(
            "SELECT sb.*, s.nama, s.nomor_absen, tb.nama_barang
             FROM setoran_belanja sb
             JOIN siswa s ON s.id = sb.siswa_id
             JOIN target_belanja tb ON tb.id = sb.target_belanja_id
             ORDER BY sb.id DESC LIMIT 10"
        )->fetch_all(MYSQLI_ASSOC);

        return [
            'items_progress' => $items_progress,
            'daftar_siswa'   => $daftar_siswa,
            'saldo_kas'      => $saldo_kas,
            'total_pemasukan'=> $total_pemasukan,
            'recent_setoran' => $recent_setoran,
        ];
    }
}