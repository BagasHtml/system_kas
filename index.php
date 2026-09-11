<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$isLoggedIn = isset($_SESSION['siswa_id']);

require_once __DIR__ . '/database/db.php';

function rows($res): array
{
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function rp_short(float $n): string
{
    $neg = $n < 0;
    $n = abs($n);
    if ($n >= 1e9) {
        $s = rtrim(rtrim(number_format($n / 1e9, 1, ',', '.'), '0'), ',') . ' M';
    } elseif ($n >= 1e6) {
        $s = rtrim(rtrim(number_format($n / 1e6, 1, ',', '.'), '0'), ',') . ' jt';
    } elseif ($n >= 1e3) {
        $s = rtrim(rtrim(number_format($n / 1e3, 1, ',', '.'), '0'), ',') . ' rb';
    } else {
        $s = number_format($n, 0, ',', '.');
    }
    return ($neg ? '-Rp ' : 'Rp ') . $s;
}

function tgl_id(?string $d): string
{
    if (!$d) return '-';
    $b = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($d);
    return date('j', $t) . ' ' . $b[(int)date('n', $t)] . ' ' . date('Y', $t);
}

function trend_pct(float $cur, float $prev): int
{
    if ($prev > 0) return (int)round(($cur - $prev) / $prev * 100);
    return $cur > 0 ? 100 : 0;
}

$db = new Koneksi();

$total_siswa = (int)($db::q("SELECT COUNT(*) c FROM siswa")->fetch_assoc()['c'] ?? 0);

$agg = $db::q(
    "SELECT
        COUNT(CASE WHEN status = 'lunas' THEN 1 END) AS lunas_count,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) AS pending_count,
        COUNT(CASE WHEN status = 'belum' THEN 1 END) AS belum_count,
        COALESCE(SUM(CASE WHEN status = 'lunas' THEN jumlah END), 0) AS pemasukan
     FROM pembayaran"
)->fetch_assoc();
$pemasukan = (float)$agg['pemasukan'];
$lunas_count = (int)$agg['lunas_count'];

$pengeluaran_total = (float)$db::q("SELECT COALESCE(SUM(jumlah), 0) t FROM pengeluaran WHERE target_belanja_id IS NULL")->fetch_assoc()['t'];
$saldo = $pemasukan - $pengeluaran_total;

$bulan_id = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
$cur_periode = date('Y-m');
$start_6bln = date('Y-m-01', strtotime('-5 months'));

$months = [];
for ($i = 5; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i months"));
    $months[$ym] = ['label' => $bulan_id[(int)date('n', strtotime($ym . '-01'))], 'masuk' => 0.0, 'keluar' => 0.0];
}

foreach (rows($db::q(
    "SELECT DATE_FORMAT(tanggal_bayar, '%Y-%m') ym, COALESCE(SUM(jumlah), 0) t
     FROM pembayaran
     WHERE status = 'lunas' AND tanggal_bayar IS NOT NULL AND tanggal_bayar >= ?
     GROUP BY ym",
    [$start_6bln]
)) as $r) {
    if (isset($months[$r['ym']])) $months[$r['ym']]['masuk'] = (float)$r['t'];
}

foreach (rows($db::q(
    "SELECT DATE_FORMAT(tanggal, '%Y-%m') ym, COALESCE(SUM(jumlah), 0) t
     FROM pengeluaran
     WHERE tanggal >= ? AND target_belanja_id IS NULL
     GROUP BY ym",
    [$start_6bln]
)) as $r) {
    if (isset($months[$r['ym']])) $months[$r['ym']]['keluar'] = (float)$r['t'];
}

$masuk_vals = array_column($months, 'masuk');
$keluar_vals = array_column($months, 'keluar');
$max_chart = max(1, max($masuk_vals), max($keluar_vals));
$chart_any = array_sum($masuk_vals) + array_sum($keluar_vals) > 0;

$masuk_bulan_ini = $months[$cur_periode]['masuk'] ?? 0.0;
$keluar_bulan_ini = $months[$cur_periode]['keluar'] ?? 0.0;
$prev_ym = date('Y-m', strtotime('-1 month'));
$masuk_bulan_lalu = $months[$prev_ym]['masuk'] ?? 0.0;
$keluar_bulan_lalu = $months[$prev_ym]['keluar'] ?? 0.0;
$trend_masuk = trend_pct($masuk_bulan_ini, $masuk_bulan_lalu);
$trend_keluar = trend_pct($keluar_bulan_ini, $keluar_bulan_lalu);

