<?php

/**
 * View  : pages/training/riwayat_model.php
 * Modul : Riwayat Model Training — Mi Store Kudus
 */
?>

<div class="content-wrapper-inner">

    <!-- ── Page Header ── -->
    <div class="mg-page-header">
        <div class="page-header-left">
            <h1 class="page-title">Riwayat Model</h1>
            <p class="page-subtitle">Semua versi model Random Forest yang telah dilatih</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('prediksi/akurasi') ?>" class="btn-mg btn-outline-mg">
                <i class="bi bi-bullseye"></i> Evaluasi Model
            </a>
        </div>
    </div>

    <!-- ── Quick Nav Pills ── -->
    <div class="quick-nav-row">
        <a href="<?= base_url('training') ?>"
            class="quick-nav-pill">
            <i class="bi bi-database-fill"></i>
            <span>Dataset Training</span>
        </a>
        <a href="<?= base_url('training/proses') ?>"
            class="quick-nav-pill">
            <i class="bi bi-play-circle-fill"></i>
            <span>Jalankan Training</span>
            <span class="pill-badge ml-badge">ML</span>
        </a>
        <a href="<?= base_url('training/riwayat_model') ?>"
            class="quick-nav-pill active">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat Model</span>
        </a>
    </div>

    <!-- ── Stats Row ── -->
    <?php
    $ringkasan = $ringkasan ?? ['total_model' => 0, 'total_sukses' => 0, 'best_mae' => null, 'best_r2' => null];
    $modelAktif = $modelAktif ?? null;
    $riwayat   = $riwayat ?? [];
    ?>
    <div class="mg-stats-row">
        <div class="stat-card accent-blue">
            <div class="stat-icon" style="background:rgba(46,109,164,0.12);color:var(--mg-light)">
                <i class="bi bi-archive-fill"></i>
            </div>
            <div class="stat-value"><?= number_format($ringkasan['total_model'] ?? count($riwayat)) ?></div>
            <div class="stat-label">Total Model</div>
        </div>
        <div class="stat-card accent-green">
            <div class="stat-icon" style="background:rgba(16,183,127,0.12);color:var(--accent-green)">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="stat-value"><?= number_format($ringkasan['total_sukses'] ?? 0) ?></div>
            <div class="stat-label">Model Sukses</div>
        </div>
        <div class="stat-card accent-cyan">
            <div class="stat-icon" style="background:rgba(0,151,184,0.12);color:var(--accent-cyan)">
                <i class="bi bi-graph-down-arrow"></i>
            </div>
            <div class="stat-value">
                <?= isset($ringkasan['best_mae']) && $ringkasan['best_mae'] !== null
                    ? number_format((float)$ringkasan['best_mae'], 2)
                    : '—' ?>
            </div>
            <div class="stat-label">Best MAE</div>
        </div>
        <div class="stat-card accent-yellow">
            <div class="stat-icon" style="background:rgba(212,160,23,0.12);color:var(--accent-yellow)">
                <i class="bi bi-bullseye"></i>
            </div>
            <div class="stat-value">
                <?= isset($ringkasan['best_r2']) && $ringkasan['best_r2'] !== null
                    ? number_format((float)$ringkasan['best_r2'] * 100, 1) . '%'
                    : '—' ?>
            </div>
            <div class="stat-label">Best R²</div>
        </div>
    </div>

    <!-- ── Model Aktif Banner ── -->
    <?php if ($modelAktif): ?>
        <div class="aktif-banner">
            <div class="aktif-banner-left">
                <div class="aktif-dot"></div>
                <div class="aktif-info">
                    <div class="aktif-label">Model Aktif Saat Ini</div>
                    <div class="aktif-versi"><?= esc($modelAktif['versi'] ?? 'v1.0') ?></div>
                </div>
            </div>
            <div class="aktif-meta">
                <?php if (!empty($modelAktif['mae'])): ?>
                    <span class="aktif-metric">
                        <i class="bi bi-graph-down-arrow"></i>
                        MAE: <?= number_format((float)$modelAktif['mae'], 2) ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($modelAktif['r2'])): ?>
                    <span class="aktif-metric">
                        <i class="bi bi-bullseye"></i>
                        R²: <?= number_format((float)$modelAktif['r2'] * 100, 1) ?>%
                    </span>
                <?php endif; ?>
                <?php if (!empty($modelAktif['total_record'])): ?>
                    <span class="aktif-metric">
                        <i class="bi bi-table"></i>
                        <?= number_format($modelAktif['total_record']) ?> record
                    </span>
                <?php endif; ?>
                <span class="aktif-metric">
                    <i class="bi bi-clock"></i>
                    <?= $modelAktif['created_at'] ? date('d M Y, H:i', strtotime($modelAktif['created_at'])) : '—' ?>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <!-- ── Model List ── -->
    <div class="card-mg">
        <div class="mg-card-header">
            <div class="mg-card-title-group">
                <span class="section-title">Semua Versi Model</span>
                <span class="badge-mg badge-cyan"><?= count($riwayat) ?> model</span>
            </div>
            <div class="mg-card-toolbar">
                <div class="mg-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="mgSearchModel" placeholder="Cari versi…">
                </div>
            </div>
        </div>

        <?php if (!empty($riwayat)): ?>
            <div class="model-list" id="modelList">
                <?php foreach ($riwayat as $m):
                    $isAktif   = (int)($m['is_active'] ?? 0) === 1;
                    $statusCls = $m['status'] === 'success' ? 'success' : ($m['status'] === 'running' ? 'running' : 'failed');
                    $statusLbl = $m['status'] === 'success' ? 'Sukses' : ($m['status'] === 'running' ? 'Berjalan' : 'Gagal');
                    $r2Pct     = isset($m['r2']) && $m['r2'] !== null ? (float)$m['r2'] * 100 : null;
                ?>
                    <div class="model-card <?= $isAktif ? 'is-active' : '' ?>" data-versi="<?= esc($m['versi'] ?? '') ?>">
                        <div class="model-card-left">
                            <div class="model-icon <?= $statusCls ?>">
                                <?php if ($m['status'] === 'running'): ?>
                                    <i class="bi bi-arrow-repeat spin"></i>
                                <?php elseif ($m['status'] === 'success'): ?>
                                    <i class="bi bi-cpu-fill"></i>
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill"></i>
                                <?php endif; ?>
                            </div>
                            <div class="model-info">
                                <div class="model-versi">
                                    <?= esc($m['versi'] ?? 'v—') ?>
                                    <?php if ($isAktif): ?>
                                        <span class="badge-aktif"><i class="bi bi-check2-circle"></i> Aktif</span>
                                    <?php endif; ?>
                                </div>
                                <div class="model-meta-row">
                                    <span class="status-pill status-<?= $statusCls ?>"><?= $statusLbl ?></span>
                                    <?php if (!empty($m['total_record'])): ?>
                                        <span class="model-meta-item"><i class="bi bi-table"></i> <?= number_format($m['total_record']) ?> rec</span>
                                    <?php endif; ?>
                                    <?php if (!empty($m['total_produk'])): ?>
                                        <span class="model-meta-item"><i class="bi bi-phone"></i> <?= number_format($m['total_produk']) ?> produk</span>
                                    <?php endif; ?>
                                    <span class="model-meta-item">
                                        <i class="bi bi-clock"></i>
                                        <?= $m['created_at'] ? date('d M Y, H:i', strtotime($m['created_at'])) : '—' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="model-card-metrics">
                            <?php if (isset($m['mae']) && $m['mae'] !== null): ?>
                                <div class="metric-block">
                                    <div class="metric-val mae-val"><?= number_format((float)$m['mae'], 2) ?></div>
                                    <div class="metric-lbl">MAE</div>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($m['rmse']) && $m['rmse'] !== null): ?>
                                <div class="metric-block">
                                    <div class="metric-val"><?= number_format((float)$m['rmse'], 2) ?></div>
                                    <div class="metric-lbl">RMSE</div>
                                </div>
                            <?php endif; ?>
                            <?php if ($r2Pct !== null): ?>
                                <div class="metric-block">
                                    <div class="metric-val r2-val <?= $r2Pct >= 80 ? 'good' : ($r2Pct >= 60 ? 'ok' : 'bad') ?>">
                                        <?= number_format($r2Pct, 1) ?>%
                                    </div>
                                    <div class="metric-lbl">R²</div>
                                    <div class="r2-bar-wrap">
                                        <div class="r2-bar-fill <?= $r2Pct >= 80 ? 'good' : ($r2Pct >= 60 ? 'ok' : 'bad') ?>"
                                            style="width:<?= min(100, max(0, $r2Pct)) ?>%"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($m['durasi_detik'])): ?>
                                <div class="metric-block">
                                    <div class="metric-val"><?= number_format((float)$m['durasi_detik'], 0) ?>s</div>
                                    <div class="metric-lbl">Durasi</div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="model-card-actions">
                            <button class="action-btn detail-btn btnDetailModel"
                                data-id="<?= esc($m['id'] ?? $m['id_model'] ?? '') ?>" title="Detail">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                            <?php if ($m['status'] === 'success' && !$isAktif): ?>
                                <button class="action-btn aktif-btn btnAktifkan"
                                    data-id="<?= esc($m['id'] ?? $m['id_model'] ?? '') ?>"
                                    data-versi="<?= esc($m['versi'] ?? '') ?>"
                                    title="Aktifkan model ini">
                                    <i class="bi bi-check-circle-fill"></i>
                                </button>
                            <?php endif; ?>
                            <?php if (!$isAktif && $m['status'] !== 'running'): ?>
                                <button class="action-btn arsip-btn btnArsipkan"
                                    data-id="<?= esc($m['id'] ?? $m['id_model'] ?? '') ?>"
                                    data-versi="<?= esc($m['versi'] ?? '') ?>"
                                    title="Arsipkan model">
                                    <i class="bi bi-archive-fill"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state-block">
                <i class="bi bi-cpu" style="font-size:44px;color:var(--text-placeholder);display:block;margin-bottom:12px"></i>
                <div style="font-weight:600;font-size:14px;color:var(--text-muted);margin-bottom:6px">Belum ada model yang dilatih</div>
                <div style="font-size:12px;color:var(--text-placeholder)">
                    Klik <strong>Jalankan Training</strong> untuk melatih model pertama Anda.
                </div>
                <a href="<?= base_url('training/proses') ?>" class="btn-mg btn-generate" style="margin-top:16px;text-decoration:none;display:inline-flex">
                    <i class="bi bi-play-circle-fill"></i> Jalankan Training
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     MODAL DETAIL MODEL
═══════════════════════════════════════════════════════════ -->
<div class="mg-modal-overlay" id="overlayDetail">
    <div class="mg-modal mg-modal-xl">
        <div class="mg-modal-header" style="--modal-accent:var(--mg-glow)">
            <div class="mg-modal-icon" style="background:rgba(46,109,164,0.15);color:var(--mg-light)">
                <i class="bi bi-cpu-fill"></i>
            </div>
            <div>
                <div class="mg-modal-title" id="detailModalTitle">Detail Model</div>
                <div class="mg-modal-sub" id="detailModalSub">Informasi lengkap model training</div>
            </div>
            <button class="mg-modal-close" id="closeDetail" type="button"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="mg-modal-body" id="detailModalBody">
            <div class="detail-loading">
                <div class="spinner-ring"></div>
                <span>Memuat data…</span>
            </div>
        </div>
        <div class="mg-modal-footer">
            <button class="btn-mg btn-outline-mg" id="cancelDetail" type="button">Tutup</button>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     SCOPED STYLES
