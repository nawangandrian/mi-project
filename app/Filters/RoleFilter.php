<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * RoleFilter
 *
 * Membatasi akses route berdasarkan role user.
 * Cara pakai di routes.php:
 *   ['filter' => 'role:admin']   → hanya admin
 *   ['filter' => 'role:cs']      → hanya cs
 *
 * Role hierarki (dari tertinggi):
 *   admin  → akses semua
 *   cs     → hanya dashboard, produk, penjualan, import
 */
class RoleFilter implements FilterInterface
{
    /**
     * @param array|null $arguments Role yang diizinkan, contoh: ['admin']
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = session()->get('role');

        // Belum login sama sekali → redirect ke login
        if (! session()->get('logged_in') || ! $role) {
            return redirect()->to('/login');
        }

        // Jika tidak ada argumen role yang didefinisikan, lewati
        if (empty($arguments)) {
            return; // tidak ada pembatasan
        }

        // Cek apakah role user ada di daftar role yang diizinkan
        if (! in_array($role, $arguments, true)) {
            // Tampilkan halaman 403 atau redirect ke dashboard dengan flash message
            return redirect()->to('/dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu aksi setelah response
    }
}