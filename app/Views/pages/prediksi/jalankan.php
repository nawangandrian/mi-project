<?php
/**
 * View  : pages/prediksi/jalankan.php
 * Modul : Jalankan Prediksi Penjualan — Mi Store Kudus
 */
?>

<div class="content-wrapper-inner">

    <!-- ── Page Header ── -->
    <div class="mg-page-header">
        <div>
            <h1 class="page-title">Jalankan Model</h1>
            <p class="page-subtitle">Generate prediksi penjualan menggunakan model Random Forest aktif</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('prediksi/akurasi') ?>" class="btn-mg btn-outline-mg">
                <i class="bi bi-bullseye"></i> Evaluasi Model
            </a>
        </div>
    </div>

    <!-- ── Quick Nav Pills ── -->
    <div class="quick-nav-row">
        <a href="<?= base_url('prediksi') ?>" class="quick-nav-pill">
            <i class="bi bi-graph-up-arrow"></i><span>Prediksi Penjualan</span>
            <span class="pill-badge ai-badge">AI</span>
        </a>
        <a href="<?= base_url('prediksi/jalankan') ?>" class="quick-nav-pill active">
            <i class="bi bi-play-circle-fill"></i><span>Jalankan Prediksi</span>
        </a>
        <a href="<?= base_url('prediksi/riwayat') ?>" class="quick-nav-pill">
            <i class="bi bi-clock-history"></i><span>Riwayat Prediksi</span>
        </a>
        <a href="<?= base_url('prediksi/akurasi') ?>" class="quick-nav-pill">
            <i class="bi bi-bullseye"></i><span>Evaluasi Model</span>
        </a>
    </div>

    <?php if (empty($modelAktif)): ?>
    <!-- ── No Model Warning ── -->
    <div class="jl-no-model-box">
        <div class="jl-nm-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div>
            <div class="jl-nm-title">Belum Ada Model Aktif</div>
            <div class="jl-nm-sub">
                Anda perlu melatih model Random Forest terlebih dahulu sebelum dapat menjalankan prediksi.
            </div>
            <a href="<?= base_url('training/proses') ?>" class="btn-mg btn-primary-mg" style="margin-top:14px;text-decoration:none;display:inline-flex">
                <i class="bi bi-cpu-fill"></i> Latih Model Sekarang
            </a>
        </div>
    </div>
    <?php else: ?>

    <!-- ── Pipeline Visual ── -->
    <div class="card-mg">
        <div class="mg-card-header">
            <div class="mg-card-title-group">
                <span class="section-title">Pipeline Prediksi</span>
            </div>
        </div>
        <div class="jl-pipeline">
            <div class="jl-pipe-step">
                <div class="jl-pipe-icon" style="background:rgba(74,159,212,0.12);color:var(--mg-light)">
                    <i class="bi bi-sliders2"></i>
                </div>
                <div class="jl-pipe-label">Konfigurasi</div>
                <div class="jl-pipe-desc">Pilih produk & periode</div>
            </div>
            <div class="jl-pipe-arrow"><i class="bi bi-arrow-right"></i></div>
            <div class="jl-pipe-step">
                <div class="jl-pipe-icon" style="background:rgba(0,151,184,0.12);color:var(--accent-cyan)">
                    <i class="bi bi-database-fill-down"></i>
                </div>
                <div class="jl-pipe-label">Load Fitur</div>
                <div class="jl-pipe-desc">Baca lag & rolling dari DB</div>
            </div>
            <div class="jl-pipe-arrow"><i class="bi bi-arrow-right"></i></div>
            <div class="jl-pipe-step">
                <div class="jl-pipe-icon" style="background:rgba(212,160,23,0.12);color:var(--accent-yellow)">
                    <i class="bi bi-cpu-fill"></i>
                </div>
                <div class="jl-pipe-label">Inferensi RF</div>
                <div class="jl-pipe-desc">Predict dengan model .pkl</div>
            </div>
            <div class="jl-pipe-arrow"><i class="bi bi-arrow-right"></i></div>
            <div class="jl-pipe-step">
                <div class="jl-pipe-icon" style="background:rgba(16,183,127,0.12);color:var(--accent-green)">
                    <i class="bi bi-floppy2-fill"></i>
                </div>
                <div class="jl-pipe-label">Simpan</div>
                <div class="jl-pipe-desc">Insert ke tabel prediksi</div>
            </div>
            <div class="jl-pipe-arrow"><i class="bi bi-arrow-right"></i></div>
            <div class="jl-pipe-step">
                <div class="jl-pipe-icon" style="background:rgba(229,62,62,0.12);color:var(--accent-red)">
                    <i class="bi bi-table"></i>
                </div>
                <div class="jl-pipe-label">Tampilkan</div>
                <div class="jl-pipe-desc">Lihat hasil prediksi</div>
            </div>
        </div>
    </div>

    <!-- ── Baris utama: Form + Status ── -->
    <div class="jl-main-grid">

        <!-- Form Konfigurasi -->
        <div class="card-mg">
            <div class="mg-card-header">
                <div class="mg-card-title-group">
                    <span class="section-title">Konfigurasi Prediksi</span>
                </div>
            </div>

            <!-- Model Aktif Info -->
            <div class="jl-model-info-box">
                <div class="jl-mi-left">
                    <div class="jl-mi-dot"></div>
                    <div class="jl-mi-icon"><i class="bi bi-cpu-fill"></i></div>
                    <div>
                        <div class="jl-mi-versi"><?= esc($modelAktif['versi'] ?? '—') ?></div>
                        <div class="jl-mi-sub">Model Aktif</div>
                    </div>
                </div>
                <?php if (!empty($modelAktif['akurasi'])): ?>
                <div class="jl-mi-right">
                    <div class="jl-mi-acc"><?= number_format((float)$modelAktif['akurasi'], 2) ?>%</div>
                    <div class="jl-mi-acc-label">Akurasi</div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Form Fields -->
            <div class="jl-form-body">

                <!-- Periode -->
                <div class="jl-field-group">
                    <div class="jl-field-label">
                        <i class="bi bi-calendar3"></i> Periode Prediksi
                    </div>
                    <div class="jl-row-2">
                        <div class="jl-field">
                            <label class="form-label-mg">Bulan</label>
                            <div class="mg-select-wrap">
                                <i class="bi bi-calendar3"></i>
                                <select name="bulan_prediksi" id="bulanPrediksi" class="form-mg">
                                    <?php
                                    $bln = ['','Januari','Februari','Maret','April','Mei','Juni',
                                            'Juli','Agustus','September','Oktober','November','Desember'];
                                    for ($i=1; $i<=12; $i++):
                                        $nextMonth = (int)date('n') % 12 + 1;
                                        $sel = $i === $nextMonth ? 'selected' : '';
                                    ?>
                                    <option value="<?= $i ?>" <?= $sel ?>><?= $bln[$i] ?></option>
                                    <?php endfor; ?>
                                </select>
                                <i class="bi bi-chevron-down mg-select-arrow"></i>
                            </div>
                        </div>
                        <div class="jl-field">
                            <label class="form-label-mg">Tahun</label>
                            <div class="mg-input-icon">
                                <i class="bi bi-calendar"></i>
                                <input type="number" id="tahunPrediksi" name="tahun_prediksi"
                                       class="form-mg" value="<?= date('Y') ?>"
                                       min="2020" max="2030">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produk -->
                <div class="jl-field-group">
                    <div class="jl-field-label">
                        <i class="bi bi-phone-fill"></i> Produk yang Diprediksi
                    </div>
                    <div class="jl-produk-mode">
                        <label class="jl-radio-card" id="rSemua">
                            <input type="radio" name="produk_mode" value="semua" checked>
                            <div class="jl-rc-body">
                                <i class="bi bi-grid-fill"></i>
                                <span class="jl-rc-title">Semua Produk</span>
                                <span class="jl-rc-sub"><?= count($produks) ?> produk</span>
                            </div>
                        </label>
                        <label class="jl-radio-card" id="rPilih">
                            <input type="radio" name="produk_mode" value="pilih">
                            <div class="jl-rc-body">
                                <i class="bi bi-list-check"></i>
                                <span class="jl-rc-title">Pilih Produk</span>
                                <span class="jl-rc-sub">Spesifik</span>
                            </div>
                        </label>
                    </div>

                    <!-- Daftar produk checkbox -->
                    <div id="produkCheckboxWrap" style="display:none;margin-top:12px">
                        <div class="jl-produk-search">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchProdukJl" placeholder="Cari produk…">
                        </div>
                        <div class="jl-produk-list" id="produkList">
                            <?php foreach ($produks as $p): ?>
                            <label class="jl-check-item">
                                <input type="checkbox" name="produk_list[]"
                                       value="<?= esc($p['nama_produk']) ?>"
                                       class="produk-checkbox">
                                <span class="jl-check-icon"><i class="bi bi-check"></i></span>
                                <span class="jl-check-label"><?= esc($p['nama_produk']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="jl-check-actions">
                            <button type="button" class="btn-jl-sm" id="btnPilihSemua">Pilih Semua</button>
                            <button type="button" class="btn-jl-sm" id="btnHapusPilihan">Hapus Pilihan</button>
                            <span class="jl-selected-count" id="selectedCount">0 dipilih</span>
                        </div>
                    </div>
                </div>

                <!-- Opsi lanjutan -->
                <div class="jl-field-group">
                    <div class="jl-field-label">
                        <i class="bi bi-gear-fill"></i> Opsi Lanjutan
                    </div>
                    <label class="jl-toggle-item">
                        <div class="jl-toggle-text">
                            <span class="jl-toggle-title">Timpa data yang sudah ada</span>
                            <span class="jl-toggle-sub">Jika periode ini sudah ada prediksinya, data lama akan diganti</span>
                        </div>
                        <label class="jl-switch">
                            <input type="checkbox" id="overwriteMode" checked>
                            <span class="jl-switch-slider"></span>
                        </label>
                    </label>
                </div>

                <!-- Tombol jalankan -->
                <button class="btn-jl-run" id="btnJalankan">
                    <span class="btn-jl-icon"><i class="bi bi-play-fill"></i></span>
                    <span class="btn-jl-text">Jalankan Prediksi</span>
                    <span class="btn-jl-sub">Random Forest Inference</span>
                </button>

                <div class="jl-info-note">
                    <i class="bi bi-info-circle-fill"></i>
                    Prediksi dihitung berdasarkan fitur lag, rolling mean, dan trend dari data training.
                </div>
            </div>
        </div>

        <!-- Status Panel -->
        <div class="card-mg jl-status-panel" id="jlStatusPanel">

            <!-- IDLE -->
            <div class="jl-state" id="jlIdle">
                <div class="jl-state-icon idle-ic">
                    <i class="bi bi-hourglass"></i>
                </div>
                <div class="jl-state-title">Siap Dijalankan</div>
                <div class="jl-state-sub">Konfigurasi parameter di kiri lalu klik <strong>Jalankan Prediksi</strong>.</div>
                <?php if (!empty($lastPrediksi)): ?>
                <div class="jl-last-run">
                    <div class="jl-lr-label">Prediksi terakhir</div>
                    <div class="jl-lr-time"><?= esc($lastPrediksi['created_at'] ?? '—') ?></div>
                    <div class="jl-lr-count">
                        <i class="bi bi-phone-fill"></i>
                        <?= $lastPrediksi['total'] ?? 0 ?> produk diprediksi
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- RUNNING -->
            <div class="jl-state" id="jlRunning" style="display:none">
                <div class="jl-state-icon running-ic">
                    <div class="jl-spinner">
                        <div class="jl-spin-ring"></div>
                        <div class="jl-spin-ring jl-spin-r2"></div>
                        <i class="bi bi-graph-up-arrow jl-spin-core"></i>
                    </div>
                </div>
                <div class="jl-state-title">Menghitung Prediksi…</div>
                <div class="jl-state-sub" id="jlRunMsg">Memproses fitur produk…</div>
                <div class="jl-progress-wrap">
                    <div class="jl-progress-bar">
                        <div class="jl-progress-fill" id="jlProgressFill"></div>
                        <div class="jl-progress-shimmer"></div>
                    </div>
                    <div class="jl-progress-pct" id="jlProgressPct">0%</div>
                </div>
                <div class="jl-steps" id="jlSteps">
                    <div class="jl-step" id="jlStep1"><i class="bi bi-circle-fill"></i> Membaca data produk</div>
                    <div class="jl-step" id="jlStep2"><i class="bi bi-circle-fill"></i> Menyiapkan fitur input</div>
                    <div class="jl-step" id="jlStep3"><i class="bi bi-circle-fill"></i> Menjalankan inferensi RF</div>
                    <div class="jl-step" id="jlStep4"><i class="bi bi-circle-fill"></i> Menyimpan hasil prediksi</div>
                </div>
                <div class="jl-elapsed">
                    Berjalan <span id="jlElapsed">0</span> detik
                </div>
            </div>

            <!-- SUCCESS -->
            <div class="jl-state" id="jlSuccess" style="display:none">
                <div class="jl-state-icon success-ic">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="jl-state-title">Prediksi Selesai!</div>
                <div class="jl-state-sub" id="jlSuccessMsg">Hasil prediksi berhasil disimpan.</div>
                <div class="jl-result-stats" id="jlResultStats">
                    <div class="jl-rs-item">
                        <span class="jl-rs-val" id="rsTotal">—</span>
                        <span class="jl-rs-label">Produk</span>
                    </div>
                    <div class="jl-rs-sep"></div>
                    <div class="jl-rs-item">
                        <span class="jl-rs-val" id="rsPeriode">—</span>
                        <span class="jl-rs-label">Periode</span>
                    </div>
                    <div class="jl-rs-sep"></div>
                    <div class="jl-rs-item">
                        <span class="jl-rs-val" id="rsDurasi">—</span>
                        <span class="jl-rs-label">Detik</span>
                    </div>
                </div>
                <div class="jl-success-actions">
                    <a href="<?= base_url('prediksi') ?>" class="btn-mg btn-primary-mg" style="text-decoration:none">
                        <i class="bi bi-graph-up-arrow"></i> Lihat Hasil
                    </a>
                    <button class="btn-mg btn-outline-mg" id="btnJlReset">
                        <i class="bi bi-arrow-repeat"></i> Prediksi Lagi
                    </button>
                </div>
            </div>

            <!-- ERROR -->
            <div class="jl-state" id="jlError" style="display:none">
                <div class="jl-state-icon error-ic">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div class="jl-state-title">Prediksi Gagal</div>
                <div class="jl-state-sub" id="jlErrMsg">Terjadi kesalahan saat prediksi.</div>
                <div class="jl-err-detail" id="jlErrDetail" style="display:none"></div>
                <button class="btn-mg btn-outline-mg" id="btnJlRetry" style="margin-top:16px">
                    <i class="bi bi-arrow-repeat"></i> Coba Lagi
                </button>
            </div>
        </div>
    </div>

    <?php endif; // end if modelAktif ?>
</div><!-- /content-wrapper-inner -->


<!-- ══════════════════════════════════════════════════════════
     STYLES
══════════════════════════════════════════════════════════ -->
<style>
.content-wrapper-inner{display:flex;flex-direction:column;gap:24px}
.mg-page-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px}
.header-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.btn-outline-mg{background:var(--card-bg-alt);color:var(--text-muted);border:1px solid var(--surface-border)}
.btn-outline-mg:hover{border-color:var(--mg-light);color:var(--mg-light)}