═══════════════════════════════════════════════════════════ -->
<style>
    .content-wrapper-inner {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* ── Page header ── */
    .mg-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }

    .page-header-left {
        flex-shrink: 0;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    /* ── Quick Nav Pills ── */
    .quick-nav-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .quick-nav-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        border-radius: var(--radius-sm);
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: var(--transition);
        white-space: nowrap;
    }

    .quick-nav-pill:hover {
        background: rgba(74, 159, 212, 0.1);
        border-color: rgba(74, 159, 212, 0.35);
        color: var(--mg-light);
        transform: translateY(-1px);
    }

    .quick-nav-pill.active {
        background: rgba(74, 159, 212, 0.15);
        border-color: rgba(74, 159, 212, 0.4);
        color: var(--mg-light);
    }

    .pill-badge {
        display: inline-flex;
        align-items: center;
        padding: 1px 7px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .03em;
    }

    .ml-badge {
        background: rgba(16, 183, 127, 0.15);
        color: var(--accent-green);
        border: 1px solid rgba(16, 183, 127, 0.3);
    }

    /* ── Buttons ── */
    .btn-generate {
        background: linear-gradient(135deg, rgba(74, 159, 212, 0.12), rgba(0, 151, 184, 0.1));
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 151, 184, 0.3);
        white-space: nowrap;
    }

    .btn-generate:hover {
        background: linear-gradient(135deg, rgba(74, 159, 212, 0.22), rgba(0, 151, 184, 0.18));
    }

    /* ── Stats ── */
    .mg-stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    @media(max-width:900px) {
        .mg-stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:540px) {
        .mg-stats-row {
            grid-template-columns: 1fr 1fr;
        }
    }

    /* ── Aktif Banner ── */
    .aktif-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        padding: 16px 20px;
        background: linear-gradient(135deg, rgba(16, 183, 127, 0.07), rgba(0, 151, 184, 0.05));
        border: 1px solid rgba(16, 183, 127, 0.25);
        border-radius: var(--radius-md);
    }

    .aktif-banner-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .aktif-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--accent-green);
        box-shadow: 0 0 0 4px rgba(16, 183, 127, 0.18);
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {

        0%,
        100% {
            box-shadow: 0 0 0 4px rgba(16, 183, 127, 0.18)
        }

        50% {
            box-shadow: 0 0 0 8px rgba(16, 183, 127, 0.08)
        }
    }

    .aktif-label {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-bottom: 2px;
    }

    .aktif-versi {
        font-size: 15px;
        font-weight: 700;
        color: var(--accent-green);
    }

    .aktif-meta {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .aktif-metric {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: var(--text-muted);
    }

    .aktif-metric i {
        color: var(--accent-cyan);
    }

    /* ── Model List ── */
    .model-list {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .model-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        background: var(--card-bg);
        border-bottom: 1px solid var(--surface-border);
        transition: var(--transition);
        flex-wrap: wrap;
    }

    .model-card:first-child {
        border-radius: var(--radius-sm) var(--radius-sm) 0 0;
    }

    .model-card:last-child {
        border-bottom: none;
        border-radius: 0 0 var(--radius-sm) var(--radius-sm);
    }

    .model-card:hover {
        background: var(--card-bg-alt);
    }

    .model-card.is-active {
        background: rgba(16, 183, 127, 0.04);
        border-left: 3px solid var(--accent-green);
    }

    .model-card-left {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
        min-width: 220px;
    }

    .model-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .model-icon.success {
        background: rgba(16, 183, 127, 0.12);
        color: var(--accent-green);
    }

    .model-icon.running {
        background: rgba(74, 159, 212, 0.12);
        color: var(--accent-cyan);
    }

    .model-icon.failed {
        background: rgba(229, 62, 62, 0.1);
        color: var(--accent-red);
    }

    .spin {
        animation: spin 1.2s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg)
        }
    }

    .model-versi {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-ink);
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .badge-aktif {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 10.5px;
        font-weight: 600;
        background: rgba(16, 183, 127, 0.12);
        color: var(--accent-green);
        border: 1px solid rgba(16, 183, 127, 0.25);
    }

    .model-meta-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-success {
        background: rgba(16, 183, 127, 0.1);
        color: var(--accent-green);
        border: 1px solid rgba(16, 183, 127, 0.2);
    }

    .status-running {
        background: rgba(74, 159, 212, 0.1);
        color: var(--accent-cyan);
        border: 1px solid rgba(74, 159, 212, 0.2);
    }

    .status-failed {
        background: rgba(229, 62, 62, 0.1);
        color: var(--accent-red);
        border: 1px solid rgba(229, 62, 62, 0.2);
    }

    .model-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11.5px;
        color: var(--text-muted);
    }

    .model-meta-item i {
        font-size: 11px;
        color: var(--text-placeholder);
    }

    /* ── Metrics ── */
    .model-card-metrics {
        display: flex;
        align-items: center;
        gap: 24px;
        flex-wrap: wrap;
    }

    .metric-block {
        text-align: center;
        min-width: 52px;
    }

    .metric-val {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-ink);
        font-family: 'Courier New', monospace;
    }

    .metric-val.mae-val {
        color: var(--accent-cyan);
    }

    .metric-val.r2-val.good {
        color: var(--accent-green);
    }

    .metric-val.r2-val.ok {
        color: var(--accent-yellow);
    }

    .metric-val.r2-val.bad {
        color: var(--accent-red);
    }

    .metric-lbl {
        font-size: 10px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-top: 1px;
    }

    .r2-bar-wrap {
        height: 3px;
        background: var(--surface-border);
        border-radius: 999px;
        margin-top: 3px;
        overflow: hidden;
        width: 52px;
    }

    .r2-bar-fill {
        height: 100%;
        border-radius: 999px;
    }

    .r2-bar-fill.good {
        background: var(--accent-green);
    }

    .r2-bar-fill.ok {
        background: var(--accent-yellow);
    }

    .r2-bar-fill.bad {
        background: var(--accent-red);
    }

    /* ── Actions ── */
    .model-card-actions {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-left: auto;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        cursor: pointer;
        transition: var(--transition);
        background: none;
    }

    .detail-btn {
        background: rgba(74, 159, 212, 0.08);
        color: var(--accent-cyan);
        border-color: rgba(74, 159, 212, 0.2);
    }

    .detail-btn:hover {
        background: rgba(74, 159, 212, 0.2);
        transform: translateY(-1px);
    }

    .aktif-btn {
        background: rgba(16, 183, 127, 0.1);
        color: var(--accent-green);
        border-color: rgba(16, 183, 127, 0.25);
    }

    .aktif-btn:hover {
        background: rgba(16, 183, 127, 0.22);
        transform: translateY(-1px);
    }

    .arsip-btn {
        background: rgba(212, 160, 23, 0.1);
        color: var(--accent-yellow);
        border-color: rgba(212, 160, 23, 0.2);
    }

    .arsip-btn:hover {
        background: rgba(212, 160, 23, 0.22);
        transform: translateY(-1px);
    }

    /* ── Empty state ── */
    .empty-state-block {
        text-align: center;
        padding: 60px 24px;
        color: var(--text-muted);
    }

    /* ── Card header ── */
    .mg-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 14px;
    }

    .mg-card-title-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .mg-card-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .mg-search-box {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 14px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-sm);
        color: var(--text-muted);
        transition: var(--transition);
    }

    .mg-search-box:focus-within {
        border-color: var(--mg-light);
        box-shadow: 0 0 0 3px rgba(74, 159, 212, 0.1);
    }

    .mg-search-box input {
        border: none;
        background: transparent;
        color: var(--text-ink);
        font-size: 13px;
        outline: none;
        width: 160px;
    }

    .mg-search-box input::placeholder {
        color: var(--text-placeholder);
    }

    /* ── Modal ── */
    .mg-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(10, 16, 28, 0.65);
        backdrop-filter: blur(5px);
        z-index: 1200;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .mg-modal-overlay.open {
        display: flex;
        animation: mgFadeIn .18s ease;
    }

    @keyframes mgFadeIn {
        from {
            opacity: 0
        }

        to {
            opacity: 1
        }
    }

    .mg-modal {
        background: var(--card-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-xl);
        width: 100%;
        max-width: 500px;
        box-shadow: 0 24px 60px rgba(14, 23, 36, .25);
        animation: mgSlideUp .2s cubic-bezier(.4, 0, .2, 1);
        overflow: hidden;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }

    .mg-modal-xl {
        max-width: 640px;
    }

    @keyframes mgSlideUp {
        from {
            transform: translateY(18px);
            opacity: 0
        }

        to {
            transform: none;
            opacity: 1
        }
    }

    .mg-modal-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 20px 22px;
        background: var(--card-bg-alt);
        border-bottom: 1px solid var(--surface-border);
        position: relative;
        flex-shrink: 0;
    }

    .mg-modal-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--modal-accent, var(--mg-glow));
        border-radius: var(--radius-xl) var(--radius-xl) 0 0;
    }

    .mg-modal-icon {
        width: 42px;
        height: 42px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        flex-shrink: 0;
    }

    .mg-modal-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-ink);
    }

    .mg-modal-sub {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 1px;
    }

    .mg-modal-close {
        margin-left: auto;
        width: 32px;
        height: 32px;
        background: var(--card-bg);
        border: 1px solid var(--surface-border);
        border-radius: 8px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 13px;
        transition: var(--transition);
    }

    .mg-modal-close:hover {
        color: var(--accent-red);
        border-color: rgba(229, 62, 62, .3);
        background: rgba(229, 62, 62, .06);
    }

    .mg-modal-body {
        padding: 22px;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        flex: 1;
    }

    .mg-modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 22px;
        border-top: 1px solid var(--surface-border);
        background: var(--card-bg-alt);
        flex-shrink: 0;
    }

    /* ── Detail loading ── */
    .detail-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 40px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .spinner-ring {
        width: 24px;
        height: 24px;
        border: 3px solid var(--surface-border);
        border-top-color: var(--accent-cyan);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    /* ── Detail content ── */
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 16px;
    }

    .detail-item {
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-sm);
        padding: 12px 14px;
    }

    .detail-item-label {
        font-size: 10.5px;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .detail-item-value {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-ink);
        font-family: 'Courier New', monospace;
    }

    .detail-section-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--text-muted);
        margin: 14px 0 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid var(--surface-border);
    }

    .fi-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 0;
        border-bottom: 1px solid var(--surface-border);
    }

    .fi-row:last-child {
        border-bottom: none;
    }

    .fi-name {
        font-size: 12px;
        flex: 1;
        color: var(--text-ink);
    }

    .fi-bar-wrap {
        width: 120px;
        height: 5px;
        background: var(--surface-border);
        border-radius: 999px;
        overflow: hidden;
    }

    .fi-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--mg-glow), var(--accent-cyan));
        border-radius: 999px;
    }

    .fi-pct {
        font-size: 11px;
        color: var(--accent-cyan);
        font-weight: 600;
        font-family: 'Courier New', monospace;
        min-width: 38px;
        text-align: right;
    }

    /* ── Mobile ── */
    @media(max-width:768px) {
        .model-card {
            gap: 12px;
        }

        .model-card-metrics {
            gap: 14px;
        }

        .model-card-actions {
            margin-left: 0;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ── Detail modal tabs ── */
    .detail-tabs {
        display: flex;
        gap: 2px;
        flex-wrap: wrap;
        margin-bottom: 16px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-sm);
        padding: 4px;
    }

    .detail-tab {
        flex: 1;
        padding: 7px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        background: none;
        border: none;
        color: var(--text-muted);
        transition: var(--transition);
        white-space: nowrap;
        min-width: 80px;
    }

    .detail-tab:hover {
        background: var(--card-bg);
        color: var(--text-ink);
    }

    .detail-tab.active {
        background: var(--card-bg);
        color: var(--mg-light);
        box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
    }

    /* ── Diagram pills ── */
    .diag-pill-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
    }

    .diag-pill {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid var(--surface-border);
        background: transparent;
        color: var(--text-muted);
        transition: var(--transition);
    }

    .diag-pill:hover {
        border-color: rgba(74, 159, 212, .35);
        color: var(--mg-light);
    }

    .diag-pill.active {
        background: rgba(74, 159, 212, .12);
        color: var(--mg-light);
        border-color: rgba(74, 159, 212, .4);
    }

    /* ── Diagram frame ── */
    .diag-frame {
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-sm);
        overflow: hidden;
        aspect-ratio: 16/9;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .diag-empty,
    .diag-loading {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: var(--text-placeholder);
        font-size: 12px;
        height: 100%;
        width: 100%;
    }

    .diag-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 10px;
    }

    /* ── Terminal log (dark) ── */
    .detail-terminal {
        background: #0d1117;
        border: 1px solid rgba(74, 159, 212, 0.2);
        border-radius: var(--radius-sm);
        padding: 14px 16px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        line-height: 1.7;
        max-height: 360px;
        overflow-y: auto;
        color: #c9d1d9;
        box-shadow: inset 0 2px 12px rgba(0, 0, 0, 0.4);
    }

    .detail-terminal::-webkit-scrollbar {
        width: 6px;
    }

    .detail-terminal::-webkit-scrollbar-track {
        background: #161b22;
    }

    .detail-terminal::-webkit-scrollbar-thumb {
        background: #30363d;
        border-radius: 3px;
    }

    /* ── Detail grid 3 kolom untuk metrik ── */
    .detail-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 14px;
    }

    .detail-item-accent-cyan .detail-item-value {
        color: var(--accent-cyan);
    }

    .detail-item-accent-green .detail-item-value {
        color: var(--accent-green);
    }

    /* ── Feature importance dalam modal ── */
    .fi-row-modal {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 7px 0;
        border-bottom: 1px solid var(--surface-border);
    }

    .fi-row-modal:last-child {
        border-bottom: none;
    }

    .fi-rank {
        font-size: 10px;
        font-weight: 700;
        color: var(--text-placeholder);
        min-width: 18px;
        text-align: right;
    }

    .fi-name-modal {
        font-size: 12px;
        font-family: 'Courier New', monospace;
        color: var(--text-ink);
        flex: 1;
    }

    .fi-bar-wrap-modal {
        width: 100px;
        height: 6px;
        background: var(--surface-border);
        border-radius: 999px;
        overflow: hidden;
    }

    .fi-bar-fill-modal {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--mg-glow), var(--accent-cyan));
        transition: width .8s ease;
    }

    .fi-pct-modal {
        font-size: 11px;
        color: var(--accent-cyan);
        font-weight: 700;
        font-family: 'Courier New', monospace;
        min-width: 40px;
        text-align: right;
    }
