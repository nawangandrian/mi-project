<?php

/**
 * View  : pages/import/index.php
 * Modul : Impor Data Excel — Mi Store Kudus
 */
?>

<style>
    /* ══════════════════════════════════════════════════════════════
   IMPORT PAGE  — Konsisten dengan design system training/proses
   ══════════════════════════════════════════════════════════════ */

    .import-wrapper {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* ── Page Header ── */
    .mg-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }

    .page-title {
        font-family: var(--font-display);
        font-size: 22px;
        font-weight: 800;
        color: var(--text-ink);
        margin: 0;
    }

    .page-subtitle {
        font-size: 13px;
        color: var(--text-muted);
        margin: 0;
        margin-top: 3px;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* ── Quick Nav ── */
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

    /* ── Card base ── */
    .card-mg {
        background: var(--card-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-md);
        padding: 24px;
    }

    /* ── Stepper ── */
    .imp-stepper {
        display: flex;
        align-items: center;
        gap: 0;
        padding: 0;
    }

    .imp-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        flex: 1;
        position: relative;
    }

    .imp-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 18px;
        left: 55%;
        width: 90%;
        height: 2px;
        background: var(--surface-border);
        z-index: 0;
        transition: background .4s;
    }

    .imp-step.step-done:not(:last-child)::after {
        background: var(--accent-green);
    }

    .imp-step.step-active:not(:last-child)::after {
        background: var(--surface-border);
    }

    .imp-step-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        border: 2px solid var(--surface-border);
        background: var(--card-bg-alt);
        color: var(--text-muted);
        z-index: 1;
        transition: all .3s;
        font-family: var(--font-display);
    }

    .imp-step.step-active .imp-step-circle {
        border-color: var(--mg-light);
        background: var(--mg-glow);
        color: #fff;
        box-shadow: 0 0 0 4px rgba(74, 159, 212, .2);
    }

    .imp-step.step-done .imp-step-circle {
        border-color: var(--accent-green);
        background: rgba(16, 183, 127, .15);
        color: var(--accent-green);
    }

    .imp-step-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-placeholder);
        text-align: center;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .imp-step.step-active .imp-step-label {
        color: var(--mg-light);
    }

    .imp-step.step-done .imp-step-label {
        color: var(--accent-green);
    }

    /* ── Panels ── */
    .imp-panel {
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

    /* ── Drop Zone ── */
    .imp-dropzone {
        position: relative;
        border: 2px dashed var(--surface-border);
        border-radius: var(--radius-md);
        padding: 3.5rem 2rem;
        text-align: center;
        cursor: pointer;
        transition: all .25s ease;
        background: var(--card-bg-alt);
        overflow: hidden;
    }

    .imp-dropzone::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at center, rgba(74, 159, 212, 0.04) 0%, transparent 70%);
        opacity: 0;
        transition: opacity .3s;
    }

    .imp-dropzone:hover,
    .imp-dropzone.drag-over {
        border-color: var(--mg-glow);
        background: rgba(74, 159, 212, .05);
    }

    .imp-dropzone:hover::before,
    .imp-dropzone.drag-over::before {
        opacity: 1;
    }

    .imp-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .imp-dz-icon {
        font-size: 3.2rem;
        color: var(--mg-light);
        margin-bottom: 1rem;
        display: block;
        transition: transform .25s;
        opacity: .7;
    }

    .imp-dropzone:hover .imp-dz-icon {
        transform: translateY(-4px);
        opacity: 1;
    }

    .imp-dz-title {
        font-family: var(--font-display);
        font-size: 15px;
        font-weight: 700;
        color: var(--text-ink);
        margin-bottom: 4px;
    }

    .imp-dz-sub {
        font-size: 12px;
        color: var(--text-muted);
    }

    .imp-dropzone.has-file {
        border-color: var(--accent-green);
        background: rgba(16, 183, 127, .04);
    }

    .imp-dropzone.has-file .imp-dz-icon {
        color: var(--accent-green);
        opacity: 1;
    }

    /* File info bar */
    .imp-file-bar {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-sm);
        animation: fadeSlideIn .25s ease;
    }

    .imp-file-icon {
        font-size: 28px;
        color: var(--accent-green);
        flex-shrink: 0;
    }

    .imp-file-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-ink);
    }

    .imp-file-size {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    /* ── Sheet Grid ── */
    .imp-sheet-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .imp-sheet-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 14px;
        border: 1.5px solid var(--surface-border);
        border-radius: 8px;
        cursor: pointer;
        font-size: 12.5px;
        font-weight: 600;
        font-family: 'Courier New', monospace;
        transition: all .18s;
        user-select: none;
        background: var(--card-bg-alt);
        color: var(--text-muted);
    }

    .imp-sheet-chip:hover {
        border-color: var(--mg-light);
        color: var(--mg-light);
    }

    .imp-sheet-chip.selected {
        border-color: var(--mg-glow);
        background: rgba(74, 159, 212, .12);
        color: var(--mg-light);
    }

    .imp-sheet-chip input[type="checkbox"] {
        display: none;
    }

    /* ── Option Pills ── */
    .imp-option-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 8px;
    }

    .imp-option-pill {
        padding: 7px 16px;
        border: 1.5px solid var(--surface-border);
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all .18s;
        background: transparent;
        color: var(--text-muted);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .imp-option-pill:hover {
        border-color: var(--mg-light);
        color: var(--mg-light);
    }

    .imp-option-pill.active {
        border-color: var(--mg-glow);
        background: linear-gradient(135deg, var(--mg-glow), var(--accent-cyan));
        color: #fff;
        box-shadow: 0 3px 12px rgba(74, 159, 212, .25);
    }

    .imp-option-pill.active-warn {
        border-color: var(--accent-red);
        background: rgba(229, 62, 62, .15);
        color: var(--accent-red);
    }

    /* ── Overwrite alert ── */
    .imp-alert-warn {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 11px 15px;
        background: rgba(212, 160, 23, .06);
        border: 1px solid rgba(212, 160, 23, .2);
        border-radius: var(--radius-sm);
        font-size: 12.5px;
        color: var(--text-muted);
        line-height: 1.6;
        animation: fadeSlideIn .2s ease;
    }

    .imp-alert-warn>i {
        color: var(--accent-yellow);
        font-size: 15px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .imp-alert-warn strong {
        color: var(--accent-yellow);
    }

    /* ── Progress ── */
    .imp-progress-wrap {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .imp-progress-bar {
        flex: 1;
        height: 8px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: 999px;
        overflow: hidden;
        position: relative;
    }

    .imp-progress-fill {
        height: 100%;
        width: 30%;
        background: linear-gradient(90deg, var(--mg-glow), var(--accent-cyan));
        border-radius: 999px;
        transition: width .5s ease;
        position: relative;
    }

    .imp-progress-fill::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .25), transparent);
        animation: shimmer 1.4s infinite;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%)
        }

        100% {
            transform: translateX(300%)
        }
    }

    /* ── Processing steps in step 4 ── */
    .imp-proc-steps {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-top: 4px;
    }

    .imp-proc-step {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12.5px;
        color: var(--text-placeholder);
        padding: 7px 12px;
        border-radius: 6px;
        transition: all .3s;
    }

    .imp-proc-step i {
        font-size: 9px;
        flex-shrink: 0;
    }

    .imp-proc-step.ps-done {
        color: var(--accent-green);
        background: rgba(16, 183, 127, .06);
    }

    .imp-proc-step.ps-done i {
        color: var(--accent-green);
        font-size: 12px;
    }

    .imp-proc-step.ps-active {
        color: var(--accent-cyan);
        background: rgba(74, 159, 212, .07);
        font-weight: 600;
    }

    .imp-proc-step.ps-active i {
        color: var(--accent-cyan);
        animation: pulse 1s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1
        }

        50% {
            opacity: .4
        }
    }

    /* ── Spinner ── */
    .imp-spinner {
        position: relative;
        width: 72px;
        height: 72px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
    }

    .imp-spin-ring {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 2.5px solid transparent;
        border-top-color: var(--accent-cyan);
        border-right-color: rgba(74, 159, 212, .25);
        animation: spinRing 1.1s linear infinite;
    }

    .imp-spin-ring-2 {
        inset: 10px;
        border-top-color: var(--accent-green);
        border-right-color: rgba(16, 183, 127, .25);
        animation-duration: .8s;
        animation-direction: reverse;
    }

    @keyframes spinRing {
        to {
            transform: rotate(360deg)
        }
    }

    .imp-spin-core {
        font-size: 22px;
        color: var(--mg-light);
        position: relative;
        z-index: 1;
        animation: pulse 1.4s ease-in-out infinite;
    }

    /* ── Result stats ── */
    .imp-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 12px;
        margin: 20px 0;
    }

    .imp-stat-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 16px 10px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-sm);
        gap: 4px;
        animation: fadeSlideIn .4s ease both;
    }

    .imp-stat-num {
        font-family: var(--font-display);
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
    }

    .imp-stat-label {
        font-size: 11px;
        color: var(--text-muted);
        font-weight: 600;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    /* Sheet badges */
    .imp-sheet-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        font-family: 'Courier New', monospace;
        background: rgba(74, 159, 212, .12);
        color: var(--mg-light);
        margin: 3px;
        border: 1px solid rgba(74, 159, 212, .2);
    }

    .imp-sheet-badge.fail {
        background: rgba(229, 62, 62, .12);
        color: var(--accent-red);
        border-color: rgba(229, 62, 62, .2);
    }

    /* ── Button base ── */
    .btn-mg {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 700;
        font-family: var(--font-display);
        border: none;
        cursor: pointer;
        transition: all .2s ease;
        text-decoration: none;
    }

    .btn-primary-mg {
        background: linear-gradient(135deg, var(--mg-glow), var(--accent-cyan));
        color: #fff;
        box-shadow: 0 4px 16px rgba(74, 159, 212, .28);
    }

    .btn-primary-mg:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(74, 159, 212, .35);
        color: #fff;
    }

    .btn-primary-mg:disabled {
        opacity: .45;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-outline-mg {
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        color: var(--text-muted);
    }

    .btn-outline-mg:hover {
        border-color: var(--mg-light);
        color: var(--mg-light);
    }

    .btn-ghost-mg {
        background: transparent;
        border: 1px solid var(--surface-border);
        color: var(--text-muted);
        padding: 8px 16px;
        font-size: 12.5px;
    }

    .btn-ghost-mg:hover {
        color: var(--text-ink);
        border-color: var(--text-muted);
    }

    .btn-success-mg {
        background: rgba(16, 183, 127, .15);
        border: 1px solid rgba(16, 183, 127, .3);
        color: var(--accent-green);
    }

    .btn-success-mg:hover {
        background: rgba(16, 183, 127, .25);
        color: var(--accent-green);
    }

    /* Result icon */
    .imp-result-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        flex-shrink: 0;
    }

    .imp-result-icon.ok {
        background: rgba(16, 183, 127, .12);
        color: var(--accent-green);
    }

    .imp-result-icon.fail {
        background: rgba(229, 62, 62, .12);
        color: var(--accent-red);
    }

    /* Section label */
    .imp-section-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-placeholder);
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 8px;
    }

    /* Desc text */
    .imp-option-desc {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 7px;
        line-height: 1.6;
        min-height: 36px;
    }

    /* card-mg header reuse */
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

    .section-title {
        font-family: var(--font-display);
        font-size: 15px;
        font-weight: 700;
        color: var(--text-ink);
    }

    .badge-mg {
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
    }

    .badge-cyan {
        background: rgba(0, 151, 184, .12);
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 151, 184, .2);
    }

    .badge-green {
        background: rgba(16, 183, 127, .12);
        color: var(--accent-green);
        border: 1px solid rgba(16, 183, 127, .2);
    }

    .ml-badge {
        background: rgba(74, 159, 212, .15);
        color: var(--mg-light);
        border: 1px solid rgba(74, 159, 212, .25);
        font-size: 10px;
        padding: 1px 7px;
    }

    /* Responsive */
    @media (max-width: 640px) {
        .imp-stat-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .imp-stepper {
            padding: 0;
        }

        .imp-step-label {
            display: none;
        }
    }

    .hidden-step {
        display: none !important;
    }

    /* ── Footer action buttons ── */
    .imp-action-footer {
        margin-top: 28px;
        padding-top: 8px;
    }

    /* ── Result action buttons ── */
    .imp-result-actions {
        margin-top: 24px;
        padding-top: 6px;
    }
