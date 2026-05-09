<?php
/**
 * View  : pages/dashboard/index.php
 * Dashboard utama — Mi Store Kudus
 */

// ── Helper ────────────────────────────────────────────────────────────────────
function fmtRp(int $n): string {
    if ($n >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 1) . ' M';
    if ($n >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 1) . ' Jt';
    return 'Rp ' . number_format($n);
}

// ── Siapkan data JS ───────────────────────────────────────────────────────────
$bulanLabel   = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
$distribOmset = array_fill(1, 12, 0);
$distribQty   = array_fill(1, 12, 0);
foreach ($distribusiRows ?? [] as $r) {
    $distribOmset[(int)$r['bln']] = (int)$r['omset'];
    $distribQty[(int)$r['bln']]   = (int)$r['qty'];
}

$jsData = json_encode([
    'tren'       => [
        'labels'    => array_column($trenRows ?? [], 'label'),
        'omset'     => array_column($trenRows ?? [], 'omset'),
        'qty'       => array_column($trenRows ?? [], 'qty'),
        'transaksi' => array_column($trenRows ?? [], 'transaksi'),
    ],
    'top'        => [
        'labels' => array_map(fn($p) => strlen($p['nama_produk']) > 28
            ? substr($p['nama_produk'], 0, 26) . '…' : $p['nama_produk'], $topProduk ?? []),
        'qty'    => array_column($topProduk ?? [], 'total_qty'),
        'omset'  => array_column($topProduk ?? [], 'total_omset'),
    ],
    'distribusi' => [
        'labels' => $bulanLabel,
        'omset'  => array_values($distribOmset),
        'qty'    => array_values($distribQty),
    ],
    'harian'     => [
        'labels'    => array_column($aktivitasHarian ?? [], 'label'),
        'omset'     => array_column($aktivitasHarian ?? [], 'omset'),
        'transaksi' => array_column($aktivitasHarian ?? [], 'transaksi'),
        'qty'       => array_column($aktivitasHarian ?? [], 'qty'),
    ],
    'promo'      => [
        'promo'    => $omsetPromo    ?? 0,
        'nonPromo' => $omsetNonPromo ?? 0,
    ],
    'tahunan'    => [
        'labels'    => array_column($omsetPerTahun ?? [], 'tahun'),
        'omset'     => array_column($omsetPerTahun ?? [], 'omset'),
        'transaksi' => array_column($omsetPerTahun ?? [], 'transaksi'),
    ],
], JSON_UNESCAPED_UNICODE);

$maxTopQty    = !empty($topProduk) ? max(array_column($topProduk, 'total_qty')) : 1;
$totalPredQty = array_sum(array_column($prediksiList ?? [], 'qty_prediksi'));

// ── Filter state ──────────────────────────────────────────────────────────────
$fTahun  = (int) ($filterTahun ?? 0);
$fBulan  = (int) ($filterBulan ?? 0);
$fLabel  = $filterLabel ?? 'Semua Periode';
$adaFilter = $fTahun > 0;
$tMin    = (int) ($tahunMin ?? date('Y'));
$tMax    = (int) ($tahunMax ?? date('Y'));
$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<?php
    /**
     * Variabel yang tersedia dari BaseController::renderPage():
     *   $model_aktif  — array|null dari ModelTrainingModel::getAktif()
     *
     * Variabel session yang relevan:
     *   session()->get('role')  — 'admin' | 'cs' | dsb.
     */
    $role  = session()->get('role') ?? 'cs';   // default ke role paling terbatas
    $isCs  = ($role === 'cs');                  // CS hanya lihat Dashboard + Data
    $uri   = uri_string();
