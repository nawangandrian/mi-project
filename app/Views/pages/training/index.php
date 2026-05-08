<?php
/**
 * View  : pages/training/index.php
 * Modul : Manajemen Data Training — Mi Store Kudus
 */
?>

<div class="content-wrapper-inner">

    <!-- ── Page Header ── -->
    <div class="mg-page-header">
        <div class="page-header-left">
            <h1 class="page-title">Data Training</h1>
            <p class="page-subtitle">Dataset fitur untuk melatih model Random Forest</p>
        </div>
        <div class="header-actions">
            <button class="btn-mg btn-danger-outline" id="btnDeleteAll" title="Hapus semua data training">
                <i class="bi bi-trash3-fill"></i> Hapus Semua
            </button>
            <button class="btn-mg btn-success-outline" id="btnImport" title="Import dari Excel">
                <i class="bi bi-file-earmark-arrow-up-fill"></i> Import
            </button>
            <a href="<?= base_url('training/export') ?>" class="btn-mg btn-export" title="Export ke Excel">
                <i class="bi bi-file-earmark-arrow-down-fill"></i> Export
            </a>
            <button class="btn-mg btn-primary-mg" id="btnOpenAdd">
                <i class="bi bi-plus-circle-fill"></i> Tambah Data
            </button>
        </div>
    </div>

    <!-- ── Quick Nav Pills ── -->
    <div class="quick-nav-row">
        <a href="<?= base_url('training') ?>"
           class="quick-nav-pill active">
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
           class="quick-nav-pill">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat Model</span>
        </a>
    </div>

    <!-- ── Generate Banner ── -->
    <div class="generate-banner">
        <div class="generate-banner-icon">
            <i class="bi bi-cpu-fill"></i>
        </div>
        <div class="generate-banner-text">
            <div class="generate-banner-title">Generate Otomatis</div>
            <div class="generate-banner-sub">
                Klik <strong>Generate dari Penjualan</strong> untuk membuat dataset training secara otomatis dari data penjualan yang tersedia — termasuk fitur lag, rolling mean, dan trend.
            </div>
        </div>
        <button class="btn-mg btn-generate-sm" id="btnGenerateSm">
            <i class="bi bi-lightning-charge-fill"></i> Generate Sekarang
        </button>
    </div>

    <!-- ── Stats Row ── -->
    <?php
    $summary  = $summary ?? ['total_record' => 0, 'total_produk' => 0, 'total_qty' => 0, 'rata_harga' => 0, 'rata_promo' => 0, 'periode_awal' => null, 'periode_akhir' => null];
    $totalRec = count($trainings ?? []);

    $bulanNama = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    ?>
    <div class="mg-stats-row">
        <div class="stat-card accent-cyan">
            <div class="stat-icon" style="background:rgba(0,151,184,0.12);color:var(--accent-cyan)">
                <i class="bi bi-table"></i>
            </div>
            <div class="stat-value"><?= number_format($summary['total_record']) ?></div>
            <div class="stat-label">Total Record</div>
        </div>
        <div class="stat-card accent-blue">
            <div class="stat-icon" style="background:rgba(46,109,164,0.12);color:var(--mg-light)">
                <i class="bi bi-phone-fill"></i>
            </div>
            <div class="stat-value"><?= number_format($summary['total_produk']) ?></div>
            <div class="stat-label">Total Produk</div>
        </div>
        <div class="stat-card accent-green">
            <div class="stat-icon" style="background:rgba(16,183,127,0.12);color:var(--accent-green)">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div class="stat-value"><?= number_format($summary['total_qty']) ?></div>
            <div class="stat-label">Total Qty</div>
        </div>
        <div class="stat-card accent-yellow">
            <div class="stat-icon" style="background:rgba(212,160,23,0.12);color:var(--accent-yellow)">
                <i class="bi bi-calendar3-range"></i>
            </div>
            <div class="stat-value" style="font-size:13px">
                <?= $summary['periode_awal'] ? date('M Y', strtotime($summary['periode_awal'] . '-01')) . ' – ' . date('M Y', strtotime($summary['periode_akhir'] . '-01')) : '—' ?>
            </div>
            <div class="stat-label">Rentang Periode</div>
        </div>
    </div>

    <!-- ── Table Card ── -->
    <div class="card-mg">
        <div class="mg-card-header">
            <div class="mg-card-title-group">
                <span class="section-title">Dataset Fitur</span>
                <span class="badge-mg badge-cyan" id="badgeCount"><?= $totalRec ?> record</span>
            </div>
            <div class="mg-card-toolbar">
                <!-- Filter Produk -->
                <div class="mg-filter-wrap">
                    <select id="filterProduk" class="form-mg form-mg-sm" style="width:200px">
                        <option value="">Semua Produk</option>
                        <?php foreach ($produks as $p): ?>
                            <option value="<?= esc($p['nama_produk']) ?>"><?= esc($p['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Filter Tahun -->
                <div class="mg-filter-wrap">
                    <select id="filterTahun" class="form-mg form-mg-sm">
                        <option value="">Semua Tahun</option>
                        <?php
                        $tahunList = array_unique(array_column($trainings ?? [], 'tahun'));
                        rsort($tahunList);
                        foreach ($tahunList as $thn): ?>
                            <option value="<?= $thn ?>"><?= $thn ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Search -->
                <div class="mg-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="mgSearch" placeholder="Cari produk…">
                </div>
            </div>
        </div>

        <div class="mg-table-wrap">
            <table class="table-mg" id="tblTraining">
                <thead>
                    <tr>
                        <th style="width:46px">No</th>
                        <th>Nama Produk</th>
                        <th style="width:80px;text-align:center">Tahun</th>
                        <th style="width:70px;text-align:center">Bulan</th>
                        <th style="width:65px;text-align:center">Q</th>
                        <th style="width:80px;text-align:center">Qty Total</th>
                        <th style="width:130px">Harga Avg</th>
                        <th style="width:90px;text-align:center">Promo Avg</th>
                        <th style="width:90px;text-align:center">N Transaksi</th>
                        <th style="width:75px;text-align:center">Lag 1</th>
                        <th style="width:75px;text-align:center">Lag 2</th>
                        <th style="width:75px;text-align:center">Lag 3</th>
                        <th style="width:100px;text-align:center">Roll3 Mean</th>
                        <th style="width:85px;text-align:center">Trend</th>
                        <th style="width:100px;text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($trainings)): $no = 1; ?>
                        <?php foreach ($trainings as $t): ?>
                            <tr data-produk="<?= esc($t['nama_produk']) ?>" data-tahun="<?= esc($t['tahun']) ?>">
                                <td><span class="row-no"><?= $no++ ?></span></td>
                                <td>
                                    <div class="produk-cell">
                                        <div class="produk-icon-tr">
                                            <i class="bi bi-cpu"></i>
                                        </div>
                                        <div>
                                            <div class="produk-name"><?= esc($t['nama_produk']) ?></div>
                                            <div class="produk-id-ref"><?= esc($t['id_training']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align:center">
                                    <span class="year-badge"><?= $t['tahun'] ?></span>
                                </td>
                                <td style="text-align:center">
                                    <span class="month-badge"><?= $bulanNama[(int)$t['bulan']] ?? $t['bulan'] ?></span>
                                </td>
                                <td style="text-align:center">
                                    <span class="quarter-badge">Q<?= $t['kuartal'] ?></span>
                                </td>
                                <td style="text-align:center">
                                    <span class="qty-badge"><?= number_format($t['qty_total']) ?></span>
                                </td>
                                <td>
                                    <?php if ($t['harga_avg']): ?>
                                        <span class="price-tag">
                                            <span class="price-currency">Rp</span>
                                            <?= number_format((float)$t['harga_avg'], 0, ',', '.') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="null-val">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center">
                                    <?php if ($t['promo_avg'] !== null): ?>
                                        <span class="promo-badge"><?= number_format((float)$t['promo_avg'] * 100, 0) ?>%</span>
                                    <?php else: ?>
                                        <span class="null-val">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center">
                                    <span class="n-badge"><?= $t['n_transaksi'] ?? '—' ?></span>
                                </td>
                                <td style="text-align:center">
                                    <span class="lag-val"><?= $t['qty_lag1'] !== null ? number_format((float)$t['qty_lag1'],1) : '—' ?></span>
                                </td>
                                <td style="text-align:center">
                                    <span class="lag-val"><?= $t['qty_lag2'] !== null ? number_format((float)$t['qty_lag2'],1) : '—' ?></span>
                                </td>
                                <td style="text-align:center">
                                    <span class="lag-val"><?= $t['qty_lag3'] !== null ? number_format((float)$t['qty_lag3'],1) : '—' ?></span>
                                </td>
                                <td style="text-align:center">
                                    <span class="roll-val"><?= $t['qty_roll3_mean'] !== null ? number_format((float)$t['qty_roll3_mean'],2) : '—' ?></span>
                                </td>
                                <td style="text-align:center">
                                    <?php if ($t['trend'] !== null):
                                        $trend = (float)$t['trend'];
                                        $cls   = $trend > 0 ? 'trend-up' : ($trend < 0 ? 'trend-down' : 'trend-flat');
                                        $icon  = $trend > 0 ? 'bi-arrow-up-short' : ($trend < 0 ? 'bi-arrow-down-short' : 'bi-dash');
                                    ?>
                                        <span class="trend-badge <?= $cls ?>">
                                            <i class="bi <?= $icon ?>"></i>
                                            <?= number_format($trend, 4) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="null-val">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <button class="action-btn edit-btn btnEdit"
                                            data-id="<?= esc($t['id_training']) ?>" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="action-btn delete-btn btnDelete"
                                            data-id="<?= esc($t['id_training']) ?>"
                                            data-nama="<?= esc($t['nama_produk']) ?>"
                                            data-bulan="<?= esc($bulanNama[(int)$t['bulan']] ?? $t['bulan']) ?>"
                                            data-tahun="<?= esc($t['tahun']) ?>"
                                            title="Hapus">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="15" class="empty-state">
                                <i class="bi bi-cpu" style="font-size:38px;color:var(--text-placeholder);display:block;margin-bottom:10px"></i>
                                Belum ada data training.<br>
                                <span style="font-size:12px">Klik <strong>Generate dari Penjualan</strong> untuk membuat dataset otomatis.</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mg-table-footer">
            <span class="table-info" id="tableInfo">Menampilkan 1–10 dari <?= $totalRec ?> record</span>
            <div class="mg-pagination" id="mgPagination"></div>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     MODAL TAMBAH DATA TRAINING
═══════════════════════════════════════════════════════════ -->
<div class="mg-modal-overlay" id="overlayAdd">
    <div class="mg-modal mg-modal-xl">
        <div class="mg-modal-header" style="--modal-accent:var(--mg-glow)">
            <div class="mg-modal-icon" style="background:rgba(46,109,164,0.15);color:var(--mg-light)">
                <i class="bi bi-plus-circle-fill"></i>
            </div>
            <div>
                <div class="mg-modal-title">Tambah Data Training</div>
                <div class="mg-modal-sub">Tambah record dataset secara manual</div>
            </div>
            <button class="mg-modal-close" id="closeAdd" type="button"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="mg-modal-body">
            <form id="formStore" autocomplete="off">
                <?= csrf_field() ?>

                <!-- Nama Produk -->
                <div class="mg-field">
                    <label class="form-label-mg">Nama Produk</label>
                    <div class="mg-input-icon">
                        <i class="bi bi-phone"></i>
                        <input type="text" name="nama_produk" class="form-mg" placeholder="Nama produk (huruf kapital)">
                    </div>
                    <small class="mg-field-error error-nama_produk-store"></small>
                </div>

                <!-- Tahun + Bulan -->
                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">Tahun</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-calendar"></i>
                            <input type="number" name="tahun" class="form-mg" placeholder="2025" value="<?= date('Y') ?>" min="2000" max="2100">
                        </div>
                        <small class="mg-field-error error-tahun-store"></small>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Bulan</label>
                        <div class="mg-select-wrap">
                            <i class="bi bi-calendar3"></i>
                            <select name="bulan" class="form-mg">
                                <?php foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $m => $nm): ?>
                                    <option value="<?= $m+1 ?>" <?= ($m+1) == date('n') ? 'selected' : '' ?>><?= $nm ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i class="bi bi-chevron-down mg-select-arrow"></i>
                        </div>
                        <small class="mg-field-error error-bulan-store"></small>
                    </div>
                </div>

                <!-- Qty Total + N Transaksi -->
                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">Qty Total <span class="label-req">*</span></label>
                        <div class="mg-input-icon">
                            <i class="bi bi-box-seam"></i>
                            <input type="number" name="qty_total" class="form-mg" placeholder="0" min="0" value="0">
                        </div>
                        <small class="mg-field-error error-qty_total-store"></small>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">N Transaksi <span class="label-opt">(opsional)</span></label>
                        <div class="mg-input-icon">
                            <i class="bi bi-receipt"></i>
                            <input type="number" name="n_transaksi" class="form-mg" placeholder="0" min="0">
                        </div>
                    </div>
                </div>

                <!-- Harga Avg + Std -->
                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">Harga Avg (Rp) <span class="label-opt">(opsional)</span></label>
                        <div class="mg-input-icon">
                            <i class="bi bi-cash"></i>
                            <input type="number" name="harga_avg" class="form-mg" placeholder="0" min="0">
                        </div>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Harga Std <span class="label-opt">(opsional)</span></label>
                        <div class="mg-input-icon">
                            <i class="bi bi-distribute-horizontal"></i>
                            <input type="number" name="harga_std" class="form-mg" placeholder="0" min="0" step="0.01">
                        </div>
                    </div>
                </div>

                <!-- Promo Avg -->
                <div class="mg-field">
                    <label class="form-label-mg">Promo Avg (0.0 – 1.0) <span class="label-opt">(opsional)</span></label>
                    <div class="mg-input-icon">
                        <i class="bi bi-percent"></i>
                        <input type="number" name="promo_avg" class="form-mg" placeholder="0.0" min="0" max="1" step="0.0001">
                    </div>
                </div>

                <!-- Divider fitur lag -->
                <div class="section-divider">
                    <span>Fitur Lag & Rolling <span class="label-opt">(opsional, bisa dikosongkan)</span></span>
                </div>

                <!-- Lag 1 / 2 / 3 -->
                <div class="mg-row-3">
                    <div class="mg-field">
                        <label class="form-label-mg">Lag 1</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-arrow-left-short"></i>
                            <input type="number" name="qty_lag1" class="form-mg" placeholder="—" step="0.01">
                        </div>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Lag 2</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-arrow-left-short"></i>
                            <input type="number" name="qty_lag2" class="form-mg" placeholder="—" step="0.01">
                        </div>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Lag 3</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-arrow-left-short"></i>
                            <input type="number" name="qty_lag3" class="form-mg" placeholder="—" step="0.01">
                        </div>
                    </div>
                </div>

                <!-- Roll3 Mean / Std + Roll6 Mean + Trend -->
                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">Roll3 Mean</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-graph-up"></i>
                            <input type="number" name="qty_roll3_mean" class="form-mg" placeholder="—" step="0.01">
                        </div>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Roll3 Std</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-distribute-horizontal"></i>
                            <input type="number" name="qty_roll3_std" class="form-mg" placeholder="—" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">Roll6 Mean</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-graph-up"></i>
                            <input type="number" name="qty_roll6_mean" class="form-mg" placeholder="—" step="0.01">
                        </div>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Trend</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-activity"></i>
                            <input type="number" name="trend" class="form-mg" placeholder="—" step="0.0001">
                        </div>
                    </div>
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
     MODAL EDIT DATA TRAINING
═══════════════════════════════════════════════════════════ -->
<div class="mg-modal-overlay" id="overlayEdit">
    <div class="mg-modal mg-modal-xl">
        <div class="mg-modal-header" style="--modal-accent:#d4a017">
            <div class="mg-modal-icon" style="background:rgba(212,160,23,0.15);color:var(--accent-yellow)">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <div class="mg-modal-title">Edit Data Training</div>
                <div class="mg-modal-sub">Perbarui record dataset</div>
            </div>
            <button class="mg-modal-close" id="closeEdit" type="button"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="mg-modal-body">
            <form id="formEdit" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="id_training" id="edit_id">

                <div class="mg-field">
                    <label class="form-label-mg">Nama Produk</label>
                    <div class="mg-input-icon">
                        <i class="bi bi-phone"></i>
                        <input type="text" name="nama_produk" id="edit_nama_produk" class="form-mg" placeholder="Nama produk">
                    </div>
                    <small class="mg-field-error error-nama_produk-edit"></small>
                </div>

                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">Tahun</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-calendar"></i>
                            <input type="number" name="tahun" id="edit_tahun" class="form-mg" min="2000" max="2100">
                        </div>
                        <small class="mg-field-error error-tahun-edit"></small>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Bulan</label>
                        <div class="mg-select-wrap">
                            <i class="bi bi-calendar3"></i>
                            <select name="bulan" id="edit_bulan" class="form-mg">
                                <?php foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $m => $nm): ?>
                                    <option value="<?= $m+1 ?>"><?= $nm ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i class="bi bi-chevron-down mg-select-arrow"></i>
                        </div>
                    </div>
                </div>

                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">Qty Total</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-box-seam"></i>
                            <input type="number" name="qty_total" id="edit_qty_total" class="form-mg" min="0">
                        </div>
                        <small class="mg-field-error error-qty_total-edit"></small>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">N Transaksi</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-receipt"></i>
                            <input type="number" name="n_transaksi" id="edit_n_transaksi" class="form-mg" min="0">
                        </div>
                    </div>
                </div>

                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">Harga Avg (Rp)</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-cash"></i>
                            <input type="number" name="harga_avg" id="edit_harga_avg" class="form-mg" min="0">
                        </div>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Harga Std</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-distribute-horizontal"></i>
                            <input type="number" name="harga_std" id="edit_harga_std" class="form-mg" min="0" step="0.01">
                        </div>
                    </div>
                </div>

                <div class="mg-field">
                    <label class="form-label-mg">Promo Avg (0.0 – 1.0)</label>
                    <div class="mg-input-icon">
                        <i class="bi bi-percent"></i>
                        <input type="number" name="promo_avg" id="edit_promo_avg" class="form-mg" min="0" max="1" step="0.0001">
                    </div>
                </div>

                <div class="section-divider"><span>Fitur Lag & Rolling</span></div>

                <div class="mg-row-3">
                    <div class="mg-field">
                        <label class="form-label-mg">Lag 1</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-arrow-left-short"></i>
                            <input type="number" name="qty_lag1" id="edit_qty_lag1" class="form-mg" step="0.01">
                        </div>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Lag 2</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-arrow-left-short"></i>
                            <input type="number" name="qty_lag2" id="edit_qty_lag2" class="form-mg" step="0.01">
                        </div>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Lag 3</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-arrow-left-short"></i>
                            <input type="number" name="qty_lag3" id="edit_qty_lag3" class="form-mg" step="0.01">
                        </div>
                    </div>
                </div>

                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">Roll3 Mean</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-graph-up"></i>
                            <input type="number" name="qty_roll3_mean" id="edit_qty_roll3_mean" class="form-mg" step="0.01">
                        </div>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Roll3 Std</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-distribute-horizontal"></i>
                            <input type="number" name="qty_roll3_std" id="edit_qty_roll3_std" class="form-mg" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="mg-row-2">
                    <div class="mg-field">
                        <label class="form-label-mg">Roll6 Mean</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-graph-up"></i>
                            <input type="number" name="qty_roll6_mean" id="edit_qty_roll6_mean" class="form-mg" step="0.01">
                        </div>
                    </div>
                    <div class="mg-field">
                        <label class="form-label-mg">Trend</label>
                        <div class="mg-input-icon">
                            <i class="bi bi-activity"></i>
                            <input type="number" name="trend" id="edit_trend" class="form-mg" step="0.0001">
                        </div>
                    </div>
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
            <div class="import-info-box">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                    <div style="font-weight:600;margin-bottom:4px">Format kolom yang diperlukan:</div>
                    <div class="import-cols">
                        <span class="import-col-badge">A: nama_produk</span>
                        <span class="import-col-badge">B: tahun</span>
                        <span class="import-col-badge">C: bulan</span>
                        <span class="import-col-badge">D: qty_total</span>
                        <span class="import-col-badge">E: harga_avg</span>
                        <span class="import-col-badge">F: harga_std</span>
                        <span class="import-col-badge">G: promo_avg</span>
                        <span class="import-col-badge">H: n_transaksi</span>
                        <span class="import-col-badge">I-K: lag1-3</span>
                        <span class="import-col-badge">L-N: roll mean/std</span>
                        <span class="import-col-badge">O: trend</span>
                    </div>
                    <div style="font-size:12px;margin-top:6px;color:var(--text-muted)">
                        Baris pertama dianggap header dan akan dilewati. Kolom E-O bersifat opsional.
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
                        <button type="button" class="drop-remove" id="removeFile"><i class="bi bi-x-circle-fill"></i></button>
                    </div>
                </div>

                <div id="importProgress" style="display:none;margin-top:14px">
                    <div class="mg-progress-bar">
                        <div class="mg-progress-fill" id="progressFill"></div>
                    </div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px;text-align:center" id="progressLabel">Mengupload…</div>
                </div>
                <div id="importResult" style="display:none;margin-top:14px"></div>
            </form>

            <div class="import-template-row">
                <i class="bi bi-download" style="color:var(--accent-cyan)"></i>
                <span>Belum punya template?</span>
                <a href="<?= base_url('training/downloadTemplate') ?>" class="template-link">Unduh template Excel</a>
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
#main-content {
    overflow-x: hidden;
    min-width: 0;
    box-sizing: border-box;
}
.content-wrapper-inner { display:flex; flex-direction:column; gap:24px; }

