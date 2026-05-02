<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProdukModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ProdukController extends BaseController
{
    protected ProdukModel $produkModel;

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
        helper(['form']);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════════════════════════════════
    public function index()
    {
        $produks = $this->produkModel->orderBy('created_at', 'DESC')->findAll();

        return $this->renderPage('pages/produk/index', [
            'title'      => 'Data Produk',
            'page_title' => 'Data Produk',
            'produks'    => $produks,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STORE  (AJAX)
    // ══════════════════════════════════════════════════════════════════════════
    public function simpan()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $rules = [
            'nama_produk' => 'required|min_length[3]|max_length[150]|is_unique[produk.nama_produk]',
            'is_active'   => 'required|in_list[0,1]',
        ];

        $messages = [
            'nama_produk' => [
                'is_unique' => 'Nama produk sudah terdaftar.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'status' => 'error_validation',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $this->produkModel->insert([
            'nama_produk' => strtoupper(trim($this->request->getPost('nama_produk'))),
            'is_active'   => (int) $this->request->getPost('is_active'),
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Produk berhasil ditambahkan.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // GET DATA  (AJAX)
    // ══════════════════════════════════════════════════════════════════════════
    public function getData()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $id    = $this->request->getPost('id');
        $produk = $this->produkModel->find($id);

        if (! $produk) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Produk tidak ditemukan']);
        }

        return $this->response->setJSON($produk);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // UPDATE  (AJAX)
    // ══════════════════════════════════════════════════════════════════════════
    public function update($id)
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $produk = $this->produkModel->find($id);
        if (! $produk) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Produk tidak ditemukan']);
        }

        $rules = [
            'nama_produk' => "required|min_length[3]|max_length[150]|is_unique[produk.nama_produk,id_produk,{$id}]",
            'is_active'   => 'required|in_list[0,1]',
        ];

        $messages = [
            'nama_produk' => [
                'is_unique' => 'Nama produk sudah terdaftar.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return $this->response->setJSON([
                'status' => 'error_validation',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $this->produkModel->update($id, [
            'nama_produk' => strtoupper(trim($this->request->getPost('nama_produk'))),
            'is_active'   => (int) $this->request->getPost('is_active'),
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Produk berhasil diperbarui.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DELETE  (AJAX)
    // ══════════════════════════════════════════════════════════════════════════
    public function hapus($id)
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        if (! $this->produkModel->find($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Produk tidak ditemukan']);
        }

        $this->produkModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Produk berhasil dihapus.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DELETE ALL  (AJAX)
    // ══════════════════════════════════════════════════════════════════════════
    public function hapusSemua()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        try {
            $this->produkModel->deleteAll();
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Semua data produk berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // IMPORT EXCEL  (POST)
    // ══════════════════════════════════════════════════════════════════════════
    public function prosesImport()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $file = $this->request->getFile('file_excel');

        if (! $file || ! $file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak valid atau tidak ditemukan.']);
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Format file harus .xlsx, .xls, atau .csv.']);
        }

        $tmpPath = $file->getTempName();

        try {
            $spreadsheet = IOFactory::load($tmpPath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true); // assoc: A,B,C…
        } catch (\Throwable $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membaca file: ' . $e->getMessage()]);
        }

        // Skip header row (row pertama)
        $dataRows  = array_slice($rows, 1);
        $inserted  = 0;
        $skipped   = 0;
        $errors    = [];

        foreach ($dataRows as $i => $row) {
            $rowNum      = $i + 2; // nomor baris di sheet (mulai 2)
            $namaProduk  = trim((string) ($row['A'] ?? ''));
            $isActive   = isset($row['B']) ? (int) $row['B'] : 1;

            if ($namaProduk === '') continue;

            if (strlen($namaProduk) < 3) {
                $errors[] = "Baris {$rowNum}: Nama produk terlalu pendek.";
                $skipped++;
                continue;
            }

            $namaProduk = strtoupper($namaProduk);

            if ($this->produkModel->existsByName($namaProduk)) {
                $errors[] = "Baris {$rowNum}: \"{$namaProduk}\" sudah ada, dilewati.";
                $skipped++;
                continue;
            }

            $this->produkModel->insert([
                'nama_produk' => $namaProduk,
                // 'harga' ← dihapus
                'is_active'   => in_array($isActive, [0, 1]) ? $isActive : 1,
            ]);
            $inserted++;
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => "{$inserted} produk berhasil diimpor, {$skipped} dilewati.",
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
        $produks = $this->produkModel->orderBy('nama_produk', 'ASC')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Produk');

        // ── Header ──────────────────────────────────────────────────────────
        $headers = ['No', 'ID Produk', 'Nama Produk', 'Status', 'Tanggal Dibuat'];
        $cols    = ['A', 'B', 'C', 'D', 'E'];

        foreach ($cols as $ci => $col) {
            $sheet->setCellValue($col . '1', $headers[$ci]);
        }

        // Header style
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E6DA4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Data ────────────────────────────────────────────────────────────
        $no = 1;
        foreach ($produks as $row => $p) {
            $r = $row + 2;
            $sheet->setCellValue('A' . $r, $no++);
            $sheet->setCellValue('B' . $r, $p['id_produk']);
            $sheet->setCellValue('C' . $r, $p['nama_produk']);
            $sheet->setCellValue('D' . $r, $p['is_active'] ? 'Aktif' : 'Nonaktif');
            $sheet->setCellValue('E' . $r, $p['created_at']);
        }

        // ── Column widths ────────────────────────────────────────────────────
        $widths = [5, 16, 55, 12, 22];
        foreach ($cols as $ci => $col) {
            $sheet->getColumnDimension($col)->setWidth($widths[$ci]);
        }

        // ── Outer border ─────────────────────────────────────────────────────
        $lastRow = count($produks) + 1;
        $sheet->getStyle("A1:F{$lastRow}")->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // ── Output ───────────────────────────────────────────────────────────
        $filename = 'data_produk_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ── Template Excel untuk impor ─────────────────────────────────────────
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import');

        // Header
        $sheet->setCellValue('A1', 'nama_produk');
        $sheet->setCellValue('B1', 'is_active (1=aktif, 0=nonaktif)');


        $sheet->getStyle('A1:B1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F5132']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Contoh data
        $examples = [
            ['REDMI NOTE 14 PRO 8/256 BLACK', 1],
            ['XIAOMI 15 12/512 WHITE',         1],
        ];
        foreach ($examples as $i => $ex) {
            $r = $i + 2;
            $sheet->setCellValue("A{$r}", $ex[0]);
            $sheet->setCellValue("B{$r}", $ex[1]);
        }

        foreach (['A', 'B'] as $ci => $col) {
            $sheet->getColumnDimension($col)->setWidth([40, 15][$ci]);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template_import_produk.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
