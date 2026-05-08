<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProdukModel;
use App\Models\PenjualanModel;

/**
 * ImportController
 * Mengelola impor data dari file Excel TRANSAKSI HARIAN MI STORE.
 *
 * Flow:
 *   1. User upload file .xlsx
 *   2. PHP simpan sementara di writable/uploads/import/
 *   3. PHP panggil python/import_excel.py via exec()
 *   4. Python baca Excel → output JSON (produk + penjualan)
 *   5. PHP baca JSON → simpan ke DB lewat Model
 *
 * Routes (tambahkan di app/Config/Routes.php):
 *   GET  import/                   → index()
 *   POST import/proses             → proses()         ← upload + proses
 *   POST import/previewSheets      → previewSheets()  ← list sheet sebelum proses
 *   GET  import/riwayat            → riwayat()
 */
class ImportController extends BaseController
{
    protected ProdukModel   $produkModel;
    protected PenjualanModel $penjualanModel;

    // Path Python
    private string $pythonBin;
    private string $scriptPath;
    private string $uploadDir;
    private string $resultDir;

    public function __construct()
    {
        $this->produkModel    = new ProdukModel();
        $this->penjualanModel = new PenjualanModel();
        helper(['form', 'filesystem']);

        $this->pythonBin  = $this->detectPythonBin();
        $this->scriptPath = ROOTPATH . 'python/import_excel.py';
        $this->uploadDir  = WRITEPATH . 'uploads/import/';
        $this->resultDir  = WRITEPATH . 'uploads/import/results/';

        foreach ([$this->uploadDir, $this->resultDir] as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    private function detectPythonBin(): string
    {
        $bin = env('PYTHON_BIN');
        if (! empty($bin)) {
            return $bin;
        }

        if (stripos(PHP_OS_FAMILY, 'Windows') !== false) {
            return 'python';
        }

        return 'python3';
    }

    // ══════════════════════════════════════════════════════════════════════════
    // INDEX — Halaman utama upload
    // ══════════════════════════════════════════════════════════════════════════

    public function index(): string
    {
        return $this->renderPage('pages/import/index', [
            'title'      => 'Impor Data Excel',
            'page_title' => 'Impor Data dari Excel',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PREVIEW SHEETS — Kembalikan daftar sheet dalam file Excel (AJAX)
    // POST import/previewSheets
    // ══════════════════════════════════════════════════════════════════════════

    public function previewSheets(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $file = $this->request->getFile('file_excel');

        if (! $file || ! $file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak valid.']);
        }

        if ($file->getExtension() !== 'xlsx') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Hanya file .xlsx yang diterima.']);
        }

        // Simpan sementara
        $tempName = 'preview_' . uniqid() . '.xlsx';
        $file->move($this->uploadDir, $tempName);
        $filePath = $this->uploadDir . $tempName;

        // ── PERBAIKAN: Panggil import_excel.py --list-sheets (bukan inline -c) ──
        $cmd = sprintf(
            '%s %s --file %s --list-sheets 2>&1',
            escapeshellcmd($this->pythonBin),
            escapeshellarg($this->scriptPath),
            escapeshellarg($filePath)
        );

        exec($cmd, $output, $exitCode);

        // Bersihkan file temp
        @unlink($filePath);

        // Gabungkan output dan parse JSON
        $rawOutput = implode('', $output);
        $result    = json_decode($rawOutput, true);

        if ($exitCode !== 0 || ! $result || ($result['status'] ?? '') === 'error') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $result['message'] ?? 'Gagal membaca sheet dari file. Pastikan openpyxl terinstall.',
                'detail'  => $rawOutput,
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'sheets' => $result['sheets'] ?? [],
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PROSES — Upload + proses Excel (AJAX POST multipart)
    // POST import/proses
    // ══════════════════════════════════════════════════════════════════════════

    public function proses(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        // ── 1. Validasi file ──────────────────────────────────────────────────
        $file = $this->request->getFile('file_excel');

        if (! $file || ! $file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak valid atau tidak ditemukan.']);
        }

        if ($file->getExtension() !== 'xlsx') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Format file harus .xlsx']);
        }

        if ($file->getSize() > 20 * 1024 * 1024) { // 20 MB
            return $this->response->setJSON(['status' => 'error', 'message' => 'Ukuran file maksimal 20 MB.']);
        }

        // ── 2. Ambil parameter ────────────────────────────────────────────────
        $selectedSheets  = $this->request->getPost('sheets')          ?? [];   // array sheet
        $modeProduk      = $this->request->getPost('mode_produk')     ?? 'skip';  // skip|update|overwrite
        $modePenjualan   = $this->request->getPost('mode_penjualan')  ?? 'skip';  // skip|overwrite
        $detectionMode   = $this->request->getPost('detection_mode')  ?? 'auto';  // auto|manual

        // ── 3. Simpan file ────────────────────────────────────────────────────
        $uniqueName = 'import_' . date('Ymd_His') . '_' . uniqid() . '.xlsx';
        $file->move($this->uploadDir, $uniqueName);
        $filePath   = $this->uploadDir . $uniqueName;
        $resultPath = $this->resultDir . 'result_' . uniqid() . '.json';

        // ── 4. Panggil Python ─────────────────────────────────────────────────
        $sheetsArg = implode(',', array_map('escapeshellarg', $selectedSheets));
        // Untuk --sheets kita tidak bisa escapeshellarg tiap item lalu gabung karena jadi multi-arg
        // Kita pass sebagai satu string CSV
        $sheetsStr = implode(',', $selectedSheets);

        $cmd = sprintf(
            '%s %s --file %s --result %s --sheets %s --mode %s 2>&1',
            escapeshellcmd($this->pythonBin),
            escapeshellarg($this->scriptPath),
            escapeshellarg($filePath),
            escapeshellarg($resultPath),
            escapeshellarg($sheetsStr),
            escapeshellarg($detectionMode)
        );

        $startTs = microtime(true);
        exec($cmd, $outputLines, $exitCode);
        $durasi  = round(microtime(true) - $startTs, 2);
        $logText = implode("\n", $outputLines);

        log_message('info', "[Import] cmd={$cmd} | exit={$exitCode} | durasi={$durasi}s");

        if ($exitCode !== 0 || ! file_exists($resultPath)) {
            @unlink($filePath);
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Proses Python gagal dijalankan.',
                'detail'  => $logText,
            ]);
        }

        // ── 5. Baca hasil JSON ────────────────────────────────────────────────
        $result = json_decode(file_get_contents($resultPath), true);
        @unlink($resultPath);

        if (! $result || ($result['status'] ?? '') === 'error') {
            @unlink($filePath);
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $result['message'] ?? 'Gagal memproses data dari Excel.',
                'detail'  => $result['detail'] ?? $logText,
            ]);
        }

        $produkData    = $result['produk']    ?? [];
        $penjualanData = $result['penjualan'] ?? [];
        $summary       = $result['summary']   ?? [];

        // ── 6. Simpan Produk ──────────────────────────────────────────────────
        $produkBaru    = 0;
        $produkSkip    = 0;
        $produkUpdate  = 0;

        if ($modeProduk === 'overwrite') {
            $this->produkModel->deleteAll();
        }

        $produkToInsert = [];
        foreach ($produkData as $p) {
            $namaProduk = trim($p['nama_produk'] ?? '');
            if (! $namaProduk) continue;

            $exists = $this->produkModel->existsByName($namaProduk);
            if ($exists) {
                if ($modeProduk === 'update') {
                    $this->produkModel->where('nama_produk', $namaProduk)
                        ->set(['is_active' => $p['is_active'] ?? 1, 'updated_at' => date('Y-m-d H:i:s')])
                        ->update();
                    $produkUpdate++;
                } else {
                    $produkSkip++;
                }
            } else {
                $produkToInsert[] = $p;
                $produkBaru++;
            }
        }

        if (! empty($produkToInsert)) {
            $this->produkModel->bulkInsert($produkToInsert);
        }

        // ── 7. Map nama_produk → id_produk ───────────────────────────────────
        $semuaProduk = $this->produkModel->findAll();
        $produkMap   = [];
        foreach ($semuaProduk as $p) {
            $produkMap[$p['nama_produk']] = $p['id_produk'];
        }

        // ── 8. Simpan Penjualan ───────────────────────────────────────────────
        $penjualanBaru = 0;
        $penjualanSkip = 0;

        if ($modePenjualan === 'overwrite') {
            $this->penjualanModel->deleteAll();
        }

        $penjualanToInsert = [];
        foreach ($penjualanData as $row) {
            $noNota     = trim($row['no_nota']     ?? '');
            $namaProduk = trim($row['nama_produk'] ?? '');
            if (! $noNota || ! $namaProduk) continue;

            if ($modePenjualan !== 'overwrite') {
                $exists = $this->penjualanModel->existsByNotaProduk($noNota, $namaProduk);
                if ($exists) {
                    $penjualanSkip++;
                    continue;
                }
            }

            $penjualanToInsert[] = [
                'no_nota'     => $noNota,
                'id_produk'   => $produkMap[$namaProduk] ?? null,
                'nama_produk' => $namaProduk,
                'qty'         => (int) ($row['qty']   ?? 1),
                'harga'       => (int) ($row['harga'] ?? 0),
                'tanggal'     => $row['tanggal'] ?? date('Y-m-d'),
                'promo'       => (int) ($row['promo'] ?? 0),
            ];
            $penjualanBaru++;

            // Batch insert tiap 500 baris agar tidak OOM
            if (count($penjualanToInsert) >= 500) {
                $this->penjualanModel->bulkInsert($penjualanToInsert);
                $penjualanToInsert = [];
            }
        }

        if (! empty($penjualanToInsert)) {
            $this->penjualanModel->bulkInsert($penjualanToInsert);
        }

        // ── 9. Simpan log riwayat import ─────────────────────────────────────
        $this->simpanLog([
            'file_name'        => $file->getClientName(),
            'sheets'           => implode(', ', $summary['sheets_processed'] ?? []),
            'total_transaksi'  => $summary['total_transaksi'] ?? 0,
            'produk_baru'      => $produkBaru,
            'produk_skip'      => $produkSkip,
            'produk_update'    => $produkUpdate,
            'penjualan_baru'   => $penjualanBaru,
            'penjualan_skip'   => $penjualanSkip,
            'durasi'           => $durasi,
            'status'           => 'success',
        ]);

        // Bersihkan file upload setelah diproses
        @unlink($filePath);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => "Import selesai dalam {$durasi}s.",
            'detail'  => [
                'sheets_processed' => $summary['sheets_processed'] ?? [],
                'sheets_failed'    => $summary['sheets_failed']    ?? [],
                'produk' => [
                    'baru'   => $produkBaru,
                    'skip'   => $produkSkip,
                    'update' => $produkUpdate,
                ],
                'penjualan' => [
                    'baru' => $penjualanBaru,
                    'skip' => $penjualanSkip,
                ],
            ],
            'durasi' => $durasi,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RIWAYAT — Halaman riwayat import
    // ══════════════════════════════════════════════════════════════════════════

    public function riwayat(): string
    {
        $logFile = $this->resultDir . 'import_log.json';
        $logs    = [];
        if (file_exists($logFile)) {
            $logs = json_decode(file_get_contents($logFile), true) ?? [];
        }

        // Add index as ID for each log entry
        foreach ($logs as &$log) {
            if (! isset($log['id_import_log'])) {
                $log['id_import_log'] = md5($log['created_at'] . $log['file_name']);
            }
        }

        return $this->renderPage('pages/import/riwayat', [
            'title'      => 'Riwayat Import',
            'page_title' => 'Riwayat Import Excel',
            'logs'       => array_reverse($logs),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DELETE LOG — Hapus riwayat import tertentu (AJAX POST)
    // ══════════════════════════════════════════════════════════════════════════

    public function deleteLog(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Forbidden']);
        }

        $json = $this->request->getJSON();
        $logId = $json->id ?? null;

        if (! $logId) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID riwayat tidak ditemukan.']);
        }

        $logFile = $this->resultDir . 'import_log.json';
        if (! file_exists($logFile)) {
            return $this->response->setJSON(['success' => false, 'message' => 'File log tidak ditemukan.']);
        }

        $logs = json_decode(file_get_contents($logFile), true) ?? [];

        // Cari dan hapus entry dengan ID yang cocok
        $found = false;
        foreach ($logs as $key => $log) {
            $currentId = $log['id_import_log'] ?? md5($log['created_at'] . $log['file_name']);
            if ($currentId === $logId) {
                unset($logs[$key]);
                $found = true;
                break;
            }
        }

        if (! $found) {
            return $this->response->setJSON(['success' => false, 'message' => 'Riwayat tidak ditemukan.']);
        }

        // Re-index array
        $logs = array_values($logs);

        // Simpan kembali
        file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        log_message('info', "[Import] Riwayat dengan ID {$logId} berhasil dihapus.");

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Riwayat berhasil dihapus.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    private function simpanLog(array $entry): void
    {
        $logFile = $this->resultDir . 'import_log.json';
        $logs    = [];
        if (file_exists($logFile)) {
            $logs = json_decode(file_get_contents($logFile), true) ?? [];
        }
        $entry['created_at'] = date('Y-m-d H:i:s');
        $logs[]              = $entry;

        // Simpan maksimal 100 log terakhir
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }

        file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
