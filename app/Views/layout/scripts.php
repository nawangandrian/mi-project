<!-- ===== SCRIPTS ===== -->
<!-- Page-specific scripts slot -->
<?= $scripts ?? '' ?>

<script>
    /* ===== GLOBAL APP JS ===== */
    (function() {
        'use strict';

        const sidebar        = document.getElementById('sidebar');
        const mainContent    = document.getElementById('main-content');
        const mainHeader     = document.getElementById('main-header');
        const mainFooter     = document.getElementById('main-footer');
        const sidebarToggle  = document.getElementById('sidebar-toggle');
        const mobileToggle   = document.getElementById('mobile-toggle');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        // ─── KONSTANTA ────────────────────────────────────────────────────────
        const SIDEBAR_FULL      = 240;
        const SIDEBAR_COLLAPSED = 70;
        const SIDEBAR_KEY       = 'mg_sidebar_collapsed';
        const MOBILE_BP         = 768;

        function isMobile() { return window.innerWidth <= MOBILE_BP; }

        // ─── CSS VARIABLE HELPER ──────────────────────────────────────────────
        function setSidebarWidth(px) {
            document.documentElement.style.setProperty('--sidebar-current-width', px + 'px');
        }

        // ─── SIDEBAR COLLAPSE (desktop) ───────────────────────────────────────
        const saved     = localStorage.getItem(SIDEBAR_KEY);
        let isCollapsed = saved === null ? false : saved === 'true';

        function applyDesktopState(animate) {
            if (!animate) {
                // Matikan transisi sesaat agar tidak flicker saat load
                [sidebar, mainContent, mainHeader, mainFooter].forEach(function(el) {
                    if (el) el.style.transition = 'none';
                });
            }

            if (isCollapsed) {
                sidebar?.classList.add('collapsed');
                mainContent?.classList.add('sidebar-collapsed');
                mainHeader?.classList.add('sidebar-collapsed');
                mainFooter?.classList.add('sidebar-collapsed');
                setSidebarWidth(SIDEBAR_COLLAPSED);
            } else {
                sidebar?.classList.remove('collapsed');
                mainContent?.classList.remove('sidebar-collapsed');
                mainHeader?.classList.remove('sidebar-collapsed');
                mainFooter?.classList.remove('sidebar-collapsed');
                setSidebarWidth(SIDEBAR_FULL);
            }

            if (!animate) {
                // Re-enable transisi setelah 2 frame
                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        [sidebar, mainContent, mainHeader, mainFooter].forEach(function(el) {
                            if (el) el.style.transition = '';
                        });
                    });
                });
            }
        }

        // Terapkan state saat load (tanpa animasi)
        if (!isMobile()) {
            applyDesktopState(false);
        } else {
            setSidebarWidth(0);
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                if (isMobile()) return;
                isCollapsed = !isCollapsed;
                localStorage.setItem(SIDEBAR_KEY, isCollapsed);
                applyDesktopState(true);
            });
        }

        // ─── SIDEBAR MOBILE ───────────────────────────────────────────────────
        function openMobile() {
            sidebar?.classList.add('mobile-open');
            sidebarOverlay?.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeMobile() {
            sidebar?.classList.remove('mobile-open');
            sidebarOverlay?.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (mobileToggle) {
            mobileToggle.addEventListener('click', function() {
                if (!isMobile()) return;
                sidebar?.classList.contains('mobile-open') ? closeMobile() : openMobile();
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeMobile);
        }

        sidebar?.querySelectorAll('.nav-item').forEach(function(item) {
            item.addEventListener('click', function() {
                if (isMobile()) closeMobile();
            });
        });

        // Sync ulang saat resize
        var resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (!isMobile()) {
                    closeMobile();
                    applyDesktopState(false);
                } else {
                    setSidebarWidth(0);
                }
            }, 100);
        });

        // ─── DROPDOWN HEADER ─────────────────────────────────────────────────
        const notifBtn      = document.getElementById('notif-btn');
        const notifDropdown = document.getElementById('notif-dropdown');
        const userBtn       = document.getElementById('user-btn');
        const userDropdown  = document.getElementById('user-dropdown');

        function closeAllDropdowns() {
            notifDropdown?.classList.remove('open');
            userDropdown?.classList.remove('open');
            userBtn?.classList.remove('open');
        }

        if (notifBtn && notifDropdown) {
            notifBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = notifDropdown.classList.contains('open');
                closeAllDropdowns();
                if (!isOpen) notifDropdown.classList.add('open');
            });
        }

        if (userBtn && userDropdown) {
            userBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = userDropdown.classList.contains('open');
                closeAllDropdowns();
                if (!isOpen) {
                    userDropdown.classList.add('open');
                    userBtn.classList.add('open');
                }
            });
        }

        document.addEventListener('click', closeAllDropdowns);

        // ─── CURRENT DATE ─────────────────────────────────────────────────────
        const dateEl = document.getElementById('current-date');
        if (dateEl) {
            const now  = new Date();
            const days = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
            const mons = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            dateEl.textContent = days[now.getDay()] + ', '
                + now.getDate() + ' '
                + mons[now.getMonth()] + ' '
                + now.getFullYear();
        }

        // ─── LOADING BAR ──────────────────────────────────────────────────────
        function showLoader() {
            let bar = document.getElementById('page-loader');
            if (!bar) {
                bar = document.createElement('div');
                bar.id = 'page-loader';
                bar.style.cssText = [
                    'position:fixed;top:0;left:0;height:2px;width:0;',
                    'background:linear-gradient(90deg,var(--accent-glow,#0097b8),var(--accent-cyan,#00d4ff));',
                    'z-index:9999;transition:width 0.3s ease;border-radius:0 2px 2px 0;',
                    'box-shadow:0 0 10px rgba(0,212,255,0.6);'
                ].join('');
                document.body.appendChild(bar);
            }
            bar.style.width = '70%';
        }

        function hideLoader() {
            const bar = document.getElementById('page-loader');
            if (bar) { bar.style.width = '100%'; setTimeout(function() { bar.remove(); }, 400); }
        }

        document.querySelectorAll('a[href]:not([href^="#"]):not([href^="javascript"])').forEach(function(a) {
            a.addEventListener('click', showLoader);
        });
        window.addEventListener('load', hideLoader);

        // ─── TOAST UTILITY ────────────────────────────────────────────────────
        window.showToast = function(message, type, duration) {
            type     = type     || 'info';
            duration = duration || 3500;

            const cfg = {
                success : ['var(--accent-green,#22d3a5)',  'rgba(34,211,165,0.1)',  'bi-check-circle-fill'],
                error   : ['var(--accent-red,#ef4444)',    'rgba(239,68,68,0.1)',   'bi-x-circle-fill'],
                warning : ['var(--accent-yellow,#fbbf24)', 'rgba(251,191,36,0.1)', 'bi-exclamation-triangle-fill'],
                info    : ['var(--accent-cyan,#00d4ff)',   'rgba(0,212,255,0.1)',   'bi-info-circle-fill'],
            };
            const [color, bg, icon] = cfg[type] || cfg.info;

            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.style.cssText = [
                'display:flex;align-items:center;gap:12px;',
                'padding:14px 18px;min-width:280px;max-width:380px;',
                'background:var(--bg-card,#1a2535);border:1px solid ' + color + '33;',
                'border-radius:var(--radius-md,10px);',
                'box-shadow:0 8px 30px rgba(0,0,0,0.4);',
                'animation:slideInToast 0.3s ease;',
                'font-size:13px;color:var(--text-primary,#e8f1f8);'
            ].join('');
            toast.innerHTML =
                '<div style="width:34px;height:34px;border-radius:8px;background:' + bg
                + ';display:flex;align-items:center;justify-content:center;color:' + color
                + ';flex-shrink:0;font-size:17px;"><i class="bi ' + icon + '"></i></div>'
                + '<span style="flex:1">' + message + '</span>'
                + '<button onclick="this.closest(\'div\').remove()" style="background:none;border:none;'
                + 'color:var(--text-muted,#8899aa);cursor:pointer;font-size:16px;padding:0;">'
                + '<i class="bi bi-x"></i></button>';

            if (!document.getElementById('toast-anim-style')) {
                const s = document.createElement('style');
                s.id = 'toast-anim-style';
                s.textContent = '@keyframes slideInToast{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}';
                document.head.appendChild(s);
            }

            container.appendChild(toast);
            setTimeout(function() { toast.remove(); }, duration);
        };

        // ─── PHP SESSION FLASH MESSAGES ───────────────────────────────────────
        <?php if (session()->getFlashdata('success')): ?>
            window.addEventListener('load', function() { showToast('<?= addslashes(session()->getFlashdata('success')) ?>', 'success'); });
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            window.addEventListener('load', function() { showToast('<?= addslashes(session()->getFlashdata('error')) ?>', 'error'); });
        <?php endif; ?>
        <?php if (session()->getFlashdata('warning')): ?>
            window.addEventListener('load', function() { showToast('<?= addslashes(session()->getFlashdata('warning')) ?>', 'warning'); });
        <?php endif; ?>
        <?php if (session()->getFlashdata('info')): ?>
            window.addEventListener('load', function() { showToast('<?= addslashes(session()->getFlashdata('info')) ?>', 'info'); });
        <?php endif; ?>

    })();
</script>
</body>
</html>