?>
<div class="dash-wrap">

    <!-- ══ PAGE HEADER + FILTER BAR ═══════════════════════════════════════════ -->
    <div class="dash-top-bar">
        <div class="dash-title-group">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">
                <i class="bi bi-calendar3"></i> <?= date('d F Y') ?>
                &nbsp;·&nbsp;<span class="live-dot"></span> Live
                <?php if ($adaFilter): ?>
                    &nbsp;·&nbsp;
                    <span class="filter-active-badge">
                        <i class="bi bi-funnel-fill"></i>
                        <?= esc($fLabel) ?>
                    </span>
                <?php endif ?>
            </p>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="" class="dash-filter-form" id="formFilter">
            <div class="filter-field">
                <label class="filter-label">Tahun</label>
                <select name="tahun" class="filter-select" id="selTahun" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <?php for ($y = $tMax; $y >= $tMin; $y--): ?>
                        <option value="<?= $y ?>" <?= $fTahun === $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor ?>
                </select>
            </div>
            <div class="filter-field" id="wrapBulan" style="<?= $fTahun > 0 ? '' : 'opacity:.4;pointer-events:none' ?>">
                <label class="filter-label">Bulan</label>
                <select name="bulan" class="filter-select" id="selBulan" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <?php for ($b = 1; $b <= 12; $b++): ?>
                        <option value="<?= $b ?>" <?= $fBulan === $b ? 'selected' : '' ?>><?= $namaBulan[$b] ?></option>
                    <?php endfor ?>
                </select>
            </div>
            <?php if ($adaFilter): ?>
                <a href="<?= base_url('dashboard') ?>" class="btn-mg btn-outline-mg filter-reset" title="Reset filter">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            <?php endif ?>
            <?php if (! $isCs): ?>
            <a href="<?= base_url('prediksi/jalankan') ?>" class="btn-mg btn-generate">
                <i class="bi bi-graph-up-arrow"></i> Prediksi
            </a>
            <?php endif ?>
        </form>
    </div>

    <!-- ══ STAT CARDS ══════════════════════════════════════════════════════════ -->
    <div class="dash-stats-row">
        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(46,109,164,.15);color:var(--mg-light)">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value"><?= fmtRp((int)($summaryRow['total_omset'] ?? 0)) ?></div>
                <div class="stat-label">Total Omset<?= $adaFilter ? ' ('.$fLabel.')' : '' ?></div>
                <?php if (!$adaFilter && $growthOmset !== null): ?>
                    <div class="stat-badge <?= $growthOmset >= 0 ? 'badge-up' : 'badge-down' ?>">
                        <i class="bi bi-arrow-<?= $growthOmset >= 0 ? 'up' : 'down' ?>-short"></i>
                        <?= abs($growthOmset) ?>% vs bulan lalu
                    </div>
                <?php elseif (!$adaFilter): ?>
                    <div class="stat-sub"><?= fmtRp($omsetBulanIni ?? 0) ?> bulan ini</div>
                <?php endif ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(0,151,184,.12);color:var(--accent-cyan)">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format((int)($summaryRow['total_transaksi'] ?? 0)) ?></div>
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-sub"><?= number_format((int)($summaryRow['total_qty'] ?? 0)) ?> unit terjual</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(16,183,127,.12);color:var(--accent-green)">
                <i class="bi bi-phone-fill"></i>
            </div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($totalProdukAktif ?? 0) ?></div>
                <div class="stat-label">Produk Aktif</div>
                <div class="stat-sub"><?= number_format((int)($summaryRow['total_produk_terjual'] ?? 0)) ?> produk terjual</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrap" style="background:rgba(212,160,23,.12);color:var(--accent-yellow)">
                <i class="bi bi-cpu-fill"></i>
            </div>
            <div class="stat-body">
                <?php if ($modelAktif): ?>
                    <div class="stat-value" style="font-size:17px;letter-spacing:-.02em"><?= esc($modelAktif['versi']) ?></div>
                    <div class="stat-label">Model Aktif</div>
                    <div class="stat-sub">
                        R²: <?= $modelAktif['r2'] !== null ? number_format((float)$modelAktif['r2']*100,1).'%' : '—' ?>
                        &nbsp;·&nbsp; MAE: <?= $modelAktif['mae'] !== null ? number_format((float)$modelAktif['mae'],2) : '—' ?>
                    </div>
                <?php else: ?>
                    <div class="stat-value" style="color:var(--text-placeholder)">—</div>
                    <div class="stat-label">Belum Ada Model</div>
                    <div class="stat-sub"><a href="<?= base_url('training/proses') ?>" style="color:var(--accent-cyan)">Latih sekarang →</a></div>
                <?php endif ?>
            </div>
        </div>
    </div>

    <!-- ══ BARIS 1: Tren Utama (full-width) ════════════════════════════════════ -->
    <div class="card-mg">
        <div class="dch">
            <div class="dch-left">
                <span class="section-title" id="lblTrenJudul"><?= esc($trenJudul ?? 'Tren Penjualan') ?></span>
                <?php if ($adaFilter): ?>
                    <span class="badge-mg badge-yellow"><?= esc($fLabel) ?></span>
                <?php else: ?>
                    <span class="badge-mg badge-cyan">12 bulan terakhir</span>
                <?php endif ?>
            </div>
            <div class="dch-right">
                <div class="ctoggle-row">
                    <button class="ctoggle active" data-chart="tren" data-mode="omset">Omset</button>
                    <button class="ctoggle" data-chart="tren" data-mode="qty">Qty</button>
                    <button class="ctoggle" data-chart="tren" data-mode="transaksi">Transaksi</button>
                </div>
            </div>
        </div>
        <div class="ch-wrap" style="height:300px"><canvas id="chartTren"></canvas></div>
    </div>

    <!-- ══ BARIS 2: Top Produk + Perbandingan Antar Tahun ══════════════════════ -->
    <div class="dash-grid-6-6">

        <!-- Top produk horizontal bar -->
        <div class="card-mg">
            <div class="dch">
                <div class="dch-left">
                    <span class="section-title">Top Produk Terlaris</span>
                    <span class="badge-mg badge-green"><?= count($topProduk ?? []) ?> produk</span>
                </div>
                <div class="ctoggle-row">
                    <button class="ctoggle active" data-chart="top" data-mode="qty">Qty</button>
                    <button class="ctoggle" data-chart="top" data-mode="omset">Omset</button>
                </div>
            </div>
            <div class="ch-wrap" style="height:300px"><canvas id="chartTop"></canvas></div>
        </div>

        <!-- Perbandingan omset per tahun -->
        <div class="card-mg">
            <div class="dch">
                <div class="dch-left">
                    <span class="section-title">Perbandingan Antar Tahun</span>
                    <span class="badge-mg badge-cyan">omset & transaksi</span>
                </div>
            </div>
            <div class="ch-wrap" style="height:300px"><canvas id="chartTahunan"></canvas></div>
        </div>

    </div>

    <!-- ══ BARIS 3: Distribusi Bulanan + Aktivitas Harian ══════════════════════ -->
    <div class="dash-grid-6-6">

        <!-- Distribusi bulanan tahun ini/filter -->
        <div class="card-mg">
            <div class="dch">
                <div class="dch-left">
                    <span class="section-title">Distribusi <?= $tahunDistribusi ?? date('Y') ?></span>
                    <span class="badge-mg badge-cyan">per bulan</span>
                </div>
                <div class="ctoggle-row">
                    <button class="ctoggle active" data-chart="dist" data-mode="omset">Omset</button>
                    <button class="ctoggle" data-chart="dist" data-mode="qty">Qty</button>
                </div>
            </div>
            <div class="ch-wrap" style="height:260px"><canvas id="chartDist"></canvas></div>
        </div>

        <!-- Aktivitas 7 hari -->
        <div class="card-mg">
            <div class="dch">
                <div class="dch-left">
                    <span class="section-title">Aktivitas 7 Hari Terakhir</span>
                    <span class="badge-mg" style="background:rgba(16,183,127,.1);color:var(--accent-green);border:1px solid rgba(16,183,127,.2)">Real-time</span>
                </div>
                <div class="ctoggle-row">
                    <button class="ctoggle active" data-chart="hari" data-mode="omset">Omset</button>
                    <button class="ctoggle" data-chart="hari" data-mode="transaksi">Transaksi</button>
                </div>
            </div>
            <div class="ch-wrap" style="height:260px"><canvas id="chartHarian"></canvas></div>
        </div>

    </div>

    <!-- ══ BARIS 4: Model ML + Prediksi + Promo ════════════════════════════════ -->
    <div class="dash-grid-4-4-4">

        <!-- Model ML -->
        <div class="card-mg">
            <div class="dch">
                <span class="section-title">Status Model ML</span>
                <?php if (! $isCs): ?>
                <a href="<?= base_url('training/riwayat_model') ?>" class="dash-more">Riwayat <i class="bi bi-arrow-right"></i></a>
                <?php endif ?>
            </div>
            <?php if ($modelAktif): ?>
                <div class="ml-metric-grid">
                    <?php
                    $r2pct = $modelAktif['r2'] !== null ? (float)$modelAktif['r2'] * 100 : null;
                    $metrics = [
                        ['R²',   $r2pct !== null  ? number_format($r2pct, 1).'%'              : '—', $r2pct !== null ? ($r2pct >= 80 ? 'var(--accent-green)' : ($r2pct >= 60 ? 'var(--accent-yellow)' : 'var(--accent-red)')) : ''],
                        ['MAE',  $modelAktif['mae']  !== null ? number_format((float)$modelAktif['mae'],  4) : '—', 'var(--accent-cyan)'],
                        ['RMSE', $modelAktif['rmse'] !== null ? number_format((float)$modelAktif['rmse'], 4) : '—', ''],
                        ['MAPE', $modelAktif['mape'] !== null ? number_format((float)$modelAktif['mape'], 2).'%' : '—', 'var(--accent-yellow)'],
                    ];
                    foreach ($metrics as [$lbl, $val, $clr]): ?>
                        <div class="ml-box">
                            <div class="ml-box-val" style="<?= $clr ? 'color:'.$clr : '' ?>"><?= $val ?></div>
                            <div class="ml-box-lbl"><?= $lbl ?></div>
                            <?php if ($lbl === 'R²' && $r2pct !== null): ?>
                                <div class="ml-bar-wrap">
                                    <div class="ml-bar-fill" style="width:<?= min(100,$r2pct) ?>%;background:<?= $clr ?>"></div>
                                </div>
                            <?php endif ?>
                        </div>
                    <?php endforeach ?>
                </div>
                <div class="ml-meta-rows">
                    <?php
                    $metaRows = [
                        ['bi-tag-fill',      'Versi',   esc($modelAktif['versi'])],
                        ['bi-table',         'Record',  $modelAktif['total_record'] ? number_format($modelAktif['total_record']) : '—'],
                        ['bi-clock-history', 'Dilatih', $modelAktif['created_at'] ? date('d M Y', strtotime($modelAktif['created_at'])) : '—'],
                    ];
                    $fMulai = $modelAktif['filter_tanggal_mulai'] ?? null;
                    $fAkhir = $modelAktif['filter_tanggal_akhir'] ?? null;
                    if ($fMulai || $fAkhir) {
                        $metaRows[] = ['bi-funnel-fill', 'Filter',
                            '<span style="color:var(--accent-cyan)">'.
                            ($fMulai ? date('M Y', strtotime($fMulai)) : '…').
                            ' – '.
                            ($fAkhir ? date('M Y', strtotime($fAkhir)) : '…').
                            '</span>'];
                    }
                    foreach ($metaRows as [$ico, $lbl, $val]): ?>
                        <div class="ml-meta-row">
                            <span class="ml-meta-lbl"><i class="bi <?= $ico ?>"></i> <?= $lbl ?></span>
                            <span class="ml-meta-val"><?= $val ?></span>
                        </div>
                    <?php endforeach ?>
                </div>
                <div class="ml-footer">
                    <div class="ml-foot-item">
                        <span class="ml-foot-num"><?= number_format((int)($ringkasanModel['total_sukses'] ?? 0)) ?></span>
                        <span class="ml-foot-lbl">Sukses</span>
                    </div>
                    <div class="ml-foot-sep"></div>
                    <div class="ml-foot-item">
                        <span class="ml-foot-num"><?= number_format((int)($ringkasanModel['total_model'] ?? 0)) ?></span>
                        <span class="ml-foot-lbl">Dilatih</span>
                    </div>
                    <div class="ml-foot-sep"></div>
                    <div class="ml-foot-item">
                        <span class="ml-foot-num" style="color:var(--accent-green)">
                            <?= $ringkasanModel['best_r2'] !== null ? number_format((float)$ringkasanModel['best_r2']*100,1).'%' : '—' ?>
                        </span>
                        <span class="ml-foot-lbl">Best R²</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="dash-empty">
                    <i class="bi bi-cpu" style="font-size:36px;display:block;margin-bottom:10px;opacity:.25"></i>
                    Belum ada model aktif.
                    <a href="<?= base_url('training/proses') ?>" class="btn-mg btn-outline-mg" style="margin-top:12px">Latih Model</a>
                </div>
            <?php endif ?>
        </div>

        <!-- Prediksi terbaru -->
        <div class="card-mg">
            <div class="dch">
                <div class="dch-left">
                    <span class="section-title">Prediksi Terbaru</span>
                    <?php if ($periodeTerbaru): ?>
                        <span class="badge-mg badge-yellow">
                            <?= date('M Y', mktime(0,0,0,(int)$periodeTerbaru['bulan'],1,(int)$periodeTerbaru['tahun'])) ?>
                        </span>
                    <?php endif ?>
                </div>
                <?php if (! $isCs): ?>
                <a href="<?= base_url('prediksi') ?>" class="dash-more">Lihat <i class="bi bi-arrow-right"></i></a>
                <?php endif ?>
            </div>
            <?php if (!empty($prediksiList)):
                $maxPredQ = max(array_column($prediksiList, 'qty_prediksi') ?: [1]);
            ?>
                <div class="pred-list">
                    <?php foreach ($prediksiList as $pr):
                        $pct     = $maxPredQ > 0 ? round(($pr['qty_prediksi'] / $maxPredQ) * 100) : 0;
                        $nama    = strlen($pr['nama_produk']) > 30 ? substr($pr['nama_produk'], 0, 28).'…' : $pr['nama_produk'];
                        $selisih = isset($pr['qty_aktual']) && $pr['qty_aktual'] !== null
                            ? (int)$pr['qty_aktual'] - (int)$pr['qty_prediksi'] : null;
                    ?>
                        <div class="pred-item">
                            <div class="pred-info">
                                <div class="pred-nama" title="<?= esc($pr['nama_produk']) ?>"><?= esc($nama) ?></div>
                                <div class="pred-bar-wrap">
                                    <div class="pred-bar-fill" style="width:<?= $pct ?>%"></div>
                                </div>
                            </div>
                            <div class="pred-nums">
                                <span class="pred-qty"><?= number_format($pr['qty_prediksi']) ?></span>
                                <?php if ($selisih !== null): ?>
                                    <span class="pred-selisih <?= $selisih >= 0 ? 'pos' : 'neg' ?>">
                                        <?= ($selisih >= 0 ? '+' : '') . number_format($selisih) ?>
                                    </span>
                                <?php endif ?>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
                <div class="pred-footer">
                    Estimasi total: <strong><?= number_format($totalPredQty) ?> unit</strong>
                </div>
            <?php elseif ($modelAktif): ?>
                <div class="dash-empty">
                    <i class="bi bi-graph-up" style="font-size:32px;display:block;margin-bottom:8px;opacity:.25"></i>
                    Belum ada prediksi.
                    <a href="<?= base_url('prediksi/jalankan') ?>" class="btn-mg btn-outline-mg" style="margin-top:10px">Jalankan</a>
                </div>
            <?php else: ?>
                <div class="dash-empty">Latih model terlebih dahulu.</div>
            <?php endif ?>
        </div>

        <!-- Promo donut + ringkasan -->
        <div class="card-mg">
            <div class="dch">
                <span class="section-title">Promo vs Normal</span>
            </div>
            <div class="ch-wrap" style="height:200px"><canvas id="chartPromo"></canvas></div>
            <?php
            $totalAll  = ($omsetPromo ?? 0) + ($omsetNonPromo ?? 0);
            $promoPct  = $totalAll > 0 ? round(($omsetPromo / $totalAll) * 100, 1) : 0;
            $nPct      = 100 - $promoPct;
            ?>
            <div class="promo-legend">
                <div class="promo-leg-item">
                    <span class="promo-dot" style="background:var(--accent-cyan)"></span>
                    <span>Promo</span> <strong><?= $promoPct ?>%</strong>
                </div>
                <div class="promo-leg-item">
                    <span class="promo-dot" style="background:var(--accent-yellow)"></span>
                    <span>Normal</span> <strong><?= $nPct ?>%</strong>
                </div>
            </div>
            <div class="promo-breakdown">
                <div class="pb-row">
                    <span><i class="bi bi-tag-fill" style="color:var(--accent-cyan)"></i> Promo</span>
                    <span><?= fmtRp((int)($omsetPromo ?? 0)) ?></span>
                </div>
                <div class="pb-row">
                    <span><i class="bi bi-tag" style="color:var(--accent-yellow)"></i> Normal</span>
                    <span><?= fmtRp((int)($omsetNonPromo ?? 0)) ?></span>
                </div>
                <div class="pb-row pb-total">
                    <span>Total</span>
                    <strong><?= fmtRp($totalAll) ?></strong>
                </div>
            </div>
        </div>

    </div>

