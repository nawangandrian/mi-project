<?php

/**
 * View  : pages/training/proses.php
 * Modul : Jalankan Training Model Random Forest — Mi Store Kudus
 */
?>

<div class="content-wrapper-inner">

    <!-- ── Page Header ── -->
    <div class="mg-page-header">
        <div>
            <h1 class="page-title">Jalankan Training</h1>
            <p class="page-subtitle">Latih ulang model Random Forest dari data training terbaru</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('prediksi/jalankan') ?>" class="btn-mg btn-outline-mg">
                <i class="bi bi-play-circle-fill"></i> Jalankan Prediksi
            </a>
        </div>
    </div>

    <!-- ── Quick Nav Pills ── -->
    <div class="quick-nav-row">
        <a href="<?= base_url('training/proses') ?>"
            class="quick-nav-pill active">
            <i class="bi bi-play-circle-fill"></i>
            <span>Jalankan Training</span>
            <span class="pill-badge ml-badge">ML</span>
        </a>
        <a href="<?= base_url('training/riwayat_model') ?>"
            class="quick-nav-pill">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat Model</span>
        </a>
    </div>

    <!-- ── Pipeline Steps Visual ── -->
    <div class="card-mg">
        <div class="mg-card-header">
            <div class="mg-card-title-group">
                <span class="section-title">Pipeline Training</span>
            </div>
        </div>
        <div class="trn-pipeline">
            <div class="trn-pipe-step">
                <div class="trn-pipe-icon" style="background:rgba(74,159,212,0.12);color:var(--mg-light)">
                    <i class="bi bi-database-fill-down"></i>
                </div>
                <div class="trn-pipe-label">Load Data</div>
                <div class="trn-pipe-desc">Baca data training dari MySQL</div>
            </div>
            <div class="trn-pipe-arrow"><i class="bi bi-arrow-right"></i></div>
            <div class="trn-pipe-step">
                <div class="trn-pipe-icon" style="background:rgba(0,151,184,0.12);color:var(--accent-cyan)">
                    <i class="bi bi-sliders2"></i>
                </div>
                <div class="trn-pipe-label">Preprocessing</div>
                <div class="trn-pipe-desc">Encoding, fillna, feature selection</div>
            </div>
            <div class="trn-pipe-arrow"><i class="bi bi-arrow-right"></i></div>
            <div class="trn-pipe-step">
                <div class="trn-pipe-icon" style="background:rgba(212,160,23,0.12);color:var(--accent-yellow)">
                    <i class="bi bi-bezier2"></i>
                </div>
                <div class="trn-pipe-label">RandomizedSearchCV</div>
                <div class="trn-pipe-desc">Tuning 20 iter × 5-fold TSS</div>
            </div>
            <div class="trn-pipe-arrow"><i class="bi bi-arrow-right"></i></div>
            <div class="trn-pipe-step">
                <div class="trn-pipe-icon" style="background:rgba(16,183,127,0.12);color:var(--accent-green)">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <div class="trn-pipe-label">Evaluasi</div>
                <div class="trn-pipe-desc">MAE, RMSE, R², MAPE</div>
            </div>
            <div class="trn-pipe-arrow"><i class="bi bi-arrow-right"></i></div>
            <div class="trn-pipe-step">
                <div class="trn-pipe-icon" style="background:rgba(229,62,62,0.12);color:var(--accent-red)">
                    <i class="bi bi-floppy2-fill"></i>
                </div>
                <div class="trn-pipe-label">Simpan Model</div>
                <div class="trn-pipe-desc">model_rf.pkl + label_encoder.pkl</div>
            </div>
        </div>
    </div>

    <!-- ── Baris atas: Info Card + Status Card ── -->
    <div class="trn-top-grid">

        <!-- Info & Tombol Training -->
        <div class="card-mg trn-info-card">
            <div class="trn-info-header">
                <div class="trn-brain-icon">
                    <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="40" cy="40" r="38" stroke="rgba(74,159,212,0.2)" stroke-width="2" />
                        <!-- Neural net nodes -->
                        <circle cx="20" cy="25" r="4" fill="rgba(74,159,212,0.5)" />
                        <circle cx="20" cy="40" r="4" fill="rgba(74,159,212,0.5)" />
                        <circle cx="20" cy="55" r="4" fill="rgba(74,159,212,0.5)" />
                        <circle cx="40" cy="18" r="4" fill="rgba(0,151,184,0.7)" />
                        <circle cx="40" cy="32" r="4" fill="rgba(0,151,184,0.7)" />
                        <circle cx="40" cy="48" r="4" fill="rgba(0,151,184,0.7)" />
                        <circle cx="40" cy="62" r="4" fill="rgba(0,151,184,0.7)" />
                        <circle cx="60" cy="25" r="4" fill="rgba(16,183,127,0.6)" />
                        <circle cx="60" cy="40" r="4" fill="rgba(16,183,127,0.6)" />
                        <circle cx="60" cy="55" r="4" fill="rgba(16,183,127,0.6)" />
                        <!-- Connections layer 1→2 -->
                        <line x1="24" y1="25" x2="36" y2="18" stroke="rgba(74,159,212,0.25)" stroke-width="1" />
                        <line x1="24" y1="25" x2="36" y2="32" stroke="rgba(74,159,212,0.25)" stroke-width="1" />
                        <line x1="24" y1="40" x2="36" y2="32" stroke="rgba(74,159,212,0.25)" stroke-width="1" />
                        <line x1="24" y1="40" x2="36" y2="48" stroke="rgba(74,159,212,0.25)" stroke-width="1" />
                        <line x1="24" y1="55" x2="36" y2="48" stroke="rgba(74,159,212,0.25)" stroke-width="1" />
                        <line x1="24" y1="55" x2="36" y2="62" stroke="rgba(74,159,212,0.25)" stroke-width="1" />
                        <!-- Connections layer 2→3 -->
                        <line x1="44" y1="18" x2="56" y2="25" stroke="rgba(0,151,184,0.25)" stroke-width="1" />
                        <line x1="44" y1="32" x2="56" y2="25" stroke="rgba(0,151,184,0.25)" stroke-width="1" />
                        <line x1="44" y1="32" x2="56" y2="40" stroke="rgba(0,151,184,0.25)" stroke-width="1" />
                        <line x1="44" y1="48" x2="56" y2="40" stroke="rgba(0,151,184,0.25)" stroke-width="1" />
                        <line x1="44" y1="48" x2="56" y2="55" stroke="rgba(0,151,184,0.25)" stroke-width="1" />
                        <line x1="44" y1="62" x2="56" y2="55" stroke="rgba(0,151,184,0.25)" stroke-width="1" />
                    </svg>
                </div>
                <div>
                    <div class="trn-info-title">Random Forest Regressor</div>
                    <div class="trn-info-sub">TimeSeriesSplit + RandomizedSearchCV</div>
                </div>
            </div>

            <!-- Stats ringkas dari data training -->
            <div class="trn-quick-stats">
                <div class="trn-qs-item">
                    <span class="trn-qs-val" id="statRecord"><?= number_format($summary['total_record'] ?? 0) ?></span>
                    <span class="trn-qs-label">Record</span>
                </div>
                <div class="trn-qs-sep"></div>
                <div class="trn-qs-item">
                    <span class="trn-qs-val" id="statProduk"><?= number_format($summary['total_produk'] ?? 0) ?></span>
                    <span class="trn-qs-label">Produk</span>
                </div>
                <div class="trn-qs-sep"></div>
                <div class="trn-qs-item">
                    <span class="trn-qs-val" id="statFitur">16</span>
                    <span class="trn-qs-label">Fitur</span>
                </div>
                <div class="trn-qs-sep"></div>
                <div class="trn-qs-item">
                    <span class="trn-qs-val">5-fold</span>
                    <span class="trn-qs-label">CV Split</span>
                </div>
            </div>

            <!-- Fitur list -->
            <div class="trn-feature-list">
                <div class="trn-feature-label">Fitur yang digunakan:</div>
                <div class="trn-feature-tags">
                    <span class="trn-ftag trn-ftag-blue">bulan</span>
                    <span class="trn-ftag trn-ftag-blue">kuartal</span>
                    <span class="trn-ftag trn-ftag-blue">produk_encoded</span>
                    <span class="trn-ftag trn-ftag-blue">time_idx</span>
                    <span class="trn-ftag trn-ftag-cyan">harga_avg</span>
                    <span class="trn-ftag trn-ftag-cyan">harga_std</span>
                    <span class="trn-ftag trn-ftag-cyan">promo_avg</span>
                    <span class="trn-ftag trn-ftag-cyan">n_transaksi</span>
                    <span class="trn-ftag trn-ftag-green">qty_lag1</span>
                    <span class="trn-ftag trn-ftag-green">qty_lag2</span>
                    <span class="trn-ftag trn-ftag-green">qty_lag3</span>
                    <span class="trn-ftag trn-ftag-green">qty_roll3_mean</span>
                    <span class="trn-ftag trn-ftag-green">qty_roll3_std</span>
                    <span class="trn-ftag trn-ftag-green">qty_roll6_mean</span>
                    <span class="trn-ftag trn-ftag-yellow">bulan_sin</span> <!-- tambah -->
                    <span class="trn-ftag trn-ftag-yellow">bulan_cos</span>
                </div>
            </div>

            <!-- Tombol utama -->
            <button class="btn-trn-start" id="btnStartTraining">
                <span class="btn-trn-icon"><i class="bi bi-play-fill"></i></span>
                <span class="btn-trn-text">Mulai Training</span>
            </button>
        </div>

        <!-- Status & Progress -->
        <div class="card-mg trn-status-card" id="cardStatus">

            <!-- State: IDLE -->
            <div class="trn-state" id="stateIdle">
                <div class="trn-state-icon idle-icon">
                    <i class="bi bi-hourglass"></i>
                </div>
                <div class="trn-state-title">Siap Dilatih</div>
                <div class="trn-state-sub">Klik <strong>Mulai Training</strong> untuk memulai proses.</div>

                <?php if (!empty($lastStatus)): ?>
                    <div class="trn-last-run">
                        <div class="trn-lr-label">Training terakhir</div>
                        <div class="trn-lr-time"><?= $lastStatus['finished_at'] ?? $lastStatus['updated_at'] ?? '—' ?></div>
                        <?php if (!empty($lastStatus['akurasi'])): ?>
                            <div class="trn-lr-acc">
                                <i class="bi bi-bullseye"></i> Akurasi terakhir:
                                <strong style="color:var(--accent-green)"><?= number_format($lastStatus['akurasi'], 2) ?>%</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- State: RUNNING -->
            <div class="trn-state" id="stateRunning" style="display:none">
                <div class="trn-state-icon running-icon">
                    <div class="trn-spinner">
                        <div class="trn-spin-ring"></div>
                        <div class="trn-spin-ring trn-spin-ring-2"></div>
                        <i class="bi bi-cpu-fill trn-spin-core"></i>
                    </div>
                </div>
                <div class="trn-state-title">Sedang Melatih Model…</div>
                <div class="trn-state-sub" id="runningMsg">Memproses data training…</div>

                <!-- Progress bar animasi -->
                <div class="trn-progress-wrap">
                    <div class="trn-progress-bar">
                        <div class="trn-progress-fill" id="progressFill"></div>
                        <div class="trn-progress-shimmer"></div>
                    </div>
                    <div class="trn-progress-pct" id="progressPct">0%</div>
                </div>

                <!-- Steps -->
                <div class="trn-steps" id="trnSteps">
                    <div class="trn-step" id="step1"><i class="bi bi-circle-fill"></i> Membaca data dari database</div>
                    <div class="trn-step" id="step2"><i class="bi bi-circle-fill"></i> Preprocessing & encoding</div>
                    <div class="trn-step" id="step3"><i class="bi bi-circle-fill"></i> Melatih Random Forest</div>
                    <div class="trn-step" id="step4"><i class="bi bi-circle-fill"></i> Evaluasi model</div>
                    <div class="trn-step" id="step5"><i class="bi bi-circle-fill"></i> Menyimpan model & encoder</div>
                </div>

                <div class="trn-elapsed" id="elapsedWrap">
                    Berjalan selama <span id="elapsedTime">0</span> detik
                </div>
            </div>

            <!-- State: SUCCESS -->
            <div class="trn-state" id="stateSuccess" style="display:none">
                <div class="trn-state-icon success-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="trn-state-title">Training Selesai!</div>
                <div class="trn-state-sub" id="successMsg">Model berhasil dilatih dan disimpan.</div>

                <!-- Metrik hasil -->
                <div class="trn-metrics" id="trnMetrics">
                    <div class="trn-metric-item">
                        <span class="trn-metric-val" id="metAkurasi">—</span>
                        <span class="trn-metric-label">Akurasi</span>
                    </div>
                    <div class="trn-metric-item">
                        <span class="trn-metric-val" id="metR2">—</span>
                        <span class="trn-metric-label">R² Score</span>
                    </div>
                    <div class="trn-metric-item">
                        <span class="trn-metric-val" id="metMAE">—</span>
                        <span class="trn-metric-label">MAE</span>
                    </div>
                    <div class="trn-metric-item">
                        <span class="trn-metric-val" id="metRMSE">—</span>
                        <span class="trn-metric-label">RMSE</span>
                    </div>
                    <div class="trn-metric-item">
                        <span class="trn-metric-val" id="metMAPE">—</span>
                        <span class="trn-metric-label">MAPE</span>
                    </div>
                    <div class="trn-metric-item">
                        <span class="trn-metric-val" id="metSampel">—</span>
                        <span class="trn-metric-label">Sampel</span>
                    </div>
                </div>

                <div class="trn-success-actions">
                    <button class="btn-mg btn-primary-mg" onclick="window.location.href='<?= base_url('training/riwayat_model') ?>'">
                        <i class="bi bi-clock-history"></i> Lihat Riwayat Model
                    </button>
                    <button class="btn-mg btn-outline-mg" id="btnResetTraining">
                        <i class="bi bi-arrow-repeat"></i> Latih Ulang
                    </button>
                </div>
            </div>

            <!-- State: ERROR -->
            <div class="trn-state" id="stateError" style="display:none">
                <div class="trn-state-icon error-icon">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div class="trn-state-title">Training Gagal</div>
                <div class="trn-state-sub" id="errorMsg">Terjadi kesalahan saat proses training.</div>
                <div class="trn-error-detail" id="errorDetail" style="display:none"></div>
                <button class="btn-mg btn-danger-outline-mg" id="btnRetryTraining" style="margin-top:18px">
                    <i class="bi bi-arrow-repeat"></i> Coba Lagi
                </button>
            </div>
        </div>
    </div>

    <!-- ── Feature Importance (muncul setelah training sukses) ── -->
    <div class="card-mg" id="cardFeatImportance" style="display:none">
        <div class="mg-card-header">
            <div class="mg-card-title-group">
                <span class="section-title">Feature Importance</span>
                <span class="badge-mg badge-cyan">Random Forest</span>
            </div>
        </div>
        <div class="fi-bars" id="fiBars">
            <!-- diisi JS -->
        </div>
    </div>

    <!-- ── Log Terminal ── -->
    <div class="card-mg" id="cardLog">
        <div class="mg-card-header">
            <div class="mg-card-title-group">
                <span class="section-title">Log Training</span>
                <span class="trn-log-dot" id="logDot"></span>
            </div>
            <button class="btn-mg btn-outline-mg btn-sm-mg" id="btnClearLog">
                <i class="bi bi-trash3"></i> Clear
            </button>
        </div>
        <div class="trn-terminal" id="trnTerminal">
            <div class="trn-terminal-line trn-line-muted">[ Mi Store Training Console ]</div>
            <div class="trn-terminal-line trn-line-muted">──────────────────────────────</div>
            <div class="trn-terminal-line">Siap. Klik <span class="trn-hl">Mulai Training</span> untuk memulai.</div>
        </div>
    </div>