/* ── Page header ── */
.mg-page-header {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    flex-wrap:wrap;
    gap:14px;
}
.page-header-left { flex-shrink:0; }
.header-actions {
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
    justify-content:flex-end;
    /* Allow actions to shrink and wrap on smaller screens */
    max-width:100%;
}

/* ── Quick Nav Pills ── */
.quick-nav-row {
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}
.quick-nav-pill {
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:9px 16px;
    border-radius:var(--radius-sm);
    background:var(--card-bg-alt);
    border:1px solid var(--surface-border);
    color:var(--text-muted);
    font-size:13px;
    font-weight:600;
    text-decoration:none;
    transition:var(--transition);
    white-space:nowrap;
}
.quick-nav-pill:hover {
    background:rgba(74,159,212,0.1);
    border-color:rgba(74,159,212,0.35);
    color:var(--mg-light);
    transform:translateY(-1px);
}
.quick-nav-pill.active {
    background:rgba(74,159,212,0.15);
    border-color:rgba(74,159,212,0.4);
    color:var(--mg-light);
}
.pill-badge {
    display:inline-flex;
    align-items:center;
    padding:1px 7px;
    border-radius:20px;
    font-size:10px;
    font-weight:700;
    letter-spacing:.03em;
}
.ml-badge {
    background:rgba(16,183,127,0.15);
    color:var(--accent-green);
    border:1px solid rgba(16,183,127,0.3);
}