/* nav pills */
.quick-nav-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.quick-nav-pill{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:var(--radius-sm);background:var(--card-bg-alt);border:1px solid var(--surface-border);color:var(--text-muted);font-size:13px;font-weight:600;text-decoration:none;transition:var(--transition);white-space:nowrap}
.quick-nav-pill:hover{background:rgba(74,159,212,0.1);border-color:rgba(74,159,212,0.35);color:var(--mg-light);transform:translateY(-1px)}
.quick-nav-pill.active{background:rgba(74,159,212,0.15);border-color:rgba(74,159,212,0.4);color:var(--mg-light)}
.pill-badge{display:inline-flex;align-items:center;padding:1px 7px;border-radius:20px;font-size:10px;font-weight:700}
.ai-badge{background:rgba(74,159,212,0.15);color:var(--mg-light);border:1px solid rgba(74,159,212,0.3)}

/* no-model */
.jl-no-model-box{display:flex;align-items:flex-start;gap:18px;padding:24px;background:rgba(212,160,23,0.06);border:1px solid rgba(212,160,23,0.2);border-radius:var(--radius-md)}
.jl-nm-icon{font-size:36px;color:var(--accent-yellow);flex-shrink:0;margin-top:4px}
.jl-nm-title{font-size:15px;font-weight:700;color:var(--text-ink);margin-bottom:6px}
.jl-nm-sub{font-size:13px;color:var(--text-muted);line-height:1.6}

