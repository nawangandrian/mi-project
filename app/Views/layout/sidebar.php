<!-- ===== SIDEBAR ===== -->
<?php
    /**
     * Variabel yang tersedia dari BaseController::renderPage():
     *   $model_aktif  — array|null dari ModelTrainingModel::getAktif()
     *
     * Variabel session yang relevan:
     *   session()->get('role')  — 'admin' | 'cs' | dsb.
     */
    $role  = session()->get('role') ?? 'cs';   // default ke role paling terbatas
    $isCs  = ($role === 'cs');                  // CS hanya lihat Dashboard + Data
    $uri   = uri_string();
?>
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

        <!-- ── MAIN (semua role) ── -->
        <div class="nav-section-label">Main</div>

        <a href="<?= base_url('dashboard') ?>"
           class="nav-item <?= ($uri === 'dashboard' || $uri === '') ? 'active' : '' ?>"
           data-tooltip="Dashboard">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        <!-- ── PREDIKSI (khusus non-CS) ── -->
        <?php if (! $isCs): ?>
        <div class="nav-section-label">Prediksi</div>

        <a href="<?= base_url('prediksi') ?>"
           class="nav-item <?= ($uri === 'prediksi') ? 'active' : '' ?>"
           data-tooltip="Prediksi Penjualan">
            <i class="bi bi-graph-up-arrow"></i>
            <span>Prediksi Penjualan</span>
            <span class="nav-badge">AI</span>
        </a>

        <a href="<?= base_url('prediksi/jalankan') ?>"
           class="nav-item <?= ($uri === 'prediksi/jalankan') ? 'active' : '' ?>"
           data-tooltip="Jalankan Model">
            <i class="bi bi-play-circle-fill"></i>
            <span>Jalankan Prediksi</span>
        </a>

        <a href="<?= base_url('prediksi/riwayat') ?>"
           class="nav-item <?= ($uri === 'prediksi/riwayat') ? 'active' : '' ?>"
           data-tooltip="Riwayat Prediksi">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat Prediksi</span>
        </a>

        <a href="<?= base_url('prediksi/akurasi') ?>"
           class="nav-item <?= ($uri === 'prediksi/akurasi') ? 'active' : '' ?>"
           data-tooltip="Evaluasi Model">
            <i class="bi bi-bullseye"></i>
            <span>Evaluasi Model</span>
        </a>
        <?php endif; ?>

        <!-- ── DATA (semua role) ── -->
        <div class="nav-section-label">Data</div>
        <?php if ($isCs): ?>
    
        <a href="<?= base_url('import') ?>"
           class="nav-item <?= str_starts_with($uri, 'import') ? 'active' : '' ?>"
           data-tooltip="Import Data">
            <i class="bi bi-cloud-arrow-up"></i>
            <span>Import Data</span>
        </a>

        <a href="<?= base_url('produk') ?>"
           class="nav-item <?= str_starts_with($uri, 'produk') ? 'active' : '' ?>"
           data-tooltip="Data Produk">
            <i class="bi bi-phone-fill"></i>
            <span>Data Produk</span>
        </a>

        <a href="<?= base_url('penjualan') ?>"
           class="nav-item <?= str_starts_with($uri, 'penjualan') ? 'active' : '' ?>"
           data-tooltip="Data Penjualan">
            <i class="bi bi-cart3"></i>
            <span>Data Penjualan</span>
        </a>
        <?php endif; ?>

        <!-- ── TRAINING & SISTEM (khusus non-CS) ── -->
        <?php if (! $isCs): ?>
        <a href="<?= base_url('training') ?>"
           class="nav-item <?= str_starts_with($uri, 'training') ? 'active' : '' ?>"
           data-tooltip="Data Training">
            <i class="bi bi-cpu-fill"></i>
            <span>Latih Model</span>
            <span class="nav-badge">ML</span>
        </a>

        <div class="nav-section-label">Sistem</div>

        <a href="<?= base_url('user') ?>"
           class="nav-item <?= str_starts_with($uri, 'user') ? 'active' : '' ?>"
           data-tooltip="Manajemen User">
            <i class="bi bi-people-fill"></i>
            <span>Manajemen User</span>
        </a>
        <?php endif; ?>

    </nav>

    <!-- Sidebar Footer — Model Aktif dari DB -->
    <div class="sidebar-footer">
        <?php if (isset($model_aktif) && $model_aktif): ?>
        <div class="sidebar-footer-card">
            <i class="bi bi-robot" style="font-size:18px;color:var(--accent-cyan);flex-shrink:0"></i>
            <div class="sidebar-footer-text">
                <div style="font-size:12px;font-weight:600;color:var(--text-on-dark)">
                    <?= esc($model_aktif['nama_model'] ?? 'Random Forest') ?>
                </div>
                <div style="font-size:11px;color:var(--text-on-dark-3)">
                    Model Aktif
                    <?php if (! empty($model_aktif['versi'])): ?>
                        · <?= esc($model_aktif['versi']) ?>
                    <?php endif; ?>
                    <?php if (! empty($model_aktif['akurasi'])): ?>
                        · <?= number_format((float) $model_aktif['akurasi'], 1) ?>%
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="sidebar-footer-card">
            <i class="bi bi-robot" style="font-size:18px;color:var(--text-on-dark-3);flex-shrink:0"></i>
            <div class="sidebar-footer-text">
                <div style="font-size:12px;font-weight:600;color:var(--text-on-dark-3)">Belum Ada Model</div>
                <div style="font-size:11px;color:var(--text-on-dark-3)">Latih model terlebih dahulu</div>
            </div>
        </div>
        <?php endif; ?>
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

    /* ── Collapsed fix ── */
    #sidebar.collapsed .nav-item {
        justify-content: center !important;
        padding: 11px 0 !important;
        margin-bottom: 4px !important;
        width: 100% !important;
    }

    #sidebar.collapsed .nav-item i {
        font-size: 18px !important;
        width: 22px !important;
        text-align: center !important;
        margin: 0 auto !important;
        flex-shrink: 0 !important;
    }

    #sidebar.collapsed .nav-item>span,
    #sidebar.collapsed .nav-item .nav-badge,
    #sidebar.collapsed .nav-section-label,
    #sidebar.collapsed .logo-text,
    #sidebar.collapsed .sidebar-footer-text {
        display: none !important;
    }

    #sidebar.collapsed .sidebar-logo {
        justify-content: center !important;
        padding: 18px 0 !important;
        gap: 0 !important;
    }

    #sidebar.collapsed #sidebar-toggle {
        display: flex !important;
        margin: 0 auto !important;
    }

    #sidebar.collapsed .sidebar-nav {
        padding: 14px 6px !important;
    }

    #sidebar.collapsed .sidebar-footer-card {
        justify-content: center !important;
        padding: 11px 0 !important;
        gap: 0 !important;
    }

    /* Tooltip saat collapsed */
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
        #sidebar {
            transform: translateX(-100%);
            width: var(--sidebar-width) !important;
            box-shadow: 6px 0 40px rgba(0, 0, 0, 0.5);
        }

        #sidebar.mobile-open {
            transform: translateX(0);
        }

        #sidebar-toggle {
            display: none !important;
        }

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

        #sidebar .nav-item::after {
            display: none !important;
        }

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
<!-- ===== SIDEBAR + LAYOUT TOGGLE SCRIPT ===== -->
<!--
    Solusi: semua elemen layout (sidebar, header, main-content) membaca
    satu CSS variable --sidebar-current-width yang diupdate JS.
    Tidak ada lagi set margin/left manual → tidak ada lagi content tergeser/terpotong.