</div><!-- /.dash-wrap -->


<!-- ══ STYLES ══════════════════════════════════════════════════════════════════ -->
<style>
.dash-wrap { display:flex; flex-direction:column; gap:20px; }

/* ── Live dot ── */
.live-dot {
    display:inline-block; width:7px; height:7px; border-radius:50%;
    background:var(--accent-green);
    box-shadow:0 0 0 3px rgba(16,183,127,.18);
    animation:live-pulse 2s infinite;
}
@keyframes live-pulse {
    0%,100%{box-shadow:0 0 0 3px rgba(16,183,127,.18)}
    50%    {box-shadow:0 0 0 6px rgba(16,183,127,.06)}
}

/* ── Top bar ── */
.dash-top-bar {
    display:flex; align-items:flex-start; justify-content:space-between;
    flex-wrap:wrap; gap:14px;
}
.dash-title-group { flex-shrink:0; }
.page-subtitle {
    display:flex; align-items:center; gap:6px;
    font-size:12px; color:var(--text-muted); margin-top:3px; flex-wrap:wrap;
}
.filter-active-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;
    background:rgba(212,160,23,.12); color:var(--accent-yellow);
    border:1px solid rgba(212,160,23,.25);
}

/* ── Filter form ── */
.dash-filter-form {
    display:flex; align-items:flex-end; gap:10px; flex-wrap:wrap;
}
.filter-field { display:flex; flex-direction:column; gap:4px; }
.filter-label {
    font-size:10.5px; text-transform:uppercase; letter-spacing:.05em;
    color:var(--text-muted); font-weight:600;
}
.filter-select {
    padding:7px 32px 7px 12px;
    background:var(--card-bg-alt);
    border:1px solid var(--surface-border);
    border-radius:var(--radius-sm);
    color:var(--text-ink);
    font-size:13px; font-weight:600;
    cursor:pointer;
    appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%238e9bb5' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 10px center;
    transition:var(--transition);
    min-width:100px;
}
.filter-select:focus { outline:none; border-color:rgba(74,159,212,.45); box-shadow:0 0 0 3px rgba(74,159,212,.1); }
.filter-reset {
    color:var(--accent-red) !important;
    border-color:rgba(229,62,62,.3) !important;
    font-size:12.5px;
}
.btn-generate {
    background:linear-gradient(135deg,rgba(74,159,212,.12),rgba(0,151,184,.1));
    color:var(--accent-cyan); border:1px solid rgba(0,151,184,.3);
}
.btn-generate:hover {
    background:linear-gradient(135deg,rgba(74,159,212,.22),rgba(0,151,184,.18));
}