/* pipeline */
.jl-pipeline{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:8px 0}
.jl-pipe-step{display:flex;flex-direction:column;align-items:center;gap:8px;flex:1;min-width:100px;text-align:center}
.jl-pipe-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px}
.jl-pipe-label{font-size:12px;font-weight:700;color:var(--text-ink)}
.jl-pipe-desc{font-size:10.5px;color:var(--text-muted);line-height:1.4}
.jl-pipe-arrow{color:var(--text-placeholder);font-size:18px;flex-shrink:0}
@media(max-width:700px){.jl-pipe-arrow{display:none}}

/* main grid */
.jl-main-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start}
@media(max-width:860px){.jl-main-grid{grid-template-columns:1fr}}

/* model info */
.jl-model-info-box{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:linear-gradient(135deg,rgba(16,183,127,0.07),rgba(0,151,184,0.04));border:1px solid rgba(16,183,127,0.22);border-radius:var(--radius-sm);margin-bottom:20px}
.jl-mi-left{display:flex;align-items:center;gap:12px}
.jl-mi-dot{width:8px;height:8px;border-radius:50%;background:var(--accent-green);box-shadow:0 0 0 3px rgba(16,183,127,0.2);animation:dot2 2s infinite;flex-shrink:0}
@keyframes dot2{0%,100%{box-shadow:0 0 0 3px rgba(16,183,127,0.2)}50%{box-shadow:0 0 0 6px rgba(16,183,127,0.08)}}
.jl-mi-icon{width:34px;height:34px;background:rgba(16,183,127,0.12);border-radius:9px;display:flex;align-items:center;justify-content:center;color:var(--accent-green);font-size:15px}
.jl-mi-versi{font-size:13px;font-weight:700;color:var(--accent-green)}
.jl-mi-sub{font-size:11px;color:var(--text-muted)}
.jl-mi-right{text-align:right}
.jl-mi-acc{font-size:20px;font-weight:800;color:var(--accent-green);font-family:var(--font-display)}
.jl-mi-acc-label{font-size:10px;color:var(--text-muted);text-transform:uppercase}

