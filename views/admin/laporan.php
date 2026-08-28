<?php
require_once __DIR__ . '/../../app/controllers/LaporanController.php';
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
    <!-- Header khusus saat cetak -->
    <div class="lap-print-header">
        <h1>Laporan Pengeluaran Kas Kelas</h1>
        <p>Periode: <?= htmlspecialchars($dari_label) ?> - <?= htmlspecialchars($sampai_label) ?></p>
        <p>Dicetak: <?= date('d M Y') ?></p>
    </div>

    <div class="dash-topbar">
        <div>
            <h1 class="dash-title">Laporan Pengeluaran Kas</h1>
            <p class="dash-subtitle">Periode <?= htmlspecialchars($dari_label) ?> - <?= htmlspecialchars($sampai_label) ?></p>
        </div>
        <div class="dash-topbar-actions">
            <button class="dash-btn dash-btn-primary" onclick="window.print()">
                <?= ic('<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>', 15) ?> Cetak
            </button>
        </div>
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
