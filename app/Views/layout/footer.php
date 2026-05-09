<?php
/**
 * layout/footer.php
 * Menutup #main-content dan #content-wrapper, lalu render footer.
 * Juga menyertakan page-loader overlay yang muncul saat reload/navigate.
 */
?>
        </div><!-- end content-wrapper -->
    </div><!-- end main-content -->

    <!-- ===== FOOTER ===== -->
    <footer id="main-footer">
        <div class="footer-inner">
            <div class="footer-left">
                <span class="footer-logo-icon"><i class="bi bi-bar-chart-line-fill"></i></span>
                <span>Mi Store Kudus &copy; <?= date('Y') ?></span>
                <span class="footer-sep">·</span>
                <span class="footer-desc">Sistem Prediksi Penjualan Smartphone</span>
            </div>
            <div class="footer-right">
                <span class="footer-tech">Random Forest Regression</span>
                <span class="footer-sep">·</span>
                <a href="<?= base_url('about') ?>">Tentang Sistem</a>
            </div>
        </div>
    </footer>

</div><!-- end app-wrapper -->

<!-- ===== PAGE LOADER OVERLAY ===== -->
<!--
    Muncul langsung (inline style display:flex) saat halaman mulai load.
    Disembunyikan via JS saat window 'load' event selesai.
    Tidak butuh JS untuk tampil — hanya butuh JS untuk hilang.
-->
<div id="page-loader-overlay">
    <div class="plo-card">
        <!-- Logo / brand -->
        <div class="plo-logo">
            <i class="bi bi-bar-chart-line-fill"></i>
        </div>
        <!-- Spinner -->
        <div class="plo-spinner">
            <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                <circle class="plo-track"  cx="25" cy="25" r="20" fill="none" stroke-width="3"/>
                <circle class="plo-arc"    cx="25" cy="25" r="20" fill="none" stroke-width="3"
                        stroke-linecap="round" stroke-dasharray="80 200" stroke-dashoffset="0"/>
            </svg>
        </div>
        <!-- Label -->
        <div class="plo-label">Memuat halaman<span class="plo-dots"></span></div>
    </div>
</div>

<style>
/* ===== FOOTER ===== */
#main-footer {
    margin-left: var(--sidebar-current-width, var(--sidebar-width, 240px));
    background: var(--sidebar-bg);
    border-top: 1px solid var(--sidebar-border);
    height: 52px;
    display: flex;
    align-items: center;
    transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
}

#main-footer.sidebar-collapsed {
    margin-left: 70px;
}

.footer-inner {
    width: 100%;
    padding: 0 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    color: var(--text-on-dark-2);
}

.footer-left,
.footer-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: nowrap;
}

.footer-sep  { color: var(--text-on-dark-3); }

.footer-logo-icon {
    color: var(--accent-cyan);
    font-size: 15px;
    line-height: 1;
}

.footer-tech {
    background: rgba(0, 151, 184, 0.12);
    color: var(--accent-cyan);
    padding: 2px 10px;
    border-radius: 20px;
    border: 1px solid rgba(0, 151, 184, 0.2);
    font-size: 11px;
    white-space: nowrap;
}

.footer-right a {
    color: var(--text-on-dark-2);
    text-decoration: none;
    transition: var(--transition);
}
.footer-right a:hover { color: var(--accent-cyan); }

@media (max-width: 900px) {
    .footer-desc  { display: none; }
    .footer-right { display: none; }
}

@media (max-width: 768px) {
    #main-footer,
    #main-footer.sidebar-collapsed {
        margin-left: 0 !important;
    }
    .footer-inner {
        padding: 0 16px;
        justify-content: center;
    }
    .footer-left   { gap: 6px; font-size: 11px; }
    .footer-sep,
    .footer-desc   { display: none; }
}

/* ===== PAGE LOADER OVERLAY ===== */
#page-loader-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    background: var(--sidebar-bg, #0d1b2a);          /* warna bg app */
    display: flex;
    align-items: center;
    justify-content: center;
    /* Fade-out transition saat dihilangkan */
    transition: opacity 0.4s ease, visibility 0.4s ease;
    opacity: 1;
    visibility: visible;
}