/* ── Stat cards ── */
.dash-stats-row {
    display:grid; grid-template-columns:repeat(4,1fr); gap:16px;
}
.stat-card {
    display:flex; align-items:flex-start; gap:14px;
    padding:18px 20px; background:var(--card-bg);
    border:1px solid var(--surface-border); border-radius:var(--radius-md);
    transition:var(--transition);
}
.stat-card:hover { background:var(--card-bg-alt); transform:translateY(-2px); }
.stat-icon-wrap {
    width:44px; height:44px; border-radius:12px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:20px;
}
.stat-body { flex:1; min-width:0; }
.stat-value {
    font-size:22px; font-weight:700; color:var(--text-ink);
    line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.stat-label { font-size:11.5px; color:var(--text-muted); margin-top:3px; }
.stat-sub   { font-size:11px;   color:var(--text-placeholder); margin-top:2px; }
.stat-badge {
    display:inline-flex; align-items:center; gap:3px;
    margin-top:5px; padding:2px 8px; border-radius:20px;
    font-size:10.5px; font-weight:600;
}
.badge-up   { background:rgba(16,183,127,.12); color:var(--accent-green); border:1px solid rgba(16,183,127,.2); }
.badge-down { background:rgba(229,62,62,.10);  color:var(--accent-red);   border:1px solid rgba(229,62,62,.2); }

/* ── Grid layouts ── */
.dash-grid-6-6   { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.dash-grid-4-4-4 { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }

/* ── Card header shorthand ── */
.dch {
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:10px; margin-bottom:14px;
}
.dch-left, .dch-right { display:flex; align-items:center; gap:8px; }
.dash-more {
    font-size:11.5px; color:var(--accent-cyan); text-decoration:none;
    font-weight:600; display:flex; align-items:center; gap:4px;
    transition:var(--transition);
}
.dash-more:hover { color:var(--mg-light); }

/* ── Chart toggle buttons ── */
.ctoggle-row { display:flex; gap:4px; }
.ctoggle {
    padding:4px 11px; border-radius:20px; font-size:11.5px; font-weight:600;
    cursor:pointer; border:1px solid var(--surface-border);
    background:transparent; color:var(--text-muted); transition:var(--transition);
}
.ctoggle.active {
    background:rgba(74,159,212,.14); color:var(--mg-light);
    border-color:rgba(74,159,212,.35);
}

/* ── Chart wrapper ── */
.ch-wrap { position:relative; width:100%; }

/* ── Model ML box ── */
.ml-metric-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px; }
.ml-box {
    background:var(--card-bg-alt); border:1px solid var(--surface-border);
    border-radius:var(--radius-sm); padding:10px 12px;
}
.ml-box-val { font-size:17px; font-weight:700; color:var(--text-ink); font-family:'Courier New',monospace; }
.ml-box-lbl { font-size:10px; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); margin-top:2px; }
.ml-bar-wrap { height:3px; background:var(--surface-border); border-radius:999px; overflow:hidden; margin-top:5px; }
.ml-bar-fill { height:100%; border-radius:999px; transition:width .8s ease; }

