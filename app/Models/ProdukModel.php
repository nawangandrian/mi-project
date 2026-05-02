<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdukModel extends Model
{
    protected $table            = 'produk';
    protected $primaryKey       = 'id_produk';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'id_produk',
        'nama_produk',
        'is_active',
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
        if (! isset($data['data']['id_produk'])) {
            $data['data']['id_produk'] = 'PRD-' . strtoupper(bin2hex(random_bytes(4)));
        }
        return $data;
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Cari produk berdasarkan nama (case-insensitive, partial match).
     */
    public function searchByName(string $keyword): array
    {
        return $this->like('nama_produk', $keyword)->orderBy('nama_produk', 'ASC')->findAll();
    }

    /**
     * Ambil produk aktif saja, diurutkan nama A-Z.
     */
    public function getActive(): array
    {
        return $this->where('is_active', 1)->orderBy('nama_produk', 'ASC')->findAll();
    }

    /**
     * Hapus semua data produk (truncate-style via deleteAll).
     * Karena ada FK dari penjualan, kita pakai soft-delete manual:
     * set is_active=0 atau hard-delete bergantung kebutuhan.
     * Di sini: hard-delete semua (pastikan FK sudah di-SET NULL).
     */
    public function deleteAll(): bool
    {
        return $this->db->table($this->table)->emptyTable();
    }

    /**
     * Bulk insert array produk.
     * Setiap row minimal punya: nama_produk, harga.
     * id_produk di-generate otomatis lewat beforeInsert callback,
     * tapi insertBatch tidak memanggil callback — jadi kita generate manual.
     */

    public function bulkInsert(array $rows): bool
    {
        $prepared = [];
        foreach ($rows as $row) {
            $prepared[] = [
                'id_produk'   => 'PRD-' . strtoupper(bin2hex(random_bytes(4))),
                'nama_produk' => $row['nama_produk'],
                'is_active'   => $row['is_active'] ?? 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
        }
        return $this->db->table($this->table)->insertBatch($prepared) !== false;
    }

    /**
     * Cek apakah nama produk sudah ada (untuk validasi duplikasi impor).
     */
    public function existsByName(string $name): bool
    {
        return $this->where('nama_produk', $name)->countAllResults() > 0;
    }
}