</style>


<div class="import-wrapper">

    <!-- ── Page Header ── -->
    <div class="mg-page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-file-earmark-excel" style="color:var(--accent-green);margin-right:10px"></i>Impor Data Excel</h1>
            <p class="page-subtitle">Upload file Excel TRANSAKSI HARIAN MI STORE dan simpan otomatis ke Produk &amp; Penjualan</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('import/riwayat') ?>" class="btn-mg btn-outline-mg">
                <i class="bi bi-clock-history"></i> Riwayat Import
            </a>
        </div>
    </div>

    <!-- ── Quick Nav ── -->
    <div class="quick-nav-row">
        <a href="<?= base_url('import') ?>" class="quick-nav-pill active">
            <i class="bi bi-upload"></i>
            <span>Import Excel</span>
            <span class="badge-mg ml-badge">XLSX</span>
        </a>
        <a href="<?= base_url('import/riwayat') ?>" class="quick-nav-pill">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat Import</span>
        </a>
    </div>

    <!-- ── Stepper ── -->
    <div class="card-mg" style="padding:20px 28px">
        <div class="imp-stepper" id="stepper">
            <div class="imp-step step-active" id="step-1">
                <div class="imp-step-circle">1</div>
                <div class="imp-step-label">Upload</div>
            </div>
            <div class="imp-step" id="step-2">
                <div class="imp-step-circle">2</div>
                <div class="imp-step-label">Pilih Sheet</div>
            </div>
            <div class="imp-step" id="step-3">
                <div class="imp-step-circle">3</div>
                <div class="imp-step-label">Opsi</div>
            </div>
            <div class="imp-step" id="step-4">
                <div class="imp-step-circle">4</div>
                <div class="imp-step-label">Proses</div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════
         STEP 1 — Upload
    ══════════════════════════════════════ -->
    <div class="card-mg imp-panel" id="panel-step-1">
        <div class="mg-card-header">
            <div class="mg-card-title-group">
                <span class="section-title">Upload File Excel</span>
                <span class="badge-mg badge-green">Step 1</span>
            </div>
        </div>

        <div class="imp-dropzone" id="dropZone">
            <input type="file" id="fileInput" accept=".xlsx" />
            <span class="imp-dz-icon"><i class="bi bi-cloud-arrow-up-fill" id="dzIcon"></i></span>
            <div class="imp-dz-title" id="dzTitle">Klik atau drag &amp; drop file di sini</div>
            <div class="imp-dz-sub">Format: <strong>.xlsx</strong> &nbsp;·&nbsp; Maks. <strong>20 MB</strong></div>
        </div>

        <!-- File info -->
        <div class="mt-3 d-none" id="fileInfo">
            <div class="imp-file-bar">
                <i class="bi bi-file-earmark-spreadsheet imp-file-icon"></i>
                <div class="flex-grow-1">
                    <div class="imp-file-name" id="fileName"></div>
                    <div class="imp-file-size" id="fileSize"></div>
                </div>
                <button class="btn-mg btn-ghost-mg" id="btnRemoveFile" style="padding:6px 12px">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn-mg btn-primary-mg" id="btnLoadSheets" disabled>
                <i class="bi bi-arrow-right-circle-fill"></i> Lanjut: Pilih Sheet
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════
         STEP 2 — Pilih Sheet
    ══════════════════════════════════════ -->
    <div class="card-mg imp-panel hidden-step" id="panel-step-2">
        <div class="mg-card-header">
            <div class="mg-card-title-group">
                <span class="section-title">Pilih Sheet yang Akan Diimpor</span>
                <span class="badge-mg badge-cyan">Step 2</span>
            </div>
        </div>
        <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:14px">Sheet yang tidak dipilih akan diabaikan. Kosongkan = proses semua sheet.</p>

        <!-- Loading -->
        <div id="sheetLoading" class="d-flex align-items-center gap-2" style="color:var(--text-muted);font-size:13px">
            <div class="spinner-border spinner-border-sm text-primary"></div>
            Membaca sheet dari file…
        </div>

        <!-- Sheet grid -->
        <div id="sheetSelectorWrap" class="d-none">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <small style="color:var(--text-muted)"><span id="sheetCount">0</span> sheet ditemukan</small>
                <div class="d-flex gap-3">
                    <button class="btn-mg btn-ghost-mg" id="btnSelectAll" style="padding:4px 12px;font-size:11.5px">Pilih Semua</button>
                    <button class="btn-mg btn-ghost-mg" id="btnDeselectAll" style="padding:4px 12px;font-size:11.5px">Reset</button>
                </div>
            </div>
            <div class="imp-sheet-grid" id="sheetGrid"></div>
        </div>

        <div class="imp-action-footer d-flex gap-2 flex-wra">
            <button class="btn-mg btn-outline-mg" id="btnBackStep1"><i class="bi bi-arrow-left"></i> Kembali</button>
            <button class="btn-mg btn-primary-mg" id="btnToStep3"><i class="bi bi-arrow-right-circle-fill"></i> Lanjut: Opsi</button>
        </div>
    </div>

    <!-- ══════════════════════════════════════
         STEP 3 — Opsi Import
    ══════════════════════════════════════ -->
    <div class="card-mg imp-panel hidden-step" id="panel-step-3">
        <div class="mg-card-header">
            <div class="mg-card-title-group">
                <span class="section-title">Atur Opsi Import</span>
                <span class="badge-mg" style="background:rgba(212,160,23,.12);color:var(--accent-yellow);border:1px solid rgba(212,160,23,.2)">Step 3</span>
            </div>
        </div>

        <div class="row g-4">
            <!-- Mode Produk -->
            <div class="col-md-4">
                <div class="imp-section-label">Jika produk sudah ada:</div>
                <div class="imp-option-group" id="grp-mode_produk">
                    <label class="imp-option-pill active" data-group="mode_produk" data-val="skip">
                        <i class="bi bi-skip-forward-fill"></i>Lewati
                    </label>
                    <label class="imp-option-pill" data-group="mode_produk" data-val="update">
                        <i class="bi bi-pencil-fill"></i>Update
                    </label>
                    <label class="imp-option-pill" data-group="mode_produk" data-val="overwrite">
                        <i class="bi bi-trash3-fill"></i>Hapus Dulu
                    </label>
                </div>
                <input type="hidden" id="modeProduk" value="skip">
                <p class="imp-option-desc" id="descProduk">Produk yang sudah ada di database akan dilewati.</p>
            </div>

            <!-- Mode Penjualan -->
            <div class="col-md-4">
                <div class="imp-section-label">Jika transaksi sudah ada:</div>
                <div class="imp-option-group" id="grp-mode_penjualan">
                    <label class="imp-option-pill active" data-group="mode_penjualan" data-val="skip">
                        <i class="bi bi-skip-forward-fill"></i>Lewati Duplikat
                    </label>
                    <label class="imp-option-pill" data-group="mode_penjualan" data-val="overwrite">
                        <i class="bi bi-arrow-repeat"></i>Hapus &amp; Ganti
                    </label>
                </div>
                <input type="hidden" id="modePenjualan" value="skip">
                <p class="imp-option-desc" id="descPenjualan">Transaksi duplikat (no_nota + produk sama) akan dilewati.</p>
            </div>

            <!-- Mode Deteksi Tanggal -->
            <div class="col-md-4">
                <div class="imp-section-label">Mode deteksi tanggal:</div>
                <div class="imp-option-group" id="grp-detection_mode">
                    <label class="imp-option-pill active" data-group="detection_mode" data-val="auto">
                        <i class="bi bi-magic"></i>Otomatis
                    </label>
                    <label class="imp-option-pill" data-group="detection_mode" data-val="manual">
                        <i class="bi bi-calendar3"></i>Dari Sheet
                    </label>
                </div>
                <input type="hidden" id="detectionMode" value="auto">
                <p class="imp-option-desc">
                    <b style="color:var(--text-ink)">Otomatis:</b> Deteksi dari isi baris Excel.
                    <b style="color:var(--text-ink)">Dari Sheet:</b> Fallback ke 01 bulan sesuai nama sheet.
                </p>
            </div>
        </div>

        <!-- Overwrite warning -->
        <div class="imp-alert-warn d-none mt-3" id="alertOverwrite">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div><strong>Perhatian:</strong> Mode "Hapus Dulu" akan menghapus seluruh data yang ada sebelum import. Pastikan Anda sudah melakukan backup.</div>
        </div>

        <div class="imp-action-footer d-flex gap-2 flex-wra">
            <button class="btn-mg btn-outline-mg" id="btnBackStep2"><i class="bi bi-arrow-left"></i> Kembali</button>
            <button class="btn-mg btn-primary-mg" id="btnStartImport">
                <i class="bi bi-cloud-download-fill"></i> Mulai Import
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════
         STEP 4 — Proses & Hasil
    ══════════════════════════════════════ -->
    <div class="card-mg imp-panel hidden-step" id="panel-step-4">

        <!-- ─ Loading state ─ -->
        <div id="importLoading">
            <div class="mg-card-header">
                <div class="mg-card-title-group">
                    <span class="section-title">Memproses Data…</span>
                    <span class="badge-mg badge-cyan">Step 4</span>
                </div>
            </div>

            <div class="imp-spinner">
                <div class="imp-spin-ring"></div>
                <div class="imp-spin-ring imp-spin-ring-2"></div>
                <i class="bi bi-file-earmark-spreadsheet imp-spin-core"></i>
            </div>

            <div class="imp-progress-wrap mb-3">
                <div class="imp-progress-bar">
                    <div class="imp-progress-fill" id="impProgressFill" style="width:15%"></div>
                </div>
                <span id="impProgressPct" style="font-size:12px;font-weight:700;color:var(--accent-cyan);min-width:34px">15%</span>
            </div>

            <div class="imp-proc-steps" id="procSteps">
                <div class="imp-proc-step ps-active" id="ps1"><i class="bi bi-arrow-right-circle-fill"></i> Mengirim file ke server Python</div>
                <div class="imp-proc-step d-none" id="ps2"><i class="bi bi-circle-fill"></i> Python membaca sheet Excel</div>
                <div class="imp-proc-step d-none" id="ps3"><i class="bi bi-circle-fill"></i> Mengekstrak data transaksi</div>
                <div class="imp-proc-step d-none" id="ps4"><i class="bi bi-circle-fill"></i> Menyimpan produk ke database</div>
                <div class="imp-proc-step d-none" id="ps5"><i class="bi bi-circle-fill"></i> Menyimpan data penjualan</div>
            </div>
        </div>

        <!-- ─ Result state ─ -->
        <div id="importResult" class="d-none">
            <div class="d-flex align-items-center gap-4 mb-4">
                <div class="imp-result-icon" id="resultIcon"><i id="resultIconI"></i></div>
                <div>
                    <div class="section-title" id="resultTitle"></div>
                    <div style="font-size:13px;color:var(--text-muted);margin-top:4px" id="resultMessage"></div>
                </div>
            </div>

            <!-- Stats -->
            <div class="imp-stat-grid" id="statGrid"></div>

            <!-- Sheet info -->
            <div id="sheetInfo" class="d-none mb-3">
                <div class="imp-section-label">Sheet Diproses</div>
                <div id="sheetsProcessed"></div>
                <div id="sheetsFailed" class="mt-2 d-none">
                    <div class="imp-section-label" style="color:var(--accent-red)">Sheet Gagal</div>
                    <div id="sheetsFailedList"></div>
                </div>
            </div>

            <div class="imp-result-actions d-flex gap-2 flex-wrap">
                <a href="<?= base_url('penjualan') ?>" class="btn-mg btn-success-mg">
                    <i class="bi bi-cart3"></i> Lihat Penjualan
                </a>

                <a href="<?= base_url('produk') ?>" class="btn-mg btn-outline-mg">
                    <i class="bi bi-phone-fill"></i> Lihat Produk
                </a>

                <button class="btn-mg btn-ghost-mg" id="btnImportLagi">
                    <i class="bi bi-arrow-repeat"></i> Import Lagi
                </button>
            </div>
        </div>
    </div>

