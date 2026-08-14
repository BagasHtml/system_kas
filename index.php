<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KasKu — Sistem Kas Sekolah Digital</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
        }
      }
    }
  </script>
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: #0a0a0a; }
    ::-webkit-scrollbar-thumb { background: #22c55e; border-radius: 10px; }

    .hero-glow {
      background: radial-gradient(ellipse 600px 400px at 70% 40%, rgba(34,197,94,0.12) 0%, transparent 70%),
                  radial-gradient(ellipse 300px 300px at 20% 80%, rgba(34,197,94,0.06) 0%, transparent 70%);
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
      box-shadow: 0 0 20px rgba(34,197,94,0.3);
      transition: all 0.3s;
    }
    .btn-glow:hover {
      box-shadow: 0 0 35px rgba(34,197,94,0.5);
      transform: translateY(-2px);
    }

    .line-accent {
      background: linear-gradient(90deg, #22c55e, transparent);
      height: 1px;
    }

    .stat-num {
      background: linear-gradient(135deg, #22c55e, #4ade80);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .glass {
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(255,255,255,0.06);
      backdrop-filter: blur(12px);
    }

    .expense-row { transition: all 0.2s; }
    .expense-row:hover { background: rgba(34,197,94,0.04); }

    tr.data-row td { transition: background 0.15s; }
    tr.data-row:hover td { background: #f0fdf4; }
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
      <button onclick="openModal('loginModal')" class="bg-green-600 text-white text-sm font-semibold px-5 py-2 rounded-lg hover:bg-green-500 transition-all flex items-center gap-2 btn-glow">
        <i data-lucide="log-in" class="w-4 h-4"></i>
        <span class="hidden sm:inline">Masuk</span>
      </button>
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
          <div class="inline-flex items-center gap-2 bg-green-600/10 border border-green-600/20 text-green-400 text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-6">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
            Sistem Kas Digital
          </div>
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-[1.1] tracking-tight">
            Kelola Kas
            <br>
            <span class="stat-num">Sekolah</span> Dengan
            <br>
            Mudah & Transparan
          </h1>
          <p class="mt-6 text-neutral-400 text-lg font-light leading-relaxed max-w-lg">
            Platform digital untuk mencatat pembayaran, pengeluaran, dan laporan keuangan kas sekolah yang bisa diakses admin dan siswa.
          </p>
          <div class="mt-10 flex flex-col sm:flex-row gap-4">
            <button onclick="openModal('loginModal')" class="bg-green-600 text-white font-semibold px-8 py-3.5 rounded-xl text-sm flex items-center justify-center gap-2 btn-glow">
              <i data-lucide="arrow-right" class="w-4 h-4"></i>
              Mulai Sekarang
            </button>
            <a href="#pembayaran" class="border border-white/10 text-white font-medium px-8 py-3.5 rounded-xl text-sm flex items-center justify-center gap-2 hover:bg-white/5 transition-all">
              <i data-lucide="eye" class="w-4 h-4"></i>
              Lihat Data
            </a>
          </div>

          <!-- Mini Stats -->
          <div class="mt-14 flex gap-8">
            <div>
              <div class="text-2xl font-extrabold stat-num">350+</div>
              <div class="text-xs text-neutral-500 mt-1">Siswa Aktif</div>
            </div>
            <div class="w-px bg-white/10"></div>
            <div>
              <div class="text-2xl font-extrabold stat-num">98%</div>
              <div class="text-xs text-neutral-500 mt-1">Kepuasan</div>
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
                    <div class="text-sm font-bold">Kas Bulanan</div>
                    <div class="text-xs text-neutral-500">Juni 2025</div>
                  </div>
                </div>
                <span class="text-xs font-bold text-green-400 bg-green-600/10 px-3 py-1 rounded-full">Aktif</span>
              </div>

              <div class="text-3xl font-extrabold mb-1">Rp 8.400.000</div>
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
                  <span class="text-sm font-bold text-green-400">+Rp 10.5jt</span>
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
                  <span class="text-sm font-bold text-red-400">-Rp 2.1jt</span>
                </div>
              </div>

              <div class="line-accent w-full my-6"></div>

              <div class="flex items-center justify-between">
                <div class="flex -space-x-2">
                  <div class="w-7 h-7 rounded-full bg-green-600 border-2 border-[#0a0a0a] flex items-center justify-center text-[10px] font-bold">A</div>
                  <div class="w-7 h-7 rounded-full bg-emerald-500 border-2 border-[#0a0a0a] flex items-center justify-center text-[10px] font-bold">S</div>
                  <div class="w-7 h-7 rounded-full bg-green-400 border-2 border-[#0a0a0a] flex items-center justify-center text-[10px] font-bold">B</div>
                  <div class="w-7 h-7 rounded-full bg-neutral-700 border-2 border-[#0a0a0a] flex items-center justify-center text-[10px] font-bold">+322</div>
                </div>
                <span class="text-xs text-neutral-500">322 siswa sudah bayar</span>
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
            <div class="text-lg font-extrabold">322</div>
            <div class="text-[10px] text-neutral-500">dari 350 siswa</div>
          </div>

          <div class="float absolute -bottom-2 -right-2 glass rounded-2xl p-4 w-44">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-6 h-6 rounded-md bg-yellow-600/20 flex items-center justify-center">
                <i data-lucide="clock" class="w-3 h-3 text-yellow-400"></i>
              </div>
              <span class="text-xs font-semibold text-yellow-400">Belum Bayar</span>
            </div>
            <div class="text-lg font-extrabold">28</div>
            <div class="text-[10px] text-neutral-500">perlu ditindaklanjuti</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2">
      <span class="text-[10px] uppercase tracking-widest text-neutral-600">Scroll</span>
      <div class="w-5 h-8 rounded-full border border-neutral-700 flex justify-center pt-1.5">
        <div class="w-1 h-2 rounded-full bg-green-500 animate-bounce"></div>
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
            <p class="text-neutral-500 mt-2">Rekap pembayaran kas bulanan seluruh siswa</p>
          </div>
          <select id="monthFilter" onchange="filterPayments()" class="bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-neutral-300 focus:outline-none focus:border-green-500 w-fit">
            <option value="all" class="bg-neutral-900">Semua Bulan</option>
            <option value="Juni" class="bg-neutral-900">Juni 2025</option>
            <option value="Mei" class="bg-neutral-900">Mei 2025</option>
            <option value="April" class="bg-neutral-900">April 2025</option>
            <option value="Maret" class="bg-neutral-900">Maret 2025</option>
          </select>
        </div>
      </div>

      <div class="fade-up glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-white/5">
                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-neutral-500">Nama Siswa</th>
                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-neutral-500">Kelas</th>
                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-neutral-500">Bulan</th>
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
    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-green-600/[0.02] to-transparent"></div>
    <div class="max-w-7xl mx-auto relative z-10">
      <div class="fade-up">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-1 h-6 rounded-full bg-green-500"></div>
          <span class="text-xs font-bold uppercase tracking-widest text-green-400">Transparan</span>
        </div>
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-2">Pengeluaran yang Terbuka</h2>
        <p class="text-neutral-500 mb-10">Semua pengeluaran kas tercatat dan dapat dilihat oleh seluruh warga sekolah</p>
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
        <p class="text-neutral-500 mb-10">Gambaran singkat kondisi keuangan kas sekolah saat ini</p>
      </div>

      <!-- Stats -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10 fade-up">
        <div class="glass rounded-2xl p-6 card-shine">
          <div class="w-10 h-10 rounded-xl bg-green-600/15 flex items-center justify-center mb-4">
            <i data-lucide="trending-up" class="w-5 h-5 text-green-400"></i>
          </div>
          <div class="text-xs font-semibold text-neutral-500 mb-1">Total Pemasukan</div>
          <div class="text-2xl font-extrabold stat-num">Rp 12.6jt</div>
          <div class="flex items-center gap-1 mt-2 text-xs text-green-400 font-medium">
            <i data-lucide="arrow-up-right" class="w-3 h-3"></i> +12%
          </div>
        </div>
        <div class="glass rounded-2xl p-6 card-shine">
          <div class="w-10 h-10 rounded-xl bg-red-600/15 flex items-center justify-center mb-4">
            <i data-lucide="trending-down" class="w-5 h-5 text-red-400"></i>
          </div>
          <div class="text-xs font-semibold text-neutral-500 mb-1">Total Pengeluaran</div>
          <div class="text-2xl font-extrabold text-red-400">Rp 4.2jt</div>
          <div class="flex items-center gap-1 mt-2 text-xs text-red-400 font-medium">
            <i data-lucide="arrow-down-right" class="w-3 h-3"></i> -5%
          </div>
        </div>
        <div class="glass rounded-2xl p-6 card-shine border border-green-600/20">
          <div class="w-10 h-10 rounded-xl bg-green-600/20 flex items-center justify-center mb-4">
            <i data-lucide="wallet" class="w-5 h-5 text-green-400"></i>
          </div>
          <div class="text-xs font-semibold text-neutral-500 mb-1">Saldo Saat Ini</div>
          <div class="text-2xl font-extrabold stat-num">Rp 8.4jt</div>
          <div class="flex items-center gap-1 mt-2 text-xs text-green-400 font-medium">
            <i data-lucide="check-circle" class="w-3 h-3"></i> Sehat
          </div>
        </div>
        <div class="glass rounded-2xl p-6 card-shine">
          <div class="w-10 h-10 rounded-xl bg-yellow-600/15 flex items-center justify-center mb-4">
            <i data-lucide="alert-circle" class="w-5 h-5 text-yellow-400"></i>
          </div>
          <div class="text-xs font-semibold text-neutral-500 mb-1">Belum Bayar</div>
          <div class="text-2xl font-extrabold text-yellow-400">28</div>
          <div class="flex items-center gap-1 mt-2 text-xs text-yellow-400 font-medium">
            <i data-lucide="clock" class="w-3 h-3"></i> Perlu tindakan
          </div>
        </div>
      </div>

      <!-- Chart + Progress -->
      <div class="grid lg:grid-cols-5 gap-6 fade-up">
        <div class="lg:col-span-3 glass rounded-2xl p-8">
          <h3 class="font-bold mb-1">Pemasukan vs Pengeluaran</h3>
          <p class="text-xs text-neutral-500 mb-8">6 bulan terakhir</p>
          <div class="flex items-end gap-4 h-48">
            <div class="flex-1 flex flex-col items-center gap-2">
              <div class="w-full flex gap-1.5 items-end justify-center h-40">
                <div class="w-6 rounded-t-md bg-green-600/80" style="height:60%"></div>
                <div class="w-6 rounded-t-md bg-red-500/60" style="height:25%"></div>
              </div>
              <span class="text-[11px] text-neutral-500 font-medium">Jan</span>
            </div>
            <div class="flex-1 flex flex-col items-center gap-2">
              <div class="w-full flex gap-1.5 items-end justify-center h-40">
                <div class="w-6 rounded-t-md bg-green-600/80" style="height:72%"></div>
                <div class="w-6 rounded-t-md bg-red-500/60" style="height:38%"></div>
              </div>
              <span class="text-[11px] text-neutral-500 font-medium">Feb</span>
            </div>
            <div class="flex-1 flex flex-col items-center gap-2">
              <div class="w-full flex gap-1.5 items-end justify-center h-40">
                <div class="w-6 rounded-t-md bg-green-500/80" style="height:80%"></div>
                <div class="w-6 rounded-t-md bg-red-500/60" style="height:30%"></div>
              </div>
              <span class="text-[11px] text-neutral-500 font-medium">Mar</span>
            </div>
            <div class="flex-1 flex flex-col items-center gap-2">
              <div class="w-full flex gap-1.5 items-end justify-center h-40">
                <div class="w-6 rounded-t-md bg-green-500/80" style="height:68%"></div>
                <div class="w-6 rounded-t-md bg-red-500/60" style="height:48%"></div>
              </div>
              <span class="text-[11px] text-neutral-500 font-medium">Apr</span>
            </div>
            <div class="flex-1 flex flex-col items-center gap-2">
              <div class="w-full flex gap-1.5 items-end justify-center h-40">
                <div class="w-6 rounded-t-md bg-green-400/80" style="height:88%"></div>
                <div class="w-6 rounded-t-md bg-red-500/60" style="height:35%"></div>
              </div>
              <span class="text-[11px] text-neutral-500 font-medium">Mei</span>
            </div>
            <div class="flex-1 flex flex-col items-center gap-2">
              <div class="w-full flex gap-1.5 items-end justify-center h-40">
                <div class="w-6 rounded-t-md bg-green-400" style="height:95%"></div>
                <div class="w-6 rounded-t-md bg-red-500/60" style="height:28%"></div>
              </div>
              <span class="text-[11px] text-neutral-500 font-medium">Jun</span>
            </div>
          </div>
          <div class="flex items-center gap-6 mt-6 text-xs font-medium">
            <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-green-500"></span><span class="text-neutral-400">Pemasukan</span></span>
            <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-red-500/60"></span><span class="text-neutral-400">Pengeluaran</span></span>
          </div>
        </div>

        <div class="lg:col-span-2 glass rounded-2xl p-8">
          <h3 class="font-bold mb-1">Kebutuhan Mendesak</h3>
          <p class="text-xs text-neutral-500 mb-6">Pengeluaran yang perlu segera diajukan</p>
          <div class="space-y-5">
            <div>
              <div class="flex justify-between text-sm mb-2"><span class="font-medium">Perlengkapan Kelas</span><span class="text-green-400 font-bold text-xs">75%</span></div>
              <div class="w-full bg-white/5 rounded-full h-2"><div class="bg-green-500 h-2 rounded-full" style="width:75%"></div></div>
            </div>
            <div>
              <div class="flex justify-between text-sm mb-2"><span class="font-medium">Kegiatan 17 Agustus</span><span class="text-yellow-400 font-bold text-xs">40%</span></div>
              <div class="w-full bg-white/5 rounded-full h-2"><div class="bg-yellow-500 h-2 rounded-full" style="width:40%"></div></div>
            </div>
            <div>
              <div class="flex justify-between text-sm mb-2"><span class="font-medium">Perbaikan AC</span><span class="text-red-400 font-bold text-xs">15%</span></div>
              <div class="w-full bg-white/5 rounded-full h-2"><div class="bg-red-500 h-2 rounded-full" style="width:15%"></div></div>
            </div>
            <div>
              <div class="flex justify-between text-sm mb-2"><span class="font-medium">Donasi Yayasan</span><span class="text-green-400 font-bold text-xs">60%</span></div>
              <div class="w-full bg-white/5 rounded-full h-2"><div class="bg-green-400 h-2 rounded-full" style="width:60%"></div></div>
            </div>
          </div>
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
        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-4">Mulai Kelola Kas<br>Sekolahmu Sekarang</h2>
        <p class="text-neutral-400 max-w-md mx-auto mb-10">Bergabung dengan ratusan sekolah yang sudah menggunakan KasKu untuk pengelolaan keuangan yang lebih transparan.</p>
        <button onclick="openModal('loginModal')" class="bg-green-600 text-white font-semibold px-10 py-4 rounded-xl text-sm btn-glow inline-flex items-center gap-2">
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
      <p class="text-xs text-neutral-600">© 2025 KasKu. Sistem Kas Sekolah Digital.</p>
      <div class="flex items-center gap-3">
        <a href="#" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-neutral-500 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="instagram" class="w-4 h-4"></i></a>
        <a href="#" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-neutral-500 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="youtube" class="w-4 h-4"></i></a>
        <a href="#" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-neutral-500 hover:text-white hover:bg-white/10 transition-all"><i data-lucide="mail" class="w-4 h-4"></i></a>
      </div>
    </div>
  </footer>

  <!-- ========== MODALS ========== -->

  <!-- Login Choice -->
  <div id="loginModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-[#141414] border border-white/10 rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl relative">
      <button onclick="closeModal('loginModal')" class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-neutral-500 hover:text-white hover:bg-white/10 transition-all">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
      <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-2xl bg-green-600/15 flex items-center justify-center mx-auto mb-4">
          <i data-lucide="wallet" class="w-6 h-6 text-green-400"></i>
        </div>
        <h3 class="text-xl font-bold">Masuk ke KasKu</h3>
        <p class="text-sm text-neutral-500 mt-1">Pilih peran kamu</p>
      </div>
      <div class="space-y-3">
        <button onclick="closeModal('loginModal');openModal('adminModal')" class="w-full flex items-center gap-4 p-4 rounded-2xl border border-white/10 hover:border-green-600/40 hover:bg-green-600/5 transition-all group">
          <div class="w-11 h-11 rounded-xl bg-green-600/15 flex items-center justify-center group-hover:bg-green-600/25 transition-colors">
            <i data-lucide="shield" class="w-5 h-5 text-green-400"></i>
          </div>
          <div class="text-left flex-1">
            <div class="font-bold text-sm">Admin / Bendahara</div>
            <div class="text-xs text-neutral-500">Kelola kas, laporan & pengeluaran</div>
          </div>
          <i data-lucide="chevron-right" class="w-4 h-4 text-neutral-600 group-hover:text-green-400 transition-colors"></i>
        </button>
        <button onclick="closeModal('loginModal');openModal('siswaModal')" class="w-full flex items-center gap-4 p-4 rounded-2xl border border-white/10 hover:border-green-600/40 hover:bg-green-600/5 transition-all group">
          <div class="w-11 h-11 rounded-xl bg-green-600/15 flex items-center justify-center group-hover:bg-green-600/25 transition-colors">
            <i data-lucide="graduation-cap" class="w-5 h-5 text-green-400"></i>
          </div>
          <div class="text-left flex-1">
            <div class="font-bold text-sm">Siswa</div>
            <div class="text-xs text-neutral-500">Cek status pembayaran kas</div>
          </div>
          <i data-lucide="chevron-right" class="w-4 h-4 text-neutral-600 group-hover:text-green-400 transition-colors"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Admin Login -->
  <div id="adminModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-[#141414] border border-white/10 rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl relative">
      <button onclick="closeModal('adminModal')" class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-neutral-500 hover:text-white transition-all">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-green-600/15 flex items-center justify-center">
          <i data-lucide="shield" class="w-5 h-5 text-green-400"></i>
        </div>
        <div>
          <h3 class="font-bold">Login Admin</h3>
          <p class="text-xs text-neutral-500">Bendahara kas sekolah</p>
        </div>
      </div>
      <form onsubmit="doLogin(event,'admin')" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-neutral-400 mb-1.5">Username</label>
          <input type="text" id="admUser" placeholder="admin" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm placeholder-neutral-600 focus:outline-none focus:border-green-500 transition-colors">
        </div>
        <div>
          <label class="block text-xs font-semibold text-neutral-400 mb-1.5">Password</label>
          <input type="password" id="admPass" placeholder="••••••" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm placeholder-neutral-600 focus:outline-none focus:border-green-500 transition-colors">
        </div>
        <button type="submit" class="w-full bg-green-600 text-white font-semibold py-3 rounded-xl text-sm btn-glow">Masuk</button>
      </form>
      <p class="text-center text-[11px] text-neutral-600 mt-4">Demo: admin / admin123</p>
    </div>
  </div>

  <!-- Siswa Login -->
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
      <form onsubmit="doLogin(event,'siswa')" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-neutral-400 mb-1.5">NIS</label>
          <input type="text" id="swNis" placeholder="2024001" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm placeholder-neutral-600 focus:outline-none focus:border-green-500 transition-colors">
        </div>
        <div>
          <label class="block text-xs font-semibold text-neutral-400 mb-1.5">Password</label>
          <input type="password" id="swPass" placeholder="••••••" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm placeholder-neutral-600 focus:outline-none focus:border-green-500 transition-colors">
        </div>
        <button type="submit" class="w-full bg-green-600 text-white font-semibold py-3 rounded-xl text-sm btn-glow">Cek Pembayaran</button>
      </form>
      <p class="text-center text-[11px] text-neutral-600 mt-4">Demo: 2024001 / siswa123</p>
    </div>
  </div>

  <!-- Dashboard Modal (Admin) -->
  <div id="dashModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 backdrop-blur-sm overflow-y-auto py-8">
    <div class="bg-[#141414] border border-white/10 rounded-3xl p-8 max-w-lg w-full mx-4 shadow-2xl relative">
      <button onclick="closeModal('dashModal')" class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-neutral-500 hover:text-white transition-all">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-green-600 flex items-center justify-center"><i data-lucide="shield" class="w-5 h-5 text-white"></i></div>
        <div><h3 class="font-bold">Dashboard Admin</h3><p class="text-xs text-neutral-500">Selamat datang, Bendahara</p></div>
      </div>
      <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="bg-green-600/10 border border-green-600/20 rounded-xl p-4">
          <div class="text-xs text-green-400 font-medium">Hari Ini</div>
          <div class="text-lg font-extrabold stat-num mt-1">+Rp 350rb</div>
        </div>
        <div class="bg-red-600/10 border border-red-600/20 rounded-xl p-4">
          <div class="text-xs text-red-400 font-medium">Pengeluaran</div>
          <div class="text-lg font-extrabold text-red-400 mt-1">Rp 0</div>
        </div>
      </div>
      <div class="text-xs font-bold uppercase tracking-wider text-neutral-500 mb-3">Aktivitas Terkini</div>
      <div class="space-y-2 mb-6">
        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/3 border border-white/5">
          <div class="w-7 h-7 rounded-lg bg-green-600/15 flex items-center justify-center"><i data-lucide="arrow-down-left" class="w-3.5 h-3.5 text-green-400"></i></div>
          <div class="flex-1"><div class="text-sm font-medium">Ahmad Fauzi — Kas Juni</div><div class="text-[11px] text-neutral-600">10 menit lalu</div></div>
          <span class="text-xs font-bold text-green-400">+30rb</span>
        </div>
        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/3 border border-white/5">
          <div class="w-7 h-7 rounded-lg bg-green-600/15 flex items-center justify-center"><i data-lucide="arrow-down-left" class="w-3.5 h-3.5 text-green-400"></i></div>
          <div class="flex-1"><div class="text-sm font-medium">Siti Nurhaliza — Kas Juni</div><div class="text-[11px] text-neutral-600">25 menit lalu</div></div>
          <span class="text-xs font-bold text-green-400">+30rb</span>
        </div>
        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/3 border border-white/5">
          <div class="w-7 h-7 rounded-lg bg-red-600/15 flex items-center justify-center"><i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-red-400"></i></div>
          <div class="flex-1"><div class="text-sm font-medium">Beli Spidol & Papan</div><div class="text-[11px] text-neutral-600">1 jam lalu</div></div>
          <span class="text-xs font-bold text-red-400">-85rb</span>
        </div>
      </div>
      <button onclick="closeModal('dashModal')" class="w-full border border-white/10 text-white font-medium py-3 rounded-xl text-sm hover:bg-white/5 transition-all">Tutup</button>
    </div>
  </div>

  <!-- Siswa Result Modal -->
  <div id="swResultModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 backdrop-blur-sm overflow-y-auto py-8">
    <div class="bg-[#141414] border border-white/10 rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl relative">
      <button onclick="closeModal('swResultModal')" class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-neutral-500 hover:text-white transition-all">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
      <div class="text-center mb-6">
        <div class="w-14 h-14 rounded-full bg-green-600/20 flex items-center justify-center mx-auto mb-3 text-green-400 text-xl font-bold border border-green-600/30">AF</div>
        <h3 class="font-bold text-lg">Ahmad Fauzi</h3>
        <p class="text-xs text-neutral-500">NIS: 2024001 • Kelas X-A</p>
      </div>
      <div class="bg-green-600/10 border border-green-600/20 rounded-xl p-4 mb-5 flex items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-green-600 flex items-center justify-center"><i data-lucide="check" class="w-4 h-4 text-white"></i></div>
        <div class="flex-1"><div class="font-bold text-sm">Juni 2025 — LUNAS</div><div class="text-[11px] text-neutral-500">Dibayar 12 Jun 2025 · Rp 30.000</div></div>
      </div>
      <div class="text-xs font-bold uppercase tracking-wider text-neutral-500 mb-3">Riwayat</div>
      <div class="space-y-2 mb-6">
        <div class="flex justify-between p-3 rounded-lg bg-white/3 border border-white/5 text-sm"><span>Juni 2025</span><span class="text-green-400 text-xs font-bold">LUNAS</span></div>
        <div class="flex justify-between p-3 rounded-lg bg-white/3 border border-white/5 text-sm"><span>Mei 2025</span><span class="text-green-400 text-xs font-bold">LUNAS</span></div>
        <div class="flex justify-between p-3 rounded-lg bg-white/3 border border-white/5 text-sm"><span>April 2025</span><span class="text-green-400 text-xs font-bold">LUNAS</span></div>
        <div class="flex justify-between p-3 rounded-lg bg-white/3 border border-white/5 text-sm"><span>Maret 2025</span><span class="text-green-400 text-xs font-bold">LUNAS</span></div>
        <div class="flex justify-between p-3 rounded-lg bg-white/3 border border-white/5 text-sm"><span>Februari 2025</span><span class="text-green-400 text-xs font-bold">LUNAS</span></div>
        <div class="flex justify-between p-3 rounded-lg bg-white/3 border border-white/5 text-sm"><span>Januari 2025</span><span class="text-green-400 text-xs font-bold">LUNAS</span></div>
      </div>
      <button onclick="closeModal('swResultModal')" class="w-full border border-white/10 text-white font-medium py-3 rounded-xl text-sm hover:bg-white/5 transition-all">Tutup</button>
    </div>
  </div>

  <!-- Toast -->
  <div id="toastBox" class="fixed top-20 right-6 z-[200] space-y-3"></div>

  <script>
    const payments = [
      { name:'Ahmad Fauzi', kelas:'X-A', month:'Juni', amount:30000, status:'lunas', date:'12 Jun 2025' },
      { name:'Siti Nurhaliza', kelas:'X-B', month:'Juni', amount:30000, status:'lunas', date:'12 Jun 2025' },
      { name:'Budi Santoso', kelas:'XI-A', month:'Juni', amount:30000, status:'lunas', date:'11 Jun 2025' },
      { name:'Dewi Lestari', kelas:'XI-B', month:'Juni', amount:30000, status:'belum', date:'-' },
      { name:'Rizky Pratama', kelas:'XII-A', month:'Mei', amount:30000, status:'lunas', date:'15 Mei 2025' },
      { name:'Anisa Rahma', kelas:'X-A', month:'Mei', amount:30000, status:'lunas', date:'14 Mei 2025' },
      { name:'Fajar Nugroho', kelas:'XI-A', month:'April', amount:30000, status:'lunas', date:'10 Apr 2025' },
      { name:'Putri Wulandari', kelas:'XII-B', month:'Maret', amount:30000, status:'terlambat', date:'20 Mar 2025' },
    ];

    const expenses = [
      { title:'Spidol & Papan Tulis', amount:85000, date:'12 Jun 2025', cat:'Alat Tulis', icon:'pencil' },
      { title:'Cetak Foto Kelas XII', amount:250000, date:'10 Jun 2025', cat:'Dokumentasi', icon:'camera' },
      { title:'Snack Rapat OSIS', amount:120000, date:'8 Jun 2025', cat:'Konsumsi', icon:'coffee' },
      { title:'Perbaikan Kipas Angin', amount:200000, date:'5 Jun 2025', cat:'Pemeliharaan', icon:'wrench' },
      { title:'Sampah Plastik Besar', amount:35000, date:'3 Jun 2025', cat:'Kebersihan', icon:'trash-2' },
      { title:'Banner HUT RI Ke-80', amount:300000, date:'1 Jun 2025', cat:'Kegiatan', icon:'flag' },
    ];

    function renderPayments(filter='all') {
      const data = filter === 'all' ? payments : payments.filter(p => p.month === filter);
      document.getElementById('payTable').innerHTML = data.map(p => {
        const st = p.status === 'lunas'
          ? '<span class="inline-flex items-center gap-1 text-[11px] font-bold bg-green-600/15 text-green-400 px-3 py-1 rounded-full"><span class="w-1 h-1 rounded-full bg-green-500"></span>Lunas</span>'
          : p.status === 'terlambat'
          ? '<span class="inline-flex items-center gap-1 text-[11px] font-bold bg-yellow-600/15 text-yellow-400 px-3 py-1 rounded-full"><span class="w-1 h-1 rounded-full bg-yellow-500"></span>Terlambat</span>'
          : '<span class="inline-flex items-center gap-1 text-[11px] font-bold bg-red-600/15 text-red-400 px-3 py-1 rounded-full"><span class="w-1 h-1 rounded-full bg-red-500"></span>Belum</span>';
        return `<tr class="data-row border-b border-white/5">
          <td class="px-6 py-4 font-medium">${p.name}</td>
          <td class="px-6 py-4 text-neutral-500">${p.kelas}</td>
          <td class="px-6 py-4 text-neutral-500">${p.month} 2025</td>
          <td class="px-6 py-4 text-right font-medium">Rp ${p.amount.toLocaleString('id-ID')}</td>
          <td class="px-6 py-4 text-center">${st}</td>
          <td class="px-6 py-4 text-center text-neutral-600">${p.date}</td>
        </tr>`;
      }).join('');
    }

    function renderExpenses() {
      document.getElementById('expenseGrid').innerHTML = expenses.map(e =>
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

    function doLogin(e, role) {
      e.preventDefault();
      if (role === 'admin') {
        if (document.getElementById('admUser').value === 'admin' && document.getElementById('admPass').value === 'admin123') {
          closeModal('adminModal'); toast('Login berhasil! Selamat datang Admin.','green');
          setTimeout(() => openModal('dashModal'), 400);
        } else toast('Username atau password salah!','red');
      } else {
        if (document.getElementById('swNis').value === '2024001' && document.getElementById('swPass').value === 'siswa123') {
          closeModal('siswaModal'); toast('Login berhasil! Selamat datang Ahmad.','green');
          setTimeout(() => openModal('swResultModal'), 400);
        } else toast('NIS atau password salah!','red');
      }
    }

    function toast(msg, color) {
      const box = document.getElementById('toastBox');
      const t = document.createElement('div');
      t.className = `bg-${color}-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2 animate-[slideIn_0.3s_ease-out]`;
      t.innerHTML = `<i data-lucide="${color==='green'?'check-circle':'alert-circle'}" class="w-4 h-4"></i>${msg}`;
      box.appendChild(t); lucide.createIcons();
      setTimeout(() => { t.style.opacity='0'; t.style.transition='opacity 0.3s'; setTimeout(()=>t.remove(),300); }, 2800);
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