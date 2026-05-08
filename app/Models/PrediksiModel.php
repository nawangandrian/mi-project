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
        'qty_aktual',
        // kolom 'selisih' adalah GENERATED COLUMN — tidak boleh diisi lewat insert/update
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
                COUNT(*)                                    AS total_produk,
                COALESCE(SUM(qty_prediksi), 0)              AS total_qty_prediksi,
                COALESCE(SUM(qty_aktual), 0)                AS total_qty_aktual,
                COALESCE(AVG(
                    CASE WHEN qty_aktual > 0
                    THEN ABS(qty_aktual - qty_prediksi) / qty_aktual * 100
                    END
                ), 0)                                       AS rata_mape,
                COUNT(CASE WHEN qty_aktual IS NOT NULL THEN 1 END) AS total_terverifikasi
            FROM {$this->table}
            WHERE tahun_prediksi = ? AND bulan_prediksi = ?
        ", [$tahun, $bulan])->getRowArray();

        return $row ?? [
            'total_produk'        => 0,
            'total_qty_prediksi'  => 0,
            'total_qty_aktual'    => 0,
            'rata_mape'           => 0,
            'total_terverifikasi' => 0,
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

        // ── Otomatis isi qty_aktual jika data penjualan periode ini sudah ada ──
        $this->sinkronAktual($tahun, $bulan);

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

    /**
     * Update qty_aktual untuk verifikasi setelah bulan berjalan.
     */
    public function updateAktual(string $idPrediksi, int $qtyAktual): bool
    {
        return $this->update($idPrediksi, [
            'qty_aktual' => $qtyAktual,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SINKRONISASI AKTUAL
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Sinkronisasi qty_aktual dari tabel penjualan ke tabel prediksi
     * untuk satu periode tertentu.
     *
     * Cara kerja:
     *   1. GROUP BY nama_produk dari penjualan bulan/tahun tersebut → total qty
     *   2. UPDATE prediksi.qty_aktual yang nama_produknya cocok
     *
     * Dipanggil otomatis oleh bulkUpsert() dan bisa juga dipanggil manual
     * dari controller (tombol "Sinkron Aktual" atau cron job).
     *
     * @return int jumlah baris prediksi yang berhasil diupdate
     */
    public function sinkronAktual(int $tahun, int $bulan): int
    {
        // Gunakan single UPDATE + JOIN agar efisien — satu query, bukan loop
        $sql = "
            UPDATE {$this->table} p
            INNER JOIN (
                SELECT
                    UPPER(TRIM(nama_produk)) AS nama_produk,
                    SUM(qty)                 AS qty_aktual
                FROM penjualan
                WHERE YEAR(tanggal)  = ?
                  AND MONTH(tanggal) = ?
                GROUP BY UPPER(TRIM(nama_produk))
            ) pj ON pj.nama_produk = UPPER(TRIM(p.nama_produk))
            SET
                p.qty_aktual  = pj.qty_aktual,
                p.updated_at  = NOW()
            WHERE p.tahun_prediksi = ?
              AND p.bulan_prediksi  = ?
        ";

        $this->db->query($sql, [$tahun, $bulan, $tahun, $bulan]);

        // Kembalikan jumlah baris yang terpengaruh
        return $this->db->affectedRows();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EVALUASI & AKURASI
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Hitung MAPE keseluruhan dari semua prediksi yang sudah ada aktualnya.
     */
    public function hitungMapeGlobal(): ?float
    {
        $row = $this->db->query("
            SELECT AVG(
                CASE WHEN qty_aktual > 0
                THEN ABS(qty_aktual - qty_prediksi) / qty_aktual * 100
                END
            ) AS mape
            FROM {$this->table}
            WHERE qty_aktual IS NOT NULL
        ")->getRowArray();

        return isset($row['mape']) ? round((float) $row['mape'], 4) : null;
    }

    /**
     * Hitung akurasi global (100 - MAPE).
     */
    public function hitungAkurasiGlobal(): ?float
    {
        $mape = $this->hitungMapeGlobal();
        return $mape !== null ? max(0, round(100 - $mape, 2)) : null;
    }

    /**
     * Ringkasan per-produk lintas semua periode (untuk laporan).
     */
    public function getRingkasanPerProduk(): array
    {
        return $this->db->query("
            SELECT
                nama_produk,
                COUNT(*)                                    AS total_periode,
                COALESCE(SUM(qty_prediksi), 0)              AS total_prediksi,
                COALESCE(SUM(qty_aktual), 0)                AS total_aktual,
                COALESCE(AVG(
                    CASE WHEN qty_aktual > 0
                    THEN ABS(qty_aktual - qty_prediksi) / qty_aktual * 100
                    END
                ), NULL)                                    AS mape_avg,
                COUNT(CASE WHEN qty_aktual IS NOT NULL THEN 1 END) AS terverifikasi
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