</div><!-- /content-wrapper-inner -->


<!-- ═══════════════════════════════════════════════════════════
     STYLES
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
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-outline-mg {
        background: var(--card-bg-alt);
        color: var(--text-muted);
        border: 1px solid var(--surface-border);
    }

    .btn-outline-mg:hover {
        border-color: var(--mg-light);
        color: var(--mg-light);
    }

    .btn-sm-mg {
        padding: 5px 12px !important;
        font-size: 12px !important;
    }

    .btn-danger-outline-mg {
        background: rgba(229, 62, 62, 0.08);
        color: var(--accent-red);
        border: 1px solid rgba(229, 62, 62, 0.25);
    }

    .btn-danger-outline-mg:hover {
        background: rgba(229, 62, 62, 0.18);
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

    /* ── Top grid ── */
    .trn-top-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        align-items: start;
    }

    @media(max-width:860px) {
        .trn-top-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ── Info card ── */
    .trn-info-card {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .trn-info-header {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .trn-brain-icon {
        width: 80px;
        height: 80px;
        flex-shrink: 0;
    }

    .trn-brain-icon svg {
        width: 100%;
        height: 100%;
    }

    .trn-info-title {
        font-family: var(--font-display);
        font-size: 16px;
        font-weight: 700;
        color: var(--text-ink);
    }

    .trn-info-sub {
        font-size: 12px;
        color: var(--accent-cyan);
        font-weight: 500;
        margin-top: 2px;
    }

    /* Quick stats */
    .trn-quick-stats {
        display: flex;
        align-items: center;
        gap: 0;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-md);
        overflow: hidden;
    }

    .trn-qs-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 12px 8px;
        gap: 4px;
    }

    .trn-qs-val {
        font-family: var(--font-display);
        font-size: 20px;
        font-weight: 700;
        color: var(--text-ink);
    }

    .trn-qs-label {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .trn-qs-sep {
        width: 1px;
        height: 40px;
        background: var(--surface-border);
    }

    /* Feature tags */
    .trn-feature-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .trn-feature-label {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .trn-feature-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .trn-ftag {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        font-family: 'Courier New', monospace;
    }

    .trn-ftag-blue {
        background: rgba(46, 109, 164, 0.12);
        color: var(--mg-light);
        border: 1px solid rgba(46, 109, 164, 0.2);
    }

    .trn-ftag-cyan {
        background: rgba(0, 151, 184, 0.1);
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 151, 184, 0.2);
    }

    .trn-ftag-green {
        background: rgba(16, 183, 127, 0.1);
        color: var(--accent-green);
        border: 1px solid rgba(16, 183, 127, 0.2);
    }

    .trn-ftag-yellow {
        background: rgba(212, 160, 23, 0.1);
        color: var(--accent-yellow);
        border: 1px solid rgba(212, 160, 23, 0.2);
    }

    /* Start button */
    .btn-trn-start {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 16px 24px;
        border-radius: var(--radius-md);
        border: none;
        cursor: pointer;
        background: linear-gradient(135deg, var(--mg-glow), var(--accent-cyan));
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        font-family: var(--font-display);
        box-shadow: 0 6px 24px rgba(74, 159, 212, 0.3);
        transition: all .22s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-trn-start::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.12), transparent);
        opacity: 0;
        transition: .22s;
    }

    .btn-trn-start:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 32px rgba(74, 159, 212, 0.4);
    }

    .btn-trn-start:hover::before {
        opacity: 1;
    }

    .btn-trn-start:active {
        transform: translateY(0);
    }

    .btn-trn-start:disabled {
        opacity: .5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-trn-icon {
        font-size: 20px;
        line-height: 1;
    }

    .btn-trn-text {
        line-height: 1;
    }

    .trn-warning-note {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 10px 14px;
        background: rgba(212, 160, 23, 0.06);
        border: 1px solid rgba(212, 160, 23, 0.18);
        border-radius: var(--radius-sm);
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.6;
    }

    .trn-warning-note>i {
        color: var(--accent-yellow);
        font-size: 14px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .trn-warning-note strong {
        color: var(--accent-yellow);
    }

    /* ── Status card ── */
    .trn-status-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 340px;
        padding: 32px 24px !important;
        position: relative;
    }

    .trn-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        width: 100%;
        text-align: center;
        animation: fadeSlideIn .3s ease;
    }

    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(10px)
        }

        to {
            opacity: 1;
            transform: none
        }
    }

    .trn-state-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin-bottom: 4px;
    }

    .idle-icon {
        background: rgba(74, 159, 212, 0.1);
        color: var(--mg-light);
    }

    .success-icon {
        background: rgba(16, 183, 127, 0.12);
        color: var(--accent-green);
    }

    .error-icon {
        background: rgba(229, 62, 62, 0.12);
        color: var(--accent-red);
    }

    .running-icon {
        background: transparent;
    }

    .trn-state-title {
        font-family: var(--font-display);
        font-size: 17px;
        font-weight: 700;
        color: var(--text-ink);
    }

    .trn-state-sub {
        font-size: 13px;
        color: var(--text-muted);
        line-height: 1.6;
        max-width: 280px;
    }

    /* Last run */
    .trn-last-run {
        margin-top: 8px;
        padding: 12px 18px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-sm);
        text-align: center;
    }

    .trn-lr-label {
        font-size: 11px;
        color: var(--text-placeholder);
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 4px;
    }

    .trn-lr-time {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-ink);
    }

    .trn-lr-acc {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    /* Spinner */
    .trn-spinner {
        position: relative;
        width: 72px;
        height: 72px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .trn-spin-ring {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 2.5px solid transparent;
        border-top-color: var(--accent-cyan);
        border-right-color: rgba(74, 159, 212, 0.3);
        animation: spinRing 1.1s linear infinite;
    }

    .trn-spin-ring-2 {
        inset: 10px;
        border-top-color: var(--accent-green);
        border-right-color: rgba(16, 183, 127, 0.3);
        animation-duration: 0.8s;
        animation-direction: reverse;
    }

    @keyframes spinRing {
        to {
            transform: rotate(360deg);
        }
    }

    .trn-spin-core {
        font-size: 22px;
        color: var(--mg-light);
        position: relative;
        z-index: 1;
        animation: pulse 1.4s ease-in-out infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1
        }

        50% {
            opacity: .5
        }
    }

    /* Progress */
    .trn-progress-wrap {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 4px;
    }

    .trn-progress-bar {
        flex: 1;
        height: 8px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: 999px;
        overflow: hidden;
        position: relative;
    }

    .trn-progress-fill {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, var(--mg-glow), var(--accent-cyan));
        border-radius: 999px;
        transition: width .6s ease;
    }

    .trn-progress-shimmer {
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.2) 50%, transparent 100%);
        animation: shimmer 1.5s infinite;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%)
        }

        100% {
            transform: translateX(300%)
        }
    }

    .trn-progress-pct {
        font-size: 12px;
        font-weight: 700;
        color: var(--accent-cyan);
        min-width: 32px;
        text-align: right;
    }

    /* Steps */
    .trn-steps {
        display: flex;
        flex-direction: column;
        gap: 6px;
        width: 100%;
        text-align: left;
        margin-top: 4px;
    }

    .trn-step {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 12px;
        color: var(--text-placeholder);
        padding: 6px 10px;
        border-radius: 6px;
        transition: all .3s ease;
    }

    .trn-step i {
        font-size: 8px;
        flex-shrink: 0;
    }

    .trn-step.step-done {
        color: var(--accent-green);
        background: rgba(16, 183, 127, 0.06);
    }

    .trn-step.step-done i {
        color: var(--accent-green);
        font-size: 11px;
    }

    .trn-step.step-active {
        color: var(--accent-cyan);
        background: rgba(74, 159, 212, 0.07);
        font-weight: 600;
    }

    .trn-step.step-active i {
        color: var(--accent-cyan);
        animation: pulse 1s infinite;
    }

    .trn-elapsed {
        font-size: 11.5px;
        color: var(--text-placeholder);
        margin-top: 4px;
    }

    /* Metrics */
    .trn-metrics {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        width: 100%;
        margin-top: 4px;
    }

    .trn-metric-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 12px 8px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-sm);
    }

    .trn-metric-val {
        font-family: var(--font-display);
        font-size: 18px;
        font-weight: 800;
        color: var(--text-ink);
    }

    .trn-metric-label {
        font-size: 10.5px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .trn-success-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 8px;
    }

    /* Error detail */
    .trn-error-detail {
        width: 100%;
        background: rgba(229, 62, 62, 0.06);
        border: 1px solid rgba(229, 62, 62, 0.2);
        border-radius: var(--radius-sm);
        padding: 10px 14px;
        font-size: 12px;
        color: var(--accent-red);
        font-family: 'Courier New', monospace;
        text-align: left;
        line-height: 1.6;
        max-height: 120px;
        overflow-y: auto;
    }

    /* ── Feature Importance ── */
    .fi-bars {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .fi-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .fi-name {
        font-size: 12px;
        font-family: 'Courier New', monospace;
        color: var(--text-muted);
        min-width: 140px;
        text-align: right;
    }

    .fi-bar-wrap {
        flex: 1;
        height: 18px;
        background: var(--card-bg-alt);
        border-radius: 999px;
        overflow: hidden;
        border: 1px solid var(--surface-border);
    }

    .fi-bar-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--mg-glow), var(--accent-cyan));
        transition: width 1s ease;
    }

    .fi-pct {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--accent-cyan);
        min-width: 40px;
    }

    /* ── Terminal log ── */
    .trn-terminal {
        background: #0d1117;
        border: 1px solid rgba(74, 159, 212, 0.18);
        border-radius: var(--radius-sm);
        padding: 16px;
        font-family: 'Courier New', monospace;
        font-size: 12.5px;
        line-height: 1.8;
        max-height: 220px;
        overflow-y: auto;
        color: #c9d1d9;
        box-shadow: inset 0 2px 12px rgba(0, 0, 0, 0.3);
    }

    .trn-terminal-line {
        animation: termLine .15s ease;
    }

    @keyframes termLine {
        from {
            opacity: 0;
            transform: translateX(-8px)
        }

        to {
            opacity: 1;
            transform: none
        }
    }

    .trn-line-ts {
        color: #58a6ff;
    }

    /* biru terang — timestamp */
    .trn-line-info {
        color: #c9d1d9;
    }

    /* putih soft — info */
    .trn-line-success {
        color: #3fb950;
        font-weight: 600;
    }

    /* hijau terang — sukses */
    .trn-line-error {
        color: #f85149;
        font-weight: 600;
    }

    /* merah terang — error */
    .trn-line-muted {
        color: #484f58;
    }

    /* abu gelap — komentar */
    .trn-hl {
        color: #79c0ff;
        font-weight: 700;
    }

    /* biru accent */

    .trn-log-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--text-placeholder);
        display: inline-block;
        margin-left: 8px;
    }

    .trn-log-dot.dot-live {
        background: var(--accent-green);
        animation: blink .9s infinite;
    }

    @keyframes blink {

        0%,
        100% {
            opacity: 1
        }

        50% {
            opacity: .2
        }
    }

    /* ── Card header (reuse from training/index) ── */
    .mg-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 18px;
    }

    .mg-card-title-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .badge-cyan {
        background: rgba(0, 151, 184, 0.12);
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 151, 184, 0.2);
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
    }

    /* ── Pipeline ── */
    .trn-pipeline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 8px 0;
    }

    .trn-pipe-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        flex: 1;
        min-width: 110px;
        text-align: center;
    }

    .trn-pipe-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .trn-pipe-label {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--text-ink);
    }

    .trn-pipe-desc {
        font-size: 11px;
        color: var(--text-muted);
        line-height: 1.5;
    }

    .trn-pipe-arrow {
        color: var(--text-placeholder);
        font-size: 18px;
        flex-shrink: 0;
    }

    @media(max-width:700px) {
        .trn-pipe-arrow {
            display: none;
        }

        .trn-pipe-step {
            min-width: 80px;
        }
    }