/* form */
.jl-form-body{display:flex;flex-direction:column;gap:18px}
.jl-field-group{display:flex;flex-direction:column;gap:8px}
.jl-field-label{font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;display:flex;align-items:center;gap:6px}
.jl-field-label i{color:var(--accent-cyan)}
.jl-row-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.jl-field{display:flex;flex-direction:column;gap:5px}
.mg-select-wrap{position:relative;display:flex;align-items:center}
.mg-select-wrap>i:first-child{position:absolute;left:12px;color:var(--text-placeholder);font-size:14px;pointer-events:none}
.mg-select-wrap .form-mg{padding-left:36px;padding-right:32px;appearance:none}
.mg-select-arrow{position:absolute;right:12px;color:var(--text-muted);font-size:11px;pointer-events:none}
.mg-input-icon{position:relative;display:flex;align-items:center}
.mg-input-icon>i:first-child{position:absolute;left:12px;color:var(--text-placeholder);font-size:14px;pointer-events:none}
.mg-input-icon .form-mg{padding-left:36px}

/* radio cards */
.jl-produk-mode{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.jl-radio-card{display:block;cursor:pointer}
.jl-radio-card input{display:none}
.jl-rc-body{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 10px;background:var(--card-bg-alt);border:2px solid var(--surface-border);border-radius:var(--radius-sm);transition:var(--transition);text-align:center}
.jl-rc-body i{font-size:20px;color:var(--text-placeholder)}
.jl-rc-title{font-size:12px;font-weight:600;color:var(--text-muted)}
.jl-rc-sub{font-size:10.5px;color:var(--text-placeholder)}
.jl-radio-card input:checked~.jl-rc-body{border-color:var(--mg-light);background:rgba(74,159,212,0.1)}
.jl-radio-card input:checked~.jl-rc-body i{color:var(--mg-light)}
.jl-radio-card input:checked~.jl-rc-body .jl-rc-title{color:var(--mg-light)}
.jl-radio-card:hover .jl-rc-body{border-color:rgba(74,159,212,0.4)}

/* produk list */
.jl-produk-search{display:flex;align-items:center;gap:8px;padding:7px 12px;background:var(--card-bg-alt);border:1px solid var(--surface-border);border-radius:var(--radius-sm);margin-bottom:8px}
.jl-produk-search i{color:var(--text-placeholder);font-size:13px}
.jl-produk-search input{border:none;background:transparent;color:var(--text-ink);font-size:12.5px;outline:none;width:100%}
.jl-produk-list{max-height:200px;overflow-y:auto;display:flex;flex-direction:column;gap:4px;padding-right:4px}
.jl-produk-list::-webkit-scrollbar{width:5px}
.jl-produk-list::-webkit-scrollbar-track{background:var(--card-bg-alt)}
.jl-produk-list::-webkit-scrollbar-thumb{background:var(--surface-border);border-radius:3px}
.jl-check-item{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:var(--radius-sm);cursor:pointer;transition:var(--transition);border:1px solid transparent}
.jl-check-item:hover{background:var(--card-bg-alt);border-color:var(--surface-border)}
.jl-check-item input{display:none}
.jl-check-icon{width:16px;height:16px;border:1.5px solid var(--surface-border);border-radius:4px;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:var(--transition)}
.jl-check-icon i{font-size:10px;color:#fff;display:none}
.jl-check-item input:checked~.jl-check-icon{background:var(--mg-glow);border-color:var(--mg-glow)}
.jl-check-item input:checked~.jl-check-icon i{display:block}
.jl-check-label{font-size:12px;color:var(--text-muted)}
.jl-check-item input:checked~.jl-check-label{color:var(--text-ink);font-weight:500}
.jl-check-actions{display:flex;align-items:center;gap:8px;margin-top:8px}
.btn-jl-sm{padding:4px 10px;border-radius:6px;border:1px solid var(--surface-border);background:var(--card-bg-alt);color:var(--text-muted);font-size:11.5px;cursor:pointer;transition:var(--transition)}
.btn-jl-sm:hover{border-color:rgba(74,159,212,0.4);color:var(--mg-light)}
.jl-selected-count{font-size:11.5px;color:var(--accent-cyan);margin-left:auto;font-weight:600}

/* toggle */
.jl-toggle-item{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:12px 14px;background:var(--card-bg-alt);border:1px solid var(--surface-border);border-radius:var(--radius-sm);cursor:pointer}
.jl-toggle-title{font-size:13px;font-weight:600;color:var(--text-ink)}
.jl-toggle-sub{font-size:11.5px;color:var(--text-muted);line-height:1.5;margin-top:2px}
.jl-switch{position:relative;width:36px;height:20px;flex-shrink:0;margin-top:2px}
.jl-switch input{display:none}
.jl-switch-slider{position:absolute;inset:0;background:var(--surface-border);border-radius:20px;transition:.25s}
.jl-switch-slider::before{content:'';position:absolute;width:14px;height:14px;top:3px;left:3px;background:#fff;border-radius:50%;transition:.25s}
.jl-switch input:checked+.jl-switch-slider{background:var(--mg-glow)}
.jl-switch input:checked+.jl-switch-slider::before{transform:translateX(16px)}

/* run button */
.btn-jl-run{display:flex;align-items:center;justify-content:center;gap:12px;padding:16px 24px;border-radius:var(--radius-md);border:none;cursor:pointer;background:linear-gradient(135deg,var(--mg-glow),var(--accent-cyan));color:#fff;font-family:var(--font-display);box-shadow:0 6px 24px rgba(74,159,212,0.3);transition:all .22s;position:relative;overflow:hidden;width:100%}
.btn-jl-run::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,0.12),transparent);opacity:0;transition:.22s}
.btn-jl-run:hover{transform:translateY(-2px);box-shadow:0 10px 32px rgba(74,159,212,0.4)}
.btn-jl-run:hover::before{opacity:1}
.btn-jl-run:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none}
.btn-jl-icon{font-size:20px}
.btn-jl-text{font-size:15px;font-weight:700}
.btn-jl-sub{font-size:11px;opacity:.8;font-weight:400}
.jl-info-note{display:flex;align-items:flex-start;gap:8px;padding:10px 14px;background:rgba(74,159,212,0.06);border:1px solid rgba(74,159,212,0.15);border-radius:var(--radius-sm);font-size:12px;color:var(--text-muted);line-height:1.6}
.jl-info-note i{color:var(--accent-cyan);flex-shrink:0;margin-top:1px}