</style>


<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════ -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        const qs = sel => document.querySelector(sel);

        // ── Modal helpers ─────────────────────────────────────────────────────────
        function openModal(id) {
            const el = qs('#' + id);
            if (!el) return;
            el.style.display = '';
            el.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(id) {
            const el = qs('#' + id);
            if (!el) return;
            el.classList.remove('open');
            el.style.display = 'none';
            document.body.style.overflow = '';
        }

        function escHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        qs('#closeDetail')?.addEventListener('click', () => closeModal('overlayDetail'));
        qs('#cancelDetail')?.addEventListener('click', () => closeModal('overlayDetail'));
        qs('#overlayDetail')?.addEventListener('click', e => {
            if (e.target === qs('#overlayDetail')) closeModal('overlayDetail');
        });

        // ── Search filter ─────────────────────────────────────────────────────────
        qs('#mgSearchModel')?.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.model-card').forEach(card => {
                card.style.display = card.dataset.versi.toLowerCase().includes(q) ? '' : 'none';
            });
        });

        // ── Detail modal ─────────────────────────────────────────────
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btnDetailModel');
            if (!btn) return;
            const id = btn.dataset.id;

            qs('#detailModalTitle').textContent = 'Detail Model';
            qs('#detailModalSub').textContent = 'Memuat…';
            qs('#detailModalBody').innerHTML =
                '<div class="detail-loading"><div class="spinner-ring"></div><span>Memuat data model…</span></div>';
            openModal('overlayDetail');

            fetch(BASE_URL + 'training/model/detail/' + id, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(r => r.json()).then(res => {
                if (res.status === 'error') {
                    qs('#detailModalBody').innerHTML =
                        `<div style="color:var(--accent-red);padding:20px;text-align:center">${res.message}</div>`;
                    return;
                }

                const d = res.data;
                const logs = res.logs || [];

                qs('#detailModalTitle').textContent = d.versi || 'Detail Model';
                qs('#detailModalSub').textContent = 'Dibuat: ' + (d.created_at ?? '—');

                // ── Diagram keys yang tersedia ────────────────────────────
                const diagramPaths = d.diagram_paths ?
                    (typeof d.diagram_paths === 'string' ?
                        JSON.parse(d.diagram_paths) : d.diagram_paths) : {};

                const diagramMeta = {
                    train_actual_vs_pred: 'Actual vs Pred (Train)',
                    test_actual_vs_pred: 'Actual vs Pred (Test)',
                    train_residual: 'Residual (Train)',
                    test_residual: 'Residual (Test)',
                    feature_importance: 'Feature Importance',
                    train_timeseries: 'Time Series (Train)',
                    test_timeseries: 'Time Series (Test)',
                    top_products: 'Top Produk',
                    monthly_trend: 'Tren Bulanan',
                };

                const availableKeys = Object.keys(diagramPaths);

                // ── Bangun HTML ───────────────────────────────────────────
                let html = '';

                // Tab bar
                html += `<div class="detail-tabs">
            <button class="detail-tab active" data-tab="tab-metrics">Metrik</button>`;
                if (availableKeys.length > 0) {
                    html += `<button class="detail-tab" data-tab="tab-diagrams">Diagram (${availableKeys.length})</button>`;
                }
                if (d.feature_importance && Object.keys(d.feature_importance).length > 0) {
                    html += `<button class="detail-tab" data-tab="tab-features">Feature Importance</button>`;
                }
                if (logs.length > 0) {
                    html += `<button class="detail-tab" data-tab="tab-logs">Log (${logs.length})</button>`;
                }
                html += `</div>`;

                // ── TAB: Metrik ───────────────────────────────────────────
                html += `<div class="detail-tab-content" id="tab-metrics">`;
                const metrics = [{
                        label: 'MAE',
                        value: d.mae != null ? parseFloat(d.mae).toFixed(4) : '—'
                    },
                    {
                        label: 'RMSE',
                        value: d.rmse != null ? parseFloat(d.rmse).toFixed(4) : '—'
                    },
                    {
                        label: 'R²',
                        value: d.r2 != null ? (parseFloat(d.r2) * 100).toFixed(2) + '%' : '—'
                    },
                    {
                        label: 'MAPE',
                        value: d.mape != null ? parseFloat(d.mape).toFixed(4) : '—'
                    },
                    {
                        label: 'Record',
                        value: d.total_record ? Number(d.total_record).toLocaleString() : '—'
                    },
                    {
                        label: 'Produk',
                        value: d.total_produk ? Number(d.total_produk).toLocaleString() : '—'
                    },
                ];
                html += '<div class="detail-grid">';
                metrics.forEach(m => {
                    html += `<div class="detail-item">
                <div class="detail-item-label">${m.label}</div>
                <div class="detail-item-value">${m.value}</div>
            </div>`;
                });
                html += '</div>';

                // Best params
                if (d.best_params && typeof d.best_params === 'object' && Object.keys(d.best_params).length > 0) {
                    html += `<div class="detail-section-title">Best Hyperparameter</div>
            <div style="background:var(--card-bg-alt);border:1px solid var(--surface-border);border-radius:var(--radius-sm);padding:10px 14px;font-size:12px;font-family:'Courier New',monospace;color:var(--text-muted);line-height:1.8;">`;
                    Object.entries(d.best_params).forEach(([k, v]) => {
                        html += `<div><span style="color:var(--accent-cyan)">${escHtml(k)}</span>: <span style="color:var(--text-ink)">${escHtml(String(v))}</span></div>`;
                    });
                    html += `</div>`;
                }
                html += `</div>`;

                // ── TAB: Diagram ──────────────────────────────────────────
                if (availableKeys.length > 0) {
                    html += `<div class="detail-tab-content" id="tab-diagrams" style="display:none">`;
                    html += `<div class="diag-pill-row">`;
                    availableKeys.forEach((key, i) => {
                        const label = diagramMeta[key] || key;
                        html += `<button class="diag-pill ${i===0?'active':''}" data-key="${key}" data-id="${id}">${label}</button>`;
                    });
                    html += `</div>`;
                    html += `<div class="diag-frame" id="diagFrame">
                <img id="diagImg" src="" alt="Diagram" style="width:100%;height:100%;object-fit:contain;display:none">
                <div class="diag-loading" id="diagLoading" style="display:none">
                    <div class="spinner-ring"></div><span>Memuat diagram…</span>
                </div>
                <div class="diag-empty" id="diagEmpty">
                    <i class="bi bi-bar-chart-fill" style="font-size:32px;opacity:.3"></i>
                    <span>Pilih diagram di atas</span>
                </div>
            </div>`;
                    html += `<div class="diag-actions">
                <button class="btn-mg btn-outline-mg btn-sm-mg" id="btnDiagZoom" style="display:none">
                    <i class="bi bi-zoom-in"></i> Perbesar
                </button>
                <a id="btnDiagDownload" href="#" download style="display:none" class="btn-mg btn-primary-mg btn-sm-mg">
                    <i class="bi bi-download"></i> Unduh PNG
                </a>
            </div>`;
                    html += `</div>`;
                }

                // ── TAB: Feature Importance ───────────────────────────────
                // ── TAB: Feature Importance ───────────────────────────────
                if (d.feature_importance && Object.keys(d.feature_importance).length > 0) {
                    html += `<div class="detail-tab-content" id="tab-features" style="display:none">`;
                    html += `<div class="detail-section-title" style="margin-top:0">Top Fitur Berpengaruh</div>`;
                    const sorted = Object.entries(d.feature_importance).sort((a, b) => b[1] - a[1]).slice(0, 16);
                    const maxFi = sorted[0][1];
                    sorted.forEach(([feat, imp], idx) => {
                        const barPct = (imp / maxFi * 100).toFixed(1);
                        const impPct = (imp * 100).toFixed(2);
                        html += `<div class="fi-row-modal">
            <span class="fi-rank">${idx+1}</span>
            <span class="fi-name-modal">${escHtml(feat)}</span>
            <div class="fi-bar-wrap-modal">
                <div class="fi-bar-fill-modal" style="width:${barPct}%"></div>
            </div>
            <span class="fi-pct-modal">${impPct}%</span>
        </div>`;
                    });
                    html += `</div>`;
                }

                // ── TAB: Log ──────────────────────────────────────────────
                // ── TAB: Log ──────────────────────────────────────────────
                if (logs.length > 0) {
                    html += `<div class="detail-tab-content" id="tab-logs" style="display:none">`;
                    html += `<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
        <span style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em">${logs.length} entri log</span>
        <span style="font-size:11px;color:var(--text-placeholder)">scroll untuk lihat semua</span>
    </div>`;
                    html += `<div class="detail-terminal">`;
                    // Header terminal
                    html += `<div style="color:#484f58;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid #21262d">[ Mi Store Training Log — ${escHtml(d.versi||'')} ]</div>`;
                    logs.forEach(log => {
                        const lvl = log.level || 'info';
                        const color = lvl === 'error' ? '#f85149' :
                            lvl === 'warning' ? '#e3b341' :
                            lvl === 'success' ? '#3fb950' : '#8b949e';
                        const icon = lvl === 'error' ? '✖' :
                            lvl === 'warning' ? '⚠' :
                            lvl === 'success' ? '✔' : '·';
                        const ts = escHtml(log.logged_at ?? '');
                        const msg = escHtml(log.pesan ?? '');
                        html += `<div style="padding:2px 0;border-bottom:1px solid rgba(255,255,255,0.04);display:flex;gap:8px;align-items:flex-start">
            <span style="color:#30363d;flex-shrink:0;font-size:10px;margin-top:1px">${icon}</span>
            <span style="color:#58a6ff;flex-shrink:0;font-size:11px">[${ts}]</span>
            <span style="color:${color};font-size:12px;word-break:break-word">${msg}</span>
        </div>`;
                    });
                    html += `</div></div>`;
                }

                qs('#detailModalBody').innerHTML = html;

                // ── Tab switching ─────────────────────────────────────────
                qs('#detailModalBody').querySelectorAll('.detail-tab').forEach(tab => {
                    tab.addEventListener('click', function() {
                        qs('#detailModalBody').querySelectorAll('.detail-tab').forEach(t => t.classList.remove('active'));
                        qs('#detailModalBody').querySelectorAll('.detail-tab-content').forEach(c => c.style.display = 'none');
                        this.classList.add('active');
                        const target = qs('#detailModalBody').querySelector('#' + this.dataset.tab);
                        if (target) target.style.display = '';
                    });
                });

                // ── Diagram pill switching ────────────────────────────────
                qs('#detailModalBody').querySelectorAll('.diag-pill').forEach(pill => {
                    pill.addEventListener('click', function() {
                        qs('#detailModalBody').querySelectorAll('.diag-pill').forEach(p => p.classList.remove('active'));
                        this.classList.add('active');
                        loadDiagram(this.dataset.id, this.dataset.key);
                    });
                });

                // Auto-load diagram pertama
                if (availableKeys.length > 0) {
                    setTimeout(() => loadDiagram(id, availableKeys[0]), 100);
                }

            }).catch(() => {
                qs('#detailModalBody').innerHTML =
                    '<div style="color:var(--accent-red);padding:20px;text-align:center">Gagal memuat data model.</div>';
            });
        });

        // ── Load diagram dari server ──────────────────────────────────
        function loadDiagram(id, key) {
            const img = document.getElementById('diagImg');
            const loading = document.getElementById('diagLoading');
            const empty = document.getElementById('diagEmpty');
            const btnZoom = document.getElementById('btnDiagZoom');
            const btnDown = document.getElementById('btnDiagDownload');
            if (!img) return;

            img.style.display = 'none';
            empty.style.display = 'none';
            loading.style.display = 'flex';
            if (btnZoom) btnZoom.style.display = 'none';
            if (btnDown) btnDown.style.display = 'none';

            const url = BASE_URL + 'training/model/diagram/' + id + '/' + key;

            const tempImg = new Image();
            tempImg.onload = function() {
                img.src = url;
                img.style.display = 'block';
                loading.style.display = 'none';
                if (btnZoom) {
                    btnZoom.style.display = '';
                    btnZoom.onclick = () => window.open(url, '_blank');
                }
                if (btnDown) {
                    btnDown.style.display = '';
                    btnDown.href = url;
                    btnDown.download = key + '.png';
                }
            };
            tempImg.onerror = function() {
                loading.style.display = 'none';
                empty.style.display = 'flex';
                empty.innerHTML = '<i class="bi bi-exclamation-circle" style="font-size:28px;opacity:.4"></i><span>Diagram tidak tersedia</span>';
            };
            tempImg.src = url;
        }

        // ── Aktifkan model ────────────────────────────────────────────────────────
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btnAktifkan');
            if (!btn) return;
            Swal.fire({
                title: 'Aktifkan model ini?',
                html: `Model <b>${btn.dataset.versi}</b> akan digunakan sebagai model prediksi aktif.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b77f',
                cancelButtonColor: '#243B55',
                confirmButtonText: '<i class="bi bi-check-circle-fill"></i> Aktifkan',
                cancelButtonText: 'Batal',
            }).then(r => {
                if (!r.isConfirmed) return;
                const fd = new FormData();
                fd.append('id', btn.dataset.id);
                fetch(BASE_URL + 'training/model/aktifkan', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                }).then(r => r.json()).then(res => {
                    Swal.fire({
                            icon: res.status === 'success' ? 'success' : 'error',
                            title: res.status === 'success' ? 'Berhasil!' : 'Gagal',
                            text: res.message,
                            confirmButtonColor: '#2e6da4'
                        })
                        .then(() => location.reload());
                });
            });
        });

        // ── Arsipkan model ────────────────────────────────────────────────────────
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btnArsipkan');
            if (!btn) return;
            Swal.fire({
                title: 'Arsipkan model?',
                html: `Model <b>${btn.dataset.versi}</b> akan diarsipkan dan tidak bisa langsung dipakai.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d4a017',
                cancelButtonColor: '#243B55',
                confirmButtonText: 'Arsipkan',
                cancelButtonText: 'Batal',
            }).then(r => {
                if (!r.isConfirmed) return;
                const fd = new FormData();
                fd.append('id', btn.dataset.id);
                fetch(BASE_URL + 'training/model/arsipkan', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                }).then(r => r.json()).then(res => {
                    Swal.fire({
                            icon: res.status === 'success' ? 'success' : 'error',
                            title: res.status === 'success' ? 'Diarsipkan!' : 'Gagal',
                            text: res.message,
                            confirmButtonColor: '#2e6da4'
                        })
                        .then(() => location.reload());
                });
            });
        });
    });
</script>