</style>


<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════ -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        // ── Config ───────────────────────────────────────────────────────────────
        const POLL_INTERVAL = 3000; // ms polling status
        const STEP_INTERVALS = [{
                step: 'step1',
                at: 2,
                pct: 15,
                msg: 'Membaca data dari database…'
            },
            {
                step: 'step2',
                at: 6,
                pct: 30,
                msg: 'Preprocessing & encoding fitur…'
            },
            {
                step: 'step3',
                at: 10,
                pct: 55,
                msg: 'Melatih Random Forest (ini butuh waktu)…'
            },
            {
                step: 'step4',
                at: 22,
                pct: 80,
                msg: 'Evaluasi model (MAE, RMSE, R², MAPE)…'
            },
            {
                step: 'step5',
                at: 28,
                pct: 95,
                msg: 'Menyimpan model & encoder ke disk…'
            },
        ];

        // ── State ─────────────────────────────────────────────────────────────────
        let pollTimer = null;
        let elapsedTimer = null;
        let startTime = null;
        let currentState = 'idle'; // idle | running | success | error

        // ── DOM refs ──────────────────────────────────────────────────────────────
        const $ = id => document.getElementById(id);
        const btnStart = $('btnStartTraining');
        const btnReset = $('btnResetTraining');
        const btnRetry = $('btnRetryTraining');

        // ── Utilities ─────────────────────────────────────────────────────────────
        function showState(name) {
            ['stateIdle', 'stateRunning', 'stateSuccess', 'stateError'].forEach(id => {
                const el = $(id);
                if (el) el.style.display = id === 'state' + capitalize(name) ? '' : 'none';
            });
            currentState = name;
        }

        function capitalize(s) {
            return s.charAt(0).toUpperCase() + s.slice(1);
        }

        function setProgress(pct, msg) {
            const fill = $('progressFill');
            const pctEl = $('progressPct');
            const msgEl = $('runningMsg');
            if (fill) fill.style.width = pct + '%';
            if (pctEl) pctEl.textContent = Math.round(pct) + '%';
            if (msgEl && msg) msgEl.textContent = msg;
        }

        function activateStep(stepId) {
            // done semua sebelumnya
            STEP_INTERVALS.forEach(s => {
                const el = $(s.step);
                if (!el) return;
                if (s.step === stepId) {
                    el.className = 'trn-step step-active';
                    const originalText = el.textContent.trim()
                        .replace(/^\S+\s*/, ''); // buang icon lama jika ada
                    el.innerHTML = '<i class="bi bi-arrow-right-circle-fill"></i> ' + originalText;
                } else if (el.classList.contains('step-active') || el.classList.contains('step-done')) {
                    el.className = 'trn-step step-done';
                    el.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + el.textContent.trim();
                }
            });
        }

        function addLog(text, type = 'info') {
            const terminal = $('trnTerminal');
            if (!terminal) return;
            const ts = new Date().toLocaleTimeString('id-ID', {
                hour12: false
            });
            const line = document.createElement('div');
            line.className = 'trn-terminal-line trn-line-' + type;
            line.innerHTML = `<span class="trn-line-ts">[${ts}]</span> ${text}`;
            terminal.appendChild(line);
            terminal.scrollTop = terminal.scrollHeight;
        }

        function startElapsed() {
            startTime = Date.now();
            elapsedTimer = setInterval(() => {
                const sec = Math.floor((Date.now() - startTime) / 1000);
                const el = $('elapsedTime');
                if (el) el.textContent = sec;

                // Simulasi step berdasarkan waktu
                STEP_INTERVALS.forEach(s => {
                    const stepEl = $(s.step);
                    if (!stepEl) return;
                    if (sec >= s.at && !stepEl.classList.contains('step-done') && !stepEl.classList.contains('step-active')) {
                        activateStep(s.step);
                        setProgress(s.pct, s.msg); // ← kirim msg sesuai step
                    }
                });
            }, 1000);
        }

        function stopElapsed() {
            clearInterval(elapsedTimer);
            elapsedTimer = null;
        }

        function startPolling() {
            stopPolling();
            pollTimer = setInterval(pollStatus, POLL_INTERVAL);
        }

        function stopPolling() {
            clearInterval(pollTimer);
            pollTimer = null;
        }

        // ── Polling status ────────────────────────────────────────────────────────
        function pollStatus() {
            fetch(BASE_URL + 'training/status', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => handleStatus(data))
                .catch(() => {
                    /* silent fail polling */
                });
        }

        function handleStatus(data) {
            if (!data || !data.status) return;

            if (data.status === 'running') {
                if (currentState !== 'running') enterRunning();
            } else if (data.status === 'success') {
                if (currentState === 'running') enterSuccess(data);
            } else if (data.status === 'error') {
                if (currentState === 'running') enterError(data.message || 'Terjadi kesalahan.');
            }
        }

        // ── State transitions ─────────────────────────────────────────────────────
        function enterRunning() {
            showState('running');
            btnStart.disabled = true;
            const logDot = $('logDot');
            if (logDot) logDot.className = 'trn-log-dot dot-live';

            // Reset steps visual
            STEP_INTERVALS.forEach(s => {
                const el = $(s.step);
                if (el) el.className = 'trn-step';
            });
            setProgress(5, STEP_INTERVALS[0].msg);
            activateStep('step1');
            startElapsed();
            startPolling();
            addLog('Proses training dimulai…', 'info');
        }

        function enterSuccess(data) {
            stopPolling();
            stopElapsed();
            showState('success');
            btnStart.disabled = false;
            const logDot = $('logDot');
            if (logDot) logDot.className = 'trn-log-dot';

            // Mark semua steps done
            STEP_INTERVALS.forEach(s => {
                const el = $(s.step);
                if (el) {
                    el.className = 'trn-step step-done';
                    el.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + el.textContent.trim();
                }
            });
            setProgress(100);

            // ── Isi metrik (flat keys dari train_status.json) ──────────────────
            const akurasi = data.akurasi ?? data.akurasi_test;
            const mae = data.mae ?? data.mae_test;
            const rmse = data.rmse ?? data.rmse_test;
            const r2 = data.r2 ?? data.r2_test;
            const mape = data.mape ?? data.mape_test;

            if ($('metAkurasi') && akurasi !== undefined && akurasi !== null) {
                $('metAkurasi').textContent = Number(akurasi).toFixed(2) + '%';
                $('metAkurasi').style.color = Number(akurasi) >= 80 ?
                    'var(--accent-green)' : 'var(--accent-yellow)';
            }
            if ($('metR2') && r2 != null) $('metR2').textContent = Number(r2).toFixed(4);
            if ($('metMAE') && mae != null) $('metMAE').textContent = Number(mae).toFixed(4);
            if ($('metRMSE') && rmse != null) $('metRMSE').textContent = Number(rmse).toFixed(4);
            if ($('metMAPE') && mape != null) $('metMAPE').textContent = Number(mape).toFixed(2) + '%';
            if ($('metSampel') && data.total_sampel != null)
                $('metSampel').textContent = Number(data.total_sampel).toLocaleString('id-ID');

            // Tampilkan info train vs test di subtitle
            const msgEl = $('successMsg');
            if (msgEl && data.akurasi_train != null && data.akurasi_test != null) {
                msgEl.innerHTML =
                    'Model berhasil dilatih &amp; disimpan. ' +
                    '<br><small style="font-size:11px;opacity:.8">' +
                    '<span style="color:#58a6ff">Train: ' + Number(data.akurasi_train).toFixed(2) + '%</span>' +
                    ' &nbsp;·&nbsp; ' +
                    '<span style="color:#3fb950">Test: ' + Number(data.akurasi_test).toFixed(2) + '%</span>' +
                    '</small>';
            }

            // Feature importance chart
            if (data.feature_importance) {
                renderFeatImportance(data.feature_importance);
            }

            const logAkTrain = data.akurasi_train != null ? Number(data.akurasi_train).toFixed(2) + '%' : '—';
            const logAkTest = data.akurasi_test != null ? Number(data.akurasi_test).toFixed(2) + '%' : '—';
            addLog('✓ Training selesai!  Train: ' + logAkTrain + '  |  Test: ' + logAkTest, 'success');
            if (data.mae != null)
                addLog('  MAE=' + Number(data.mae).toFixed(4) +
                    '  RMSE=' + Number(data.rmse).toFixed(4) +
                    '  R²=' + Number(data.r2).toFixed(4) +
                    '  MAPE=' + Number(data.mape).toFixed(2) + '%', 'info');
        }

        function enterError(msg) {
            stopPolling();
            stopElapsed();
            showState('error');
            btnStart.disabled = false;
            const logDot = $('logDot');
            if (logDot) logDot.className = 'trn-log-dot';

            const errMsg = $('errorMsg');
            if (errMsg) errMsg.textContent = msg || 'Terjadi kesalahan saat proses training.';

            const errDetail = $('errorDetail');
            if (errDetail && msg && msg.length > 80) {
                errDetail.style.display = '';
                errDetail.textContent = msg;
            }

            addLog('ERROR: ' + msg, 'error');
        }

        // ── Feature Importance render ─────────────────────────────────────────────
        function renderFeatImportance(fi) {
            const card = $('cardFeatImportance');
            const bars = $('fiBars');
            if (!card || !bars) return;

            card.style.display = '';
            bars.innerHTML = '';

            const entries = Object.entries(fi).sort((a, b) => b[1] - a[1]).slice(0, 10);
            const max = entries[0][1];

            entries.forEach(([name, val]) => {
                const pct = (val / max * 100).toFixed(1);
                const row = document.createElement('div');
                row.className = 'fi-row';
                row.innerHTML = `
                <span class="fi-name">${name}</span>
                <div class="fi-bar-wrap">
                    <div class="fi-bar-fill" style="width:0%" data-target="${pct}%"></div>
                </div>
                <span class="fi-pct">${(val*100).toFixed(1)}%</span>
            `;
                bars.appendChild(row);
            });

            // Animate bars after render
            requestAnimationFrame(() => {
                bars.querySelectorAll('.fi-bar-fill').forEach(el => {
                    el.style.width = el.dataset.target;
                });
            });
        }

        // ── Button handlers ───────────────────────────────────────────────────────
        btnStart?.addEventListener('click', function() {
            if (currentState === 'running') return;

            Swal.fire({
                title: 'Mulai Training Model?',
                html: 'Model Random Forest akan dilatih ulang menggunakan <b><?= number_format($summary['total_record'] ?? 0) ?> record</b> data training.<br><br>Proses membutuhkan beberapa menit.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2e6da4',
                cancelButtonColor: '#243B55',
                confirmButtonText: '<i class="bi bi-play-fill"></i> Ya, Mulai!',
                cancelButtonText: 'Batal',
            }).then(r => {
                if (!r.isConfirmed) return;

                fetch(BASE_URL + 'training/proses', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        }),
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'error') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message
                            });
                            return;
                        }
                        if (data.status === 'warning') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Perhatian',
                                text: data.message,
                                confirmButtonColor: '#2e6da4'
                            });
                            // Tetap tampilkan running kalau memang sudah running
                            if (currentState !== 'running') enterRunning();
                            return;
                        }
                        // success → masuk running
                        enterRunning();
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menghubungi server.'
                        });
                    });
            });
        });

        btnReset?.addEventListener('click', () => {
            showState('idle');
            const logDot = $('logDot');
            if (logDot) logDot.className = 'trn-log-dot';
            $('cardFeatImportance') && ($('cardFeatImportance').style.display = 'none');
        });

        btnRetry?.addEventListener('click', () => {
            showState('idle');
        });

        $('btnClearLog')?.addEventListener('click', () => {
            const t = $('trnTerminal');
            if (t) t.innerHTML = '<div class="trn-terminal-line trn-line-muted">Log dibersihkan.</div>';
        });

        // ── Init: cek apakah ada training yang sedang berjalan ────────────────────
        fetch(BASE_URL + 'training/status', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (!data) return;
                if (data.status === 'running') {
                    enterRunning();
                    addLog('Ditemukan proses training yang sedang berjalan…', 'info');
                } else if (data.status === 'success' && data.finished_at) {
                    // Ada hasil sebelumnya — tampilkan saja di idle state (sudah ada di lastStatus PHP)
                }
            })
            .catch(() => {});
    });
</script>