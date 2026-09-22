<?php
require_once __DIR__ . '/../../app/controllers/AuthController.php';
AuthController::requireSiswa();
require_once '../../database/db.php';

$id = max(0, (int)($_GET['id'] ?? 0));
$my_id = (int)($_SESSION['siswa_id'] ?? 0);
$is_bendahara = !empty($_SESSION['is_bendahara']);

$res = Koneksi::q(
    "SELECT p.*, s.nama, s.nomor_absen
     FROM pembayaran p
     INNER JOIN siswa s ON s.id = p.siswa_id
     WHERE p.id = ? AND p.status = 'lunas'
     LIMIT 1",
    [$id]
);
if (!$res || $res->num_rows === 0) {
    header("Location: dashboard.php");
    exit;
}
$r = $res->fetch_assoc();
if (!$r || (!$is_bendahara && (int)$r['siswa_id'] !== $my_id)) {
    header("Location: dashboard.php");
    exit;
}

$metodeLabel = [
    'langsung' => 'Tunai / Langsung',
    'dana'     => 'Transfer DANA',
    'qris'     => 'QRIS',
];
$metode = $metodeLabel[$r['metode'] ?? 'langsung'] ?? 'Langsung';
$nomor_struk = 'KAS-' . str_pad((string)$id, 5, '0', STR_PAD_LEFT) . '-' . $r['periode'];
$tanggal_terbit = date('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran Kas - <?= htmlspecialchars($r['nama']) ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI', Roboto, Arial, sans-serif; background:#eef1f4; color:#1f2937; }
        .wrap { max-width:420px; margin:32px auto; padding:0 12px; }
        .toolbar { display:flex; gap:8px; justify-content:space-between; margin-bottom:14px; }
        .btn { display:inline-flex; align-items:center; gap:6px; padding:9px 14px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; cursor:pointer; border:none; }
        .btn-primary { background:#00A37A; color:#fff; }
        .btn-light { background:#fff; color:#374151; border:1px solid #d7dde3; }
        .receipt { background:#fff; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.06); overflow:hidden; }
        .rc-head { background:#00A37A; color:#fff; padding:22px 24px; display:flex; justify-content:space-between; align-items:center; }
        .rc-head .brand { font-weight:800; font-size:17px; letter-spacing:.2px; }
        .rc-head .brand small { display:block; font-weight:500; font-size:11px; opacity:.85; }
        .rc-head .no { text-align:right; font-size:11px; }
        .rc-head .no b { display:block; font-size:15px; letter-spacing:.5px; }
        .rc-body { padding:20px 24px 22px; }
        .rc-title { font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:#9aa3ad; font-weight:700; margin-bottom:14px; }
        .rc-student { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
        .rc-avatar { width:44px; height:44px; border-radius:50%; background:#e6f7f1; color:#00A37A; font-weight:800; display:flex; align-items:center; justify-content:center; font-size:18px; }
        .rc-avatar + div b { display:block; font-size:15px; }
        .rc-avatar + div span { font-size:12px; color:#9aa3ad; }
        .rc-rows { border-top:1px dashed #e2e7ec; }
        .rc-row { display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid #f1f4f7; font-size:13px; }
        .rc-row .lbl { color:#9aa3ad; }
        .rc-row .val { font-weight:600; text-align:right; }
        .rc-row .val.amount { font-size:16px; font-weight:800; color:#059669; }
        .rc-foot { padding:16px 24px; background:#f8fafc; border-top:1px solid #eef2f5; font-size:11px; color:#9aa3ad; text-align:center; }
        .status-lunas { display:inline-flex; align-items:center; gap:5px; font-weight:700; font-size:12px; color:#059669; }
        @media print {
            body { background:#fff; }
            .wrap { margin:0; max-width:none; }
            .toolbar { display:none; }
            .receipt { box-shadow:none; border-radius:0; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="toolbar">
            <a class="btn btn-light" href="dashboard.php">&larr; Kembali ke Dashboard</a>
            <button class="btn btn-primary" onclick="window.print()">Cetak / Simpan PDF</button>
        </div>

        <div class="receipt">
            <div class="rc-head">
                <div class="brand">Kas Kelas<small>Struk Pembayaran Kas Kelas</small></div>
                <div class="no">Nomor Struk<b><?= htmlspecialchars($nomor_struk) ?></b></div>
            </div>

            <div class="rc-body">
                <div class="rc-title">Penerima Setoran</div>
                <div class="rc-student">
                    <div class="rc-avatar"><?= strtoupper(substr($r['nama'], 0, 1)) ?></div>
                    <div>
                        <b><?= htmlspecialchars($r['nama']) ?></b>
                        <span>Nomor absen <?= (int)$r['nomor_absen'] ?></span>
                    </div>
                </div>

                <div class="rc-rows">
                    <div class="rc-row">
                        <span class="lbl">Bulan Pembayaran</span>
                        <span class="val"><?= htmlspecialchars(Koneksi::periodeLabel($r['periode'])) ?></span>
                    </div>
                    <div class="rc-row">
                        <span class="lbl">Metode</span>
                        <span class="val"><?= htmlspecialchars($metode) ?></span>
                    </div>
                    <div class="rc-row">
                        <span class="lbl">Tanggal Dibayar</span>
                        <span class="val"><?= date('d/m/Y', strtotime($r['tanggal_bayar'])) ?></span>
                    </div>
                    <div class="rc-row">
                        <span class="lbl">Status</span>
                        <span class="val"><span class="status-lunas">&#10003; Lunas</span></span>
                    </div>
                    <div class="rc-row">
                        <span class="lbl">Jumlah Pembayaran</span>
                        <span class="val amount"><?= Koneksi::rupiah((float)$r['jumlah']) ?></span>
                    </div>
                </div>
            </div>

            <div class="rc-foot">
                Diterbitkan <?= $tanggal_terbit ?> &middot; Bukti resmi pembayaran kas kelas &middot; Verifikasi oleh bendahara
            </div>
        </div>
    </div>
</body>
</html>