/* ── Generate banner ── */
.generate-banner {
    display:flex; align-items:center; gap:16px;
    padding:16px 20px;
    background: linear-gradient(135deg, rgba(74,159,212,0.08), rgba(0,151,184,0.05));
    border:1px solid rgba(74,159,212,0.2);
    border-radius:var(--radius-md);
    flex-wrap:wrap;
}
.generate-banner-icon {
    width:46px; height:46px; flex-shrink:0;
    background:linear-gradient(135deg, var(--mg-glow), var(--accent-cyan));
    border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:20px;
}
.generate-banner-text { flex:1; min-width:180px; }
.generate-banner-title { font-weight:700; font-size:13.5px; color:var(--text-ink); margin-bottom:3px; }
.generate-banner-sub   { font-size:12px; color:var(--text-muted); line-height:1.5; }

/* ── Buttons ── */
.btn-mg {
    /* Ensure buttons don't overflow, allow text to wrap if needed */
    white-space:nowrap;
    flex-shrink:0;
}
.btn-danger-outline  { background:rgba(229,62,62,0.08);  color:var(--accent-red);   border:1px solid rgba(229,62,62,0.25); }
.btn-danger-outline:hover  { background:rgba(229,62,62,0.18); }
.btn-success-outline { background:rgba(16,183,127,0.08); color:var(--accent-green); border:1px solid rgba(16,183,127,0.25); }
.btn-success-outline:hover { background:rgba(16,183,127,0.18); }
.btn-generate {
    background:linear-gradient(135deg, rgba(74,159,212,0.12), rgba(0,151,184,0.1));
    color:var(--accent-cyan); border:1px solid rgba(0,151,184,0.3);
}
.btn-generate:hover { background:linear-gradient(135deg, rgba(74,159,212,0.22), rgba(0,151,184,0.18)); }
.btn-generate-sm {
    background:linear-gradient(135deg, var(--mg-glow), var(--accent-cyan));
    color:#fff; border:none; flex-shrink:0;
    padding:9px 18px; border-radius:var(--radius-sm);
    font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:7px;
    transition:var(--transition);
}
.btn-generate-sm:hover { opacity:.88; transform:translateY(-1px); }
.btn-export {
    background:rgba(0,151,184,0.08); color:var(--accent-cyan);
    border:1px solid rgba(0,151,184,0.25);
    text-decoration:none; display:inline-flex; align-items:center; gap:7px;
    padding:8px 16px; border-radius:var(--radius-sm);
    font-size:13px; font-weight:600; transition:var(--transition);
    white-space:nowrap;
}
.btn-export:hover { background:rgba(0,151,184,0.18); }