$latest_pay_periode = $db::q("SELECT MAX(periode) p FROM pembayaran")->fetch_assoc()['p'] ?? null;
$latest_target_periode = $db::q("SELECT MAX(periode) p FROM target_kas")->fetch_assoc()['p'] ?? null;
$candidates = array_filter([$cur_periode, $latest_pay_periode, $latest_target_periode]);
$periode_aktif = $candidates ? max($candidates) : $cur_periode;
$periode_aktif_label = Koneksi::periodeLabel($periode_aktif);

$lunas_aktif = (int)($db::q(
    "SELECT COUNT(DISTINCT siswa_id) c FROM pembayaran WHERE periode = ? AND status = 'lunas'",
    [$periode_aktif]
)->fetch_assoc()['c'] ?? 0);
$belum_aktif = max(0, $total_siswa - $lunas_aktif);

$recent_pay = rows($db::q(
    "SELECT p.periode, p.jumlah, p.status, p.tanggal_bayar, s.nama, s.nomor_absen
     FROM pembayaran p
     INNER JOIN siswa s ON s.id = p.siswa_id
     ORDER BY p.id DESC LIMIT 8"
));

$periodes = array_column(rows($db::q(
    "SELECT DISTINCT periode FROM pembayaran ORDER BY periode DESC LIMIT 12"
)), 'periode');

$recent_exp = rows($db::q(
    "SELECT keterangan, jumlah, kategori, tanggal FROM pengeluaran ORDER BY tanggal DESC, id DESC LIMIT 6"
));

$exp_icons = [
    'Kebersihan' => 'trash-2',
    'Konsumsi' => 'coffee',
    'Dokumentasi' => 'camera',
    'Pemeliharaan' => 'wrench',
    'Kegiatan' => 'flag',
    'Alat Tulis' => 'pencil',
];

$payments_js = [];
foreach ($recent_pay as $p) {
    $payments_js[] = [
        'name' => $p['nama'],
        'absen' => (int)$p['nomor_absen'],
        'periode' => $p['periode'],
        'plabel' => Koneksi::periodeLabel($p['periode']),
        'amount' => (float)$p['jumlah'],
        'status' => $p['status'],
        'date' => tgl_id($p['tanggal_bayar']),
    ];
}

$expenses_js = [];
foreach ($recent_exp as $e) {
    $expenses_js[] = [
        'title' => $e['keterangan'],
        'amount' => (float)$e['jumlah'],
        'date' => tgl_id($e['tanggal']),
        'cat' => $e['kategori'] ?: 'Lainnya',
        'icon' => $exp_icons[$e['kategori']] ?? 'tag',
    ];
}

$targets_progress = [];
$total_siswa_t = max(1, $total_siswa);
foreach (Koneksi::targetMap() as $ym => $t) {
    $target_total = (float)$t['per_siswa'] * $total_siswa_t;
    if ($target_total <= 0) continue;
    $collected = (float)($db::q(
        "SELECT COALESCE(SUM(jumlah), 0) t FROM pembayaran WHERE periode = ? AND status = 'lunas'",
        [$ym]
    )->fetch_assoc()['t'] ?? 0);
    $targets_progress[] = [
        'ym' => $ym,
        'label' => Koneksi::periodeLabel($ym),
        'ket' => $t['keterangan'] ?? null,
        'pct' => (int)min(100, round($collected / $target_total * 100)),
        'collected' => $collected,
        'total' => $target_total,
    ];
}
usort($targets_progress, fn($a, $b) => strcmp($b['ym'], $a['ym']));
$targets_progress = array_slice($targets_progress, 0, 4);

$json_flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
$payments_json = json_encode($payments_js, $json_flags) ?: '[]';
$expenses_json = json_encode($expenses_js, $json_flags) ?: '[]';

