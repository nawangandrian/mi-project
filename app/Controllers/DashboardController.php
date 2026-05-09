<?php

namespace App\Controllers;

use App\Models\PenjualanModel;
use App\Models\ProdukModel;
use App\Models\ModelTrainingModel;
use App\Models\PrediksiModel;

class DashboardController extends BaseController
{
    protected PenjualanModel      $penjualanModel;
    protected ProdukModel         $produkModel;
    protected ModelTrainingModel  $modelTrainingModel;
    protected PrediksiModel       $prediksiModel;

    public function __construct()
    {
        $this->penjualanModel     = new PenjualanModel();
        $this->produkModel        = new ProdukModel();
        $this->modelTrainingModel = new ModelTrainingModel();
        $this->prediksiModel      = new PrediksiModel();
    }

    public function index(): string
    {
        $db = \Config\Database::connect();

        // ── Filter periode dari GET ────────────────────────────────────────────
        $filterTahun = (int) ($this->request->getGet('tahun') ?: 0);
        $filterBulan = (int) ($this->request->getGet('bulan') ?: 0);

        // Rentang tahun tersedia
        $tahunRange = $db->query("
            SELECT MIN(YEAR(tanggal)) AS tahun_min, MAX(YEAR(tanggal)) AS tahun_max
            FROM penjualan
        ")->getRowArray();
        $tahunMin = (int) ($tahunRange['tahun_min'] ?? date('Y'));
        $tahunMax = (int) ($tahunRange['tahun_max'] ?? date('Y'));

        // Kondisi WHERE berdasarkan filter
        if ($filterTahun > 0 && $filterBulan > 0) {
            $whereKlausa  = "WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ?";
            $filterParams = [$filterTahun, $filterBulan];
            $filterLabel  = date('F Y', mktime(0, 0, 0, $filterBulan, 1, $filterTahun));
        } elseif ($filterTahun > 0) {
            $whereKlausa  = "WHERE YEAR(tanggal) = ?";
            $filterParams = [$filterTahun];
            $filterLabel  = "Tahun {$filterTahun}";
        } else {
            $whereKlausa  = '';
            $filterParams = [];
            $filterLabel  = 'Semua Periode';
        }

        // ── 1. Ringkasan utama (ikut filter) ─────────────────────────────────
        $summaryRow = $db->query("
            SELECT
                COUNT(*)                        AS total_transaksi,
                COALESCE(SUM(qty * harga), 0)   AS total_omset,
                COALESCE(SUM(qty), 0)           AS total_qty,
                COUNT(DISTINCT nama_produk)     AS total_produk_terjual
            FROM penjualan {$whereKlausa}
        ", $filterParams)->getRowArray();

        $totalProdukAktif = $this->produkModel->where('is_active', 1)->countAllResults();

        // ── 2. Bulan ini vs bulan lalu (selalu real-time, tanpa filter) ───────
        $bulanIni   = date('Y-m');
        $bulanLalu  = date('Y-m', strtotime('-1 month'));

        $omsetBulanIni = (int) $db->query("
            SELECT COALESCE(SUM(qty * harga), 0) AS omset FROM penjualan
            WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?
        ", [$bulanIni])->getRowArray()['omset'];

        $omsetBulanLalu = (int) $db->query("
            SELECT COALESCE(SUM(qty * harga), 0) AS omset FROM penjualan
            WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?
        ", [$bulanLalu])->getRowArray()['omset'];

        $growthOmset = $omsetBulanLalu > 0
            ? round((($omsetBulanIni - $omsetBulanLalu) / $omsetBulanLalu) * 100, 1)
            : null;

        $transaksiIni = (int) $db->query("
            SELECT COUNT(*) AS cnt FROM penjualan
            WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?
        ", [$bulanIni])->getRowArray()['cnt'];

        // ── 3. Tren (adaptif sesuai filter) ──────────────────────────────────
        if ($filterTahun > 0 && $filterBulan > 0) {
            // Tren harian bulan terpilih
            $trenRows = $db->query("
                SELECT
                    DATE_FORMAT(tanggal, '%Y-%m-%d') AS periode,
                    DATE_FORMAT(tanggal, '%d %b')    AS label,
                    SUM(qty * harga) AS omset,
                    SUM(qty)         AS qty,
                    COUNT(*)         AS transaksi
                FROM penjualan
                WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ?
                GROUP BY DATE(tanggal),
                         DATE_FORMAT(tanggal, '%Y-%m-%d'),
                         DATE_FORMAT(tanggal, '%d %b')
                ORDER BY periode ASC
            ", [$filterTahun, $filterBulan])->getResultArray();
            $trenJudul = "Tren Harian — {$filterLabel}";
        } elseif ($filterTahun > 0) {
            // Tren bulanan tahun terpilih
            $trenRows = $db->query("
                SELECT
                    DATE_FORMAT(tanggal, '%Y-%m')  AS periode,
                    DATE_FORMAT(tanggal, '%b %Y')  AS label,
                    SUM(qty * harga) AS omset,
                    SUM(qty)         AS qty,
                    COUNT(*)         AS transaksi
                FROM penjualan
                WHERE YEAR(tanggal) = ?
                GROUP BY DATE_FORMAT(tanggal, '%Y-%m'),
                         DATE_FORMAT(tanggal, '%b %Y')
                ORDER BY periode ASC
            ", [$filterTahun])->getResultArray();
            $trenJudul = "Tren Bulanan — {$filterLabel}";
        } else {
            // Default: 12 bulan terakhir
            $trenRows = $db->query("
                SELECT
                    DATE_FORMAT(tanggal, '%Y-%m')  AS periode,
                    DATE_FORMAT(tanggal, '%b %Y')  AS label,
                    SUM(qty * harga) AS omset,
                    SUM(qty)         AS qty,
                    COUNT(*)         AS transaksi
                FROM penjualan
                WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(tanggal, '%Y-%m'),
                         DATE_FORMAT(tanggal, '%b %Y')
                ORDER BY periode ASC
            ")->getResultArray();
            $trenJudul = "Tren 12 Bulan Terakhir";
        }

        // ── 4. Top 10 produk terlaris (ikut filter) ───────────────────────────
        $topProduk = $db->query("
            SELECT
                nama_produk,
                SUM(qty)         AS total_qty,
                SUM(qty * harga) AS total_omset,
                COUNT(*)         AS total_transaksi
            FROM penjualan {$whereKlausa}
            GROUP BY nama_produk
            ORDER BY total_qty DESC
            LIMIT 10
        ", $filterParams)->getResultArray();

        // ── 5. Distribusi bulanan tahun (filter tahun atau tahun ini) ─────────
        $tahunDistribusi = $filterTahun > 0 ? $filterTahun : (int) date('Y');
        $distribusiRows  = $db->query("
            SELECT
                MONTH(tanggal)             AS bln,
                DATE_FORMAT(tanggal, '%b') AS label,
                SUM(qty * harga)           AS omset,
                SUM(qty)                   AS qty
            FROM penjualan
            WHERE YEAR(tanggal) = ?
            GROUP BY MONTH(tanggal), DATE_FORMAT(tanggal, '%b')
            ORDER BY bln ASC
        ", [$tahunDistribusi])->getResultArray();

        // ── 6. Perbandingan omset antar tahun ─────────────────────────────────
        $omsetPerTahun = $db->query("
            SELECT
                YEAR(tanggal)    AS tahun,
                SUM(qty * harga) AS omset,
                SUM(qty)         AS qty,
                COUNT(*)         AS transaksi
            FROM penjualan
            GROUP BY YEAR(tanggal)
            ORDER BY tahun ASC
        ")->getResultArray();

        // ── 7. Aktivitas 7 hari terakhir (selalu real-time) ───────────────────
        $aktivitasHarian = $db->query("
            SELECT
                DATE(tanggal)                   AS tgl,
                DATE_FORMAT(tanggal, '%d %b')   AS label,
                COUNT(*)                         AS transaksi,
                SUM(qty * harga)                 AS omset,
                SUM(qty)                         AS qty
            FROM penjualan
            WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(tanggal), DATE_FORMAT(tanggal, '%d %b')
            ORDER BY tgl ASC
        ")->getResultArray();

        // ── 8. Model ML ───────────────────────────────────────────────────────
        $modelAktif     = $this->modelTrainingModel->getAktif();
        $ringkasanModel = $this->modelTrainingModel->getRingkasan();

        // ── 9. Prediksi terbaru ────────────────────────────────────────────────
        $periodeTerbaru = $this->prediksiModel->getPeriodeTerbaru();
        $prediksiList   = [];
        if ($periodeTerbaru) {
            $all = $this->prediksiModel->getByPeriode(
                (int) $periodeTerbaru['tahun'],
                (int) $periodeTerbaru['bulan']
            );
            usort($all, fn($a, $b) => ($b['qty_prediksi'] ?? 0) <=> ($a['qty_prediksi'] ?? 0));
            $prediksiList = array_slice($all, 0, 10);
        }

        // ── 10. Promo vs Normal (ikut filter) ─────────────────────────────────
        $promoStats = $db->query("
            SELECT promo, COUNT(*) AS transaksi, SUM(qty * harga) AS omset
            FROM penjualan {$whereKlausa}
            GROUP BY promo
        ", $filterParams)->getResultArray();

        $omsetPromo = $omsetNonPromo = 0;
        foreach ($promoStats as $p) {
            if ((int) $p['promo'] === 1) $omsetPromo    = (int) $p['omset'];
            else                          $omsetNonPromo = (int) $p['omset'];
        }

        return $this->renderPage('pages/dashboard/index', [
            'title'            => 'Dashboard',
            'page_title'       => 'Dashboard',
            'active_menu'      => 'dashboard',

            // Filter
            'filterTahun'      => $filterTahun,
            'filterBulan'      => $filterBulan,
            'filterLabel'      => $filterLabel,
            'tahunMin'         => $tahunMin,
            'tahunMax'         => $tahunMax,

            // Stat cards
            'summaryRow'       => $summaryRow,
            'totalProdukAktif' => $totalProdukAktif,
            'omsetBulanIni'    => $omsetBulanIni,
            'omsetBulanLalu'   => $omsetBulanLalu,
            'growthOmset'      => $growthOmset,
            'transaksiIni'     => $transaksiIni,
            'bulanIni'         => $bulanIni,

            // Chart data
            'trenRows'         => $trenRows,
            'trenJudul'        => $trenJudul,
            'distribusiRows'   => $distribusiRows,
            'tahunDistribusi'  => $tahunDistribusi,
            'aktivitasHarian'  => $aktivitasHarian,
            'omsetPerTahun'    => $omsetPerTahun,

            // List/tabel
            'topProduk'        => $topProduk,
            'prediksiList'     => $prediksiList,
            'periodeTerbaru'   => $periodeTerbaru,

            // ML Model
            'modelAktif'       => $modelAktif,
            'ringkasanModel'   => $ringkasanModel,

            // Promo
            'omsetPromo'       => $omsetPromo,
            'omsetNonPromo'    => $omsetNonPromo,
        ]);
    }
}