/* status panel */
.jl-status-panel{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:340px;padding:32px 24px!important}
.jl-state{display:flex;flex-direction:column;align-items:center;gap:12px;width:100%;text-align:center;animation:fadeSlIn .3s ease}
@keyframes fadeSlIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.jl-state-icon{width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:30px}
.idle-ic{background:rgba(74,159,212,0.1);color:var(--mg-light)}
.success-ic{background:rgba(16,183,127,0.12);color:var(--accent-green)}
.error-ic{background:rgba(229,62,62,0.12);color:var(--accent-red)}
.running-ic{background:transparent}
.jl-state-title{font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--text-ink)}
.jl-state-sub{font-size:13px;color:var(--text-muted);line-height:1.6;max-width:260px}

/* last run */
.jl-last-run{margin-top:6px;padding:12px 18px;background:var(--card-bg-alt);border:1px solid var(--surface-border);border-radius:var(--radius-sm);text-align:center;width:100%}
.jl-lr-label{font-size:10.5px;color:var(--text-placeholder);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px}
.jl-lr-time{font-size:13px;font-weight:600;color:var(--text-ink)}
.jl-lr-count{font-size:12px;color:var(--text-muted);margin-top:3px;display:flex;align-items:center;justify-content:center;gap:5px}
.jl-lr-count i{color:var(--accent-cyan)}