-->
<style>
    /* ── Layout Shell ── */
    :root {
        --sidebar-width        : 240px;   /* lebar penuh */
        --sidebar-collapsed-w  : 70px;    /* lebar collapsed */
        --sidebar-current-width: var(--sidebar-width); /* diupdate JS */
        --header-height        : 60px;
        --transition-sidebar   : 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Sidebar menggunakan width dari variable langsung */
    #sidebar {
        width: var(--sidebar-current-width) !important;
        transition: width var(--transition-sidebar) !important;
    }

    /* Header ikut geser */
    #main-header {
        left: var(--sidebar-current-width) !important;
        transition: left var(--transition-sidebar) !important;
    }

    /* Main content ikut geser — KUNCI PERBAIKAN */
    #main-content {
        margin-left: var(--sidebar-current-width) !important;
        transition: margin-left var(--transition-sidebar) !important;
        min-height: 100vh;
        padding-top: var(--header-height);
        box-sizing: border-box;
        /* Pastikan tidak ada overflow tersembunyi */
        overflow-x: hidden;
    }

    /* Mobile: sidebar overlay, content TIDAK bergeser */
    @media (max-width: 768px) {
        #main-header {
            left: 0 !important;
            transition: none !important;
        }
        #main-content {
            margin-left: 0 !important;
            transition: none !important;
        }
    }
