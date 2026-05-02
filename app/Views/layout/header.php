<!-- ===== HEADER ===== -->
<header id="main-header">

    <!-- Left: Mobile toggle + Page title -->
    <div class="header-left">
        <button id="mobile-toggle" class="header-icon-btn" title="Buka Menu" aria-label="Buka Menu">
            <i class="bi bi-list"></i>
        </button>
        <div class="header-page-info">
            <span class="header-page-title"><?= $page_title ?? $title ?? 'Dashboard' ?></span>
            <span class="header-page-breadcrumb">
                <?php if (isset($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $i => $bc): ?>
                        <?php if ($i < count($breadcrumbs) - 1): ?>
                            <a href="<?= base_url($bc['url']) ?>"><?= $bc['label'] ?></a>
                            <i class="bi bi-chevron-right"></i>
                        <?php else: ?>
                            <span><?= $bc['label'] ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <a href="<?= base_url('dashboard') ?>">Home</a>
                    <i class="bi bi-chevron-right"></i>
                    <span><?= $page_title ?? $title ?? 'Dashboard' ?></span>
                <?php endif; ?>
            </span>
        </div>
    </div>

    <!-- Right: Actions -->
    <div class="header-right">

        <!-- Date Info -->
        <div class="header-date-info">
            <i class="bi bi-calendar3"></i>
            <span id="current-date">--</span>
        </div>

        <!-- Quick Predict Button -->
        <a href="<?= base_url('prediksi/jalankan') ?>" class="header-predict-btn">
            <i class="bi bi-lightning-charge-fill"></i>
            <span class="predict-label">Prediksi</span>
        </a>

        <!-- Notifications -->
        <div class="header-notif-wrapper">
            <button class="header-icon-btn" id="notif-btn" title="Notifikasi" aria-label="Notifikasi">
                <i class="bi bi-bell-fill"></i>
                <?php if (($notif_count ?? 0) > 0): ?>
                <span class="notif-dot"><?= $notif_count ?></span>
                <?php endif; ?>
            </button>
            <div class="notif-dropdown" id="notif-dropdown" role="menu">
                <div class="notif-header">
                    <span>Notifikasi</span>
                    <a href="#">Tandai semua dibaca</a>
                </div>
                <div class="notif-list">
                    <div class="notif-item unread">
                        <div class="notif-icon" style="background:rgba(0,151,184,0.1);color:var(--accent-cyan)">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div>
                            <div class="notif-text">Model prediksi selesai dilatih</div>
                            <div class="notif-time">2 menit yang lalu</div>
                        </div>
                    </div>
                    <div class="notif-item">
                        <div class="notif-icon" style="background:rgba(16,183,127,0.1);color:var(--accent-green)">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <div class="notif-text">Data penjualan bulan ini berhasil diimport</div>
                            <div class="notif-time">1 jam yang lalu</div>
                        </div>
                    </div>
                    <div class="notif-item">
                        <div class="notif-icon" style="background:rgba(212,160,23,0.1);color:var(--accent-yellow)">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div>
                            <div class="notif-text">Akurasi model turun 2% dari bulan lalu</div>
                            <div class="notif-time">3 jam yang lalu</div>
                        </div>
                    </div>
                </div>
                <div class="notif-footer">
                    <a href="<?= base_url('notifikasi') ?>">Lihat semua notifikasi</a>
                </div>
            </div>
        </div>

        <!-- User Menu -->
        <div class="header-user-wrapper">
            <button class="header-user-btn" id="user-btn" aria-label="Menu pengguna">
                <div class="user-avatar">
                    <?= strtoupper(substr(session()->get('username') ?? 'A', 0, 1)) ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?= esc(session()->get('nama') ?? 'Administrator') ?></span>
                    <span class="user-role"><?= esc(session()->get('role') ?? 'Admin') ?></span>
                </div>
                <i class="bi bi-chevron-down user-chevron"></i>
            </button>
            <div class="user-dropdown" id="user-dropdown" role="menu">
                <div class="user-dropdown-header">
                    <div class="user-avatar-lg">
                        <?= strtoupper(substr(session()->get('username') ?? 'A', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="ud-name"><?= esc(session()->get('nama') ?? 'Administrator') ?></div>
                        <div class="ud-email"><?= esc(session()->get('email') ?? 'admin@mistore.id') ?></div>
                    </div>
                </div>
                <div class="user-dropdown-menu">
                    <a href="<?= base_url('profil') ?>" class="user-menu-item">
                        <i class="bi bi-person-fill"></i> Profil Saya
                    </a>
                    <a href="<?= base_url('pengaturan') ?>" class="user-menu-item">
                        <i class="bi bi-gear-fill"></i> Pengaturan
                    </a>
                    <div class="user-menu-divider"></div>
                    <a href="<?= base_url('auth/logout') ?>" class="user-menu-item item-danger">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </a>
                </div>
            </div>
        </div>

    </div>
</header>

<style>
/* ===== HEADER ===== */
#main-header {
    position: fixed;
    top: 0;
    left: var(--sidebar-width);
    right: 0;
    height: var(--header-height);
    background: var(--header-bg);
    backdrop-filter: blur(18px) saturate(1.6);
    -webkit-backdrop-filter: blur(18px) saturate(1.6);
    border-bottom: 1px solid var(--sidebar-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 26px;
    z-index: 900;
    transition: left 0.3s cubic-bezier(0.4,0,0.2,1);
    box-shadow: 0 1px 20px rgba(0,0,0,0.25);
}

#main-header.sidebar-collapsed { left: 70px; }

/* ── Left ── */
.header-left { display: flex; align-items: center; gap: 14px; }

/* Mobile toggle: always visible, but hidden visually on desktop */
#mobile-toggle {
    display: none; /* hidden on desktop */
}

@media (max-width: 768px) {
    #mobile-toggle { display: flex; }
    #main-header { left: 0 !important; padding: 0 14px; }
}

.header-page-info { display: flex; flex-direction: column; }

.header-page-title {
    font-family: var(--font-display);
    font-size: 15px; font-weight: 700;
    color: var(--text-on-dark);
    line-height: 1.2;
}

.header-page-breadcrumb {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; color: var(--text-on-dark-3);
    margin-top: 1px;
}

.header-page-breadcrumb a {
    color: var(--text-on-dark-3); text-decoration: none;
    transition: var(--transition);
}
.header-page-breadcrumb a:hover { color: var(--accent-cyan); }
.header-page-breadcrumb span { color: var(--accent-cyan); }
.header-page-breadcrumb .bi { font-size: 9px; }

/* ── Right ── */
.header-right { display: flex; align-items: center; gap: 8px; }

/* Date */
.header-date-info {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; color: var(--text-on-dark-3);
    padding: 6px 12px;
    background: rgba(36,59,85,0.35);
    border-radius: var(--radius-sm);
    border: 1px solid var(--sidebar-border);
}

/* Predict Btn */
.header-predict-btn {
    display: flex; align-items: center; gap: 7px;
    padding: 7px 15px;
    background: linear-gradient(135deg, var(--mg-glow), var(--accent-cyan));
    color: #fff; border-radius: var(--radius-sm);
    text-decoration: none; font-size: 13px; font-weight: 500;
    transition: var(--transition);
    box-shadow: 0 2px 12px rgba(0,151,184,0.25);
}

.header-predict-btn:hover {
    box-shadow: 0 4px 20px rgba(0,151,184,0.4);
    transform: translateY(-1px); color: #fff;
}

/* Icon button */
.header-icon-btn {
    width: 36px; height: 36px;
    background: rgba(36,59,85,0.35);
    border: 1px solid var(--sidebar-border);
    border-radius: var(--radius-sm);
    color: var(--text-on-dark-2);
    font-size: 16px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: var(--transition);
    position: relative;
}

.header-icon-btn:hover {
    color: var(--accent-cyan);
    border-color: rgba(0,151,184,0.35);
    background: rgba(0,151,184,0.07);
}

/* Notif Dot */
.notif-dot {
    position: absolute; top: -5px; right: -5px;
    width: 18px; height: 18px;
    background: var(--accent-red);
    border-radius: 50%;
    font-size: 9px; font-weight: 700; color: #fff;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid var(--mg-deep);
    animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
    0%,100% { box-shadow: 0 0 0 0 rgba(229,62,62,0.4); }
    50%      { box-shadow: 0 0 0 5px rgba(229,62,62,0); }
}

/* Dropdowns */
.header-notif-wrapper, .header-user-wrapper { position: relative; }

.notif-dropdown, .user-dropdown {
    position: absolute; top: calc(100% + 10px); right: 0;
    background: #ffffff;
    border: 1px solid var(--surface-border);
    border-radius: var(--radius-lg);
    box-shadow: 0 20px 50px rgba(20,30,48,0.18), 0 4px 12px rgba(20,30,48,0.08);
    min-width: 300px;
    display: none;
    z-index: 1100;
    overflow: hidden;
    animation: dropIn 0.2s ease;
}

.notif-dropdown.open, .user-dropdown.open { display: block; }

@keyframes dropIn {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}

.notif-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 15px 18px 12px;
    border-bottom: 1px solid var(--surface-border);
    font-size: 13px; font-weight: 700;
    color: var(--text-ink);
    font-family: var(--font-display);
}

.notif-header a { font-size: 11px; color: var(--accent-cyan); text-decoration: none; font-weight: 500; }

.notif-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 13px 18px;
    border-bottom: 1px solid var(--surface-border);
    transition: var(--transition); cursor: pointer;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: var(--surface-hover); }
