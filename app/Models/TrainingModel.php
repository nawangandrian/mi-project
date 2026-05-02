<?php

namespace App\Models;

use CodeIgniter\Model;

class TrainingModel extends Model
{
    protected $table            = 'data_training';
    protected $primaryKey       = 'id_training';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'id_training',
        'nama_produk',
        'tahun',
        'bulan',
        'kuartal',
        'time_idx',
        'qty_total',
        'harga_avg',
        'harga_std',
        'promo_avg',
        'n_transaksi',
        'qty_lag1',
        'qty_lag2',
        'qty_lag3',
        'qty_roll3_mean',
        'qty_roll3_std',
        'qty_roll6_mean',
        'trend',
    ];

    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = null; // data_training tidak punya updated_at

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateId'];

    // ── Auto-generate ID ───────────────────────────────────────────────────────
    protected function generateId(array $data): array
    {
        if (! isset($data['data']['id_training'])) {
            $data['data']['id_training'] = 'TRN-' . strtoupper(bin2hex(random_bytes(4)));
        }
        return $data;
    }

    // ── Summary stats ──────────────────────────────────────────────────────────
    public function getSummary(): array
    {
        $row = $this->db->query("
            SELECT
                COUNT(*)                          AS total_record,
                COUNT(DISTINCT nama_produk)        AS total_produk,
                COALESCE(SUM(qty_total), 0)        AS total_qty,
                COALESCE(AVG(harga_avg), 0)        AS rata_harga,
                COALESCE(AVG(promo_avg), 0)        AS rata_promo,
                MIN(CONCAT(tahun,'-',LPAD(bulan,2,'0'))) AS periode_awal,
                MAX(CONCAT(tahun,'-',LPAD(bulan,2,'0'))) AS periode_akhir
            FROM {$this->table}
        ")->getRowArray();

        return $row ?? [
            'total_record'  => 0,
            'total_produk'  => 0,
            'total_qty'     => 0,
            'rata_harga'    => 0,
            'rata_promo'    => 0,
            'periode_awal'  => null,
            'periode_akhir' => null,
        ];
    }

    /**
     * Ambil semua record diurutkan periode terbaru dulu.
     */
    public function getAllOrdered(): array
    {
        return $this->orderBy('tahun', 'DESC')
            ->orderBy('bulan', 'DESC')
            ->orderBy('nama_produk', 'ASC')
            ->findAll();
    }

    /**
     * Daftar produk unik yang ada di data training.
     */
    public function getDistinctProduk(): array
    {
        return $this->db->query("
            SELECT DISTINCT nama_produk FROM {$this->table} ORDER BY nama_produk ASC
        ")->getResultArray();
    }

    /**
     * Hapus semua data training.
     */
    public function deleteAll(): bool
    {
        return $this->db->table($this->table)->truncate();
    }

    /**
     * Cek duplikat unik (nama_produk + tahun + bulan).
     */
    public function existsByProdukBulan(string $namaProduk, int $tahun, int $bulan): bool
    {
        return $this->where('nama_produk', $namaProduk)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->countAllResults() > 0;
    }

    /**
     * Bulk insert dengan ID manual (insertBatch bypass beforeInsert).
     */
    public function bulkInsert(array $rows): bool
    {
        $prepared = [];
        foreach ($rows as $row) {
            $tahun  = (int) ($row['tahun']  ?? date('Y'));
            $bulan  = (int) ($row['bulan']  ?? date('n'));
            $prepared[] = [
                'id_training'    => 'TRN-' . strtoupper(bin2hex(random_bytes(4))),
                'nama_produk'    => strtoupper(trim($row['nama_produk'] ?? '')),
                'tahun'          => $tahun,
                'bulan'          => $bulan,
                'kuartal'        => (int) ceil($bulan / 3),
                'time_idx'       => $tahun * 12 + $bulan,
                'qty_total'      => (int)   ($row['qty_total']      ?? 0),
                'harga_avg'      => isset($row['harga_avg'])      ? (float) $row['harga_avg']      : null,
                'harga_std'      => isset($row['harga_std'])      ? (float) $row['harga_std']      : null,
                'promo_avg'      => isset($row['promo_avg'])      ? (float) $row['promo_avg']      : null,
                'n_transaksi'    => isset($row['n_transaksi'])    ? (int)   $row['n_transaksi']    : null,
                'qty_lag1'       => isset($row['qty_lag1'])       ? (float) $row['qty_lag1']       : null,
                'qty_lag2'       => isset($row['qty_lag2'])       ? (float) $row['qty_lag2']       : null,
                'qty_lag3'       => isset($row['qty_lag3'])       ? (float) $row['qty_lag3']       : null,
                'qty_roll3_mean' => isset($row['qty_roll3_mean']) ? (float) $row['qty_roll3_mean'] : null,
                'qty_roll3_std'  => isset($row['qty_roll3_std'])  ? (float) $row['qty_roll3_std']  : null,
                'qty_roll6_mean' => isset($row['qty_roll6_mean']) ? (float) $row['qty_roll6_mean'] : null,
                'trend'          => isset($row['trend'])          ? (float) $row['trend']          : null,
                'created_at'     => date('Y-m-d H:i:s'),
            ];
        }
        return $this->db->table($this->table)->insertBatch($prepared) !== false;
    }

    /**
     * Generate data training dari tabel penjualan (agregasi bulanan).
     * Dipanggil saat proses training otomatis.
     */
    public function generateFromPenjualan(): int
    {
        // Ambil agregasi dari penjualan
        $rows = $this->db->query("
            SELECT
                nama_produk,
                YEAR(tanggal)                        AS tahun,
                MONTH(tanggal)                       AS bulan,
                CEIL(MONTH(tanggal) / 3)             AS kuartal,
                YEAR(tanggal) * 12 + MONTH(tanggal)  AS time_idx,
                SUM(qty)                             AS qty_total,
                AVG(harga)                           AS harga_avg,
                STDDEV(harga)                        AS harga_std,
                AVG(promo)                           AS promo_avg,
                COUNT(*)                             AS n_transaksi
            FROM penjualan
            GROUP BY
                nama_produk,
                YEAR(tanggal),
                MONTH(tanggal),
                CEIL(MONTH(tanggal) / 3),
                YEAR(tanggal) * 12 + MONTH(tanggal)
            ORDER BY nama_produk, tahun, bulan
        ")->getResultArray();

        if (empty($rows)) return 0;

        // Kelompokkan per produk untuk hitung lag & rolling
        $byProduk = [];
        foreach ($rows as $r) {
            $byProduk[$r['nama_produk']][] = $r;
        }

        $toInsert = [];
        foreach ($byProduk as $produk => $series) {
            // Hitung trend (slope linear sederhana)
            $n      = count($series);
            $xMean  = ($n - 1) / 2;
            $yVals  = array_column($series, 'qty_total');
            $yMean  = array_sum($yVals) / $n;
            $num    = 0;
            $den = 0;
            foreach ($yVals as $i => $y) {
                $num += ($i - $xMean) * ($y - $yMean);
                $den += ($i - $xMean) ** 2;
            }
            $slope = $den > 0 ? $num / $den : 0;

            foreach ($series as $idx => $r) {
                $qty = (float) $r['qty_total'];
                $toInsert[] = [
                    'nama_produk'    => strtoupper($produk),
                    'tahun'          => (int) $r['tahun'],
                    'bulan'          => (int) $r['bulan'],
                    'kuartal'        => (int) $r['kuartal'],
                    'time_idx'       => (int) $r['time_idx'],
                    'qty_total'      => (int) $qty,
                    'harga_avg'      => round((float) $r['harga_avg'], 2),
                    'harga_std'      => $r['harga_std'] !== null ? round((float) $r['harga_std'], 2) : 0,
                    'promo_avg'      => round((float) $r['promo_avg'], 4),
                    'n_transaksi'    => (int) $r['n_transaksi'],
                    'qty_lag1'       => $idx >= 1 ? (float) $series[$idx - 1]['qty_total'] : null,
                    'qty_lag2'       => $idx >= 2 ? (float) $series[$idx - 2]['qty_total'] : null,
                    'qty_lag3'       => $idx >= 3 ? (float) $series[$idx - 3]['qty_total'] : null,
                    'qty_roll3_mean' => $idx >= 2 ? round(array_sum(array_slice($yVals, max(0, $idx - 2), 3)) / min(3, $idx + 1), 2) : null,
                    'qty_roll3_std'  => null,
                    'qty_roll6_mean' => $idx >= 5 ? round(array_sum(array_slice($yVals, max(0, $idx - 5), 6)) / min(6, $idx + 1), 2) : null,
                    'trend'          => round($slope, 4),
                ];
            }
        }

        // Hapus lama lalu insert baru
        $this->db->table($this->table)->truncate();
        return $this->bulkInsert($toInsert) ? count($toInsert) : 0;
    }
}
