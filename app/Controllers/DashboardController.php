<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $data = [
            'title'       => 'Dashboard',
            'active_menu' => 'dashboard',

            // Statistik ringkas — ganti dengan query model nyata
            'stats' => [
                [
                    'label' => 'Total Pengguna',
                    'value' => 1_245,
                    'icon'  => 'bi-people-fill',
                    'color' => 'primary',
                ],
                [
                    'label' => 'Transaksi Hari Ini',
                    'value' => 87,
                    'icon'  => 'bi-cart-check-fill',
                    'color' => 'success',
                ],
                [
                    'label' => 'Laporan Pending',
                    'value' => 14,
                    'icon'  => 'bi-file-earmark-text-fill',
                    'color' => 'warning',
                ],
                [
                    'label' => 'Notifikasi',
                    'value' => $this->globalData['notif_count'],
                    'icon'  => 'bi-bell-fill',
                    'color' => 'danger',
                ],
            ],

            // Aktivitas terbaru — ganti dengan query model nyata
            'recent_activities' => [
                ['user' => 'Budi Santoso',  'action' => 'Menambah produk baru',       'time' => '5 menit lalu'],
                ['user' => 'Siti Rahayu',   'action' => 'Mengupdate profil',           'time' => '20 menit lalu'],
                ['user' => 'Ahmad Fauzi',   'action' => 'Membuat laporan bulanan',     'time' => '1 jam lalu'],
                ['user' => 'Dewi Lestari',  'action' => 'Menghapus data transaksi',    'time' => '2 jam lalu'],
                ['user' => 'Rizky Pratama', 'action' => 'Login ke sistem',             'time' => '3 jam lalu'],
            ],
        ];

        return $this->renderPage('pages/dashboard/index', $data);
    }
}