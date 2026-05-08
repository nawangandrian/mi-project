<?php

/**
 * View  : pages/prediksi/riwayat.php
 * Modul : Riwayat Prediksi Penjualan — Mi Store Kudus
 *
 * Perbaikan:
 *  1. Filter periode → <select> dropdown (bukan pills yang overflow)
 *  2. Tabel riwayat → pagination 10 baris/halaman
 *  3. Card ringkasan produk → pagination 10 card/halaman
 *  4. Bug fix: nama produk di card tampil dua kali (hapus duplikat)
 */
?>

<div class="content-wrapper-inner">

    <!-- ── Page Header ── -->
    <div class="mg-page-header">
        <div>
            <h1 class="page-title">Riwayat Prediksi</h1>
            <p class="page-subtitle">Rekap hasil forecast Random Forest seluruh periode</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('prediksi/akurasi') ?>" class="btn-mg btn-outline-mg">
                <i class="bi bi-bullseye"></i> Evaluasi Model
            </a>
            <a href="<?= base_url('prediksi/jalankan') ?>" class="btn-mg btn-primary-mg">
                <i class="bi bi-play-circle-fill"></i> Jalankan Prediksi
            </a>
        </div>
    </div>

    <!-- ── Quick Nav Pills ── -->
    <div class="quick-nav-row">
        <a href="<?= base_url('prediksi') ?>" class="quick-nav-pill">
            <i class="bi bi-graph-up-arrow"></i>
            <span>Prediksi Penjualan</span>
        </a>
        <a href="<?= base_url('prediksi/jalankan') ?>" class="quick-nav-pill">
            <i class="bi bi-play-circle-fill"></i>
            <span>Jalankan Prediksi</span>
        </a>
        <a href="<?= base_url('prediksi/riwayat') ?>" class="quick-nav-pill active">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat Prediksi</span>
        </a>
        <a href="<?= base_url('prediksi/akurasi') ?>" class="quick-nav-pill">
            <i class="bi bi-bullseye"></i>
            <span>Evaluasi Model</span>
        </a>
    </div>

    <?php
    $bulanNama   = [
        '',
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];
    $bulanPendek = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    ?>

    <?php if (empty($prediksiList)): ?>
        <!-- ── Empty State ── -->
        <div class="rw-empty-wrap">
            <div class="rw-empty-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="rw-empty-title">Belum Ada Riwayat Prediksi</div>
            <div class="rw-empty-sub">
                Jalankan model prediksi terlebih dahulu untuk melihat riwayat forecast penjualan.
            </div>
            <a href="<?= base_url('prediksi/jalankan') ?>" class="btn-mg btn-primary-mg" style="margin-top:20px;text-decoration:none">
                <i class="bi bi-play-circle-fill"></i> Jalankan Prediksi Sekarang
            </a>
        </div>

    <?php else: ?>

        <!-- ── Summary Stats ── -->
        <?php
        $totalProdukUnik  = count(array_unique(array_column($prediksiList, 'nama_produk')));
        $totalQtyPrediksi = array_sum(array_column($prediksiList, 'qty_prediksi'));
        $totalPeriode     = count($daftarPeriode);
        ?>
        <div class="rw-stats-row">
            <div class="rw-stat-card">
                <div class="rw-stat-icon" style="background:rgba(74,159,212,0.12);color:var(--mg-light)">
                    <i class="bi bi-calendar3-range"></i>
                </div>
                <div class="rw-stat-val"><?= $totalPeriode ?></div>
                <div class="rw-stat-lbl">Total Periode</div>
            </div>
            <div class="rw-stat-card">
                <div class="rw-stat-icon" style="background:rgba(0,151,184,0.12);color:var(--accent-cyan)">
                    <i class="bi bi-phone-fill"></i>
                </div>
                <div class="rw-stat-val"><?= $totalProdukUnik ?></div>
                <div class="rw-stat-lbl">Produk Unik</div>
            </div>
            <div class="rw-stat-card">
                <div class="rw-stat-icon" style="background:rgba(16,183,127,0.12);color:var(--accent-green)">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div class="rw-stat-val"><?= number_format($totalQtyPrediksi) ?></div>
                <div class="rw-stat-lbl">Total Qty Diprediksi</div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
         FILTER PERIODE — SELECT DROPDOWN
    ══════════════════════════════════════════════════════════ -->
        <div class="card-mg">
            <div class="mg-card-header">
                <div class="mg-card-title-group">
                    <span class="section-title">Filter Periode</span>
                    <span class="badge-mg badge-cyan"><?= $totalPeriode ?> periode tersedia</span>
                </div>
            </div>

            <!-- SELECT DROPDOWN PERIODE -->
            <div class="rw-filter-bar">
                <div class="rw-filter-select-wrap">
                    <i class="bi bi-calendar3 rw-filter-icon"></i>
                    <select id="rwPeriodeSelect" class="rw-periode-select">
                        <option value=""
                            <?= empty($periodeAktif) ? 'selected' : '' ?>>
                            — Semua Periode —
                        </option>
                        <?php foreach ($daftarPeriode as $p):
                            $val     = $p['tahun'] . '_' . $p['bulan'];
                            $isAktif = !empty($periodeAktif)
                                && (int)$periodeAktif['tahun'] === (int)$p['tahun']
                                && (int)$periodeAktif['bulan'] === (int)$p['bulan'];
                        ?>
                            <option value="<?= esc($val) ?>"
                                data-url="<?= base_url('prediksi/riwayat?tahun=' . $p['tahun'] . '&bulan=' . $p['bulan']) ?>"
                                <?= $isAktif ? 'selected' : '' ?>>
                                <?= ($bulanNama[(int)$p['bulan']] ?? $p['bulan']) . ' ' . $p['tahun'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="bi bi-chevron-down rw-select-arrow"></i>
                </div>

                <?php if (!empty($periodeAktif)): ?>
                    <a href="<?= base_url('prediksi/riwayat') ?>"
                        class="btn-mg btn-outline-mg btn-sm-rw"
                        style="text-decoration:none;gap:5px">
                        <i class="bi bi-x-circle"></i> Reset Filter
                    </a>
                <?php endif; ?>

                <!-- Info periode aktif -->
                <?php if (!empty($periodeAktif)): ?>
                    <div class="rw-periode-aktif-info">
                        <i class="bi bi-funnel-fill"></i>
                        Menampilkan:
                        <strong><?= ($bulanNama[(int)$periodeAktif['bulan']] ?? '') . ' ' . $periodeAktif['tahun'] ?></strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
         TABEL RIWAYAT — PAGINATION 10/HALAMAN
    ══════════════════════════════════════════════════════════ -->
        <div class="card-mg">
            <div class="mg-card-header">
                <div class="mg-card-title-group">
                    <span class="section-title">
                        <?php if (!empty($periodeAktif)): ?>
                            Data Prediksi — <?= $bulanNama[(int)$periodeAktif['bulan']] ?? '' ?> <?= $periodeAktif['tahun'] ?>
                        <?php else: ?>
                            Semua Riwayat Prediksi
                        <?php endif; ?>
                    </span>
                    <span class="badge-mg badge-cyan" id="rwBadgeCount"><?= count($prediksiList) ?> record</span>
                </div>
                <div class="mg-card-toolbar">
                    <div class="mg-search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" id="rwSearch" placeholder="Cari produk…">
                    </div>
                    <div class="rw-sort-wrap">
                        <select id="rwSort" class="form-mg form-mg-sm">
                            <option value="periode_desc">Periode ↓ Terbaru</option>
                            <option value="periode_asc">Periode ↑ Terlama</option>
                            <option value="qty_desc">Qty ↓ Tertinggi</option>
                            <option value="qty_asc">Qty ↑ Terendah</option>
                            <option value="nama_asc">Nama A–Z</option>
                        </select>
                    </div>
                    <button class="btn-mg btn-outline-mg btn-sm-rw" id="btnRwExport" title="Export CSV">
                        <i class="bi bi-download"></i> Export
                    </button>
                </div>
            </div>

            <div class="mg-table-wrap">
                <table class="table-mg rw-table" id="rwTable">
                    <thead>
                        <tr>
                            <th style="width:46px">No</th>
                            <th>Nama Produk</th>
                            <th style="text-align:center;width:120px">Periode</th>
                            <th style="text-align:right;width:130px">Qty Prediksi</th>
                            <th style="text-align:center;width:130px">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody id="rwTableBody">
                        <?php foreach ($prediksiList as $i => $p):
                            $qty       = (float)$p['qty_prediksi'];
                            $tahun     = $p['tahun_prediksi'];
                            $bulan     = (int)$p['bulan_prediksi'];
                            $periodeKey = $tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT);
                        ?>
                            <tr class="rw-row"
                                data-nama="<?= esc(strtolower($p['nama_produk'])) ?>"
                                data-qty="<?= $qty ?>"
                                data-periode="<?= $periodeKey ?>">
                                <td><span class="row-no"><?= $i + 1 ?></span></td>
                                <td>
                                    <div class="rw-produk-cell">
                                        <div class="rw-produk-icon"><i class="bi bi-phone-fill"></i></div>
                                        <span class="rw-produk-nama"><?= esc($p['nama_produk']) ?></span>
                                    </div>
                                </td>
                                <td style="text-align:center">
                                    <span class="rw-periode-badge">
                                        <?= ($bulanPendek[$bulan] ?? $bulan) . ' ' . $tahun ?>
                                    </span>
                                </td>
                                <td style="text-align:right">
                                    <span class="rw-qty-pred"><?= number_format($qty, 1) ?></span>
                                </td>
                                <td style="text-align:center;font-size:12px;color:var(--text-muted)">
                                    <?= !empty($p['created_at']) ? date('d M Y', strtotime($p['created_at'])) : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- No result -->
            <div class="rw-no-result" id="rwNoResult" style="display:none">
                <i class="bi bi-search"></i>
                <div>Produk tidak ditemukan.</div>
            </div>

            <!-- ── Pagination Tabel ── -->
            <div class="rw-pagination-bar" id="rwTablePagBar">
                <div class="rw-pag-info" id="rwCountInfo">
                    Menampilkan 1–10 dari <?= count($prediksiList) ?> record
                </div>
                <div class="rw-pag-controls">
                    <button class="rw-pag-btn" id="rwTablePrev" disabled>
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div class="rw-pag-pages" id="rwTablePages"></div>
                    <button class="rw-pag-btn" id="rwTableNext">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
         RINGKASAN PER PRODUK — PAGINATION 10 CARD/HALAMAN
    ══════════════════════════════════════════════════════════ -->
        <?php if (!empty($ringkasanProduk)): ?>
            <div class="card-mg">
                <div class="mg-card-header">
                    <div class="mg-card-title-group">
                        <span class="section-title">Ringkasan Per Produk</span>
                        <span class="badge-mg badge-cyan" id="rwProdukBadge"><?= count($ringkasanProduk) ?> produk</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                        <div class="mg-search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="rwProdukSearch" placeholder="Cari produk…">
                        </div>
                    </div>
                </div>

                <!-- Grid card (semua dirender, JS yang atur visibilitas + paginasi) -->
                <div class="rw-produk-grid" id="rwProdukGrid">
                    <?php
                    usort($ringkasanProduk, fn($a, $b) => (float)$b['total_prediksi'] <=> (float)$a['total_prediksi']);
                    $maxPred = !empty($ringkasanProduk) ? max(array_column($ringkasanProduk, 'total_prediksi')) : 1;
                    foreach ($ringkasanProduk as $rank => $rp):
                        $barW = $maxPred > 0 ? min(100, ((float)$rp['total_prediksi'] / $maxPred) * 100) : 0;
                    ?>
                        <div class="rw-produk-card"
                            data-produk="<?= esc(strtolower($rp['nama_produk'])) ?>"
                            data-rank="<?= $rank ?>">
                            <div class="rw-pc-header">
                                <div class="rw-pc-rank">#<?= $rank + 1 ?></div>
                                <span class="badge-mg badge-cyan" style="font-size:10px;padding:1px 7px">Forecast Only</span>
                            </div>
                            <div class="rw-pc-icon"><i class="bi bi-phone-fill"></i></div>
                            <!-- BUGFIX: nama produk tidak lagi tampil dua kali -->
                            <div class="rw-pc-nama"><?= esc($rp['nama_produk']) ?></div>

                            <div class="rw-pc-bar-wrap">
                                <div class="rw-pc-bar-fill" data-w="<?= $barW ?>"></div>
                            </div>

                            <div class="rw-pc-stats">
                                <div class="rw-pc-stat-item">
                                    <span class="rw-pc-stat-label">Total Prediksi</span>
                                    <span class="rw-pc-stat-val" style="color:var(--mg-light)">
                                        <?= number_format((float)$rp['total_prediksi']) ?>
                                    </span>
                                </div>
                                <div class="rw-pc-stat-item">
                                    <span class="rw-pc-stat-label">Periode</span>
                                    <span class="rw-pc-stat-val"><?= $rp['total_periode'] ?>×</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- No result produk -->
                <div class="rw-no-result" id="rwProdukNoResult" style="display:none">
                    <i class="bi bi-search"></i>
                    <div>Produk tidak ditemukan.</div>
                </div>

                <!-- ── Pagination Card Produk ── -->
                <div class="rw-pagination-bar" id="rwCardPagBar">
                    <div class="rw-pag-info" id="rwCardCountInfo">
                        Menampilkan 1–10 dari <?= count($ringkasanProduk) ?> produk
                    </div>
                    <div class="rw-pag-controls">
                        <button class="rw-pag-btn" id="rwCardPrev" disabled>
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <div class="rw-pag-pages" id="rwCardPages"></div>
                        <button class="rw-pag-btn" id="rwCardNext">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Trend Chart Lintas Periode ── -->
        <?php if (count($daftarPeriode) > 1): ?>
            <div class="card-mg">
                <div class="mg-card-header">
                    <div class="mg-card-title-group">
                        <span class="section-title">Tren Prediksi Lintas Periode</span>
                        <span class="badge-mg badge-cyan"><?= $totalPeriode ?> periode</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span class="rw-legend-item"><span class="rw-leg-dot" style="background:var(--mg-glow)"></span>Total Qty Prediksi</span>

                    </div>
                </div>
                <div class="rw-chart-wrap">
                    <canvas id="rwTrendChart" style="width:100%;max-height:280px"></canvas>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; // end if prediksiList not empty 
    ?>

