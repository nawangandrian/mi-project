<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ═══════════════════════════════════════════════════════════════
// PUBLIC ROUTES (tanpa auth)
// ═══════════════════════════════════════════════════════════════
$routes->get('/', 'Home::index');

$routes->get('login',          'AuthController::index');
$routes->post('login/attempt', 'AuthController::authenticate');
$routes->get('logout',         'AuthController::logout');

// ═══════════════════════════════════════════════════════════════
// PROTECTED ROUTES — semua butuh login (filter: auth)
// ═══════════════════════════════════════════════════════════════
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // ── DASHBOARD — semua role (admin & cs) ────────────────────
    $routes->get('dashboard', 'DashboardController::index');

    // ── DATA — khusus cs & admin ────────────────────────────────
    // Sidebar menunjukkan import, produk, penjualan hanya untuk role cs.
    // Admin tetap bisa akses karena filter menerima kedua role.
    $routes->group('import', ['filter' => 'role:cs'], function ($routes) {
        $routes->get('/',              'ImportController::index');
        $routes->post('previewSheets', 'ImportController::previewSheets');
        $routes->post('proses',        'ImportController::proses');
        $routes->get('riwayat',        'ImportController::riwayat');
        $routes->post('deleteLog',     'ImportController::deleteLog');
    });

    $routes->group('produk', ['filter' => 'role:cs'], function ($routes) {
        $routes->get('/',                      'ProdukController::index');
        $routes->post('simpan',                'ProdukController::simpan');
        $routes->post('getData',               'ProdukController::getData');
        $routes->post('update/(:segment)',     'ProdukController::update/$1');
        $routes->post('hapus/(:segment)',      'ProdukController::hapus/$1');
        $routes->post('hapusSemua',            'ProdukController::hapusSemua');
        $routes->get('export',                 'ProdukController::export');
        $routes->get('downloadTemplate',       'ProdukController::downloadTemplate');
        $routes->post('prosesImport',          'ProdukController::prosesImport');
    });

    $routes->group('penjualan', ['filter' => 'role:cs'], function ($routes) {
        $routes->get('/',                      'PenjualanController::index');
        $routes->post('simpan',                'PenjualanController::simpan');
        $routes->post('getData',               'PenjualanController::getData');
        $routes->post('update/(:segment)',     'PenjualanController::update/$1');
        $routes->post('hapus/(:segment)',      'PenjualanController::hapus/$1');
        $routes->post('hapusSemua',            'PenjualanController::hapusSemua');
        $routes->get('export',                 'PenjualanController::export');
        $routes->get('downloadTemplate',       'PenjualanController::downloadTemplate');
        $routes->post('prosesImport',          'PenjualanController::prosesImport');
    });

    // ── PREDIKSI — khusus admin (bukan cs) ─────────────────────
    $routes->group('prediksi', ['filter' => 'role:admin'], static function ($routes) {
        $routes->get('/',                'PrediksiController::index');
        $routes->get('jalankan',         'PrediksiController::jalankan');
        $routes->post('jalankan',        'PrediksiController::prosesJalankan');
        $routes->get('riwayat',          'PrediksiController::riwayat');
        $routes->get('akurasi',          'PrediksiController::akurasi');
        $routes->get('detail/(:num)',    'PrediksiController::detail/$1');
        $routes->post('sinkron-aktual',  'PrediksiController::sinkronAktual');
    });

    // ── TRAINING — khusus admin (bukan cs) ─────────────────────
    $routes->group('training', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/',                               'TrainingController::index');
        $routes->get('proses',                          'TrainingController::prosesView');
        $routes->post('proses',                         'TrainingController::proses');
        $routes->get('status',                          'TrainingController::status');
        $routes->get('riwayat_model',                   'TrainingController::riwayatModel');
        $routes->get('model/detail/(:num)',             'TrainingController::detailModel/$1');
        $routes->post('model/aktifkan',                 'TrainingController::aktifkanModel');
        $routes->post('model/arsipkan',                 'TrainingController::arsipkanModel');
        $routes->get('model/diagram/(:num)/(:segment)', 'TrainingController::serveDiagram/$1/$2');
        $routes->get('filter-preview',                  'TrainingController::filterPreview');
    });

    // ── USER MANAGEMENT — khusus admin (bukan cs) ──────────────
    $routes->group('user', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/',           'UserController::index');
        $routes->post('store',      'UserController::store');
        $routes->post('getData',    'UserController::getData');
        $routes->post('update',     'UserController::update');
        $routes->post('delete',     'UserController::delete');
    });

});

// ═══════════════════════════════════════════════════════════════
// AUTH alternatif path (legacy, opsional)
// ═══════════════════════════════════════════════════════════════
$routes->group('auth', static function ($routes) {
    $routes->get('login',        'AuthController::index');
    $routes->post('prosesLogin', 'AuthController::authenticate');
    $routes->get('logout',       'AuthController::logout');
});