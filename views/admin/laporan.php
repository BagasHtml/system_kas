<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$title = 'Laporan - Admin';
$active = 'laporan';
include '../partials/header.php';
include '../partials/admin_sidebar.php';
include '../partials/helpers.php';
include_once '../../database/db.php';

$db = new Koneksi();

$resSiswa = $db::q("SELECT id, nama, nomor_absen FROM siswa ORDER BY nomor_absen ASC");
$siswa = $resSiswa ? $resSiswa->fetch_all(MYSQLI_ASSOC) : [];

$resPeriode = $db::q("SELECT DISTINCT periode FROM pembayaran ORDER BY periode ASC");
$periode_list = [];
if ($resPeriode) {
    while ($r = $resPeriode->fetch_assoc()) {
        $periode_list[] = $r['periode'];
    }
}

$map = [];
$resMap = $db::q("SELECT siswa_id, periode, status FROM pembayaran");
if ($resMap) {
    while ($r = $resMap->fetch_assoc()) {
        $map[$r['siswa_id']][$r['periode']] = $r['status'];
    }
}

$resSum = $db::q("SELECT
    IFNULL(SUM(CASE WHEN status = 'lunas' THEN jumlah ELSE 0 END), 0) AS pemasukan,
    IFNULL(SUM(jumlah), 0) AS total_tagihan,
    IFNULL(SUM(CASE WHEN status = 'belum' THEN jumlah ELSE 0 END), 0) AS belum_bayar
    FROM pembayaran");
$sum = $resSum ? $resSum->fetch_assoc() : ['pemasukan' => 0, 'total_tagihan' => 0, 'belum_bayar' => 0];

$resOut = $db::q("SELECT IFNULL(SUM(jumlah), 0) AS total FROM pengeluaran");
$pengeluaran_total = $resOut ? (float)$resOut->fetch_assoc()['total'] : 0;

$saldo = (float)$sum['pemasukan'] - $pengeluaran_total;

$bulan_pendek = function ($periode) {
    $nama = explode(' ', trim($periode));
    $pendek = [
        'Januari' => 'Jan', 'Februari' => 'Feb', 'Maret' => 'Mar', 'April' => 'Apr',
        'Mei' => 'Mei', 'Juni' => 'Jun', 'Juli' => 'Jul', 'Agustus' => 'Ags',
        'September' => 'Sep', 'Oktober' => 'Okt', 'November' => 'Nov', 'Desember' => 'Des'
    ];
    $b = isset($pendek[$nama[0]]) ? $pendek[$nama[0]] : ($nama[0] ?? '');
    $t = $nama[1] ?? '';
    return trim($b . ' ' . $t);
};
?>

<div class="main-content">
    <div class="page-header">
        <div>
            <h4>Laporan Kas Kelas</h4>
            <div class="sub">Rekap pembayaran dan saldo kas kelas</div>
        </div>
        <button class="btn-primary-custom" onclick="window.print()">
            <i class="bi bi-printer"></i> Cetak
        </button>
    </div>

    <div class="dash-kpis" style="margin-bottom:20px;">
        <?php
        $kpis = [
            ['label' => 'Pemasukan (Lunas)', 'value' => rupiah((float)$sum['pemasukan']), 'tone' => 'success'],
            ['label' => 'Pengeluaran', 'value' => rupiah($pengeluaran_total), 'tone' => 'danger'],
            ['label' => 'Saldo Kas', 'value' => rupiah($saldo), 'tone' => 'accent'],
            ['label' => 'Belum Dibayar', 'value' => rupiah((float)$sum['belum_bayar']), 'tone' => 'warn'],
        ];
        foreach ($kpis as $k) {
            echo kpi($k);
        }
        ?>
    </div>

    <div class="lap-actions" style="margin-bottom:12px;">
        <button class="btn-outline-custom" onclick="window.print()"><i class="bi bi-printer"></i> Cetak</button>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h6>Matriks Pembayaran per Siswa</h6>
            <span style="font-size:11px;color:var(--text-secondary);"><?= count($siswa) ?> siswa &times; <?= count($periode_list) ?> periode</span>
        </div>
        <div class="table-responsive">
            <table class="table lap-matrix">
                <thead>
                    <tr>
                        <th style="width:44px;">No</th>
                        <th>Nama Siswa</th>
                        <?php foreach ($periode_list as $per): ?>
                            <th style="text-align:center;"><?= htmlspecialchars($bulan_pendek($per)) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($siswa) || empty($periode_list)): ?>
                        <tr>
                            <td colspan="<?= 2 + count($periode_list) ?>" style="text-align:center;padding:40px 0;color:var(--text-muted);">
                                <i class="bi bi-inbox" style="font-size:24px;display:block;margin-bottom:4px;"></i>
                                Belum ada data untuk dibuatkan laporan
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; ?>
                        <?php foreach ($siswa as $s): ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $no++ ?></td>
                                <td><span style="font-weight:600;"><?= htmlspecialchars($s['nama']) ?></span></td>
                                <?php foreach ($periode_list as $per): ?>
                                    <td style="text-align:center;">
                                        <?php $st = $map[$s['id']][$per] ?? 'belum'; ?>
                                        <?php if ($st === 'lunas'): ?>
                                            <span class="lap-cell-lunas"><i class="bi bi-check-circle-fill"></i> Lunas</span>
                                        <?php else: ?>
                                            <span class="lap-cell-belum"><i class="bi bi-x-circle-fill"></i> Belum</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
