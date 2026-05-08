<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ModelTrainingModel
 * Kelola riwayat sesi training model Random Forest.
 */
class ModelTrainingModel extends Model
{
    protected $table            = 'model_training';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'versi',
        'nama_model',
        'algoritma',
        'status',
        'mulai_at',
        'selesai_at',
        'durasi_detik',
        'total_record',
        'total_produk',
        'total_fitur',
        'cv_splits',
        'mae',
        'rmse',
        'r2',
        'mape',
        'akurasi',
        'n_estimators',
        'max_depth',
        'min_samples_split',
        'min_samples_leaf',
        'max_features',
        'path_model',
        'path_encoder',
        'ukuran_model_kb',
        'best_params',
        'feature_importance',
        'config_snapshot',
        'pesan_error',
        'dibuat_oleh',
        'is_active',
        'catatan',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ── Generate versi ─────────────────────────────────────────────────────────
    /**
     * Buat string versi otomatis: v{N} atau v{tanggal-jam}.
     */
    public function generateVersi(): string
    {
        $count = $this->countAll();
        return 'v' . ($count + 1) . '-' . date('Ymd-His');
    }

    // ── Buat sesi training baru ────────────────────────────────────────────────
    /**
     * Insert baris training baru dengan status 'running'.
     * Kembalikan ID yang baru dibuat.
     */
    public function buatSesi(array $extra = []): int
    {
        $data = array_merge([
            'versi'      => $this->generateVersi(),
            'nama_model' => 'Random Forest Regressor',
            'algoritma'  => 'RandomForestRegressor',
            'status'     => 'running',
            'mulai_at'   => date('Y-m-d H:i:s'),
            'is_active'  => 0,
        ], $extra);

        $this->insert($data);
        return (int) $this->getInsertID();
    }

    // ── Tandai selesai (sukses) ────────────────────────────────────────────────
    /**
     * Update baris setelah training berhasil.
     *
     * @param int   $id      ID sesi training
     * @param array $result  Data hasil dari train_status.json
     */
    public function tandaiSukses(int $id, array $result): bool
    {
        $mulai = $this->find($id)['mulai_at'] ?? date('Y-m-d H:i:s');
        $selesai = $result['finished_at'] ?? date('Y-m-d H:i:s');
        $durasi  = (int) abs(strtotime($selesai) - strtotime($mulai));

        // Ukuran file model (KB)
        $modelPath   = $result['model_path']   ?? '';
        $ukuranKb    = $modelPath && file_exists($modelPath)
            ? (int) round(filesize($modelPath) / 1024)
            : null;

        // Hyperparameter
        $bp = $result['best_params'] ?? [];

        return $this->update($id, [
            'status'             => 'success',
            'selesai_at'         => $selesai,
            'durasi_detik'       => $durasi,
            'total_record'       => (int)   ($result['total_sampel']  ?? 0),
            'total_produk'       => (int)   ($result['total_produk']  ?? 0),
            'mae'                => isset($result['mae'])   ? round((float) $result['mae'],  4) : null,
            'rmse'               => isset($result['rmse'])  ? round((float) $result['rmse'], 4) : null,
            'r2'                 => isset($result['r2'])    ? round((float) $result['r2'],   6) : null,
            'mape'               => isset($result['mape'])  ? round((float) $result['mape'], 4) : null,
            'akurasi'            => isset($result['akurasi']) ? round((float) $result['akurasi'], 2) : null,
            'n_estimators'       => isset($bp['n_estimators'])       ? (int) $bp['n_estimators']       : null,
            'max_depth'          => isset($bp['max_depth'])          ? (int) $bp['max_depth']          : null,
            'min_samples_split'  => isset($bp['min_samples_split'])  ? (int) $bp['min_samples_split']  : null,
            'min_samples_leaf'   => isset($bp['min_samples_leaf'])   ? (int) $bp['min_samples_leaf']   : null,
            'max_features'       => isset($bp['max_features'])       ? (string) $bp['max_features']    : null,
            'path_model'         => $modelPath                       ?: null,
            'path_encoder'       => $result['encoder_path']          ?? null,
            'ukuran_model_kb'    => $ukuranKb,
            'best_params'        => json_encode($bp,                 JSON_UNESCAPED_UNICODE),
            'feature_importance' => isset($result['feature_importance'])
                ? json_encode($result['feature_importance'], JSON_UNESCAPED_UNICODE)
                : null,
        ]);
    }

    // ── Tandai error ───────────────────────────────────────────────────────────
    public function tandaiError(int $id, string $pesan): bool
    {
        return $this->update($id, [
            'status'      => 'error',
            'selesai_at'  => date('Y-m-d H:i:s'),
            'pesan_error' => $pesan,
        ]);
    }

