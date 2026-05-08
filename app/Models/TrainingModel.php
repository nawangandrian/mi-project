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
     * Generate data training dari tabel penjualan.
     *
     * CATATAN v3:
     * ───────────
     * Fungsi ini menghasilkan data preview/audit di tabel data_training.
     * Proses TRAINING SESUNGGUHNYA (train_rf.py v3) membaca langsung dari
     * tabel penjualan — tidak bergantung pada data_training lagi.
     *
     * Namun nilai di sini dibuat KONSISTEN dengan logika Python pandas agar
     * data_training berguna sebagai referensi / audit trail:
     *
     *   pandas shift(1)            → PHP: nilai dari baris sebelumnya (idx-1)
     *   rolling(3, min_periods=1)  → PHP: array_slice(prev, max(0,idx-3), min(3,idx))
     *   rolling(3, min_periods=2).std() → PHP: stddev dengan minimal 2 nilai
     *   fillna(0)                  → PHP: 0 jika tidak ada histori (TANPA bfill)
     */
    public function generateFromPenjualan(): int
    {
        // ── Agregasi bulanan dari penjualan (identik dengan agregasi_bulanan() Python) ──
        $rows = $this->db->query("
            SELECT
                UPPER(TRIM(nama_produk))              AS nama_produk,
                YEAR(tanggal)                          AS tahun,
                MONTH(tanggal)                         AS bulan,
                CEIL(MONTH(tanggal) / 3)               AS kuartal,
                YEAR(tanggal) * 12 + MONTH(tanggal)    AS time_idx,
                SUM(qty)                               AS qty_total,
                AVG(harga)                             AS harga_avg,
                STDDEV_SAMP(harga)                     AS harga_std,
                AVG(CASE
                    WHEN UPPER(TRIM(promo)) = 'YA'    THEN 1
                    WHEN UPPER(TRIM(promo)) = 'TIDAK' THEN 0
                    WHEN promo REGEXP '^[01]\$'        THEN CAST(promo AS UNSIGNED)
                    ELSE 0
                END)                                   AS promo_avg,
                COUNT(*)                               AS n_transaksi
            FROM penjualan
            GROUP BY
                UPPER(TRIM(nama_produk)),
                YEAR(tanggal),
                MONTH(tanggal),
                CEIL(MONTH(tanggal) / 3),
                YEAR(tanggal) * 12 + MONTH(tanggal)
            ORDER BY nama_produk, tahun, bulan ASC
        ")->getResultArray();

        if (empty($rows)) return 0;

        // ── Group per produk ──────────────────────────────────────────────────
        $byProduk = [];
        foreach ($rows as $r) {
            $produk = strtoupper(trim($r['nama_produk']));
            $byProduk[$produk][] = $r;
        }

        // ── Helper: sample std (ddof=1) — identik pandas rolling std ─────────
        $sampleStd = static function (array $arr): float {
            $n = count($arr);
            if ($n < 2) return 0.0;
            $mean = array_sum($arr) / $n;
            $sq   = 0.0;
            foreach ($arr as $v) {
                $sq += ($v - $mean) ** 2;
            }
            return sqrt($sq / ($n - 1));  // ddof=1
        };

        $toInsert = [];

        foreach ($byProduk as $produk => $series) {

            $n     = count($series);
            $yVals = array_column($series, 'qty_total');

            // time_idx range untuk trend (disimpan sebagai referensi audit)
            $timeIdxs = array_column($series, 'time_idx');
            $minT     = (int) min($timeIdxs);
            $maxT     = (int) max($timeIdxs);
            $range    = max($maxT - $minT, 1);

            foreach ($series as $idx => $r) {

                $bulan = (int) $r['bulan'];

                // ── Lag (identik pandas shift(1), shift(2), shift(3)) ─────────
                // shift(1): nilai idx-1; shift(2): idx-2; dst.
                // Jika tidak ada histori → 0 (identik fillna(0) Python)
                $lag1 = $idx >= 1 ? (float) $yVals[$idx - 1] : 0.0;
                $lag2 = $idx >= 2 ? (float) $yVals[$idx - 2] : 0.0;
                $lag3 = $idx >= 3 ? (float) $yVals[$idx - 3] : 0.0;

                // ── Rolling (identik pandas shift(1).rolling(N, min_periods=M)) ─
                // "shift(1) dulu" = nilai yang tersedia SEBELUM baris ini (bukan include baris ini)
                // rolling(3, min_periods=1): ambil max 3 nilai sebelum idx (tidak include idx)
                $prevAll = array_slice($yVals, 0, $idx); // semua nilai sebelum idx

                $prev3 = array_slice($prevAll, max(0, $idx - 3)); // max 3 terakhir
                $prev6 = array_slice($prevAll, max(0, $idx - 6)); // max 6 terakhir

                // roll3_mean — min_periods=1
                $roll3Mean = count($prev3) >= 1
                    ? array_sum($prev3) / count($prev3)
                    : 0.0;

                // roll3_std — min_periods=2 (butuh minimal 2 nilai)
                $roll3Std  = count($prev3) >= 2
                    ? $sampleStd($prev3)
                    : 0.0;

                // roll6_mean — min_periods=1
                $roll6Mean = count($prev6) >= 1
                    ? array_sum($prev6) / count($prev6)
                    : 0.0;

                // ── Trend 0-1 (hanya untuk audit — tidak dipakai model v3) ────
                $trend = ($r['time_idx'] - $minT) / $range;

                $toInsert[] = [
                    'nama_produk'    => $produk,
                    'tahun'          => (int)   $r['tahun'],
                    'bulan'          => $bulan,
                    'kuartal'        => (int)   $r['kuartal'],
                    'time_idx'       => (int)   $r['time_idx'],
                    'qty_total'      => (int)   $r['qty_total'],
                    'harga_avg'      => round((float) $r['harga_avg'], 2),
                    'harga_std'      => $r['harga_std'] !== null
                                        ? round((float) $r['harga_std'], 2)
                                        : 0.0,
                    'promo_avg'      => round((float) $r['promo_avg'], 4),
                    'n_transaksi'    => (int)   $r['n_transaksi'],
                    'qty_lag1'       => round($lag1,     2),
                    'qty_lag2'       => round($lag2,     2),
                    'qty_lag3'       => round($lag3,     2),
                    'qty_roll3_mean' => round($roll3Mean, 2),
                    'qty_roll3_std'  => round($roll3Std,  2),
                    'qty_roll6_mean' => round($roll6Mean, 2),
                    'trend'          => round($trend,     6),
                ];
            }
        }

        // ── Simpan ke DB ──────────────────────────────────────────────────────
        $this->db->table($this->table)->truncate();

        return $this->bulkInsert($toInsert)
            ? count($toInsert)
            : 0;
    }
}