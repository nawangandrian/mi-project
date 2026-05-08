<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PrediksiModel;
use App\Models\ModelTrainingModel;
use App\Models\ProdukModel;

/**
 * PrediksiController
 * Mengelola prediksi penjualan menggunakan model Random Forest.
 *
 * Routes:
 *   GET  prediksi/                      → index()
 *   GET  prediksi/jalankan              → jalankan()
 *   POST prediksi/jalankan              → prosesJalankan()
 *   GET  prediksi/riwayat               → riwayat()
 *   GET  prediksi/akurasi               → akurasi()
 *   GET  prediksi/detail/:id            → detail()
 *   POST prediksi/sinkron-aktual        → sinkronAktual()   ← BARU
 */
class PrediksiController extends BaseController
{
    protected PrediksiModel      $prediksiModel;
    protected ModelTrainingModel $modelTrainingModel;
    protected ProdukModel        $produkModel;

    public function __construct()
    {
        $this->prediksiModel      = new PrediksiModel();
        $this->modelTrainingModel = new ModelTrainingModel();
        $this->produkModel        = new ProdukModel();
        helper(['form']);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // INDEX — Halaman utama prediksi
    // ══════════════════════════════════════════════════════════════════════════

    public function index(): string
    {
        $tahun = (int) ($this->request->getGet('tahun') ?: 0);
        $bulan = (int) ($this->request->getGet('bulan') ?: 0);

        $modelAktif = $this->modelTrainingModel->getAktif();

        $periode = null;
        if ($tahun > 0 && $bulan > 0) {
            $prediksiList = $this->prediksiModel->getByPeriode($tahun, $bulan);
            $periode      = ['tahun' => $tahun, 'bulan' => $bulan];
        } else {
            $prediksiList = $this->prediksiModel->getTerbaru();
            $periode      = $this->prediksiModel->getPeriodeTerbaru();
        }

        return $this->renderPage('pages/prediksi/index', [
            'title'        => 'Prediksi Penjualan',
            'page_title'   => 'Prediksi Penjualan',
            'prediksiList' => $prediksiList,
            'modelAktif'   => $modelAktif,
            'periode'      => $periode,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // JALANKAN — Halaman form & status jalankan prediksi
    // ══════════════════════════════════════════════════════════════════════════

    public function jalankan(): string
    {
        $modelAktif   = $this->modelTrainingModel->getAktif();
        $produks      = $this->produkModel->getActive();
        $lastPrediksi = $this->prediksiModel->getInfoTerakhir();

        return $this->renderPage('pages/prediksi/jalankan', [
            'title'        => 'Jalankan Prediksi',
            'page_title'   => 'Jalankan Model',
            'modelAktif'   => $modelAktif,
            'produks'      => $produks,
            'lastPrediksi' => $lastPrediksi,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PROSES JALANKAN — Inferensi model RF via Python script (AJAX POST)
    // ══════════════════════════════════════════════════════════════════════════

    public function prosesJalankan(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        // ── Validasi model aktif ─────────────────────────────────────────────
        $modelAktif = $this->modelTrainingModel->getAktif();
        if (! $modelAktif) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Belum ada model aktif. Latih model Random Forest terlebih dahulu.',
            ]);
        }

        $pathModel = $modelAktif['path_model'] ?? '';
        if (empty($pathModel) || ! file_exists($pathModel)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'File model (.pkl) tidak ditemukan di server. Latih ulang model.',
            ]);
        }

        // ── Parameter ────────────────────────────────────────────────────────
        $bulan     = (int) $this->request->getPost('bulan_prediksi');
        $tahun     = (int) $this->request->getPost('tahun_prediksi');
        $mode      = $this->request->getPost('produk_mode') ?: 'semua';
        $overwrite = (int) $this->request->getPost('overwrite');

        if ($bulan < 1 || $bulan > 12) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Bulan tidak valid.']);
        }
        if ($tahun < 2020 || $tahun > 2100) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tahun tidak valid.']);
        }

        if ($mode === 'pilih') {
            $produkList = $this->request->getPost('produk_list') ?? [];
            if (empty($produkList)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Pilih minimal satu produk.',
                ]);
            }
        } else {
            $produkRaw  = $this->produkModel->getActive();
            $produkList = array_column($produkRaw, 'nama_produk');
        }

        if (empty($produkList)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Tidak ada produk yang tersedia untuk diprediksi.',
            ]);
        }

        // ── Hapus data lama jika overwrite ───────────────────────────────────
        if ($overwrite) {
            $this->prediksiModel->hapusByPeriode($tahun, $bulan);
        }

        // ── Path & config ────────────────────────────────────────────────────
        $pythonBin   = env('PYTHON_BIN', 'python3');
        $scriptPath  = ROOTPATH . 'python/predict_rf.py';
        $modelDir    = ROOTPATH . 'python/models';
        $logFile     = ROOTPATH . 'python/logs/predict.log';
        $resultFile  = $modelDir . '/predict_result.json';
        $encoderPath = $modelAktif['path_encoder'] ?? ($modelDir . '/label_encoder.pkl');

        foreach ([$modelDir, dirname($logFile)] as $dir) {
            if (! is_dir($dir)) mkdir($dir, 0755, true);
        }

        $produkFile = $modelDir . '/predict_produk_list.json';
        file_put_contents($produkFile, json_encode([
            'produk_list'    => $produkList,
            'bulan_prediksi' => $bulan,
            'tahun_prediksi' => $tahun,
        ], JSON_PRETTY_PRINT));

        $dbConf = config('Database')->default;

        $cmd = sprintf(
            '%s %s --model-path %s --encoder-path %s --produk-file %s --result-file %s --db-host %s --db-port %d --db-name %s --db-user %s --db-pass %s --log-file %s 2>&1',
            escapeshellcmd($pythonBin),
            escapeshellarg($scriptPath),
            escapeshellarg($pathModel),
            escapeshellarg($encoderPath),
            escapeshellarg($produkFile),
            escapeshellarg($resultFile),
            escapeshellarg($dbConf['hostname'] ?? '127.0.0.1'),
            (int) ($dbConf['port'] ?? 3306),
            escapeshellarg($dbConf['database'] ?? 'mi_store'),
            escapeshellarg($dbConf['username'] ?? 'root'),
            escapeshellarg($dbConf['password'] ?? ''),
            escapeshellarg($logFile)
        );

        $startTs = microtime(true);
        exec($cmd, $output, $exitCode);
        $durasi = round(microtime(true) - $startTs, 2);

        log_message('info', "[Prediksi] cmd={$cmd} | exit={$exitCode} | durasi={$durasi}s");

        if ($exitCode !== 0 || ! file_exists($resultFile)) {
            $errDetail = implode("\n", $output);
            log_message('error', "[Prediksi] Python error: {$errDetail}");

            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Script prediksi Python gagal dijalankan.',
                'detail'  => $errDetail,
            ]);
        }

        $result = json_decode(file_get_contents($resultFile), true);

        if (! $result || ($result['status'] ?? '') === 'error') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $result['message'] ?? 'Prediksi gagal.',
                'detail'  => $result['detail']  ?? '',
            ]);
        }

        // ── Simpan hasil prediksi ─────────────────────────────────────────────
        // bulkUpsert() di model sudah memanggil sinkronAktual() secara otomatis,
        // sehingga qty_aktual & selisih langsung terisi jika data penjualan ada.
        $predictions = $result['predictions'] ?? [];
        $savedCount  = 0;

        if (! empty($predictions)) {
            $savedCount = $this->prediksiModel->bulkUpsert($predictions, $tahun, $bulan);
        }

        @unlink($produkFile);
        @unlink($resultFile);

        // Hitung berapa produk yang langsung terverifikasi (qty_aktual terisi)
        $summary = $this->prediksiModel->getSummaryPeriode($tahun, $bulan);
        $terverifikasi = (int) ($summary['total_terverifikasi'] ?? 0);

        $message = "Prediksi berhasil untuk {$savedCount} produk.";
        if ($terverifikasi > 0) {
            $message .= " {$terverifikasi} produk langsung terverifikasi dari data penjualan.";
        }

        return $this->response->setJSON([
            'status'        => 'success',
            'message'       => $message,
            'total'         => $savedCount,
            'terverifikasi' => $terverifikasi,
            'durasi'        => $durasi,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SINKRON AKTUAL — Isi qty_aktual manual dari data penjualan (AJAX POST)
    // POST prediksi/sinkron-aktual
    // ══════════════════════════════════════════════════════════════════════════

    public function sinkronAktual(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $tahun = (int) $this->request->getPost('tahun');
        $bulan = (int) $this->request->getPost('bulan');

        if ($bulan < 1 || $bulan > 12 || $tahun < 2020) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Parameter periode tidak valid.']);
        }

        $count = $this->prediksiModel->sinkronAktual($tahun, $bulan);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $count > 0
                ? "{$count} produk berhasil disinkronkan dengan data penjualan."
                : 'Tidak ada data penjualan yang cocok untuk periode ini.',
            'total'   => $count,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RIWAYAT — Halaman riwayat prediksi semua periode
    // ══════════════════════════════════════════════════════════════════════════

    public function riwayat(): string
    {
        $modelAktif    = $this->modelTrainingModel->getAktif();
        $daftarPeriode = $this->prediksiModel->getDaftarPeriode();

        $tahunFilter = (int) ($this->request->getGet('tahun') ?: 0);
        $bulanFilter = (int) ($this->request->getGet('bulan') ?: 0);

        if ($tahunFilter > 0 && $bulanFilter > 0) {
            $prediksiList = $this->prediksiModel->getByPeriode($tahunFilter, $bulanFilter);
            $periodeAktif = ['tahun' => $tahunFilter, 'bulan' => $bulanFilter];
        } else {
            $prediksiList = $this->prediksiModel->getRiwayat(500);
            $periodeAktif = null;
        }

        $ringkasanProduk = $this->prediksiModel->getRingkasanPerProduk();

        return $this->renderPage('pages/prediksi/riwayat', [
            'title'           => 'Riwayat Prediksi',
            'page_title'      => 'Riwayat Prediksi',
            'prediksiList'    => $prediksiList,
            'daftarPeriode'   => $daftarPeriode,
            'periodeAktif'    => $periodeAktif,
            'ringkasanProduk' => $ringkasanProduk,
            'modelAktif'      => $modelAktif,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AKURASI — Halaman evaluasi model
    // ══════════════════════════════════════════════════════════════════════════

    public function akurasi(): string
    {
        $modelAktif    = $this->modelTrainingModel->getAktifDetail();
        $riwayatModels = $this->modelTrainingModel->getTopSukses(10);

        return $this->renderPage('pages/prediksi/akurasi', [
            'title'         => 'Evaluasi Model',
            'page_title'    => 'Evaluasi Model',
            'modelAktif'    => $modelAktif,
            'riwayatModels' => $riwayatModels,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DETAIL — Detail prediksi satu record (AJAX)
    // ══════════════════════════════════════════════════════════════════════════

    public function detail(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $idStr = 'PDK-' . str_pad((string) $id, 8, '0', STR_PAD_LEFT);
        $data  = $this->prediksiModel->find($idStr);

        if (! $data) {
            return $this->response->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'Data prediksi tidak ditemukan.']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data,
        ]);
    }
}