    // ── Aktifkan model ─────────────────────────────────────────────────────────
    /**
     * Nonaktifkan semua, lalu aktifkan model dengan id tertentu.
     */
    public function aktifkan(int $id): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        // Nonaktifkan semua — pakai query builder langsung agar tidak butuh WHERE di Model
        $db->table($this->table)->update(['is_active' => 0]);
        // Aktifkan yang dipilih
        $this->update($id, ['is_active' => 1]);
        $db->transComplete();
        return $db->transStatus();
    }

    // ── Arsipkan ───────────────────────────────────────────────────────────────
    public function arsipkan(int $id): bool
    {
        return $this->update($id, ['status' => 'archived', 'is_active' => 0]);
    }

    // ── Query helpers ──────────────────────────────────────────────────────────
    /** Model aktif yang sedang dipakai prediksi. */
    public function getAktif(): ?array
    {
        return $this->where('is_active', 1)->first();
    }

    /** Semua model diurutkan terbaru. */
    public function getAllOrdered(): array
    {
        return $this->orderBy('mulai_at', 'DESC')->findAll();
    }

    /** Semua model sukses. */
    public function getSukses(): array
    {
        return $this->where('status', 'success')
            ->orderBy('selesai_at', 'DESC')
            ->findAll();
    }

    /** Ringkasan untuk dashboard card. */
    public function getRingkasan(): array
    {
        $row = $this->db->query("
        SELECT
            COUNT(*)                                          AS total_sesi,
            SUM(status = 'success')                          AS total_sukses,
            SUM(status = 'error')                            AS total_error,
            SUM(status = 'running')                          AS total_running,
            MAX(CASE WHEN is_active = 1 THEN akurasi END)    AS akurasi_aktif,
            MAX(CASE WHEN is_active = 1 THEN versi   END)    AS versi_aktif,
            MAX(CASE WHEN is_active = 1 THEN selesai_at END) AS terlatih_aktif,
            MAX(selesai_at)                                   AS training_terakhir,
            MIN(CASE WHEN status = 'success' THEN mae END)   AS best_mae,
            MAX(CASE WHEN status = 'success' THEN r2  END)   AS best_r2,
            COUNT(*)                                          AS total_model
        FROM {$this->table}
    ")->getRowArray();

        return $row ?? [
            'total_sesi'        => 0,
            'total_sukses'      => 0,
            'total_error'       => 0,
            'total_running'     => 0,
            'akurasi_aktif'     => null,
            'versi_aktif'       => null,
            'terlatih_aktif'    => null,
            'training_terakhir' => null,
            'best_mae'          => null,
            'best_r2'           => null,
            'total_model'       => 0,
        ];
    }

    /** Bandingkan beberapa model (array of id). */
    public function bandingkan(array $ids): array
    {
        return $this->whereIn('id', $ids)
            ->select('id,versi,status,akurasi,mae,rmse,r2,mape,n_estimators,max_depth,total_record,selesai_at,is_active')
            ->orderBy('akurasi', 'DESC')
            ->findAll();
    }

    /** Hapus model lama yang di-archive lebih dari N hari. */
    public function hapusUsang(int $hari = 30): int
    {
        $batas = date('Y-m-d H:i:s', strtotime("-{$hari} days"));
        $this->where('status', 'archived')
            ->where('updated_at <', $batas)
            ->delete();
        return $this->db->affectedRows();
    }

    /**
     * Model aktif dengan semua kolom (termasuk kolom migration baru:
     * mae_test, rmse_test, r2_test, mape_test, akurasi_test, diagram_paths).
     *
     * Juga decode semua kolom JSON dan memprioritaskan tabel
     * model_training_diagram untuk diagram_paths.
     */
    public function getAktifDetail(): ?array
    {
        $row = $this->where('is_active', 1)->first();
        if (! $row) return null;

        // Helper: decode kolom JSON dengan aman — CI kadang sudah mengembalikan array
        $jsonDecode = static function (mixed $value): array {
            if (is_array($value))  return $value;
            if (! is_string($value) || $value === '') return [];
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        };

        $row['best_params']        = $jsonDecode($row['best_params']        ?? '');
        $row['feature_importance'] = $jsonDecode($row['feature_importance'] ?? '');
        $row['diagram_paths']      = $jsonDecode($row['diagram_paths']      ?? '');

        // Prioritaskan tabel model_training_diagram (jika ada)
        $diagRows = $this->db->table('model_training_diagram')
            ->where('training_id', $row['id'])
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        if (! empty($diagRows)) {
            $paths = [];
            foreach ($diagRows as $d) {
                $paths[$d['diagram_key']] = $d['path'];
            }
            $row['diagram_paths'] = $paths;
        }

        return $row;
    }

    /**
     * Ambil N model sukses terakhir untuk tabel perbandingan di halaman evaluasi.
     *
     * @param int $limit Jumlah model yang diambil (default 10)
     */
    public function getTopSukses(int $limit = 10): array
    {
        return $this->where('status', 'success')
            ->select('id, versi, status, is_active, akurasi, mae, rmse, r2, mape,
                  total_record, total_produk, selesai_at, durasi_detik')
            ->orderBy('selesai_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
