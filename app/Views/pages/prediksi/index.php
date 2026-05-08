<?php
/**
 * View  : pages/prediksi/index.php
 * Modul : Prediksi Penjualan — Mi Store Kudus
 * Fix   : filter/sort bekerja di kedua view, pagination 10 per halaman
 */
?>

<div class="content-wrapper-inner">

    <!-- ── Page Header ── -->
    <div class="mg-page-header">
        <div>
            <h1 class="page-title">Prediksi Penjualan</h1>
            <p class="page-subtitle">Hasil forecast Random Forest per produk</p>
        </div>
        <div class="header-actions">
            <?php if (!empty($prediksiList) && !empty($periode)): ?>
                <button class="btn-mg btn-outline-mg" id="btnSinkronAktual"
                    data-tahun="<?= $periode['tahun'] ?>"
                    data-bulan="<?= $periode['bulan'] ?>">
                    <i class="bi bi-arrow-repeat"></i> Sinkron Aktual
                </button>
            <?php endif; ?>
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
        <a href="<?= base_url('prediksi') ?>" class="quick-nav-pill active">
            <i class="bi bi-graph-up-arrow"></i><span>Prediksi Penjualan</span>
            <span class="pill-badge ai-badge">AI</span>
        </a>
        <a href="<?= base_url('prediksi/jalankan') ?>" class="quick-nav-pill">
            <i class="bi bi-play-circle-fill"></i><span>Jalankan Prediksi</span>
        </a>
        <a href="<?= base_url('prediksi/riwayat') ?>" class="quick-nav-pill">
            <i class="bi bi-clock-history"></i><span>Riwayat Prediksi</span>
        </a>
        <a href="<?= base_url('prediksi/akurasi') ?>" class="quick-nav-pill">
            <i class="bi bi-bullseye"></i><span>Evaluasi Model</span>
        </a>
    </div>

    <?php
    $bulanNama   = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $bulanPendek = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    ?>

    <?php if (empty($prediksiList)): ?>
        <div class="pred-empty-wrap">
            <div class="pred-empty-icon">
                <svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" width="120" height="120">
                    <circle cx="60" cy="60" r="58" stroke="rgba(74,159,212,0.15)" stroke-width="2"/>
                    <path d="M30 85 L45 60 L58 72 L72 45 L90 55" stroke="rgba(74,159,212,0.4)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    <circle cx="90" cy="55" r="4" fill="rgba(74,159,212,0.5)"/>
                    <circle cx="72" cy="45" r="4" fill="rgba(0,151,184,0.5)"/>
                    <circle cx="58" cy="72" r="4" fill="rgba(16,183,127,0.5)"/>
                    <path d="M60 28 L63 35 L70 35 L64.5 40 L66.5 47 L60 43 L53.5 47 L55.5 40 L50 35 L57 35 Z" fill="rgba(212,160,23,0.4)"/>
                </svg>
            </div>
            <div class="pred-empty-title">Belum Ada Data Prediksi</div>
            <div class="pred-empty-sub">Jalankan model prediksi untuk mendapatkan forecast penjualan per produk.</div>
            <div class="pred-empty-actions">
                <a href="<?= base_url('prediksi/jalankan') ?>" class="btn-mg btn-primary-mg" style="text-decoration:none">
                    <i class="bi bi-play-circle-fill"></i> Jalankan Prediksi Sekarang
                </a>
                <?php if (empty($modelAktif)): ?>
                    <a href="<?= base_url('training/proses') ?>" class="btn-mg btn-outline-mg" style="text-decoration:none">
                        <i class="bi bi-cpu-fill"></i> Latih Model Dulu
                    </a>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>

        <!-- ── Model Strip ── -->
        <?php if ($modelAktif): ?>
            <div class="pred-model-strip">
                <div class="pred-strip-left">
                    <div class="pred-strip-dot"></div>
                    <i class="bi bi-cpu-fill" style="color:var(--accent-green)"></i>
                    <span class="pred-strip-label">Model Aktif:</span>
                    <span class="pred-strip-versi"><?= esc($modelAktif['versi'] ?? '—') ?></span>
                    <?php if (!empty($modelAktif['akurasi'])): ?>
                        <span class="pred-strip-acc">
                            <i class="bi bi-bullseye"></i>
                            <?= number_format((float)$modelAktif['akurasi'], 2) ?>% akurasi
                        </span>
                    <?php endif; ?>
                </div>
                <div class="pred-strip-right">
                    <?php if (!empty($periode)): ?>
                        <span class="pred-strip-period">
                            <i class="bi bi-calendar3"></i>
                            Periode: <?= $bulanNama[(int)$periode['bulan']] ?? $periode['bulan'] ?> <?= $periode['tahun'] ?>
                        </span>
                    <?php endif; ?>
                    <a href="<?= base_url('prediksi/akurasi') ?>" class="btn-mg btn-outline-mg btn-xs-mg" style="text-decoration:none">
                        <i class="bi bi-bar-chart-fill"></i> Detail
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Summary Stats ── -->
        <div class="pred-stats-row">
            <div class="pred-stat-card">
                <div class="pred-stat-icon" style="background:rgba(74,159,212,0.12);color:var(--mg-light)">
                    <i class="bi bi-phone-fill"></i>
                </div>
                <div class="pred-stat-val"><?= number_format(count($prediksiList)) ?></div>
                <div class="pred-stat-lbl">Produk Diprediksi</div>
            </div>
            <div class="pred-stat-card">
                <div class="pred-stat-icon" style="background:rgba(16,183,127,0.12);color:var(--accent-green)">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div class="pred-stat-val"><?= number_format(array_sum(array_column($prediksiList, 'qty_prediksi'))) ?></div>
                <div class="pred-stat-lbl">Total Qty Prediksi</div>
            </div>
            <div class="pred-stat-card">
                <?php
                $topPrediksi = !empty($prediksiList) ? array_reduce($prediksiList, fn($carry, $item) =>
                    $carry === null || (float)$item['qty_prediksi'] > (float)$carry['qty_prediksi'] ? $item : $carry) : null;
                ?>
                <div class="pred-stat-icon" style="background:rgba(212,160,23,0.12);color:var(--accent-yellow)">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div class="pred-stat-val" style="font-size:14px;line-height:1.2">
                    <?= $topPrediksi ? esc(mb_substr($topPrediksi['nama_produk'], 0, 20) . (mb_strlen($topPrediksi['nama_produk']) > 20 ? '…' : '')) : '—' ?>
                </div>
                <div class="pred-stat-lbl">Prediksi Tertinggi</div>
            </div>
            <div class="pred-stat-card">
                <div class="pred-stat-icon" style="background:rgba(0,151,184,0.12);color:var(--accent-cyan)">
                    <i class="bi bi-calendar3-range"></i>
                </div>
                <div class="pred-stat-val">
                    <?= !empty($periode) ? ($bulanPendek[(int)$periode['bulan']] ?? $periode['bulan']) . ' ' . $periode['tahun'] : '—' ?>
                </div>
                <div class="pred-stat-lbl">Periode Prediksi</div>
            </div>
        </div>

        <!-- ── Filter & Toolbar ── -->
        <div class="card-mg">
            <div class="mg-card-header">
                <div class="mg-card-title-group">
                    <span class="section-title">Hasil Prediksi</span>
                    <span class="badge-mg badge-cyan" id="badgePredCount"><?= count($prediksiList) ?> produk</span>
                </div>
                <div class="mg-card-toolbar">
                    <div class="mg-search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchPrediksi" placeholder="Cari produk…">
                    </div>
                    <div class="pred-sort-wrap">
                        <select id="sortPrediksi" class="form-mg form-mg-sm">
                            <option value="qty_desc">Qty ↓ Tertinggi</option>
                            <option value="qty_asc">Qty ↑ Terendah</option>
                            <option value="nama_asc">Nama A–Z</option>
                            <option value="nama_desc">Nama Z–A</option>
                            <option value="selisih_desc">Selisih ↓</option>
                        </select>
                    </div>
                    <div class="pred-view-toggle">
                        <button class="pred-view-btn active" id="btnViewCard" title="Card view">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </button>
                        <button class="pred-view-btn" id="btnViewTable" title="Table view">
                            <i class="bi bi-table"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Card View ── -->
            <div id="viewCard">
                <div class="pred-card-grid" id="predCardGrid">
                    <?php
                    $maxQty = max(array_column($prediksiList, 'qty_prediksi')) ?: 1;
                    foreach ($prediksiList as $idx => $p):
                        $qty     = (float)$p['qty_prediksi'];
                        $aktual  = $p['qty_aktual'] !== null ? (float)$p['qty_aktual'] : null;
                        $selisih = $p['selisih']    !== null ? (float)$p['selisih']    : null;
                        $mapeRow = ($aktual !== null && $aktual > 0) ? abs(($aktual - $qty) / $aktual) * 100 : null;
                        $selColor = $selisih === null ? 'var(--text-muted)' : ($selisih >= 0 ? 'var(--accent-green)' : 'var(--accent-red)');
                        $selIcon  = $selisih === null ? 'bi-dash' : ($selisih >= 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short');
                        $barW     = min(100, ($qty / $maxQty) * 100);
                    ?>
                        <div class="pred-card"
                            data-nama="<?= esc(strtolower($p['nama_produk'])) ?>"
                            data-qty="<?= $qty ?>"
                            data-selisih="<?= $selisih ?? 0 ?>"
                            data-idx="<?= $idx ?>">
                            <div class="pred-card-top">
                                <div class="pred-card-rank">#<span class="rank-num"><?= $idx + 1 ?></span></div>
                                <div class="pred-card-badge">
                                    <?php if ($aktual !== null): ?>
                                        <span class="pred-badge-aktual">Ada Aktual</span>
                                    <?php else: ?>
                                        <span class="pred-badge-forecast">Forecast</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="pred-card-icon"><i class="bi bi-phone-fill"></i></div>
                            <div class="pred-card-nama"><?= esc($p['nama_produk']) ?></div>
                            <div class="pred-card-qty-wrap">
                                <div class="pred-card-qty-label">Qty Prediksi</div>
                                <div class="pred-card-qty"><?= number_format($qty, 1) ?></div>
                            </div>
                            <div class="pred-card-bar-wrap">
                                <div class="pred-card-bar-fill" data-w="<?= $barW ?>"></div>
                            </div>
                            <?php if ($aktual !== null): ?>
                                <div class="pred-card-actual-row">
                                    <div class="pred-card-actual-item">
                                        <span class="pred-cai-label">Aktual</span>
                                        <span class="pred-cai-val"><?= number_format($aktual) ?></span>
                                    </div>
                                    <div class="pred-card-actual-item">
                                        <span class="pred-cai-label">Selisih</span>
                                        <span class="pred-cai-val" style="color:<?= $selColor ?>">
                                            <i class="bi <?= $selIcon ?>"></i>
                                            <?= $selisih >= 0 ? '+' : '' ?><?= number_format($selisih, 1) ?>
                                        </span>
                                    </div>
                                    <?php if ($mapeRow !== null): ?>
                                        <div class="pred-card-actual-item">
                                            <span class="pred-cai-label">MAPE</span>
                                            <span class="pred-cai-val" style="color:<?= $mapeRow < 10 ? 'var(--accent-green)' : ($mapeRow < 20 ? 'var(--accent-yellow)' : 'var(--accent-red)') ?>">
                                                <?= number_format($mapeRow, 1) ?>%
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="pred-card-date">
                                <i class="bi bi-calendar3"></i>
                                <?= $bulanNama[(int)$p['bulan_prediksi']] ?? $p['bulan_prediksi'] ?>
                                <?= $p['tahun_prediksi'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="pred-no-result" id="predNoResult" style="display:none">
                    <i class="bi bi-search" style="font-size:32px;opacity:.3"></i>
                    <div style="margin-top:8px;font-size:13px;color:var(--text-muted)">Produk tidak ditemukan.</div>
                </div>
                <!-- Pagination Card -->
                <div class="pred-pagination" id="paginationCard"></div>
            </div>

            <!-- ── Table View ── -->
            <div id="viewTable" style="display:none">
                <div class="mg-table-wrap">
                    <table class="table-mg" id="predTableView">
                        <thead>
                            <tr>
                                <th style="width:46px">No</th>
                                <th>Nama Produk</th>
                                <th style="text-align:center;width:120px">Periode</th>
                                <th style="text-align:right;width:130px">Qty Prediksi</th>
                                <th style="text-align:right;width:110px">Qty Aktual</th>
                                <th style="text-align:right;width:100px">Selisih</th>
                                <th style="text-align:center;width:90px">MAPE</th>
                                <th style="text-align:center;width:90px">Status</th>
                            </tr>
                        </thead>
                        <tbody id="predTableBody">
                            <?php foreach ($prediksiList as $i => $p):
                                $qty    = (float)$p['qty_prediksi'];
                                $aktual = $p['qty_aktual'] !== null ? (float)$p['qty_aktual'] : null;
                                $sel    = $p['selisih']    !== null ? (float)$p['selisih']    : null;
                                $mapeR  = ($aktual !== null && $aktual > 0) ? abs(($aktual - $qty) / $aktual) * 100 : null;
                                $selColor = $sel === null ? '' : ($sel >= 0 ? 'color:var(--accent-green)' : 'color:var(--accent-red)');
                            ?>
                                <tr data-nama="<?= esc(strtolower($p['nama_produk'])) ?>"
                                    data-qty="<?= $qty ?>"
                                    data-selisih="<?= $sel ?? 0 ?>"
                                    data-idx="<?= $i ?>">
                                    <td><span class="row-no row-no-num"><?= $i + 1 ?></span></td>
                                    <td>
                                        <div class="pred-tbl-produk">
                                            <div class="pred-tbl-icon"><i class="bi bi-phone-fill"></i></div>
                                            <span><?= esc($p['nama_produk']) ?></span>
                                        </div>
                                    </td>
                                    <td style="text-align:center">
                                        <span class="month-badge-sm">
                                            <?= ($bulanPendek[(int)$p['bulan_prediksi']] ?? $p['bulan_prediksi']) . ' ' . $p['tahun_prediksi'] ?>
                                        </span>
                                    </td>
                                    <td style="text-align:right">
                                        <span class="pred-qty-val"><?= number_format($qty, 1) ?></span>
                                    </td>
                                    <td style="text-align:right">
                                        <?= $aktual !== null ? '<span class="pred-aktual-val">' . number_format($aktual) . '</span>' : '<span class="null-val">—</span>' ?>
                                    </td>
                                    <td style="text-align:right;<?= $selColor ?>">
                                        <?php if ($sel !== null): ?>
                                            <span style="font-weight:600"><?= $sel >= 0 ? '+' : '' ?><?= number_format($sel, 1) ?></span>
                                        <?php else: ?><span class="null-val">—</span><?php endif; ?>
                                    </td>
                                    <td style="text-align:center">
                                        <?php if ($mapeR !== null): ?>
                                            <span class="mape-pill <?= $mapeR < 10 ? 'mape-good' : ($mapeR < 20 ? 'mape-ok' : 'mape-bad') ?>">
                                                <?= number_format($mapeR, 1) ?>%
                                            </span>
                                        <?php else: ?><span class="null-val">—</span><?php endif; ?>
                                    </td>
                                    <td style="text-align:center">
                                        <?php if ($aktual !== null): ?>
                                            <span class="status-pill status-success">Terverifikasi</span>
                                        <?php else: ?>
                                            <span class="status-pill status-forecast">Forecast</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination Table -->
                <div class="pred-pagination" id="paginationTable"></div>
            </div>
        </div>

        <!-- ── Chart ── -->
        <div class="card-mg">
            <div class="mg-card-header">
                <div class="mg-card-title-group">
                    <span class="section-title">Visualisasi Prediksi</span>
                    <span class="badge-mg badge-cyan">
                        <?= !empty($periode) ? ($bulanNama[(int)$periode['bulan']] ?? '') . ' ' . $periode['tahun'] : 'Semua Periode' ?>
                    </span>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span class="chart-legend-item"><span class="clg-dot" style="background:var(--mg-glow)"></span>Prediksi</span>
                    <?php if (array_filter($prediksiList, fn($r) => $r['qty_aktual'] !== null)): ?>
                        <span class="chart-legend-item"><span class="clg-dot" style="background:var(--accent-green)"></span>Aktual</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="pred-chart-wrap">
                <canvas id="predChart" style="width:100%;max-height:320px"></canvas>
            </div>
        </div>

    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════
     STYLES
══════════════════════════════════════════════════════════ -->
<style>
.content-wrapper-inner{display:flex;flex-direction:column;gap:24px}
.mg-page-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px}
.header-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.btn-outline-mg{background:var(--card-bg-alt);color:var(--text-muted);border:1px solid var(--surface-border)}
.btn-outline-mg:hover{border-color:var(--mg-light);color:var(--mg-light)}
.btn-xs-mg{padding:5px 10px!important;font-size:11.5px!important}
.quick-nav-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.quick-nav-pill{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:var(--radius-sm);background:var(--card-bg-alt);border:1px solid var(--surface-border);color:var(--text-muted);font-size:13px;font-weight:600;text-decoration:none;transition:var(--transition);white-space:nowrap}
.quick-nav-pill:hover{background:rgba(74,159,212,0.1);border-color:rgba(74,159,212,0.35);color:var(--mg-light);transform:translateY(-1px)}
.quick-nav-pill.active{background:rgba(74,159,212,0.15);border-color:rgba(74,159,212,0.4);color:var(--mg-light)}
.pill-badge{display:inline-flex;align-items:center;padding:1px 7px;border-radius:20px;font-size:10px;font-weight:700}
.ai-badge{background:rgba(74,159,212,0.15);color:var(--mg-light);border:1px solid rgba(74,159,212,0.3)}
.pred-empty-wrap{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:80px 24px;text-align:center}
.pred-empty-icon{margin-bottom:20px;opacity:.85}
.pred-empty-title{font-size:16px;font-weight:700;color:var(--text-ink);margin-bottom:8px}
.pred-empty-sub{font-size:13px;color:var(--text-muted);max-width:340px;line-height:1.65;margin-bottom:20px}
.pred-empty-actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:center}
.pred-model-strip{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:12px 18px;background:linear-gradient(135deg,rgba(16,183,127,0.07),rgba(0,151,184,0.04));border:1px solid rgba(16,183,127,0.22);border-radius:var(--radius-md)}
.pred-strip-left{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.pred-strip-dot{width:8px;height:8px;border-radius:50%;background:var(--accent-green);box-shadow:0 0 0 3px rgba(16,183,127,0.2);animation:pDot 2s infinite;flex-shrink:0}
@keyframes pDot{0%,100%{box-shadow:0 0 0 3px rgba(16,183,127,0.2)}50%{box-shadow:0 0 0 6px rgba(16,183,127,0.08)}}
.pred-strip-label{font-size:12px;color:var(--text-muted)}
.pred-strip-versi{font-size:13px;font-weight:700;color:var(--accent-green)}
.pred-strip-acc{font-size:12px;color:var(--text-muted);display:inline-flex;align-items:center;gap:4px}
.pred-strip-acc i{color:var(--accent-cyan)}
.pred-strip-right{display:flex;align-items:center;gap:10px}
.pred-strip-period{font-size:12px;color:var(--text-muted);display:inline-flex;align-items:center;gap:5px}
.pred-strip-period i{color:var(--text-placeholder)}
.pred-stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
@media(max-width:860px){.pred-stats-row{grid-template-columns:repeat(2,1fr)}}
@media(max-width:480px){.pred-stats-row{grid-template-columns:1fr 1fr}}
.pred-stat-card{background:var(--card-bg);border:1px solid var(--surface-border);border-radius:var(--radius-md);padding:16px;display:flex;flex-direction:column;gap:6px;transition:var(--transition)}
.pred-stat-card:hover{border-color:rgba(74,159,212,0.3);transform:translateY(-2px)}
.pred-stat-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px}
.pred-stat-val{font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--text-ink);line-height:1.1}
.pred-stat-lbl{font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em}
.mg-card-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px}
.mg-card-title-group{display:flex;align-items:center;gap:10px}
.mg-card-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.mg-search-box{display:flex;align-items:center;gap:8px;padding:7px 14px;background:var(--card-bg-alt);border:1px solid var(--surface-border);border-radius:var(--radius-sm);color:var(--text-muted);transition:var(--transition)}
.mg-search-box:focus-within{border-color:var(--mg-light);box-shadow:0 0 0 3px rgba(74,159,212,0.1)}
.mg-search-box input{border:none;background:transparent;color:var(--text-ink);font-size:13px;outline:none;width:150px}
.mg-search-box input::placeholder{color:var(--text-placeholder)}
.pred-sort-wrap select{padding:7px 10px;font-size:12.5px}
.pred-view-toggle{display:flex;background:var(--card-bg-alt);border:1px solid var(--surface-border);border-radius:var(--radius-sm);overflow:hidden}
.pred-view-btn{width:34px;height:34px;display:flex;align-items:center;justify-content:center;background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:14px;transition:var(--transition)}
.pred-view-btn:hover{color:var(--mg-light)}
.pred-view-btn.active{background:rgba(74,159,212,0.15);color:var(--mg-light)}

