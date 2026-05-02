<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TrainingModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TrainingController extends BaseController
{
    protected TrainingModel $trainingModel;

    public function __construct()
    {
        $this->trainingModel = new TrainingModel();
        helper(['form']);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════════════════════════════════
    public function index()
    {
        $trainings = $this->trainingModel->getAllOrdered();
        $summary   = $this->trainingModel->getSummary();
        $produks   = $this->trainingModel->getDistinctProduk();

        return $this->renderPage('pages/training/index', [
            'title'      => 'Data Training',
            'page_title' => 'Data Training',
            'trainings'  => $trainings,
            'summary'    => $summary,
            'produks'    => $produks,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STORE  (AJAX)
    // ══════════════════════════════════════════════════════════════════════════
    public function simpan()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $rules = [
            'nama_produk' => 'required|max_length[150]',
            'tahun'       => 'required|integer|greater_than[2000]|less_than[2100]',
            'bulan'       => 'required|integer|greater_than[0]|less_than[13]',
            'qty_total'   => 'required|integer|greater_than_equal_to[0]',
            'harga_avg'   => 'permit_empty|numeric',
            'harga_std'   => 'permit_empty|numeric',
            'promo_avg'   => 'permit_empty|decimal',
            'n_transaksi' => 'permit_empty|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error_validation',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $tahun = (int) $this->request->getPost('tahun');
        $bulan = (int) $this->request->getPost('bulan');
        $nama  = strtoupper(trim($this->request->getPost('nama_produk')));

        // Cek duplikat
        if ($this->trainingModel->existsByProdukBulan($nama, $tahun, $bulan)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => "Data {$nama} bulan {$bulan}/{$tahun} sudah ada.",
            ]);
        }

        $this->trainingModel->insert([
            'nama_produk'    => $nama,
            'tahun'          => $tahun,
            'bulan'          => $bulan,
            'kuartal'        => (int) ceil($bulan / 3),
            'time_idx'       => $tahun * 12 + $bulan,
            'qty_total'      => (int)   $this->request->getPost('qty_total'),
            'harga_avg'      => $this->request->getPost('harga_avg')   ?: null,
            'harga_std'      => $this->request->getPost('harga_std')   ?: null,
            'promo_avg'      => $this->request->getPost('promo_avg')   ?: null,
            'n_transaksi'    => $this->request->getPost('n_transaksi') ?: null,
            'qty_lag1'       => $this->request->getPost('qty_lag1')       ?: null,
            'qty_lag2'       => $this->request->getPost('qty_lag2')       ?: null,
            'qty_lag3'       => $this->request->getPost('qty_lag3')       ?: null,
            'qty_roll3_mean' => $this->request->getPost('qty_roll3_mean') ?: null,
            'qty_roll3_std'  => $this->request->getPost('qty_roll3_std')  ?: null,
            'qty_roll6_mean' => $this->request->getPost('qty_roll6_mean') ?: null,
            'trend'          => $this->request->getPost('trend')          ?: null,
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Data training berhasil ditambahkan.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // GET DATA  (AJAX — untuk modal edit)
    // ══════════════════════════════════════════════════════════════════════════
    public function getData()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $id   = $this->request->getPost('id');
        $data = $this->trainingModel->find($id);

        if (! $data) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        return $this->response->setJSON($data);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // UPDATE  (AJAX)
    // ══════════════════════════════════════════════════════════════════════════
    public function update($id)
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        if (! $this->trainingModel->find($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        $rules = [
            'nama_produk' => 'required|max_length[150]',
            'tahun'       => 'required|integer|greater_than[2000]',
            'bulan'       => 'required|integer|greater_than[0]|less_than[13]',
            'qty_total'   => 'required|integer|greater_than_equal_to[0]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error_validation',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $tahun = (int) $this->request->getPost('tahun');
        $bulan = (int) $this->request->getPost('bulan');

        $this->trainingModel->update($id, [
            'nama_produk'    => strtoupper(trim($this->request->getPost('nama_produk'))),
            'tahun'          => $tahun,
            'bulan'          => $bulan,
            'kuartal'        => (int) ceil($bulan / 3),
            'time_idx'       => $tahun * 12 + $bulan,
            'qty_total'      => (int) $this->request->getPost('qty_total'),
            'harga_avg'      => $this->request->getPost('harga_avg')      ?: null,
            'harga_std'      => $this->request->getPost('harga_std')      ?: null,
            'promo_avg'      => $this->request->getPost('promo_avg')      ?: null,
            'n_transaksi'    => $this->request->getPost('n_transaksi')    ?: null,
            'qty_lag1'       => $this->request->getPost('qty_lag1')       ?: null,
            'qty_lag2'       => $this->request->getPost('qty_lag2')       ?: null,
            'qty_lag3'       => $this->request->getPost('qty_lag3')       ?: null,
            'qty_roll3_mean' => $this->request->getPost('qty_roll3_mean') ?: null,
            'qty_roll3_std'  => $this->request->getPost('qty_roll3_std')  ?: null,
            'qty_roll6_mean' => $this->request->getPost('qty_roll6_mean') ?: null,
            'trend'          => $this->request->getPost('trend')          ?: null,
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Data training berhasil diperbarui.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DELETE SINGLE  (AJAX)
    // ══════════════════════════════════════════════════════════════════════════
    public function hapus($id)
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        if (! $this->trainingModel->find($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        $this->trainingModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Data training berhasil dihapus.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DELETE ALL  (AJAX)
    // ══════════════════════════════════════════════════════════════════════════
    public function hapusSemua()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        try {
            $this->trainingModel->deleteAll();
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Semua data training berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menghapus: ' . $e->getMessage(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // GENERATE DARI PENJUALAN  (AJAX)
    // ══════════════════════════════════════════════════════════════════════════
    public function generate()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        try {
            $count = $this->trainingModel->generateFromPenjualan();

            if ($count === 0) {
                return $this->response->setJSON([
                    'status'  => 'warning',
                    'message' => 'Tidak ada data penjualan untuk di-generate. Pastikan data penjualan sudah diisi.',
                ]);
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => "{$count} record data training berhasil di-generate dari data penjualan.",
                'count'   => $count,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal generate: ' . $e->getMessage(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PROSES TRAINING  (AJAX — trigger Python/notebook)
    // ══════════════════════════════════════════════════════════════════════════
    public function proses()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        // Cek data training tersedia
        $count = $this->trainingModel->countAll();
        if ($count === 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Data training kosong. Generate data terlebih dahulu.',
            ]);
        }

        // TODO: Panggil script Python / Jupyter notebook
        // Contoh: shell_exec('python3 /path/to/train.py > /tmp/train.log 2>&1 &');
        // Untuk sekarang, simpan status ke session/cache sebagai simulasi
        $session = session();
        $session->set('training_status', 'running');
        $session->set('training_started', date('Y-m-d H:i:s'));

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Proses training dimulai. Silakan pantau status di halaman ini.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STATUS POLLING  (AJAX)
    // ══════════════════════════════════════════════════════════════════════════
    public function status()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $session = session();
        $status  = $session->get('training_status') ?? 'idle';

        return $this->response->setJSON([
            'status'  => $status,
            'started' => $session->get('training_started'),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // IMPORT EXCEL  (AJAX)
    // ══════════════════════════════════════════════════════════════════════════
    public function prosesImport()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $file = $this->request->getFile('file_excel');

        if (! $file || ! $file->isValid()) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'File tidak valid atau tidak ditemukan.',
            ]);
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Format file harus .xlsx, .xls, atau .csv.',
            ]);
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $rows        = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal membaca file: ' . $e->getMessage(),
            ]);
        }

        // Kolom: A=nama_produk B=tahun C=bulan D=qty_total E=harga_avg F=harga_std
        //        G=promo_avg H=n_transaksi I=qty_lag1 J=qty_lag2 K=qty_lag3
        //        L=qty_roll3_mean M=qty_roll3_std N=qty_roll6_mean O=trend
        $dataRows = array_slice($rows, 1);
        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($dataRows as $i => $row) {
            $rowNum      = $i + 2;
            $namaProduk  = strtoupper(trim((string) ($row['A'] ?? '')));
            $tahun       = (int) ($row['B'] ?? 0);
            $bulan       = (int) ($row['C'] ?? 0);
            $qtyTotal    = trim((string) ($row['D'] ?? ''));

            if ($namaProduk === '' && $tahun === 0) continue;

            if ($namaProduk === '') {
                $errors[] = "Baris {$rowNum}: Nama produk kosong."; $skipped++; continue;
            }
            if ($tahun < 2000 || $tahun > 2100) {
                $errors[] = "Baris {$rowNum}: Tahun tidak valid ({$tahun})."; $skipped++; continue;
            }
            if ($bulan < 1 || $bulan > 12) {
                $errors[] = "Baris {$rowNum}: Bulan tidak valid ({$bulan})."; $skipped++; continue;
            }
            if (! is_numeric($qtyTotal) || (int) $qtyTotal < 0) {
                $errors[] = "Baris {$rowNum}: Qty total tidak valid ({$qtyTotal})."; $skipped++; continue;
            }

            // Skip duplikat
            if ($this->trainingModel->existsByProdukBulan($namaProduk, $tahun, $bulan)) {
                $errors[] = "Baris {$rowNum}: {$namaProduk} {$bulan}/{$tahun} sudah ada, dilewati.";
                $skipped++; continue;
            }

            $this->trainingModel->insert([
                'nama_produk'    => $namaProduk,
                'tahun'          => $tahun,
                'bulan'          => $bulan,
                'kuartal'        => (int) ceil($bulan / 3),
                'time_idx'       => $tahun * 12 + $bulan,
                'qty_total'      => (int) $qtyTotal,
                'harga_avg'      => isset($row['E']) && is_numeric($row['E']) ? (float) $row['E'] : null,
                'harga_std'      => isset($row['F']) && is_numeric($row['F']) ? (float) $row['F'] : null,
                'promo_avg'      => isset($row['G']) && is_numeric($row['G']) ? (float) $row['G'] : null,
                'n_transaksi'    => isset($row['H']) && is_numeric($row['H']) ? (int)   $row['H'] : null,
                'qty_lag1'       => isset($row['I']) && is_numeric($row['I']) ? (float) $row['I'] : null,
                'qty_lag2'       => isset($row['J']) && is_numeric($row['J']) ? (float) $row['J'] : null,
                'qty_lag3'       => isset($row['K']) && is_numeric($row['K']) ? (float) $row['K'] : null,
                'qty_roll3_mean' => isset($row['L']) && is_numeric($row['L']) ? (float) $row['L'] : null,
                'qty_roll3_std'  => isset($row['M']) && is_numeric($row['M']) ? (float) $row['M'] : null,
                'qty_roll6_mean' => isset($row['N']) && is_numeric($row['N']) ? (float) $row['N'] : null,
                'trend'          => isset($row['O']) && is_numeric($row['O']) ? (float) $row['O'] : null,
            ]);
            $inserted++;
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => "{$inserted} data berhasil diimpor, {$skipped} dilewati.",
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EXPORT EXCEL
    // ══════════════════════════════════════════════════════════════════════════
    public function export()
    {
        $trainings = $this->trainingModel->getAllOrdered();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Training');

        // ── Header ──────────────────────────────────────────────────────────
        $headers = [
            'No', 'ID Training', 'Nama Produk', 'Tahun', 'Bulan', 'Kuartal',
            'Time Idx', 'Qty Total', 'Harga Avg', 'Harga Std', 'Promo Avg',
            'N Transaksi', 'Lag 1', 'Lag 2', 'Lag 3',
            'Roll3 Mean', 'Roll3 Std', 'Roll6 Mean', 'Trend',
        ];
        $cols = range('A', 'S');

        foreach ($cols as $ci => $col) {
            $sheet->setCellValue($col . '1', $headers[$ci]);
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A4B7A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];
        $sheet->getStyle('A1:S1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Data ────────────────────────────────────────────────────────────
        $bulanNama = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $no = 1;
        foreach ($trainings as $idx => $t) {
            $r = $idx + 2;
            $values = [
                $no++,
                $t['id_training'],
                $t['nama_produk'],
                $t['tahun'],
                $bulanNama[(int) $t['bulan']] ?? $t['bulan'],
                'Q' . $t['kuartal'],
                $t['time_idx'],
                $t['qty_total'],
                $t['harga_avg']      ?? '-',
                $t['harga_std']      ?? '-',
                $t['promo_avg']      ?? '-',
                $t['n_transaksi']    ?? '-',
                $t['qty_lag1']       ?? '-',
                $t['qty_lag2']       ?? '-',
                $t['qty_lag3']       ?? '-',
                $t['qty_roll3_mean'] ?? '-',
                $t['qty_roll3_std']  ?? '-',
                $t['qty_roll6_mean'] ?? '-',
                $t['trend']          ?? '-',
            ];

            foreach ($cols as $ci => $col) {
                $sheet->setCellValue($col . $r, $values[$ci]);
            }

            if ($no % 2 === 0) {
                $sheet->getStyle("A{$r}:S{$r}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EBF3FB');
            }
        }

        // ── Column widths ────────────────────────────────────────────────────
        $widths = [4, 16, 42, 8, 8, 8, 9, 10, 12, 12, 11, 12, 9, 9, 9, 12, 12, 12, 9];
        foreach ($cols as $ci => $col) {
            $sheet->getColumnDimension($col)->setWidth($widths[$ci] ?? 10);
        }

        $sheet->getStyle("A1:S" . (count($trainings) + 1))->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->freezePane('A2');

        $filename = 'data_training_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        (new XlsxWriter($spreadsheet))->save('php://output');
        exit;
    }

    // ── Template Excel untuk impor ─────────────────────────────────────────
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Training');

        $headers = [
            'nama_produk', 'tahun', 'bulan', 'qty_total',
            'harga_avg', 'harga_std', 'promo_avg', 'n_transaksi',
            'qty_lag1', 'qty_lag2', 'qty_lag3',
            'qty_roll3_mean', 'qty_roll3_std', 'qty_roll6_mean', 'trend',
        ];

        $cols = range('A', 'O');
        foreach ($cols as $ci => $col) {
            $sheet->setCellValue($col . '1', $headers[$ci]);
        }

        $sheet->getStyle('A1:O1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A4B7A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Contoh data
        $examples = [
            ['REDMI NOTE 13 5G 8/256 BLACK', 2025, 1, 3, 2800000, 0, 1.0, 3, null, null, null, null, null, null, 0.5],
            ['XIAOMI 14T 12/512 BLACK',       2025, 1, 1, 7000000, 0, 1.0, 1, null, null, null, null, null, null, 0.2],
        ];

        foreach ($examples as $i => $ex) {
            $r = $i + 2;
            foreach ($cols as $ci => $col) {
                $sheet->setCellValue($col . $r, $ex[$ci]);
            }
        }

        $widths = [42, 8, 8, 10, 12, 10, 10, 12, 9, 9, 9, 13, 13, 13, 8];
        foreach ($cols as $ci => $col) {
            $sheet->getColumnDimension($col)->setWidth($widths[$ci] ?? 10);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template_import_training.xlsx"');
        header('Cache-Control: max-age=0');

        (new XlsxWriter($spreadsheet))->save('php://output');
        exit;
    }
}