<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form']);
    }

    /**
     * Halaman login — route: GET /login
     * Hanya menampilkan form login, tanpa data landing page.
     */
    public function index(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('pages/auth/index', [
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