</div><!-- /.import-wrapper -->


<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        // ── State ────────────────────────────────────────────────────────────────
        let selectedFile = null;
        let availSheets = [];
        const selectedSh = new Set();
        let modeProduk = 'skip';
        let modePenjualan = 'skip';
        let detectionMode = 'auto';

        const descProduk = {
            skip: 'Produk yang sudah ada di database akan dilewati.',
            update: 'Produk yang sudah ada akan di-update status aktifnya.',
            overwrite: 'SELURUH data produk akan dihapus lalu diganti dengan data baru.',
        };
        const descPenjualan = {
            skip: 'Transaksi duplikat (no_nota + produk sama) akan dilewati.',
            overwrite: 'SELURUH data penjualan akan dihapus lalu diganti dengan data dari file ini.',
        };

        const PROC_STEPS = [{
                id: 'ps1',
                pct: 20,
                text: 'Mengirim file ke server Python'
            },
            {
                id: 'ps2',
                pct: 40,
                text: 'Python membaca sheet Excel'
            },
            {
                id: 'ps3',
                pct: 60,
                text: 'Mengekstrak data transaksi'
            },
            {
                id: 'ps4',
                pct: 80,
                text: 'Menyimpan produk ke database'
            },
            {
                id: 'ps5',
                pct: 100,
                text: 'Menyimpan data penjualan'
            },
        ];

        // ── Stepper ──────────────────────────────────────────────────────────────

        function goStep(n) {

            document.querySelectorAll('.imp-step').forEach((el, i) => {
                el.classList.remove('step-active', 'step-done');

                if (i + 1 < n) {
                    el.classList.add('step-done');
                }

                if (i + 1 === n) {
                    el.classList.add('step-active');
                }
            });

            // sembunyikan semua panel
            document.querySelectorAll('[id^="panel-step-"]').forEach(el => {
                el.classList.add('hidden-step');
            });

            // tampilkan panel aktif
            const panel = document.getElementById('panel-step-' + n);

            if (panel) {
                panel.classList.remove('hidden-step');

                panel.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        // ── Drop Zone ─────────────────────────────────────────────────────────────
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');

        dropZone.addEventListener('dragover', e => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            const f = e.dataTransfer.files[0];
            if (f) setFile(f);
        });
        fileInput.addEventListener('change', () => {
            if (fileInput.files[0]) setFile(fileInput.files[0]);
        });

        function setFile(f) {
            if (!f.name.endsWith('.xlsx')) {
                alert('Hanya file .xlsx yang diterima.');
                return;
            }
            selectedFile = f;
            document.getElementById('dzTitle').textContent = f.name;
            document.getElementById('dzIcon').className = 'bi bi-file-earmark-excel-fill';
            document.getElementById('fileName').textContent = f.name;
            document.getElementById('fileSize').textContent = (f.size / 1024).toFixed(1) + ' KB';
            document.getElementById('fileInfo').classList.remove('d-none');
            document.getElementById('btnLoadSheets').disabled = false;
            dropZone.classList.add('has-file');
        }

        document.getElementById('btnRemoveFile').addEventListener('click', () => {
            selectedFile = null;
            fileInput.value = '';
            document.getElementById('fileInfo').classList.add('d-none');
            document.getElementById('btnLoadSheets').disabled = true;
            document.getElementById('dzTitle').textContent = 'Klik atau drag & drop file di sini';
            document.getElementById('dzIcon').className = 'bi bi-cloud-arrow-up-fill';
            dropZone.classList.remove('has-file');
        });

        // ── Step 1 → 2 ───────────────────────────────────────────────────────────
        document.getElementById('btnLoadSheets').addEventListener('click', async () => {
            goStep(2);
            document.getElementById('sheetLoading').classList.remove('d-none');
            document.getElementById('sheetSelectorWrap').classList.add('d-none');

            const fd = new FormData();
            fd.append('file_excel', selectedFile);

            try {
                const res = await fetch('<?= base_url('import/previewSheets') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                });
                const data = await res.json();
                document.getElementById('sheetLoading').classList.add('d-none');

                if (data.status !== 'success') {
                    alert('Gagal membaca sheet: ' + data.message);
                    goStep(1);
                    return;
                }

                availSheets = data.sheets || [];
                renderSheetGrid(availSheets);
                document.getElementById('sheetCount').textContent = availSheets.length;
                document.getElementById('sheetSelectorWrap').classList.remove('d-none');
                availSheets.forEach(s => selectedSh.add(s));
                refreshSheetUI();
            } catch (err) {
                document.getElementById('sheetLoading').classList.add('d-none');
                alert('Error: ' + err.message);
                goStep(1);
            }
        });

        function renderSheetGrid(sheets) {
            const grid = document.getElementById('sheetGrid');
            grid.innerHTML = '';

            sheets.forEach(s => {

                const chip = document.createElement('label');
                chip.className = 'imp-sheet-chip selected';
                chip.dataset.sheet = s;

                chip.innerHTML = `
            <input type="checkbox" value="${s}" checked>
            <i class="bi bi-check-circle-fill"
               style="color:var(--mg-light);font-size:12px"></i>
            <span>${s}</span>
        `;

                const checkbox = chip.querySelector('input');

                // gunakan event change checkbox
                checkbox.addEventListener('change', function() {

                    if (this.checked) {
                        selectedSh.add(s);
                    } else {
                        selectedSh.delete(s);
                    }

                    refreshSheetUI();
                });

                grid.appendChild(chip);
            });
        }

        function refreshSheetUI() {

            document.querySelectorAll('.imp-sheet-chip').forEach(chip => {

                const s = chip.dataset.sheet;
                const sel = selectedSh.has(s);

                chip.classList.toggle('selected', sel);

                const checkbox = chip.querySelector('input');
                checkbox.checked = sel;

                const icon = chip.querySelector('i');

                icon.className = sel ?
                    'bi bi-check-circle-fill' :
                    'bi bi-circle';

                icon.style.color = sel ?
                    'var(--mg-light)' :
                    'var(--text-placeholder)';
            });
        }

        document.getElementById('btnSelectAll').addEventListener('click', () => {
            availSheets.forEach(s => selectedSh.add(s));
            refreshSheetUI();
        });
        document.getElementById('btnDeselectAll').addEventListener('click', () => {
            selectedSh.clear();
            refreshSheetUI();
        });
        document.getElementById('btnBackStep1').addEventListener('click', () => goStep(1));
        document.getElementById('btnToStep3').addEventListener('click', () => goStep(3));

        // ── Step 3: Opsi ──────────────────────────────────────────────────────────
        document.querySelectorAll('.imp-option-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                const group = pill.dataset.group;
                const val = pill.dataset.val;

                document.querySelectorAll(`.imp-option-pill[data-group="${group}"]`)
                    .forEach(p => p.classList.remove('active', 'active-warn'));

                const isDestructive = val === 'overwrite';
                pill.classList.add(isDestructive ? 'active-warn' : 'active');

                if (group === 'mode_produk') {
                    modeProduk = val;
                    document.getElementById('modeProduk').value = val;
                    document.getElementById('descProduk').textContent = descProduk[val];
                } else if (group === 'mode_penjualan') {
                    modePenjualan = val;
                    document.getElementById('modePenjualan').value = val;
                    document.getElementById('descPenjualan').textContent = descPenjualan[val];
                } else if (group === 'detection_mode') {
                    detectionMode = val;
                    document.getElementById('detectionMode').value = val;
                }

                const hasOverwrite = modeProduk === 'overwrite' || modePenjualan === 'overwrite';
                document.getElementById('alertOverwrite').classList.toggle('d-none', !hasOverwrite);
            });
        });

        document.getElementById('btnBackStep2').addEventListener('click', () => goStep(2));

        // ── Step 4: Proses Import ─────────────────────────────────────────────────
        document.getElementById('btnStartImport').addEventListener('click', async () => {
            goStep(4);
            document.getElementById('importLoading').classList.remove('d-none');
            document.getElementById('importResult').classList.add('d-none');

            // Simulate step progress
            // ── Progress Step Realistic ─────────────────────────────
            let stepIdx = 0;
            let stepTimer = null;

            function renderProcSteps(activeIdx = 0, finished = false) {

                PROC_STEPS.forEach((ps, i) => {

                    const el = document.getElementById(ps.id);

                    if (!el) return;

                    el.classList.remove('d-none', 'ps-active', 'ps-done');

                    // semua selesai
                    if (finished || i < activeIdx) {

                        el.classList.add('ps-done');

                        el.innerHTML = `
                <i class="bi bi-check-circle-fill"></i>
                ${ps.text}
            `;

                        return;
                    }

                    // step aktif
                    if (i === activeIdx) {

                        el.classList.add('ps-active');

                        el.innerHTML = `
                <i class="bi bi-arrow-right-circle-fill"></i>
                ${ps.text}
            `;

                        return;
                    }

                    // step belum jalan
                    el.classList.add('d-none');

                    el.innerHTML = `
            <i class="bi bi-circle-fill"></i>
            ${ps.text}
        `;
                });
            }

            function nextProcStep() {

                if (stepIdx >= PROC_STEPS.length) {
                    clearInterval(stepTimer);
                    return;
                }

                renderProcSteps(stepIdx);

                setProgress(PROC_STEPS[stepIdx].pct);

                stepIdx++;
            }

            // init
            renderProcSteps(0);
            setProgress(15);

            // mulai animasi progress
            stepTimer = setInterval(nextProcStep, 1800);

            const fd = new FormData();
            fd.append('file_excel', selectedFile);
            [...selectedSh].forEach(s => fd.append('sheets[]', s));
            fd.append('mode_produk', modeProduk);
            fd.append('mode_penjualan', modePenjualan);
            fd.append('detection_mode', detectionMode);

            try {
                const res = await fetch('<?= base_url('import/proses') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                });
                clearInterval(stepTimer);
                const data = await res.json();

                // hentikan progress animation
                clearInterval(stepTimer);

                // paksa semua step selesai
                renderProcSteps(PROC_STEPS.length, true);

                // progress 100%
                setProgress(100);

                // delay kecil agar transisi smooth
                setTimeout(() => {
                    showResult(data);
                }, 400);
            } catch (err) {
                clearInterval(stepTimer);

                renderProcSteps(stepIdx, false);

                showResult({
                    status: 'error',
                    message: 'Koneksi gagal: ' + err.message
                });
            }
        });

        function setProgress(pct) {
            const fill = document.getElementById('impProgressFill');
            const pctEl = document.getElementById('impProgressPct');
            if (fill) fill.style.width = pct + '%';
            if (pctEl) pctEl.textContent = Math.round(pct) + '%';
        }

        function showResult(data) {

            // ── Hide loading ─────────────────────────
            document.getElementById('importLoading').classList.add('d-none');
            document.getElementById('importResult').classList.remove('d-none');

            // ── STOP spinner animation completely ───
            document.querySelectorAll('.imp-spin-ring').forEach(el => {
                el.style.animation = 'none';
            });

            const spinCore = document.querySelector('.imp-spin-core');

            // ── Result State ─────────────────────────
            const isOk = data.status === 'success';
            const detail = data.detail || {};

            // ganti icon spinner jadi success/error
            spinCore.className = isOk ?
                'bi bi-check-circle-fill imp-spin-core' :
                'bi bi-x-circle-fill imp-spin-core';

            spinCore.style.animation = 'none';
            spinCore.style.color = isOk ?
                'var(--accent-green)' :
                'var(--accent-red)';

            // ── Result Header ────────────────────────
            const iconEl = document.getElementById('resultIcon');

            iconEl.className = 'imp-result-icon ' + (isOk ? 'ok' : 'fail');

            document.getElementById('resultIconI').className =
                isOk ?
                'bi bi-check-circle-fill' :
                'bi bi-x-circle-fill';

            document.getElementById('resultTitle').textContent =
                isOk ?
                'Import Berhasil!' :
                'Import Gagal';

            document.getElementById('resultMessage').textContent =
                data.message || '';

            // ── Stats ────────────────────────────────
            const grid = document.getElementById('statGrid');
            grid.innerHTML = '';

            // reset sheet info dulu
            document.getElementById('sheetInfo').classList.add('d-none');
            document.getElementById('sheetsFailed').classList.add('d-none');
            document.getElementById('sheetsProcessed').innerHTML = '';
            document.getElementById('sheetsFailedList').innerHTML = '';

            if (isOk) {

                const stats = [{
                        num: detail.penjualan?.baru ?? 0,
                        label: 'Transaksi Disimpan',
                        color: 'var(--accent-green)'
                    },
                    {
                        num: detail.penjualan?.skip ?? 0,
                        label: 'Transaksi Dilewati',
                        color: 'var(--accent-yellow)'
                    },
                    {
                        num: detail.produk?.baru ?? 0,
                        label: 'Produk Baru',
                        color: 'var(--mg-light)'
                    },
                    {
                        num: detail.produk?.skip ?? 0,
                        label: 'Produk Dilewati',
                        color: 'var(--text-muted)'
                    },
                    {
                        num: detail.produk?.update ?? 0,
                        label: 'Produk Diupdate',
                        color: 'var(--accent-cyan)'
                    },
                ];

                stats.forEach((s, i) => {

                    const el = document.createElement('div');

                    el.className = 'imp-stat-card';
                    el.style.animationDelay = (i * 0.07) + 's';

                    el.innerHTML = `
                <div class="imp-stat-num" style="color:${s.color}">
                    ${s.num.toLocaleString('id-ID')}
                </div>
                <div class="imp-stat-label">${s.label}</div>
            `;

                    grid.appendChild(el);
                });

                // ── Sheet processed ──────────────────
                const sp = detail.sheets_processed || [];
                const sf = detail.sheets_failed || [];

                if (sp.length > 0) {

                    document.getElementById('sheetInfo')
                        .classList.remove('d-none');

                    document.getElementById('sheetsProcessed').innerHTML =
                        sp.map(s =>
                            `<span class="imp-sheet-badge">${s}</span>`
                        ).join('');
                }

                // ── Sheet gagal hanya tampil jika ada ─
                if (sf.length > 0) {

                    document.getElementById('sheetsFailed')
                        .classList.remove('d-none');

                    document.getElementById('sheetsFailedList').innerHTML =
                        sf.map(s =>
                            `<span class="imp-sheet-badge fail">
                        ${s.sheet ?? s}
                    </span>`
                        ).join('');
                } else {

                    // paksa hidden jika kosong
                    document.getElementById('sheetsFailed')
                        .classList.add('d-none');

                    document.getElementById('sheetsFailedList').innerHTML = '';
                }
            }
        }

        // ── Reset ─────────────────────────────────────────────────────────────────
        document.getElementById('btnImportLagi').addEventListener('click', () => {
            selectedFile = null;
            availSheets = [];
            selectedSh.clear();
            fileInput.value = '';
            document.getElementById('fileInfo').classList.add('d-none');
            document.getElementById('btnLoadSheets').disabled = true;
            document.getElementById('dzTitle').textContent = 'Klik atau drag & drop file di sini';
            document.getElementById('dzIcon').className = 'bi bi-cloud-arrow-up-fill';
            dropZone.classList.remove('has-file');
            // reset spinner animation
            document.querySelectorAll('.imp-spin-ring').forEach(el => {
                el.style.animation = '';
            });

            const spinCore = document.querySelector('.imp-spin-core');

            spinCore.className =
                'bi bi-file-earmark-spreadsheet imp-spin-core';

            spinCore.style.animation = '';
            spinCore.style.color = 'var(--mg-light)';
            goStep(1);
        });
    });
</script>