/* ── Stats ── */
.mg-stats-row {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
}
@media(max-width:1100px) { .mg-stats-row { grid-template-columns:repeat(4,1fr); } }
@media(max-width:900px)  { .mg-stats-row { grid-template-columns:repeat(2,1fr); } }
@media(max-width:540px)  { .mg-stats-row { grid-template-columns:1fr 1fr; } }
@media(max-width:400px)  { .mg-stats-row { grid-template-columns:1fr; } }

/* ── Card header ── */
.mg-card-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:18px; }
.mg-card-title-group { display:flex; align-items:center; gap:10px; }
.mg-card-toolbar { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.form-mg-sm { padding:6px 10px; font-size:12.5px; }
.mg-filter-wrap { display:inline-flex; }
.mg-search-box {
    display:flex; align-items:center; gap:8px; padding:7px 14px;
    background:var(--card-bg-alt); border:1px solid var(--surface-border);
    border-radius:var(--radius-sm); color:var(--text-muted); transition:var(--transition);
}
.mg-search-box:focus-within { border-color:var(--mg-light); box-shadow:0 0 0 3px rgba(74,159,212,0.1); }
.mg-search-box input { border:none; background:transparent; color:var(--text-ink); font-size:13px; outline:none; width:160px; }
.mg-search-box input::placeholder { color:var(--text-placeholder); }

/* ── Table ── */
.mg-table-wrap { overflow-x:auto; border-radius:var(--radius-sm); border:1px solid var(--surface-border); }
.table-mg { min-width:1100px; }
.row-no { display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; background:var(--card-bg-alt); border:1px solid var(--surface-border); border-radius:6px; font-size:11px; color:var(--text-muted); font-weight:600; }

.produk-cell { display:flex; align-items:center; gap:10px; }
.produk-icon-tr {
    width:32px; height:32px; flex-shrink:0;
    background:linear-gradient(135deg, rgba(74,159,212,0.2), rgba(0,151,184,0.15));
    border:1px solid rgba(74,159,212,0.2);
    border-radius:8px;
    display:flex; align-items:center; justify-content:center;
    color:var(--accent-cyan); font-size:14px;
}
.produk-name { font-size:12.5px; font-weight:500; }
.produk-id-ref { font-size:10.5px; color:var(--text-placeholder); font-family:'Courier New',monospace; }

.year-badge {
    display:inline-flex; align-items:center; justify-content:center;
    padding:2px 8px; border-radius:6px;
    background:rgba(46,109,164,0.12); color:var(--mg-light);
    font-size:12px; font-weight:700;
}
.month-badge {
    display:inline-flex; align-items:center; justify-content:center;
    padding:2px 8px; border-radius:6px;
    background:rgba(0,151,184,0.1); color:var(--accent-cyan);
    font-size:11.5px; font-weight:600;
}
.quarter-badge {
    display:inline-flex; align-items:center; justify-content:center;
    padding:2px 7px; border-radius:20px;
    background:rgba(212,160,23,0.1); color:var(--accent-yellow);
    font-size:11px; font-weight:600;
    border:1px solid rgba(212,160,23,0.2);
}
.qty-badge {
    display:inline-flex; align-items:center; justify-content:center;
    min-width:28px; height:22px; padding:0 7px;
    background:rgba(16,183,127,0.1); color:var(--accent-green);
    border:1px solid rgba(16,183,127,0.2); border-radius:6px;
    font-size:12px; font-weight:700;
}
.promo-badge {
    display:inline-flex; align-items:center; justify-content:center;
    padding:2px 8px; border-radius:20px;
    background:rgba(229,62,62,0.08); color:var(--accent-red);
    font-size:11px; font-weight:600;
}
.n-badge {
    display:inline-flex; align-items:center; justify-content:center;
    padding:2px 8px; border-radius:6px;
    background:rgba(74,159,212,0.07); color:var(--text-muted);
    font-size:12px;
}
.lag-val, .roll-val { font-size:12px; color:var(--text-muted); font-family:'Courier New',monospace; }
.null-val { color:var(--text-placeholder); font-size:12px; }
.price-tag { display:inline-flex; align-items:baseline; gap:3px; font-weight:600; color:var(--accent-green); font-size:12.5px; }
.price-currency { font-size:10px; font-weight:500; }

.trend-badge { display:inline-flex; align-items:center; gap:2px; padding:2px 7px; border-radius:6px; font-size:11px; font-weight:600; font-family:'Courier New',monospace; }
.trend-up   { background:rgba(16,183,127,0.1);  color:var(--accent-green); }
.trend-down { background:rgba(229,62,62,0.1);   color:var(--accent-red); }
.trend-flat { background:rgba(128,128,128,0.1); color:var(--text-muted); }

.action-group { display:flex; align-items:center; justify-content:center; gap:7px; }
.action-btn { width:32px; height:32px; border-radius:8px; border:1px solid transparent; display:flex; align-items:center; justify-content:center; font-size:13px; cursor:pointer; transition:var(--transition); background:none; }
.edit-btn   { background:rgba(212,160,23,0.1);  color:var(--accent-yellow); border-color:rgba(212,160,23,0.2); }
.edit-btn:hover   { background:rgba(212,160,23,0.22);  transform:translateY(-1px); }
.delete-btn { background:rgba(229,62,62,0.1);   color:var(--accent-red);    border-color:rgba(229,62,62,0.2); }
.delete-btn:hover { background:rgba(229,62,62,0.22);   transform:translateY(-1px); }

.empty-state { text-align:center; padding:48px 24px !important; color:var(--text-muted); font-size:13px; }
.mg-table-footer { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; padding:12px 16px; border-top:1px solid var(--surface-border); background:var(--card-bg-alt); border-radius:0 0 var(--radius-md) var(--radius-md); }
.table-info { font-size:12.5px; color:var(--text-muted); }

/* ── Modal ── */
.mg-modal-overlay { position:fixed; inset:0; background:rgba(10,16,28,0.65); backdrop-filter:blur(5px); z-index:1200; display:none; align-items:center; justify-content:center; padding:20px; }
.mg-modal-overlay.open { display:flex; animation:mgFadeIn .18s ease; }
@keyframes mgFadeIn { from{opacity:0} to{opacity:1} }
.mg-modal {
    background:var(--card-bg); border:1px solid var(--surface-border);
    border-radius:var(--radius-xl); width:100%; max-width:500px;
    box-shadow:0 24px 60px rgba(14,23,36,.25), 0 4px 16px rgba(14,23,36,.1);
    animation:mgSlideUp .2s cubic-bezier(.4,0,.2,1);
    overflow:hidden; max-height:90vh; display:flex; flex-direction:column;
}
.mg-modal-lg  { max-width:560px; }
.mg-modal-xl  { max-width:640px; }
@keyframes mgSlideUp { from{transform:translateY(18px);opacity:0} to{transform:none;opacity:1} }

.mg-modal-header { display:flex; align-items:center; gap:14px; padding:20px 22px; background:var(--card-bg-alt); border-bottom:1px solid var(--surface-border); position:relative; flex-shrink:0; }
.mg-modal-header::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:var(--modal-accent, var(--mg-glow)); border-radius:var(--radius-xl) var(--radius-xl) 0 0; }
.mg-modal-icon { width:42px; height:42px; border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; font-size:19px; flex-shrink:0; }
.mg-modal-title { font-family:var(--font-display); font-size:15px; font-weight:700; color:var(--text-ink); }
.mg-modal-sub   { font-size:11.5px; color:var(--text-muted); margin-top:1px; }
.mg-modal-close { margin-left:auto; width:32px; height:32px; background:var(--card-bg); border:1px solid var(--surface-border); border-radius:8px; color:var(--text-muted); display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:13px; transition:var(--transition); }
.mg-modal-close:hover { color:var(--accent-red); border-color:rgba(229,62,62,.3); background:rgba(229,62,62,.06); }
.mg-modal-body { padding:22px; display:flex; flex-direction:column; overflow-y:auto; flex:1; }
.mg-modal-footer { display:flex; align-items:center; justify-content:flex-end; gap:10px; padding:16px 22px; border-top:1px solid var(--surface-border); background:var(--card-bg-alt); flex-shrink:0; }

