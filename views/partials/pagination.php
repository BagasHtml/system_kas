<?php
/* ===== Pagination UI =====
   Expects:
     $page     (int, halaman aktif)
     $total_pages (int)
     $pg_query (string, extra GET params tanpa '?'; bisa kosong)
*/
if (!isset($page, $total_pages, $pg_query)) return;

$total_pages = max(1, (int)$total_pages);
$page        = max(1, min((int)$page, $total_pages));
if ($total_pages <= 1) return;

$href = function (int $hal) use ($pg_query): string {
    $qs = trim($pg_query);
    return ($qs === '' ? '?' : '?' . $qs . '&') . 'hal=' . $hal;
};

$links = [];
for ($i = 1; $i <= $total_pages; $i++) {
    if ($i === 1 || $i === $total_pages || abs($i - $page) <= 2) {
        $last = $links[count($links) - 1] ?? null;
        if ($last !== null && $last !== '...' && $last !== $i - 1) {
            $links[] = '...';
        }
        $links[] = $i;
    }
}
?>
<div class="dash-pagination">
    <span class="dash-pg-info">Halaman <?= $page ?> dari <?= $total_pages ?></span>
    <div class="dash-pg-nav">
        <a class="dash-pg-btn <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= $href(max(1, $page - 1)) ?>">Sebelumnya</a>
        <?php foreach ($links as $pg): ?>
            <?php if ($pg === '...'): ?>
                <span class="dash-pg-ellipsis">…</span>
            <?php else: ?>
                <a class="dash-pg-btn <?= $pg === $page ? 'active' : '' ?>" href="<?= $href($pg) ?>"><?= $pg ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
        <a class="dash-pg-btn <?= $page >= $total_pages ? 'disabled' : '' ?>" href="<?= $href(min($total_pages, $page + 1)) ?>">Berikutnya</a>
    </div>
</div>