<!--
    ===== LAYOUT FIX PATCH =====
    Tambahkan snippet <style> ini di layout/head.php (setelah CSS utama)
    ATAU di bagian akhir <head>.

    Ini override semua aturan lama yang hardcode margin-left / left
    agar semuanya pakai --sidebar-current-width yang dikontrol JS.
-->
/* ─────────────────────────────────────────────
   LAYOUT SHELL — semua gerak via 1 CSS variable
   ───────────────────────────────────────────── */

/* Hapus override lama yang hardcode nilai px */
#main-content {
    margin-left: var(--sidebar-current-width, 240px) !important;
    transition : margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    min-width  : 0 !important;   /* cegah overflow horizontal */
    overflow-x : hidden !important;
}

#main-header {
    left      : var(--sidebar-current-width, 240px) !important;
    transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

/* Sidebar: biarkan --sidebar-current-width yang mengatur width */
#sidebar {
    width     : var(--sidebar-current-width, 240px) !important;
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

/* ── Mobile: content tidak ikut geser ── */
@media (max-width: 768px) {
    #main-content {
        margin-left: 0 !important;
        transition : none !important;
    }
    #main-header {
        left      : 0 !important;
        transition: none !important;
    }
    /* Sidebar: posisi fixed, keluar dari flow */
    #sidebar {
        width    : 240px !important;    /* selalu full saat mobile */
        transform: translateX(-100%);   /* disembunyikan via transform */
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    #sidebar.mobile-open {
        transform: translateX(0) !important;
    }
}

/* ── Content wrapper: cegah konten terpotong ── */
.content-wrapper {
    width    : 100%;
    min-width: 0;
    overflow-x: hidden;
}
</style>

