<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · Mi Store</title>
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/906/906334.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        /* ══════════════════════════════════════════════════════
           TOKENS
        ══════════════════════════════════════════════════════ */
        :root {
            --bg-deep:   #0D1520;
            --bg-base:   #141E30;
            --bg-mid:    #1A2840;
            --bg-card:   #1E3050;
            --blue-1:    #243B55;
            --blue-2:    #2E4E72;
            --accent:    #38BDF8;
            --accent-2:  #818CF8;
            --accent-3:  #34D399;
            --danger:    #F87171;
            --glow:      rgba(56,189,248,0.18);
            --text-h:    #F0F6FF;
            --text-b:    #A8BEDB;
            --text-m:    #6A8CAF;
            --border:    rgba(56,189,248,0.12);
            --border-2:  rgba(56,189,248,0.24);
            --radius:    14px;
            --radius-sm: 8px;
            --font-head: 'Sora', sans-serif;
            --font-mono: 'Space Mono', monospace;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: var(--font-head);
            background: var(--bg-base);
            color: var(--text-b);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 15% 5%, rgba(56,189,248,0.06) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 85% 90%, rgba(129,140,248,0.06) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        canvas#grid-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            opacity: 0.35;
            pointer-events: none;
        }

        /* ══════════════════════════════════════════════════════
           NAVBAR
        ══════════════════════════════════════════════════════ */
        .sp2s-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            height: 64px;
            background: rgba(13,21,32,0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .nav-logo {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 18px rgba(56,189,248,0.35);
        }

        .nav-logo i { color: #fff; font-size: 18px; }

        .nav-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .nav-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-h);
        }

        .nav-sub {
            font-family: var(--font-mono);
            font-size: 10px;
            color: var(--text-m);
            letter-spacing: 0.08em;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-tag {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--text-m);
            background: rgba(56,189,248,0.07);
            border: 1px solid var(--border);
            padding: 3px 10px;
            border-radius: 100px;
        }

        .btn-back {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border: 1px solid var(--border-2);
            background: transparent;
            color: var(--text-b);
            font-size: 13px;
            font-weight: 500;
            border-radius: var(--radius-sm);
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: rgba(56,189,248,0.07);
            border-color: var(--accent);
            color: var(--accent);
        }

        /* ══════════════════════════════════════════════════════
           MAIN LAYOUT
        ══════════════════════════════════════════════════════ */
        main {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 80px 40px 60px;
        }

        .login-section {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
        }

        .sec-head {
            text-align: center;
            margin-bottom: 52px;
            animation: fadeSlideUp 0.5s ease both;
        }

        .sec-eye {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--accent);
            letter-spacing: 0.14em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .sec-h2 {
            font-size: 1.9rem;
            font-weight: 800;
            color: var(--text-h);
            margin-bottom: 10px;
        }

        .sec-p {
            font-size: 14px;
            color: var(--text-m);
            max-width: 400px;
            margin: 0 auto;
            line-height: 1.65;
        }

        /* ── Two-column ── */
        .tech-login {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: start;
            animation: fadeSlideUp 0.55s 0.1s ease both;
        }

        /* ══════════════════════════════════════════════════════
           LEFT: SPEC PANEL
        ══════════════════════════════════════════════════════ */
        .spec-head {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--accent);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .spec-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(56,189,248,0.06);
        }

        .spec-ico {
            width: 36px; height: 36px;
            background: rgba(56,189,248,0.08);
            border: 1px solid var(--border);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            font-size: 15px;
            flex-shrink: 0;
        }

        .spec-lbl {
            font-size: 11px;
            color: var(--text-m);
            font-family: var(--font-mono);
            letter-spacing: 0.06em;
            margin-bottom: 2px;
        }

        .spec-val {
            font-size: 13px;
            color: var(--text-h);
            font-weight: 500;
        }

        /* Performance badges */
        .perf-badges {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .pbadge {
            display: flex;
            flex-direction: column;
            gap: 4px;
            background: rgba(20,30,48,0.7);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 12px 14px;
        }

        .pb-l {
            font-family: var(--font-mono);
            font-size: 10px;
            color: var(--text-m);
            letter-spacing: 0.08em;
        }

        .pb-v {
            font-size: 15px;
            font-weight: 700;
        }

        .pb-v.good  { color: var(--accent-3); }
        .pb-v.info  { color: var(--accent); }
        .pb-v.warn  { color: #FBBF24; }
        .pb-v.danger{ color: var(--danger); }

        /* ══════════════════════════════════════════════════════
           RIGHT: LOGIN CARD
        ══════════════════════════════════════════════════════ */
        .login-card {
            background: rgba(26,40,64,0.7);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px 32px;
            backdrop-filter: blur(14px);
            box-shadow: 0 24px 80px rgba(0,0,0,0.35), 0 0 0 1px rgba(56,189,248,0.04);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--accent), var(--accent-2));
        }

        .lc-logo {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: #fff;
            margin: 0 auto 18px;
            box-shadow: 0 8px 28px rgba(56,189,248,0.35);
        }

        .lc-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text-h);
            text-align: center;
            margin-bottom: 6px;
        }

        .lc-sub {
            font-size: 12px;
            color: var(--text-m);
            text-align: center;
            margin-bottom: 28px;
            line-height: 1.6;
        }

        /* ── Form ── */
        .f-block {
            margin-bottom: 18px;
        }

        .f-lbl {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-b);
            margin-bottom: 7px;
            letter-spacing: 0.03em;
        }

        .f-wrap {
            position: relative;
        }

        .f-ico {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-m);
            font-size: 16px;
            pointer-events: none;
        }

        .f-inp {
            width: 100%;
            background: rgba(13,21,32,0.8);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 12px 14px 12px 40px;
            font-family: var(--font-head);
            font-size: 14px;
            color: var(--text-h);
            outline: none;
            transition: all 0.2s;
        }

        .f-inp::placeholder { color: var(--text-m); }

        .f-inp:focus {
            border-color: var(--accent);
            background: rgba(20,30,48,0.9);
            box-shadow: 0 0 0 3px rgba(56,189,248,0.1);
        }

        .f-inp.err {
            border-color: var(--danger);
            box-shadow: 0 0 0 3px rgba(248,113,113,0.12);
        }

        /* Toggle password */
        .f-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--text-m);
            font-size: 16px;
            cursor: pointer;
            padding: 4px;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .f-toggle:hover { color: var(--accent); }

        /* Error message */
        .f-err {
            display: none;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            font-size: 12px;
            color: var(--danger);
        }

        .f-err.show { display: flex; }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            border: none;
            border-radius: var(--radius-sm);
            color: #fff;
            font-family: var(--font-head);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 18px rgba(56,189,248,0.3);
            position: relative;
            overflow: hidden;
            margin-top: 8px;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 28px rgba(56,189,248,0.45);
        }

        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Spinner */
        .btn-spinner {
            display: none;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            flex-shrink: 0;
        }

        .btn-login.loading .btn-spinner { display: block; }
        .btn-login.loading .btn-lbl    { opacity: 0.6; }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Divider */
        .lc-divider {
            text-align: center;
            margin: 22px 0 14px;
            font-family: var(--font-mono);
            font-size: 10px;
            color: var(--text-m);
            letter-spacing: 0.08em;
            position: relative;
        }

        .lc-divider::before,
        .lc-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 28%;
            height: 1px;
            background: var(--border);
        }

        .lc-divider::before { left: 0; }
        .lc-divider::after  { right: 0; }

        .lc-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            color: var(--text-m);
            text-decoration: none;
            transition: color 0.2s;
        }

        .lc-back:hover { color: var(--accent); }

        /* ══════════════════════════════════════════════════════
           FOOTER
        ══════════════════════════════════════════════════════ */
        .sp2s-footer {
            position: relative;
            z-index: 1;
            background: rgba(13,21,32,0.9);
            border-top: 1px solid var(--border);
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            color: var(--text-m);
            font-family: var(--font-mono);
        }

        /* ══════════════════════════════════════════════════════
           ANIMATION
        ══════════════════════════════════════════════════════ */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ══════════════════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════════════════ */
        @media (max-width: 860px) {
            .sp2s-nav { padding: 0 20px; }
            main { padding: 80px 20px 60px; }
            .tech-login { grid-template-columns: 1fr; }
            .perf-badges { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<canvas id="grid-bg"></canvas>

<!-- NAVBAR -->
<nav class="sp2s-nav">
    <a href="<?= site_url('/') ?>" class="nav-brand">
        <div class="nav-logo"><i class="bi bi-phone-fill"></i></div>
        <div class="nav-brand-text">
            <span class="nav-title"><b>Mi Store</b> · Prediksi Penjualan Smartphone</span>
            <span class="nav-sub">RANDOM FOREST REGRESSOR · <?= date('Y') ?></span>
        </div>
    </a>
    <div class="nav-right">
        <span class="nav-tag">v1.0 · ML-Powered</span>
        <a href="<?= site_url('/') ?>" class="btn-back">
            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </a>
    </div>
</nav>

<!-- MAIN -->
<main>
    <div class="login-section">

        <!-- Header -->
        <div class="sec-head">
            <div class="sec-eye">Autentikasi & Akses</div>
            <h2 class="sec-h2">Masuk ke Sistem Mi Store</h2>
            <p class="sec-p">Gunakan akun yang telah diberikan administrator untuk mengakses platform prediksi penjualan smartphone.</p>
        </div>

        <!-- Two-column -->
        <div class="tech-login">

            <!-- Kiri: Tech specs + performa -->
            <div>
                <div class="spec-head">Stack Teknologi</div>
                <div>
                    <?php foreach ($specs as [$ico, $lbl, $val]): ?>
                        <div class="spec-item">
                            <div class="spec-ico"><i class="bi <?= esc($ico) ?>"></i></div>
                            <div>
                                <div class="spec-lbl"><?= esc($lbl) ?></div>
                                <div class="spec-val"><?= esc($val) ?></div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>

                <div class="spec-head" style="margin-top:26px;">Performa Model</div>
                <div class="perf-badges">
                    <?php if (! empty($modelAktif) && ! empty($modelAktif['r2'])): ?>
                        <div class="pbadge">
                            <span class="pb-l">MODEL AKTIF</span>
                            <span class="pb-v info"><?= esc($modelAktif['nama_model']) ?></span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">ALGORITMA</span>
                            <span class="pb-v info"><?= esc($modelConfig['algoritma']) ?></span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">R² SCORE</span>
                            <span class="pb-v good"><?= number_format((float)$modelAktif['r2'], 3) ?></span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">AKURASI</span>
                            <span class="pb-v good">
                                <?= ! empty($modelAktif['akurasi']) ? number_format((float)$modelAktif['akurasi'], 2) . '%' : '–' ?>
                            </span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">RMSE</span>
                            <span class="pb-v info"><?= ! empty($modelAktif['rmse']) ? number_format((float)$modelAktif['rmse'], 4) : '–' ?></span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">MAE</span>
                            <span class="pb-v info"><?= ! empty($modelAktif['mae']) ? number_format((float)$modelAktif['mae'], 4) : '–' ?></span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">MAPE</span>
                            <span class="pb-v <?= ! empty($modelAktif['mape']) && (float)$modelAktif['mape'] < 10 ? 'good' : 'warn' ?>">
                                <?= ! empty($modelAktif['mape']) ? number_format((float)$modelAktif['mape'], 2) . '%' : '–' ?>
                            </span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">N_ESTIMATORS</span>
                            <span class="pb-v warn"><?= esc($modelConfig['n_estimators']) ?></span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">MAX_DEPTH</span>
                            <span class="pb-v info"><?= esc($modelConfig['max_depth']) ?></span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">CROSS-VAL</span>
                            <span class="pb-v info"><?= esc($modelConfig['cross_val']) ?></span>
                        </div>
                        <?php if (! empty($modelAktif['selesai_at'])): ?>
                        <div class="pbadge">
                            <span class="pb-l">DILATIH</span>
                            <span class="pb-v info" style="font-size:12px;">
                                <?= date('d M Y', strtotime($modelAktif['selesai_at'])) ?>
                            </span>
                        </div>
                        <?php endif ?>
                    <?php else: ?>
                        <div class="pbadge">
                            <span class="pb-l">ALGORITMA</span>
                            <span class="pb-v info"><?= esc($modelConfig['algoritma']) ?></span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">R² SCORE</span>
                            <span class="pb-v <?= $modelConfig['r2_range'] === 'Belum ada model' ? 'danger' : 'good' ?>">
                                <?= esc($modelConfig['r2_range']) ?>
                            </span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">AKURASI</span>
                            <span class="pb-v good"><?= esc($modelConfig['akurasi_range']) ?></span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">CROSS-VAL</span>
                            <span class="pb-v info"><?= esc($modelConfig['cross_val']) ?></span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">N_ESTIMATORS</span>
                            <span class="pb-v warn"><?= esc($modelConfig['n_estimators']) ?></span>
                        </div>
                        <div class="pbadge">
                            <span class="pb-l">MAX_DEPTH</span>
                            <span class="pb-v info"><?= esc($modelConfig['max_depth']) ?></span>
                        </div>
                        <div class="pbadge" style="grid-column: span 2;">
                            <span class="pb-l">STATUS</span>
                            <span class="pb-v danger" style="font-size:12px;">Belum ada model aktif</span>
                        </div>
                    <?php endif ?>
                </div>
            </div>

            <!-- Kanan: Login form -->
            <div class="login-card" id="form-anchor">
                <div class="lc-logo"><i class="bi bi-phone-fill"></i></div>
                <div class="lc-title">Selamat Datang</div>
                <p class="lc-sub">Masuk ke Mi Store untuk mulai menganalisis dan memprediksi penjualan smartphone.</p>

                <form id="formLogin" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="f-block">
                        <label class="f-lbl" for="login">Username</label>
                        <div class="f-wrap">
                            <i class="bi bi-person f-ico"></i>
                            <input type="text"
                                   name="login"
                                   id="login"
                                   class="f-inp"
                                   placeholder="Masukkan username"
                                   autocomplete="username"
                                   spellcheck="false">
                        </div>
                        <div class="f-err" id="err-login">
                            <i class="bi bi-exclamation-circle-fill"></i><span></span>
                        </div>
                    </div>

                    <div class="f-block">
                        <label class="f-lbl" for="password">Password</label>
                        <div class="f-wrap">
                            <i class="bi bi-lock f-ico"></i>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="f-inp password-field"
                                   placeholder="Masukkan password"
                                   autocomplete="current-password"
                                   style="padding-right:42px;">
                            <button type="button" class="f-toggle toggle-password" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="f-err" id="err-password">
                            <i class="bi bi-exclamation-circle-fill"></i><span></span>
                        </div>
                    </div>

                    <button type="submit" class="btn-login" id="btnLogin">
                        <div class="btn-spinner"></div>
                        <span class="btn-lbl">
                            <i class="bi bi-box-arrow-in-right"></i>&nbsp; Masuk ke Sistem
                        </span>
                    </button>

                    <div class="lc-divider">Sistem Resmi · Platform Prediksi Penjualan</div>
                    <a href="<?= site_url('/') ?>" class="lc-back">
                        <i class="bi bi-arrow-left"></i> Kembali ke Beranda
                    </a>
                </form>
            </div>

        </div>
    </div>
</main>

<!-- FOOTER -->
<footer class="sp2s-footer">
    <span><b>Mi Store</b> · Sistem Prediksi Penjualan Smartphone · Random Forest Regressor</span>
    <span>CodeIgniter 4 + scikit-learn · <?= date('Y') ?></span>
</footer>

<script>
/* ── Grid canvas ─────────────────────────────────────────── */
(function () {
    const cvs = document.getElementById('grid-bg');
    const ctx = cvs.getContext('2d');

    function draw() {
        cvs.width  = window.innerWidth;
        cvs.height = window.innerHeight;
        ctx.clearRect(0, 0, cvs.width, cvs.height);
        const size = 40;
        ctx.strokeStyle = 'rgba(56,189,248,0.07)';
        ctx.lineWidth   = 0.5;
        for (let x = 0; x <= cvs.width; x += size) {
            ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, cvs.height); ctx.stroke();
        }
        for (let y = 0; y <= cvs.height; y += size) {
            ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(cvs.width, y); ctx.stroke();
        }
        for (let x = 0; x <= cvs.width; x += size) {
            for (let y = 0; y <= cvs.height; y += size) {
                if (Math.random() > 0.97) {
                    ctx.beginPath();
                    ctx.arc(x, y, 1.5, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(56,189,248,${(0.2 + Math.random() * 0.5).toFixed(2)})`;
                    ctx.fill();
                }
            }
        }
    }
    draw();
    window.addEventListener('resize', draw);
})();

/* ── Toggle password ─────────────────────────────────────── */
$(document).on('click', '.toggle-password', function () {
    const inp  = $(this).closest('.f-wrap').find('.password-field');
    const icon = $(this).find('i');
    if (inp.attr('type') === 'password') {
        inp.attr('type', 'text');
        icon.removeClass('bi-eye').addClass('bi-eye-slash');
    } else {
        inp.attr('type', 'password');
        icon.removeClass('bi-eye-slash').addClass('bi-eye');
    }
});

/* ── Clear error on focus ────────────────────────────────── */
$('#login, #password').on('focus', function () {
    $(this).removeClass('err');
    $('#err-' + $(this).attr('id')).removeClass('show').find('span').text('');
});

/* ── Login submit via AJAX ───────────────────────────────── */
$('#formLogin').on('submit', function (e) {
    e.preventDefault();

    $('#login, #password').removeClass('err');
    $('#err-login, #err-password').removeClass('show').find('span').text('');

    const btn = $('#btnLogin').addClass('loading').prop('disabled', true);

    $.ajax({
        url       : '<?= site_url('login/attempt') ?>',
        method    : 'POST',
        data      : $(this).serialize(),
        dataType  : 'json',

        success: function (res) {
            if (res.status === 'error_validation') {
                btn.removeClass('loading').prop('disabled', false);
                if (res.errors.login) {
                    $('#login').addClass('err');
                    $('#err-login').addClass('show').find('span').text(res.errors.login);
                }
                if (res.errors.password) {
                    $('#password').addClass('err');
                    $('#err-password').addClass('show').find('span').text(res.errors.password);
                }
                return;
            }

            if (res.status === 'error') {
                btn.removeClass('loading').prop('disabled', false);
                Swal.fire({
                    icon              : 'error',
                    title             : 'Login Gagal',
                    text              : res.message,
                    background        : '#0D1520',
                    color             : '#F0F6FF',
                    confirmButtonColor: '#38BDF8',
                    iconColor         : '#F87171',
                    confirmButtonText : 'Coba Lagi'
                });
                return;
            }

            // success
            Swal.fire({
                icon            : 'success',
                title           : 'Berhasil!',
                text            : 'Login sukses. Mengalihkan ke dashboard...',
                background      : '#0D1520',
                color           : '#F0F6FF',
                iconColor       : '#34D399',
                timer           : 1400,
                showConfirmButton: false,
                timerProgressBar: true
            }).then(() => {
                window.location.href = '<?= site_url('dashboard') ?>';
            });
        },

        error: function () {
            btn.removeClass('loading').prop('disabled', false);
            Swal.fire({
                icon              : 'error',
                title             : 'Koneksi Gagal',
                text              : 'Tidak dapat terhubung ke server. Coba lagi.',
                background        : '#0D1520',
                color             : '#F0F6FF',
                confirmButtonColor: '#38BDF8'
            });
        }
    });
});
</script>
</body>
</html>