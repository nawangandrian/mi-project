<!-- ===== SCRIPTS ===== -->
<!-- Page-specific scripts slot -->
<?= $scripts ?? '' ?>

<script>
    /* ===== GLOBAL APP JS ===== */
    (function() {
        'use strict';

        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const mainHeader = document.getElementById('main-header');
        const mainFooter = document.getElementById('main-footer');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const mobileToggle = document.getElementById('mobile-toggle');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        // ─── SIDEBAR COLLAPSE (desktop) ───────────────────────────────────────
        const SIDEBAR_KEY = 'mg_sidebar_collapsed';
        const saved = localStorage.getItem(SIDEBAR_KEY);
        let isCollapsed = saved === null ? false : saved === 'true';

        function applySidebarState(animate) {
            if (!animate) {
                sidebar.style.transition = 'none';
                mainContent.style.transition = 'none';
                mainHeader.style.transition = 'none';
                mainFooter.style.transition = 'none';
            }

            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
                mainHeader.classList.add('sidebar-collapsed');
                mainFooter.classList.add('sidebar-collapsed');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('sidebar-collapsed');
                mainHeader.classList.remove('sidebar-collapsed');
                mainFooter.classList.remove('sidebar-collapsed');
            }

            if (!animate) {
                setTimeout(() => {
                    sidebar.style.transition = '';
                    mainContent.style.transition = '';
                    mainHeader.style.transition = '';
                    mainFooter.style.transition = '';
                }, 50);
            }
        }

        // Apply without animation on load
        applySidebarState(false);

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                isCollapsed = !isCollapsed;
                localStorage.setItem(SIDEBAR_KEY, isCollapsed);
                applySidebarState(true);
            });
        }

        // ─── SIDEBAR MOBILE ───────────────────────────────────────────────────
        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                sidebar.classList.toggle('mobile-open');
                sidebarOverlay.classList.toggle('active');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
            });
        }

        // ─── NOTIFICATION DROPDOWN ────────────────────────────────────────────
        const notifBtn = document.getElementById('notif-btn');
        const notifDropdown = document.getElementById('notif-dropdown');
        const userBtn = document.getElementById('user-btn');
        const userDropdown = document.getElementById('user-dropdown');

        function closeAll() {
            notifDropdown?.classList.remove('open');
            userDropdown?.classList.remove('open');
            userBtn?.classList.remove('open');
        }

        if (notifBtn && notifDropdown) {
            notifBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = notifDropdown.classList.contains('open');
                closeAll();
                if (!isOpen) notifDropdown.classList.add('open');
            });
        }

        if (userBtn && userDropdown) {
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = userDropdown.classList.contains('open');
                closeAll();
                if (!isOpen) {
                    userDropdown.classList.add('open');
                    userBtn.classList.add('open');
                }
            });
        }

        document.addEventListener('click', closeAll);

        // ─── CURRENT DATE ─────────────────────────────────────────────────────
        const dateEl = document.getElementById('current-date');
        if (dateEl) {
            const now = new Date();
            dateEl.textContent = now.toLocaleDateString('id-ID', {
                weekday: 'short',
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }

        // ─── ACTIVE NAV ───────────────────────────────────────────────────────
        const navItems = document.querySelectorAll('.nav-item');
        const currentPath = window.location.pathname;

        navItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && currentPath.includes(href.replace(window.location.origin, ''))) {
                item.classList.add('active');
            }
        });

        // ─── LOADING BAR ──────────────────────────────────────────────────────
        function showLoader() {
            let bar = document.getElementById('page-loader');
            if (!bar) {
                bar = document.createElement('div');
                bar.id = 'page-loader';
                bar.style.cssText = `
                position:fixed;top:0;left:0;height:2px;width:0;
                background:linear-gradient(90deg,var(--accent-glow),var(--accent-cyan));
                z-index:9999;transition:width 0.3s ease;border-radius:0 2px 2px 0;
                box-shadow:0 0 10px rgba(0,212,255,0.6);
            `;
                document.body.appendChild(bar);
            }
            bar.style.width = '70%';
        }

        function hideLoader() {
            const bar = document.getElementById('page-loader');
            if (bar) {
                bar.style.width = '100%';
                setTimeout(() => bar.remove(), 400);
            }
        }

        // Attach to all nav links
        document.querySelectorAll('a[href]:not([href^="#"]):not([href^="javascript"])').forEach(a => {
            a.addEventListener('click', () => showLoader());
        });

        window.addEventListener('load', hideLoader);

        // ─── TOAST UTILITY ────────────────────────────────────────────────────
        window.showToast = function(message, type = 'info', duration = 3500) {
            const colors = {
                success: ['var(--accent-green)', 'rgba(34,211,165,0.1)', 'bi-check-circle-fill'],
                error: ['var(--accent-red)', 'rgba(239,68,68,0.1)', 'bi-x-circle-fill'],
                warning: ['var(--accent-yellow)', 'rgba(251,191,36,0.1)', 'bi-exclamation-triangle-fill'],
                info: ['var(--accent-cyan)', 'rgba(0,212,255,0.1)', 'bi-info-circle-fill'],
            };
            const [color, bg, icon] = colors[type] || colors.info;

            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.style.cssText = `
            display:flex;align-items:center;gap:12px;
            padding:14px 18px;min-width:280px;max-width:380px;
            background:var(--bg-card);border:1px solid ${color}33;
            border-radius:var(--radius-md);
            box-shadow:0 8px 30px rgba(0,0,0,0.4);
            animation:slideInToast 0.3s ease;
            font-size:13px;color:var(--text-primary);
        `;
            toast.innerHTML = `
            <div style="width:34px;height:34px;border-radius:8px;background:${bg};display:flex;align-items:center;justify-content:center;color:${color};flex-shrink:0;font-size:17px;">
                <i class="bi ${icon}"></i>
            </div>
            <span style="flex:1">${message}</span>
            <button onclick="this.closest('div').remove()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:16px;padding:0;">
                <i class="bi bi-x"></i>
            </button>
        `;

            const style = document.createElement('style');
            style.textContent = `
            @keyframes slideInToast {
                from { opacity:0; transform:translateX(20px); }
                to   { opacity:1; transform:translateX(0); }
            }
        `;
            document.head.appendChild(style);

            container.appendChild(toast);
            setTimeout(() => toast.remove(), duration);
        };

        // ─── PHP SESSION FLASH MESSAGES ───────────────────────────────────────
        <?php if (session()->getFlashdata('success')): ?>
            window.addEventListener('load', () => showToast('<?= session()->getFlashdata('success') ?>', 'success'));
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            window.addEventListener('load', () => showToast('<?= session()->getFlashdata('error') ?>', 'error'));
        <?php endif; ?>
        <?php if (session()->getFlashdata('warning')): ?>
            window.addEventListener('load', () => showToast('<?= session()->getFlashdata('warning') ?>', 'warning'));
        <?php endif; ?>
        <?php if (session()->getFlashdata('info')): ?>
            window.addEventListener('load', () => showToast('<?= session()->getFlashdata('info') ?>', 'info'));
        <?php endif; ?>

    })();
</script>
</body>

</html>