<?php
require_once __DIR__ . '/../../app/controllers/LaporanController.php';

if (isset($_GET['export'])) {
    LaporanController::export();
}

extract(LaporanController::handle(), EXTR_SKIP);

$title = 'Laporan Pengeluaran';
$active = 'laporan';

include '../partials/header.php';
include '../partials/admin_sidebar.php';
include '../partials/helpers.php';

$dari_label  = $dari  ? date('d M Y', strtotime($dari))  : '';
$sampai_label = $sampai ? date('d M Y', strtotime($sampai)) : '';
?>

<div class="main-content dash-page">
    <div class="dash-topbar">
        <div>
            <h1 class="dash-title">Laporan Pengeluaran Kas</h1>
            <p class="dash-subtitle">Periode <?= htmlspecialchars($dari_label) ?> - <?= htmlspecialchars($sampai_label) ?></p>
        </div>
        <div class="dash-topbar-actions">
<<<<<<< HEAD
            <button type="button" class="dash-btn dash-btn-light" onclick="window.print()">
                <?= ic('<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8" rx="1"/>', 15) ?> Print
=======
            <a class="dash-btn dash-btn-light" href="export_excel.php"
               title="Unduh rekap kas kelas (matriks pembayaran + pengeluaran) dalam format CSV (dibuka di Excel)">
                <?= ic('<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>', 15) ?> Unduh Excel (CSV)
            </a>
            <button class="dash-btn dash-btn-primary" onclick="window.print()">
                <?= ic('<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>', 15) ?> Cetak
>>>>>>> d67dedf (update layout and added new system for manage admin dashboard and added fix more bugs and update layout and added readme)
            </button>
            <a class="dash-btn dash-btn-primary" href="laporan.php?export=1&dari=<?= urlencode($dari) ?>&sampai=<?= urlencode($sampai) ?>">
                <?= ic('<path d="M12 3v12"/><path d="m8 11 4 4 4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/>', 15) ?> Export CSV
            </a>
        </div>
    </div>

    <div class="lap-print-header">
        <h1>Laporan Pengeluaran Kas</h1>
        <p>Periode: <?= htmlspecialchars($dari_label ?: 'Awal') ?> - <?= htmlspecialchars($sampai_label ?: 'Terakhir') ?></p>
        <p>Total Pengeluaran: <?= rupiah($pengeluaran_total) ?></p>
        <p>Dicetak pada: <?= date('d M Y H:i') ?></p>
    </div>

    <div class="lap-filterbar">
        <form method="get" action="laporan.php">
            <label>
                <span>Dari</span>
                <input type="date" name="dari" value="<?= htmlspecialchars($dari) ?>">
            </label>
            <label>
                <span>Sampai</span>
                <input type="date" name="sampai" value="<?= htmlspecialchars($sampai) ?>">
            </label>
            <button type="submit" class="dash-btn dash-btn-primary">Tampilkan</button>
            <a href="laporan.php" class="dash-btn dash-btn-light">Reset</a>
        </form>
    </div>

    <div class="dash-card">
        <div class="dash-table-wrap">
            <table class="dash-table lap-table">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th style="width:100px;">Tanggal</th>
                        <th style="width:120px;">Kategori</th>
                        <th>Keterangan</th>
                        <th style="text-align:right;width:140px;padding-right:18px;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pengeluaran)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="dash-empty" style="padding:32px 16px;">
                                    <div class="t">Tidak ada data pengeluaran</div>
                                    <div class="s">Belum ada pengeluaran pada periode ini</div>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; ?>
                        <?php foreach ($pengeluaran as $e): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y', strtotime($e['tanggal'])) ?></td>
                                <td>
                                    <span class="lap-cat"><?= htmlspecialchars($e['kategori'] ?? 'Lainnya') ?></span>
                                </td>
                                <td><?= htmlspecialchars($e['keterangan']) ?></td>
                                <td class="num">- <?= rupiah((float)$e['jumlah']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($pengeluaran)): ?>
                <tfoot>
                    <tr>
                        <td colspan="4" class="num">Total Pengeluaran</td>
                        <td class="num">- <?= rupiah($pengeluaran_total) ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
