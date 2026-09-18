<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="mobile-nav">
    <button type="button" class="mobile-hamb" aria-label="Buka menu" aria-expanded="false">
        <i class="bi bi-list"></i>
    </button>
    <span class="mobile-nav-brand">Kas Kelas</span>
    <?php if (!empty($mobile_user)): ?>
        <span class="mobile-nav-user"><?= htmlspecialchars($mobile_user) ?></span>
    <?php endif; ?>
    <a href="<?= $mobile_logout ?? '../../function/logout.php' ?>" class="mobile-nav-logout" title="Keluar">
        <i class="bi bi-box-arrow-right"></i>
    </a>
</nav>

<div class="mobile-drawer">
    <div class="mobile-drawer-backdrop" data-drawer-close></div>
    <aside class="mobile-drawer-panel">
        <div class="mobile-drawer-head">
            <div class="mobile-drawer-logo"><i class="bi bi-diamond-fill"></i></div>
            <div class="mobile-drawer-title">Kas Kelas</div>
            <button type="button" class="mobile-drawer-close" data-drawer-close aria-label="Tutup menu">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <?php if (!empty($mobile_user)): ?>
            <div class="mobile-drawer-user">
                <i class="bi bi-person-circle"></i>
                <div>
                    <span class="n"><?= htmlspecialchars($mobile_user) ?></span>
                    <?php if (!empty($mobile_user_role)): ?>
                        <span class="r"><?= htmlspecialchars($mobile_user_role) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mobile-drawer-links">
            <?php foreach ($mobile_links as $l): ?>
                <a class="<?= ($mobile_active ?? '') === $l['key'] ? 'active' : '' ?>" href="<?= $l['href'] ?>">
                    <i class="bi <?= $l['icon'] ?>"></i><span><?= $l['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="mobile-drawer-foot">
            <a href="<?= $mobile_logout ?? '../../function/logout.php' ?>" class="mobile-drawer-logout">
                <i class="bi bi-box-arrow-right"></i><span>Keluar</span>
            </a>
        </div>
    </aside>
</div>

<script>
(function () {
    const hamb   = document.querySelector('.mobile-hamb');
    const drawer = document.querySelector('.mobile-drawer');
    if (!hamb || !drawer) return;

    function open() {
        drawer.classList.add('open');
        hamb.setAttribute('aria-expanded', 'true');
        document.body.classList.add('mobile-drawer-open');
    }
    function close() {
        drawer.classList.remove('open');
        hamb.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('mobile-drawer-open');
    }

    hamb.addEventListener('click', function () {
        drawer.classList.contains('open') ? close() : open();
    });
    drawer.querySelectorAll('[data-drawer-close]').forEach(function (el) {
        el.addEventListener('click', close);
    });
    drawer.querySelectorAll('a[href]').forEach(function (a) {
        a.addEventListener('click', close);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') close();
    });
})();
</script>