/* spinner */
.jl-spinner{position:relative;width:72px;height:72px;display:flex;align-items:center;justify-content:center}
.jl-spin-ring{position:absolute;inset:0;border-radius:50%;border:2.5px solid transparent;border-top-color:var(--accent-cyan);border-right-color:rgba(74,159,212,0.3);animation:spinRf 1.1s linear infinite}
.jl-spin-r2{inset:10px;border-top-color:var(--accent-green);animation-duration:.8s;animation-direction:reverse}
@keyframes spinRf{to{transform:rotate(360deg)}}
.jl-spin-core{font-size:22px;color:var(--mg-light);position:relative;z-index:1;animation:pulseC 1.4s ease-in-out infinite}
@keyframes pulseC{0%,100%{opacity:1}50%{opacity:.45}}

/* progress */
.jl-progress-wrap{width:100%;display:flex;align-items:center;gap:10px;margin-top:4px}
.jl-progress-bar{flex:1;height:8px;background:var(--card-bg-alt);border:1px solid var(--surface-border);border-radius:999px;overflow:hidden;position:relative}
.jl-progress-fill{height:100%;width:0%;background:linear-gradient(90deg,var(--mg-glow),var(--accent-cyan));border-radius:999px;transition:width .6s ease}
.jl-progress-shimmer{position:absolute;inset:0;background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,0.2) 50%,transparent 100%);animation:shimJl 1.5s infinite}
@keyframes shimJl{0%{transform:translateX(-100%)}100%{transform:translateX(300%)}}
.jl-progress-pct{font-size:12px;font-weight:700;color:var(--accent-cyan);min-width:32px;text-align:right}

