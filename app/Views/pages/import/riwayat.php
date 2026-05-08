<div class="content-wrapper-inner">

    <!-- ── Page Header ── -->
    <div class="mg-page-header">
        <div>
            <h1 class="page-title">Riwayat Import Data</h1>
            <p class="page-subtitle">Kelola riwayat impor transaksi dari file Excel</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('import') ?>" class="btn-mg btn-primary-mg">
                <i class="bi bi-upload"></i> Import Baru
            </a>
        </div>
    </div>

    <!-- ── Quick Nav ── -->
    <div class="quick-nav-row">
        <a href="<?= base_url('import') ?>" class="quick-nav-pill ">
            <i class="bi bi-upload"></i>
            <span>Import Excel</span>
        </a>
        <a href="<?= base_url('import/riwayat') ?>" class="quick-nav-pill active">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat Import</span>
        </a>
    </div>

    <!-- ── Stats Row ── -->
    <?php
    $totalImport      = count($logs ?? []);
    $totalTransaksi   = array_sum(array_column($logs ?? [], 'total_transaksi'));
    $totalProdukBaru  = array_sum(array_column($logs ?? [], 'produk_baru'));
    $totalPenjualan   = array_sum(array_column($logs ?? [], 'penjualan_baru'));
    ?>
    <div class="mg-stats-row">
        <div class="stat-card accent-blue">
            <div class="stat-icon" style="background:rgba(46,109,164,0.12);color:var(--mg-light)">
                <i class="bi bi-file-earmark-arrow-up-fill"></i>
            </div>
            <div class="stat-value"><?= number_format($totalImport) ?></div>
            <div class="stat-label">Total Import</div>
        </div>
        <div class="stat-card accent-cyan">
            <div class="stat-icon" style="background:rgba(0,151,184,0.12);color:var(--accent-cyan)">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div class="stat-value"><?= number_format($totalTransaksi) ?></div>
            <div class="stat-label">Total Transaksi</div>
        </div>
        <div class="stat-card accent-green">
            <div class="stat-icon" style="background:rgba(16,183,127,0.12);color:var(--accent-green)">
                <i class="bi bi-box2"></i>
            </div>
            <div class="stat-value"><?= number_format($totalProdukBaru) ?></div>
            <div class="stat-label">Produk Baru</div>
        </div>
        <div class="stat-card accent-yellow">
            <div class="stat-icon" style="background:rgba(212,160,23,0.12);color:var(--accent-yellow)">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="stat-value"><?= number_format($totalPenjualan) ?></div>
            <div class="stat-label">Penjualan Masuk</div>
        </div>
    </div>

    <!-- ── Table Card ── -->
    <div class="card-mg">
        <div class="mg-card-header">
            <div class="mg-card-title-group">
                <span class="section-title">Data Riwayat Import</span>
                <span class="badge-mg badge-blue" id="badgeCount"><?= $totalImport ?> riwayat</span>
            </div>
            <div class="mg-card-toolbar">
                <!-- Search -->
                <div class="mg-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="mgSearch" placeholder="Cari nama file…">
                </div>
            </div>
        </div>

        <div class="mg-table-wrap">
            <table class="table-mg" id="tblImportHistory">
                <thead>
                    <tr>
                        <th style="width:46px">No</th>
                        <th>File</th>
                        <th style="width:100px">Sheet</th>
                        <th style="width:110px">Total</th>
                        <th style="width:140px">Produk</th>
                        <th style="width:140px">Penjualan</th>
                        <th style="width:100px">Durasi</th>
                        <th style="width:110px">Status</th>
                        <th style="width:130px">Tanggal</th>
                        <th style="width:90px;text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): $no = 1; ?>
                        <?php foreach ($logs as $log): ?>
                            <?php $sheetCount = count(array_filter(explode(',', $log['sheets']))); ?>
                            <tr class="import-row" data-file="<?= strtolower(esc($log['file_name'])) ?>">
                                <td><span class="row-no"><?= $no++ ?></span></td>
                                <td>
                                    <div class="file-cell">
                                        <div class="file-icon">
                                            <i class="bi bi-file-spreadsheet-fill"></i>
                                        </div>
                                        <div>
                                            <div class="file-name"><?= esc($log['file_name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-mg badge-indigo">
                                        <i class="bi bi-table"></i> <?= $sheetCount ?> Sheet
                                    </span>
                                </td>
                                <td>
                                    <span class="metric-box metric-info">
                                        <?= number_format($log['total_transaksi']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div>
                                            <span class="metric-box metric-success">
                                                +<?= number_format($log['produk_baru']) ?>
                                            </span>
                                            <small class="text-muted ms-1">Baru</small>
                                        </div>
                                        <div>
                                            <span class="metric-box metric-warning">
                                                <?= number_format($log['produk_skip'] ?? 0) ?>
                                            </span>
                                            <small class="text-muted ms-1">Skip</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div>
                                            <span class="metric-box metric-success">
                                                +<?= number_format($log['penjualan_baru']) ?>
                                            </span>
                                            <small class="text-muted ms-1">Baru</small>
                                        </div>
                                        <div>
                                            <span class="metric-box metric-danger">
                                                <?= number_format($log['penjualan_skip'] ?? 0) ?>
                                            </span>
                                            <small class="text-muted ms-1">Skip</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="duration-badge">
                                        <i class="bi bi-hourglass-split"></i>
                                        <?= number_format($log['durasi'], 2) ?>s
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = $log['status'] === 'success' ? 'status-success' : 'status-error';
                                    $statusIcon  = $log['status'] === 'success' ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <i class="bi <?= $statusIcon ?>"></i>
                                        <?= ucfirst($log['status']) ?>
                                    </span>
                                </td>
                                <td class="date-cell">
                                    <div class="fw-semibold text-dark">
                                        <?= date('d M Y', strtotime($log['created_at'])) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('H:i:s', strtotime($log['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <button class="action-btn delete-btn btnDeleteLog"
                                            data-id="<?= esc($log['id_import_log'] ?? $log['id']) ?>"
                                            data-file="<?= esc($log['file_name']) ?>"
                                            title="Hapus riwayat ini">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="empty-state">
                                <i class="bi bi-file-earmark-x" style="font-size:38px;color:var(--text-placeholder);display:block;margin-bottom:10px"></i>
                                Belum ada riwayat import
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mg-table-footer">
            <span class="table-info" id="tableInfo">Menampilkan <?= count($logs ?? []) ?> riwayat import</span>
        </div>
    </div>

</div>


<style>
    .file-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .file-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: rgba(16, 185, 129, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #10b981;
        flex-shrink: 0;
    }

    .file-name {
        font-weight: 600;
        color: #111827;
        word-break: break-word;
    }

    .duration-badge {
        background: #f3f4f6;
        border-radius: 999px;
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .action-group {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .action-btn.edit-btn {
        background: #eff6ff;
        color: #2563eb;
    }

    .action-btn.edit-btn:hover {
        background: #2563eb;
        color: white;
    }

    .action-btn.delete-btn {
        background: #fef2f2;
        color: #dc2626;
    }

    .action-btn.delete-btn:hover {
        background: #dc2626;
        color: white;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border-radius: 999px;
        padding: 0.45rem 0.8rem;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .status-success {
        background: #ecfdf5;
        color: #059669;
    }

    .status-error {
        background: #fef2f2;
        color: #dc2626;
    }

    /* ── Quick Nav ── */
    .quick-nav-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 28px;
        /* jarak ke stats/card bawah */
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

    /* ═══════════════════════════════════════════════
   FIX STATS CARD LAYOUT
═══════════════════════════════════════════════ */

    .mg-stats-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        position: relative;
        background: #ffffff;
        border-radius: 24px;
        padding: 28px;
        min-height: 180px;
        border: 1px solid rgba(148, 163, 184, 0.12);
        box-shadow:
            0 4px 12px rgba(15, 23, 42, 0.04),
            0 1px 2px rgba(15, 23, 42, 0.02);
        overflow: hidden;
        transition: all .25s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow:
            0 12px 24px rgba(15, 23, 42, 0.08),
            0 4px 8px rgba(15, 23, 42, 0.04);
    }

    .ml-badge {
        background: rgba(74, 159, 212, .15);
        color: var(--mg-light);
        border: 1px solid rgba(74, 159, 212, .25);
        font-size: 10px;
        padding: 1px 7px;
    }

    /* Accent top border */
    .stat-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }

    .stat-card.accent-blue::before {
        background: #3b82f6;
    }

    .stat-card.accent-cyan::before {
        background: #06b6d4;
    }

    .stat-card.accent-green::before {
        background: #10b981;
    }

    .stat-card.accent-yellow::before {
        background: #f59e0b;
    }

    .stat-icon {
        width: 72px;
        height: 72px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 24px;
    }

    .stat-value {
        font-size: 42px;
        line-height: 1;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 12px;
    }

    .stat-label {
        font-size: 15px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #6b85a3;
    }

    /* HEADER */
    .mg-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 32px;
        /* jarak ke card bawah */
    }

    /* tombol */
    .header-actions {
        margin-bottom: 6px;
    }

    /* RESPONSIVE */
    @media (max-width: 1200px) {
        .mg-stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .mg-stats-row {
            grid-template-columns: 1fr;
        }

        .stat-card {
            min-height: auto;
            padding: 22px;
        }

        .stat-value {
            font-size: 34px;
        }
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.85rem;
        }

        .file-cell {
            flex-direction: column;
            align-items: flex-start;
        }

        .action-group {
            justify-content: flex-start;
        }
    }

    /* ═══════════════════════════════════════════════════════
   FIX TABLE RIWAYAT IMPORT
═══════════════════════════════════════════════════════ */

    .mg-table-wrap {
        width: 100%;
        overflow-x: auto;
        border-radius: var(--radius-md);
        border: 1px solid var(--surface-border);
        background: var(--card-bg);
    }

    /* TABLE */
    .table-mg {
        width: 100%;
        min-width: 1200px;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }

    /* HEADER */
    .table-mg thead th {
        background: var(--card-bg-alt);
        color: #7a93ad;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 16px 14px;
        border-bottom: 1px solid var(--surface-border);
        white-space: nowrap;
    }

    /* BODY */
    .table-mg tbody td {
        padding: 18px 14px;
        vertical-align: middle;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        background: #fff;
    }

    /* ROW HOVER */
    .table-mg tbody tr {
        transition: all .2s ease;
    }

    .table-mg tbody tr:hover td {
        background: rgba(74, 159, 212, 0.03);
    }

    /* NO */
    .row-no {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: rgba(74, 159, 212, .08);
        color: var(--mg-light);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 12px;
    }

    /* FILE */
    .file-cell {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .file-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(16, 185, 129, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #10b981;
        font-size: 18px;
        flex-shrink: 0;
    }

    .file-name {
        font-weight: 700;
        color: #111827;
        line-height: 1.45;
        word-break: break-word;
        font-size: 13px;
    }

    /* METRIC */
    .metric-box {
        min-width: 56px;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 800;
    }

    .metric-info {
        background: rgba(59, 130, 246, .12);
        color: #2563eb;
    }

    .metric-success {
        background: rgba(16, 185, 129, .12);
        color: #059669;
    }

    .metric-warning {
        background: rgba(245, 158, 11, .12);
        color: #d97706;
    }

    .metric-danger {
        background: rgba(239, 68, 68, .12);
        color: #dc2626;
    }

    /* DURATION */
    .duration-badge {
        background: #f3f4f6;
        border-radius: 999px;
        padding: 0.42rem 0.8rem;
        font-size: 12px;
        font-weight: 700;
        color: #374151;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
    }

    /* STATUS */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border-radius: 999px;
        padding: 0.45rem 0.85rem;
        font-size: 11.5px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-success {
        background: #ecfdf5;
        color: #059669;
    }

    .status-error {
        background: #fef2f2;
        color: #dc2626;
    }

    /* DATE */
    .date-cell {
        white-space: nowrap;
    }

    .date-cell .fw-semibold {
        font-size: 12.5px;
    }

    /* ACTION */
    .action-group {
        display: flex;
        justify-content: center;
    }

    .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all .2s ease;
    }

    .action-btn.delete-btn {
        background: #fef2f2;
        color: #dc2626;
    }

    .action-btn.delete-btn:hover {
        background: #dc2626;
        color: #fff;
        transform: translateY(-1px);
    }

    /* EMPTY */
    .empty-state {
        text-align: center;
        padding: 60px 20px !important;
        color: var(--text-muted);
        font-size: 14px;
    }

    /* SEARCH */
    .mg-search-box {
        position: relative;
        width: 260px;
    }

    .mg-search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-placeholder);
        font-size: 14px;
    }

    .mg-search-box input {
        width: 100%;
        height: 42px;
        border-radius: 12px;
        border: 1px solid var(--surface-border);
        background: var(--card-bg-alt);
        padding: 0 14px 0 38px;
        font-size: 13px;
        outline: none;
        transition: all .2s ease;
    }

    .mg-search-box input:focus {
        border-color: var(--mg-light);
        box-shadow: 0 0 0 4px rgba(74, 159, 212, .08);
    }

    /* FOOTER */
    .mg-table-footer {
        padding-top: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-info {
        font-size: 12px;
        color: var(--text-muted);
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {

        .mg-card-header {
            flex-direction: column;
            align-items: stretch;
        }

        .mg-search-box {
            width: 100%;
        }

        .table-mg {
            min-width: 1050px;
        }
    }

    /* FIX FLEX OVERFLOW */
    #main-content {
        min-width: 0;
        overflow-x: hidden;
    }

    .content-wrapper {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        box-sizing: border-box;
    }

    .content-wrapper-inner {
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    /* IMPORTANT */
    .card-mg,
    .mg-stats-row,
    .mg-page-header,
    .mg-table-wrap {
        min-width: 0;
        max-width: 100%;
    }

    /* HEADER CARD */
    .mg-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 20px;
        /* JARAK KE TABEL */
    }

    /* title + badge */
    .mg-card-title-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
</style>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tblImportHistory = document.getElementById('tblImportHistory');
        const mgSearch = document.getElementById('mgSearch');
        const badgeCount = document.getElementById('badgeCount');
        const tableInfo = document.getElementById('tableInfo');

        // ════════════════════════════════════════════════════════════
        // SEARCH FUNCTIONALITY
        // ════════════════════════════════════════════════════════════
        if (mgSearch) {
            mgSearch.addEventListener('keyup', function() {
                const searchVal = this.value.toLowerCase().trim();
                const rows = tblImportHistory.querySelectorAll('tbody tr.import-row');
                let visibleCount = 0;

                rows.forEach(row => {
                    const fileName = row.getAttribute('data-file');
                    if (fileName.includes(searchVal) || searchVal === '') {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                tableInfo.textContent = `Menampilkan ${visibleCount} riwayat import`;
            });
        }
        // ════════════════════════════════════════════════════════════
        // DELETE FUNCTIONALITY - SWEET ALERT
        // ════════════════════════════════════════════════════════════
        document.querySelectorAll('.btnDeleteLog').forEach(btn => {

            btn.addEventListener('click', function() {

                const logId = this.getAttribute('data-id');
                const fileName = this.getAttribute('data-file');

                if (!logId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'ID riwayat tidak ditemukan.',
                        confirmButtonColor: '#dc2626'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Hapus Riwayat?',
                    html: `
                <div style="font-size:14px;color:#64748b">
                    Riwayat import
                    <br><br>
                    <strong style="color:#0f172a">${fileName}</strong>
                    <br><br>
                    akan dihapus.
                    <br>
                    Data transaksi yang sudah masuk database tidak ikut terhapus.
                </div>
            `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#94a3b8',
                    borderRadius: '18px'
                }).then((resultSwal) => {

                    if (!resultSwal.isConfirmed) return;

                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('<?= base_url('import/deleteLog') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                            },
                            body: JSON.stringify({
                                id: logId
                            })
                        })
                        .then(response => response.json())
                        .then(result => {

                            if (result.success) {

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Riwayat import berhasil dihapus.',
                                    confirmButtonColor: '#10b981',
                                    timer: 1800,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });

                            } else {

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: result.message || 'Gagal menghapus riwayat.',
                                    confirmButtonColor: '#dc2626'
                                });

                            }

                        })
                        .catch(error => {

                            console.error(error);

                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: 'Tidak dapat menghapus riwayat import.',
                                confirmButtonColor: '#dc2626'
                            });

                        });

                });

            });

        });

    });
</script>