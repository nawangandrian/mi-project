<div class="content-wrapper-inner">

    <!-- ── Page Header ── -->
    <div class="mg-page-header">
        <div>
            <h1 class="page-title">Manajemen User</h1>
            <p class="page-subtitle">Kelola akun dan hak akses pengguna sistem</p>
        </div>
        <button class="btn-mg btn-primary-mg" id="btnOpenAdd">
            <i class="bi bi-person-plus-fill"></i> Tambah User
        </button>
    </div>

    <!-- ── Stats Row ── -->
    <div class="mg-stats-row">
        <div class="stat-card accent-cyan">
            <div class="stat-icon" style="background:rgba(0,151,184,0.12);color:var(--accent-cyan)">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-value"><?= count($users ?? []) ?></div>
            <div class="stat-label">Total User</div>
        </div>
        <div class="stat-card accent-blue">
            <div class="stat-icon" style="background:rgba(46,109,164,0.12);color:var(--mg-light)">
                <i class="bi bi-shield-fill-check"></i>
            </div>
            <div class="stat-value"><?= count(array_filter($users ?? [], fn($u) => $u['role'] === 'admin')) ?></div>
            <div class="stat-label">Admin</div>
        </div>
        <div class="stat-card accent-green">
            <div class="stat-icon" style="background:rgba(16,183,127,0.12);color:var(--accent-green)">
                <i class="bi bi-headset"></i>
            </div>
            <div class="stat-value"><?= count(array_filter($users ?? [], fn($u) => $u['role'] === 'cs')) ?></div>
            <div class="stat-label">CS</div>
        </div>
    </div>

    <!-- ── Table Card ── -->
    <div class="card-mg">
        <div class="mg-card-header">
            <div class="mg-card-title-group">
                <span class="section-title">Data User</span>
                <span class="badge-mg badge-cyan"><?= count($users ?? []) ?> user</span>
            </div>
            <div class="mg-card-toolbar"> <!-- tambah wrapper ini -->
                <div class="mg-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="mgSearch" placeholder="Cari username...">
                </div>
            </div>
        </div>

        <div class="mg-table-wrap">
            <table class="table-mg" id="tblUser">
                <thead>
                    <tr>
                        <th style="width:50px">No</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Dibuat</th>
                        <th style="width:110px;text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): $no = 1; ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><span class="row-no"><?= $no++ ?></span></td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-cell-avatar"><?= strtoupper(substr($u['username'], 0, 1)) ?></div>
                                        <span><?= esc($u['username']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $roleClass = $u['role'] === 'admin' ? 'badge-blue'  : 'badge-green';
                                    $roleIcon  = $u['role'] === 'admin' ? 'shield-fill' : 'headset';
                                    ?>
                                    <span class="badge-mg <?= $roleClass ?>">
                                        <i class="bi bi-<?= $roleIcon ?>"></i>
                                        <?= strtoupper($u['role']) ?>
                                    </span>
                                </td>
                                <td class="date-cell">
                                    <i class="bi bi-calendar3" style="font-size:11px;margin-right:5px"></i>
                                    <?= date('d M Y, H:i', strtotime($u['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <button class="action-btn edit-btn btnEdit"
                                            data-id="<?= esc($u['id_user']) ?>" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="action-btn delete-btn btnDelete"
                                            data-id="<?= esc($u['id_user']) ?>" title="Hapus">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-state">
                                <i class="bi bi-people" style="font-size:36px;color:var(--text-placeholder);display:block;margin-bottom:10px"></i>
                                Belum ada data user
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===============================
     MODAL TAMBAH USER
     =============================== -->
<div class="mg-modal-overlay" id="overlayAdd">
    <div class="mg-modal">
        <div class="mg-modal-header" style="--modal-accent:var(--mg-glow)">
            <div class="mg-modal-icon" style="background:rgba(46,109,164,0.15);color:var(--mg-light)">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <div>
                <div class="mg-modal-title">Tambah User</div>
                <div class="mg-modal-sub">Buat akun baru untuk sistem</div>
            </div>
            <button class="mg-modal-close" id="closeAdd" type="button"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="mg-modal-body">
            <form id="formStore" autocomplete="off">
                <?= csrf_field() ?>

                <div class="mg-field">
                    <label class="form-label-mg">Username</label>
                    <div class="mg-input-icon">
                        <i class="bi bi-person"></i>
                        <input type="text" name="username" class="form-mg" placeholder="Masukkan username…">
                    </div>
                    <small class="mg-field-error error-username-store"></small>
                </div>

                <div class="mg-field">
                    <label class="form-label-mg">Password</label>
                    <div class="mg-input-icon">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" class="form-mg password-field" placeholder="Min. 6 karakter">
                        <button type="button" class="mg-eye-btn toggle-password"><i class="bi bi-eye"></i></button>
                    </div>
                    <small class="mg-field-error error-password-store"></small>
                </div>

                <div class="mg-field">
                    <label class="form-label-mg">Role</label>
                    <div class="mg-select-wrap">
                        <i class="bi bi-shield-check"></i>
                        <select name="role" class="form-mg">
                            <option value="">-- Pilih Role --</option>
                            <option value="admin">Admin</option>
                            <option value="cs">CS</option>
                        </select>
                        <i class="bi bi-chevron-down mg-select-arrow"></i>
                    </div>
                    <small class="mg-field-error error-role-store"></small>
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

<!-- ===============================
     MODAL EDIT USER
     =============================== -->
<div class="mg-modal-overlay" id="overlayEdit">
    <div class="mg-modal">
        <div class="mg-modal-header" style="--modal-accent:#d4a017">
            <div class="mg-modal-icon" style="background:rgba(212,160,23,0.15);color:var(--accent-yellow)">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <div class="mg-modal-title">Edit User</div>
                <div class="mg-modal-sub">Perbarui data akun pengguna</div>
            </div>
            <button class="mg-modal-close" id="closeEdit" type="button"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="mg-modal-body">
            <form id="formEdit" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="id_user" id="edit_id">

                <div class="mg-field">
                    <label class="form-label-mg">Username</label>
                    <div class="mg-input-icon">
                        <i class="bi bi-person"></i>
                        <input type="text" name="username" id="edit_username" class="form-mg" placeholder="Username">
                    </div>
                    <small class="mg-field-error error-username-edit"></small>
                </div>

                <div class="mg-field">
                    <label class="form-label-mg">
                        Password
                        <span style="font-weight:400;color:var(--text-muted);text-transform:none;letter-spacing:0">(Opsional)</span>
                    </label>
                    <div class="mg-input-icon">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" id="password_edit" class="form-mg"
                            placeholder="Kosongkan jika tidak diubah" autocomplete="new-password">
                        <button type="button" class="mg-eye-btn" id="togglePasswordEdit"><i class="bi bi-eye"></i></button>
                    </div>
                    <small class="mg-field-error error-password-edit"></small>
                </div>

                <div class="mg-field">
                    <label class="form-label-mg">Role</label>
                    <div class="mg-select-wrap">
                        <i class="bi bi-shield-check"></i>
                        <select name="role" id="edit_role" class="form-mg">
                            <option value="admin">Admin</option>
                            <option value="cs">CS</option>
                        </select>
                        <i class="bi bi-chevron-down mg-select-arrow"></i>
                    </div>
                    <small class="mg-field-error error-role-edit"></small>
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

<!-- ===============================
     SCOPED STYLES
     =============================== -->
<style>
    .content-wrapper-inner {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .mg-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }

    .mg-stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    @media(max-width:640px) {
        .mg-stats-row {
            grid-template-columns: 1fr;
        }
    }

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
        box-shadow: 0 0 0 3px rgba(74, 159, 212, 0.1);
    }

    .mg-search-box input {
        border: none;
        background: transparent;
        color: var(--text-ink);
        font-size: 13px;
        outline: none;
        width: 190px;
    }

    .mg-search-box input::placeholder {
        color: var(--text-placeholder);
    }

    .mg-table-wrap {
        overflow-x: auto;
        border-radius: var(--radius-sm);
        border: 1px solid var(--surface-border);
    }

    .table-mg {
        min-width: 560px;
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

    .user-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-cell-avatar {
        width: 30px;
        height: 30px;
        background: linear-gradient(135deg, var(--mg-glow), var(--accent-cyan));
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-display);
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }

    .date-cell {
        font-size: 12.5px;
        color: var(--text-muted);
    }

    .action-group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        cursor: pointer;
        transition: var(--transition);
        background: none;
    }

    .edit-btn {
        background: rgba(212, 160, 23, 0.1);
        color: var(--accent-yellow);
        border-color: rgba(212, 160, 23, 0.2);
    }

    .edit-btn:hover {
        background: rgba(212, 160, 23, 0.22);
        transform: translateY(-1px);
    }

    .delete-btn {
        background: rgba(229, 62, 62, 0.1);
        color: var(--accent-red);
        border-color: rgba(229, 62, 62, 0.2);
    }

    .delete-btn:hover {
        background: rgba(229, 62, 62, 0.22);
        transform: translateY(-1px);
    }

    .empty-state {
        text-align: center;
        padding: 48px 24px !important;
        color: var(--text-muted);
        font-size: 13px;
    }

    /* Modal */
    .mg-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(10, 16, 28, 0.65);
        backdrop-filter: blur(5px);
        z-index: 1200;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .mg-modal-overlay.open {
        display: flex;
        animation: mgFadeIn 0.18s ease;
    }

    @keyframes mgFadeIn {
        from {
            opacity: 0
        }

        to {
            opacity: 1
        }
    }

    .mg-modal {
        background: var(--card-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-xl);
        width: 100%;
        max-width: 440px;
        box-shadow: 0 24px 60px rgba(14, 23, 36, 0.25), 0 4px 16px rgba(14, 23, 36, 0.1);
        animation: mgSlideUp 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    @keyframes mgSlideUp {
        from {
            transform: translateY(18px);
            opacity: 0
        }

        to {
            transform: none;
            opacity: 1
        }
    }

    .mg-modal-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 20px 22px;
        background: var(--card-bg-alt);
        border-bottom: 1px solid var(--surface-border);
        position: relative;
    }

    .mg-modal-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--modal-accent, var(--mg-glow));
        border-radius: var(--radius-xl) var(--radius-xl) 0 0;
    }

    .mg-modal-icon {
        width: 42px;
        height: 42px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        flex-shrink: 0;
    }

    .mg-modal-title {
        font-family: var(--font-display);
        font-size: 15px;
        font-weight: 700;
        color: var(--text-ink);
    }

    .mg-modal-sub {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 1px;
    }

    .mg-modal-close {
        margin-left: auto;
        width: 32px;
        height: 32px;
        background: var(--card-bg);
        border: 1px solid var(--surface-border);
        border-radius: 8px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 13px;
        transition: var(--transition);
    }

    .mg-modal-close:hover {
        color: var(--accent-red);
        border-color: rgba(229, 62, 62, 0.3);
        background: rgba(229, 62, 62, 0.06);
    }

    .mg-modal-body {
        padding: 22px;
        display: flex;
        flex-direction: column;
    }

    .mg-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 14px;
    }

    .mg-field:last-child {
        margin-bottom: 0;
    }

    .mg-input-icon {
        position: relative;
        display: flex;
        align-items: center;
    }

    .mg-input-icon>i:first-child {
        position: absolute;
        left: 12px;
        color: var(--text-placeholder);
        font-size: 14px;
        pointer-events: none;
    }

    .mg-input-icon .form-mg {
        padding-left: 36px;
        padding-right: 40px;
    }

    .mg-eye-btn {
        position: absolute;
        right: 10px;
        background: none;
        border: none;
        color: var(--text-placeholder);
        cursor: pointer;
        padding: 4px;
        font-size: 14px;
        transition: var(--transition);
    }

    .mg-eye-btn:hover {
        color: var(--mg-light);
    }

    .mg-select-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .mg-select-wrap>i:first-child {
        position: absolute;
        left: 12px;
        color: var(--text-placeholder);
        font-size: 14px;
        pointer-events: none;
    }

    .mg-select-wrap .form-mg {
        padding-left: 36px;
        padding-right: 32px;
        appearance: none;
    }

    .mg-select-arrow {
        position: absolute;
        right: 12px;
        color: var(--text-muted);
        font-size: 11px;
        pointer-events: none;
    }

    .mg-field-error {
        font-size: 11.5px;
        color: var(--accent-red);
        min-height: 16px;
    }

    .mg-modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 22px;
        border-top: 1px solid var(--surface-border);
        background: var(--card-bg-alt);
    }

    /* Pastikan di user page */
    .mg-card-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Tambahkan di bagian bawah <style> yang sudah ada */