/* steps */
.jl-steps{display:flex;flex-direction:column;gap:5px;width:100%;text-align:left;margin-top:4px}
.jl-step{display:flex;align-items:center;gap:9px;font-size:12px;color:var(--text-placeholder);padding:5px 10px;border-radius:6px;transition:all .3s}
.jl-step i{font-size:8px;flex-shrink:0}
.jl-step.step-done{color:var(--accent-green);background:rgba(16,183,127,0.06)}
.jl-step.step-done i{font-size:11px;color:var(--accent-green)}
.jl-step.step-active{color:var(--accent-cyan);background:rgba(74,159,212,0.07);font-weight:600}
.jl-step.step-active i{color:var(--accent-cyan);animation:pulseC 1s infinite}
.jl-elapsed{font-size:11px;color:var(--text-placeholder);margin-top:4px}

/* result stats */
.jl-result-stats{display:flex;align-items:center;background:var(--card-bg-alt);border:1px solid var(--surface-border);border-radius:var(--radius-sm);overflow:hidden;width:100%;margin:4px 0}
.jl-rs-item{flex:1;display:flex;flex-direction:column;align-items:center;padding:12px 8px;gap:4px}
.jl-rs-val{font-family:var(--font-display);font-size:20px;font-weight:800;color:var(--text-ink)}
.jl-rs-label{font-size:10.5px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em}
.jl-rs-sep{width:1px;height:40px;background:var(--surface-border)}
.jl-success-actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-top:4px}

/* error */
.jl-err-detail{width:100%;background:rgba(229,62,62,0.06);border:1px solid rgba(229,62,62,0.2);border-radius:var(--radius-sm);padding:10px 14px;font-size:12px;color:var(--accent-red);font-family:'Courier New',monospace;text-align:left;max-height:100px;overflow-y:auto;line-height:1.6}

/* card header */
.mg-card-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px}
.mg-card-title-group{display:flex;align-items:center;gap:10px}
.form-label-mg{font-size:11.5px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em}
</style>

