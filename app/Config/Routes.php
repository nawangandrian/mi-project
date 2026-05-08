<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// ── PUBLIC ROUTES ────────────────────────────────────────────
// Landing page → Home controller → welcome_message view
$routes->get('/', 'Home::index');

// Login page (form + proses)
$routes->get('login',         'AuthController::index');
$routes->post('login/attempt', 'AuthController::authenticate');
$routes->get('logout',        'AuthController::logout');

$routes->group('', ['filter' => 'auth'], function ($routes) {

    // ─── DASHBOARD ─────────────────────────────
    $routes->get('dashboard', 'DashboardController::index');

    // ─── USER ───────────────────────────────────
    $routes->group('user', function ($routes) {
        $routes->get('/', 'UserController::index');
        $routes->post('store', 'UserController::store');
        $routes->post('getData', 'UserController::getData');
        $routes->post('update', 'UserController::update');
        $routes->post('delete', 'UserController::delete');
    });

    // ─── PRODUK ────────────────────────────────
    $routes->group('produk', function ($routes) {
        $routes->get('/', 'ProdukController::index');
        $routes->post('simpan', 'ProdukController::simpan');
        $routes->post('getData', 'ProdukController::getData');
        $routes->post('update/(:segment)', 'ProdukController::update/$1');
        $routes->post('hapus/(:segment)', 'ProdukController::hapus/$1');
        $routes->post('hapusSemua', 'ProdukController::hapusSemua');
        $routes->get('export', 'ProdukController::export');
        $routes->get('downloadTemplate', 'ProdukController::downloadTemplate');
        $routes->post('prosesImport', 'ProdukController::prosesImport');
    });

    // ─── PENJUALAN ─────────────────────────────
    $routes->group('penjualan', function ($routes) {
        $routes->get('/', 'PenjualanController::index');
        $routes->post('simpan', 'PenjualanController::simpan');
        $routes->post('getData', 'PenjualanController::getData');
        $routes->post('update/(:segment)', 'PenjualanController::update/$1');
        $routes->post('hapus/(:segment)', 'PenjualanController::hapus/$1');
        $routes->post('hapusSemua', 'PenjualanController::hapusSemua');
        $routes->get('export', 'PenjualanController::export');
        $routes->get('downloadTemplate', 'PenjualanController::downloadTemplate');
        $routes->post('prosesImport', 'PenjualanController::prosesImport');
    });
});

// ─── DATA TRAINING ─────────────────────────
$routes->group('training', function ($routes) {
    $routes->get('/',                    'TrainingController::index');
    $routes->post('simpan',              'TrainingController::simpan');
    $routes->post('getData',             'TrainingController::getData');
    $routes->post('update/(:segment)',   'TrainingController::update/$1');
    $routes->post('hapus/(:segment)',    'TrainingController::hapus/$1');
    $routes->post('hapusSemua',          'TrainingController::hapusSemua');
    $routes->post('generate',            'TrainingController::generate');
    $routes->get('proses',               'TrainingController::prosesView');
    $routes->post('proses',              'TrainingController::proses');
    $routes->get('status',               'TrainingController::status');
    $routes->get('riwayat_model',        'TrainingController::riwayatModel');
    $routes->get('export',               'TrainingController::export');
    $routes->get('downloadTemplate',     'TrainingController::downloadTemplate');
    $routes->post('prosesImport',        'TrainingController::prosesImport');
    $routes->get('model/detail/(:num)',  'TrainingController::detailModel/$1');
    $routes->post('model/aktifkan',      'TrainingController::aktifkanModel');
    $routes->post('model/arsipkan',      'TrainingController::arsipkanModel');
    $routes->get('model/diagram/(:num)/(:segment)', 'TrainingController::serveDiagram/$1/$2');
});

// ─── PREDIKSI ─────────────────────────────────────────────────────────────────

$routes->group('prediksi', static function ($routes) {
    $routes->get('/',                'PrediksiController::index');
    $routes->get('jalankan',         'PrediksiController::jalankan');
    $routes->post('jalankan',        'PrediksiController::prosesJalankan');
    $routes->get('riwayat',          'PrediksiController::riwayat');
    $routes->get('akurasi',          'PrediksiController::akurasi');
    $routes->get('detail/(:num)',    'PrediksiController::detail/$1');
    $routes->post('sinkron-aktual',  'PrediksiController::sinkronAktual');  // ← BARU
});

// ─── LAPORAN ──────────────────────────────────────────────────────────────────
$routes->group('laporan', static function ($routes) {
    $routes->get('bulanan',      'LaporanController::bulanan');
    $routes->get('export',       'LaporanController::export');
    $routes->post('downloadPdf', 'LaporanController::downloadPdf');
    $routes->post('downloadExcel', 'LaporanController::downloadExcel');
});

// ─── PENGATURAN ───────────────────────────────────────────────────────────────
$routes->group('pengaturan', static function ($routes) {
    $routes->get('/',            'PengaturanController::index');
    $routes->post('simpan',      'PengaturanController::simpan');
});

// ─── NOTIFIKASI ───────────────────────────────────────────────────────────────
$routes->get('notifikasi',              'NotifikasiController::index');
$routes->post('notifikasi/bacaSemua',   'NotifikasiController::bacaSemua');

// ─── PROFIL ───────────────────────────────────────────────────────────────────
$routes->get('profil',           'ProfilController::index');
$routes->post('profil/update',   'ProfilController::update');

// ─── AUTH ─────────────────────────────────────────────────────────────────────
$routes->group('auth', static function ($routes) {
    $routes->get('login',         'AuthController::login');
    $routes->post('prosesLogin',  'AuthController::prosesLogin');
    $routes->get('logout',        'AuthController::logout');
});

// ─── ABOUT ────────────────────────────────────────────────────────────────────
$routes->get('about', 'AboutController::index');
