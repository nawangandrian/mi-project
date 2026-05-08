<!-- ===== SIDEBAR ===== -->
<aside id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="logo-icon">
            <i class="bi bi-bar-chart-line-fill"></i>
        </div>
        <div class="logo-text">
            <span class="logo-brand">Mi Store</span>
            <span class="logo-sub">Kudus · Analytics</span>
        </div>
        <button id="sidebar-toggle" title="Toggle Sidebar">
            <i class="bi bi-layout-sidebar-reverse"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <div class="nav-section-label">Main</div>

        <a href="<?= base_url('dashboard') ?>" class="nav-item <?= (uri_string() === 'dashboard' || uri_string() === '') ? 'active' : '' ?>" data-tooltip="Dashboard">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        <div class="nav-section-label">Prediksi</div>

        <a href="<?= base_url('prediksi') ?>" class="nav-item <?= (uri_string() === 'prediksi') ? 'active' : '' ?>" data-tooltip="Prediksi Penjualan">
            <i class="bi bi-graph-up-arrow"></i>
            <span>Prediksi Penjualan</span>
            <span class="nav-badge">AI</span>
        </a>

        <a href="<?= base_url('prediksi/jalankan') ?>" class="nav-item <?= (uri_string() === 'prediksi/jalankan') ? 'active' : '' ?>" data-tooltip="Jalankan Model">
            <i class="bi bi-play-circle-fill"></i>
            <span>Jalankan Prediksi</span>
        </a>

        <a href="<?= base_url('prediksi/riwayat') ?>" class="nav-item <?= (uri_string() === 'prediksi/riwayat') ? 'active' : '' ?>" data-tooltip="Riwayat Prediksi">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat Prediksi</span>
        </a>

        <a href="<?= base_url('prediksi/akurasi') ?>" class="nav-item <?= (uri_string() === 'prediksi/akurasi') ? 'active' : '' ?>" data-tooltip="Evaluasi Model">
            <i class="bi bi-bullseye"></i>
            <span>Evaluasi Model</span>
        </a>

        <div class="nav-section-label">Data</div>

        <a href="<?= base_url('import') ?>" class="nav-item <?= str_starts_with(uri_string(), 'import') ? 'active' : '' ?>" data-tooltip="Import Data">
            <i class="bi bi-cloud-arrow-up"></i>
            <span>Import Data</span>
        </a>

        <a href="<?= base_url('produk') ?>" class="nav-item <?= str_starts_with(uri_string(), 'produk') ? 'active' : '' ?>" data-tooltip="Data Produk">
            <i class="bi bi-phone-fill"></i>
            <span>Data Produk</span>
        </a>

        <a href="<?= base_url('penjualan') ?>" class="nav-item <?= str_starts_with(uri_string(), 'penjualan') ? 'active' : '' ?>" data-tooltip="Data Penjualan">
            <i class="bi bi-cart3"></i>
            <span>Data Penjualan</span>
        </a>

        <a href="<?= base_url('training') ?>" class="nav-item <?= str_starts_with(uri_string(), 'training') ? 'active' : '' ?>" data-tooltip="Data Training">
            <i class="bi bi-cpu-fill"></i>
            <span>Latih Model</span>
            <span class="nav-badge">ML</span>
        </a>

        <div class="nav-section-label">Sistem</div>

        <a href="<?= base_url('user') ?>" class="nav-item <?= str_starts_with(uri_string(), 'user') ? 'active' : '' ?>" data-tooltip="Manajemen User">
            <i class="bi bi-people-fill"></i>
            <span>Manajemen User</span>
        </a>

    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-footer-card">
            <i class="bi bi-robot" style="font-size:18px;color:var(--accent-cyan);flex-shrink:0"></i>
            <div class="sidebar-footer-text">
                <div style="font-size:12px;font-weight:600;color:var(--text-on-dark)">Random Forest</div>
                <div style="font-size:11px;color:var(--text-on-dark-3)">Model Aktif · v2.1</div>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Overlay (must be outside #sidebar) -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<style>
    /* ===== SIDEBAR ===== */
    #sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: var(--sidebar-width);
        height: 100vh;
        background: var(--sidebar-bg);
        border-right: 1px solid var(--sidebar-border);
        display: flex;
        flex-direction: column;
        z-index: 1000;
        overflow-y: auto;
        overflow-x: hidden;
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        scrollbar-width: none;
        will-change: transform, width;
    }

    #sidebar::-webkit-scrollbar {
        display: none;
    }

    /* ── Logo ── */
    .sidebar-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 18px 16px;
        border-bottom: 1px solid var(--sidebar-border);
        min-height: var(--header-height);
        flex-shrink: 0;
        position: relative;
    }

    .logo-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, var(--mg-glow), var(--accent-cyan));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 0 16px rgba(0, 151, 184, 0.3);
    }

    .logo-text {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: opacity 0.2s, width 0.3s;
        flex: 1;
        min-width: 0;
    }

    .logo-brand {
        font-family: var(--font-display);
        font-size: 15px;
        font-weight: 700;
        color: var(--text-on-dark);
        line-height: 1.2;
        white-space: nowrap;
    }

    .logo-sub {
        font-size: 10px;
        color: var(--text-on-dark-3);
        letter-spacing: 0.06em;
        white-space: nowrap;
        text-transform: uppercase;
    }

    #sidebar-toggle {
        background: none;
        border: none;
        color: var(--text-on-dark-3);
        cursor: pointer;
        font-size: 16px;
        padding: 6px;
        border-radius: 6px;
        transition: var(--transition);
        flex-shrink: 0;
    }

    #sidebar-toggle:hover {
        color: var(--accent-cyan);
        background: rgba(0, 151, 184, 0.08);
    }

    /* ── Nav ── */
    .sidebar-nav {
        padding: 14px 10px;
        flex: 1;
    }

    .nav-section-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--text-on-dark-3);
        padding: 14px 10px 5px;
        white-space: nowrap;
        overflow: hidden;
        transition: opacity 0.2s;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 9px 12px;
        border-radius: var(--radius-sm);
        color: var(--text-on-dark-2);
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 400;
        transition: var(--transition);
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        position: relative;
        border: 1px solid transparent;
    }

    .nav-item i {
        font-size: 15px;
        flex-shrink: 0;
        width: 18px;
        text-align: center;
    }

    .nav-item>span:not(.nav-badge) {
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
        min-width: 0;
    }

    .nav-item:hover {
        background: rgba(36, 59, 85, 0.6);
        color: var(--text-on-dark);
    }

    .nav-item.active {
        background: linear-gradient(135deg, rgba(46, 109, 164, 0.3), rgba(0, 151, 184, 0.1));
        color: var(--accent-cyan);
        border-color: rgba(0, 151, 184, 0.2);
        font-weight: 500;
    }

    .nav-item.active i {
        color: var(--accent-cyan);
    }

    .nav-badge {
        margin-left: auto;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.06em;
        padding: 2px 7px;
        border-radius: 20px;
        background: linear-gradient(135deg, var(--mg-glow), var(--accent-cyan));
        color: #fff;
        flex-shrink: 0;
    }

    /* ── Footer ── */
    .sidebar-footer {
        padding: 14px 10px;
        border-top: 1px solid var(--sidebar-border);
        flex-shrink: 0;
    }

    .sidebar-footer-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 12px;
        background: rgba(36, 59, 85, 0.4);
        border-radius: var(--radius-md);
        border: 1px solid var(--sidebar-border);
        overflow: hidden;
    }

    .sidebar-footer-text {
        overflow: hidden;
        transition: opacity 0.2s;
    }

    /* ===== COLLAPSED (desktop) ===== */
    #sidebar.collapsed {
        width: 70px;
    }

    #sidebar.collapsed .logo-text,
    #sidebar.collapsed .nav-section-label,
    #sidebar.collapsed .nav-badge,
    #sidebar.collapsed .sidebar-footer-text {
        opacity: 0;
        pointer-events: none;
        width: 0;
        overflow: hidden;
    }

    #sidebar.collapsed .nav-item>span:not(.nav-badge) {
        opacity: 0;
        width: 0;
        overflow: hidden;
        pointer-events: none;
    }

    #sidebar.collapsed .nav-item {
        justify-content: center;
        padding: 11px 0;
    }

    #sidebar.collapsed .sidebar-logo {
        justify-content: center;
        padding: 18px 0 16px;
    }

    #sidebar.collapsed #sidebar-toggle {
        margin-left: 0;
    }

    #sidebar.collapsed .sidebar-footer-card {
        justify-content: center;
        padding: 11px 0;
    }

    /* ===== MOBILE ===== */
    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(10, 15, 22, 0.65);
        z-index: 999;
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }

    @media (max-width: 768px) {
        #sidebar {
            transform: translateX(-100%);
            width: var(--sidebar-width) !important;
            /* always full width on mobile */
            box-shadow: 6px 0 40px rgba(0, 0, 0, 0.5);
        }

        #sidebar.mobile-open {
            transform: translateX(0);
        }

        .sidebar-overlay {
            display: block;
        }

        .sidebar-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        /* Reset collapsed on mobile */
        #sidebar.collapsed.mobile-open {
            width: var(--sidebar-width) !important;
        }

        #sidebar.collapsed.mobile-open .logo-text,
        #sidebar.collapsed.mobile-open .nav-section-label,
        #sidebar.collapsed.mobile-open .nav-badge,
        #sidebar.collapsed.mobile-open .sidebar-footer-text,
        #sidebar.collapsed.mobile-open .nav-item>span:not(.nav-badge) {
            opacity: 1;
            pointer-events: auto;
            width: auto;
            overflow: visible;
        }

        #sidebar.collapsed.mobile-open .nav-item {
            justify-content: flex-start;
            padding: 9px 12px;
        }

        #sidebar.collapsed.mobile-open .sidebar-logo {
            justify-content: flex-start;
            padding: 18px 18px 16px;
        }

        #sidebar.collapsed.mobile-open .sidebar-footer-card {
            justify-content: flex-start;
            padding: 11px 12px;
        }
    }

    /* =====================================================
   SIDEBAR COLLAPSED FIX — tambahkan di layout CSS
   ===================================================== */

    /* Saat collapsed, semua nav-item center + ukuran konsisten */
    #sidebar.collapsed .nav-item {
        justify-content: center !important;
        padding: 11px 0 !important;
        margin-bottom: 4px !important;
        width: 100% !important;
    }

    /* Ikon tetap ukuran fix dan center */
    #sidebar.collapsed .nav-item i {
        font-size: 18px !important;
        width: 22px !important;
        text-align: center !important;
        margin: 0 auto !important;
        flex-shrink: 0 !important;
    }

    /* Sembunyikan semua teks & badge */
    #sidebar.collapsed .nav-item>span,
    #sidebar.collapsed .nav-item .nav-badge,
    #sidebar.collapsed .nav-section-label,
    #sidebar.collapsed .logo-text,
    #sidebar.collapsed .sidebar-footer-text {
        display: none !important;
    }

    /* Logo area center */
    #sidebar.collapsed .sidebar-logo {
        justify-content: center !important;
        padding: 18px 0 !important;
        gap: 0 !important;
    }

    /* Toggle button tetap tampil */
    #sidebar.collapsed #sidebar-toggle {
        display: flex !important;
        margin: 0 auto !important;
    }

    /* Nav padding saat collapsed */
    #sidebar.collapsed .sidebar-nav {
        padding: 14px 6px !important;
    }

    /* Footer card center */
    #sidebar.collapsed .sidebar-footer-card {
        justify-content: center !important;
        padding: 11px 0 !important;
        gap: 0 !important;
    }

    /* Hover tooltip saat collapsed */
    #sidebar.collapsed .nav-item {
        position: relative;
    }

    #sidebar.collapsed .nav-item::after {
        content: attr(data-tooltip);
        position: absolute;
        left: calc(100% + 12px);
        top: 50%;
        transform: translateY(-50%);
        background: #1e2d42;
        color: #e8f1f8;
        font-size: 12px;
        font-weight: 500;
        padding: 6px 12px;
        border-radius: 6px;
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        border: 1px solid rgba(36, 59, 85, 0.8);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        transition: opacity 0.15s ease;
        z-index: 1200;
    }

    #sidebar.collapsed .nav-item:hover::after {
        opacity: 1;
    }

    @media (max-width: 768px) {

        /* Sidebar tersembunyi di kiri, slide masuk via mobile-open */
        #sidebar {
            transform: translateX(-100%);
            width: var(--sidebar-width) !important;
            box-shadow: 6px 0 40px rgba(0, 0, 0, 0.5);
        }

        #sidebar.mobile-open {
            transform: translateX(0);
        }

        /* Sembunyikan toggle collapse di mobile */
        #sidebar-toggle {
            display: none !important;
        }

        /* Reset SEMUA efek collapsed di mobile */
        #sidebar .logo-text,
        #sidebar .sidebar-footer-text {
            opacity: 1 !important;
            max-width: 200px !important;
            pointer-events: auto !important;
        }

        #sidebar .nav-section-label {
            opacity: 1 !important;
            max-height: 40px !important;
            padding: 14px 10px 5px !important;
            pointer-events: auto !important;
        }

        #sidebar .nav-item>span:not(.nav-badge),
        #sidebar .nav-badge {
            opacity: 1 !important;
            width: auto !important;
            overflow: visible !important;
            pointer-events: auto !important;
        }

        #sidebar .nav-item {
            justify-content: flex-start !important;
            padding: 9px 12px !important;
            gap: 11px !important;
        }

        #sidebar .nav-item i {
            font-size: 15px !important;
            width: 18px !important;
            margin: 0 !important;
        }

        #sidebar .sidebar-logo {
            justify-content: flex-start !important;
            padding: 18px 18px 16px !important;
            gap: 12px !important;
        }

        #sidebar .sidebar-nav {
            padding: 14px 10px !important;
        }

        #sidebar .sidebar-footer {
            padding: 14px 10px !important;
        }

        #sidebar .sidebar-footer-card {
            justify-content: flex-start !important;
            padding: 11px 12px !important;
            gap: 12px !important;
        }

        /* Sembunyikan tooltip di mobile */
        #sidebar .nav-item::after {
            display: none !important;
        }

        /* Overlay */
        .sidebar-overlay {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(10, 15, 22, 0.65);
            z-index: 999;
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .sidebar-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
    }
</style>