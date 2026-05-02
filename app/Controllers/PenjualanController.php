<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PenjualanModel;
use App\Models\ProdukModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PenjualanController extends BaseController
{
    protected PenjualanModel $penjualanModel;
    protected ProdukModel    $produkModel;

    public function __construct()
    {
        $this->penjualanModel = new PenjualanModel();
        $this->produkModel    = new ProdukModel();
        helper(['form']);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // INDEX
    // ══════════════════════════════════════════════════════════════════════════
    public function index()
    {
        $penjualans = $this->penjualanModel
            ->orderBy('tanggal', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $produks = $this->produkModel
            ->where('is_active', 1)
            ->orderBy('nama_produk', 'ASC')
            ->findAll();

        $summary = $this->penjualanModel->getSummary();

        return $this->renderPage('pages/penjualan/index', [
            'title'      => 'Data Penjualan',
            'page_title' => 'Data Penjualan',
            'penjualans' => $penjualans,
            'produks'    => $produks,
            'summary'    => $summary,
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
            'no_nota'     => 'required|max_length[20]',
            'id_produk'   => 'permit_empty|max_length[20]',
            'nama_produk' => 'required|max_length[150]',
            'qty'         => 'required|integer|greater_than[0]',
            'harga'       => 'required|numeric|greater_than[0]',
            'tanggal'     => 'required|valid_date[Y-m-d]',
            'promo'       => 'required|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error_validation',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        // Jika id_produk dipilih, snapshot nama dari tabel produk
        $idProduk    = $this->request->getPost('id_produk') ?: null;
        $namaProduk  = $this->request->getPost('nama_produk');

        if ($idProduk) {
            $produk = $this->produkModel->find($idProduk);
            if ($produk) $namaProduk = $produk['nama_produk'];
        }

        $this->penjualanModel->insert([
            'no_nota'     => trim($this->request->getPost('no_nota')),
            'id_produk'   => $idProduk,
            'nama_produk' => strtoupper(trim($namaProduk)),
            'qty'         => (int) $this->request->getPost('qty'),
            'harga'       => (int) $this->request->getPost('harga'),
            'tanggal'     => $this->request->getPost('tanggal'),
            'promo'       => (int) $this->request->getPost('promo'),
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Data penjualan berhasil ditambahkan.',
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
        $data = $this->penjualanModel->find($id);

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

        if (! $this->penjualanModel->find($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        $rules = [
            'no_nota'     => 'required|max_length[20]',
            'nama_produk' => 'required|max_length[150]',
            'qty'         => 'required|integer|greater_than[0]',
            'harga'       => 'required|numeric|greater_than[0]',
            'tanggal'     => 'required|valid_date[Y-m-d]',
            'promo'       => 'required|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error_validation',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $idProduk   = $this->request->getPost('id_produk') ?: null;
        $namaProduk = $this->request->getPost('nama_produk');

        if ($idProduk) {
            $produk = $this->produkModel->find($idProduk);
            if ($produk) $namaProduk = $produk['nama_produk'];
        }

        $this->penjualanModel->update($id, [
            'no_nota'     => trim($this->request->getPost('no_nota')),
            'id_produk'   => $idProduk,
            'nama_produk' => strtoupper(trim($namaProduk)),
            'qty'         => (int) $this->request->getPost('qty'),
            'harga'       => (int) $this->request->getPost('harga'),
            'tanggal'     => $this->request->getPost('tanggal'),
            'promo'       => (int) $this->request->getPost('promo'),
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Data penjualan berhasil diperbarui.',
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

        if (! $this->penjualanModel->find($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }

        $this->penjualanModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Data penjualan berhasil dihapus.',
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
            $this->penjualanModel->deleteAll();
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Semua data penjualan berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // GET HARGA PRODUK  (AJAX — auto-fill harga saat pilih produk di form)
    // ══════════════════════════════════════════════════════════════════════════
    public function getHargaProduk()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $id     = $this->request->getPost('id_produk');
        $produk = $this->produkModel->find($id);

        if (! $produk) {
            return $this->response->setJSON(['status' => 'error', 'harga' => 0]);
        }

        return $this->response->setJSON([
            'status'      => 'success',
            'nama_produk' => $produk['nama_produk'],
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

        // Skip baris header (baris ke-1)
        $dataRows = array_slice($rows, 1);
        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($dataRows as $i => $row) {
            $rowNum     = $i + 2;
            $noNota     = trim((string) ($row['A'] ?? ''));
            $namaProduk = trim((string) ($row['B'] ?? ''));
            $qty        = trim((string) ($row['C'] ?? ''));
            $harga      = trim((string) ($row['D'] ?? ''));
            $tanggal    = trim((string) ($row['E'] ?? ''));
            $promo      = isset($row['F']) ? (int) $row['F'] : 0;

            // Skip baris kosong
            if ($noNota === '' && $namaProduk === '') continue;

            // Validasi field wajib
            if ($noNota === '') {
                $errors[] = "Baris {$rowNum}: No. Nota kosong."; $skipped++; continue;
            }
            if ($namaProduk === '') {
                $errors[] = "Baris {$rowNum}: Nama produk kosong."; $skipped++; continue;
            }
            if (! is_numeric($qty) || (int) $qty < 1) {
                $errors[] = "Baris {$rowNum}: Qty tidak valid ({$qty})."; $skipped++; continue;
            }
            if (! is_numeric($harga) || (int) $harga < 1) {
                $errors[] = "Baris {$rowNum}: Harga tidak valid ({$harga})."; $skipped++; continue;
            }

            // Normalisasi tanggal: Y-m-d atau d/m/Y
            $tgl = null;
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                $tgl = $tanggal;
            } elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $tanggal)) {
                $parts = explode('/', $tanggal);
                $tgl   = "{$parts[2]}-" . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            } elseif (is_numeric($tanggal)) {
                // Excel serial date → PHP date
                $tgl = date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp((float)$tanggal));
            }

            if (! $tgl || ! strtotime($tgl)) {
                $errors[] = "Baris {$rowNum}: Tanggal tidak valid ({$tanggal})."; $skipped++; continue;
            }

            $namaProduk = strtoupper($namaProduk);

            // Cari id_produk jika nama cocok
            $produkRow = $this->produkModel->where('nama_produk', $namaProduk)->first();
            $idProduk  = $produkRow['id_produk'] ?? null;

            $this->penjualanModel->insert([
                'no_nota'     => $noNota,
                'id_produk'   => $idProduk,
                'nama_produk' => $namaProduk,
                'qty'         => (int) $qty,
                'harga'       => (int) $harga,
                'tanggal'     => $tgl,
                'promo'       => in_array($promo, [0, 1]) ? $promo : 0,
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
        $penjualans = $this->penjualanModel
            ->orderBy('tanggal', 'DESC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Penjualan');

        // ── Header ──────────────────────────────────────────────────────────
        $headers = ['No', 'ID Transaksi', 'No. Nota', 'Nama Produk', 'Qty', 'Harga Satuan', 'Subtotal', 'Tanggal', 'Promo'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        foreach ($cols as $ci => $col) {
            $sheet->setCellValue($col . '1', $headers[$ci]);
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A4B7A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Data ────────────────────────────────────────────────────────────
        $no = 1;
        foreach ($penjualans as $idx => $p) {
            $r       = $idx + 2;
            $subtotal = (int)$p['qty'] * (int)$p['harga'];

            $sheet->setCellValue('A' . $r, $no++);
            $sheet->setCellValue('B' . $r, $p['id_penjualan']);
            $sheet->setCellValue('C' . $r, $p['no_nota']);
            $sheet->setCellValue('D' . $r, $p['nama_produk']);
            $sheet->setCellValue('E' . $r, (int) $p['qty']);
            $sheet->setCellValue('F' . $r, (int) $p['harga']);
            $sheet->setCellValue('G' . $r, $subtotal);
            $sheet->setCellValue('H' . $r, $p['tanggal']);
            $sheet->setCellValue('I' . $r, $p['promo'] ? 'YA' : 'TIDAK');

            // Zebra stripe
            if ($no % 2 === 0) {
                $sheet->getStyle("A{$r}:I{$r}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EBF3FB');
            }

            // Highlight baris promo
            if ($p['promo']) {
                $sheet->getStyle("I{$r}")
                    ->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF10b77f'));
            }

            // Format angka
            foreach (['F', 'G'] as $numCol) {
                $sheet->getStyle("{$numCol}{$r}")
                    ->getNumberFormat()->setFormatCode('#,##0');
            }
            $sheet->getStyle("E{$r}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // ── Totals row ───────────────────────────────────────────────────────
        $lastData = count($penjualans) + 1;
        $totRow   = $lastData + 1;
        $sheet->setCellValue('D' . $totRow, 'TOTAL');
        $sheet->setCellValue('E' . $totRow, "=SUM(E2:E{$lastData})");
        $sheet->setCellValue('G' . $totRow, "=SUM(G2:G{$lastData})");

        $sheet->getStyle("A{$totRow}:I{$totRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A4B7A']],
        ]);
        $sheet->getStyle("G{$totRow}")
            ->getNumberFormat()->setFormatCode('#,##0');

        // ── Column widths ────────────────────────────────────────────────────
        $widths = [5, 18, 14, 50, 8, 18, 18, 14, 10];
        foreach ($cols as $ci => $col) {
            $sheet->getColumnDimension($col)->setWidth($widths[$ci]);
        }

        // ── Outer border ─────────────────────────────────────────────────────
        $sheet->getStyle("A1:I{$totRow}")->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // ── Freeze header ────────────────────────────────────────────────────
        $sheet->freezePane('A2');

        // ── Output ───────────────────────────────────────────────────────────
        $filename = 'data_penjualan_' . date('Ymd_His') . '.xlsx';

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
        $sheet->setTitle('Template Import');

        $headers = ['no_nota', 'nama_produk', 'qty', 'harga', 'tanggal (YYYY-MM-DD)', 'promo (1=ya, 0=tidak)'];
        foreach (['A','B','C','D','E','F'] as $ci => $col) {
            $sheet->setCellValue($col . '1', $headers[$ci]);
        }

        $sheet->getStyle('A1:F1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A4B7A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Contoh data
        $examples = [
            ['85700', 'REDMI NOTE 13 5G 8/256 BLACK', 1, 2800000, date('Y-m-d'), 1],
            ['85701', 'XIAOMI 14T 12/512 BLACK',       2, 7000000, date('Y-m-d'), 0],
        ];
        foreach ($examples as $i => $ex) {
            $r = $i + 2;
            foreach (['A','B','C','D','E','F'] as $ci => $col) {
                $sheet->setCellValue($col . $r, $ex[$ci]);
            }
        }

        foreach (['A','B','C','D','E','F'] as $ci => $col) {
            $sheet->getColumnDimension($col)->setWidth([14, 45, 8, 14, 22, 22][$ci]);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template_import_penjualan.xlsx"');
        header('Cache-Control: max-age=0');

        (new XlsxWriter($spreadsheet))->save('php://output');
        exit;
    }
}