.notif-item.unread { background: rgba(0,151,184,0.03); }

.notif-icon { width: 34px; height: 34px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
.notif-text { font-size: 13px; color: var(--text-body); margin-bottom: 2px; }
.notif-time { font-size: 11px; color: var(--text-muted); }

.notif-footer { padding: 11px 18px; text-align: center; border-top: 1px solid var(--surface-border); }
.notif-footer a { font-size: 12px; color: var(--accent-cyan); text-decoration: none; font-weight: 500; }

/* User Button */
.header-user-btn {
    display: flex; align-items: center; gap: 9px;
    padding: 5px 10px 5px 5px;
    background: rgba(36,59,85,0.35);
    border: 1px solid var(--sidebar-border);
    border-radius: var(--radius-md);
    cursor: pointer; transition: var(--transition);
}
.header-user-btn:hover { border-color: rgba(0,151,184,0.35); background: rgba(0,151,184,0.06); }

.user-avatar {
    width: 28px; height: 28px;
    background: linear-gradient(135deg, var(--mg-glow), var(--accent-cyan));
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-display); font-size: 13px; font-weight: 700; color: #fff;
    flex-shrink: 0;
}

.user-avatar-lg {
    width: 40px; height: 40px; border-radius: 11px; font-size: 17px;
    background: linear-gradient(135deg, var(--mg-glow), var(--accent-cyan));
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font-display); font-weight: 700; color: #fff; flex-shrink: 0;
}

