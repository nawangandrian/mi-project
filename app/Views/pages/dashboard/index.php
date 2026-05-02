<?php /** @var array $stats @var array $recent_activities @var string $title */ ?>

<style>
/* === MIDNIGHT GLOW THEME === */
body {
    background: linear-gradient(135deg, #141E30, #243B55);
    color: #eaeef5;
}

/* Card Glass */
.card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255,255,255,0.08);
    backdrop-filter: blur(14px);
    border-radius: 16px;
    transition: all .3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.45);
}

.card-header {
    background: transparent !important;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

/* Text */
.text-muted {
    color: rgba(255,255,255,0.6) !important;
}

/* Icon Glow */
.icon-glow {
    box-shadow: 0 0 18px rgba(255,255,255,0.15);
}

/* Buttons */
.btn {
    border-radius: 12px;
    transition: all .2s ease;
}

.btn-outline-secondary {
    border-color: rgba(255,255,255,0.3);
    color: #fff;
}

.btn-outline-secondary:hover {
    background: rgba(255,255,255,0.1);
}

/* Quick Button */
.quick-btn {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    color: white;
}

.quick-btn:hover {
    background: rgba(255,255,255,0.1);
}

/* List */
.list-group-item {
    background: transparent;
    border-color: rgba(255,255,255,0.05);
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
}

/* Global smooth */
* {
    transition: all .2s ease;
}
</style>

<!-- HEADER -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-semibold text-white"><?= esc($title) ?></h4>
        <small class="text-muted">
            <?= date('l, d F Y') ?>
        </small>
    </div>
    <button class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
    </button>
</div>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
    <?php foreach ($stats as $stat): ?>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                
                <!-- Icon -->
                <div class="rounded-3 p-3 text-<?= esc($stat['color']) ?> icon-glow"
                     style="background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));">
                    <i class="bi <?= esc($stat['icon']) ?> fs-4"></i>
                </div>

                <!-- Info -->
                <div>
                    <div class="fs-4 fw-bold text-white mb-1">
                        <?= number_format($stat['value']) ?>
                    </div>
                    <div class="text-muted small">
                        <?= esc($stat['label']) ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach ?>
</div>

<!-- MAIN CONTENT -->
<div class="row g-3">

    <!-- ACTIVITY -->
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-white">Aktivitas Terbaru</h6>
                <a href="#" class="text-decoration-none text-info small">Lihat Semua</a>
            </div>

            <div class="card-body p-0">
                <ul class="list-group list-group-flush">

                    <?php foreach ($recent_activities as $act): ?>
                    <li class="list-group-item px-4 py-3 d-flex align-items-center gap-3">

                        <?php
                        $words = explode(' ', $act['user']);
                        $initials = strtoupper(
                            substr($words[0], 0, 1) .
                            (isset($words[1]) ? substr($words[1], 0, 1) : '')
                        );
                        ?>

                        <!-- Avatar -->
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-semibold flex-shrink-0"
                             style="
                                width:38px;height:38px;
                                font-size:.8rem;
                                background: linear-gradient(135deg, #4facfe, #00f2fe);
                                color:white;
                                box-shadow:0 0 12px rgba(0,0,0,0.4);
                             ">
                            <?= $initials ?>
                        </div>

                        <!-- Content -->
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-white text-truncate small">
                                <?= esc($act['user']) ?>
                            </div>
                            <div class="text-muted text-truncate" style="font-size:.8rem;">
                                <?= esc($act['action']) ?>
                            </div>
                        </div>

                        <!-- Time -->
                        <div class="text-muted small flex-shrink-0">
                            <?= esc($act['time']) ?>
                        </div>

                    </li>
                    <?php endforeach ?>

                </ul>
            </div>
        </div>
    </div>

    <!-- QUICK ACCESS -->
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-semibold text-white">Akses Cepat</h6>
            </div>

            <div class="card-body">
                <div class="row g-2">

                    <?php
                    $shortcuts = [
                        ['label' => 'Tambah Pengguna',  'icon' => 'bi-person-plus',      'href' => '#'],
                        ['label' => 'Buat Laporan',     'icon' => 'bi-file-earmark-plus', 'href' => '#'],
                        ['label' => 'Lihat Transaksi',  'icon' => 'bi-receipt',           'href' => '#'],
                        ['label' => 'Pengaturan',       'icon' => 'bi-gear',              'href' => '#'],
                    ];

                    foreach ($shortcuts as $s): ?>
                    <div class="col-6">
                        <a href="<?= $s['href'] ?>"
                           class="btn quick-btn w-100 d-flex flex-column align-items-center gap-1 py-3">
                            
                            <i class="bi <?= $s['icon'] ?> fs-5"></i>
                            <span class="small"><?= $s['label'] ?></span>

                        </a>
                    </div>
                    <?php endforeach ?>

                </div>
            </div>
        </div>
    </div>

</div>