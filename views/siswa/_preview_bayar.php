<?php
$title = 'Preview - Bayar Online';
include '../partials/header.php';
include '../partials/helpers.php';
?>
<div class="dash-page" style="padding:28px;background:#f6f4f0;">
    <div class="dash-card dash-pay-card" id="bayar">
        <div class="dash-card-head">
            <div>
                <div class="dash-card-title">Bayar Kas Online</div>
                <div class="dash-card-sub">Transfer Send Dana atau scan QRIS untuk membayar kas kelas</div>
            </div>
        </div>

        <div class="dash-pay">
            <div class="dash-pay-method">
                <div class="dash-pay-brand dana">
                    <?= ic('<path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/>', 20) ?>
                    Send Dana
                </div>
                <div class="dash-pay-info">
                    <div class="dash-pay-row">
                        <span class="lbl">Nomor Dana</span>
                        <span class="val num">0813-2175-0459</span>
                    </div>
                    <div class="dash-pay-row">
                        <span class="lbl">Atas Nama</span>
                        <span class="val">Bagas Tresna Nanda MS</span>
                    </div>
                </div>
            </div>

            <div class="dash-pay-method">
                <div class="dash-pay-brand qris">
                    <?= ic('<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h4v4h-4zM19 19h2v2h-2z"/>', 20) ?>
                    QRIS
                </div>
                <div class="dash-pay-qris">
                    <img src="<?= BASE_URL ?>/assets/img/qris.png" alt="QRIS Kas Kelas">
                </div>
                <div class="dash-pay-hint">Scan kode di atas setelah transfer</div>
            </div>
        </div>
    </div>
</div>
<?php include '../partials/footer.php'; ?>
