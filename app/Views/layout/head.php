<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Prediksi Penjualan Smartphone - Mi Store Kudus">
    <meta name="author" content="Mi Store Kudus">
    <title><?= $title ?? 'Dashboard | Mi Store Kudus' ?></title>

    <!-- Favicon -->
    <!-- <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png') ?>"> -->
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/906/906334.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const BASE_URL = '<?= base_url() ?>';
    </script>

    <style>
        /* ===== CSS VARIABLES ===== */
        :root {
            /* ── Dark side (sidebar, header) ── */
            --sidebar-bg: #0e1724;
            --sidebar-border: rgba(36, 59, 85, 0.7);
            --header-bg: rgba(20, 30, 48, 0.96);

            /* ── Light content area ── */
            --page-bg: #eef3f9;
            --card-bg: #ffffff;
            --card-bg-alt: #f4f8fc;
            --surface-border: #dce8f2;
            --surface-hover: #e8f1fa;

            /* ── Brand palette (Midnight Glow) ── */
            --mg-deep: #141E30;
            --mg-mid: #243B55;
            --mg-glow: #2e6da4;
            --mg-light: #4a9fd4;

            /* ── Accent ── */
            --accent-cyan: #0097b8;
            --accent-teal: #00a896;
            --accent-orange: #f97316;
            --accent-green: #10b77f;
            --accent-red: #e53e3e;
            --accent-yellow: #d4a017;

            /* ── Text in content area ── */
            --text-ink: #0d1a2a;
            --text-body: #2d4a65;
            --text-muted: #7a96b0;
            --text-placeholder: #a8bece;

            /* ── Text in dark surfaces ── */
            --text-on-dark: #e8f1f8;
            --text-on-dark-2: #8ba5c0;
            --text-on-dark-3: #4a6480;

            /* ── Glow effects ── */
            --glow-cyan: rgba(0, 151, 184, 0.15);
            --glow-blue: rgba(46, 109, 164, 0.2);

            --shadow-sm: 0 1px 4px rgba(20, 30, 48, 0.08), 0 2px 12px rgba(20, 30, 48, 0.06);
            --shadow-md: 0 4px 20px rgba(20, 30, 48, 0.10), 0 1px 4px rgba(20, 30, 48, 0.06);
            --shadow-card: 0 2px 16px rgba(36, 59, 85, 0.08), 0 1px 3px rgba(36, 59, 85, 0.05);
            --shadow-hover: 0 8px 32px rgba(20, 30, 48, 0.14), 0 2px 6px rgba(20, 30, 48, 0.08);

            --sidebar-width: 260px;
            --header-height: 62px;
            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 22px;

            --font-display: 'Poppins', sans-serif;
            --font-body: 'Inter', sans-serif;
            --transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== RESET ===== */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--page-bg);
            color: var(--text-body);
            font-size: 14px;
            line-height: 1.6;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* ===== LAYOUT ===== */
        #app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        #main-content {
            margin-left: var(--sidebar-width);
            padding-top: var(--header-height);
            flex: 1;
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--page-bg);
        }

        #main-content.sidebar-collapsed {
            margin-left: 70px;
        }

        .content-wrapper {
            padding: 28px 32px;
            min-height: calc(100vh - var(--header-height) - 52px);
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: var(--page-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--surface-border);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--mg-light);
        }

        /* ===== CARD ===== */
        .card-mg {
            background: var(--card-bg);
            border: 1px solid var(--surface-border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            padding: 24px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .card-mg:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
            border-color: #b8d4ea;
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--surface-border);
            border-radius: var(--radius-lg);
            padding: 22px 24px;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            box-shadow: var(--shadow-card);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            border-color: #b8d4ea;
        }

        .stat-card .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 14px;
        }

        .stat-card .stat-value {
            font-family: var(--font-display);
            font-size: 26px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 5px;
            color: var(--text-ink);
        }

        .stat-card .stat-label {
            color: var(--text-muted);
            font-size: 11.5px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-weight: 500;
        }

        .stat-card .stat-change {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .stat-card .stat-change.up {
            background: rgba(16, 183, 127, 0.1);
            color: var(--accent-green);
        }

        .stat-card .stat-change.down {
            background: rgba(229, 62, 62, 0.1);
            color: var(--accent-red);
        }

        /* strip accent top border */
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }

        .stat-card.accent-cyan::after {
            background: linear-gradient(90deg, var(--accent-cyan), #00c8e8);
        }

        .stat-card.accent-green::after {
            background: linear-gradient(90deg, var(--accent-green), #34d399);
        }

        .stat-card.accent-orange::after {
            background: linear-gradient(90deg, var(--accent-orange), #fb923c);
        }

        .stat-card.accent-blue::after {
            background: linear-gradient(90deg, var(--mg-glow), var(--mg-light));
        }

        /* ===== TYPOGRAPHY ===== */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-display);
            font-weight: 600;
            color: var(--text-ink);
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-ink);
            letter-spacing: -0.02em;
            margin-bottom: 2px;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 13px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-ink);
            margin-bottom: 0;
        }

        /* ===== BADGE ===== */
        .badge-mg {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.03em;
        }

        .badge-cyan {
            background: rgba(0, 151, 184, 0.1);
            color: var(--accent-cyan);
            border: 1px solid rgba(0, 151, 184, 0.2);
        }

        .badge-green {
            background: rgba(16, 183, 127, 0.1);
            color: var(--accent-green);
            border: 1px solid rgba(16, 183, 127, 0.2);
        }

        .badge-orange {
            background: rgba(249, 115, 22, 0.1);
            color: var(--accent-orange);
            border: 1px solid rgba(249, 115, 22, 0.2);
        }

        .badge-red {
            background: rgba(229, 62, 62, 0.1);
            color: var(--accent-red);
            border: 1px solid rgba(229, 62, 62, 0.2);
        }

        .badge-blue {
            background: rgba(46, 109, 164, 0.1);
            color: var(--mg-glow);
            border: 1px solid rgba(46, 109, 164, 0.2);
        }

        /* ===== TABLE ===== */
        .table-mg {
            width: 100%;
            border-collapse: collapse;
        }

        .table-mg th {
            padding: 11px 16px;
            font-family: var(--font-display);
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid var(--surface-border);
            font-weight: 600;
            background: var(--card-bg-alt);
        }

        .table-mg td {
            padding: 13px 16px;
            border-bottom: 1px solid var(--surface-border);
            color: var(--text-body);
            transition: var(--transition);
        }

        .table-mg tbody tr:hover td {
            background: var(--surface-hover);
            color: var(--text-ink);
        }

        .table-mg tr:last-child td {
            border-bottom: none;
        }

        /* ===== BUTTONS ===== */
        .btn-mg {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 20px;
            border-radius: var(--radius-sm);
            font-family: var(--font-body);
            font-size: 13px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-primary-mg {
            background: linear-gradient(135deg, var(--mg-glow), var(--mg-mid));
            color: #fff;
        }

        .btn-primary-mg:hover {
            background: linear-gradient(135deg, var(--mg-light), var(--mg-glow));
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(46, 109, 164, 0.3);
            color: #fff;
        }

        .btn-outline-mg {
            background: transparent;
            color: var(--mg-glow);
            border: 1px solid rgba(46, 109, 164, 0.4);
        }

        .btn-outline-mg:hover {
            background: rgba(46, 109, 164, 0.06);
            border-color: var(--mg-glow);
        }

        /* ===== FORM ===== */
        .form-mg {
            background: var(--card-bg);
            border: 1px solid var(--surface-border);
            border-radius: var(--radius-sm);
            padding: 9px 14px;
            color: var(--text-ink);
            font-family: var(--font-body);
            font-size: 13px;
            width: 100%;
            transition: var(--transition);
            outline: none;
        }

        .form-mg:focus {
            border-color: var(--mg-light);
            box-shadow: 0 0 0 3px rgba(74, 159, 212, 0.12);
        }

        .form-mg::placeholder {
            color: var(--text-placeholder);
        }

        label.form-label-mg {
            font-size: 12px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 6px;
            display: block;
            font-weight: 600;
        }

        /* ===== DIVIDER ===== */
        .divider-mg {
            height: 1px;
            background: var(--surface-border);
            margin: 20px 0;
        }

        /* ===== PROGRESS BAR ===== */
        .progress-mg {
            height: 5px;
            background: var(--surface-border);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 8px;
        }

        .progress-mg-bar {
            height: 100%;
            border-radius: 3px;
            transition: width 0.8s ease;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            #main-content {
                margin-left: 0 !important;
            }

            .content-wrapper {
                padding: 20px 16px;
            }
        }
    </style>
</head>

<body>
    <div id="app-wrapper">