<script>
(function () {
    'use strict';

    // ── Konstanta ────────────────────────────────────────────────────────────
    const SIDEBAR_FULL      = 240;   // px, harus sama dengan --sidebar-width
    const SIDEBAR_COLLAPSED = 70;    // px, harus sama dengan --sidebar-collapsed-w
    const LS_KEY            = 'sidebar_collapsed';
    const MOBILE_BP         = 768;   // px

    // ── Elemen ──────────────────────────────────────────────────────────────
    const sidebar        = document.getElementById('sidebar');
    const overlay        = document.getElementById('sidebar-overlay');
    const mobileToggle   = document.getElementById('mobile-toggle');
    const desktopToggle  = document.getElementById('sidebar-toggle');

    if (!sidebar) return; // guard: jika sidebar tidak ada di halaman ini

    // ── Helpers ─────────────────────────────────────────────────────────────

    /** Set CSS variable --sidebar-current-width di :root */
    function setSidebarWidth(px) {
        document.documentElement.style.setProperty(
            '--sidebar-current-width', px + 'px'
        );
    }

    function isMobile() {
        return window.innerWidth <= MOBILE_BP;
    }

    // ── State awal ───────────────────────────────────────────────────────────
    let isCollapsed = localStorage.getItem(LS_KEY) === 'true';

    /** Terapkan state collapsed/expanded (desktop) tanpa animasi opsional */
    function applyDesktopState(animate) {
        if (!animate) {
            // Matikan transisi sementara agar tidak flicker saat load
            sidebar.style.transition = 'none';
            document.documentElement.style.setProperty('--transition-sidebar', '0s');
        }

        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            setSidebarWidth(SIDEBAR_COLLAPSED);
        } else {
            sidebar.classList.remove('collapsed');
            setSidebarWidth(SIDEBAR_FULL);
        }

        if (!animate) {
            // Re-enable transisi setelah satu frame
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    sidebar.style.transition = '';
                    document.documentElement.style.setProperty(
                        '--transition-sidebar', '0.3s cubic-bezier(0.4, 0, 0.2, 1)'
                    );
                });
            });
        }
    }

    /** Toggle collapsed (desktop) */
    function toggleDesktop() {
        isCollapsed = !isCollapsed;
        localStorage.setItem(LS_KEY, isCollapsed);
        applyDesktopState(true);
    }

    // ── Mobile: open/close ───────────────────────────────────────────────────
    function openMobile() {
        sidebar.classList.add('mobile-open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // cegah scroll body
    }

    function closeMobile() {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    function toggleMobile() {
        sidebar.classList.contains('mobile-open') ? closeMobile() : openMobile();
    }

    // ── Event listeners ──────────────────────────────────────────────────────

    // Desktop toggle button (di dalam sidebar)
    if (desktopToggle) {
        desktopToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!isMobile()) toggleDesktop();
        });
    }

    // Mobile hamburger button (di header)
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function () {
            if (isMobile()) toggleMobile();
        });
    }

    // Klik overlay → tutup mobile sidebar
    if (overlay) {
        overlay.addEventListener('click', closeMobile);
    }

    // Klik nav item di mobile → tutup sidebar otomatis
    sidebar.querySelectorAll('.nav-item').forEach(function (item) {
        item.addEventListener('click', function () {
            if (isMobile()) closeMobile();
        });
    });

    // Resize: sinkronisasi ulang state
    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (!isMobile()) {
                // Kembali ke desktop: tutup mobile state, terapkan collapsed state
                closeMobile();
                applyDesktopState(false);
            } else {
                // Mobile: width content harus 0 offset (sudah dihandle CSS)
                setSidebarWidth(0); // tidak relevan di mobile (CSS override ke margin-left:0)
            }
        }, 100);
    });

    // ── Init ─────────────────────────────────────────────────────────────────
    if (isMobile()) {
        // Mobile: jangan terapkan collapsed state, biarkan sidebar tersembunyi
        setSidebarWidth(0);
    } else {
        applyDesktopState(false); // tanpa animasi saat load
    }

})();
</script>

<!-- User Dropdown Toggle -->
<script>
(function () {
    const userBtn      = document.getElementById('user-btn');
    const userDropdown = document.getElementById('user-dropdown');

    if (!userBtn || !userDropdown) return;

    userBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = userDropdown.classList.toggle('open');
        userBtn.classList.toggle('open', isOpen);
    });

    document.addEventListener('click', function (e) {
        if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.remove('open');
            userBtn.classList.remove('open');
        }
    });
})();
</script>

<!-- Update tanggal di header -->
<script>
(function () {
    const el = document.getElementById('current-date');
    if (!el) return;

    const now  = new Date();
    const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    const mons = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    el.textContent = days[now.getDay()] + ', '
        + now.getDate() + ' '
        + mons[now.getMonth()] + ' '
        + now.getFullYear();
})();
</script>