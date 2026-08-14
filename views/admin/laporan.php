<?php
require_once __DIR__ . '/../../app/controllers/LaporanController.php';
extract(LaporanController::handle(), EXTR_SKIP);

$title = 'Laporan - Admin';
$active = 'laporan';

include '../partials/header.php';
include '../partials/admin_sidebar.php';
include '../partials/helpers.php';
?>

<div class="main-content dash-page">
    <div class="dash-topbar">
        <div>
            <h1 class="dash-title">Laporan Kas Kelas</h1>
            <p class="dash-subtitle">Rekap pembayaran dan saldo kas kelas</p>
        </div>
        <div class="dash-topbar-actions">
            <a href="export_excel.php" class="dash-btn dash-btn-light">
                <i class="bi bi-file-earmark-excel"></i> Ekspor Excel / CSV
            </a>
            <button class="dash-btn dash-btn-primary" onclick="window.print()">
                <?= ic('<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>', 15) ?> Cetak Laporan
            </button>
        </div>
    </div>

    <div class="dash-kpis">
        <?php
        $kpis = [
            ['label' => 'Pemasukan (Terkumpul)', 'value' => rupiah((float)$sum['pemasukan']), 'tone' => 'success'],
            ['label' => 'Pengeluaran', 'value' => rupiah($pengeluaran_total), 'tone' => 'danger'],
            ['label' => 'Saldo Kas', 'value' => rupiah($saldo), 'tone' => 'accent'],
            ['label' => 'Belum Terkumpul', 'value' => rupiah($belum_bayar), 'tone' => 'warn'],
            ['label' => 'Target Kas', 'value' => rupiah($total_target), 'tone' => 'info', 'note' => $ada_target && $kas_per_siswa !== null ? rupiah($kas_per_siswa) . ' x ' . $jumlah_siswa . ' siswa' : 'Belum ditetapkan'],
        ];
        foreach ($kpis as $k) {
            echo kpi($k);
        }
        ?>
    </div>

    <div class="dash-card">
        <div class="dash-card-head">
            <div>
                <div class="dash-card-title">Matriks Pembayaran per Siswa</div>
                <div class="dash-card-sub"><?= count($siswa) ?> siswa &times; <?= count($periode_list) ?> bulan</div>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="export_excel.php" class="dash-btn dash-btn-light">
                    <i class="bi bi-file-earmark-excel"></i> Ekspor CSV
                </a>
                <button class="dash-btn dash-btn-light" onclick="window.print()">
                    <?= ic('<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>', 15) ?> Cetak
                </button>
            </div>
        </div>
        <div class="dash-table-wrap">
            <table class="dash-table lap-matrix">
                <thead>
                    <tr>
                        <th style="width:44px;">No</th>
                        <th>Nama Siswa</th>
                        <?php foreach ($periode_list as $per): ?>
                            <th style="text-align:center;"><?= htmlspecialchars(Koneksi::periodeShortLabel($per)) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($siswa) || empty($periode_list)): ?>
                        <tr>
                            <td colspan="<?= 2 + count($periode_list) ?>">
                                <div class="dash-empty">
                                    <div class="t">Belum ada data</div>
                                    <div class="s">Belum ada data untuk dibuatkan laporan</div>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; ?>
                        <?php foreach ($siswa as $s): ?>
                            <tr>
                                <td style="color:var(--text-muted);"><?= $no++ ?></td>
                                <td>
                                    <div class="dash-cell-name">
                                        <div class="dash-cell-avatar" style="background:var(--accent-soft);color:var(--accent);">
                                            <?= strtoupper(substr($s['nama'], 0, 1)) ?>
                                        </div>
                                        <span class="nm"><?= htmlspecialchars($s['nama']) ?></span>
                                    </div>
                                </td>
                                <?php foreach ($periode_list as $per): ?>
                                    <td style="text-align:center;">
                                        <?php $st = $map[$s['id']][$per] ?? 'belum'; ?>
                                        <?php if ($st === 'lunas'): ?>
                                            <span class="lap-cell-lunas"><i class="bi bi-check-circle-fill"></i> Terkumpul</span>
                                        <?php else: ?>
                                            <span class="lap-cell-belum"><i class="bi bi-x-circle-fill"></i> Belum Terkumpul</span>
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