/* State tersembunyi — ditambahkan JS saat halaman siap */
#page-loader-overlay.plo-hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

/* Card tengah */
.plo-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    animation: plo-enter 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes plo-enter {
    from { opacity: 0; transform: scale(0.85) translateY(12px); }
    to   { opacity: 1; transform: scale(1)    translateY(0);    }
}

/* Logo icon */
.plo-logo {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg,
        var(--mg-glow,  #1a5276),
        var(--accent-cyan, #0097b8)
    );
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #fff;
    box-shadow: 0 0 28px rgba(0, 151, 184, 0.45);
    animation: plo-pulse 2s ease-in-out infinite;
}

@keyframes plo-pulse {
    0%, 100% { box-shadow: 0 0 20px rgba(0, 151, 184, 0.4); }
    50%       { box-shadow: 0 0 36px rgba(0, 151, 184, 0.7); }
}

/* SVG Spinner */
.plo-spinner {
    width: 52px;
    height: 52px;
}

.plo-spinner svg {
    width: 100%;
    height: 100%;
    animation: plo-rotate 1.4s linear infinite;
}

@keyframes plo-rotate {
    to { transform: rotate(360deg); }
}

.plo-track {
    stroke: rgba(255, 255, 255, 0.07);
}

.plo-arc {
    stroke: var(--accent-cyan, #0097b8);
    animation: plo-dash 1.4s ease-in-out infinite;
    transform-origin: center;
}

@keyframes plo-dash {
    0%   { stroke-dasharray: 1   200; stroke-dashoffset: 0;   }
    50%  { stroke-dasharray: 100 200; stroke-dashoffset: -30; }
    100% { stroke-dasharray: 100 200; stroke-dashoffset: -125;}
}

/* Label teks */
.plo-label {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-on-dark-3, #6b8aa8);
    letter-spacing: 0.03em;
}

/* Animasi titik-titik ... */
.plo-dots::after {
    content: '';
    animation: plo-dot-cycle 1.5s steps(4, end) infinite;
}

@keyframes plo-dot-cycle {
    0%  { content: '';    }
    25% { content: '.';   }
    50% { content: '..';  }
    75% { content: '...'; }
}
</style>

<script>
/* ===== PAGE LOADER — sembunyikan saat halaman siap ===== */
(function () {
    const overlay = document.getElementById('page-loader-overlay');
    if (!overlay) return;

    function hideOverlay() {
        overlay.classList.add('plo-hidden');
        // Hapus dari DOM setelah animasi selesai (400ms)
        setTimeout(function () {
            if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }, 450);
    }

    // window 'load' = semua resource (gambar, CSS, font) selesai
    if (document.readyState === 'complete') {
        // Halaman sudah siap (misal cache) — tunggu 1 frame agar render selesai
        requestAnimationFrame(function () {
            requestAnimationFrame(hideOverlay);
        });
    } else {
        window.addEventListener('load', function () {
            // Tambah 80ms buffer agar konten tidak berkedip sebelum loader hilang
            setTimeout(hideOverlay, 80);
        });
    }

    // Tampilkan loader lagi saat user klik link navigasi
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a[href]');
        if (!link) return;

        const href = link.getAttribute('href');
        // Skip: anchor, javascript:, target blank, link kosong
        if (!href
            || href.startsWith('#')
            || href.startsWith('javascript')
            || link.target === '_blank'
            || e.ctrlKey || e.metaKey || e.shiftKey
        ) return;

        // Tampilkan overlay lagi
        const fresh = document.createElement('div');
        fresh.id = 'page-loader-overlay';
        fresh.innerHTML = overlay.innerHTML || document.getElementById('page-loader-overlay')?.innerHTML || '';
        // Buat ulang overlay ringan tanpa animasi masuk
        fresh.style.cssText = [
            'position:fixed;inset:0;z-index:99999;',
            'background:var(--sidebar-bg,#0d1b2a);',
            'display:flex;align-items:center;justify-content:center;',
            'opacity:0;transition:opacity 0.2s ease;'
        ].join('');
        document.body.appendChild(fresh);
        requestAnimationFrame(function () {
            fresh.style.opacity = '1';
        });
    }, true); // capture phase agar lebih cepat

})();
</script>