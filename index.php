<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UangKas Kelas - Sistem Iuran Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --green-900: #064e3b;
            --green-700: #047857;
            --green-600: #059669;
            --green-500: #10b981;
            --green-100: #d1fae5;
            --green-50: #ecfdf5;
            --gray-900: #111827;
            --gray-700: #374151;
            --gray-500: #6b7280;
            --gray-300: #d1d5db;
            --gray-100: #f3f4f6;
            --gray-50: #f9fafb;
            --white: #ffffff;
            --red-600: #dc2626;
            --red-100: #fee2e2;
            --amber-600: #d97706;
            --amber-100: #fef3c7;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Navigation */
        nav {
            background: var(--white);
            border-bottom: 1px solid var(--gray-100);
            padding: 18px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 20px;
            font-weight: 700;
            color: var(--gray-900);
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .logo span {
            color: var(--green-600);
        }

        .nav-links {
            display: flex;
            gap: 32px;
            list-style: none;
        }

        .nav-links a {
            color: var(--gray-700);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: var(--green-600);
        }

        .login-btn {
            background: var(--gray-900);
            color: var(--white);
            padding: 8px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: background 0.2s;
        }

        .login-btn:hover {
            background: var(--green-600);
        }

        /* Hero Section */
        .hero {
            padding: 80px 0 60px;
        }

        .hero-badge {
            display: inline-block;
            background: var(--green-50);
            color: var(--green-700);
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
            border: 1px solid var(--green-100);
        }

        .hero h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--gray-900);
            line-height: 1.15;
            letter-spacing: -1px;
        }

        .hero h1 span {
            color: var(--green-600);
        }

        .hero p {
            font-size: 17px;
            color: var(--gray-500);
            max-width: 540px;
            margin-bottom: 32px;
            line-height: 1.7;
        }

        .hero-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: var(--green-600);
            color: var(--white);
            padding: 12px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: var(--green-700);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--gray-900);
            padding: 12px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            border: 1px solid var(--gray-300);
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            border-color: var(--gray-900);
            background: var(--gray-50);
        }

        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin: 48px 0;
        }

        .stat-card {
            background: var(--white);
            padding: 24px;
            border-radius: 8px;
            border: 1px solid var(--gray-100);
        }

        .stat-label {
            font-size: 13px;
            color: var(--gray-500);
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .stat-value.positive {
            color: var(--green-600);
        }

        .stat-sub {
            font-size: 13px;
            color: var(--gray-500);
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 64px;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--gray-100);
            border-radius: 8px;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--gray-900);
        }

        .card-badge {
            background: var(--gray-100);
            color: var(--gray-700);
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .card-body {
            padding: 0 24px;
        }

        /* Member List */
        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .item-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            background: var(--green-50);
            color: var(--green-700);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 13px;
        }

        .item-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--gray-900);
        }

        .item-role {
            font-size: 12px;
            color: var(--gray-500);
        }

        .status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-paid {
            background: var(--green-50);
            color: var(--green-700);
        }

        .status-pending {
            background: var(--amber-100);
            color: var(--amber-600);
        }

        /* Transaction */
        .transaction-icon {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .icon-income {
            background: var(--green-50);
            color: var(--green-700);
        }

        .icon-expense {
            background: var(--red-100);
            color: var(--red-600);
        }

        .transaction-name {
            font-weight: 500;
            font-size: 14px;
            color: var(--gray-900);
        }

        .transaction-date {
            font-size: 12px;
            color: var(--gray-500);
            margin-top: 2px;
        }

        .transaction-amount {
            font-weight: 600;
            font-size: 14px;
        }

        .amount-positive {
            color: var(--green-600);
        }

        .amount-negative {
            color: var(--red-600);
        }

        /* Features */
        .features {
            margin: 64px 0;
        }

        .section-header {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .section-subtitle {
            color: var(--gray-500);
            font-size: 16px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .feature-card {
            background: var(--white);
            padding: 24px;
            border-radius: 8px;
            border: 1px solid var(--gray-100);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: var(--green-50);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .feature-icon-inner {
            width: 20px;
            height: 20px;
            background: var(--green-600);
            border-radius: 4px;
        }

        .feature-card h3 {
            font-size: 15px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .feature-card p {
            font-size: 13px;
            color: var(--gray-500);
            line-height: 1.6;
        }

        /* CTA */
        .cta-section {
            background: var(--gray-900);
            padding: 56px 40px;
            border-radius: 8px;
            margin: 64px 0;
            text-align: center;
            color: var(--white);
        }

        .cta-section h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .cta-section p {
            font-size: 15px;
            color: var(--gray-300);
            margin-bottom: 28px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-button {
            background: var(--green-500);
            color: var(--white);
            padding: 12px 32px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            display: inline-block;
            transition: background 0.2s;
        }

        .cta-button:hover {
            background: var(--green-600);
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--gray-100);
            padding: 24px 0;
            text-align: center;
            color: var(--gray-500);
            font-size: 13px;
        }

        footer span {
            color: var(--green-600);
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero {
                padding: 48px 0 40px;
            }

            .hero h1 {
                font-size: 32px;
            }

            .stats {
                grid-template-columns: 1fr 1fr;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .features-grid {
                grid-template-columns: 1fr 1fr;
            }

            .cta-section {
                padding: 40px 24px;
            }

            .cta-section h2 {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .stats,
            .features-grid {
                grid-template-columns: 1fr;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .hero-buttons a {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="container nav-content">
            <a href="#" class="logo">Uang<span>Kas</span></a>
            <ul class="nav-links">
                <li><a href="#dashboard">Dashboard</a></li>
                <li><a href="#anggota">Anggota</a></li>
                <li><a href="#transaksi">Transaksi</a></li>
                <li><a href="#fitur">Fitur</a></li>
            </ul>
            <a href="#" class="login-btn">Masuk</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <span class="hero-badge">Sistem Keuangan Digital</span>
            <h1>Kelola uang kas kelas<br>dengan <span>transparan</span> dan efisien.</h1>
            <p>Catat iuran, pengeluaran, dan laporan keuangan kelas secara real-time. Data tersimpan rapi dan dapat diakses kapan saja oleh seluruh anggota.</p>
            <div class="hero-buttons">
                <a href="#" class="btn-primary">Mulai Sekarang</a>
                <a href="#fitur" class="btn-secondary">Pelajari Lebih Lanjut</a>
            </div>

            <!-- Stats -->
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-label">Saldo Saat Ini</div>
                    <div class="stat-value positive">Rp 850.000</div>
                    <div class="stat-sub">+Rp 150.000 minggu ini</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Anggota</div>
                    <div class="stat-value">32</div>
                    <div class="stat-sub">28 telah melunasi</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Pemasukan Bulan Ini</div>
                    <div class="stat-value positive">Rp 1.200.000</div>
                    <div class="stat-sub">Dari iuran anggota</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Pengeluaran Bulan Ini</div>
                    <div class="stat-value">Rp 350.000</div>
                    <div class="stat-sub">Untuk kegiatan kelas</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <div class="content-grid">
            <!-- Member List -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Status Iuran Anggota</h2>
                    <span class="card-badge">Agustus 2026</span>
                </div>
                <div class="card-body">
                    <div class="list-item">
                        <div class="item-info">
                            <div class="avatar">AS</div>
                            <div>
                                <div class="item-name">Ahmad Surya</div>
                                <div class="item-role">Ketua Kelas</div>
                            </div>
                        </div>
                        <span class="status status-paid">Lunas</span>
                    </div>
                    <div class="list-item">
                        <div class="item-info">
                            <div class="avatar">DW</div>
                            <div>
                                <div class="item-name">Dewi Wulandari</div>
                                <div class="item-role">Bendahara</div>
                            </div>
                        </div>
                        <span class="status status-paid">Lunas</span>
                    </div>
                    <div class="list-item">
                        <div class="item-info">
                            <div class="avatar">RP</div>
                            <div>
                                <div class="item-name">Rizky Pratama</div>
                                <div class="item-role">Anggota</div>
                            </div>
                        </div>
                        <span class="status status-pending">Belum</span>
                    </div>
                    <div class="list-item">
                        <div class="item-info">
                            <div class="avatar">NF</div>
                            <div>
                                <div class="item-name">Nadia Fitriani</div>
                                <div class="item-role">Anggota</div>
                            </div>
                        </div>
                        <span class="status status-paid">Lunas</span>
                    </div>
                    <div class="list-item">
                        <div class="item-info">
                            <div class="avatar">BK</div>
                            <div>
                                <div class="item-name">Budi Kurniawan</div>
                                <div class="item-role">Sekretaris</div>
                            </div>
                        </div>
                        <span class="status status-paid">Lunas</span>
                    </div>
                </div>
            </div>

            <!-- Transaction List -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Transaksi Terbaru</h2>
                    <span class="card-badge">5 terakhir</span>
                </div>
                <div class="card-body">
                    <div class="list-item">
                        <div class="item-info">
                            <div class="transaction-icon icon-income">IN</div>
                            <div>
                                <div class="transaction-name">Iuran Mingguan - Ahmad</div>
                                <div class="transaction-date">7 Agustus 2026</div>
                            </div>
                        </div>
                        <div class="transaction-amount amount-positive">+Rp 15.000</div>
                    </div>
                    <div class="list-item">
                        <div class="item-info">
                            <div class="transaction-icon icon-expense">OUT</div>
                            <div>
                                <div class="transaction-name">Beli Spidol dan ATK</div>
                                <div class="transaction-date">6 Agustus 2026</div>
                            </div>
                        </div>
                        <div class="transaction-amount amount-negative">-Rp 45.000</div>
                    </div>
                    <div class="list-item">
                        <div class="item-info">
                            <div class="transaction-icon icon-income">IN</div>
                            <div>
                                <div class="transaction-name">Iuran Mingguan - Dewi</div>
                                <div class="transaction-date">5 Agustus 2026</div>
                            </div>
                        </div>
                        <div class="transaction-amount amount-positive">+Rp 15.000</div>
                    </div>
                    <div class="list-item">
                        <div class="item-info">
                            <div class="transaction-icon icon-expense">OUT</div>
                            <div>
                                <div class="transaction-name">Print Foto Kelas</div>
                                <div class="transaction-date">3 Agustus 2026</div>
                            </div>
                        </div>
                        <div class="transaction-amount amount-negative">-Rp 75.000</div>
                    </div>
                    <div class="list-item">
                        <div class="item-info">
                            <div class="transaction-icon icon-income">IN</div>
                            <div>
                                <div class="transaction-name">Donasi Kegiatan</div>
                                <div class="transaction-date">1 Agustus 2026</div>
                            </div>
                        </div>
                        <div class="transaction-amount amount-positive">+Rp 100.000</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <section class="features" id="fitur">
            <div class="section-header">
                <h2 class="section-title">Keunggulan Sistem</h2>
                <p class="section-subtitle">Fitur lengkap untuk memudahkan pengelolaan keuangan kelas</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <div class="feature-icon-inner"></div>
                    </div>
                    <h3>Laporan Real-Time</h3>
                    <p>Pantau pemasukan dan pengeluaran kapan saja dengan laporan yang selalu diperbarui</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <div class="feature-icon-inner"></div>
                    </div>
                    <h3>Notifikasi Otomatis</h3>
                    <p>Ingatkan anggota yang belum membayar iuran melalui notifikasi otomatis</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <div class="feature-icon-inner"></div>
                    </div>
                    <h3>Aman dan Transparan</h3>
                    <p>Seluruh anggota dapat melihat laporan, menciptakan kepercayaan bersama</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <div class="feature-icon-inner"></div>
                    </div>
                    <h3>Akses Multi-Device</h3>
                    <p>Dapat diakses dari perangkat mana saja, baik ponsel maupun laptop</p>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="cta-section">
            <h2>Siap mengelola uang kas dengan cara modern?</h2>
            <p>Bergabung sekarang dan buat pengelolaan uang kas kelas Anda menjadi lebih mudah dan terorganisir.</p>
            <a href="#" class="cta-button">Daftar Gratis</a>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>© 2026 UangKas Kelas. Dibuat untuk sekolah <span>UangKas</span></p>
        </div>
    </footer>
</body>
</html>