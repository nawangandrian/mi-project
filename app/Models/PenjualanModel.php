<?php

namespace App\Models;

use CodeIgniter\Model;

class PenjualanModel extends Model
{
    protected $table            = 'penjualan';
    protected $primaryKey       = 'id_penjualan';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'id_penjualan',
        'no_nota',
        'id_produk',
        'nama_produk',
        'qty',
        'harga',
        'tanggal',
        'promo',
    ];

    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts        = [];
    protected array $castHandlers = [];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateId'];

    // ── Auto-generate ID ───────────────────────────────────────────────────────
    protected function generateId(array $data): array
    {
        if (! isset($data['data']['id_penjualan'])) {
            $data['data']['id_penjualan'] = 'TRX-' . strtoupper(bin2hex(random_bytes(4)));
        }
        return $data;
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Ambil semua penjualan beserta join ke produk (opsional, jika id_produk ada).
     */
    public function getAllWithProduk(): array
    {
        return $this->db->table($this->table . ' p')
            ->select('p.*, pr.nama_produk AS nama_produk_ref')
            ->join('produk pr', 'pr.id_produk = p.id_produk', 'left')
            ->orderBy('p.tanggal', 'DESC')
            ->orderBy('p.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Summary stats untuk dashboard cards.
     */
    public function getSummary(): array
    {
        $row = $this->db->query("
            SELECT
                COUNT(*)          AS total_transaksi,
                COALESCE(SUM(qty),0)                     AS total_qty,
                COALESCE(SUM(qty * harga),0)             AS total_omzet,
                COALESCE(SUM(CASE WHEN promo=1 THEN 1 ELSE 0 END),0) AS total_promo
            FROM {$this->table}
        ")->getRowArray();

        return $row ?? [
            'total_transaksi' => 0,
            'total_qty'       => 0,
            'total_omzet'     => 0,
            'total_promo'     => 0,
        ];
    }

    /**
     * Hapus semua data penjualan.
     */
    public function deleteAll(): bool
    {
        return $this->db->table($this->table)->truncate();
    }

    /**
     * Cek duplikat no_nota + nama_produk (untuk impor).
     */
    public function existsByNotaProduk(string $noNota, string $namaProduk): bool
    {
        return $this->where('no_nota', $noNota)
                    ->where('nama_produk', $namaProduk)
                    ->countAllResults() > 0;
    }

    /**
     * Bulk insert — beforeInsert tidak jalan di insertBatch, jadi ID di-generate manual.
     */
    public function bulkInsert(array $rows): bool
    {
        $prepared = [];
        foreach ($rows as $row) {
            $prepared[] = [
                'id_penjualan' => 'TRX-' . strtoupper(bin2hex(random_bytes(4))),
                'no_nota'      => $row['no_nota'],
                'id_produk'    => $row['id_produk']    ?? null,
                'nama_produk'  => $row['nama_produk'],
                'qty'          => (int)   ($row['qty']   ?? 1),
                'harga'        => (int)   ($row['harga'] ?? 0),
                'tanggal'      => $row['tanggal'],
                'promo'        => (int)   ($row['promo'] ?? 0),
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ];
        }
        return $this->db->table($this->table)->insertBatch($prepared) !== false;
    }
}