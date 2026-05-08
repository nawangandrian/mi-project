<?php

/**
 * View  : pages/prediksi/akurasi.php
 * Modul : Evaluasi Model Random Forest — Mi Store Kudus
 */
?>

<div class="content-wrapper-inner">

    <!-- ── Page Header ── -->
    <div class="mg-page-header">
        <div>
            <h1 class="page-title">Evaluasi Model</h1>
            <p class="page-subtitle">Metrik performa model Random Forest yang sedang aktif</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('training/proses') ?>" class="btn-mg btn-outline-mg">
                <i class="bi bi-arrow-repeat"></i> Latih Ulang
            </a>
            <a href="<?= base_url('prediksi/jalankan') ?>" class="btn-mg btn-primary-mg">
                <i class="bi bi-play-circle-fill"></i> Jalankan Prediksi
            </a>
        </div>
    </div>

    <!-- ── Quick Nav Pills ── -->
    <div class="quick-nav-row">
        <a href="<?= base_url('prediksi') ?>"
            class="quick-nav-pill">
            <i class="bi bi-graph-up-arrow"></i>
            <span>Prediksi Penjualan</span>
        </a>
        <a href="<?= base_url('prediksi/jalankan') ?>"
            class="quick-nav-pill">
            <i class="bi bi-play-circle-fill"></i>
            <span>Jalankan Model</span>
        </a>
        <a href="<?= base_url('prediksi/riwayat') ?>"
            class="quick-nav-pill">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat Prediksi</span>
        </a>
        <a href="<?= base_url('prediksi/akurasi') ?>"
            class="quick-nav-pill active">
            <i class="bi bi-bullseye"></i>
            <span>Evaluasi Model</span>
        </a>
    </div>

    <?php if (empty($modelAktif)): ?>
        <!-- ── Empty State ── -->
        <div class="eval-empty-state">
            <div class="eval-empty-icon">
                <i class="bi bi-cpu"></i>
            </div>
            <div class="eval-empty-title">Belum Ada Model Aktif</div>
            <div class="eval-empty-sub">
                Latih model Random Forest terlebih dahulu agar data evaluasi tersedia.
            </div>
            <a href="<?= base_url('training/proses') ?>" class="btn-mg btn-primary-mg" style="margin-top:20px;text-decoration:none">
                <i class="bi bi-play-circle-fill"></i> Mulai Training
            </a>
        </div>

    <?php else:
        $m = $modelAktif;
        $mae        = isset($m['mae'])     ? (float)$m['mae']     : null;
        $rmse       = isset($m['rmse'])    ? (float)$m['rmse']    : null;
        $r2         = isset($m['r2'])      ? (float)$m['r2']      : null;
        $mape       = isset($m['mape'])    ? (float)$m['mape']    : null;
        $akurasi    = isset($m['akurasi']) ? (float)$m['akurasi'] : null;

        $akurasiTest  = isset($m['akurasi_test'])  ? (float)$m['akurasi_test']  : $akurasi;
        $maeTest      = isset($m['mae_test'])       ? (float)$m['mae_test']       : $mae;
        $rmseTest     = isset($m['rmse_test'])      ? (float)$m['rmse_test']      : $rmse;
        $r2Test       = isset($m['r2_test'])        ? (float)$m['r2_test']        : $r2;
        $mapeTest     = isset($m['mape_test'])      ? (float)$m['mape_test']      : $mape;

        $akurasiTrain = isset($m['akurasi'])        ? (float)$m['akurasi']        : null;
        $r2Pct        = $r2Test !== null ? $r2Test * 100 : null;
        $r2Color      = $r2Pct !== null ? ($r2Pct >= 80 ? 'var(--accent-green)' : ($r2Pct >= 60 ? 'var(--accent-yellow)' : 'var(--accent-red)')) : 'var(--text-muted)';
        $akurasiColor = $akurasiTest !== null ? ($akurasiTest >= 80 ? 'var(--accent-green)' : ($akurasiTest >= 60 ? 'var(--accent-yellow)' : 'var(--accent-red)')) : 'var(--text-muted)';

        // Helper decode JSON aman: handle string, array, maupun null
        $jsonSafe = static function (mixed $v): array {
            if (is_array($v))  return $v;
            if (!is_string($v) || $v === '') return [];
            $d = json_decode($v, true);
            return is_array($d) ? $d : [];
        };

        $bestParams        = $jsonSafe($m['best_params']        ?? '');
        $featureImportance = $jsonSafe($m['feature_importance'] ?? '');

        $diagramPaths = $jsonSafe($m['diagram_paths'] ?? '');
    ?>

        <!-- ── Model Aktif Banner ── -->
        <div class="eval-model-banner">
            <div class="eval-banner-left">
                <div class="eval-banner-dot"></div>
                <div class="eval-banner-icon"><i class="bi bi-cpu-fill"></i></div>
                <div>
                    <div class="eval-banner-versi"><?= esc($m['versi'] ?? 'v—') ?></div>
                    <div class="eval-banner-meta">
                        Model Aktif
                        <?php if (!empty($m['selesai_at'])): ?>
                            &nbsp;·&nbsp; Dilatih <?= date('d M Y, H:i', strtotime($m['selesai_at'])) ?>
                        <?php endif; ?>
                        <?php if (!empty($m['total_record'])): ?>
                            &nbsp;·&nbsp; <?= number_format($m['total_record']) ?> record
                        <?php endif; ?>
                        <?php if (!empty($m['total_produk'])): ?>
                            &nbsp;·&nbsp; <?= number_format($m['total_produk']) ?> produk
                        <?php endif; ?>
                        <?php if (!empty($m['durasi_detik'])): ?>
                            &nbsp;·&nbsp; <?= number_format($m['durasi_detik']) ?>s training
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <a href="<?= base_url('training/riwayat_model') ?>" class="btn-mg btn-outline-mg btn-sm-eval">
                <i class="bi bi-clock-history"></i> Riwayat Model
            </a>
        </div>

        <!-- ── Metrik Utama — Stats Grid ── -->
        <div class="eval-stats-grid">

            <!-- Akurasi (Test) -->
            <div class="eval-stat-card highlight-card">
                <div class="eval-stat-header">
                    <div class="eval-stat-icon" style="background:rgba(16,183,127,0.12);color:var(--accent-green)">
                        <i class="bi bi-bullseye"></i>
                    </div>
                    <span class="eval-stat-label">Akurasi Test</span>
                </div>
                <div class="eval-stat-value" style="color:<?= $akurasiColor ?>">
                    <?= $akurasiTest !== null ? number_format($akurasiTest, 2) . '%' : '—' ?>
                </div>
                <?php if ($akurasiTrain !== null): ?>
                    <div class="eval-stat-compare">
                        <span class="eval-stat-compare-label">Train:</span>
                        <span class="eval-stat-compare-val"><?= number_format($akurasiTrain, 2) ?>%</span>
                    </div>
                <?php endif; ?>
                <!-- Gauge bar -->
                <div class="eval-gauge-wrap">
                    <div class="eval-gauge-bar">
                        <div class="eval-gauge-fill"
                            style="width:<?= $akurasiTest !== null ? min(100, $akurasiTest) : 0 ?>%;background:<?= $akurasiColor ?>">
                        </div>
                    </div>
                    <span class="eval-gauge-label"><?= $akurasiTest !== null ? ($akurasiTest >= 80 ? 'Baik' : ($akurasiTest >= 60 ? 'Cukup' : 'Kurang')) : '—' ?></span>
                </div>
            </div>

            <!-- R² Score -->
            <div class="eval-stat-card">
                <div class="eval-stat-header">
                    <div class="eval-stat-icon" style="background:rgba(74,159,212,0.12);color:var(--mg-light)">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <span class="eval-stat-label">R² Score</span>
                </div>
                <div class="eval-stat-value" style="color:<?= $r2Color ?>">
                    <?= $r2Test !== null ? number_format($r2Test, 4) : '—' ?>
                </div>
                <div class="eval-stat-sub">
                    <?= $r2Pct !== null ? number_format($r2Pct, 1) . '% variansi dijelaskan' : 'Belum tersedia' ?>
                </div>
                <div class="eval-gauge-wrap">
                    <div class="eval-gauge-bar">
                        <div class="eval-gauge-fill"
                            style="width:<?= $r2Pct !== null ? min(100, max(0, $r2Pct)) : 0 ?>%;background:<?= $r2Color ?>">
                        </div>
                    </div>
                    <span class="eval-gauge-label"><?= $r2Pct !== null ? ($r2Pct >= 80 ? 'Tinggi' : ($r2Pct >= 60 ? 'Sedang' : 'Rendah')) : '—' ?></span>
                </div>
            </div>

            <!-- MAE -->
            <div class="eval-stat-card">
                <div class="eval-stat-header">
                    <div class="eval-stat-icon" style="background:rgba(0,151,184,0.12);color:var(--accent-cyan)">
                        <i class="bi bi-graph-down-arrow"></i>
                    </div>
                    <span class="eval-stat-label">MAE</span>
                </div>
                <div class="eval-stat-value" style="color:var(--accent-cyan)">
                    <?= $maeTest !== null ? number_format($maeTest, 4) : '—' ?>
                </div>
                <div class="eval-stat-sub">Mean Absolute Error</div>
                <div class="eval-stat-note">Lebih kecil = lebih baik</div>
            </div>

            <!-- RMSE -->
            <div class="eval-stat-card">
                <div class="eval-stat-header">
                    <div class="eval-stat-icon" style="background:rgba(212,160,23,0.12);color:var(--accent-yellow)">
                        <i class="bi bi-activity"></i>
                    </div>
                    <span class="eval-stat-label">RMSE</span>
                </div>
                <div class="eval-stat-value" style="color:var(--accent-yellow)">
                    <?= $rmseTest !== null ? number_format($rmseTest, 4) : '—' ?>
                </div>
                <div class="eval-stat-sub">Root Mean Square Error</div>
                <div class="eval-stat-note">Lebih kecil = lebih baik</div>
            </div>

            <!-- MAPE -->
            <div class="eval-stat-card">
                <div class="eval-stat-header">
                    <div class="eval-stat-icon" style="background:rgba(229,62,62,0.12);color:var(--accent-red)">
                        <i class="bi bi-percent"></i>
                    </div>
                    <span class="eval-stat-label">MAPE</span>
                </div>
                <div class="eval-stat-value" style="color:var(--accent-red)">
                    <?= $mapeTest !== null ? number_format($mapeTest, 2) . '%' : '—' ?>
                </div>
                <div class="eval-stat-sub">Mean Absolute Percentage Error</div>
                <div class="eval-stat-note">Lebih kecil = lebih baik</div>
            </div>

        </div>

        <!-- ── Baris bawah: Train vs Test + Hyperparameter ── -->
        <div class="eval-mid-grid">

            <!-- Train vs Test Comparison -->
            <div class="card-mg">
                <div class="mg-card-header">
                    <div class="mg-card-title-group">
                        <span class="section-title">Perbandingan Train vs Test</span>
                        <span class="badge-mg badge-cyan">Overfitting Check</span>
                    </div>
                </div>
                <div class="eval-compare-table">
                    <?php
                    $compareRows = [
                        ['Akurasi', $akurasiTrain, $akurasiTest, '%', 2],
                        ['MAE',     $mae,          $maeTest,     '', 4],
                        ['RMSE',    $rmse,         $rmseTest,    '', 4],
                        ['R²',      $r2 !== null ? $r2 * 100 : null, $r2Test !== null ? $r2Test * 100 : null, '%', 2],
                        ['MAPE',    $mape,         $mapeTest,    '%', 2],
                    ];
                    foreach ($compareRows as $row):
                        [$label, $train, $test, $suffix, $dec] = $row;
                        $diff = ($train !== null && $test !== null) ? $test - $train : null;
                        $diffSign = $diff !== null ? ($diff > 0 ? '+' : '') : '';
                        // Untuk MAE/RMSE/MAPE, selisih negatif = lebih baik (hijau)
                        $isErrorMetric = in_array($label, ['MAE', 'RMSE', 'MAPE']);
                        $diffGood = $diff !== null ? ($isErrorMetric ? $diff <= 0 : $diff >= 0) : null;
                        $diffColor = $diffGood === null ? 'var(--text-placeholder)' : ($diffGood ? 'var(--accent-green)' : 'var(--accent-red)');
                    ?>
                        <div class="eval-cmp-row">
                            <div class="eval-cmp-label"><?= $label ?></div>
                            <div class="eval-cmp-val train-val">
                                <?= $train !== null ? number_format($train, $dec) . $suffix : '—' ?>
                            </div>
                            <div class="eval-cmp-arrow"><i class="bi bi-arrow-right"></i></div>
                            <div class="eval-cmp-val test-val">
                                <?= $test !== null ? number_format($test, $dec) . $suffix : '—' ?>
                            </div>
                            <div class="eval-cmp-diff" style="color:<?= $diffColor ?>">
                                <?= $diff !== null ? $diffSign . number_format($diff, $dec) . $suffix : '—' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="eval-compare-legend">
                    <span class="eval-legend-item"><span class="dot-train"></span> Train</span>
                    <span class="eval-legend-item"><span class="dot-test"></span> Test</span>
                    <span class="eval-legend-item"><i class="bi bi-info-circle" style="color:var(--text-placeholder);font-size:11px"></i>
                        Selisih kecil = model tidak overfitting</span>
                </div>
            </div>

            <!-- Hyperparameter Best Params -->
            <div class="card-mg">
                <div class="mg-card-header">
                    <div class="mg-card-title-group">
                        <span class="section-title">Hyperparameter Terpilih</span>
                        <span class="badge-mg badge-yellow">RandomizedSearchCV</span>
                    </div>
                </div>
                <?php if (!empty($bestParams)): ?>
                    <div class="eval-params-grid">
                        <?php foreach ($bestParams as $key => $val): ?>
                            <div class="eval-param-item">
                                <div class="eval-param-key"><?= esc($key) ?></div>
                                <div class="eval-param-val"><?= esc(is_null($val) ? 'None' : $val) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="padding:24px;text-align:center;color:var(--text-placeholder);font-size:13px">
                        Data hyperparameter belum tersedia.
                    </div>
                <?php endif; ?>

                <!-- Info Model -->
                <div class="eval-model-info-row">
                    <?php if (!empty($m['total_fitur'])): ?>
                        <div class="eval-minfo-item">
                            <i class="bi bi-list-columns"></i>
                            <span><?= $m['total_fitur'] ?> Fitur</span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($m['cv_splits'])): ?>
                        <div class="eval-minfo-item">
                            <i class="bi bi-scissors"></i>
                            <span><?= $m['cv_splits'] ?>-fold CV</span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($m['algoritma'])): ?>
                        <div class="eval-minfo-item">
                            <i class="bi bi-tree-fill"></i>
                            <span><?= esc($m['algoritma']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($m['ukuran_model_kb'])): ?>
                        <div class="eval-minfo-item">
                            <i class="bi bi-hdd-fill"></i>
                            <span><?= number_format($m['ukuran_model_kb']) ?> KB</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── Feature Importance ── -->
        <?php if (!empty($featureImportance)): ?>
            <div class="card-mg">
                <div class="mg-card-header">
                    <div class="mg-card-title-group">
                        <span class="section-title">Feature Importance</span>
                        <span class="badge-mg badge-cyan">Top <?= min(16, count($featureImportance)) ?> Fitur</span>
                    </div>
                    <div class="eval-fi-legend">
                        <span style="font-size:11.5px;color:var(--text-muted)">
                            <i class="bi bi-info-circle"></i>
                            Fitur dengan nilai lebih tinggi lebih berpengaruh terhadap prediksi
                        </span>
                    </div>
                </div>
                <?php
                arsort($featureImportance);
                $topFi  = array_slice($featureImportance, 0, 16, true);
                $maxFi  = max($topFi);
                $rank   = 1;
                $fiGroups = [
                    'blue'   => ['tahun', 'bulan', 'kuartal', 'time_idx', 'produk_encoded'],
                    'cyan'   => ['harga_avg', 'harga_std', 'promo_avg', 'n_transaksi'],
                    'green'  => ['qty_lag1', 'qty_lag2', 'qty_lag3', 'qty_roll3_mean', 'qty_roll3_std', 'qty_roll6_mean'],
                    'yellow' => ['trend'],
                ];
                function getFiColor(string $name): string
                {
                    $map = [
                        'blue'   => ['tahun', 'bulan', 'kuartal', 'time_idx', 'produk_encoded'],
                        'cyan'   => ['harga_avg', 'harga_std', 'promo_avg', 'n_transaksi'],
                        'green'  => ['qty_lag1', 'qty_lag2', 'qty_lag3', 'qty_roll3_mean', 'qty_roll3_std', 'qty_roll6_mean'],
                        'yellow' => ['trend'],
                    ];
                    foreach ($map as $color => $names) {
                        if (in_array($name, $names)) return $color;
                    }
                    return 'blue';
                }
                $colorMap = [
                    'blue'   => ['bg' => 'rgba(46,109,164,0.12)',  'bar' => 'rgba(74,159,212,0.8)',  'text' => 'var(--mg-light)'],
                    'cyan'   => ['bg' => 'rgba(0,151,184,0.10)',   'bar' => 'rgba(0,151,184,0.8)',   'text' => 'var(--accent-cyan)'],
                    'green'  => ['bg' => 'rgba(16,183,127,0.10)',  'bar' => 'rgba(16,183,127,0.8)',  'text' => 'var(--accent-green)'],
                    'yellow' => ['bg' => 'rgba(212,160,23,0.10)',  'bar' => 'rgba(212,160,23,0.8)',  'text' => 'var(--accent-yellow)'],
                ];
                ?>
                <div class="eval-fi-grid">
                    <?php foreach ($topFi as $feat => $imp):
                        $pct     = $maxFi > 0 ? ($imp / $maxFi * 100) : 0;
                        $impPct  = $imp * 100;
                        $ckey    = getFiColor($feat);
                        $c       = $colorMap[$ckey];
                    ?>
                        <div class="eval-fi-row">
                            <div class="eval-fi-rank"><?= $rank++ ?></div>
                            <div class="eval-fi-name" style="background:<?= $c['bg'] ?>;color:<?= $c['text'] ?>">
                                <?= esc($feat) ?>
                            </div>
                            <div class="eval-fi-bar-wrap">
                                <div class="eval-fi-bar-fill" data-w="<?= $pct ?>"
                                    style="background:<?= $c['bar'] ?>"></div>
                            </div>
                            <div class="eval-fi-pct"><?= number_format($impPct, 2) ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Diagram Visualisasi ── -->
        <?php if (!empty($diagramPaths)): ?>
            <div class="card-mg">
                <div class="mg-card-header">
                    <div class="mg-card-title-group">
                        <span class="section-title">Visualisasi Evaluasi</span>
                        <span class="badge-mg badge-cyan"><?= count($diagramPaths) ?> diagram</span>
                    </div>
                </div>

                <!-- Diagram pills -->
                <div class="eval-diag-pills" id="evalDiagPills">
                    <?php
                    $diagramLabels = [
                        'train_actual_vs_pred' => 'Actual vs Pred (Train)',
                        'test_actual_vs_pred'  => 'Actual vs Pred (Test)',
                        'train_residual'       => 'Residual (Train)',
                        'test_residual'        => 'Residual (Test)',
                        'feature_importance'   => 'Feature Importance',
                        'train_timeseries'     => 'Time Series (Train)',
                        'test_timeseries'      => 'Time Series (Test)',
                        'top_products'         => 'Top Produk',
                        'monthly_trend'        => 'Tren Bulanan',
                    ];
                    $first = true;
                    foreach ($diagramPaths as $key => $path):
                        $label = $diagramLabels[$key] ?? $key;
                    ?>
                        <button class="eval-diag-pill <?= $first ? 'active' : '' ?>"
                            data-key="<?= esc($key) ?>"
                            data-id="<?= (int)($m['id'] ?? 0) ?>">
                            <?= esc($label) ?>
                        </button>
                    <?php $first = false;
                    endforeach; ?>
                </div>

                <!-- Diagram frame -->
                <div class="eval-diag-frame" id="evalDiagFrame">
                    <img id="evalDiagImg" src="" alt="Diagram"
                        style="width:100%;height:100%;object-fit:contain;display:none">
                    <div class="eval-diag-loading" id="evalDiagLoading" style="display:none">
                        <div class="eval-spinner-ring"></div>
                        <span>Memuat diagram…</span>
                    </div>
                    <div class="eval-diag-empty" id="evalDiagEmpty">
                        <i class="bi bi-bar-chart-fill"></i>
                        <span>Pilih diagram di atas</span>
                    </div>
                </div>

                <div class="eval-diag-actions">
                    <button class="btn-mg btn-outline-mg btn-sm-eval" id="evalBtnZoom" style="display:none">
                        <i class="bi bi-zoom-in"></i> Perbesar
                    </button>
                    <a id="evalBtnDownload" href="#" download style="display:none"
                        class="btn-mg btn-primary-mg btn-sm-eval">
                        <i class="bi bi-download"></i> Unduh PNG
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Interpretasi & Panduan ── -->
        <div class="card-mg">
            <div class="mg-card-header">
                <div class="mg-card-title-group">
                    <span class="section-title">Panduan Interpretasi Metrik</span>
                    <span class="badge-mg badge-green">Referensi</span>
                </div>
            </div>
            <div class="eval-guide-grid">
                <div class="eval-guide-item">
                    <div class="eval-guide-icon" style="background:rgba(16,183,127,0.12);color:var(--accent-green)">
                        <i class="bi bi-bullseye"></i>
                    </div>
                    <div>
                        <div class="eval-guide-title">Akurasi</div>
                        <div class="eval-guide-body">
                            Persentase prediksi yang dianggap akurat (100% − MAPE).
                            <strong style="color:var(--accent-green)">≥ 80%</strong> = baik untuk forecasting ritel.
                        </div>
                    </div>
                </div>
                <div class="eval-guide-item">
                    <div class="eval-guide-icon" style="background:rgba(74,159,212,0.12);color:var(--mg-light)">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div>
                        <div class="eval-guide-title">R² Score</div>
                        <div class="eval-guide-body">
                            Proporsi variansi target yang dijelaskan model.
                            <strong style="color:var(--mg-light)">≥ 0.80</strong> = model fit yang baik.
                            Nilai 1.0 = prediksi sempurna.
                        </div>
                    </div>
                </div>
                <div class="eval-guide-item">
                    <div class="eval-guide-icon" style="background:rgba(0,151,184,0.12);color:var(--accent-cyan)">
                        <i class="bi bi-graph-down-arrow"></i>
                    </div>
                    <div>
                        <div class="eval-guide-title">MAE</div>
                        <div class="eval-guide-body">
                            Rata-rata kesalahan absolut dalam satuan yang sama dengan target (qty).
                            Lebih mudah diinterpretasikan dibanding RMSE.
                        </div>
                    </div>
                </div>
                <div class="eval-guide-item">
                    <div class="eval-guide-icon" style="background:rgba(212,160,23,0.12);color:var(--accent-yellow)">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div>
                        <div class="eval-guide-title">RMSE</div>
                        <div class="eval-guide-body">
                            Seperti MAE, tapi memberikan bobot lebih besar pada error besar.
                            RMSE &gt; MAE mengindikasikan outlier prediksi.
                        </div>
                    </div>
                </div>
                <div class="eval-guide-item">
                    <div class="eval-guide-icon" style="background:rgba(229,62,62,0.12);color:var(--accent-red)">
                        <i class="bi bi-percent"></i>
                    </div>
                    <div>
                        <div class="eval-guide-title">MAPE</div>
                        <div class="eval-guide-body">
                            Error relatif dalam persen.
                            <strong style="color:var(--accent-green)">&lt; 10%</strong> = sangat baik,
                            <strong style="color:var(--accent-yellow)">&lt; 20%</strong> = cukup baik untuk ritel.
                        </div>
                    </div>
                </div>
                <div class="eval-guide-item">
                    <div class="eval-guide-icon" style="background:rgba(74,159,212,0.12);color:var(--accent-cyan)">
                        <i class="bi bi-bezier2"></i>
                    </div>
                    <div>
                        <div class="eval-guide-title">Overfitting</div>
                        <div class="eval-guide-body">
                            Jika akurasi Train jauh lebih tinggi dari Test, model overfitting.
                            Selisih &lt; 10% dianggap normal untuk Random Forest.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Riwayat Model (perbandingan singkat) ── -->
        <?php if (!empty($riwayatModels)): ?>
            <div class="card-mg">
                <div class="mg-card-header">
                    <div class="mg-card-title-group">
                        <span class="section-title">Perbandingan Model Sebelumnya</span>
                        <span class="badge-mg badge-cyan"><?= count($riwayatModels) ?> model</span>
                    </div>
                    <a href="<?= base_url('training/riwayat_model') ?>" class="btn-mg btn-outline-mg btn-sm-eval">
                        <i class="bi bi-arrow-right"></i> Lihat Semua
                    </a>
                </div>
                <div class="mg-table-wrap">
                    <table class="table-mg eval-compare-tbl">
                        <thead>
                            <tr>
                                <th>Versi</th>
                                <th style="text-align:center">Akurasi</th>
                                <th style="text-align:center">R²</th>
                                <th style="text-align:center">MAE</th>
                                <th style="text-align:center">RMSE</th>
                                <th style="text-align:center">MAPE</th>
                                <th style="text-align:center">Record</th>
                                <th style="text-align:center">Tanggal</th>
                                <th style="text-align:center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayatModels as $rm):
                                $isAkt = (int)($rm['is_active'] ?? 0) === 1;
                                $rmAk  = isset($rm['akurasi']) ? (float)$rm['akurasi'] : null;
                                $rmR2  = isset($rm['r2'])      ? (float)$rm['r2']      : null;
                            ?>
                                <tr <?= $isAkt ? 'class="row-active-model"' : '' ?>>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px">
                                            <?php if ($isAkt): ?>
                                                <span class="dot-aktif-sm"></span>
                                            <?php endif; ?>
                                            <span style="font-weight:600;font-size:13px"><?= esc($rm['versi'] ?? '—') ?></span>
                                            <?php if ($isAkt): ?>
                                                <span class="badge-mg badge-green" style="font-size:10px;padding:1px 7px">Aktif</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="text-align:center">
                                        <?php if ($rmAk !== null): ?>
                                            <span style="color:<?= $rmAk >= 80 ? 'var(--accent-green)' : ($rmAk >= 60 ? 'var(--accent-yellow)' : 'var(--accent-red)') ?>;font-weight:700">
                                                <?= number_format($rmAk, 2) ?>%
                                            </span>
                                        <?php else: ?><span class="null-val">—</span><?php endif; ?>
                                    </td>
                                    <td style="text-align:center">
                                        <?php if ($rmR2 !== null): ?>
                                            <span style="color:<?= $rmR2 >= 0.8 ? 'var(--accent-green)' : ($rmR2 >= 0.6 ? 'var(--accent-yellow)' : 'var(--accent-red)') ?>;font-weight:600">
                                                <?= number_format($rmR2, 4) ?>
                                            </span>
                                        <?php else: ?><span class="null-val">—</span><?php endif; ?>
                                    </td>
                                    <td style="text-align:center;color:var(--accent-cyan)">
                                        <?= isset($rm['mae']) ? number_format((float)$rm['mae'], 4) : '<span class="null-val">—</span>' ?>
                                    </td>
                                    <td style="text-align:center;color:var(--accent-yellow)">
                                        <?= isset($rm['rmse']) ? number_format((float)$rm['rmse'], 4) : '<span class="null-val">—</span>' ?>
                                    </td>
                                    <td style="text-align:center;color:var(--accent-red)">
                                        <?= isset($rm['mape']) ? number_format((float)$rm['mape'], 2) . '%' : '<span class="null-val">—</span>' ?>
                                    </td>
                                    <td style="text-align:center">
                                        <?= isset($rm['total_record']) ? number_format($rm['total_record']) : '<span class="null-val">—</span>' ?>
                                    </td>
                                    <td style="text-align:center;font-size:12px;color:var(--text-muted)">
                                        <?= !empty($rm['selesai_at']) ? date('d M Y', strtotime($rm['selesai_at'])) : '—' ?>
                                    </td>
                                    <td style="text-align:center">
                                        <span class="status-pill status-<?= $rm['status'] === 'success' ? 'success' : 'failed' ?>">
                                            <?= $rm['status'] === 'success' ? 'Sukses' : esc($rm['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; // end if modelAktif 
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

    .btn-sm-eval {
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

    /* ── Empty state ── */
    .eval-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 80px 24px;
        text-align: center;
    }

    .eval-empty-icon {
        width: 80px;
        height: 80px;
        background: rgba(74, 159, 212, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        color: var(--text-placeholder);
        margin-bottom: 16px;
    }

    .eval-empty-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-ink);
        margin-bottom: 6px;
    }

    .eval-empty-sub {
        font-size: 13px;
        color: var(--text-muted);
        max-width: 300px;
        line-height: 1.6;
    }

    /* ── Model banner ── */
    .eval-model-banner {
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

    .eval-banner-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .eval-banner-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--accent-green);
        box-shadow: 0 0 0 4px rgba(16, 183, 127, 0.18);
        animation: pulseDot 2s infinite;
        flex-shrink: 0;
    }

    @keyframes pulseDot {

        0%,
        100% {
            box-shadow: 0 0 0 4px rgba(16, 183, 127, 0.18)
        }

        50% {
            box-shadow: 0 0 0 8px rgba(16, 183, 127, 0.08)
        }
    }

    .eval-banner-icon {
        width: 38px;
        height: 38px;
        background: rgba(16, 183, 127, 0.12);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent-green);
        font-size: 17px;
        flex-shrink: 0;
    }

    .eval-banner-versi {
        font-size: 15px;
        font-weight: 700;
        color: var(--accent-green);
    }

    .eval-banner-meta {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    /* ── Stats grid ── */
    .eval-stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
    }

    @media(max-width:1100px) {
        .eval-stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media(max-width:700px) {
        .eval-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:420px) {
        .eval-stats-grid {
            grid-template-columns: 1fr;
        }
    }

    .eval-stat-card {
        background: var(--card-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-md);
        padding: 18px 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: var(--transition);
        animation: fadeSlideIn .3s ease;
    }

    .eval-stat-card:hover {
        border-color: rgba(74, 159, 212, 0.3);
        transform: translateY(-2px);
    }

    .highlight-card {
        background: linear-gradient(135deg, rgba(16, 183, 127, 0.05), rgba(0, 151, 184, 0.03));
        border-color: rgba(16, 183, 127, 0.2);
    }

    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(8px)
        }

        to {
            opacity: 1;
            transform: none
        }
    }

    .eval-stat-header {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .eval-stat-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .eval-stat-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .eval-stat-value {
        font-family: var(--font-display);
        font-size: 26px;
        font-weight: 800;
        line-height: 1;
    }

    .eval-stat-sub {
        font-size: 11.5px;
        color: var(--text-muted);
    }

    .eval-stat-note {
        font-size: 11px;
        color: var(--text-placeholder);
        font-style: italic;
    }

    .eval-stat-compare {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
        color: var(--text-muted);
    }

    .eval-stat-compare-label {
        color: var(--text-placeholder);
    }

    .eval-stat-compare-val {
        color: var(--accent-cyan);
        font-weight: 600;
    }

    /* Gauge */
    .eval-gauge-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 2px;
    }

    .eval-gauge-bar {
        flex: 1;
        height: 5px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: 999px;
        overflow: hidden;
    }

    .eval-gauge-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 1s ease;
    }

    .eval-gauge-label {
        font-size: 10px;
        font-weight: 600;
        color: var(--text-placeholder);
        white-space: nowrap;
    }

    /* ── Mid grid ── */
    .eval-mid-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media(max-width:860px) {
        .eval-mid-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ── Compare table ── */
    .eval-compare-table {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .eval-cmp-row {
        display: grid;
        grid-template-columns: 80px 1fr 28px 1fr 80px;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border-bottom: 1px solid var(--surface-border);
        font-size: 13px;
    }

    .eval-cmp-row:last-child {
        border-bottom: none;
    }

    .eval-cmp-row:hover {
        background: var(--card-bg-alt);
        border-radius: 6px;
    }

    .eval-cmp-label {
        font-weight: 600;
        color: var(--text-ink);
        font-size: 12.5px;
    }

    .eval-cmp-val {
        font-family: 'Courier New', monospace;
        font-weight: 600;
        font-size: 13px;
        text-align: center;
    }

    .train-val {
        color: var(--text-muted);
    }

    .test-val {
        color: var(--text-ink);
    }

    .eval-cmp-arrow {
        color: var(--text-placeholder);
        font-size: 12px;
        text-align: center;
    }

    .eval-cmp-diff {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        font-weight: 700;
        text-align: right;
    }

    .eval-compare-legend {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 10px 12px;
        border-top: 1px solid var(--surface-border);
        font-size: 11.5px;
        color: var(--text-muted);
        background: var(--card-bg-alt);
        border-radius: 0 0 var(--radius-sm) var(--radius-sm);
        margin-top: 4px;
    }

    .eval-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .dot-train {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--text-muted);
        display: inline-block;
    }

    .dot-test {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--mg-light);
        display: inline-block;
    }

    /* ── Hyperparams ── */
    .eval-params-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-bottom: 14px;
    }

    @media(max-width:520px) {
        .eval-params-grid {
            grid-template-columns: 1fr;
        }
    }

    .eval-param-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 14px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-sm);
    }

    .eval-param-key {
        font-size: 11.5px;
        color: var(--text-muted);
        font-family: 'Courier New', monospace;
    }

    .eval-param-val {
        font-size: 13px;
        font-weight: 700;
        color: var(--accent-cyan);
        font-family: 'Courier New', monospace;
    }

    .eval-model-info-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        padding: 10px 14px;
        background: rgba(74, 159, 212, 0.04);
        border: 1px solid rgba(74, 159, 212, 0.12);
        border-radius: var(--radius-sm);
        margin-top: 4px;
    }

    .eval-minfo-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--text-muted);
    }

    .eval-minfo-item i {
        color: var(--accent-cyan);
        font-size: 13px;
    }

    /* ── Feature importance ── */
    .eval-fi-grid {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .eval-fi-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .eval-fi-rank {
        font-size: 10px;
        font-weight: 700;
        color: var(--text-placeholder);
        min-width: 18px;
        text-align: right;
    }

    .eval-fi-name {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        font-family: 'Courier New', monospace;
        min-width: 130px;
        text-align: right;
        flex-shrink: 0;
    }

    .eval-fi-bar-wrap {
        flex: 1;
        height: 14px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: 999px;
        overflow: hidden;
    }

    .eval-fi-bar-fill {
        height: 100%;
        border-radius: 999px;
        width: 0;
        transition: width 1.2s ease;
    }

    .eval-fi-pct {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--accent-cyan);
        min-width: 44px;
        text-align: right;
        font-family: 'Courier New', monospace;
    }

    .eval-fi-legend {
        font-size: 11.5px;
        color: var(--text-muted);
    }

    /* ── Diagram ── */
    .eval-diag-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 14px;
    }

    .eval-diag-pill {
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid var(--surface-border);
        background: transparent;
        color: var(--text-muted);
        transition: var(--transition);
    }

    .eval-diag-pill:hover {
        border-color: rgba(74, 159, 212, .35);
        color: var(--mg-light);
    }

    .eval-diag-pill.active {
        background: rgba(74, 159, 212, .12);
        color: var(--mg-light);
        border-color: rgba(74, 159, 212, .4);
    }

    .eval-diag-frame {
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

    .eval-diag-empty,
    .eval-diag-loading {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: var(--text-placeholder);
        font-size: 13px;
        height: 100%;
        width: 100%;
    }

    .eval-diag-empty i {
        font-size: 36px;
        opacity: .3;
    }

    .eval-spinner-ring {
        width: 24px;
        height: 24px;
        border: 3px solid var(--surface-border);
        border-top-color: var(--accent-cyan);
        border-radius: 50%;
        animation: spinR .8s linear infinite;
    }

    @keyframes spinR {
        to {
            transform: rotate(360deg)
        }
    }

    .eval-diag-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 10px;
    }

    /* ── Guide ── */
    .eval-guide-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }

    @media(max-width:860px) {
        .eval-guide-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:540px) {
        .eval-guide-grid {
            grid-template-columns: 1fr;
        }
    }

    .eval-guide-item {
        display: flex;
        gap: 12px;
        padding: 14px;
        background: var(--card-bg-alt);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-sm);
    }

    .eval-guide-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .eval-guide-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-ink);
        margin-bottom: 4px;
    }

    .eval-guide-body {
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.6;
    }

    /* ── Compare table (riwayat) ── */
    .eval-compare-tbl {
        min-width: 850px;
    }

    .row-active-model {
        background: rgba(16, 183, 127, 0.04) !important;
        border-left: 3px solid var(--accent-green);
    }

    .dot-aktif-sm {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--accent-green);
        flex-shrink: 0;
        box-shadow: 0 0 0 2px rgba(16, 183, 127, .25);
    }

    .null-val {
        color: var(--text-placeholder);
        font-size: 12px;
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

    .status-failed {
        background: rgba(229, 62, 62, 0.1);
        color: var(--accent-red);
        border: 1px solid rgba(229, 62, 62, 0.2);
    }

    /* Badges */
    .badge-mg {
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
    }

    .badge-cyan {
        background: rgba(0, 151, 184, 0.12);
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 151, 184, 0.2);
    }

    .badge-green {
        background: rgba(16, 183, 127, 0.12);
        color: var(--accent-green);
        border: 1px solid rgba(16, 183, 127, 0.2);
    }

    .badge-yellow {
        background: rgba(212, 160, 23, 0.12);
        color: var(--accent-yellow);
        border: 1px solid rgba(212, 160, 23, 0.2);
    }

    /* Card header */
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

    .mg-table-wrap {
        overflow-x: auto;
        border-radius: var(--radius-sm);
        border: 1px solid var(--surface-border);
    }