.user-info { display: flex; flex-direction: column; }
.user-name  { font-size: 12.5px; font-weight: 600; color: var(--text-on-dark); display: block; line-height: 1.3; }
.user-role  { font-size: 10.5px; color: var(--text-on-dark-3); display: block; }
.user-chevron { font-size: 11px; color: var(--text-on-dark-3); transition: var(--transition); }
.header-user-btn.open .user-chevron { transform: rotate(180deg); }

/* User dropdown (light theme) */
.user-dropdown { min-width: 215px; }

.user-dropdown-header {
    display: flex; align-items: center; gap: 12px;
    padding: 16px 18px;
    border-bottom: 1px solid var(--surface-border);
    background: var(--card-bg-alt);
}

.ud-name  { font-weight: 700; font-size: 14px; color: var(--text-ink); font-family: var(--font-display); }
.ud-email { font-size: 11.5px; color: var(--text-muted); }

.user-dropdown-menu { padding: 7px; }

.user-menu-item {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 11px;
    border-radius: var(--radius-sm);
    color: var(--text-body); text-decoration: none;
    font-size: 13px; transition: var(--transition);
}
.user-menu-item:hover { background: var(--surface-hover); color: var(--text-ink); }
.user-menu-item.item-danger { color: var(--accent-red); }
.user-menu-item.item-danger:hover { background: rgba(229,62,62,0.07); }

.user-menu-divider { height: 1px; background: var(--surface-border); margin: 5px 0; }

/* ── Responsive ── */
@media (max-width: 900px) {
    .header-date-info { display: none; }
}

@media (max-width: 768px) {
    .predict-label { display: none; }
    .user-info, .user-chevron { display: none; }
    .header-predict-btn { padding: 7px 10px; }
}

/* Paksa semua modal overlay ke body level */
.mg-modal-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    transform: none !important;
    will-change: unset !important;
}
</style>