/* ── Card Grid ── */
.pred-card-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:4px}
@media(max-width:580px){.pred-card-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:380px){.pred-card-grid{grid-template-columns:1fr}}
.pred-card{background:var(--card-bg);border:1px solid var(--surface-border);border-radius:var(--radius-md);padding:16px;display:flex;flex-direction:column;gap:8px;transition:all .2s ease}
.pred-card:hover{border-color:rgba(74,159,212,0.35);transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.12)}
.pred-card-top{display:flex;align-items:center;justify-content:space-between}
.pred-card-rank{font-size:11px;font-weight:700;color:var(--text-placeholder);background:var(--card-bg-alt);border:1px solid var(--surface-border);padding:1px 7px;border-radius:20px}
.pred-badge-aktual{padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600;background:rgba(16,183,127,0.1);color:var(--accent-green);border:1px solid rgba(16,183,127,0.2)}
.pred-badge-forecast{padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600;background:rgba(74,159,212,0.1);color:var(--mg-light);border:1px solid rgba(74,159,212,0.2)}
.pred-card-icon{width:38px;height:38px;background:linear-gradient(135deg,rgba(74,159,212,0.15),rgba(0,151,184,0.1));border:1px solid rgba(74,159,212,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--accent-cyan);font-size:16px}
.pred-card-nama{font-size:12px;font-weight:600;color:var(--text-ink);line-height:1.4}
.pred-card-qty-wrap{margin-top:2px}
.pred-card-qty-label{font-size:10px;color:var(--text-placeholder);text-transform:uppercase;letter-spacing:.05em}
.pred-card-qty{font-family:var(--font-display);font-size:28px;font-weight:800;color:var(--mg-light);line-height:1.1}
.pred-card-bar-wrap{height:4px;background:var(--card-bg-alt);border-radius:999px;overflow:hidden;border:1px solid var(--surface-border)}
.pred-card-bar-fill{height:100%;background:linear-gradient(90deg,var(--mg-glow),var(--accent-cyan));border-radius:999px;width:0;transition:width 1.1s ease}
.pred-card-actual-row{display:grid;grid-template-columns:repeat(3,1fr);gap:4px;padding:8px;background:var(--card-bg-alt);border:1px solid var(--surface-border);border-radius:var(--radius-sm)}
.pred-card-actual-item{display:flex;flex-direction:column;align-items:center;gap:2px}
.pred-cai-label{font-size:9px;color:var(--text-placeholder);text-transform:uppercase;letter-spacing:.04em}
.pred-cai-val{font-size:12px;font-weight:700;color:var(--text-ink);display:flex;align-items:center;gap:1px}
.pred-card-date{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--text-placeholder)}
.pred-no-result{display:flex;flex-direction:column;align-items:center;padding:48px 24px;color:var(--text-muted)}

