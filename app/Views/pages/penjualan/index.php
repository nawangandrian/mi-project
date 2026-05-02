<?php

/**
 * View  : pages/penjualan/index.php
 * Modul : Manajemen Data Penjualan — Mi Store Kudus
 */
?>

<div class="content-wrapper-inner">

    <!-- ── Page Header ── -->
    <div class="mg-page-header">
        <div>
            <h1 class="page-title">Data Penjualan</h1>
            <p class="page-subtitle">Kelola transaksi penjualan Mi Store Kudus</p>
        </div>
        <div class="header-actions">
            <button class="btn-mg btn-danger-outline" id="btnDeleteAll" title="Hapus semua transaksi">
                <i class="bi bi-trash3-fill"></i> Hapus Semua
            </button>
            <button class="btn-mg btn-success-outline" id="btnImport" title="Import dari Excel">
                <i class="bi bi-file-earmark-arrow-up-fill"></i> Import
            </button>
            <a href="<?= base_url('penjualan/export') ?>" class="btn-mg btn-export" title="Export ke Excel">
                <i class="bi bi-file-earmark-arrow-down-fill"></i> Export
            </a>
            <button class="btn-mg btn-primary-mg" id="btnOpenAdd">
                <i class="bi bi-plus-circle-fill"></i> Tambah Transaksi
            </button>
        </div>
    </div>

    <!-- ── Stats Row ── -->
    <?php
    $summary  = $summary ?? ['total_transaksi' => 0, 'total_qty' => 0, 'total_omzet' => 0, 'total_promo' => 0];
    $totalRec = count($penjualans ?? []);
    ?>
    <div class="mg-stats-row">
        <div class="stat-card accent-cyan">
            <div class="stat-icon" style="background:rgba(0,151,184,0.12);color:var(--accent-cyan)">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div class="stat-value"><?= number_format($summary['total_transaksi']) ?></div>
            <div class="stat-label">Total Transaksi</div>
        </div>
        <div class="stat-card accent-blue">
            <div class="stat-icon" style="background:rgba(46,109,164,0.12);color:var(--mg-light)">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div class="stat-value"><?= number_format($summary['total_qty']) ?></div>
            <div class="stat-label">Total Unit Terjual</div>
        </div>
        <div class="stat-card accent-green">
            <div class="stat-icon" style="background:rgba(16,183,127,0.12);color:var(--accent-green)">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div class="stat-value" style="font-size:16px">
                Rp <?= number_format($summary['total_omzet'], 0, ',', '.') ?>
            </div>
            <div class="stat-label">Total Omzet</div>
        </div>
        <div class="stat-card accent-yellow">
            <div class="stat-icon" style="background:rgba(212,160,23,0.12);color:var(--accent-yellow)">
                <i class="bi bi-percent"></i>
            </div>
            <div class="stat-value"><?= number_format($summary['total_promo']) ?></div>
            <div class="stat-label">Transaksi Promo</div>
        </div>
    </div>

    <!-- ── Table Card ── -->
    <div class="card-mg">
        <div class="mg-card-header">
            <div class="mg-card-title-group">
                <span class="section-title">Data Transaksi</span>
                <span class="badge-mg badge-cyan" id="badgeCount"><?= $totalRec ?> transaksi</span>
            </div>
            <div class="mg-card-toolbar">
                <!-- Filter Promo -->
                <div class="mg-filter-wrap">
                    <select id="filterPromo" class="form-mg form-mg-sm">
                        <option value="">Semua</option>
                        <option value="promo">Promo</option>
                        <option value="regular">Regular</option>
                    </select>
                </div>
                <!-- Search -->
                <div class="mg-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="mgSearch" placeholder="Cari nota / produk…">
                </div>
            </div>
        </div>

        <div class="mg-table-wrap">
            <table class="table-mg" id="tblPenjualan">
                <thead>
                    <tr>
                        <th style="width:46px">No</th>
                        <th style="width:120px">No. Nota</th>
                        <th>Nama Produk</th>
                        <th style="width:60px;text-align:center">Qty</th>
                        <th style="width:145px">Harga Satuan</th>
                        <th style="width:155px">Subtotal</th>
                        <th style="width:120px">Tanggal</th>
                        <th style="width:90px;text-align:center">Promo</th>
                        <th style="width:110px;text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($penjualans)): $no = 1; ?>
                        <?php foreach ($penjualans as $p): ?>
                            <?php $subtotal = (int)$p['qty'] * (int)$p['harga']; ?>
                            <tr data-promo="<?= $p['promo'] ? 'promo' : 'regular' ?>">
                                <td><span class="row-no"><?= $no++ ?></span></td>
                                <td><code class="id-code nota-code"><?= esc($p['no_nota']) ?></code></td>
                                <td>
                                    <div class="produk-cell">
                                        <div class="produk-icon">
                                            <i class="bi bi-phone-fill"></i>
                                        </div>
                                        <div>
                                            <div class="produk-name"><?= esc($p['nama_produk']) ?></div>
                                            <?php if ($p['id_produk']): ?>
                                                <div class="produk-id-ref"><?= esc($p['id_produk']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align:center">
                                    <span class="qty-badge"><?= $p['qty'] ?></span>
                                </td>
                                <td>
                                    <span class="price-tag">
                                        <span class="price-currency">Rp</span>
                                        <?= number_format($p['harga'], 0, ',', '.') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="price-tag subtotal-tag">
                                        <span class="price-currency">Rp</span>
                                        <?= number_format($subtotal, 0, ',', '.') ?>
                                    </span>
                                </td>
                                <td class="date-cell">
                                    <i class="bi bi-calendar3" style="font-size:11px;margin-right:5px"></i>
                                    <?= date('d M Y', strtotime($p['tanggal'])) ?>
                                </td>
                                <td style="text-align:center">
                                    <?php if ($p['promo']): ?>
                                        <span class="badge-mg badge-green"><i class="bi bi-tags-fill"></i> YA</span>
                                    <?php else: ?>
                                        <span class="badge-mg badge-gray"><i class="bi bi-dash-circle"></i> TIDAK</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <button class="action-btn edit-btn btnEdit"
                                            data-id="<?= esc($p['id_penjualan']) ?>" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="action-btn delete-btn btnDelete"
                                            data-id="<?= esc($p['id_penjualan']) ?>"
                                            data-nota="<?= esc($p['no_nota']) ?>"
                                            data-nama="<?= esc($p['nama_produk']) ?>" title="Hapus">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="empty-state">
                                <i class="bi bi-receipt" style="font-size:38px;color:var(--text-placeholder);display:block;margin-bottom:10px"></i>
                                Belum ada data penjualan
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mg-table-footer">
            <span class="table-info" id="tableInfo">Menampilkan 1–10 dari <?= $totalRec ?> transaksi</span>
            <div class="mg-pagination" id="mgPagination"></div>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     MODAL TAMBAH TRANSAKSI
═══════════════════════════════════════════════════════════ -->
<div class="mg-modal-overlay" id="overlayAdd">
    <div class="mg-modal">
        <div class="mg-modal-header" style="--modal-accent:var(--mg-glow)">
            <div class="mg-modal-icon" style="background:rgba(46,109,164,0.15);color:var(--mg-light)">
                <i class="bi bi-plus-circle-fill"></i>
            </div>
            <div>
                <div class="mg-modal-title">Tambah Transaksi</div>
                <div class="mg-modal-sub">Catat transaksi penjualan baru</div>
            </div>
            <button class="mg-modal-close" id="closeAdd" type="button"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="mg-modal-body">
            <form id="formStore" autocomplete="off">
                <?= csrf_field() ?>

                <!-- No. Nota + Tanggal (2 kolom) -->
                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">No. Nota</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-receipt"></i>
                            <input type="text" name="no_nota" class="form-mg" placeholder="Contoh: 85700">
                        </div>
                        <small class="mg-field-error error-no_nota-store"></small>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Tanggal</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-calendar3"></i>
                            <input type="date" name="tanggal" class="form-mg" value="<?= date('Y-m-d') ?>">
                        </div>
                        <small class="mg-field-error error-tanggal-store"></small>
                    </div>
                </div>

                <!-- Pilih Produk (dropdown) -->
                <div class="mg-field">
                    <label class="form-label-mg">Produk <span class="label-opt">(dari katalog)</span></label>
                    <div class="mg-select-wrap">
                        <i class="bi bi-phone"></i>
                        <select name="id_produk" id="store_id_produk" class="form-mg">
                            <option value="">-- Pilih produk dari katalog --</option>
                            <?php foreach ($produks as $pr): ?>
                                <option value="<?= esc($pr['id_produk']) ?>"
                                    data-nama="<?= esc($pr['nama_produk']) ?>">
                                    <?= esc($pr['nama_produk']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="bi bi-chevron-down mg-select-arrow"></i>
                    </div>
                    <small class="mg-field-info">Pilih dari katalog untuk auto-isi nama & harga, atau isi manual di bawah.</small>
                </div>

                <!-- Nama Produk manual -->
                <div class="mg-field">
                    <label class="form-label-mg">Nama Produk</label>
                    <div class="mg-input-icon">
                        <i class="bi bi-tag"></i>
                        <input type="text" name="nama_produk" id="store_nama_produk" class="form-mg" placeholder="Nama produk (otomatis atau isi manual)">
                    </div>
                    <small class="mg-field-error error-nama_produk-store"></small>
                </div>

                <!-- Harga + Qty (2 kolom) -->
                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">Harga Satuan (Rp)</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-cash"></i>
                            <input type="number" name="harga" id="store_harga" class="form-mg" placeholder="0" min="1">
                        </div>
                        <small class="mg-field-error error-harga-store"></small>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Qty</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-123"></i>
                            <input type="number" name="qty" id="store_qty" class="form-mg" placeholder="1" min="1" value="1">
                        </div>
                        <small class="mg-field-error error-qty-store"></small>
                    </div>
                </div>

                <!-- Subtotal live preview -->
                <div class="subtotal-preview" id="storeSubtotalPreview">
                    <i class="bi bi-calculator"></i>
                    Subtotal: <strong id="storeSubtotalVal">Rp 0</strong>
                </div>

                <!-- Promo -->
                <div class="mg-field">
                    <label class="form-label-mg">Promo?</label>
                    <div class="mg-select-wrap">
                        <i class="bi bi-percent"></i>
                        <select name="promo" class="form-mg">
                            <option value="0">TIDAK</option>
                            <option value="1">YA</option>
                        </select>
                        <i class="bi bi-chevron-down mg-select-arrow"></i>
                    </div>
                    <small class="mg-field-error error-promo-store"></small>
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
     MODAL EDIT TRANSAKSI
═══════════════════════════════════════════════════════════ -->
<div class="mg-modal-overlay" id="overlayEdit">
    <div class="mg-modal">
        <div class="mg-modal-header" style="--modal-accent:#d4a017">
            <div class="mg-modal-icon" style="background:rgba(212,160,23,0.15);color:var(--accent-yellow)">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <div class="mg-modal-title">Edit Transaksi</div>
                <div class="mg-modal-sub">Perbarui data penjualan</div>
            </div>
            <button class="mg-modal-close" id="closeEdit" type="button"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="mg-modal-body">
            <form id="formEdit" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="id_penjualan" id="edit_id">

                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">No. Nota</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-receipt"></i>
                            <input type="text" name="no_nota" id="edit_no_nota" class="form-mg" placeholder="No. Nota">
                        </div>
                        <small class="mg-field-error error-no_nota-edit"></small>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Tanggal</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-calendar3"></i>
                            <input type="date" name="tanggal" id="edit_tanggal" class="form-mg">
                        </div>
                        <small class="mg-field-error error-tanggal-edit"></small>
                    </div>
                </div>

                <div class="mg-field">
                    <label class="form-label-mg">Produk <span class="label-opt">(dari katalog)</span></label>
                    <div class="mg-select-wrap">
                        <i class="bi bi-phone"></i>
                        <select name="id_produk" id="edit_id_produk" class="form-mg">
                            <option value="">-- Pilih produk dari katalog --</option>
                            <?php foreach ($produks as $pr): ?>
                                <option value="<?= esc($pr['id_produk']) ?>"
                                    data-nama="<?= esc($pr['nama_produk']) ?>">
                                    <?= esc($pr['nama_produk']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="bi bi-chevron-down mg-select-arrow"></i>
                    </div>
                </div>

                <div class="mg-field">
                    <label class="form-label-mg">Nama Produk</label>
                    <div class="mg-input-icon">
                        <i class="bi bi-tag"></i>
                        <input type="text" name="nama_produk" id="edit_nama_produk" class="form-mg" placeholder="Nama produk">
                    </div>
                    <small class="mg-field-error error-nama_produk-edit"></small>
                </div>

                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">Harga Satuan (Rp)</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-cash"></i>
                            <input type="number" name="harga" id="edit_harga" class="form-mg" placeholder="0" min="1">
                        </div>
                        <small class="mg-field-error error-harga-edit"></small>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Qty</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-123"></i>
                            <input type="number" name="qty" id="edit_qty" class="form-mg" placeholder="1" min="1">
                        </div>
                        <small class="mg-field-error error-qty-edit"></small>
                    </div>
                </div>

                <!-- Subtotal live preview -->
                <div class="subtotal-preview" id="editSubtotalPreview">
                    <i class="bi bi-calculator"></i>
                    Subtotal: <strong id="editSubtotalVal">Rp 0</strong>
                </div>

                <div class="mg-field">
                    <label class="form-label-mg">Promo?</label>
                    <div class="mg-select-wrap">
                        <i class="bi bi-percent"></i>
                        <select name="promo" id="edit_promo" class="form-mg">
                            <option value="0">TIDAK</option>
                            <option value="1">YA</option>
                        </select>
                        <i class="bi bi-chevron-down mg-select-arrow"></i>
                    </div>
                    <small class="mg-field-error error-promo-edit"></small>
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
                        <span class="import-col-badge">A: no_nota</span>
                        <span class="import-col-badge">B: nama_produk</span>
                        <span class="import-col-badge">C: qty</span>
                        <span class="import-col-badge">D: harga</span>
                        <span class="import-col-badge">E: tanggal</span>
                        <span class="import-col-badge">F: promo (1/0)</span>
                    </div>
                    <div style="font-size:12px;margin-top:6px;color:var(--text-muted)">
                        Baris pertama dianggap sebagai header dan akan dilewati. Tanggal format YYYY-MM-DD.
                    </div>
                </div>
            </div>

            <form id="formImport" enctype="multipart/form-data" autocomplete="off">
                <?= csrf_field() ?>

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

                <div id="importProgress" style="display:none;margin-top:14px">
                    <div class="mg-progress-bar">
                        <div class="mg-progress-fill" id="progressFill"></div>
                    </div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px;text-align:center" id="progressLabel">
                        Mengupload…
                    </div>
                </div>

                <div id="importResult" style="display:none;margin-top:14px"></div>
            </form>

            <div class="import-template-row">
                <i class="bi bi-download" style="color:var(--accent-cyan)"></i>
                <span>Belum punya template?</span>
                <a href="<?= base_url('penjualan/downloadTemplate') ?>" class="template-link">
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

    /* ── Buttons ── */
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

    /* ── Card header + toolbar ── */
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
        min-width: 860px;
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
        background: rgba(0, 151, 184, 0.08);
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid rgba(0, 151, 184, 0.15);
        white-space: nowrap;
    }

    .nota-code {
        color: var(--accent-yellow);
        background: rgba(212, 160, 23, 0.08);
        border-color: rgba(212, 160, 23, 0.18);
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

    .produk-id-ref {
        font-size: 10.5px;
        color: var(--text-placeholder);
        font-family: 'Courier New', monospace;
    }

    .qty-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 22px;
        padding: 0 6px;
        background: rgba(74, 159, 212, 0.1);
        color: var(--mg-light);
        border: 1px solid rgba(74, 159, 212, 0.2);
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
    }

    .price-tag {
        display: inline-flex;
        align-items: baseline;
        gap: 3px;
        font-weight: 600;
        color: var(--accent-green);
        font-size: 13px;
    }

    .subtotal-tag {
        color: var(--accent-cyan);
    }

    .price-currency {
        font-size: 10.5px;
        font-weight: 500;
    }

    .date-cell {
        font-size: 12.5px;
        color: var(--text-muted);
    }

    .badge-gray {
        background: rgba(128, 128, 128, 0.1);
        color: var(--text-muted);
        border: 1px solid rgba(128, 128, 128, 0.15);
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

    /* ── Modal base ── */
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
        max-width: 500px;
        box-shadow: 0 24px 60px rgba(14, 23, 36, 0.25), 0 4px 16px rgba(14, 23, 36, 0.1);
        animation: mgSlideUp 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }

    .mg-modal-lg {
        max-width: 560px;
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

    .mg-row-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    @media(max-width:480px) {
        .mg-row-2 {
            grid-template-columns: 1fr;
        }
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

    .mg-field-info {
        font-size: 11px;
        color: var(--text-placeholder);
    }

    .label-opt {
        font-size: 11px;
        font-weight: 400;
        color: var(--text-muted);
        text-transform: none;
        letter-spacing: 0;
    }

    /* ── Subtotal preview ── */
    .subtotal-preview {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        margin-bottom: 14px;
        background: rgba(0, 151, 184, 0.07);
        border: 1px solid rgba(0, 151, 184, 0.18);
        border-radius: var(--radius-sm);
        font-size: 13px;
        color: var(--text-muted);
    }

    .subtotal-preview strong {
        color: var(--accent-cyan);
        font-size: 14px;
    }

    /* ── Import modal ── */
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
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 11px;
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

    /* Tambahkan di bagian bawah <style> yang sudah ada */

/* ═══════════════════════════════════════════════
   FIX CORE — Mobile overflow & clip kiri
═══════════════════════════════════════════════ */
@media (max-width: 768px) {

    /* Pastikan main-content tidak ada margin sisa sidebar */
    #main-content {
        margin-left: 0 !important;
        width: 100% !important;
        overflow-x: hidden !important;
    }

    /* Content wrapper full-width, padding lebih kecil */
    .content-wrapper {
        padding: 16px 12px !important;
        overflow-x: hidden;
    }

    /* Wrapper inner juga full-width */
    .content-wrapper-inner {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    /* Card tidak boleh keluar dari parent */
    .card-mg {
        width: 100%;
        min-width: 0;
        padding: 14px 12px;
        border-radius: var(--radius-md);
    }

    /* Stat cards: padding lebih ketat */
    .stat-card {
        padding: 12px 10px !important;
        min-width: 0;
    }

    /* Table wrap: pastikan scroll horizontal aktif */
    .mg-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Kolom tanggal: sembunyikan di mobile (terlalu lebar) */
    .date-cell,
    .table-mg thead th:nth-child(5) {
        display: none;
    }

    /* ID produk: lebih compact */
    .id-code {
        font-size: 10px;
        padding: 2px 4px;
        max-width: 90px;
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Produk name: boleh wrap */
    .produk-name {
        font-size: 12px;
        white-space: normal;
        word-break: break-word;
    }

    /* Produk icon: lebih kecil */
    .produk-icon {
        width: 26px;
        height: 26px;
        font-size: 12px;
        flex-shrink: 0;
    }

    /* Badge di tabel: kompak */
    .badge-mg {
        font-size: 10px;
        padding: 2px 6px;
        white-space: nowrap;
    }

    /* Row number: lebih kecil */
    .row-no {
        width: 20px;
        height: 20px;
        font-size: 10px;
    }

    /* Action buttons: rapat */
    .action-group {
        gap: 5px;
    }

    .action-btn {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
}

/* ═══════════════════════════════════════════════
   FIX — Mobile kecil ≤ 480px
═══════════════════════════════════════════════ */
@media (max-width: 480px) {

    .content-wrapper {
        padding: 12px 10px !important;
    }

    /* Sembunyikan juga kolom No di layar sangat kecil */
    .table-mg thead th:first-child,
    .table-mg tbody td:first-child {
        display: none;
    }

    /* Table min-width lebih kecil agar muat tanpa scroll */
    .table-mg {
        min-width: 320px;
    }
}
</style>


<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════ -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        const qs = sel => document.querySelector(sel);
        const qsa = sel => document.querySelectorAll(sel);

        // ── Utilities ─────────────────────────────────────────────────────────────
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

        function clearErrors() {
            qsa('.mg-field-error').forEach(el => el.textContent = '');
        }

        function setErrors(errors, suffix) {
            Object.entries(errors).forEach(([field, msg]) => {
                const el = qs('.error-' + field + '-' + suffix);
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
            }).then(r => r.json());
        }

        function postData(url, payload) {
            const fd = new FormData();
            Object.entries(payload).forEach(([k, v]) => fd.append(k, v));
            return fetch(BASE_URL + url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd,
            }).then(r => r.json());
        }

        function formatRp(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID');
        }

        // ── Pagination state ──────────────────────────────────────────────────────
        const PER_PAGE = 10;
        let currentPage = 1;
        let filteredRows = []; // array of <tr> elements yang lolos filter

        // ── Filter + Pagination core ──────────────────────────────────────────────
        const searchInput = qs('#mgSearch');
        const filterPromo = qs('#filterPromo');
        const allRows = Array.from(qsa('#tblPenjualan tbody tr'));

        function applyFilters() {
            const q = searchInput ? searchInput.value.toLowerCase() : '';
            const promo = filterPromo ? filterPromo.value : '';

            // Tentukan baris yang lolos filter
            filteredRows = allRows.filter(tr => {
                // Abaikan baris empty-state
                if (tr.querySelector('.empty-state') || tr.cells.length === 1) return false;
                const matchQ = !q || tr.textContent.toLowerCase().includes(q);
                const matchP = !promo || (tr.dataset.promo || '') === promo;
                return matchQ && matchP;
            });

            currentPage = 1; // reset ke halaman 1 saat filter berubah
            renderPage();
        }

        function renderPage() {
            const total = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));

            // Sembunyikan semua baris dulu
            allRows.forEach(tr => tr.style.display = 'none');

            // Tampilkan baris halaman saat ini
            const start = (currentPage - 1) * PER_PAGE;
            const end = Math.min(start + PER_PAGE, total);
            filteredRows.slice(start, end).forEach(tr => tr.style.display = '');

            // Tampilkan empty-state jika tidak ada hasil
            const emptyRow = allRows.find(tr => tr.querySelector('td.empty-state'));
            if (emptyRow) {
                emptyRow.style.display = total === 0 ? '' : 'none';
            }

            // Update info teks
            const info = qs('#tableInfo');
            const badge = qs('#badgeCount');
            const promo = filterPromo ? filterPromo.value : '';
            const label = promo === 'promo' ? 'promo' :
                promo === 'regular' ? 'regular' :
                'transaksi';

            if (info) {
                if (total === 0) {
                    info.textContent = 'Tidak ada data ditemukan';
                } else {
                    info.textContent = `Menampilkan ${start + 1}–${end} dari ${total} ${label}`;
                }
            }
            if (badge) {
                badge.textContent = `${total} ${label}`;
            }

            // Render nomor baris ulang sesuai halaman
            filteredRows.slice(start, end).forEach((tr, i) => {
                const noEl = tr.querySelector('.row-no');
                if (noEl) noEl.textContent = start + i + 1;
            });

            // Render tombol pagination
            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            const container = qs('#mgPagination');
            if (!container) return;
            container.innerHTML = '';

            if (totalPages <= 1) return;

            // Helper buat tombol
            function makeBtn(label, page, isActive = false, isDisabled = false) {
                const btn = document.createElement('button');
                btn.className = 'pg-btn' + (isActive ? ' active' : '');
                btn.innerHTML = label;
                btn.disabled = isDisabled;
                if (!isDisabled && !isActive) {
                    btn.addEventListener('click', () => {
                        currentPage = page;
                        renderPage();
                        // Scroll ke atas tabel
                        qs('#tblPenjualan')?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    });
                }
                return btn;
            }

            function makeEllipsis() {
                const span = document.createElement('span');
                span.className = 'pg-ellipsis';
                span.textContent = '…';
                return span;
            }

            // Prev
            container.appendChild(makeBtn('<i class="bi bi-chevron-left"></i>', currentPage - 1, false, currentPage === 1));

            // Halaman angka — tampilkan maks 5 tombol dengan ellipsis
            const pages = [];
            if (totalPages <= 7) {
                for (let i = 1; i <= totalPages; i++) pages.push(i);
            } else {
                pages.push(1);
                if (currentPage > 3) pages.push('...');
                for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
                    pages.push(i);
                }
                if (currentPage < totalPages - 2) pages.push('...');
                pages.push(totalPages);
            }

            pages.forEach(p => {
                if (p === '...') {
                    container.appendChild(makeEllipsis());
                } else {
                    container.appendChild(makeBtn(p, p, p === currentPage));
                }
            });

            // Next
            container.appendChild(makeBtn('<i class="bi bi-chevron-right"></i>', currentPage + 1, false, currentPage === totalPages));
        }

        // Inisialisasi awal
        applyFilters();

        if (searchInput) searchInput.addEventListener('input', applyFilters);
        if (filterPromo) filterPromo.addEventListener('change', applyFilters);

        // ── Modal wiring ──────────────────────────────────────────────────────────
        [
            ['btnOpenAdd', 'overlayAdd', 'closeAdd', 'cancelAdd'],
            ['btnImport', 'overlayImport', 'closeImport', 'cancelImport'],
        ].forEach(([opener, overlay, closer, canceler]) => {
            qs('#' + opener)?.addEventListener('click', () => openModal(overlay));
            qs('#' + closer)?.addEventListener('click', () => closeModal(overlay));
            qs('#' + canceler)?.addEventListener('click', () => closeModal(overlay));
        });
        qs('#closeEdit')?.addEventListener('click', () => closeModal('overlayEdit'));
        qs('#cancelEdit')?.addEventListener('click', () => closeModal('overlayEdit'));

        qsa('.mg-modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', e => {
                if (e.target === overlay) closeModal(overlay.id);
            });
        });

        // ── Auto-fill nama saat pilih produk dari katalog ─────────────────────────
        const storeIdProduk = qs('#store_id_produk');
        const storeNama = qs('#store_nama_produk');
        const storeHarga = qs('#store_harga');
        const storeQty = qs('#store_qty');
        const storeSubtotal = qs('#storeSubtotalVal');

        function calcSubtotal(hargaEl, qtyEl, subtotalEl) {
            const h = parseFloat(hargaEl?.value || 0);
            const q = parseFloat(qtyEl?.value || 0);
            if (subtotalEl) subtotalEl.textContent = formatRp(h * q);
        }

        storeIdProduk?.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (this.value && storeNama) {
                storeNama.value = opt.dataset.nama || '';
            }
            calcSubtotal(storeHarga, storeQty, storeSubtotal);
        });
        storeHarga?.addEventListener('input', () => calcSubtotal(storeHarga, storeQty, storeSubtotal));
        storeQty?.addEventListener('input', () => calcSubtotal(storeHarga, storeQty, storeSubtotal));

        const editHarga = qs('#edit_harga');
        const editQty = qs('#edit_qty');
        const editSubtotal = qs('#editSubtotalVal');
        editHarga?.addEventListener('input', () => calcSubtotal(editHarga, editQty, editSubtotal));
        editQty?.addEventListener('input', () => calcSubtotal(editHarga, editQty, editSubtotal));

        const editIdProduk = qs('#edit_id_produk');
        const editNama = qs('#edit_nama_produk');
        editIdProduk?.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (this.value && editNama) {
                editNama.value = opt.dataset.nama || '';
            }
            calcSubtotal(editHarga, editQty, editSubtotal);
        });

        // ── CREATE ────────────────────────────────────────────────────────────────
        qs('#btnSave')?.addEventListener('click', function() {
            clearErrors();
            postForm('penjualan/simpan', 'formStore')
                .then(res => {
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
                        .then(() => location.reload());
                })
                .catch(() => Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menghubungi server.'
                }));
        });

        // ── GET DATA (edit) ───────────────────────────────────────────────────────
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btnEdit');
            if (!btn) return;
            postData('penjualan/getData', {
                    id: btn.dataset.id
                })
                .then(res => {
                    if (res.status === 'error') {
                        Swal.fire('Error', res.message, 'error');
                        return;
                    }
                    qs('#edit_id').value = res.id_penjualan;
                    qs('#edit_no_nota').value = res.no_nota;
                    qs('#edit_tanggal').value = res.tanggal;
                    qs('#edit_nama_produk').value = res.nama_produk;
                    qs('#edit_harga').value = res.harga;
                    qs('#edit_qty').value = res.qty;
                    qs('#edit_promo').value = res.promo;
                    if (qs('#edit_id_produk') && res.id_produk) {
                        qs('#edit_id_produk').value = res.id_produk;
                    }
                    calcSubtotal(qs('#edit_harga'), qs('#edit_qty'), qs('#editSubtotalVal'));
                    clearErrors();
                    openModal('overlayEdit');
                });
        });

        // ── UPDATE ────────────────────────────────────────────────────────────────
        qs('#btnUpdate')?.addEventListener('click', function() {
            clearErrors();
            const id = qs('#edit_id').value;
            fetch(BASE_URL + 'penjualan/update/' + id, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new FormData(qs('#formEdit')),
                })
                .then(r => r.json())
                .then(res => {
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
                        .then(() => location.reload());
                });
        });

        // ── DELETE SINGLE ─────────────────────────────────────────────────────────
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btnDelete');
            if (!btn) return;
            Swal.fire({
                title: 'Hapus transaksi ini?',
                html: `Nota: <b>${btn.dataset.nota}</b><br><span style="font-size:13px;color:#888">${btn.dataset.nama}</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e53e3e',
                cancelButtonColor: '#243B55',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
            }).then(r => {
                if (!r.isConfirmed) return;
                postData('penjualan/hapus/' + btn.dataset.id, {})
                    .then(res => {
                        Swal.fire({
                                icon: 'success',
                                title: 'Dihapus!',
                                text: res.message,
                                confirmButtonColor: '#2e6da4'
                            })
                            .then(() => location.reload());
                    });
            });
        });

        // ── DELETE ALL ────────────────────────────────────────────────────────────
        qs('#btnDeleteAll')?.addEventListener('click', function() {
            Swal.fire({
                title: 'Hapus SEMUA data penjualan?',
                html: '<span style="color:#e53e3e;font-weight:600">Seluruh transaksi akan dihapus permanen!</span><br><span style="font-size:13px;color:#888">Ketik <b>HAPUS SEMUA</b> untuk konfirmasi.</span>',
                icon: 'warning',
                input: 'text',
                inputPlaceholder: 'Ketik HAPUS SEMUA',
                showCancelButton: true,
                confirmButtonColor: '#e53e3e',
                cancelButtonColor: '#243B55',
                confirmButtonText: 'Hapus Semua',
                cancelButtonText: 'Batal',
                preConfirm: val => {
                    if (val !== 'HAPUS SEMUA') Swal.showValidationMessage('Konfirmasi tidak sesuai.');
                },
            }).then(r => {
                if (!r.isConfirmed) return;
                postData('penjualan/hapusSemua', {})
                    .then(res => {
                        Swal.fire({
                                icon: 'success',
                                title: 'Dihapus!',
                                text: res.message,
                                confirmButtonColor: '#2e6da4'
                            })
                            .then(() => location.reload());
                    });
            });
        });

        // ── IMPORT — drop zone ────────────────────────────────────────────────────
        const dropZone = qs('#dropZone');
        const fileInput = qs('#fileExcel');
        const dropContent = qs('#dropZoneContent');
        const dropSel = qs('#dropSelected');
        const selName = qs('#selectedFileName');
        const removeBtn = qs('#removeFile');

        function showFileSelected(name) {
            dropContent && (dropContent.style.display = 'none');
            dropSel && (dropSel.style.display = 'flex');
            selName && (selName.textContent = name);
        }

        function clearFileSelected() {
            dropContent && (dropContent.style.display = 'flex');
            dropSel && (dropSel.style.display = 'none');
            if (fileInput) fileInput.value = '';
            const res = qs('#importResult');
            if (res) {
                res.style.display = 'none';
                res.innerHTML = '';
            }
        }

        dropZone?.addEventListener('click', e => {
            if (e.target.closest('#removeFile')) return;
            fileInput?.click();
        });
        dropZone?.addEventListener('dragover', e => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
        dropZone?.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
        dropZone?.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            const f = e.dataTransfer.files[0];
            if (f && fileInput) {
                const dt = new DataTransfer();
                dt.items.add(f);
                fileInput.files = dt.files;
                showFileSelected(f.name);
            }
        });
        fileInput?.addEventListener('change', function() {
            if (this.files[0]) showFileSelected(this.files[0].name);
        });
        removeBtn?.addEventListener('click', e => {
            e.stopPropagation();
            clearFileSelected();
        });

        // ── IMPORT — upload ───────────────────────────────────────────────────────
        qs('#btnUpload')?.addEventListener('click', function() {
            if (!fileInput?.files[0]) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File belum dipilih',
                    text: 'Pilih file Excel terlebih dahulu.',
                    confirmButtonColor: '#2e6da4'
                });
                return;
            }

            const prog = qs('#importProgress');
            const fill = qs('#progressFill');
            const label = qs('#progressLabel');
            const res = qs('#importResult');

            prog && (prog.style.display = '');
            fill && (fill.style.width = '0%');
            res && (res.style.display = 'none', res.innerHTML = '');
            this.disabled = true;

            let pct = 0;
            const interval = setInterval(() => {
                pct = Math.min(pct + Math.random() * 15, 85);
                if (fill) fill.style.width = pct + '%';
                if (label) label.textContent = 'Memproses… ' + Math.round(pct) + '%';
            }, 200);

            const btn = this;
            postForm('penjualan/prosesImport', 'formImport')
                .then(data => {
                    clearInterval(interval);
                    if (fill) fill.style.width = '100%';
                    if (label) label.textContent = 'Selesai!';

                    const cls = data.skipped > 0 ? 'warning' : 'success';
                    let html = `<div style="font-weight:600;margin-bottom:4px">${data.message}</div>`;
                    if (data.errors?.length) {
                        html += '<ul class="err-list">' + data.errors.map(e => `<li>${e}</li>`).join('') + '</ul>';
                    }
                    if (res) {
                        res.innerHTML = `<div class="import-result-box ${cls}">${html}</div>`;
                        res.style.display = '';
                    }

                    if (data.inserted > 0) {
                        setTimeout(() => {
                            closeModal('overlayImport');
                            Swal.fire({
                                    icon: 'success',
                                    title: 'Import Selesai!',
                                    text: data.message,
                                    confirmButtonColor: '#2e6da4'
                                })
                                .then(() => location.reload());
                        }, 1200);
                    }
                })
                .catch(() => Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan saat mengupload file.'
                }))
                .finally(() => btn.disabled = false);
        });

        qs('#overlayImport')?.addEventListener('transitionend', function() {
            if (!this.classList.contains('open')) {
                clearFileSelected();
                const prog = qs('#importProgress');
                if (prog) prog.style.display = 'none';
            }
        });

    });
</script>