</div><!-- /content-wrapper-inner -->


<!-- ════════════════════════════════════════════════════════════════
     STYLES
════════════════════════════════════════════════════════════════ -->
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

    .btn-sm-rw {
        padding: 6px 14px !important;
        font-size: 12px !important;
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

    /* ── Quick nav ── */
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

    /* ── Empty state ── */
    .rw-empty-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 80px 24px;
        text-align: center;
    }

    .rw-empty-icon {
        width: 80px;
        height: 80px;
        background: rgba(74, 159, 212, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
        color: var(--text-placeholder);
        margin-bottom: 16px;
    }

    .rw-empty-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-ink);
        margin-bottom: 6px;
    }

    .rw-empty-sub {
        font-size: 13px;
        color: var(--text-muted);
        max-width: 320px;
        line-height: 1.65;
    }

    /* ── Stats row ── */
    .rw-stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    @media(max-width:700px) {
        .rw-stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:420px) {
        .rw-stats-row {
            grid-template-columns: 1fr;
        }
    }

    .rw-stat-card {
        background: var(--card-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-md);
        padding: 18px 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        transition: var(--transition);
        animation: rwFadeIn .35s ease both;
    }

    .rw-stat-card:hover {
        border-color: rgba(74, 159, 212, .3);
        transform: translateY(-2px);
    }

    .highlight-stat {
        background: linear-gradient(135deg, rgba(16, 183, 127, .05), rgba(0, 151, 184, .03));
        border-color: rgba(16, 183, 127, .2);
    }

    @keyframes rwFadeIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    .rw-stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .rw-stat-val {
        font-family: var(--font-display);
        font-size: 24px;
        font-weight: 800;
        color: var(--text-ink);
        line-height: 1.1;
    }

    .rw-stat-lbl {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    /* ══════════════════════════════════
   FILTER SELECT DROPDOWN
══════════════════════════════════ */
    .rw-filter-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .rw-filter-select-wrap {
        position: relative;
        display: flex;
        align-items: center;
        min-width: 260px;
        max-width: 360px;
        flex: 1;
    }

    .rw-filter-icon {
        position: absolute;
        left: 14px;
        color: var(--accent-cyan);
        font-size: 14px;
        pointer-events: none;
        z-index: 1;
    }

    .rw-select-arrow {
        position: absolute;
        right: 14px;
        color: var(--text-muted);
        font-size: 12px;
        pointer-events: none;
        z-index: 1;
    }

    .rw-periode-select {
        width: 100%;
        padding: 10px 40px 10px 40px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-sm);
        color: var(--text-ink);
        font-size: 13px;
        font-weight: 600;
        appearance: none;
        -webkit-appearance: none;
        cursor: pointer;
        transition: var(--transition);
        outline: none;
    }

    .rw-periode-select:hover,
    .rw-periode-select:focus {
        border-color: rgba(74, 159, 212, .45);
        box-shadow: 0 0 0 3px rgba(74, 159, 212, .08);
        background: var(--card-bg);
    }

    .rw-periode-select option {
        background: var(--card-bg);
        color: var(--text-ink);
    }

    .rw-periode-aktif-info {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: rgba(74, 159, 212, .08);
        border: 1px solid rgba(74, 159, 212, .25);
        border-radius: var(--radius-sm);
        font-size: 12.5px;
        color: var(--mg-light);
    }

    .rw-periode-aktif-info strong {
        color: var(--accent-cyan);
    }

    /* ── Toolbar ── */
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
        box-shadow: 0 0 0 3px rgba(74, 159, 212, .1);
    }

    .mg-search-box input {
        border: none;
        background: transparent;
        color: var(--text-ink);
        font-size: 13px;
        outline: none;
        width: 150px;
    }

    .mg-search-box input::placeholder {
        color: var(--text-placeholder);
    }

    .rw-sort-wrap select {
        padding: 7px 10px;
        font-size: 12.5px;
    }

    /* ── Table ── */
    .mg-table-wrap {
        overflow-x: auto;
        border-radius: var(--radius-sm);
        border: 1px solid var(--surface-border);
    }

    .rw-table {
        min-width: 900px;
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

    .rw-produk-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .rw-produk-icon {
        width: 30px;
        height: 30px;
        background: linear-gradient(135deg, rgba(74, 159, 212, .15), rgba(0, 151, 184, .1));
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent-cyan);
        font-size: 13px;
        flex-shrink: 0;
    }

    .rw-produk-nama {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-ink);
        line-height: 1.3;
    }

    .rw-periode-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 10px;
        border-radius: 6px;
        background: rgba(0, 151, 184, .1);
        color: var(--accent-cyan);
        font-size: 11.5px;
        font-weight: 600;
    }

    .rw-qty-pred {
        font-family: 'Courier New', monospace;
        font-size: 14px;
        font-weight: 700;
        color: var(--mg-light);
    }

    .rw-qty-aktual {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        color: var(--text-ink);
    }

    .null-val {
        color: var(--text-placeholder);
        font-size: 12px;
    }

    .mape-pill {
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .mape-good {
        background: rgba(16, 183, 127, .1);
        color: var(--accent-green);
        border: 1px solid rgba(16, 183, 127, .2);
    }

    .mape-ok {
        background: rgba(212, 160, 23, .1);
        color: var(--accent-yellow);
        border: 1px solid rgba(212, 160, 23, .2);
    }

    .mape-bad {
        background: rgba(229, 62, 62, .1);
        color: var(--accent-red);
        border: 1px solid rgba(229, 62, 62, .2);
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-success {
        background: rgba(16, 183, 127, .1);
        color: var(--accent-green);
        border: 1px solid rgba(16, 183, 127, .2);
    }

    .status-forecast {
        background: rgba(74, 159, 212, .1);
        color: var(--mg-light);
        border: 1px solid rgba(74, 159, 212, .2);
    }

    .rw-no-result {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 48px 24px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .rw-no-result i {
        font-size: 32px;
        opacity: .3;
    }

    /* ══════════════════════════════════
   PAGINATION SHARED
══════════════════════════════════ */
    .rw-pagination-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        padding: 14px 0 4px;
        border-top: 1px solid var(--surface-border);
        margin-top: 10px;
    }

    .rw-pag-info {
        font-size: 12px;
        color: var(--text-muted);
    }

    .rw-pag-controls {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .rw-pag-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid var(--surface-border);
        background: var(--card-bg-alt);
        color: var(--text-muted);
        cursor: pointer;
        font-size: 13px;
        transition: var(--transition);
    }

    .rw-pag-btn:hover:not(:disabled) {
        border-color: rgba(74, 159, 212, .4);
        color: var(--mg-light);
        background: rgba(74, 159, 212, .08);
    }

    .rw-pag-btn:disabled {
        opacity: .35;
        cursor: not-allowed;
    }

    .rw-pag-pages {
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .rw-page-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 6px;
        border-radius: 8px;
        border: 1px solid var(--surface-border);
        background: var(--card-bg-alt);
        color: var(--text-muted);
        cursor: pointer;
        font-size: 12.5px;
        font-weight: 600;
        transition: var(--transition);
        user-select: none;
    }

    .rw-page-num:hover {
        border-color: rgba(74, 159, 212, .35);
        color: var(--mg-light);
        background: rgba(74, 159, 212, .07);
    }

    .rw-page-num.active {
        background: rgba(74, 159, 212, .18);
        border-color: rgba(74, 159, 212, .45);
        color: var(--mg-light);
    }

    .rw-page-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 32px;
        color: var(--text-placeholder);
        font-size: 13px;
        user-select: none;
    }

    /* ── Ringkasan per produk ── */
    .rw-produk-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
        gap: 14px;
    }

    @media(max-width:600px) {
        .rw-produk-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .rw-produk-card {
        background: var(--card-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-md);
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: var(--transition);
        animation: rwFadeIn .3s ease both;
    }

    .rw-produk-card:hover {
        border-color: rgba(74, 159, 212, .3);
        transform: translateY(-2px);
    }

    .rw-pc-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .rw-pc-rank {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-placeholder);
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        padding: 1px 7px;
        border-radius: 20px;
    }

    .rw-pc-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, rgba(74, 159, 212, .15), rgba(0, 151, 184, .1));
        border: 1px solid rgba(74, 159, 212, .2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent-cyan);
        font-size: 15px;
    }

    .rw-pc-nama {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-ink);
        line-height: 1.4;
    }

    .rw-pc-bar-wrap {
        height: 4px;
        background: var(--card-bg-alt);
        border-radius: 999px;
        overflow: hidden;
        border: 1px solid var(--surface-border);
    }

    .rw-pc-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--mg-glow), var(--accent-cyan));
        border-radius: 999px;
        width: 0;
        transition: width 1.1s ease;
    }

    .rw-pc-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }

    .rw-pc-stat-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 6px 8px;
        background: var(--card-bg-alt);
        border-radius: 6px;
        border: 1px solid var(--surface-border);
    }

    .rw-pc-stat-label {
        font-size: 9.5px;
        color: var(--text-placeholder);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .rw-pc-stat-val {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-ink);
    }

    /* ── Chart ── */
    .rw-chart-wrap {
        position: relative;
        padding: 4px 0;
    }

    .rw-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11.5px;
        color: var(--text-muted);
    }

    .rw-leg-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    /* ── Badges ── */
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

    .section-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-ink);
    }

    /* Spinner tombol sinkron */
    .rw-spin-sm {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 2px solid var(--surface-border);
        border-top-color: var(--accent-cyan);
        border-radius: 50%;
        animation: rwSpinSm .7s linear infinite;
        vertical-align: middle;
        margin-right: 4px;
    }

    @keyframes rwSpinSm {
        to {
            transform: rotate(360deg);
        }
    }
