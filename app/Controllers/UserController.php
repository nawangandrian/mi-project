<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form']);
    }

    /* ================= INDEX ================= */
    public function index()
    {
        $users = $this->userModel
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->renderPage('pages/user/index', [
            'title'      => 'Manajemen User',
            'page_title' => 'Manajemen User',
            'users'      => $users,
        ]);
    }

    /* ================= STORE ================= */
    public function store()
    {
        // Terima AJAX dari fetch() maupun jQuery
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $rules = [
            'username' => 'required|min_length[4]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[admin,cs]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error_validation',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $this->userModel->insert([
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => $this->request->getPost('role'),
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'User berhasil ditambahkan',
        ]);
    }

    /* ================= GET DATA ================= */
    public function getData()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $id   = $this->request->getPost('id');
        $user = $this->userModel->find($id);

        if (! $user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User tidak ditemukan']);
        }

        return $this->response->setJSON($user);
    }

    /* ================= UPDATE ================= */
    public function update()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $id   = $this->request->getPost('id_user');
        $user = $this->userModel->find($id);

        if (! $user) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'User tidak ditemukan',
            ]);
        }

        $rules = [
            'username' => "required|min_length[4]|is_unique[users.username,id_user,{$id}]",
            'role'     => 'required|in_list[admin,cs]',
        ];

        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[6]';
        }

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error_validation',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $dataUpdate = [
            'username' => $this->request->getPost('username'),
            'role'     => $this->request->getPost('role'),
        ];

        if ($this->request->getPost('password')) {
            $dataUpdate['password'] = password_hash(
                $this->request->getPost('password'),
                PASSWORD_DEFAULT
            );
        }

        $this->userModel->update($id, $dataUpdate);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'User berhasil diperbarui',
        ]);
    }

    /* ================= DELETE ================= */
    public function delete()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }

        $id = $this->request->getPost('id');

        if (! $this->userModel->find($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User tidak ditemukan']);
        }

        $this->userModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'User berhasil dihapus',
        ]);
    }
}