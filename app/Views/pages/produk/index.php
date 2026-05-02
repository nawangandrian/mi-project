<?php

/**
 * View  : pages/produk/index.php
 * Modul : Manajemen Produk — Mi Store Kudus
 */
?>

<div class="content-wrapper-inner">

    <!-- ── Page Header ── -->
    <div class="mg-page-header">
        <div>
            <h1 class="page-title">Data Produk</h1>
            <p class="page-subtitle">Kelola data katalog produk Mi Store</p>
        </div>
        <div class="header-actions">
            <button class="btn-mg btn-danger-outline" id="btnDeleteAll" title="Hapus semua produk">
                <i class="bi bi-trash3-fill"></i> Hapus Semua
            </button>
            <button class="btn-mg btn-success-outline" id="btnImport" title="Import dari Excel">
                <i class="bi bi-file-earmark-arrow-up-fill"></i> Import
            </button>
            <a href="<?= base_url('produk/export') ?>" class="btn-mg btn-export" title="Export ke Excel">
                <i class="bi bi-file-earmark-arrow-down-fill"></i> Export
            </a>
            <button class="btn-mg btn-primary-mg" id="btnOpenAdd">
                <i class="bi bi-plus-circle-fill"></i> Tambah Produk
            </button>
        </div>
    </div>

    <!-- ── Stats Row ── -->
    <div class="mg-stats-row">
        <?php
        $total    = count($produks ?? []);
        $aktif    = count(array_filter($produks ?? [], fn($p) => $p['is_active'] == 1));
        $nonaktif = $total - $aktif;
        ?>
        <div class="stat-card accent-cyan">
            <div class="stat-icon" style="background:rgba(0,151,184,0.12);color:var(--accent-cyan)">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-label">Total Produk</div>
        </div>
        <div class="stat-card accent-green">
            <div class="stat-icon" style="background:rgba(16,183,127,0.12);color:var(--accent-green)">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="stat-value"><?= $aktif ?></div>
            <div class="stat-label">Produk Aktif</div>
        </div>
        <div class="stat-card accent-red">
            <div class="stat-icon" style="background:rgba(229,62,62,0.12);color:var(--accent-red)">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <div class="stat-value"><?= $nonaktif ?></div>
            <div class="stat-label">Nonaktif</div>
        </div>
    </div>

    <!-- ── Table Card ── -->
    <div class="card-mg">
        <div class="mg-card-header">
            <div class="mg-card-title-group">
                <span class="section-title">Data Produk</span>
                <span class="badge-mg badge-cyan" id="badgeCount"><?= $total ?> produk</span>
            </div>
            <div class="mg-card-toolbar">
                <!-- Filter Status -->
                <div class="mg-filter-wrap">
                    <select id="filterStatus" class="form-mg form-mg-sm">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <!-- Search -->
                <div class="mg-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="mgSearch" placeholder="Cari nama produk…">
                </div>
            </div>
        </div>

        <div class="mg-table-wrap">
            <table class="table-mg" id="tblProduk">
                <thead>
                    <tr>
                        <th style="width:46px">No</th>
                        <th style="width:130px">ID Produk</th>
                        <th>Nama Produk</th>
                        <th style="width:100px;text-align:center">Status</th>
                        <th style="width:155px">Dibuat</th>
                        <th style="width:110px;text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($produks)): $no = 1; ?>
                        <?php foreach ($produks as $p): ?>
                            <tr data-status="<?= $p['is_active'] ? 'aktif' : 'nonaktif' ?>">
                                <td><span class="row-no"><?= $no++ ?></span></td>
                                <td><code class="id-code"><?= esc($p['id_produk']) ?></code></td>
                                <td>
                                    <div class="produk-cell">
                                        <div class="produk-icon">
                                            <i class="bi bi-phone-fill"></i>
                                        </div>
                                        <span class="produk-name"><?= esc($p['nama_produk']) ?></span>
                                    </div>
                                </td>
                                <td style="text-align:center">
                                    <?php if ($p['is_active']): ?>
                                        <span class="badge-mg badge-green">
                                            <i class="bi bi-check-circle-fill"></i> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-mg badge-red">
                                            <i class="bi bi-x-circle-fill"></i> Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="date-cell">
                                    <i class="bi bi-calendar3" style="font-size:11px;margin-right:5px"></i>
                                    <?= date('d M Y, H:i', strtotime($p['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <button class="action-btn edit-btn btnEdit"
                                            data-id="<?= esc($p['id_produk']) ?>" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="action-btn delete-btn btnDelete"
                                            data-id="<?= esc($p['id_produk']) ?>"
                                            data-nama="<?= esc($p['nama_produk']) ?>" title="Hapus">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="bi bi-box-seam" style="font-size:38px;color:var(--text-placeholder);display:block;margin-bottom:10px"></i>
                                Belum ada data produk
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination / row count info -->
        <div class="mg-table-footer">
            <span class="table-info" id="tableInfo">Menampilkan 1–10 dari <?= $total ?> produk</span>
            <div class="mg-pagination" id="mgPagination"></div>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     MODAL TAMBAH PRODUK
═══════════════════════════════════════════════════════════ -->
<div class="mg-modal-overlay" id="overlayAdd">
    <div class="mg-modal">
        <div class="mg-modal-header" style="--modal-accent:var(--mg-glow)">
            <div class="mg-modal-icon" style="background:rgba(46,109,164,0.15);color:var(--mg-light)">
                <i class="bi bi-plus-circle-fill"></i>
            </div>
            <div>
                <div class="mg-modal-title">Tambah Produk</div>
                <div class="mg-modal-sub">Daftarkan produk baru ke katalog</div>
            </div>
            <button class="mg-modal-close" id="closeAdd" type="button"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="mg-modal-body">
            <form id="formStore" autocomplete="off">
                <?= csrf_field() ?>

                <div class="mg-field">
                    <label class="form-label-mg">Nama Produk</label>
                    <div class="mg-input-icon">
                        <i class="bi bi-phone"></i>
                        <input type="text" name="nama_produk" class="form-mg" placeholder="Contoh: REDMI NOTE 14 PRO 8/256 BLACK">
                    </div>
                    <small class="mg-field-error error-nama_produk-store"></small>
                </div>

                <div class="mg-field">
                    <label class="form-label-mg">Status</label>
                    <div class="mg-select-wrap">
                        <i class="bi bi-toggle-on"></i>
                        <select name="is_active" class="form-mg">
                            <option value="1" selected>Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                        <i class="bi bi-chevron-down mg-select-arrow"></i>
                    </div>
                    <small class="mg-field-error error-is_active-store"></small>
                </div>
            </form>
        </div>

        <div class="mg-modal-footer">
            <button class="btn-mg btn-outline-mg" id="cancelAdd" type="button">Batal</button>
            <button class="btn-mg btn-primary-mg" id="btnSave" type="button">
                <i class="bi bi-floppy-fill"></i> Simpan
            </button>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     MODAL EDIT PRODUK
═══════════════════════════════════════════════════════════ -->
<div class="mg-modal-overlay" id="overlayEdit">
    <div class="mg-modal">
        <div class="mg-modal-header" style="--modal-accent:#d4a017">
            <div class="mg-modal-icon" style="background:rgba(212,160,23,0.15);color:var(--accent-yellow)">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <div class="mg-modal-title">Edit Produk</div>
                <div class="mg-modal-sub">Perbarui informasi produk</div>
            </div>
            <button class="mg-modal-close" id="closeEdit" type="button"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="mg-modal-body">
            <form id="formEdit" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="id_produk" id="edit_id">

                <div class="mg-field">
                    <label class="form-label-mg">Nama Produk</label>
                    <div class="mg-input-icon">
                        <i class="bi bi-phone"></i>
                        <input type="text" name="nama_produk" id="edit_nama" class="form-mg" placeholder="Nama produk">
                    </div>
                    <small class="mg-field-error error-nama_produk-edit"></small>
                </div>

                <div class="mg-field">
                    <label class="form-label-mg">Status</label>
                    <div class="mg-select-wrap">
                        <i class="bi bi-toggle-on"></i>
                        <select name="is_active" id="edit_status" class="form-mg">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                        <i class="bi bi-chevron-down mg-select-arrow"></i>
                    </div>
                    <small class="mg-field-error error-is_active-edit"></small>
                </div>
            </form>
        </div>

        <div class="mg-modal-footer">
            <button class="btn-mg btn-outline-mg" id="cancelEdit" type="button">Batal</button>
            <button class="btn-mg" id="btnUpdate" type="button"
                style="background:linear-gradient(135deg,#b8860b,var(--accent-yellow));color:#fff">
                <i class="bi bi-arrow-repeat"></i> Update
            </button>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     MODAL IMPORT EXCEL
═══════════════════════════════════════════════════════════ -->
<div class="mg-modal-overlay" id="overlayImport">
    <div class="mg-modal mg-modal-lg">
        <div class="mg-modal-header" style="--modal-accent:#10b77f">
            <div class="mg-modal-icon" style="background:rgba(16,183,127,0.15);color:var(--accent-green)">
                <i class="bi bi-file-earmark-excel-fill"></i>
            </div>
            <div>
                <div class="mg-modal-title">Import Excel</div>
                <div class="mg-modal-sub">Unggah file .xlsx / .xls / .csv</div>
            </div>
            <button class="mg-modal-close" id="closeImport" type="button"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="mg-modal-body">
            <!-- Info format -->
            <div class="import-info-box">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                    <div style="font-weight:600;margin-bottom:4px">Format kolom yang diperlukan:</div>
                    <div class="import-cols">
                        <span class="import-col-badge">A: nama_produk</span>
                        <span class="import-col-badge">B: is_active (1/0)</span>
                    </div>
                    <div style="font-size:12px;margin-top:6px;color:var(--text-muted)">
                        Baris pertama dianggap sebagai header dan akan dilewati.
                    </div>
                </div>
            </div>

            <form id="formImport" enctype="multipart/form-data" autocomplete="off">
                <?= csrf_field() ?>

                <!-- Drop Zone -->
                <div class="drop-zone" id="dropZone">
                    <div class="drop-zone-inner" id="dropZoneContent">
                        <div class="drop-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                        <div class="drop-title">Klik atau seret file ke sini</div>
                        <div class="drop-sub">.xlsx, .xls, .csv — maks. 5 MB</div>
                        <input type="file" name="file_excel" id="fileExcel" accept=".xlsx,.xls,.csv" hidden>
                    </div>
                    <div class="drop-selected" id="dropSelected" style="display:none">
                        <i class="bi bi-file-earmark-spreadsheet-fill" style="color:var(--accent-green);font-size:24px"></i>
                        <span id="selectedFileName">—</span>
                        <button type="button" class="drop-remove" id="removeFile" title="Hapus file">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                </div>

                <!-- Progress -->
                <div id="importProgress" style="display:none;margin-top:14px">
                    <div class="mg-progress-bar">
                        <div class="mg-progress-fill" id="progressFill"></div>
                    </div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px;text-align:center" id="progressLabel">Mengupload…</div>
                </div>

                <!-- Result -->
                <div id="importResult" style="display:none;margin-top:14px"></div>
            </form>

            <!-- Template download -->
            <div class="import-template-row">
                <i class="bi bi-download" style="color:var(--accent-cyan)"></i>
                <span>Belum punya template?</span>
                <a href="<?= base_url('produk/downloadTemplate') ?>" class="template-link">
                    Unduh template Excel
                </a>
            </div>
        </div>

        <div class="mg-modal-footer">
            <button class="btn-mg btn-outline-mg" id="cancelImport" type="button">Batal</button>
            <button class="btn-mg" id="btnUpload" type="button"
                style="background:linear-gradient(135deg,#0a7a57,var(--accent-green));color:#fff">
                <i class="bi bi-upload"></i> Upload & Proses
            </button>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     SCOPED STYLES
═══════════════════════════════════════════════════════════ -->
<style>
    /* ── Layout ── */
    .content-wrapper-inner {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

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
            grid-template-columns: 1fr;
        }
    }

    /* ── Buttons extra ── */
    .btn-danger-outline {
        background: rgba(229, 62, 62, 0.08);
        color: var(--accent-red);
        border: 1px solid rgba(229, 62, 62, 0.25);
    }

    .btn-danger-outline:hover {
        background: rgba(229, 62, 62, 0.18);
        border-color: rgba(229, 62, 62, 0.45);
    }

    .btn-success-outline {
        background: rgba(16, 183, 127, 0.08);
        color: var(--accent-green);
        border: 1px solid rgba(16, 183, 127, 0.25);
    }

    .btn-success-outline:hover {
        background: rgba(16, 183, 127, 0.18);
        border-color: rgba(16, 183, 127, 0.45);
    }

    .btn-export {
        background: rgba(0, 151, 184, 0.08);
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 151, 184, 0.25);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 16px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 600;
        transition: var(--transition);
    }

    .btn-export:hover {
        background: rgba(0, 151, 184, 0.18);
        border-color: rgba(0, 151, 184, 0.45);
    }

    /* ── Table card header ── */
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

    .mg-card-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .mg-filter-wrap {
        position: relative;
    }

    .form-mg-sm {
        padding: 6px 10px;
        font-size: 12.5px;
        width: 140px;
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
        width: 190px;
    }

    .mg-search-box input::placeholder {
        color: var(--text-placeholder);
    }

    /* ── Table ── */
    .mg-table-wrap {
        overflow-x: auto;
        border-radius: var(--radius-sm);
        border: 1px solid var(--surface-border);
    }

    .table-mg {
        min-width: 700px;
    }

    .row-no {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: 6px;
        font-size: 11px;
        color: var(--text-muted);
        font-weight: 600;
    }

    .id-code {
        font-family: 'Courier New', monospace;
        font-size: 11.5px;
        color: var(--accent-cyan);
        background: rgba(0, 151, 184, 0.08);
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid rgba(0, 151, 184, 0.15);
        white-space: nowrap;
    }

    .produk-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .produk-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, var(--mg-glow), var(--accent-cyan));
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 14px;
        flex-shrink: 0;
    }

    .produk-name {
        font-size: 13px;
        font-weight: 500;
    }

    .price-tag {
        display: inline-flex;
        align-items: baseline;
        gap: 3px;
        font-weight: 600;
        color: var(--accent-green);
        font-size: 13.5px;
    }

    .price-currency {
        font-size: 11px;
        font-weight: 500;
    }

    .date-cell {
        font-size: 12.5px;
        color: var(--text-muted);
    }

    .action-group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
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

    .edit-btn {
        background: rgba(212, 160, 23, 0.1);
        color: var(--accent-yellow);
        border-color: rgba(212, 160, 23, 0.2);
    }

    .edit-btn:hover {
        background: rgba(212, 160, 23, 0.22);
        transform: translateY(-1px);
    }

    .delete-btn {
        background: rgba(229, 62, 62, 0.1);
        color: var(--accent-red);
        border-color: rgba(229, 62, 62, 0.2);
    }

    .delete-btn:hover {
        background: rgba(229, 62, 62, 0.22);
        transform: translateY(-1px);
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px !important;
        color: var(--text-muted);
        font-size: 13px;
    }

    .mg-table-footer {
        padding: 12px 16px;
        border-top: 1px solid var(--surface-border);
        background: var(--card-bg-alt);
        border-radius: 0 0 var(--radius-md) var(--radius-md);
    }

    .table-info {
        font-size: 12.5px;
        color: var(--text-muted);
    }

    /* ── Modal base (shared with user page) ── */
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
        animation: mgFadeIn 0.18s ease;
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
        max-width: 460px;
        box-shadow: 0 24px 60px rgba(14, 23, 36, 0.25), 0 4px 16px rgba(14, 23, 36, 0.1);
        animation: mgSlideUp 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .mg-modal-lg {
        max-width: 540px;
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
        font-family: var(--font-display);
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
        border-color: rgba(229, 62, 62, 0.3);
        background: rgba(229, 62, 62, 0.06);
    }

    .mg-modal-body {
        padding: 22px;
        display: flex;
        flex-direction: column;
    }

    .mg-modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 22px;
        border-top: 1px solid var(--surface-border);
        background: var(--card-bg-alt);
    }

    /* ── Form fields ── */
    .mg-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 14px;
    }

    .mg-field:last-child {
        margin-bottom: 0;
    }

    .mg-input-icon {
        position: relative;
        display: flex;
        align-items: center;
    }

    .mg-input-icon>i:first-child {
        position: absolute;
        left: 12px;
        color: var(--text-placeholder);
        font-size: 14px;
        pointer-events: none;
    }

    .mg-input-icon .form-mg {
        padding-left: 36px;
    }

    .mg-select-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .mg-select-wrap>i:first-child {
        position: absolute;
        left: 12px;
        color: var(--text-placeholder);
        font-size: 14px;
        pointer-events: none;
    }

    .mg-select-wrap .form-mg {
        padding-left: 36px;
        padding-right: 32px;
        appearance: none;
    }

    .mg-select-arrow {
        position: absolute;
        right: 12px;
        color: var(--text-muted);
        font-size: 11px;
        pointer-events: none;
    }

    .mg-field-error {
        font-size: 11.5px;
        color: var(--accent-red);
        min-height: 16px;
    }

    /* ── Import modal specifics ── */
    .import-info-box {
        display: flex;
        gap: 12px;
        padding: 14px;
        margin-bottom: 16px;
        background: rgba(74, 159, 212, 0.06);
        border: 1px solid rgba(74, 159, 212, 0.18);
        border-radius: var(--radius-sm);
        font-size: 13px;
        color: var(--text-ink);
    }

    .import-info-box>i {
        color: var(--accent-cyan);
        font-size: 16px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .import-cols {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 6px;
    }

    .import-col-badge {
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        background: rgba(0, 151, 184, 0.1);
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 151, 184, 0.2);
        font-weight: 600;
    }

    .drop-zone {
        border: 2px dashed var(--surface-border);
        border-radius: var(--radius-md);
        background: var(--card-bg-alt);
        cursor: pointer;
        transition: var(--transition);
        overflow: hidden;
    }

    .drop-zone:hover,
    .drop-zone.drag-over {
        border-color: var(--accent-green);
        background: rgba(16, 183, 127, 0.05);
    }

    .drop-zone-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 32px 20px;
        gap: 8px;
        text-align: center;
    }

    .drop-icon {
        font-size: 36px;
        color: var(--accent-green);
    }

    .drop-title {
        font-weight: 600;
        font-size: 14px;
        color: var(--text-ink);
    }

    .drop-sub {
        font-size: 12px;
        color: var(--text-muted);
    }

    .drop-selected {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
    }

    .drop-selected span {
        font-size: 13px;
        font-weight: 500;
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .drop-remove {
        background: none;
        border: none;
        color: var(--accent-red);
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        transition: var(--transition);
    }

    .drop-remove:hover {
        transform: scale(1.2);
    }

    .mg-progress-bar {
        height: 6px;
        background: var(--card-bg-alt);
        border-radius: 999px;
        overflow: hidden;
        border: 1px solid var(--surface-border);
    }

    .mg-progress-fill {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, var(--accent-green), var(--accent-cyan));
        border-radius: 999px;
        transition: width 0.4s ease;
    }

    .import-result-box {
        padding: 14px;
        border-radius: var(--radius-sm);
        font-size: 13px;
    }

    .import-result-box.success {
        background: rgba(16, 183, 127, 0.07);
        border: 1px solid rgba(16, 183, 127, 0.25);
        color: var(--accent-green);
    }

    .import-result-box.warning {
        background: rgba(212, 160, 23, 0.07);
        border: 1px solid rgba(212, 160, 23, 0.25);
        color: var(--accent-yellow);
    }

    .import-result-box .err-list {
        max-height: 100px;
        overflow-y: auto;
        margin-top: 8px;
        font-size: 12px;
        color: var(--text-muted);
        padding-left: 14px;
    }

    .import-template-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 14px;
        font-size: 12.5px;
        color: var(--text-muted);
    }

    .template-link {
        color: var(--accent-cyan);
        font-weight: 600;
        text-decoration: none;
    }

    .template-link:hover {
        text-decoration: underline;
    }

    /* ── Pagination ── */
    .mg-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .mg-pagination {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .pg-btn {
        min-width: 30px;
        height: 30px;
        padding: 0 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid var(--surface-border);
        background: var(--card-bg-alt);
        color: var(--text-muted);
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
    }

    .pg-btn:hover:not(:disabled) {
        background: rgba(74, 159, 212, 0.1);
        border-color: var(--mg-light);
        color: var(--mg-light);
    }

    .pg-btn.active {
        background: var(--mg-glow);
        border-color: var(--mg-glow);
        color: #fff;
    }

    .pg-btn:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    .pg-ellipsis {
        color: var(--text-muted);
        font-size: 12px;
        padding: 0 4px;
    }

    @media (max-width: 768px) {

        /* ── Layout core ── */
        #main-content {
            margin-left: 0 !important;
            width: 100% !important;
        }

        .content-wrapper {
            padding: 14px 10px !important;
        }

        .content-wrapper-inner {
            gap: 14px;
        }

        .card-mg {
            padding: 14px 12px;
        }

        /* ── Page header ── */
        .mg-page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .header-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        .header-actions .btn-primary-mg {
            grid-column: 1 / -1;
            justify-content: center;
        }

        .header-actions .btn-mg,
        .header-actions .btn-export {
            justify-content: center;
            font-size: 12px;
            padding: 7px 8px;
            width: 100%;
        }

        /* ── Stats: tetap 3 kolom ── */
        .mg-stats-row {
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 8px;
        }

        .stat-card {
            padding: 12px 8px !important;
            min-width: 0;
        }

        .stat-value {
            font-size: 20px !important;
        }

        .stat-label {
            font-size: 9px !important;
            letter-spacing: 0 !important;
        }

        .stat-icon {
            width: 28px !important;
            height: 28px !important;
            font-size: 13px !important;
            margin-bottom: 6px !important;
        }

        /* ── Card toolbar ── */
        .mg-card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .mg-card-toolbar {
            width: 100%;
            flex-direction: column;
            gap: 8px;
        }

        .mg-filter-wrap,
        .form-mg-sm,
        .mg-search-box {
            width: 100%;
        }

        .mg-search-box input {
            width: 100%;
            min-width: 0;
        }

        /* ══════════════════════════════════════
       TABEL — scroll horizontal, kolom minimal
    ══════════════════════════════════════ */

        /* Biarkan scroll, reset table-layout ke auto */
        .mg-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-mg {
            /* Kembali ke auto — biarkan browser hitung lebar kolom */
            table-layout: auto !important;
            /* Lebar minimal: cukup untuk ID + Nama + Status + Aksi */
            min-width: 380px !important;
            width: 100%;
        }

        /* Sembunyikan kolom No & Tanggal */
        .table-mg th:nth-child(1),
        .table-mg td:nth-child(1),
        .table-mg th:nth-child(5),
        .table-mg td:nth-child(5) {
            display: none !important;
        }

        /* Paksa lebar kolom dengan px, bukan % */
        .table-mg th:nth-child(2),
        .table-mg td:nth-child(2) {
            width: 100px;
            min-width: 100px;
        }

        /* ID */

        .table-mg th:nth-child(3),
        .table-mg td:nth-child(3) {
            width: auto;
            min-width: 120px;
        }

        /* Nama */

        .table-mg th:nth-child(4),
        .table-mg td:nth-child(4) {
            width: 64px;
            min-width: 64px;
            text-align: center;
        }

        /* Status */

        .table-mg th:nth-child(6),
        .table-mg td:nth-child(6) {
            width: 68px;
            min-width: 68px;
            text-align: center;
        }

        /* Aksi */

        /* Padding sel lebih rapat */
        .table-mg th,
        .table-mg td {
            padding: 9px 8px;
        }

        /* ID: ellipsis */
        .id-code {
            font-size: 10px;
            padding: 2px 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            max-width: 95px;
        }

        /* Nama: flex dengan min-width: 0 agar tidak meluber */
        .produk-cell {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }

        .produk-icon {
            width: 26px;
            height: 26px;
            font-size: 12px;
            flex-shrink: 0;
        }

        .produk-name {
            font-size: 12px;
            font-weight: 500;
            white-space: normal;
            word-break: break-word;
            min-width: 0;
            flex: 1;
        }

        /* Badge status */
        .table-mg .badge-mg {
            font-size: 10px;
            padding: 2px 5px;
            white-space: nowrap;
        }

        .table-mg .badge-mg i {
            display: none;
        }

        /* Tombol aksi */
        .action-group {
            gap: 4px;
            justify-content: center;
        }

        .action-btn {
            width: 28px;
            height: 28px;
            font-size: 12px;
            border-radius: 6px;
        }

        /* ── Table footer ── */
        .mg-table-footer {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .mg-pagination {
            width: 100%;
            justify-content: center;
            flex-wrap: wrap;
        }

        .pg-btn {
            min-width: 28px;
            height: 28px;
            font-size: 11.5px;
            padding: 0 5px;
        }

        /* ── Modal: slide dari bawah ── */
        .mg-modal-overlay {
            padding: 0;
            align-items: flex-end;
        }

        .mg-modal,
        .mg-modal-lg {
            max-width: 100%;
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
            max-height: 90vh;
            overflow-y: auto;
        }

        .mg-modal-header {
            padding: 14px 16px;
        }

        .mg-modal-body {
            padding: 16px;
        }

        .mg-modal-footer {
            padding: 12px 16px;
            flex-direction: column-reverse;
            gap: 8px;
        }

        .mg-modal-footer .btn-mg {
            width: 100%;
            justify-content: center;
        }
    }
</style>


<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════ -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        // ── Utilities ─────────────────────────────────────────────────────────────
        function qs(sel) {
            return document.querySelector(sel);
        }

        function qsa(sel) {
            return document.querySelectorAll(sel);
        }

        function openModal(id) {
            var el = qs('#' + id);
            if (!el) return;
            el.style.display = '';
            el.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(id) {
            var el = qs('#' + id);
            if (!el) return;
            el.classList.remove('open');
            el.style.display = 'none';
            document.body.style.overflow = '';
        }

        function clearErrors() {
            qsa('.mg-field-error').forEach(function(el) {
                el.textContent = '';
            });
        }

        function setErrors(errors, suffix) {
            Object.entries(errors).forEach(function([field, msg]) {
                var el = qs('.error-' + field + '-' + suffix);
                if (el) el.textContent = msg;
            });
        }

        function postForm(url, formId) {
            return fetch(BASE_URL + url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(qs('#' + formId)),
            }).then(function(r) {
                return r.json();
            });
        }

        function postData(url, payload) {
            var fd = new FormData();
            Object.entries(payload).forEach(function([k, v]) {
                fd.append(k, v);
            });
            return fetch(BASE_URL + url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd,
            }).then(function(r) {
                return r.json();
            });
        }

        // ── Pagination state ──────────────────────────────────────────────────────
        var PER_PAGE = 10;
        var currentPage = 1;
        var filteredRows = [];
        var allRows = Array.from(qsa('#tblProduk tbody tr'));

        // ── Filter + Pagination core ──────────────────────────────────────────────
        var searchInput = qs('#mgSearch');
        var filterStatus = qs('#filterStatus');

        function applyFilters() {
            var q = searchInput ? searchInput.value.toLowerCase() : '';
            var status = filterStatus ? filterStatus.value.toLowerCase() : '';

            filteredRows = allRows.filter(function(tr) {
                if (tr.querySelector('td.empty-state') || tr.cells.length === 1) return false;
                var matchQ = !q || tr.textContent.toLowerCase().includes(q);
                var matchSt = !status || (tr.dataset.status || '').toLowerCase() === status;
                return matchQ && matchSt;
            });

            currentPage = 1;
            renderPage();
        }

        function renderPage() {
            var total = filteredRows.length;
            var totalPages = Math.max(1, Math.ceil(total / PER_PAGE));

            // Sembunyikan semua baris
            allRows.forEach(function(tr) {
                tr.style.display = 'none';
            });

            // Tampilkan baris halaman aktif
            var start = (currentPage - 1) * PER_PAGE;
            var end = Math.min(start + PER_PAGE, total);
            filteredRows.slice(start, end).forEach(function(tr) {
                tr.style.display = '';
            });

            // Empty state
            var emptyRow = allRows.find(function(tr) {
                return tr.querySelector('td.empty-state');
            });
            if (emptyRow) {
                emptyRow.style.display = total === 0 ? '' : 'none';
            }

            // Label sesuai filter status
            var statusVal = filterStatus ? filterStatus.value : '';
            var label = statusVal === 'aktif' ? 'produk aktif' :
                statusVal === 'nonaktif' ? 'produk nonaktif' :
                'produk';

            // Update info & badge
            var info = qs('#tableInfo');
            var badge = qs('#badgeCount');
            if (info) {
                info.textContent = total === 0 ?
                    'Tidak ada data ditemukan' :
                    'Menampilkan ' + (start + 1) + '–' + end + ' dari ' + total + ' ' + label;
            }
            if (badge) {
                badge.textContent = total + ' ' + label;
            }

            // Nomor baris sesuai halaman
            filteredRows.slice(start, end).forEach(function(tr, i) {
                var noEl = tr.querySelector('.row-no');
                if (noEl) noEl.textContent = start + i + 1;
            });

            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            var container = qs('#mgPagination');
            if (!container) return;
            container.innerHTML = '';
            if (totalPages <= 1) return;

            function makeBtn(label, page, isActive, isDisabled) {
                var btn = document.createElement('button');
                btn.className = 'pg-btn' + (isActive ? ' active' : '');
                btn.innerHTML = label;
                btn.disabled = !!isDisabled;
                if (!isDisabled && !isActive) {
                    btn.addEventListener('click', function() {
                        currentPage = page;
                        renderPage();
                        var tbl = qs('#tblProduk');
                        if (tbl) tbl.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    });
                }
                return btn;
            }

            function makeEllipsis() {
                var span = document.createElement('span');
                span.className = 'pg-ellipsis';
                span.textContent = '…';
                return span;
            }

            // Prev
            container.appendChild(makeBtn('<i class="bi bi-chevron-left"></i>', currentPage - 1, false, currentPage === 1));

            // Angka halaman dengan ellipsis
            var pages = [];
            if (totalPages <= 7) {
                for (var i = 1; i <= totalPages; i++) pages.push(i);
            } else {
                pages.push(1);
                if (currentPage > 3) pages.push('...');
                var from = Math.max(2, currentPage - 1);
                var to = Math.min(totalPages - 1, currentPage + 1);
                for (var j = from; j <= to; j++) pages.push(j);
                if (currentPage < totalPages - 2) pages.push('...');
                pages.push(totalPages);
            }

            pages.forEach(function(p) {
                if (p === '...') {
                    container.appendChild(makeEllipsis());
                } else {
                    container.appendChild(makeBtn(p, p, p === currentPage, false));
                }
            });

            // Next
            container.appendChild(makeBtn('<i class="bi bi-chevron-right"></i>', currentPage + 1, false, currentPage === totalPages));
        }

        // Inisialisasi
        applyFilters();
        if (searchInput) searchInput.addEventListener('input', applyFilters);
        if (filterStatus) filterStatus.addEventListener('change', applyFilters);

        // ── Modal wiring ──────────────────────────────────────────────────────────
        var pairs = [
            ['btnOpenAdd', 'overlayAdd', 'closeAdd', 'cancelAdd'],
            ['btnImport', 'overlayImport', 'closeImport', 'cancelImport'],
        ];
        pairs.forEach(function(p) {
            var opener = qs('#' + p[0]);
            var closer = qs('#' + p[2]);
            var canceler = qs('#' + p[3]);
            if (opener) opener.addEventListener('click', function() {
                openModal(p[1]);
            });
            if (closer) closer.addEventListener('click', function() {
                closeModal(p[1]);
            });
            if (canceler) canceler.addEventListener('click', function() {
                closeModal(p[1]);
            });
        });

        var closeEdit = qs('#closeEdit');
        var cancelEdit = qs('#cancelEdit');
        if (closeEdit) closeEdit.addEventListener('click', function() {
            closeModal('overlayEdit');
        });
        if (cancelEdit) cancelEdit.addEventListener('click', function() {
            closeModal('overlayEdit');
        });

        qsa('.mg-modal-overlay').forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) closeModal(this.id);
            });
        });

        // ── CREATE ────────────────────────────────────────────────────────────────
        var btnSave = qs('#btnSave');
        if (btnSave) {
            btnSave.addEventListener('click', function() {
                clearErrors();
                postForm('produk/simpan', 'formStore')
                    .then(function(res) {
                        if (res.status === 'error_validation') {
                            setErrors(res.errors, 'store');
                            return;
                        }
                        closeModal('overlayAdd');
                        Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                confirmButtonColor: '#2e6da4'
                            })
                            .then(function() {
                                location.reload();
                            });
                    })
                    .catch(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menghubungi server.'
                        });
                    });
            });
        }

        // ── GET DATA (edit) ───────────────────────────────────────────────────────
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btnEdit');
            if (!btn) return;
            postData('produk/getData', {
                    id: btn.dataset.id
                })
                .then(function(res) {
                    if (res.status === 'error') {
                        Swal.fire('Error', res.message, 'error');
                        return;
                    }
                    qs('#edit_id').value = res.id_produk;
                    qs('#edit_nama').value = res.nama_produk;
                    qs('#edit_status').value = res.is_active;
                    clearErrors();
                    openModal('overlayEdit');
                });
        });

        // ── UPDATE ────────────────────────────────────────────────────────────────
        var btnUpdate = qs('#btnUpdate');
        if (btnUpdate) {
            btnUpdate.addEventListener('click', function() {
                clearErrors();
                var id = qs('#edit_id').value;
                fetch(BASE_URL + 'produk/update/' + id, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(qs('#formEdit')),
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(res) {
                        if (res.status === 'error_validation') {
                            setErrors(res.errors, 'edit');
                            return;
                        }
                        closeModal('overlayEdit');
                        Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: res.message,
                                confirmButtonColor: '#2e6da4'
                            })
                            .then(function() {
                                location.reload();
                            });
                    });
            });
        }

        // ── DELETE single ─────────────────────────────────────────────────────────
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btnDelete');
            if (!btn) return;
            var id = btn.dataset.id;
            var nama = btn.dataset.nama;
            Swal.fire({
                title: 'Hapus produk ini?',
                html: '<b>' + nama + '</b><br><span style="font-size:13px;color:#888">Tindakan ini tidak dapat dibatalkan.</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e53e3e',
                cancelButtonColor: '#243B55',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
            }).then(function(r) {
                if (!r.isConfirmed) return;
                postData('produk/hapus/' + id, {})
                    .then(function(res) {
                        Swal.fire({
                                icon: 'success',
                                title: 'Dihapus!',
                                text: res.message,
                                confirmButtonColor: '#2e6da4'
                            })
                            .then(function() {
                                location.reload();
                            });
                    });
            });
        });

        // ── DELETE ALL ────────────────────────────────────────────────────────────
        var btnDeleteAll = qs('#btnDeleteAll');
        if (btnDeleteAll) {
            btnDeleteAll.addEventListener('click', function() {
                Swal.fire({
                    title: 'Hapus SEMUA produk?',
                    html: '<span style="color:#e53e3e;font-weight:600">Seluruh data produk akan dihapus permanen!</span><br><span style="font-size:13px;color:#888">Ketik <b>HAPUS SEMUA</b> untuk konfirmasi.</span>',
                    icon: 'warning',
                    input: 'text',
                    inputPlaceholder: 'Ketik HAPUS SEMUA',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#243B55',
                    confirmButtonText: 'Hapus Semua',
                    cancelButtonText: 'Batal',
                    preConfirm: function(val) {
                        if (val !== 'HAPUS SEMUA') {
                            Swal.showValidationMessage('Konfirmasi tidak sesuai.');
                        }
                    },
                }).then(function(r) {
                    if (!r.isConfirmed) return;
                    postData('produk/hapusSemua', {})
                        .then(function(res) {
                            Swal.fire({
                                    icon: 'success',
                                    title: 'Dihapus!',
                                    text: res.message,
                                    confirmButtonColor: '#2e6da4'
                                })
                                .then(function() {
                                    location.reload();
                                });
                        });
                });
            });
        }

        // ── IMPORT — drop zone ────────────────────────────────────────────────────
        var dropZone = qs('#dropZone');
        var fileInput = qs('#fileExcel');
        var dropContent = qs('#dropZoneContent');
        var dropSel = qs('#dropSelected');
        var selName = qs('#selectedFileName');
        var removeBtn = qs('#removeFile');

        function showFileSelected(name) {
            if (dropContent) dropContent.style.display = 'none';
            if (dropSel) dropSel.style.display = 'flex';
            if (selName) selName.textContent = name;
        }

        function clearFileSelected() {
            if (dropContent) dropContent.style.display = 'flex';
            if (dropSel) dropSel.style.display = 'none';
            if (fileInput) fileInput.value = '';
            var res = qs('#importResult');
            if (res) {
                res.style.display = 'none';
                res.innerHTML = '';
            }
        }

        if (dropZone) {
            dropZone.addEventListener('click', function(e) {
                if (e.target.closest('#removeFile')) return;
                fileInput && fileInput.click();
            });
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropZone.classList.add('drag-over');
            });
            dropZone.addEventListener('dragleave', function() {
                dropZone.classList.remove('drag-over');
            });
            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                dropZone.classList.remove('drag-over');
                var f = e.dataTransfer.files[0];
                if (f && fileInput) {
                    var dt = new DataTransfer();
                    dt.items.add(f);
                    fileInput.files = dt.files;
                    showFileSelected(f.name);
                }
            });
        }
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files[0]) showFileSelected(this.files[0].name);
            });
        }
        if (removeBtn) {
            removeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                clearFileSelected();
            });
        }

        // ── IMPORT — upload ───────────────────────────────────────────────────────
        var btnUpload = qs('#btnUpload');
        if (btnUpload) {
            btnUpload.addEventListener('click', function() {
                if (!fileInput || !fileInput.files[0]) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'File belum dipilih',
                        text: 'Pilih file Excel (.xlsx/.xls/.csv) terlebih dahulu.',
                        confirmButtonColor: '#2e6da4'
                    });
                    return;
                }

                var prog = qs('#importProgress');
                var fill = qs('#progressFill');
                var label = qs('#progressLabel');
                var res = qs('#importResult');

                if (prog) {
                    prog.style.display = '';
                    fill.style.width = '0%';
                }
                if (res) {
                    res.style.display = 'none';
                    res.innerHTML = '';
                }
                btnUpload.disabled = true;

                var pct = 0;
                var interval = setInterval(function() {
                    pct = Math.min(pct + Math.random() * 15, 85);
                    if (fill) fill.style.width = pct + '%';
                    if (label) label.textContent = 'Memproses… ' + Math.round(pct) + '%';
                }, 200);

                postForm('produk/prosesImport', 'formImport')
                    .then(function(data) {
                        clearInterval(interval);
                        if (fill) fill.style.width = '100%';
                        if (label) label.textContent = 'Selesai!';

                        var cls = data.skipped > 0 ? 'warning' : 'success';
                        var html = '<div style="font-weight:600;margin-bottom:4px">' + data.message + '</div>';
                        if (data.errors && data.errors.length) {
                            html += '<ul class="err-list">';
                            data.errors.forEach(function(e) {
                                html += '<li>' + e + '</li>';
                            });
                            html += '</ul>';
                        }
                        if (res) {
                            res.innerHTML = '<div class="import-result-box ' + cls + '">' + html + '</div>';
                            res.style.display = '';
                        }

                        if (data.inserted > 0) {
                            setTimeout(function() {
                                closeModal('overlayImport');
                                Swal.fire({
                                        icon: 'success',
                                        title: 'Import Selesai!',
                                        text: data.message,
                                        confirmButtonColor: '#2e6da4'
                                    })
                                    .then(function() {
                                        location.reload();
                                    });
                            }, 1200);
                        }
                    })
                    .catch(function() {
                        clearInterval(interval);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat mengupload file.'
                        });
                    })
                    .finally(function() {
                        btnUpload.disabled = false;
                    });
            });
        }

        // ── Reset import modal saat ditutup ───────────────────────────────────────
        var overlayImport = qs('#overlayImport');
        if (overlayImport) {
            overlayImport.addEventListener('transitionend', function() {
                if (!this.classList.contains('open')) {
                    clearFileSelected();
                    var prog = qs('#importProgress');
                    if (prog) prog.style.display = 'none';
                }
            });
        }

    });
</script>