</style>


<!-- ════════════════════════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        /* ══════════════════════════════════════════════════════════
           HELPER: Pagination Engine (generic, reusable)
           items    : NodeList / Array
           perPage  : int (default 10)
           callbacks: { onPageChange(visibleItems, page, totalPages, totalItems) }
        ══════════════════════════════════════════════════════════ */
        function PaginationEngine(cfg) {
            const {
                items, // semua elemen yang bisa ditampilkan
                perPage = 10,
                btnPrev,
                btnNext,
                pagesContainer,
                infoEl,
                infoTpl, // fn(from, to, total) → string
                onShow, // fn(item) — tampilkan item
                onHide, // fn(item) — sembunyikan item
                onAfterRender, // fn() — callback setelah render (bar animasi dll)
            } = cfg;

            let currentPage = 1;
            let filteredItems = Array.from(items);

            function totalPages() {
                return Math.max(1, Math.ceil(filteredItems.length / perPage));
            }

            function render() {
                const tp = totalPages();
                const start = (currentPage - 1) * perPage;
                const end = Math.min(start + perPage, filteredItems.length);

                // Sembunyikan semua dulu
                Array.from(items).forEach(el => onHide(el));
                // Tampilkan halaman aktif
                filteredItems.slice(start, end).forEach(el => onShow(el));

                // Update info
                if (infoEl) {
                    if (filteredItems.length === 0) {
                        infoEl.textContent = 'Tidak ada data';
                    } else {
                        infoEl.textContent = infoTpl ?
                            infoTpl(start + 1, end, filteredItems.length) :
                            `Menampilkan ${start + 1}–${end} dari ${filteredItems.length}`;
                    }
                }

                // Tombol prev / next
                if (btnPrev) btnPrev.disabled = currentPage <= 1;
                if (btnNext) btnNext.disabled = currentPage >= tp;

                // Nomor halaman
                renderPageNumbers(tp);

                if (onAfterRender) onAfterRender(filteredItems.slice(start, end));
            }

            function renderPageNumbers(tp) {
                if (!pagesContainer) return;
                pagesContainer.innerHTML = '';

                // Tentukan range halaman yang ditampilkan (max 5 nomor)
                let pages = [];
                if (tp <= 7) {
                    for (let i = 1; i <= tp; i++) pages.push(i);
                } else {
                    // Selalu tampilkan 1, ..., (cp-1, cp, cp+1), ..., tp
                    const around = new Set([1, tp, currentPage]);
                    if (currentPage > 1) around.add(currentPage - 1);
                    if (currentPage < tp) around.add(currentPage + 1);
                    pages = Array.from(around).sort((a, b) => a - b);
                }

                let last = 0;
                pages.forEach(p => {
                    if (p - last > 1) {
                        // Ellipsis
                        const dot = document.createElement('span');
                        dot.className = 'rw-page-ellipsis';
                        dot.textContent = '…';
                        pagesContainer.appendChild(dot);
                    }
                    const btn = document.createElement('button');
                    btn.className = 'rw-page-num' + (p === currentPage ? ' active' : '');
                    btn.textContent = p;
                    btn.addEventListener('click', () => goTo(p));
                    pagesContainer.appendChild(btn);
                    last = p;
                });
            }

            function goTo(p) {
                const tp = totalPages();
                currentPage = Math.max(1, Math.min(p, tp));
                render();
            }

            function setItems(newItems) {
                filteredItems = newItems;
                currentPage = 1;
                render();
            }

            // Bind tombol
            btnPrev?.addEventListener('click', () => goTo(currentPage - 1));
            btnNext?.addEventListener('click', () => goTo(currentPage + 1));

            // Init
            render();

            return {
                goTo,
                setItems,
                render
            };
        }

        /* ══════════════════════════════════════════════════════════
           1. FILTER PERIODE — SELECT DROPDOWN
        ══════════════════════════════════════════════════════════ */
        const periodeSelect = document.getElementById('rwPeriodeSelect');
        if (periodeSelect) {
            periodeSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                if (!this.value) {
                    // Semua periode
                    window.location.href = BASE_URL + 'prediksi/riwayat';
                } else {
                    const url = selected.dataset.url;
                    if (url) window.location.href = url;
                }
            });
        }

        /* ══════════════════════════════════════════════════════════
           2. TABEL — Search + Sort + Pagination
        ══════════════════════════════════════════════════════════ */
        const allRows = Array.from(document.querySelectorAll('#rwTableBody .rw-row'));
        const rwSearch = document.getElementById('rwSearch');
        const rwSort = document.getElementById('rwSort');
        const rwBadge = document.getElementById('rwBadgeCount');
        const rwNoResult = document.getElementById('rwNoResult');

        let activeRows = [...allRows]; // rows yang lolos filter

        const tablePag = PaginationEngine({
            items: allRows,
            perPage: 10,
            btnPrev: document.getElementById('rwTablePrev'),
            btnNext: document.getElementById('rwTableNext'),
            pagesContainer: document.getElementById('rwTablePages'),
            infoEl: document.getElementById('rwCountInfo'),
            infoTpl: (f, t, total) => `Menampilkan ${f}–${t} dari ${total} record`,
            onShow: el => {
                el.style.display = '';
            },
            onHide: el => {
                el.style.display = 'none';
            },
            onAfterRender: (visItems) => {
                // Re-numbering berdasarkan posisi di activeRows
                visItems.forEach(r => {
                    const globalIdx = activeRows.indexOf(r);
                    const noEl = r.querySelector('.row-no');
                    if (noEl) noEl.textContent = globalIdx + 1;
                });
                if (rwBadge) rwBadge.textContent = activeRows.length + ' record';
                if (rwNoResult) rwNoResult.style.display = activeRows.length === 0 ? 'flex' : 'none';
            },
        });

        function applySortFilter() {
            const q = (rwSearch?.value || '').toLowerCase().trim();

            // Filter
            let result = allRows.filter(r =>
                q === '' || r.dataset.nama.includes(q)
            );

            // Sort
            const sortVal = rwSort?.value || 'periode_desc';
            result.sort((a, b) => {
                switch (sortVal) {
                    case 'periode_desc':
                        return b.dataset.periode.localeCompare(a.dataset.periode);
                    case 'periode_asc':
                        return a.dataset.periode.localeCompare(b.dataset.periode);
                    case 'qty_desc':
                        return parseFloat(b.dataset.qty) - parseFloat(a.dataset.qty);
                    case 'qty_asc':
                        return parseFloat(a.dataset.qty) - parseFloat(b.dataset.qty);
                    case 'nama_asc':
                        return a.dataset.nama.localeCompare(b.dataset.nama);
                    default:
                        return 0;
                }
            });

            // Susun ulang DOM tbody sesuai urutan sort
            const tbody = document.getElementById('rwTableBody');
            result.forEach(r => tbody.appendChild(r));

            activeRows = result;
            tablePag.setItems(result);
        }

        rwSearch?.addEventListener('input', applySortFilter);
        rwSort?.addEventListener('change', applySortFilter);

        /* ══════════════════════════════════════════════════════════
           3. CARD GRID PRODUK — Search + Pagination
        ══════════════════════════════════════════════════════════ */
        const allCards = Array.from(document.querySelectorAll('.rw-produk-card'));
        const rwProdukSearch = document.getElementById('rwProdukSearch');
        const rwProdukBadge = document.getElementById('rwProdukBadge');
        const rwProdukNoRes = document.getElementById('rwProdukNoResult');

        let activeCards = [...allCards];

        const cardPag = PaginationEngine({
            items: allCards,
            perPage: 10,
            btnPrev: document.getElementById('rwCardPrev'),
            btnNext: document.getElementById('rwCardNext'),
            pagesContainer: document.getElementById('rwCardPages'),
            infoEl: document.getElementById('rwCardCountInfo'),
            infoTpl: (f, t, total) => `Menampilkan ${f}–${t} dari ${total} produk`,
            onShow: el => {
                el.style.display = '';
            },
            onHide: el => {
                el.style.display = 'none';
            },
            onAfterRender: (visCards) => {
                // Animasi bar untuk card yang baru muncul
                visCards.forEach(card => {
                    const bar = card.querySelector('.rw-pc-bar-fill');
                    if (bar && bar.style.width === '0px' || bar?.style.width === '') {
                        const w = bar?.dataset.w;
                        if (w) setTimeout(() => {
                            bar.style.width = w + '%';
                        }, 100);
                    }
                });
                if (rwProdukBadge) rwProdukBadge.textContent = activeCards.length + ' produk';
                if (rwProdukNoRes) rwProdukNoRes.style.display = activeCards.length === 0 ? 'flex' : 'none';
            },
        });

        // Animasi bar halaman pertama
        requestAnimationFrame(() => {
            // Hanya bar yang visible (halaman 1)
            document.querySelectorAll('.rw-produk-card:not([style*="display: none"]) .rw-pc-bar-fill')
                .forEach(el => {
                    const w = el.dataset.w;
                    if (w) setTimeout(() => el.style.width = w + '%', 200);
                });
        });

        rwProdukSearch?.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            activeCards = allCards.filter(c =>
                q === '' || (c.dataset.produk || '').includes(q)
            );
            cardPag.setItems(activeCards);
        });

        /* ══════════════════════════════════════════════════════════
           4. EXPORT CSV
        ══════════════════════════════════════════════════════════ */
        document.getElementById('btnRwExport')?.addEventListener('click', function() {
            // Export semua row yang lolos filter (bukan hanya halaman aktif)
            const exportRows = activeRows;
            if (!exportRows.length) {
                alert('Tidak ada data untuk diexport.');
                return;
            }
            const headers = ['No', 'Nama Produk', 'Periode', 'Qty Prediksi', 'Dibuat'];
            const csvRows = [headers.join(',')];
            // Sementara tampilkan semua row agar innerText bisa dibaca
            exportRows.forEach((r, i) => {
                const prevDisplay = r.style.display;
                r.style.display = '';
                const cells = r.querySelectorAll('td');
                const vals = Array.from(cells).map(td => {
                    const txt = td.innerText.trim().replace(/\n/g, ' ').replace(/,/g, ';');
                    return '"' + txt + '"';
                });
                vals[0] = '"' + (i + 1) + '"';
                csvRows.push(vals.join(','));
                r.style.display = prevDisplay;
            });
            const blob = new Blob(['\uFEFF' + csvRows.join('\n')], {
                type: 'text/csv;charset=utf-8;'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'riwayat_prediksi_' + new Date().toISOString().slice(0, 10) + '.csv';
            a.click();
            URL.revokeObjectURL(url);
        });

        /* ══════════════════════════════════════════════════════════
           6. TREND CHART
        ══════════════════════════════════════════════════════════ */
        const ctx = document.getElementById('rwTrendChart');
        if (!ctx) return;

        const periodeData = <?= json_encode(
                                (function () use ($prediksiList, $bulanPendek) {
                                    $grouped = [];
                                    foreach ($prediksiList as $p) {
                                        $key = $p['tahun_prediksi'] . '-' . str_pad($p['bulan_prediksi'], 2, '0', STR_PAD_LEFT);
                                        if (!isset($grouped[$key])) {
                                            // SESUDAH
                                            $grouped[$key] = [
                                                'label'    => ($bulanPendek[(int)$p['bulan_prediksi']] ?? $p['bulan_prediksi']) . ' ' . $p['tahun_prediksi'],
                                                'prediksi' => 0,
                                            ];
                                        }
                                        $grouped[$key]['prediksi'] += (float)$p['qty_prediksi'];
                                    }
                                    ksort($grouped);
                                    return array_values($grouped);
                                })()
                            ) ?>;

        if (!periodeData.length) return;

        const labels = periodeData.map(d => d.label);
        const predVals = periodeData.map(d => d.prediksi);

        const datasets = [{
            label: 'Total Qty Prediksi',
            data: predVals,
            borderColor: 'rgba(74,159,212,0.9)',
            backgroundColor: 'rgba(74,159,212,0.12)',
            borderWidth: 2.5,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: 'rgba(74,159,212,1)',
            pointRadius: 5,
            pointHoverRadius: 8,
        }];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            color: '#8b9aad',
                            font: {
                                size: 12
                            },
                            boxWidth: 12,
                            boxHeight: 12
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1a2332',
                        borderColor: 'rgba(74,159,212,0.3)',
                        borderWidth: 1,
                        titleColor: '#e2e8f0',
                        bodyColor: '#8b9aad',
                        padding: 12,
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y !== null ? Math.round(ctx.parsed.y) : '—'} unit`
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#5a7a9a',
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.04)'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#5a7a9a',
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(255,255,255,0.06)'
                        }
                    }
                }
            }
        });
    });
</script>