.ml-meta-rows { display:flex; flex-direction:column; gap:0; margin-bottom:12px; }
.ml-meta-row {
    display:flex; align-items:center; justify-content:space-between;
    font-size:12px; padding:6px 0; border-bottom:1px solid var(--surface-border);
}
.ml-meta-row:last-child { border-bottom:none; }
.ml-meta-lbl { color:var(--text-muted); display:flex; align-items:center; gap:5px; }
.ml-meta-lbl i { font-size:11px; color:var(--text-placeholder); }
.ml-meta-val { font-weight:600; color:var(--text-ink); font-family:'Courier New',monospace; font-size:11.5px; text-align:right; }

.ml-footer {
    display:flex; align-items:center; gap:0;
    padding-top:10px; border-top:1px solid var(--surface-border);
}
.ml-foot-item { display:flex; flex-direction:column; align-items:center; flex:1; }
.ml-foot-num  { font-size:18px; font-weight:700; color:var(--text-ink); }
.ml-foot-lbl  { font-size:10.5px; color:var(--text-muted); }
.ml-foot-sep  { width:1px; height:34px; background:var(--surface-border); flex-shrink:0; }

/* ── Prediksi list ── */
.pred-list { display:flex; flex-direction:column; gap:8px; }
.pred-item { display:flex; align-items:center; gap:10px; }
.pred-info { flex:1; min-width:0; }
.pred-nama {
    font-size:11.5px; color:var(--text-ink);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-bottom:3px;
}
.pred-bar-wrap { height:3px; background:var(--surface-border); border-radius:999px; overflow:hidden; }
.pred-bar-fill { height:100%; background:linear-gradient(90deg,var(--accent-yellow),rgba(212,160,23,.35)); border-radius:999px; }
.pred-nums { display:flex; flex-direction:column; align-items:flex-end; min-width:54px; flex-shrink:0; }
.pred-qty  { font-size:12px; font-weight:700; color:var(--text-ink); font-family:'Courier New',monospace; }
.pred-selisih { font-size:10.5px; font-weight:600; font-family:'Courier New',monospace; }
.pred-selisih.pos { color:var(--accent-green); }
.pred-selisih.neg { color:var(--accent-red); }
.pred-footer { margin-top:10px; padding-top:8px; border-top:1px solid var(--surface-border); font-size:11.5px; color:var(--text-muted); text-align:right; }
.pred-footer strong { color:var(--accent-cyan); }