/* ── Table ── */
.mg-table-wrap{overflow-x:auto;border-radius:var(--radius-sm);border:1px solid var(--surface-border)}
.row-no{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;background:var(--card-bg-alt);border:1px solid var(--surface-border);border-radius:6px;font-size:11px;color:var(--text-muted);font-weight:600}
.pred-tbl-produk{display:flex;align-items:center;gap:10px}
.pred-tbl-icon{width:30px;height:30px;background:linear-gradient(135deg,rgba(74,159,212,0.15),rgba(0,151,184,0.1));border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--accent-cyan);font-size:13px;flex-shrink:0}
.month-badge-sm{padding:2px 9px;border-radius:6px;background:rgba(0,151,184,0.1);color:var(--accent-cyan);font-size:11.5px;font-weight:600}
.pred-qty-val{font-family:'Courier New',monospace;font-size:14px;font-weight:700;color:var(--mg-light)}
.pred-aktual-val{font-family:'Courier New',monospace;font-size:13px;color:var(--text-ink)}
.null-val{color:var(--text-placeholder);font-size:12px}
.mape-pill{padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
.mape-good{background:rgba(16,183,127,0.1);color:var(--accent-green);border:1px solid rgba(16,183,127,0.2)}
.mape-ok{background:rgba(212,160,23,0.1);color:var(--accent-yellow);border:1px solid rgba(212,160,23,0.2)}
.mape-bad{background:rgba(229,62,62,0.1);color:var(--accent-red);border:1px solid rgba(229,62,62,0.2)}
.status-pill{display:inline-flex;align-items:center;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
.status-success{background:rgba(16,183,127,0.1);color:var(--accent-green);border:1px solid rgba(16,183,127,0.2)}
.status-forecast{background:rgba(74,159,212,0.1);color:var(--mg-light);border:1px solid rgba(74,159,212,0.2)}

/* ── Pagination ── */
.pred-pagination{display:flex;align-items:center;justify-content:center;gap:6px;padding:18px 0 4px;flex-wrap:wrap}
.pg-btn{min-width:34px;height:34px;padding:0 10px;display:inline-flex;align-items:center;justify-content:center;border-radius:var(--radius-sm);border:1px solid var(--surface-border);background:var(--card-bg-alt);color:var(--text-muted);font-size:13px;font-weight:600;cursor:pointer;transition:var(--transition)}
.pg-btn:hover:not(:disabled){border-color:rgba(74,159,212,0.4);color:var(--mg-light);background:rgba(74,159,212,0.08)}
.pg-btn.active{background:rgba(74,159,212,0.15);border-color:rgba(74,159,212,0.5);color:var(--mg-light)}
.pg-btn:disabled{opacity:.35;cursor:not-allowed}
.pg-info{font-size:12px;color:var(--text-placeholder);padding:0 6px}

/* ── Chart ── */
.pred-chart-wrap{position:relative;padding:4px 0}
.chart-legend-item{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;color:var(--text-muted)}
.clg-dot{width:8px;height:8px;border-radius:50%;display:inline-block}
.badge-mg{padding:2px 10px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-cyan{background:rgba(0,151,184,0.12);color:var(--accent-cyan);border:1px solid rgba(0,151,184,0.2)}
</style>

<!-- ══════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const PER_PAGE = 10;

    // ── State terpusat ────────────────────────────────────────────────────────
    // Semua card dan row dibaca sekali, lalu filter/sort/paginate dijalankan
    // terhadap array JS — tidak ada display:none per item lagi.
    const allCards = Array.from(document.querySelectorAll('.pred-card'));
    const allRows  = Array.from(document.querySelectorAll('#predTableBody tr'));

    let currentView    = 'card'; // 'card' | 'table'
    let currentPage    = 1;
    let filteredCards  = [...allCards];
    let filteredRows   = [...allRows];

    // ── Sembunyikan semua card & row di awal (kontrol lewat JS) ──────────────
    allCards.forEach(c => c.style.display = 'none');
    allRows.forEach(r  => r.style.display = 'none');

    // ── Animate bar helper ────────────────────────────────────────────────────
    function animateBars(cards) {
        requestAnimationFrame(() => {
            cards.forEach(c => {
                const fill = c.querySelector('.pred-card-bar-fill');
                if (fill) setTimeout(() => fill.style.width = fill.dataset.w + '%', 80);
            });
        });
    }

    // ── Render halaman card ───────────────────────────────────────────────────
    function renderCards() {
        const start = (currentPage - 1) * PER_PAGE;
        const end   = start + PER_PAGE;
        const noRes = document.getElementById('predNoResult');
        const badge = document.getElementById('badgePredCount');

        allCards.forEach(c => c.style.display = 'none');

        if (filteredCards.length === 0) {
            if (noRes) noRes.style.display = 'flex';
            if (badge) badge.textContent = '0 produk';
            renderPagination('paginationCard', 0);
            return;
        }
        if (noRes) noRes.style.display = 'none';
        if (badge) badge.textContent = filteredCards.length + ' produk';

        const pageCards = filteredCards.slice(start, end);
        pageCards.forEach((c, i) => {
            c.style.display = '';
            // Update nomor rank sesuai urutan hasil filter+sort
            const rankEl = c.querySelector('.rank-num');
            if (rankEl) rankEl.textContent = start + i + 1;
        });
        animateBars(pageCards);
        renderPagination('paginationCard', filteredCards.length);
    }

    // ── Render halaman tabel ──────────────────────────────────────────────────
    function renderTable() {
        const start = (currentPage - 1) * PER_PAGE;
        const end   = start + PER_PAGE;

        allRows.forEach(r => r.style.display = 'none');

        const pageRows = filteredRows.slice(start, end);
        pageRows.forEach((r, i) => {
            r.style.display = '';
            // Update nomor urut
            const noEl = r.querySelector('.row-no-num');
            if (noEl) noEl.textContent = start + i + 1;
        });
        renderPagination('paginationTable', filteredRows.length);
    }

    // ── Render pagination ─────────────────────────────────────────────────────
    function renderPagination(containerId, total) {
        const el = document.getElementById(containerId);
        if (!el) return;
        const totalPages = Math.ceil(total / PER_PAGE);
        if (totalPages <= 1) { el.innerHTML = ''; return; }

        let html = '';
        // Tombol prev
        html += `<button class="pg-btn" id="${containerId}-prev" ${currentPage === 1 ? 'disabled' : ''}>
                    <i class="bi bi-chevron-left"></i>
                 </button>`;

        // Nomor halaman (tampilkan max 5 di sekitar halaman aktif)
        const range = pageRange(currentPage, totalPages);
        range.forEach(p => {
            if (p === '...') {
                html += `<span class="pg-info">…</span>`;
            } else {
                html += `<button class="pg-btn ${p === currentPage ? 'active' : ''}" data-page="${p}">${p}</button>`;
            }
        });

        // Tombol next
        html += `<button class="pg-btn" id="${containerId}-next" ${currentPage === totalPages ? 'disabled' : ''}>
                    <i class="bi bi-chevron-right"></i>
                 </button>`;

        html += `<span class="pg-info">${currentPage}/${totalPages} &middot; ${total} produk</span>`;
        el.innerHTML = html;

        // Events
        el.querySelector(`#${containerId}-prev`)?.addEventListener('click', () => goPage(currentPage - 1));
        el.querySelector(`#${containerId}-next`)?.addEventListener('click', () => goPage(currentPage + 1));
        el.querySelectorAll('.pg-btn[data-page]').forEach(btn => {
            btn.addEventListener('click', () => goPage(parseInt(btn.dataset.page)));
        });
    }

    function pageRange(current, total) {
        // Tampilkan: 1 ... (current-1) current (current+1) ... total
        const delta = 1;
        const range = [];
        const rangeWithDots = [];
        let l;
        for (let i = 1; i <= total; i++) {
            if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
                range.push(i);
            }
        }
        range.forEach(i => {
            if (l) {
                if (i - l === 2) rangeWithDots.push(l + 1);
                else if (i - l !== 1) rangeWithDots.push('...');
            }
            rangeWithDots.push(i);
            l = i;
        });
        return rangeWithDots;
    }

    function goPage(page) {
        const totalPages = Math.ceil(
            (currentView === 'card' ? filteredCards.length : filteredRows.length) / PER_PAGE
        );
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        if (currentView === 'card') renderCards();
        else renderTable();
        // Scroll ke toolbar
        document.getElementById('searchPrediksi')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // ── Filter (search) ───────────────────────────────────────────────────────
    function applyFilter(q) {
        const lq = q.toLowerCase().trim();
        filteredCards = allCards.filter(c => c.dataset.nama.includes(lq));
        filteredRows  = allRows.filter(r  => r.dataset.nama?.includes(lq));
        currentPage = 1;
        if (currentView === 'card') renderCards();
        else renderTable();
    }

    document.getElementById('searchPrediksi')?.addEventListener('input', e => applyFilter(e.target.value));

    // ── Sort ──────────────────────────────────────────────────────────────────
    function applySort(val) {
        const sortFn = (a, b) => {
            const aQty = parseFloat(a.dataset.qty), bQty = parseFloat(b.dataset.qty);
            const aNama = a.dataset.nama, bNama = b.dataset.nama;
            const aSel = parseFloat(a.dataset.selisih || 0), bSel = parseFloat(b.dataset.selisih || 0);
            switch (val) {
                case 'qty_desc':    return bQty - aQty;
                case 'qty_asc':     return aQty - bQty;
                case 'nama_asc':    return aNama.localeCompare(bNama);
                case 'nama_desc':   return bNama.localeCompare(aNama);
                case 'selisih_desc':return bSel - aSel;
                default: return 0;
            }
        };

        // Sort filtered arrays
        filteredCards.sort(sortFn);
        filteredRows.sort(sortFn);

        // Re-inject ke DOM supaya urutan DOM konsisten
        const grid = document.getElementById('predCardGrid');
        if (grid) filteredCards.forEach(c => grid.appendChild(c));

        const tbody = document.getElementById('predTableBody');
        if (tbody) filteredRows.forEach(r => tbody.appendChild(r));

        currentPage = 1;
        if (currentView === 'card') renderCards();
        else renderTable();
    }

    document.getElementById('sortPrediksi')?.addEventListener('change', function () {
        applySort(this.value);
    });

    // ── View toggle ───────────────────────────────────────────────────────────
    const btnCard  = document.getElementById('btnViewCard');
    const btnTable = document.getElementById('btnViewTable');
    const vCard    = document.getElementById('viewCard');
    const vTable   = document.getElementById('viewTable');

    btnCard?.addEventListener('click', () => {
        btnCard.classList.add('active');
        btnTable.classList.remove('active');
        vCard.style.display = '';
        vTable.style.display = 'none';
        currentView = 'card';
        currentPage = 1;
        renderCards();
    });

    btnTable?.addEventListener('click', () => {
        btnTable.classList.add('active');
        btnCard.classList.remove('active');
        vTable.style.display = '';
        vCard.style.display = 'none';
        currentView = 'table';
        currentPage = 1;
        renderTable();
    });

    // ── Render awal ───────────────────────────────────────────────────────────
    renderCards();

    // ── Chart.js ──────────────────────────────────────────────────────────────
    const ctx = document.getElementById('predChart');
    if (ctx) {
        const chartData = <?= json_encode(
            array_map(
                fn($p) => [
                    'nama'    => $p['nama_produk'],
                    'prediksi' => (float)$p['qty_prediksi'],
                    'aktual'  => $p['qty_aktual'] !== null ? (float)$p['qty_aktual'] : null,
                ],
                array_slice(
                    usort($prediksiList, fn($a, $b) => (float)$b['qty_prediksi'] <=> (float)$a['qty_prediksi']) ? $prediksiList : $prediksiList,
                    0, 15
                )
            )
        ) ?>;

        const labels   = chartData.map(d => { const p = d.nama.split(' '); return p.length > 3 ? p.slice(0,3).join(' ') + '…' : d.nama; });
        const predVals = chartData.map(d => d.prediksi);
        const aktVals  = chartData.map(d => d.aktual);
        const hasAkt   = aktVals.some(v => v !== null);

        const datasets = [{ label:'Prediksi', data:predVals, backgroundColor:'rgba(74,159,212,0.75)', borderColor:'rgba(74,159,212,1)', borderWidth:1, borderRadius:5, borderSkipped:false }];
        if (hasAkt) datasets.push({ label:'Aktual', data:aktVals, backgroundColor:'rgba(16,183,127,0.65)', borderColor:'rgba(16,183,127,1)', borderWidth:1, borderRadius:5, borderSkipped:false });

        new Chart(ctx, {
            type: 'bar',
            data: { labels, datasets },
            options: {
                responsive: true, maintainAspectRatio: true,
                interaction: { mode:'index', intersect:false },
                plugins: {
                    legend: { display:hasAkt, labels:{ color:'#8b9aad', font:{size:12}, boxWidth:12, boxHeight:12 } },
                    tooltip: { backgroundColor:'#1a2332', borderColor:'rgba(74,159,212,0.3)', borderWidth:1, titleColor:'#e2e8f0', bodyColor:'#8b9aad', padding:12,
                        callbacks: { label: c => ` ${c.dataset.label}: ${c.parsed.y !== null ? c.parsed.y.toFixed(1) : '—'} unit` }
                    }
                },
                scales: {
                    x: { ticks:{ color:'#5a7a9a', font:{size:10}, maxRotation:35 }, grid:{ color:'rgba(255,255,255,0.04)' } },
                    y: { beginAtZero:true, ticks:{ color:'#5a7a9a', font:{size:11} }, grid:{ color:'rgba(255,255,255,0.06)' } }
                }
            }
        });
    }

    // ── Sinkron Aktual ────────────────────────────────────────────────────────
    document.getElementById('btnSinkronAktual')?.addEventListener('click', function () {
        const tahun = this.dataset.tahun;
        const bulan = this.dataset.bulan;
        const bNama = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        Swal.fire({
            title: 'Sinkronisasi Qty Aktual?',
            html: `Mengambil data penjualan nyata untuk periode<br><b>${bNama[parseInt(bulan)]} ${tahun}</b> dan mengisi kolom Qty Aktual.`,
            icon: 'question', showCancelButton: true,
            confirmButtonColor:'#2e6da4', cancelButtonColor:'#243B55',
            confirmButtonText:'<i class="bi bi-arrow-repeat"></i> Ya, Sinkronkan!', cancelButtonText:'Batal',
        }).then(r => {
            if (!r.isConfirmed) return;
            const fd = new FormData();
            fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            fd.append('tahun', tahun); fd.append('bulan', bulan);
            Swal.fire({ title:'Menyinkronkan…', text:'Mohon tunggu sebentar.', allowOutsideClick:false, didOpen:() => Swal.showLoading() });
            fetch(BASE_URL + 'prediksi/sinkron-aktual', { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({ icon:'success', title:'Sinkronisasi Selesai!', text:data.message, confirmButtonColor:'#2e6da4' }).then(() => location.reload());
                    } else { Swal.fire('Gagal', data.message ?? 'Terjadi kesalahan.', 'error'); }
                })
                .catch(() => Swal.fire('Error', 'Gagal menghubungi server.', 'error'));
        });
    });
});
</script>