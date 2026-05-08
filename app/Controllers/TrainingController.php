<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ModelTrainingModel;

class TrainingController extends BaseController
{
    protected ModelTrainingModel $modelTrainingModel;

    public function __construct()
    {
        $this->modelTrainingModel = new ModelTrainingModel();
        helper(['form']);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // INDEX → redirect ke halaman proses training
    // ══════════════════════════════════════════════════════════════════════════
    public function index()
    {
        return redirect()->to(base_url('training/proses'));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HALAMAN PROSES TRAINING (GET)
    // ══════════════════════════════════════════════════════════════════════════
    public function prosesView(): string
    {
        $modelAktif = $this->modelTrainingModel->getAktif();
        $ringkasan  = $this->modelTrainingModel->getRingkasan();

        // Ambil total record & produk langsung dari DB penjualan
        $db = \Config\Database::connect();
        $summary = $db->query("
            SELECT
                COUNT(*)                                     AS total_record,
                COUNT(DISTINCT nama_produk)                  AS total_produk,
                MIN(YEAR(tanggal))                           AS tahun_awal,
                MAX(YEAR(tanggal))                           AS tahun_akhir
            FROM penjualan
        ")->getRowArray();

        // Status file JSON (untuk deteksi running saat refresh)
        $statusFile = ROOTPATH . 'python/models/train_status.json';
        $lastStatus = null;
        if (file_exists($statusFile)) {
            $raw = file_get_contents($statusFile);
            $dec = json_decode($raw, true);
            if ($dec && ($dec['status'] ?? '') !== 'running') {
                $lastStatus = $dec;
            }
        }

        return $this->renderPage('pages/training/proses', [
            'title'      => 'Jalankan Training',
            'page_title' => 'Training Model',
            'summary'    => $summary,
            'modelAktif' => $modelAktif,
            'ringkasan'  => $ringkasan,
            'lastStatus' => $lastStatus,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RIWAYAT MODEL
    // ══════════════════════════════════════════════════════════════════════════
    public function riwayatModel(): string
    {
        $modelAktif = $this->modelTrainingModel->getAktif();
        $riwayat    = $this->modelTrainingModel->getSukses();
        $ringkasan  = $this->modelTrainingModel->getRingkasan();

        return $this->renderPage('pages/training/riwayat_model', [
            'title'      => 'Riwayat Model Training',
            'page_title' => 'Riwayat Model',
            'modelAktif' => $modelAktif,
            'riwayat'    => $riwayat,
            'ringkasan'  => $ringkasan,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AJAX — Mulai training (POST training/proses)
    // ══════════════════════════════════════════════════════════════════════════
    public function proses(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        // ── Cek data penjualan tersedia ────────────────────────────────────
        $db    = \Config\Database::connect();
        $count = $db->table('penjualan')->countAll();
        if ($count === 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Data penjualan kosong. Tambahkan data penjualan terlebih dahulu.',
            ]);
        }

        // ── Path config ────────────────────────────────────────────────────
        $pythonBin  = env('PYTHON_BIN', 'python3');
        $scriptPath = ROOTPATH . 'python/train_rf.py';
        $modelDir   = ROOTPATH . 'python/models';
        $logFile    = ROOTPATH . 'python/logs/train.log';
        $statusFile = $modelDir . '/train_status.json';

        foreach ([$modelDir, dirname($logFile)] as $dir) {
            if (! is_dir($dir)) mkdir($dir, 0755, true);
        }

        // ── Cek running ────────────────────────────────────────────────────
        if (file_exists($statusFile)) {
            $prev = json_decode(file_get_contents($statusFile), true);
            if (($prev['status'] ?? '') === 'running') {
                return $this->response->setJSON([
                    'status'  => 'warning',
                    'message' => 'Proses training sedang berjalan. Tunggu hingga selesai.',
                ]);
            }
        }

        // ── Ringkasan data penjualan untuk snapshot ────────────────────────
        $summary = $db->query("
            SELECT
                COUNT(*)                    AS total_record,
                COUNT(DISTINCT nama_produk) AS total_produk
            FROM penjualan
        ")->getRowArray();

        $userId = session()->get('id_user');

        // ── Buat baris model_training (status = running) ───────────────────
        $trainingId = $this->modelTrainingModel->buatSesi([
            'mulai_at'        => date('Y-m-d H:i:s'),
            'total_record'    => (int) ($summary['total_record'] ?? 0),
            'total_produk'    => (int) ($summary['total_produk'] ?? 0),
            'total_fitur'     => 16,
            'cv_splits'       => 5,
            'dibuat_oleh'     => $userId ?: null,
            'config_snapshot' => json_encode([
                'python_bin'  => $pythonBin,
                'script_path' => $scriptPath,
                'model_dir'   => $modelDir,
                'started_by'  => 'web',
                'source'      => 'penjualan',   // preprocessing dilakukan di Python
            ]),
        ]);

        // ── Tulis status awal ke JSON ──────────────────────────────────────
        file_put_contents($statusFile, json_encode([
            'status'      => 'running',
            'training_id' => $trainingId,
            'message'     => 'Proses training dimulai...',
            'updated_at'  => date('Y-m-d H:i:s'),
        ], JSON_PRETTY_PRINT));

        // ── Ambil konfigurasi DB ───────────────────────────────────────────
        $dbConf = config('Database')->default;
        $dbHost = $dbConf['hostname'] ?? '127.0.0.1';
        $dbPort = $dbConf['port']     ?? 3306;
        $dbName = $dbConf['database'] ?? 'mi_store';
        $dbUser = $dbConf['username'] ?? 'root';
        $dbPass = $dbConf['password'] ?? '';

        // ── Bangun command ─────────────────────────────────────────────────
        // Script Python bertanggung jawab membaca penjualan, preprocessing,
        // lalu langsung training — tidak perlu data_training.
        $cmd = sprintf(
            '%s %s --training-id %d --db-host %s --db-port %d --db-name %s --db-user %s --db-pass %s --model-dir %s --log-file %s',
            escapeshellcmd($pythonBin),
            escapeshellarg($scriptPath),
            (int) $trainingId,
            escapeshellarg($dbHost),
            (int) $dbPort,
            escapeshellarg($dbName),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($modelDir),
            escapeshellarg($logFile)
        );

        // ── Jalankan non-blocking ──────────────────────────────────────────
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen('start /B ' . $cmd, 'r'));
        } else {
            exec($cmd . ' > /dev/null 2>&1 &');
        }

        log_message('info', "[Training] ID={$trainingId} command: {$cmd}");

        return $this->response->setJSON([
            'status'      => 'success',
            'training_id' => $trainingId,
            'message'     => 'Proses training dimulai. Pantau status di halaman ini.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AJAX — Polling status (GET training/status)
    // ══════════════════════════════════════════════════════════════════════════
    public function status(): \CodeIgniter\HTTP\ResponseInterface
    {
        $statusFile = ROOTPATH . 'python/models/train_status.json';

        if (! file_exists($statusFile)) {
            return $this->response->setJSON(['status' => 'idle', 'message' => 'Belum ada proses training.']);
        }

        $data = json_decode(file_get_contents($statusFile), true);
        if (! $data) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Status file tidak dapat dibaca.']);
        }

        // Kalau success & ada training_id → sinkron DB
        if ($data['status'] === 'success' && ! empty($data['training_id'])) {
            $id  = (int) $data['training_id'];
            $row = $this->modelTrainingModel->find($id);
            if ($row && $row['status'] === 'running') {
                $this->modelTrainingModel->tandaiSukses($id, $data);
            }
        }

        return $this->response->setJSON($data);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AJAX — Aktifkan model (POST training/model/aktifkan)
    // ══════════════════════════════════════════════════════════════════════════
    public function aktifkanModel(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $id = (int) $this->request->getPost('id');
        if (! $id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid.']);
        }

        $model = $this->modelTrainingModel->find($id);
        if (! $model || $model['status'] !== 'success') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Model tidak ditemukan atau belum sukses.']);
        }

        $ok = $this->modelTrainingModel->aktifkan($id);
        if (! $ok) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal mengaktifkan model.']);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => "Model {$model['versi']} berhasil diaktifkan sebagai model prediksi.",
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AJAX — Arsipkan model (POST training/model/arsipkan)
    // ══════════════════════════════════════════════════════════════════════════
    public function arsipkanModel(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $id    = (int) $this->request->getPost('id');
        $model = $this->modelTrainingModel->find($id);

        if (! $model) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Model tidak ditemukan.']);
        }
        if ((int) $model['is_active'] === 1) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Model aktif tidak bisa diarsipkan. Aktifkan model lain terlebih dahulu.',
            ]);
        }

        $this->modelTrainingModel->arsipkan($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => "Model {$model['versi']} berhasil diarsipkan.",
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AJAX — Detail model (GET training/model/detail/{id})
    // ══════════════════════════════════════════════════════════════════════════
    public function detailModel(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $model = $this->modelTrainingModel->find($id);
        if (! $model) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Model tidak ditemukan.']);
        }

        $model['best_params']        = json_decode($model['best_params']        ?? '{}', true);
        $model['feature_importance'] = json_decode($model['feature_importance'] ?? '{}', true);

        $db = \Config\Database::connect();

        // Ambil diagram dari tabel model_training_diagram
        $diagRows = $db->table('model_training_diagram')
            ->where('training_id', $id)
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        $diagramPaths = [];
        if (! empty($diagRows)) {
            foreach ($diagRows as $row) {
                $diagramPaths[$row['diagram_key']] = $row['path'];
            }
        } else {
            $diagramPaths = json_decode($model['diagram_paths'] ?? '{}', true) ?: [];
        }

        $model['diagram_paths'] = $diagramPaths;

        $logs = $db->table('model_training_log')
            ->where('training_id', $id)
            ->orderBy('logged_at', 'ASC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $model,
            'logs'   => $logs,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Serve file PNG diagram ke browser
    // GET training/model/diagram/{id}/{key}
    // ══════════════════════════════════════════════════════════════════════════
    public function serveDiagram(int $id, string $key): \CodeIgniter\HTTP\ResponseInterface
    {
        $db  = \Config\Database::connect();

        $row = $db->table('model_training_diagram')
            ->where('training_id', $id)
            ->where('diagram_key', $key)
            ->get()->getRowArray();

        if (! $row) {
            $model = $this->modelTrainingModel->find($id);
            if ($model) {
                $diagramPaths = json_decode($model['diagram_paths'] ?? '{}', true);
                if (! empty($diagramPaths[$key])) {
                    $row = ['path' => $diagramPaths[$key]];
                }
            }
        }

        if (! $row) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => "Diagram '{$key}' tidak ditemukan untuk training ID {$id}"]);
        }

        $path = str_replace('\\', '/', $row['path']);

        if (! file_exists($path)) {
            if (preg_match('#python[/\\\\](.+)$#i', $row['path'], $m)) {
                $path = ROOTPATH . 'python/' . str_replace('\\', '/', $m[1]);
            }
        }

        if (! file_exists($path)) {
            log_message('error', "[serveDiagram] File tidak ada: {$path} (original: {$row['path']})");
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'File diagram tidak ada di server', 'path_debug' => $path]);
        }

        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody(file_get_contents($path));
    }
}