</style>


<!-- ════════════════════════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════════════════════ -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        // ── Animate feature importance bars ─────────────────────────────────
        requestAnimationFrame(() => {
            document.querySelectorAll('.eval-fi-bar-fill').forEach(el => {
                const w = el.dataset.w;
                if (w) setTimeout(() => el.style.width = w + '%', 100);
            });
        });

        // ── Diagram switching ────────────────────────────────────────────────
        const diagImg = document.getElementById('evalDiagImg');
        const diagLoading = document.getElementById('evalDiagLoading');
        const diagEmpty = document.getElementById('evalDiagEmpty');
        const btnZoom = document.getElementById('evalBtnZoom');
        const btnDownload = document.getElementById('evalBtnDownload');

        function loadDiagram(id, key) {
            if (!diagImg) return;
            diagImg.style.display = 'none';
            diagEmpty.style.display = 'none';
            diagLoading.style.display = 'flex';
            if (btnZoom) btnZoom.style.display = 'none';
            if (btnDownload) btnDownload.style.display = 'none';

            const url = BASE_URL + 'training/model/diagram/' + id + '/' + key;
            const tmp = new Image();
            tmp.onload = function() {
                diagImg.src = url;
                diagImg.style.display = 'block';
                diagLoading.style.display = 'none';
                if (btnZoom) {
                    btnZoom.style.display = '';
                    btnZoom.onclick = () => window.open(url, '_blank');
                }
                if (btnDownload) {
                    btnDownload.style.display = '';
                    btnDownload.href = url;
                    btnDownload.download = key + '.png';
                }
            };
            tmp.onerror = function() {
                diagLoading.style.display = 'none';
                diagEmpty.style.display = 'flex';
                diagEmpty.innerHTML = '<i class="bi bi-exclamation-circle" style="font-size:28px;opacity:.4"></i><span>Diagram tidak tersedia</span>';
            };
            tmp.src = url;
        }

        document.querySelectorAll('.eval-diag-pill').forEach(pill => {
            pill.addEventListener('click', function() {
                document.querySelectorAll('.eval-diag-pill').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                loadDiagram(this.dataset.id, this.dataset.key);
            });
        });

        // Auto-load diagram pertama jika ada
        const firstPill = document.querySelector('.eval-diag-pill.active');
        if (firstPill && firstPill.dataset.id && firstPill.dataset.key) {
            setTimeout(() => loadDiagram(firstPill.dataset.id, firstPill.dataset.key), 200);
        }
    });
</script>