$payers = array_slice(array_reverse(array_map(fn($p) => $p['nama'], $recent_pay)), 0, 3);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KasKu Sistem Kas Kelas Digital</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
          colors: {
            green: {
              400: '#00B98A',
              500: '#00A37A',
              600: '#008062',
            },
            emerald: {
              500: '#00A37A',
            },
          },
        }
      }
    }
  </script>
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: #0a0a0a; }
    ::-webkit-scrollbar-thumb { background: #00A37A; border-radius: 10px; }

    .hero-glow {
      background: radial-gradient(ellipse 600px 400px at 70% 40%, rgba(0,163,122,0.12) 0%, transparent 70%),
                  radial-gradient(ellipse 300px 300px at 20% 80%, rgba(0,163,122,0.06) 0%, transparent 70%);
    }

    .grid-bg {
      background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
      background-size: 60px 60px;
    }

    .float { animation: float 6s ease-in-out infinite; }
    .float-d { animation: float 6s ease-in-out 2s infinite; }
    @keyframes float {
      0%,100% { transform: translateY(0); }
      50% { transform: translateY(-14px); }
    }

    .card-shine {
      position: relative;
      overflow: hidden;
    }
    .card-shine::before {
      content: '';
      position: absolute;
      top: -50%; left: -50%;
      width: 200%; height: 200%;
      background: linear-gradient(45deg, transparent 40%, rgba(255,255,255,0.03) 50%, transparent 60%);
      animation: shine 4s ease-in-out infinite;
    }
    @keyframes shine {
      0% { transform: translateX(-100%) rotate(45deg); }
      100% { transform: translateX(100%) rotate(45deg); }
    }

    .fade-up {
      opacity: 0;
      transform: translateY(40px);
      transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .fade-up.visible {
      opacity: 1;
      transform: translateY(0);
    }

    .btn-glow {
      box-shadow: 0 0 20px rgba(0,163,122,0.3);
      transition: all 0.3s;
    }
    .btn-glow:hover {
      box-shadow: 0 0 35px rgba(0,163,122,0.5);
      transform: translateY(-2px);
    }

    .line-accent {
      background: linear-gradient(90deg, #00A37A, transparent);
      height: 1px;
    }

    .stat-num {
      background: linear-gradient(135deg, #00A37A, #00B98A);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .glass {
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(255,255,255,0.06);
      backdrop-filter: blur(12px);
    }

    .expense-row { transition: all 0.2s; }
    .expense-row:hover { background: rgba(0,163,122,0.04); }

    tr.data-row td { transition: background 0.15s; }
    tr.data-row:hover td { background: #E6F5F1; }
  </style>
</head>
<body class="bg-[#0a0a0a] text-white">

  <!-- NAVBAR -->
  <nav class="fixed top-0 left-0 right-0 z-50 bg-[#0a0a0a]/80 backdrop-blur-xl border-b border-white/5">
    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
      <a href="#" class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-green-600 flex items-center justify-center">
          <i data-lucide="wallet" class="w-4 h-4 text-white"></i>
        </div>
        <span class="text-lg font-bold tracking-tight">KasKu</span>
      </a>
      <div class="hidden md:flex items-center gap-8 text-sm">
        <a href="#hero" class="text-neutral-400 hover:text-white transition-colors">Beranda</a>
        <a href="#pembayaran" class="text-neutral-400 hover:text-white transition-colors">Pembayaran</a>
        <a href="#pengeluaran" class="text-neutral-400 hover:text-white transition-colors">Pengeluaran</a>
        <a href="#laporan" class="text-neutral-400 hover:text-white transition-colors">Laporan</a>
      </div>
      <?php if ($isLoggedIn): ?>
      <a href="views/siswa/dashboard.php" class="bg-green-600 text-white text-sm font-semibold px-5 py-2 rounded-lg hover:bg-green-500 transition-all flex items-center gap-2 btn-glow">
        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
        <span class="hidden sm:inline">Dashboard</span>
      </a>
      <?php else: ?>
      <button onclick="openModal('siswaModal')" class="bg-green-600 text-white text-sm font-semibold px-5 py-2 rounded-lg hover:bg-green-500 transition-all flex items-center gap-2 btn-glow">
        <i data-lucide="log-in" class="w-4 h-4"></i>
        <span class="hidden sm:inline">Masuk</span>
      </button>
      <?php endif; ?>
    </div>
  </nav>

  <!-- HERO -->
  <section id="hero" class="relative min-h-screen flex items-center pt-16 hero-glow grid-bg overflow-hidden">
    <!-- Decorative blobs -->
    <div class="absolute top-1/4 right-1/4 w-[500px] h-[500px] bg-green-600/5 rounded-full blur-[120px]"></div>
    <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-green-500/5 rounded-full blur-[100px]"></div>

    <div class="max-w-7xl mx-auto px-6 w-full relative z-10">
      <div class="grid lg:grid-cols-2 gap-16 items-center">
        <!-- Left: Text -->
        <div>
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-[1.1] tracking-tight">
            Kelola Kas
            <br>
            <span class="stat-num">Kelas</span> Dengan
            <br>
            Mudah & Transparan
          </h1>
          <p class="mt-6 text-neutral-400 text-lg font-light leading-relaxed max-w-lg">
            Platform digital untuk mencatat pembayaran kas, pengeluaran, dan laporan keuangan kelas yang bisa diakses bendahara dan siswa.
          </p>
          <div class="mt-10 flex flex-col sm:flex-row gap-4">
            <?php if ($isLoggedIn): ?>
            <a href="views/siswa/dashboard.php" class="bg-green-600 text-white font-semibold px-8 py-3.5 rounded-xl text-sm flex items-center justify-center gap-2 btn-glow">
              <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
              Buka Dashboard
            </a>
            <?php else: ?>
            <button onclick="openModal('siswaModal')" class="bg-green-600 text-white font-semibold px-8 py-3.5 rounded-xl text-sm flex items-center justify-center gap-2 btn-glow">
              <i data-lucide="arrow-right" class="w-4 h-4"></i>
              Mulai Sekarang
            </button>
            <?php endif; ?>
            <a href="#pembayaran" class="border border-white/10 text-white font-medium px-8 py-3.5 rounded-xl text-sm flex items-center justify-center gap-2 hover:bg-white/5 transition-all">
              <i data-lucide="eye" class="w-4 h-4"></i>
              Lihat Data
            </a>
          </div>

          <!-- Mini Stats -->
          <div class="mt-14 flex gap-8">
            <div>
              <div class="text-2xl font-extrabold stat-num"><?= $total_siswa ?></div>
              <div class="text-xs text-neutral-500 mt-1">Siswa Aktif</div>
            </div>
            <div class="w-px bg-white/10"></div>
            <div>
              <div class="text-2xl font-extrabold stat-num"><?= $lunas_count ?></div>
              <div class="text-xs text-neutral-500 mt-1">Pembayaran Lunas</div>
            </div>
            <div class="w-px bg-white/10"></div>
            <div>
              <div class="text-2xl font-extrabold stat-num">24/7</div>
              <div class="text-xs text-neutral-500 mt-1">Akses Online</div>
            </div>
          </div>
        </div>

        <!-- Right: Visual Card -->
        <div class="relative flex justify-center">
          <div class="float w-full max-w-md">
            <div class="card-shine glass rounded-3xl p-8">
              <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-green-600/20 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5 text-green-400"></i>
                  </div>
                  <div>
                    <div class="text-sm font-bold">Kas Kelas</div>
                    <div class="text-xs text-neutral-500"><?= htmlspecialchars($periode_aktif_label) ?></div>
                  </div>
                </div>
                <span class="text-xs font-bold text-green-400 bg-green-600/10 px-3 py-1 rounded-full">Aktif</span>
              </div>

              <div class="text-3xl font-extrabold mb-1"><?= htmlspecialchars(Koneksi::rupiah($saldo)) ?></div>
              <div class="text-xs text-neutral-500 mb-6">Total Saldo Kas Saat Ini</div>

              <div class="line-accent w-full mb-6"></div>

              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-green-600/15 flex items-center justify-center">
                      <i data-lucide="arrow-down-left" class="w-4 h-4 text-green-400"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium">Pemasukan</div>
                      <div class="text-xs text-neutral-500">Bulan ini</div>
                    </div>
                  </div>
                  <span class="text-sm font-bold text-green-400">+<?= htmlspecialchars(rp_short($masuk_bulan_ini)) ?></span>
                </div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-red-600/15 flex items-center justify-center">
                      <i data-lucide="arrow-up-right" class="w-4 h-4 text-red-400"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium">Pengeluaran</div>
                      <div class="text-xs text-neutral-500">Bulan ini</div>
                    </div>
                  </div>
                  <span class="text-sm font-bold text-red-400">-<?= htmlspecialchars(rp_short($keluar_bulan_ini)) ?></span>
                </div>
              </div>

              <div class="line-accent w-full my-6"></div>

              <div class="flex items-center justify-between">
                <div class="flex -space-x-2">
                  <?php foreach ($payers as $i => $nm): ?>
                  <div class="w-7 h-7 rounded-full <?= ['bg-green-600', 'bg-emerald-500', 'bg-green-400'][$i] ?> border-2 border-[#0a0a0a] flex items-center justify-center text-[10px] font-bold"><?= strtoupper(mb_substr(trim($nm), 0, 1)) ?></div>
                  <?php endforeach; ?>
                  <?php if ($total_siswa > count($payers)): ?>
                  <div class="w-7 h-7 rounded-full bg-neutral-700 border-2 border-[#0a0a0a] flex items-center justify-center text-[10px] font-bold">+<?= $total_siswa - count($payers) ?></div>
                  <?php endif; ?>
                </div>
                <span class="text-xs text-neutral-500"><?= $lunas_aktif ?> dari <?= $total_siswa ?> siswa sudah bayar</span>
              </div>
            </div>
          </div>

          <!-- Floating small cards -->
          <div class="float-d absolute -top-4 -left-4 glass rounded-2xl p-4 w-40">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-6 h-6 rounded-md bg-green-600/20 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-3 h-3 text-green-400"></i>
              </div>
              <span class="text-xs font-semibold text-green-400">Lunas</span>
            </div>
            <div class="text-lg font-extrabold"><?= $lunas_aktif ?></div>
            <div class="text-[10px] text-neutral-500">dari <?= $total_siswa ?> siswa</div>
          </div>

          <div class="float absolute -bottom-2 -right-2 glass rounded-2xl p-4 w-44">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-6 h-6 rounded-md bg-yellow-600/20 flex items-center justify-center">
                <i data-lucide="clock" class="w-3 h-3 text-yellow-400"></i>
              </div>
              <span class="text-xs font-semibold text-yellow-400">Belum Bayar</span>
            </div>
            <div class="text-lg font-extrabold"><?= $belum_aktif ?></div>
            <div class="text-[10px] text-neutral-500">periode <?= htmlspecialchars($periode_aktif_label) ?></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- PEMBAYARAN -->
  <section id="pembayaran" class="py-24 px-6">
    <div class="max-w-7xl mx-auto">
      <div class="fade-up">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-1 h-6 rounded-full bg-green-500"></div>
          <span class="text-xs font-bold uppercase tracking-widest text-green-400">Pembayaran</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
          <div>
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Catatan Pembayaran</h2>
            <p class="text-neutral-500 mt-2">Rekap pembayaran kas terbaru seluruh siswa</p>
          </div>
          <select id="monthFilter" onchange="filterPayments()" class="bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-neutral-300 focus:outline-none focus:border-green-500 w-fit">
            <option value="all" class="bg-neutral-900">Semua Periode</option>
            <?php foreach ($periodes as $ym): ?>
            <option value="<?= htmlspecialchars($ym) ?>" class="bg-neutral-900"><?= htmlspecialchars(Koneksi::periodeLabel($ym)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="fade-up glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-white/5">
                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-neutral-500">Nama Siswa</th>
                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-neutral-500">Absen</th>
                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-neutral-500">Periode</th>
                <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-neutral-500">Jumlah</th>
                <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-neutral-500">Status</th>
                <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-neutral-500">Tanggal</th>
              </tr>
            </thead>
            <tbody id="payTable"></tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <!-- PENGELUARAN -->
  <section id="pengeluaran" class="py-24 px-6 relative">
    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-[#00A37A]/[0.02] to-transparent"></div>
    <div class="max-w-7xl mx-auto relative z-10">
      <div class="fade-up">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-1 h-6 rounded-full bg-green-500"></div>
          <span class="text-xs font-bold uppercase tracking-widest text-green-400">Transparan</span>
        </div>
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-2">Pengeluaran yang Terbuka</h2>
        <p class="text-neutral-500 mb-10">Semua pengeluaran kas tercatat dan dapat dilihat oleh seluruh anggota kelas</p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4" id="expenseGrid"></div>
    </div>
  </section>

  <!-- LAPORAN -->
  <section id="laporan" class="py-24 px-6">
    <div class="max-w-7xl mx-auto">
      <div class="fade-up">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-1 h-6 rounded-full bg-green-500"></div>
          <span class="text-xs font-bold uppercase tracking-widest text-green-400">Ringkasan</span>
        </div>
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-2">Laporan Ringkas</h2>
        <p class="text-neutral-500 mb-10">Gambaran singkat kondisi keuangan kas kelas saat ini</p>
      </div>

      <!-- Stats -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10 fade-up">
        <div class="glass rounded-2xl p-6 card-shine">
          <div class="w-10 h-10 rounded-xl bg-green-600/15 flex items-center justify-center mb-4">
            <i data-lucide="trending-up" class="w-5 h-5 text-green-400"></i>
          </div>
          <div class="text-xs font-semibold text-neutral-500 mb-1">Total Pemasukan</div>
          <div class="text-2xl font-extrabold stat-num"><?= htmlspecialchars(rp_short($pemasukan)) ?></div>
          <div class="flex items-center gap-1 mt-2 text-xs font-medium <?= $trend_masuk >= 0 ? 'text-green-400' : 'text-red-400' ?>">
            <i data-lucide="<?= $trend_masuk >= 0 ? 'arrow-up-right' : 'arrow-down-right' ?>" class="w-3 h-3"></i> <?= $trend_masuk >= 0 ? '+' : '' ?><?= $trend_masuk ?>%
          </div>
        </div>
        <div class="glass rounded-2xl p-6 card-shine">
          <div class="w-10 h-10 rounded-xl bg-red-600/15 flex items-center justify-center mb-4">
            <i data-lucide="trending-down" class="w-5 h-5 text-red-400"></i>
          </div>
          <div class="text-xs font-semibold text-neutral-500 mb-1">Total Pengeluaran</div>
          <div class="text-2xl font-extrabold text-red-400"><?= htmlspecialchars(rp_short($pengeluaran_total)) ?></div>
          <div class="flex items-center gap-1 mt-2 text-xs font-medium <?= $trend_keluar <= 0 ? 'text-green-400' : 'text-red-400' ?>">
            <i data-lucide="<?= $trend_keluar >= 0 ? 'arrow-up-right' : 'arrow-down-right' ?>" class="w-3 h-3"></i> <?= $trend_keluar >= 0 ? '+' : '' ?><?= $trend_keluar ?>%
          </div>
        </div>
        <div class="glass rounded-2xl p-6 card-shine border border-green-600/20">
          <div class="w-10 h-10 rounded-xl bg-green-600/20 flex items-center justify-center mb-4">
            <i data-lucide="wallet" class="w-5 h-5 text-green-400"></i>
          </div>
          <div class="text-xs font-semibold text-neutral-500 mb-1">Saldo Saat Ini</div>
          <div class="text-2xl font-extrabold stat-num"><?= htmlspecialchars(rp_short($saldo)) ?></div>
          <div class="flex items-center gap-1 mt-2 text-xs font-medium <?= $saldo >= 0 ? 'text-green-400' : 'text-red-400' ?>">
            <i data-lucide="<?= $saldo >= 0 ? 'check-circle' : 'alert-circle' ?>" class="w-3 h-3"></i> <?= $saldo >= 0 ? 'Sehat' : 'Minus' ?>
          </div>
        </div>
        <div class="glass rounded-2xl p-6 card-shine">
          <div class="w-10 h-10 rounded-xl bg-yellow-600/15 flex items-center justify-center mb-4">
            <i data-lucide="alert-circle" class="w-5 h-5 text-yellow-400"></i>
          </div>
          <div class="text-xs font-semibold text-neutral-500 mb-1">Belum Bayar</div>
          <div class="text-2xl font-extrabold text-yellow-400"><?= $belum_aktif ?></div>
          <div class="flex items-center gap-1 mt-2 text-xs text-yellow-400 font-medium">
            <i data-lucide="clock" class="w-3 h-3"></i> <?= htmlspecialchars($periode_aktif_label) ?>
          </div>
        </div>
      </div>

      <!-- Chart + Progress -->
      <div class="grid lg:grid-cols-5 gap-6 fade-up">
        <div class="lg:col-span-3 glass rounded-2xl p-8">
          <h3 class="font-bold mb-1">Pemasukan vs Pengeluaran</h3>
          <p class="text-xs text-neutral-500 mb-8">6 bulan terakhir</p>
          <?php if ($chart_any): ?>
          <div class="flex items-end gap-4 h-48">
            <?php foreach (array_values($months) as $i => $m): ?>
            <div class="flex-1 flex flex-col items-center gap-2">
              <div class="w-full flex gap-1.5 items-end justify-center h-40">
                <div class="w-6 rounded-t-md <?= $i === 5 ? 'bg-green-400' : ($i >= 3 ? 'bg-green-500/80' : 'bg-green-600/80') ?>" style="height:<?= max(2, round($m['masuk'] / $max_chart * 100)) ?>%"></div>
                <div class="w-6 rounded-t-md bg-red-500/60" style="height:<?= max(2, round($m['keluar'] / $max_chart * 100)) ?>%"></div>
              </div>
              <span class="text-[11px] text-neutral-500 font-medium"><?= $m['label'] ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <div class="h-48 flex items-center justify-center text-sm text-neutral-600">Belum ada transaksi dalam 6 bulan terakhir</div>
          <?php endif; ?>
          <div class="flex items-center gap-6 mt-6 text-xs font-medium">
            <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-green-500"></span><span class="text-neutral-400">Pemasukan</span></span>
            <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-red-500/60"></span><span class="text-neutral-400">Pengeluaran</span></span>
          </div>
        </div>

        <div class="lg:col-span-2 glass rounded-2xl p-8">
          <h3 class="font-bold mb-1">Progress Target Kas</h3>
          <p class="text-xs text-neutral-500 mb-6">Realisasi pemasukan per periode target</p>
          <?php if ($targets_progress): ?>
          <div class="space-y-5">
            <?php foreach ($targets_progress as $t):
              $bar = $t['pct'] >= 75 ? ['text-green-400', 'bg-green-500'] : ($t['pct'] >= 40 ? ['text-yellow-400', 'bg-yellow-500'] : ['text-red-400', 'bg-red-500']);
            ?>
            <div>
              <div class="flex justify-between text-sm mb-2">
                <span class="font-medium"><?= htmlspecialchars($t['label']) ?><?= $t['ket'] ? ' — ' . htmlspecialchars($t['ket']) : '' ?></span>
                <span class="<?= $bar[0] ?> font-bold text-xs"><?= $t['pct'] ?>%</span>
              </div>
              <div class="w-full bg-white/5 rounded-full h-2"><div class="<?= $bar[1] ?> h-2 rounded-full" style="width:<?= $t['pct'] ?>%"></div></div>
              <div class="text-[10px] text-neutral-600 mt-1"><?= htmlspecialchars(rp_short($t['collected'])) ?> terkumpul dari target <?= htmlspecialchars(rp_short($t['total'])) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <div class="text-sm text-neutral-600">Belum ada target kas yang ditetapkan bendahara.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="py-24 px-6">
    <div class="max-w-4xl mx-auto relative">
      <div class="absolute inset-0 bg-green-600/10 rounded-3xl blur-[80px]"></div>
      <div class="relative glass rounded-3xl p-10 md:p-16 text-center border border-green-600/20">
        <div class="w-14 h-14 rounded-2xl bg-green-600/15 flex items-center justify-center mx-auto mb-6">
          <i data-lucide="rocket" class="w-7 h-7 text-green-400"></i>
        </div>
        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-4">Kelola Kas Kelasmu<br>Sekarang Juga</h2>
        <p class="text-neutral-400 max-w-md mx-auto mb-10">Catat pembayaran, pantau pengeluaran, dan lihat laporan kas kelas secara transparan dalam satu aplikasi.</p>
        <button onclick="openModal('siswaModal')" class="bg-green-600 text-white font-semibold px-10 py-4 rounded-xl text-sm btn-glow inline-flex items-center gap-2">
          <i data-lucide="log-in" class="w-4 h-4"></i>
          Masuk ke KasKu
        </button>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="border-t border-white/5 py-12 px-6">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-lg bg-green-600 flex items-center justify-center">
          <i data-lucide="wallet" class="w-3.5 h-3.5 text-white"></i>
        </div>
        <span class="font-bold text-sm">KasKu</span>
      </div>
      <p class="text-xs text-neutral-600">© <?= date('Y') ?> KasKu. Sistem Kas Kelas Digital.</p>
      <div class="flex items-center gap-3">
        <a href="#" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-neutral-500 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="instagram" class="w-4 h-4"></i></a>
        <a href="#" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-neutral-500 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="youtube" class="w-4 h-4"></i></a>
        <a href="#" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-neutral-500 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="mail" class="w-4 h-4"></i></a>
      </div>
    </div>
  </footer>

  <!-- ========== MODALS ========== -->

  <!-- Login Siswa (satu-satunya form login) -->
  <div id="siswaModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-[#141414] border border-white/10 rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl relative">
      <button onclick="closeModal('siswaModal')" class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-neutral-500 hover:text-white transition-all">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-green-600/15 flex items-center justify-center">
          <i data-lucide="graduation-cap" class="w-5 h-5 text-green-400"></i>
        </div>
        <div>
          <h3 class="font-bold">Login Siswa</h3>
          <p class="text-xs text-neutral-500">Cek pembayaran kas</p>
        </div>
      </div>
      <form id="swForm" action="function/search.php" method="POST" class="space-y-4">
        <?= Koneksi::csrfField() ?>
        <div>
          <label class="block text-xs font-semibold text-neutral-400 mb-1.5">Nama</label>
          <input type="text" name="nama" placeholder="Masukkan nama lengkap" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm placeholder-neutral-600 focus:outline-none focus:border-green-500 transition-colors">
        </div>
        <div>
          <label class="block text-xs font-semibold text-neutral-400 mb-1.5">Nomor Absen</label>
          <input type="number" name="nomor_absen" placeholder="Masukkan nomor absen" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm placeholder-neutral-600 focus:outline-none focus:border-green-500 transition-colors">
        </div>
        <button type="submit" class="w-full bg-green-600 text-white font-semibold py-3 rounded-xl text-sm btn-glow">Cek Pembayaran</button>
      </form>
      <p class="text-center text-[11px] text-neutral-600 mt-4">Masuk dengan nama dan nomor absen kamu</p>
    </div>
  </div>

  <script>
    const payments = <?= $payments_json ?>;
    const expenses = <?= $expenses_json ?>;

    const statusBadge = {
      lunas: '<span class="inline-flex items-center gap-1 text-[11px] font-bold bg-green-600/15 text-green-400 px-3 py-1 rounded-full"><span class="w-1 h-1 rounded-full bg-green-500"></span>Lunas</span>',
      pending: '<span class="inline-flex items-center gap-1 text-[11px] font-bold bg-yellow-600/15 text-yellow-400 px-3 py-1 rounded-full"><span class="w-1 h-1 rounded-full bg-yellow-500"></span>Pending</span>',
      belum: '<span class="inline-flex items-center gap-1 text-[11px] font-bold bg-red-600/15 text-red-400 px-3 py-1 rounded-full"><span class="w-1 h-1 rounded-full bg-red-500"></span>Belum</span>'
    };

    function renderPayments(filter='all') {
      const data = filter === 'all' ? payments : payments.filter(p => p.periode === filter);
      const tb = document.getElementById('payTable');
      if (!data.length) {
        tb.innerHTML = '<tr><td colspan="6" class="px-6 py-10 text-center text-neutral-600">Belum ada data pembayaran</td></tr>';
        return;
      }
      tb.innerHTML = data.map(p => `
        <tr class="data-row border-b border-white/5">
          <td class="px-6 py-4 font-medium">${p.name}</td>
          <td class="px-6 py-4 text-neutral-500">${p.absen}</td>
          <td class="px-6 py-4 text-neutral-500">${p.plabel}</td>
          <td class="px-6 py-4 text-right font-medium">Rp ${p.amount.toLocaleString('id-ID')}</td>
          <td class="px-6 py-4 text-center">${statusBadge[p.status] || statusBadge.belum}</td>
          <td class="px-6 py-4 text-center text-neutral-600">${p.date}</td>
        </tr>`).join('');
    }

    function renderExpenses() {
      const grid = document.getElementById('expenseGrid');
      if (!expenses.length) {
        grid.innerHTML = '<div class="glass rounded-2xl p-10 text-center text-neutral-600 md:col-span-2 lg:col-span-3">Belum ada data pengeluaran</div>';
        return;
      }
      grid.innerHTML = expenses.map(e =>
        `<div class="glass rounded-2xl p-5 card-shine expense-row">
          <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center">
              <i data-lucide="${e.icon}" class="w-5 h-5 text-green-400"></i>
            </div>
            <span class="text-[11px] font-bold text-neutral-500 bg-white/5 px-2.5 py-1 rounded-full">${e.cat}</span>
          </div>
          <h4 class="text-sm font-bold mb-3 leading-snug">${e.title}</h4>
          <div class="flex items-center justify-between">
            <span class="text-xs text-neutral-600">${e.date}</span>
            <span class="text-sm font-bold text-red-400">-Rp ${e.amount.toLocaleString('id-ID')}</span>
          </div>
        </div>`
      ).join('');
      lucide.createIcons();
    }

    function filterPayments() {
      renderPayments(document.getElementById('monthFilter').value);
    }

    function openModal(id) {
      const m = document.getElementById(id);
      m.classList.remove('hidden'); m.classList.add('flex');
      document.body.style.overflow = 'hidden';
      lucide.createIcons();
    }
    function closeModal(id) {
      const m = document.getElementById(id);
      m.classList.add('hidden'); m.classList.remove('flex');
      document.body.style.overflow = '';
    }

    // Close modal on backdrop click
    document.querySelectorAll('[id$="Modal"]').forEach(m => {
      m.addEventListener('click', e => { if (e.target === m) closeModal(m.id); });
    });

    // Scroll animations
    const observer = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1 });
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(a => {
      a.addEventListener('click', e => {
        e.preventDefault();
        document.querySelector(a.getAttribute('href'))?.scrollIntoView({ behavior:'smooth' });
      });
    });

    renderPayments();
    renderExpenses();
    lucide.createIcons();
  </script>
</body>
</html>