/* ═══════════════════════════════════════════════
   FIX CORE — Mobile overflow & clip kiri
═══════════════════════════════════════════════ */
@media (max-width: 768px) {

    /* Pastikan main-content tidak ada margin sisa sidebar */
    #main-content {
        margin-left: 0 !important;
        width: 100% !important;
        overflow-x: hidden !important;
    }

    /* Content wrapper full-width, padding lebih kecil */
    .content-wrapper {
        padding: 16px 12px !important;
        overflow-x: hidden;
    }

    /* Wrapper inner juga full-width */
    .content-wrapper-inner {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    /* Card tidak boleh keluar dari parent */
    .card-mg {
        width: 100%;
        min-width: 0;
        padding: 14px 12px;
        border-radius: var(--radius-md);
    }

    /* Stat cards: padding lebih ketat */
    .stat-card {
        padding: 12px 10px !important;
        min-width: 0;
    }

    /* Table wrap: pastikan scroll horizontal aktif */
    .mg-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Kolom tanggal: sembunyikan di mobile (terlalu lebar) */
    .date-cell,
    .table-mg thead th:nth-child(5) {
        display: none;
    }

    /* ID produk: lebih compact */
    .id-code {
        font-size: 10px;
        padding: 2px 4px;
        max-width: 90px;
        display: inline-block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Produk name: boleh wrap */
    .produk-name {
        font-size: 12px;
        white-space: normal;
        word-break: break-word;
    }

    /* Produk icon: lebih kecil */
    .produk-icon {
        width: 26px;
        height: 26px;
        font-size: 12px;
        flex-shrink: 0;
    }

    /* Badge di tabel: kompak */
    .badge-mg {
        font-size: 10px;
        padding: 2px 6px;
        white-space: nowrap;
    }

    /* Row number: lebih kecil */
    .row-no {
        width: 20px;
        height: 20px;
        font-size: 10px;
    }

    /* Action buttons: rapat */
    .action-group {
        gap: 5px;
    }

    .action-btn {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
}

/* ═══════════════════════════════════════════════
   FIX — Mobile kecil ≤ 480px
═══════════════════════════════════════════════ */
@media (max-width: 480px) {

    .content-wrapper {
        padding: 12px 10px !important;
    }

    /* Sembunyikan juga kolom No di layar sangat kecil */
    .table-mg thead th:first-child,
    .table-mg tbody td:first-child {
        display: none;
    }

    /* Table min-width lebih kecil agar muat tanpa scroll */
    .table-mg {
        min-width: 320px;
    }
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        function qs(sel) {
            return document.querySelector(sel);
        }

        function qsa(sel) {
            return document.querySelectorAll(sel);
        }

        function closeModal(id) {
            var el = qs('#' + id);
            if (el) {
                el.classList.remove('open');
                el.style.display = 'none'; // ← tambah ini
                document.body.style.overflow = '';
            }
        }

        function openModal(id) {
            var el = qs('#' + id);
            if (el) {
                el.style.display = ''; // ← reset dulu
                el.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        }

        function clearErrors() {
            qsa('.mg-field-error').forEach(function(el) {
                el.textContent = '';
            });
        }

        function setErrors(errors, suffix) {
            Object.entries(errors).forEach(function([field, msg]) {
                var el = qs('.error-' + field + '-' + suffix);
                if (el) el.textContent = msg;
            });
        }

        function postForm(url, formId) {
            var form = qs('#' + formId);
            var data = new FormData(form);
            return fetch(BASE_URL + url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: data,
            }).then(function(r) {
                return r.json();
            });
        }

        function postData(url, payload) {
            var fd = new FormData();
            Object.entries(payload).forEach(function([k, v]) {
                fd.append(k, v);
            });
            return fetch(BASE_URL + url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd,
            }).then(function(r) {
                return r.json();
            });
        }

        /* Live Search */
        var searchInput = qs('#mgSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                var q = this.value.toLowerCase();
                qsa('#tblUser tbody tr').forEach(function(tr) {
                    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }

        /* Modal open/close */
        var btnOpenAdd = qs('#btnOpenAdd');
        if (btnOpenAdd) btnOpenAdd.addEventListener('click', function() {
            openModal('overlayAdd');
        });

        var closeAdd = qs('#closeAdd');
        if (closeAdd) closeAdd.addEventListener('click', function() {
            closeModal('overlayAdd');
        });

        var cancelAdd = qs('#cancelAdd');
        if (cancelAdd) cancelAdd.addEventListener('click', function() {
            closeModal('overlayAdd');
        });

        var closeEdit = qs('#closeEdit');
        if (closeEdit) closeEdit.addEventListener('click', function() {
            closeModal('overlayEdit');
        });

        var cancelEdit = qs('#cancelEdit');
        if (cancelEdit) cancelEdit.addEventListener('click', function() {
            closeModal('overlayEdit');
        });

        qsa('.mg-modal-overlay').forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) closeModal(this.id);
            });
        });

        /* Toggle password */
        qsa('.toggle-password').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var inp = this.closest('.mg-input-icon').querySelector('.password-field');
                var icon = this.querySelector('i');
                var show = inp.type === 'password';
                inp.type = show ? 'text' : 'password';
                icon.classList.toggle('bi-eye', !show);
                icon.classList.toggle('bi-eye-slash', show);
            });
        });

        var togglePwEdit = qs('#togglePasswordEdit');
        if (togglePwEdit) {
            togglePwEdit.addEventListener('click', function() {
                var inp = qs('#password_edit');
                var icon = this.querySelector('i');
                var show = inp.type === 'password';
                inp.type = show ? 'text' : 'password';
                icon.classList.toggle('bi-eye', !show);
                icon.classList.toggle('bi-eye-slash', show);
            });
        }

        /* CREATE */
        var btnSave = qs('#btnSave');
        if (btnSave) {
            btnSave.addEventListener('click', function() {
                clearErrors();
                postForm('user/store', 'formStore')
                    .then(function(res) {
                        if (res.status === 'error_validation') {
                            setErrors(res.errors, 'store');
                            return;
                        }
                        closeModal('overlayAdd'); // ← tutup modal dulu
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            confirmButtonColor: '#2e6da4'
                        }).then(function() {
                            location.reload();
                        });
                    })
                    .catch(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menghubungi server.'
                        });
                    });
            });
        }

        /* UPDATE */
        var btnUpdate = qs('#btnUpdate');
        if (btnUpdate) {
            btnUpdate.addEventListener('click', function() {
                clearErrors();
                postForm('user/update', 'formEdit')
                    .then(function(res) {
                        if (res.status === 'error_validation') {
                            setErrors(res.errors, 'edit');
                            return;
                        }
                        closeModal('overlayEdit'); // ← tutup modal dulu
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: res.message,
                            confirmButtonColor: '#2e6da4'
                        }).then(function() {
                            location.reload();
                        });
                    });
            });
        }

        /* GET DATA (edit) - pakai event delegation */
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btnEdit');
            if (!btn) return;
            var id = btn.dataset.id;
            postData('user/getData', {
                    id: id
                })
                .then(function(res) {
                    qs('#edit_id').value = res.id_user;
                    qs('#edit_username').value = res.username;
                    qs('#edit_role').value = res.role;
                    openModal('overlayEdit');
                });
        });

        /* DELETE - pakai event delegation */
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btnDelete');
            if (!btn) return;
            var id = btn.dataset.id;
            Swal.fire({
                title: 'Hapus user ini?',
                text: 'Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e53e3e',
                cancelButtonColor: '#243B55',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
            }).then(function(r) {
                if (!r.isConfirmed) return;
                postData('user/delete', {
                        id: id
                    })
                    .then(function(res) {
                        Swal.fire({
                                icon: 'success',
                                title: 'Dihapus!',
                                text: res.message,
                                confirmButtonColor: '#2e6da4'
                            })
                            .then(function() {
                                location.reload();
                            });
                    });
            });
        });

    });
</script>