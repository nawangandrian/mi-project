<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * PrediksiModel
 * Kelola data prediksi penjualan hasil inferensi Random Forest.
 *
 * Tabel: prediksi
 * PK   : id_prediksi (VARCHAR 20, format PDK-XXXXXXXX)
 */
class PrediksiModel extends Model
{
    protected $table            = 'prediksi';
    protected $primaryKey       = 'id_prediksi';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'id_prediksi',
        'nama_produk',
        'tahun_prediksi',
        'bulan_prediksi',
        'qty_prediksi',
    ];

    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateId'];

    // ── Auto-generate ID ───────────────────────────────────────────────────────

    protected function generateId(array $data): array
    {
        if (! isset($data['data']['id_prediksi'])) {
            $data['data']['id_prediksi'] = 'PDK-' . strtoupper(bin2hex(random_bytes(4)));
        }
        return $data;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // QUERY UTAMA
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Ambil semua prediksi untuk periode tertentu, diurutkan qty tertinggi.
     */
    public function getByPeriode(int $tahun, int $bulan): array
    {
        return $this->where('tahun_prediksi', $tahun)
            ->where('bulan_prediksi', $bulan)
            ->orderBy('qty_prediksi', 'DESC')
            ->findAll();
    }

    /**
     * Ambil periode prediksi terbaru yang ada di database.
     * Mengembalikan ['tahun' => ..., 'bulan' => ...] atau null.
     */
    public function getPeriodeTerbaru(): ?array
    {
        $row = $this->db->query("
            SELECT tahun_prediksi AS tahun, bulan_prediksi AS bulan
            FROM {$this->table}
            ORDER BY tahun_prediksi DESC, bulan_prediksi DESC
            LIMIT 1
        ")->getRowArray();

        return $row ?: null;
    }

    /**
     * Ambil prediksi terbaru (periode terkini, sorted qty DESC).
     */
    public function getTerbaru(): array
    {
        $periode = $this->getPeriodeTerbaru();
        if (! $periode) return [];

        return $this->getByPeriode((int) $periode['tahun'], (int) $periode['bulan']);
    }

    /**
     * Ambil daftar periode unik yang tersedia di tabel prediksi.
     */
    public function getDaftarPeriode(): array
    {
        return $this->db->query("
            SELECT DISTINCT tahun_prediksi AS tahun, bulan_prediksi AS bulan
            FROM {$this->table}
            ORDER BY tahun_prediksi DESC, bulan_prediksi DESC
        ")->getResultArray();
    }

    /**
     * Ambil riwayat prediksi lintas periode, diurutkan terbaru.
     * Digunakan untuk halaman riwayat.
     */
    public function getRiwayat(int $limit = 200): array
    {
        return $this->select('*')
            ->orderBy('tahun_prediksi', 'DESC')
            ->orderBy('bulan_prediksi', 'DESC')
            ->orderBy('qty_prediksi', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Ambil riwayat yang dikelompokkan per periode (untuk tampilan kartu).
     */
    public function getRiwayatGrouped(): array
    {
        $rows    = $this->getRiwayat();
        $grouped = [];
        foreach ($rows as $r) {
            $key           = $r['tahun_prediksi'] . '-' . str_pad($r['bulan_prediksi'], 2, '0', STR_PAD_LEFT);
            $grouped[$key][] = $r;
        }
        return $grouped;
    }

    /**
     * Summary statistik prediksi untuk suatu periode.
     */
    public function getSummaryPeriode(int $tahun, int $bulan): array
    {
        $row = $this->db->query("
        SELECT
            COUNT(*)                       AS total_produk,
            COALESCE(SUM(qty_prediksi), 0) AS total_qty_prediksi
        FROM {$this->table}
        WHERE tahun_prediksi = ? AND bulan_prediksi = ?
    ", [$tahun, $bulan])->getRowArray();

        return $row ?? [
            'total_produk'       => 0,
            'total_qty_prediksi' => 0,
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // INSERT / UPDATE
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Simpan atau update prediksi untuk satu produk + periode.
     * Jika sudah ada (unik per nama_produk+tahun+bulan) → update qty_prediksi.
     * Jika belum ada → insert baru.
     */
    public function upsert(string $namaProduk, int $tahun, int $bulan, float $qtyPrediksi): bool
    {
        $existing = $this->where('nama_produk', $namaProduk)
            ->where('tahun_prediksi', $tahun)
            ->where('bulan_prediksi', $bulan)
            ->first();

        if ($existing) {
            return $this->update($existing['id_prediksi'], [
                'qty_prediksi' => $qtyPrediksi,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        return (bool) $this->insert([
            'nama_produk'    => $namaProduk,
            'tahun_prediksi' => $tahun,
            'bulan_prediksi' => $bulan,
            'qty_prediksi'   => $qtyPrediksi,
        ]);
    }

    /**
     * Bulk upsert lalu langsung sinkronkan qty_aktual dari penjualan.
     * Dengan begitu selisih (GENERATED COLUMN) langsung terisi jika
     * data penjualan untuk periode tersebut sudah ada.
     *
     * @param array $items  [ ['nama_produk'=>..., 'qty_prediksi'=>...], ... ]
     */
    public function bulkUpsert(array $items, int $tahun, int $bulan): int
    {
        if (empty($items)) return 0;

        $inserted = 0;
        foreach ($items as $item) {
            $ok = $this->upsert(
                strtoupper(trim($item['nama_produk'])),
                $tahun,
                $bulan,
                (float) $item['qty_prediksi']
            );
            if ($ok) $inserted++;
        }

        return $inserted;
    }

    /**
     * Hapus semua prediksi untuk periode tertentu.
     */
    public function hapusByPeriode(int $tahun, int $bulan): bool
    {
        return $this->where('tahun_prediksi', $tahun)
            ->where('bulan_prediksi', $bulan)
            ->delete();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EVALUASI & AKURASI
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Ringkasan per-produk lintas semua periode (untuk laporan).
     */

    public function getRingkasanPerProduk(): array
    {
        return $this->db->query("
        SELECT
            nama_produk,
            COUNT(*)                       AS total_periode,
            COALESCE(SUM(qty_prediksi), 0) AS total_prediksi
        FROM {$this->table}
        GROUP BY nama_produk
        ORDER BY total_prediksi DESC
    ")->getResultArray();
    }

    /**
     * Info prediksi terakhir (untuk panel status di halaman jalankan).
     * Query kompatibel dengan sql_mode=only_full_group_by.
     */
    public function getInfoTerakhir(): ?array
    {
        $periode = $this->getPeriodeTerbaru();
        if (! $periode) return null;

        $tahun = (int) $periode['tahun'];
        $bulan = (int) $periode['bulan'];

        $row = $this->db->query("
            SELECT
                MAX(created_at)  AS created_at,
                COUNT(*)         AS total,
                {$tahun}         AS tahun_prediksi,
                {$bulan}         AS bulan_prediksi
            FROM {$this->table}
            WHERE tahun_prediksi = {$tahun}
              AND bulan_prediksi  = {$bulan}
        ")->getRowArray();

        return ($row && (int) $row['total'] > 0) ? $row : null;
    }
}