/* ── Promo ── */
.promo-legend { display:flex; gap:16px; justify-content:center; margin-top:10px; }
.promo-leg-item { display:flex; align-items:center; gap:6px; font-size:12px; color:var(--text-muted); }
.promo-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
.promo-leg-item strong { color:var(--text-ink); font-size:13px; }

.promo-breakdown { display:flex; flex-direction:column; gap:0; margin-top:12px; }
.pb-row {
    display:flex; align-items:center; justify-content:space-between;
    font-size:12px; padding:6px 0; color:var(--text-muted);
    border-bottom:1px solid var(--surface-border);
}
.pb-row:last-child { border-bottom:none; }
.pb-row span:first-child { display:flex; align-items:center; gap:5px; }
.pb-total { font-weight:700; color:var(--text-ink); padding-top:8px; }
.pb-total strong { color:var(--accent-cyan); }

/* ── Empty state ── */
.dash-empty {
    text-align:center; padding:30px 16px;
    color:var(--text-muted); font-size:12.5px;
    display:flex; flex-direction:column; align-items:center;
}

/* ── Responsive ── */
@media(max-width:1100px) {
    .dash-grid-4-4-4 { grid-template-columns:1fr 1fr; }
    .dash-grid-4-4-4 > :last-child { grid-column:1/-1; }
}
@media(max-width:900px) {
    .dash-stats-row  { grid-template-columns:1fr 1fr; }
    .dash-grid-6-6   { grid-template-columns:1fr; }
    .dash-grid-4-4-4 { grid-template-columns:1fr; }
}
@media(max-width:540px) {
    .dash-stats-row { grid-template-columns:1fr; }
    .dash-filter-form { flex-direction:column; align-items:stretch; }
}
</style>