/* ── Form ── */
.mg-field { display:flex; flex-direction:column; gap:6px; margin-bottom:14px; }
.mg-field:last-child { margin-bottom:0; }
.mg-row-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.mg-row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }
@media(max-width:480px) { .mg-row-2,.mg-row-3 { grid-template-columns:1fr; } }

.mg-input-icon { position:relative; display:flex; align-items:center; }
.mg-input-icon>i:first-child { position:absolute; left:12px; color:var(--text-placeholder); font-size:14px; pointer-events:none; }
.mg-input-icon .form-mg { padding-left:36px; }
.mg-select-wrap { position:relative; display:flex; align-items:center; }
.mg-select-wrap>i:first-child { position:absolute; left:12px; color:var(--text-placeholder); font-size:14px; pointer-events:none; }
.mg-select-wrap .form-mg { padding-left:36px; padding-right:32px; appearance:none; }
.mg-select-arrow { position:absolute; right:12px; color:var(--text-muted); font-size:11px; pointer-events:none; }
.mg-field-error { font-size:11.5px; color:var(--accent-red); min-height:16px; }
.label-opt { font-size:11px; font-weight:400; color:var(--text-muted); text-transform:none; letter-spacing:0; }
.label-req { color:var(--accent-red); }

.section-divider {
    display:flex; align-items:center; margin:10px 0 14px;
    font-size:11.5px; font-weight:600; color:var(--text-muted);
    text-transform:uppercase; letter-spacing:.05em;
}
.section-divider::before,.section-divider::after { content:''; flex:1; height:1px; background:var(--surface-border); }
.section-divider::before { margin-right:10px; }
.section-divider::after  { margin-left:10px;  }

