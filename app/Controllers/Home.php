<?php

namespace App\Controllers;

use App\Models\ModelTrainingModel;
use App\Models\PenjualanModel;
use App\Models\ProdukModel;

class Home extends BaseController
{
    public function index(): string
    {
        $modelTrainingModel = new ModelTrainingModel();
        $penjualanModel     = new PenjualanModel();
        $produkModel        = new ProdukModel();
        $db                 = \Config\Database::connect();

        // ── Model aktif ────────────────────────────────────────────────────────
        $modelAktif = $modelTrainingModel->getAktif();

        // ── Ringkasan statistik dari DB ────────────────────────────────────────
        $statsRow = $db->query("
            SELECT
                COUNT(*)                    AS total_transaksi,
                COUNT(DISTINCT nama_produk) AS total_produk,
                COALESCE(SUM(qty), 0)       AS total_qty,
                MIN(YEAR(tanggal))          AS tahun_min,
                MAX(YEAR(tanggal))          AS tahun_max
            FROM penjualan
        ")->getRowArray();

        // Total brand (distinct dari kolom brand jika ada, fallback dari nama_produk prefix)
        $totalBrand = (int) $db->query("
            SELECT COUNT(DISTINCT
                TRIM(SUBSTRING_INDEX(nama_produk, ' ', 1))
            ) AS total FROM penjualan
        ")->getRowArray()['total'];

        // ── Ringkasan model training ───────────────────────────────────────────
        $ringkasanModel = $modelTrainingModel->getRingkasan();

        // ── Jumlah produk aktif ────────────────────────────────────────────────
        $totalProdukAktif = $produkModel->where('is_active', 1)->countAllResults();

        return view('welcome_message', [
            'modelAktif'      => $modelAktif,
            'ringkasanModel'  => $ringkasanModel,
            'statsRow'        => $statsRow,
            'totalBrand'      => $totalBrand,
            'totalProdukAktif'=> $totalProdukAktif,
        ]);
    }
}