<!-- ══ CHART.JS ══════════════════════════════════════════════════════════════ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    const D  = <?= $jsData ?>;
    const cs = getComputedStyle(document.documentElement);
    const cv = k => cs.getPropertyValue(k).trim();

    const cCyan   = cv('--accent-cyan')    || '#00b7d4';
    const cGreen  = cv('--accent-green')   || '#10b77f';
    const cYellow = cv('--accent-yellow')  || '#d4a017';
    const cRed    = cv('--accent-red')     || '#e53e3e';
    const cBorder = cv('--surface-border') || 'rgba(255,255,255,.08)';
    const cMuted  = cv('--text-muted')     || '#8e9bb5';
    const cInk    = cv('--text-ink')       || '#eaeef5';
    const cBg     = cv('--card-bg')        || '#141e30';

    Chart.defaults.color       = cMuted;
    Chart.defaults.borderColor = cBorder;
    Chart.defaults.font.family = "'Segoe UI',sans-serif";
    Chart.defaults.font.size   = 11;

    const grid  = { color: cBorder, drawTicks: false };
    const ticks = { color: cMuted, padding: 8 };

    // Format rupiah singkat
    function fmtJt(v) {
        v = Number(v);
        if (v >= 1e9) return (v/1e9).toFixed(1)+'M';
        if (v >= 1e6) return (v/1e6).toFixed(1)+'Jt';
        return Math.round(v).toLocaleString('id-ID');
    }

    // Tooltip style standar
    const tooltipBase = {
        backgroundColor: cBg,
        borderColor: cBorder, borderWidth: 1,
        titleColor: cInk, bodyColor: cMuted,
        padding: 10, cornerRadius: 8,
    };

    // Helper: buat gradient vertikal
    function grad(ctx, h, topClr, opacity=0.5) {
        const g = ctx.createLinearGradient(0, 0, 0, h);
        g.addColorStop(0, topClr.replace(')',`,${opacity})`).replace('rgb','rgba'));
        g.addColorStop(1, topClr.replace(')',',0)').replace('rgb','rgba'));
        return g;
    }
    // Untuk hex color
    function hexGrad(ctx, h, hex, op=0.5) {
        const g = ctx.createLinearGradient(0, 0, 0, h);
        g.addColorStop(0, hex + Math.round(op*255).toString(16).padStart(2,'0'));
        g.addColorStop(1, hex + '00');
        return g;
    }

    // ── 1. Tren Utama (line) ────────────────────────────────────────────────
    const ctxT = document.getElementById('chartTren').getContext('2d');
    const trenGrad = ctxT.createLinearGradient(0,0,0,300);
    trenGrad.addColorStop(0, 'rgba(74,159,212,0.35)');
    trenGrad.addColorStop(1, 'rgba(74,159,212,0.00)');

    let trenMode = 'omset';
    const chartTren = new Chart(ctxT, {
        type: 'line',
        data: {
            labels: D.tren.labels,
            datasets: [{
                label: 'Omset',
                data: D.tren.omset.map(Number),
                borderColor: cCyan, backgroundColor: trenGrad,
                borderWidth: 2.5, fill: true, tension: 0.4,
                pointRadius: 3, pointHoverRadius: 6,
                pointBackgroundColor: cCyan,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipBase, callbacks: {
                    label: ctx => ' ' + (trenMode === 'omset' ? 'Rp '+fmtJt(ctx.parsed.y)
                        : trenMode === 'qty' ? ctx.parsed.y+' unit'
                        : ctx.parsed.y+' transaksi')
                }}
            },
            scales: {
                x: { grid, ticks },
                y: { grid, ticks: { ...ticks, callback: v => trenMode === 'omset' ? fmtJt(v) : v }, beginAtZero: true }
            }
        }
    });

    document.querySelectorAll('.ctoggle[data-chart="tren"]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.ctoggle[data-chart="tren"]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            trenMode = this.dataset.mode;
            const map = { omset: D.tren.omset, qty: D.tren.qty, transaksi: D.tren.transaksi };
            chartTren.data.datasets[0].data  = map[trenMode].map(Number);
            chartTren.data.datasets[0].label = this.textContent;
            chartTren.options.scales.y.ticks.callback = trenMode === 'omset' ? v => fmtJt(v) : v => v;
            chartTren.update();
        });
    });

    // ── 2. Top Produk (horizontal bar) ─────────────────────────────────────
    let topMode = 'qty';
    const ctxTop = document.getElementById('chartTop').getContext('2d');
    const chartTop = new Chart(ctxTop, {
        type: 'bar',
        data: {
            labels: D.top.labels,
            datasets: [{
                label: 'Qty',
                data: D.top.qty.map(Number),
                backgroundColor: 'rgba(16,183,127,0.55)',
                borderColor: cGreen, borderWidth: 1.5,
                borderRadius: 5, borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipBase, callbacks: {
                    label: ctx => ' ' + (topMode === 'qty' ? ctx.parsed.x+' unit' : 'Rp '+fmtJt(ctx.parsed.x))
                }}
            },
            scales: {
                x: { grid, ticks: { ...ticks, callback: v => topMode === 'qty' ? v : fmtJt(v) }, beginAtZero: true },
                y: { grid: { display: false }, ticks: { ...ticks, font: { size: 10.5 } } }
            }
        }
    });

    document.querySelectorAll('.ctoggle[data-chart="top"]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.ctoggle[data-chart="top"]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            topMode = this.dataset.mode;
            chartTop.data.datasets[0].data = (topMode === 'qty' ? D.top.qty : D.top.omset).map(Number);
            chartTop.data.datasets[0].label = this.textContent;
            chartTop.data.datasets[0].backgroundColor = topMode === 'qty' ? 'rgba(16,183,127,0.55)' : 'rgba(74,159,212,0.55)';
            chartTop.data.datasets[0].borderColor = topMode === 'qty' ? cGreen : cCyan;
            chartTop.options.scales.x.ticks.callback = topMode === 'qty' ? v => v : v => fmtJt(v);
            chartTop.update();
        });
    });

    // ── 3. Perbandingan Antar Tahun (grouped bar) ───────────────────────────
    const ctxTH = document.getElementById('chartTahunan').getContext('2d');
    new Chart(ctxTH, {
        type: 'bar',
        data: {
            labels: D.tahunan.labels,
            datasets: [
                {
                    label: 'Omset',
                    data: D.tahunan.omset.map(Number),
                    backgroundColor: 'rgba(74,159,212,0.55)',
                    borderColor: cCyan, borderWidth: 1.5,
                    borderRadius: 5, yAxisID: 'y',
                },
                {
                    label: 'Transaksi',
                    data: D.tahunan.transaksi.map(Number),
                    backgroundColor: 'rgba(212,160,23,0.55)',
                    borderColor: cYellow, borderWidth: 1.5,
                    borderRadius: 5, type: 'line',
                    yAxisID: 'y2', tension: 0.4,
                    pointRadius: 4, pointBackgroundColor: cYellow,
                    fill: false,
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true, position: 'top',
                    labels: { color: cMuted, boxWidth: 12, font: { size: 11 } }
                },
                tooltip: { ...tooltipBase, callbacks: {
                    label: ctx => ctx.dataset.label === 'Omset'
                        ? ' Rp '+fmtJt(ctx.parsed.y)
                        : ' '+ctx.parsed.y+' transaksi'
                }}
            },
            scales: {
                x:  { grid, ticks },
                y:  { grid, ticks: { ...ticks, callback: v => fmtJt(v) }, beginAtZero: true, position: 'left' },
                y2: { grid: { display: false }, ticks, beginAtZero: true, position: 'right' }
            }
        }
    });

    // ── 4. Distribusi Bulanan (bar) ─────────────────────────────────────────
    let distMode = 'omset';
    const ctxD = document.getElementById('chartDist').getContext('2d');
    const distGrad = ctxD.createLinearGradient(0,0,0,260);
    distGrad.addColorStop(0,'rgba(74,159,212,0.6)');
    distGrad.addColorStop(1,'rgba(74,159,212,0.1)');

    const chartDist = new Chart(ctxD, {
        type: 'bar',
        data: {
            labels: D.distribusi.labels,
            datasets: [{
                label: 'Omset',
                data: D.distribusi.omset.map(Number),
                backgroundColor: distGrad,
                borderColor: cCyan, borderWidth: 1,
                borderRadius: 4, borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipBase, callbacks: {
                    label: ctx => ' ' + (distMode === 'omset' ? 'Rp '+fmtJt(ctx.parsed.y) : ctx.parsed.y+' unit')
                }}
            },
            scales: {
                x: { grid: { display: false }, ticks: { ...ticks, font: { size: 10 } } },
                y: { grid, ticks: { ...ticks, callback: v => distMode === 'omset' ? fmtJt(v) : v }, beginAtZero: true }
            }
        }
    });

    document.querySelectorAll('.ctoggle[data-chart="dist"]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.ctoggle[data-chart="dist"]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            distMode = this.dataset.mode;
            chartDist.data.datasets[0].data = (distMode === 'omset' ? D.distribusi.omset : D.distribusi.qty).map(Number);
            chartDist.data.datasets[0].label = this.textContent;
            chartDist.options.scales.y.ticks.callback = distMode === 'omset' ? v => fmtJt(v) : v => v;
            chartDist.update();
        });
    });

    // ── 5. Aktivitas Harian (bar) ───────────────────────────────────────────
    let hariMode = 'omset';
    const ctxH = document.getElementById('chartHarian').getContext('2d');
    const hariGrad = ctxH.createLinearGradient(0,0,0,260);
    hariGrad.addColorStop(0,'rgba(16,183,127,0.55)');
    hariGrad.addColorStop(1,'rgba(16,183,127,0.1)');

    const chartHarian = new Chart(ctxH, {
        type: 'bar',
        data: {
            labels: D.harian.labels,
            datasets: [{
                label: 'Omset',
                data: D.harian.omset.map(Number),
                backgroundColor: hariGrad,
                borderColor: cGreen, borderWidth: 1.5,
                borderRadius: 5, borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipBase, callbacks: {
                    label: ctx => ' '+(hariMode === 'omset' ? 'Rp '+fmtJt(ctx.parsed.y) : ctx.parsed.y+' transaksi')
                }}
            },
            scales: {
                x: { grid: { display: false }, ticks },
                y: { grid, ticks: { ...ticks, callback: v => hariMode === 'omset' ? fmtJt(v) : v }, beginAtZero: true }
            }
        }
    });

    document.querySelectorAll('.ctoggle[data-chart="hari"]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.ctoggle[data-chart="hari"]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            hariMode = this.dataset.mode;
            const src = hariMode === 'omset' ? D.harian.omset : D.harian.transaksi;
            chartHarian.data.datasets[0].data  = src.map(Number);
            chartHarian.data.datasets[0].label = this.textContent;
            const isGreen = hariMode === 'omset';
            const g = ctxH.createLinearGradient(0,0,0,260);
            g.addColorStop(0, isGreen ? 'rgba(16,183,127,0.55)' : 'rgba(212,160,23,0.55)');
            g.addColorStop(1, isGreen ? 'rgba(16,183,127,0.10)' : 'rgba(212,160,23,0.10)');
            chartHarian.data.datasets[0].backgroundColor = g;
            chartHarian.data.datasets[0].borderColor = isGreen ? cGreen : cYellow;
            chartHarian.options.scales.y.ticks.callback = hariMode === 'omset' ? v => fmtJt(v) : v => v;
            chartHarian.update();
        });
    });

    // ── 6. Promo Donut ──────────────────────────────────────────────────────
    const ctxP = document.getElementById('chartPromo').getContext('2d');
    new Chart(ctxP, {
        type: 'doughnut',
        data: {
            labels: ['Promo','Normal'],
            datasets: [{
                data: [D.promo.promo, D.promo.nonPromo].map(Number),
                backgroundColor: [cCyan+'bb', cYellow+'bb'],
                borderColor: [cCyan, cYellow],
                borderWidth: 1.5, hoverOffset: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: { ...tooltipBase, callbacks: {
                    label: ctx => ' Rp '+fmtJt(ctx.parsed)
                }}
            }
        }
    });

    // ── Enable bulan select hanya jika tahun dipilih ────────────────────────
    document.getElementById('selTahun')?.addEventListener('change', function() {
        const wrap = document.getElementById('wrapBulan');
        if (wrap) {
            wrap.style.opacity = this.value ? '1' : '0.4';
            wrap.style.pointerEvents = this.value ? 'auto' : 'none';
            if (!this.value) document.getElementById('selBulan').value = '';
        }
    });

})();
</script>