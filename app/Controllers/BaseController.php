<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController
 *
 * Semua controller yang membutuhkan layout (sidebar + header + footer)
 * harus extends BaseController ini.
 *
 * Untuk controller yang TIDAK butuh layout (misal: AuthController),
 * langsung extends CodeIgniter\Controller saja.
 */
class BaseController extends Controller
{
    /**
     * @var IncomingRequest|CLIRequest
     */
    protected $request;

    /**
     * Helper yang otomatis di-load untuk semua controller turunan.
     */
    protected $helpers = ['url', 'form', 'text'];

    /**
     * Data global yang tersedia di semua view (layout).
     * Bisa di-override / ditambah di controller turunan.
     */
    protected array $globalData = [];

    // ──────────────────────────────────────────────────────────────────────
    public function initController(
        RequestInterface  $request,
        ResponseInterface $response,
        LoggerInterface   $logger
    ): void {
        parent::initController($request, $response, $logger);

        // Redirect ke login jika belum login
        // (hapus komentar jika fitur auth sudah siap)
        // if (! session()->get('isLoggedIn')) {
        //     redirect()->to(base_url('auth/login'))->send();
        //     exit;
        // }

        // Data global yang dilewatkan ke semua view
        $this->globalData = [
            'notif_count' => $this->getNotifCount(),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    /**
     * Render halaman lengkap dengan layout:
     *   layout/head  → layout/sidebar → layout/header
     *   → $viewPath  (konten halaman)
     *   → layout/footer → layout/scripts
     *
     * @param  string $viewPath  Path view konten, misal 'pages/dashboard'
     * @param  array  $data      Data khusus halaman ini
     * @param  string $scripts   HTML <script> tambahan untuk halaman
     * @return string            Output HTML lengkap
     */
    protected function renderPage(string $viewPath, array $data = [], string $scripts = ''): string
    {
        $viewData = array_merge($this->globalData, $data, ['scripts' => $scripts]);

        $output  = view('layout/head',    $viewData);
        $output .= view('layout/sidebar', $viewData);
        $output .= view('layout/header',  $viewData);

        $output .= '<div id="main-content"><div class="content-wrapper">';
        $output .= view($viewPath, $viewData);
        $output .= '</div></div>';

        // ↓ Tambah ini — portal untuk modal agar di luar #main-content
        $output .= '<div id="modal-portal"></div>';

        $output .= view('layout/footer',  $viewData);
        $output .= view('layout/scripts', $viewData);

        return $output;
    }

    // ──────────────────────────────────────────────────────────────────────
    /**
     * Helper: ambil jumlah notifikasi belum dibaca.
     * Ganti implementasi sesuai model / tabel yang kamu punya.
     */
    protected function getNotifCount(): int
    {
        // Contoh dummy — ganti dengan query nyata:
        // return model('NotifikasiModel')->countUnread(session()->get('user_id'));
        return 3;
    }

    // ──────────────────────────────────────────────────────────────────────
    /**
     * Helper: kirim JSON response (untuk AJAX / API endpoint).
     */
    protected function jsonResponse(mixed $data, int $code = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($code)
            ->setJSON($data);
    }
}