<!-- ══════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const $ = id => document.getElementById(id);

    // ── Produk mode toggle ──────────────────────────────────────────────
    document.querySelectorAll('input[name="produk_mode"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const wrap = $('produkCheckboxWrap');
            if (wrap) wrap.style.display = this.value === 'pilih' ? '' : 'none';
        });
    });

    // ── Produk search ───────────────────────────────────────────────────
    $('searchProdukJl')?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.jl-check-item').forEach(item => {
            item.style.display = item.querySelector('.jl-check-label')
                ?.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // ── Select/deselect all ─────────────────────────────────────────────
    function updateCount() {
        const n = document.querySelectorAll('.produk-checkbox:checked').length;
        const el = $('selectedCount');
        if (el) el.textContent = n + ' dipilih';
    }
    document.querySelectorAll('.produk-checkbox').forEach(cb => cb.addEventListener('change', updateCount));
    $('btnPilihSemua')?.addEventListener('click', () => {
        document.querySelectorAll('.produk-checkbox').forEach(cb => cb.checked = true);
        updateCount();
    });
    $('btnHapusPilihan')?.addEventListener('click', () => {
        document.querySelectorAll('.produk-checkbox').forEach(cb => cb.checked = false);
        updateCount();
    });

    // ── State management ────────────────────────────────────────────────
    let elTimer = null;
    let startTs = null;
    let state   = 'idle';

    const STEPS = [
        {id:'jlStep1', at:1,  pct:20, msg:'Membaca data produk dari database…'},
        {id:'jlStep2', at:3,  pct:45, msg:'Menyiapkan matriks fitur input…'},
        {id:'jlStep3', at:5,  pct:70, msg:'Menjalankan inferensi Random Forest…'},
        {id:'jlStep4', at:8,  pct:92, msg:'Menyimpan hasil prediksi ke database…'},
    ];

    function showState(name) {
        ['jlIdle','jlRunning','jlSuccess','jlError'].forEach(id => {
            const el = $(id);
            if (el) el.style.display = id === 'jl' + name.charAt(0).toUpperCase() + name.slice(1) ? '' : 'none';
        });
        state = name;
    }

    function setProgress(pct, msg) {
        const fill = $('jlProgressFill');
        const pctEl = $('jlProgressPct');
        const msgEl = $('jlRunMsg');
        if (fill)  fill.style.width = pct + '%';
        if (pctEl) pctEl.textContent = Math.round(pct) + '%';
        if (msgEl && msg) msgEl.textContent = msg;
    }

    function activateStep(sid) {
        STEPS.forEach(s => {
            const el = $(s.id);
            if (!el) return;
            if (s.id === sid) {
                el.className = 'jl-step step-active';
                el.innerHTML = '<i class="bi bi-arrow-right-circle-fill"></i> ' + el.textContent.trim();
            } else if (el.classList.contains('step-active') || el.classList.contains('step-done')) {
                el.className = 'jl-step step-done';
                el.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + el.textContent.trim();
            }
        });
    }

    function startElTimer() {
        startTs = Date.now();
        elTimer = setInterval(() => {
            const sec = Math.floor((Date.now() - startTs) / 1000);
            const el = $('jlElapsed');
            if (el) el.textContent = sec;
            STEPS.forEach(s => {
                const stepEl = $(s.id);
                if (!stepEl) return;
                if (sec >= s.at && !stepEl.classList.contains('step-done') && !stepEl.classList.contains('step-active')) {
                    activateStep(s.id);
                    setProgress(s.pct, s.msg);
                }
            });
        }, 1000);
    }

    function stopElTimer() { clearInterval(elTimer); elTimer = null; }

    // ── Jalankan ────────────────────────────────────────────────────────
    $('btnJalankan')?.addEventListener('click', function () {
        if (state === 'running') return;

        const mode  = document.querySelector('input[name="produk_mode"]:checked')?.value || 'semua';
        const bulan = $('bulanPrediksi')?.value;
        const tahun = $('tahunPrediksi')?.value;
        const overwrite = $('overwriteMode')?.checked ? 1 : 0;

        const produkList = mode === 'pilih'
            ? Array.from(document.querySelectorAll('.produk-checkbox:checked')).map(cb => cb.value)
            : [];

        if (mode === 'pilih' && produkList.length === 0) {
            Swal.fire({ icon:'warning', title:'Pilih Produk', text:'Pilih minimal satu produk.', confirmButtonColor:'#2e6da4' });
            return;
        }

        const bulanNama = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        Swal.fire({
            title: 'Jalankan Prediksi?',
            html: `Prediksi untuk <b>${mode === 'semua' ? 'semua produk' : produkList.length + ' produk'}</b><br>Periode: <b>${bulanNama[parseInt(bulan)]} ${tahun}</b>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2e6da4',
            cancelButtonColor: '#243B55',
            confirmButtonText: '<i class="bi bi-play-fill"></i> Ya, Jalankan!',
            cancelButtonText: 'Batal',
        }).then(r => {
            if (!r.isConfirmed) return;

            showState('running');
            this.disabled = true;
            STEPS.forEach(s => { const el = $(s.id); if (el) el.className = 'jl-step'; });
            setProgress(5, STEPS[0].msg);
            activateStep('jlStep1');
            startElTimer();

            const fd = new FormData();
            fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            fd.append('bulan_prediksi', bulan);
            fd.append('tahun_prediksi', tahun);
            fd.append('produk_mode', mode);
            fd.append('overwrite', overwrite);
            if (mode === 'pilih') produkList.forEach(p => fd.append('produk_list[]', p));

            fetch(BASE_URL + 'prediksi/jalankan', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd,
            })
            .then(res => res.json())
            .then(data => {
                stopElTimer();
                setProgress(100);
                STEPS.forEach(s => {
                    const el = $(s.id);
                    if (el) {
                        el.className = 'jl-step step-done';
                        el.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + el.textContent.trim();
                    }
                });

                if (data.status === 'error') {
                    showState('error');
                    const em = $('jlErrMsg');
                    if (em) em.textContent = data.message;
                    const ed = $('jlErrDetail');
                    if (ed && data.detail) { ed.style.display=''; ed.textContent = data.detail; }
                } else {
                    showState('success');
                    const durasi = Math.floor((Date.now() - startTs) / 1000);
                    const sm = $('jlSuccessMsg');
                    if (sm) sm.textContent = data.message || 'Prediksi berhasil disimpan.';
                    if ($('rsTotal'))   $('rsTotal').textContent   = data.total ?? '—';
                    if ($('rsPeriode')) $('rsPeriode').textContent = (bulanNama[parseInt(bulan)] ?? bulan) + ' ' + tahun;
                    if ($('rsDurasi'))  $('rsDurasi').textContent  = durasi + 's';
                }
                $('btnJalankan').disabled = false;
            })
            .catch(() => {
                stopElTimer();
                showState('error');
                const em = $('jlErrMsg');
                if (em) em.textContent = 'Gagal menghubungi server.';
                $('btnJalankan').disabled = false;
            });
        });
    });

    $('btnJlReset')?.addEventListener('click', () => { showState('idle'); });
    $('btnJlRetry')?.addEventListener('click', () => { showState('idle'); });
});
</script>