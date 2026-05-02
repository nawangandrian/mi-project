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

<style>
/* ===== FOOTER ===== */
#main-footer {
    margin-left: var(--sidebar-width);
    background: var(--sidebar-bg);          /* ✅ fix: ganti --bg-sidebar → --sidebar-bg */
    border-top: 1px solid var(--sidebar-border); /* ✅ fix: ganti --border-subtle → --sidebar-border */
    height: 52px;
    display: flex;
    align-items: center;
    transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
}

/* Sinkronkan dengan sidebar collapsed (desktop) */
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
    color: var(--text-on-dark-2);           /* ✅ fix: ganti --text-muted → --text-on-dark-2 (karena bg gelap) */
}

.footer-left,
.footer-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: nowrap;
}

.footer-sep {
    color: var(--text-on-dark-3);           /* ✅ fix: pakai variabel yang ada */
}

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

.footer-right a:hover {
    color: var(--accent-cyan);
}

/* ===== RESPONSIVE ===== */

/* Tablet: sembunyikan deskripsi tengah */
@media (max-width: 900px) {
    .footer-desc {
        display: none;
    }
    .footer-right {
        display: none;
    }
}

/* Mobile: footer full-width, hapus margin sidebar */
@media (max-width: 768px) {
    #main-footer,
    #main-footer.sidebar-collapsed {
        margin-left: 0 !important;          /* ✅ fix: reset margin di mobile */
    }

    .footer-inner {
        padding: 0 16px;
        justify-content: center;           /* center konten di mobile */
    }

    .footer-left {
        gap: 6px;
        font-size: 11px;
    }

    .footer-sep,
    .footer-desc {
        display: none;
    }
}
</style>