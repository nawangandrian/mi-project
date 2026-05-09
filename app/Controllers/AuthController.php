<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ModelTrainingModel;

class AuthController extends BaseController
{
    protected UserModel          $userModel;
    protected ModelTrainingModel $modelTrainingModel;

    public function __construct()
    {
        $this->userModel          = new UserModel();
        $this->modelTrainingModel = new ModelTrainingModel();
        helper(['form']);
    }

    /**
     * Halaman login — route: GET /login
     */
    public function index(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        // ── Model aktif dari DB ────────────────────────────────────────────────
        $modelAktif = $this->modelTrainingModel->getAktif();

        // ── Ringkasan model (total model, terlatih terakhir, dll) ─────────────
        // Gunakan method yang sudah ada di ModelTrainingModel
        $ringkasanModel = $this->modelTrainingModel->getRingkasan();

        // ── Konfigurasi default fallback dari DB ───────────────────────────────
        // Jika ada model aktif, ambil parameter dari sana.
        // Jika tidak, coba ambil model terakhir apapun statusnya.
        // Jika benar-benar kosong, baru pakai nilai default.
        if ($modelAktif) {
            // Nama kolom sesuai tabel model_training:
            // r2 (bukan r2_score), akurasi, cv_splits (bukan cv_fold)
            $modelConfig = [
                'algoritma'     => $modelAktif['algoritma']    ?? 'Random Forest',
                'r2_range'      => ! empty($modelAktif['r2'])
                                    ? number_format((float)$modelAktif['r2'], 3)
                                    : '–',
                'akurasi_range' => ! empty($modelAktif['akurasi'])
                                    ? number_format((float)$modelAktif['akurasi'], 2) . '%'
                                    : '–',
                'cross_val'     => ! empty($modelAktif['cv_splits'])
                                    ? $modelAktif['cv_splits'] . '-fold CV'
                                    : '5-fold CV',
                'n_estimators'  => ! empty($modelAktif['n_estimators'])
                                    ? $modelAktif['n_estimators'] . ' Trees'
                                    : '–',
                'max_depth'     => $modelAktif['max_depth'] ?? '–',
            ];
        } else {
            // Tidak ada model aktif — gunakan ringkasan (best_r2) atau teks kosong
            $modelConfig = [
                'algoritma'     => 'Random Forest',
                'r2_range'      => ! empty($ringkasanModel['best_r2'])
                                    ? '~ ' . number_format((float)$ringkasanModel['best_r2'], 3)
                                    : 'Belum ada model',
                'akurasi_range' => ! empty($ringkasanModel['akurasi_aktif'])
                                    ? number_format((float)$ringkasanModel['akurasi_aktif'], 2) . '%'
                                    : '–',
                'cross_val'     => '5-fold CV',
                'n_estimators'  => '–',
                'max_depth'     => '–',
            ];
        }

        // ── Stack teknologi — versi diambil dari konstanta PHP ────────────────
        $ciVersion  = defined('\CodeIgniter\CodeIgniter::CI_VERSION')
                        ? \CodeIgniter\CodeIgniter::CI_VERSION
                        : '4.x';
        $phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;

        $specs = [
            ['bi-braces',         'Backend',       "CodeIgniter {$ciVersion} · PHP {$phpVersion} · MySQL 8"],
            ['bi-robot',          'ML Engine',     'Python 3 · scikit-learn · RandomForestRegressor'],
            ['bi-bar-chart-line', 'Evaluasi',      'R² · RMSE · MAE · MAPE · CV 5-fold'],
            ['bi-funnel',         'Data Pipeline', 'Pandas · NumPy · Pipeline · ColumnTransformer'],
            ['bi-globe',          'Frontend',      'Bootstrap 5 · Chart.js · DataTables'],
            ['bi-shield-lock',    'Keamanan',      'Session Auth · CSRF · Role-based Access'],
        ];

        return view('pages/auth/index', [
            'modelAktif'     => $modelAktif,
            'ringkasanModel' => $ringkasanModel,
            'modelConfig'    => $modelConfig,
            'specs'          => $specs,
        ]);
    }

    /**
     * Proses login — AJAX POST
     * Route: POST /login/attempt
     */
    public function authenticate(): \CodeIgniter\HTTP\ResponseInterface
    {
        $rules = [
            'login'    => 'required',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error_validation',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $loginInput = $this->request->getPost('login');
        $password   = $this->request->getPost('password');

        $user = $this->userModel->getUserByLogin($loginInput);

        if (! $user) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Akun tidak ditemukan.',
            ]);
        }

        if (! password_verify($password, $user['password'])) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Password salah.',
            ]);
        }

        session()->set([
            'id_user'   => $user['id_user'],
            'username'  => $user['username'],
            'nama'      => $user['nama']  ?? $user['username'],
            'email'     => $user['email'] ?? '',
            'role'      => $user['role'],
            'logged_in' => true,
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Login berhasil.',
        ]);
    }

    /**
     * Logout — Route: GET /logout
     */
    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}