/* ── Import ── */
.import-info-box { display:flex; gap:12px; padding:14px; margin-bottom:16px; background:rgba(74,159,212,0.06); border:1px solid rgba(74,159,212,0.18); border-radius:var(--radius-sm); font-size:13px; color:var(--text-ink); }
.import-info-box>i { color:var(--accent-cyan); font-size:16px; flex-shrink:0; margin-top:2px; }
.import-cols { display:flex; flex-wrap:wrap; gap:6px; margin-top:6px; }
.import-col-badge { padding:2px 9px; border-radius:20px; font-size:11px; background:rgba(0,151,184,0.1); color:var(--accent-cyan); border:1px solid rgba(0,151,184,0.2); font-weight:600; }
.drop-zone { border:2px dashed var(--surface-border); border-radius:var(--radius-md); background:var(--card-bg-alt); cursor:pointer; transition:var(--transition); overflow:hidden; }
.drop-zone:hover,.drop-zone.drag-over { border-color:var(--accent-green); background:rgba(16,183,127,0.05); }
.drop-zone-inner { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:32px 20px; gap:8px; text-align:center; }
.drop-icon { font-size:36px; color:var(--accent-green); }
.drop-title { font-weight:600; font-size:14px; color:var(--text-ink); }
.drop-sub { font-size:12px; color:var(--text-muted); }
.drop-selected { display:flex; align-items:center; gap:12px; padding:16px 20px; }
.drop-selected span { font-size:13px; font-weight:500; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.drop-remove { background:none; border:none; color:var(--accent-red); font-size:18px; cursor:pointer; padding:0; transition:var(--transition); }
.drop-remove:hover { transform:scale(1.2); }
.mg-progress-bar { height:6px; background:var(--card-bg-alt); border-radius:999px; overflow:hidden; border:1px solid var(--surface-border); }
.mg-progress-fill { height:100%; width:0%; background:linear-gradient(90deg, var(--accent-green), var(--accent-cyan)); border-radius:999px; transition:width .4s ease; }
.import-result-box { padding:14px; border-radius:var(--radius-sm); font-size:13px; }
.import-result-box.success { background:rgba(16,183,127,0.07); border:1px solid rgba(16,183,127,0.25); color:var(--accent-green); }
.import-result-box.warning { background:rgba(212,160,23,0.07); border:1px solid rgba(212,160,23,0.25); color:var(--accent-yellow); }
.import-result-box .err-list { max-height:100px; overflow-y:auto; margin-top:8px; font-size:12px; color:var(--text-muted); padding-left:14px; }
.import-template-row { display:flex; align-items:center; gap:8px; margin-top:14px; font-size:12.5px; color:var(--text-muted); }
.template-link { color:var(--accent-cyan); font-weight:600; text-decoration:none; }
.template-link:hover { text-decoration:underline; }

/* ── Pagination ── */
.mg-pagination { display:flex; align-items:center; gap:4px; }
.pg-btn { min-width:30px; height:30px; padding:0 8px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid var(--surface-border); background:var(--card-bg-alt); color:var(--text-muted); font-size:12.5px; font-weight:600; cursor:pointer; transition:var(--transition); }
.pg-btn:hover:not(:disabled) { background:rgba(74,159,212,.1); border-color:var(--mg-light); color:var(--mg-light); }
.pg-btn.active { background:var(--mg-glow); border-color:var(--mg-glow); color:#fff; }
.pg-btn:disabled { opacity:.35; cursor:not-allowed; }
.pg-ellipsis { color:var(--text-muted); font-size:12px; padding:0 4px; }

/* ── Mobile ── */
@media(max-width:768px) {
    #main-content { margin-left:0 !important; width:100% !important; overflow-x:hidden !important; }
    .content-wrapper { padding:16px 12px !important; overflow-x:hidden; }
    .content-wrapper-inner { width:100%; max-width:100%; overflow-x:hidden; }
    .card-mg { width:100%; min-width:0; padding:14px 12px; border-radius:var(--radius-md); }
    .stat-card { padding:12px 10px !important; min-width:0; }
    .mg-table-wrap { width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch; }
    .generate-banner { flex-direction:column; align-items:flex-start; }
    .produk-name { font-size:12px; white-space:normal; word-break:break-word; }
    .header-actions { width:100%; }
}
@media(max-width:480px) {
    .content-wrapper { padding:12px 10px !important; }
    .table-mg { min-width:700px; }
    .header-actions .btn-mg { font-size:12px; padding:7px 10px; }
}
</style>


<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const qs  = sel => document.querySelector(sel);
    const qsa = sel => document.querySelectorAll(sel);

    // ── Utilities ─────────────────────────────────────────────────────────────
    function openModal(id)  { const el = qs('#'+id); if(!el) return; el.style.display=''; el.classList.add('open'); document.body.style.overflow='hidden'; }
    function closeModal(id) { const el = qs('#'+id); if(!el) return; el.classList.remove('open'); el.style.display='none'; document.body.style.overflow=''; }
    function clearErrors()  { qsa('.mg-field-error').forEach(el => el.textContent = ''); }
    function setErrors(errors, suffix) {
        Object.entries(errors).forEach(([field, msg]) => {
            const el = qs('.error-' + field + '-' + suffix);
            if (el) el.textContent = msg;
        });
    }
    function postForm(url, formId) {
        return fetch(BASE_URL + url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(qs('#' + formId)),
        }).then(r => r.json());
    }
    function postData(url, payload) {
        const fd = new FormData();
        Object.entries(payload).forEach(([k,v]) => fd.append(k, v));
        return fetch(BASE_URL + url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
        }).then(r => r.json());
    }

    // ── Pagination state ──────────────────────────────────────────────────────
    const PER_PAGE = 10;
    let currentPage  = 1;
    let filteredRows = [];

    const searchInput  = qs('#mgSearch');
    const filterProduk = qs('#filterProduk');
    const filterTahun  = qs('#filterTahun');
    const allRows      = Array.from(qsa('#tblTraining tbody tr'));

    function applyFilters() {
        const q      = searchInput  ? searchInput.value.toLowerCase()  : '';
        const produk = filterProduk ? filterProduk.value.toLowerCase() : '';
        const tahun  = filterTahun  ? filterTahun.value                : '';

        filteredRows = allRows.filter(tr => {
            if (tr.querySelector('.empty-state') || tr.cells.length === 1) return false;
            const matchQ = !q      || tr.textContent.toLowerCase().includes(q);
            const matchP = !produk || (tr.dataset.produk || '').toLowerCase() === produk;
            const matchT = !tahun  || (tr.dataset.tahun || '') === tahun;
            return matchQ && matchP && matchT;
        });

        currentPage = 1;
        renderPage();
    }

    function renderPage() {
        const total      = filteredRows.length;
        const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
        allRows.forEach(tr => tr.style.display = 'none');

        const start = (currentPage - 1) * PER_PAGE;
        const end   = Math.min(start + PER_PAGE, total);
        filteredRows.slice(start, end).forEach(tr => tr.style.display = '');

        const emptyRow = allRows.find(tr => tr.querySelector('td.empty-state'));
        if (emptyRow) emptyRow.style.display = total === 0 ? '' : 'none';

        const info  = qs('#tableInfo');
        const badge = qs('#badgeCount');
        if (info)  info.textContent  = total === 0 ? 'Tidak ada data ditemukan' : `Menampilkan ${start+1}–${end} dari ${total} record`;
        if (badge) badge.textContent = `${total} record`;

        filteredRows.slice(start, end).forEach((tr, i) => {
            const noEl = tr.querySelector('.row-no');
            if (noEl) noEl.textContent = start + i + 1;
        });

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        const container = qs('#mgPagination');
        if (!container) return;
        container.innerHTML = '';
        if (totalPages <= 1) return;

        function makeBtn(label, page, isActive=false, isDisabled=false) {
            const btn = document.createElement('button');
            btn.className = 'pg-btn' + (isActive ? ' active' : '');
            btn.innerHTML = label; btn.disabled = isDisabled;
            if (!isDisabled && !isActive) btn.addEventListener('click', () => { currentPage = page; renderPage(); qs('#tblTraining')?.scrollIntoView({ behavior:'smooth', block:'start' }); });
            return btn;
        }
        function makeEllipsis() { const s = document.createElement('span'); s.className='pg-ellipsis'; s.textContent='…'; return s; }

        container.appendChild(makeBtn('<i class="bi bi-chevron-left"></i>', currentPage-1, false, currentPage===1));
        const pages = [];
        if (totalPages <= 7) { for(let i=1;i<=totalPages;i++) pages.push(i); }
        else {
            pages.push(1);
            if (currentPage > 3) pages.push('...');
            for(let i=Math.max(2,currentPage-1); i<=Math.min(totalPages-1,currentPage+1); i++) pages.push(i);
            if (currentPage < totalPages-2) pages.push('...');
            pages.push(totalPages);
        }
        pages.forEach(p => { if(p==='...') container.appendChild(makeEllipsis()); else container.appendChild(makeBtn(p, p, p===currentPage)); });
        container.appendChild(makeBtn('<i class="bi bi-chevron-right"></i>', currentPage+1, false, currentPage===totalPages));
    }

    applyFilters();
    searchInput?.addEventListener('input', applyFilters);
    filterProduk?.addEventListener('change', applyFilters);
    filterTahun?.addEventListener('change', applyFilters);

    // ── Modal wiring ──────────────────────────────────────────────────────────
    qs('#btnOpenAdd')?.addEventListener('click', () => openModal('overlayAdd'));
    qs('#closeAdd')?.addEventListener('click',   () => closeModal('overlayAdd'));
    qs('#cancelAdd')?.addEventListener('click',  () => closeModal('overlayAdd'));
    qs('#closeEdit')?.addEventListener('click',  () => closeModal('overlayEdit'));
    qs('#cancelEdit')?.addEventListener('click', () => closeModal('overlayEdit'));
    qs('#btnImport')?.addEventListener('click',  () => openModal('overlayImport'));
    qs('#closeImport')?.addEventListener('click',  () => closeModal('overlayImport'));
    qs('#cancelImport')?.addEventListener('click', () => closeModal('overlayImport'));
    qsa('.mg-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(overlay.id); });
    });

    // ── Generate dari Penjualan ───────────────────────────────────────────────
    function doGenerate() {
        Swal.fire({
            title: 'Generate Data Training?',
            html: 'Semua data training yang ada akan <b>diganti</b> dengan hasil agregasi terbaru dari data penjualan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2e6da4',
            cancelButtonColor: '#243B55',
            confirmButtonText: '<i class="bi bi-lightning-charge-fill"></i> Ya, Generate!',
            cancelButtonText: 'Batal',
        }).then(r => {
            if (!r.isConfirmed) return;
            Swal.fire({ title: 'Memproses…', text: 'Sedang generate dataset dari penjualan.', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            postData('training/generate', {})
                .then(res => {
                    if (res.status === 'warning') { Swal.fire({ icon:'warning', title:'Perhatian', text: res.message, confirmButtonColor:'#2e6da4' }); return; }
                    if (res.status !== 'success') { Swal.fire({ icon:'error', title:'Gagal', text: res.message }); return; }
                    Swal.fire({ icon:'success', title:'Berhasil!', text: res.message, confirmButtonColor:'#2e6da4' })
                        .then(() => location.reload());
                })
                .catch(() => Swal.fire({ icon:'error', title:'Error', text:'Gagal menghubungi server.' }));
        });
    }
    qs('#btnGenerate')?.addEventListener('click', doGenerate);
    qs('#btnGenerateSm')?.addEventListener('click', doGenerate);

    // ── CREATE ────────────────────────────────────────────────────────────────
    qs('#btnSave')?.addEventListener('click', function () {
        clearErrors();
        postForm('training/simpan', 'formStore')
            .then(res => {
                if (res.status === 'error_validation') { setErrors(res.errors, 'store'); return; }
                if (res.status === 'error') { Swal.fire({ icon:'error', title:'Gagal', text: res.message }); return; }
                closeModal('overlayAdd');
                Swal.fire({ icon:'success', title:'Berhasil!', text: res.message, confirmButtonColor:'#2e6da4' })
                    .then(() => location.reload());
            })
            .catch(() => Swal.fire({ icon:'error', title:'Error', text:'Gagal menghubungi server.' }));
    });

    // ── GET DATA (edit) ───────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btnEdit');
        if (!btn) return;
        postData('training/getData', { id: btn.dataset.id })
            .then(res => {
                if (res.status === 'error') { Swal.fire('Error', res.message, 'error'); return; }
                qs('#edit_id').value                = res.id_training;
                qs('#edit_nama_produk').value        = res.nama_produk;
                qs('#edit_tahun').value              = res.tahun;
                qs('#edit_bulan').value              = res.bulan;
                qs('#edit_qty_total').value          = res.qty_total;
                qs('#edit_n_transaksi').value        = res.n_transaksi   ?? '';
                qs('#edit_harga_avg').value          = res.harga_avg     ?? '';
                qs('#edit_harga_std').value          = res.harga_std     ?? '';
                qs('#edit_promo_avg').value          = res.promo_avg     ?? '';
                qs('#edit_qty_lag1').value           = res.qty_lag1      ?? '';
                qs('#edit_qty_lag2').value           = res.qty_lag2      ?? '';
                qs('#edit_qty_lag3').value           = res.qty_lag3      ?? '';
                qs('#edit_qty_roll3_mean').value     = res.qty_roll3_mean ?? '';
                qs('#edit_qty_roll3_std').value      = res.qty_roll3_std  ?? '';
                qs('#edit_qty_roll6_mean').value     = res.qty_roll6_mean ?? '';
                qs('#edit_trend').value              = res.trend         ?? '';
                clearErrors();
                openModal('overlayEdit');
            });
    });

    // ── UPDATE ────────────────────────────────────────────────────────────────
    qs('#btnUpdate')?.addEventListener('click', function () {
        clearErrors();
        const id = qs('#edit_id').value;
        fetch(BASE_URL + 'training/update/' + id, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(qs('#formEdit')),
        }).then(r => r.json()).then(res => {
            if (res.status === 'error_validation') { setErrors(res.errors, 'edit'); return; }
            closeModal('overlayEdit');
            Swal.fire({ icon:'success', title:'Updated!', text: res.message, confirmButtonColor:'#2e6da4' })
                .then(() => location.reload());
        });
    });

    // ── DELETE SINGLE ─────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btnDelete');
        if (!btn) return;
        Swal.fire({
            title: 'Hapus record ini?',
            html: `<b>${btn.dataset.nama}</b><br><span style="font-size:13px;color:#888">${btn.dataset.bulan} ${btn.dataset.tahun}</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#243B55',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
        }).then(r => {
            if (!r.isConfirmed) return;
            postData('training/hapus/' + btn.dataset.id, {})
                .then(res => {
                    Swal.fire({ icon:'success', title:'Dihapus!', text: res.message, confirmButtonColor:'#2e6da4' })
                        .then(() => location.reload());
                });
        });
    });

    // ── DELETE ALL ────────────────────────────────────────────────────────────
    qs('#btnDeleteAll')?.addEventListener('click', function () {
        Swal.fire({
            title: 'Hapus SEMUA data training?',
            html: '<span style="color:#e53e3e;font-weight:600">Seluruh dataset akan dihapus permanen!</span><br><span style="font-size:13px;color:#888">Ketik <b>HAPUS SEMUA</b> untuk konfirmasi.</span>',
            icon: 'warning',
            input: 'text',
            inputPlaceholder: 'Ketik HAPUS SEMUA',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#243B55',
            confirmButtonText: 'Hapus Semua',
            cancelButtonText: 'Batal',
            preConfirm: val => { if (val !== 'HAPUS SEMUA') Swal.showValidationMessage('Konfirmasi tidak sesuai.'); },
        }).then(r => {
            if (!r.isConfirmed) return;
            postData('training/hapusSemua', {})
                .then(res => {
                    Swal.fire({ icon:'success', title:'Dihapus!', text: res.message, confirmButtonColor:'#2e6da4' })
                        .then(() => location.reload());
                });
        });
    });

    // ── IMPORT ────────────────────────────────────────────────────────────────
    const dropZone    = qs('#dropZone');
    const fileInput   = qs('#fileExcel');
    const dropContent = qs('#dropZoneContent');
    const dropSel     = qs('#dropSelected');
    const selName     = qs('#selectedFileName');
    const removeBtn   = qs('#removeFile');

    function showFileSelected(name) { dropContent && (dropContent.style.display='none'); dropSel && (dropSel.style.display='flex'); selName && (selName.textContent = name); }
    function clearFileSelected() {
        dropContent && (dropContent.style.display='flex'); dropSel && (dropSel.style.display='none');
        if (fileInput) fileInput.value = '';
        const res = qs('#importResult'); if (res) { res.style.display='none'; res.innerHTML=''; }
    }

    dropZone?.addEventListener('click', e => { if (e.target.closest('#removeFile')) return; fileInput?.click(); });
    dropZone?.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone?.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone?.addEventListener('drop', e => {
        e.preventDefault(); dropZone.classList.remove('drag-over');
        const f = e.dataTransfer.files[0];
        if (f && fileInput) { const dt = new DataTransfer(); dt.items.add(f); fileInput.files = dt.files; showFileSelected(f.name); }
    });
    fileInput?.addEventListener('change', function () { if (this.files[0]) showFileSelected(this.files[0].name); });
    removeBtn?.addEventListener('click', e => { e.stopPropagation(); clearFileSelected(); });

    qs('#btnUpload')?.addEventListener('click', function () {
        if (!fileInput?.files[0]) { Swal.fire({ icon:'warning', title:'File belum dipilih', text:'Pilih file Excel terlebih dahulu.', confirmButtonColor:'#2e6da4' }); return; }

        const prog = qs('#importProgress'), fill = qs('#progressFill'), label = qs('#progressLabel'), res = qs('#importResult');
        prog && (prog.style.display=''); fill && (fill.style.width='0%'); res && (res.style.display='none', res.innerHTML='');
        this.disabled = true;

        let pct = 0;
        const interval = setInterval(() => {
            pct = Math.min(pct + Math.random() * 15, 85);
            if (fill) fill.style.width = pct + '%';
            if (label) label.textContent = 'Memproses… ' + Math.round(pct) + '%';
        }, 200);

        const btn = this;
        postForm('training/prosesImport', 'formImport')
            .then(data => {
                clearInterval(interval);
                if (fill) fill.style.width = '100%';
                if (label) label.textContent = 'Selesai!';
                const cls = data.skipped > 0 ? 'warning' : 'success';
                let html = `<div style="font-weight:600;margin-bottom:4px">${data.message}</div>`;
                if (data.errors?.length) html += '<ul class="err-list">' + data.errors.map(e=>`<li>${e}</li>`).join('') + '</ul>';
                if (res) { res.innerHTML = `<div class="import-result-box ${cls}">${html}</div>`; res.style.display=''; }
                if (data.inserted > 0) {
                    setTimeout(() => { closeModal('overlayImport'); Swal.fire({ icon:'success', title:'Import Selesai!', text: data.message, confirmButtonColor:'#2e6da4' }).then(() => location.reload()); }, 1200);
                }
            })
            .catch(() => Swal.fire({ icon:'error', title:'Gagal', text:'Terjadi kesalahan saat mengupload file.' }))
            .finally(() => btn.disabled = false);
    });

    qs('#overlayImport')?.addEventListener('transitionend', function () {
        if (!this.classList.contains('open')) { clearFileSelected(); const prog = qs('#importProgress'); if (prog) prog.style.display='none'; }
    });
});
</script>