<?php

namespace App\Controllers;

use App\Models\AkunModel;
use App\Libraries\FirebaseService;

class AuthController extends BaseController
{
    protected $firebase;

    public function __construct()
    {
        $this->firebase = new FirebaseService();
    }


    public function index()
    {
        // Jika sudah login, redirect ke dashboard
        if (session()->get('logged_in')) {
            return redirect()->to($this->getRedirectUrl(session()->get('tipe_akun')));
        }
        
        return view('login');
    }

    public function process()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $email = trim($this->request->getPost('email'));
        $password = $this->request->getPost('password');

        if (empty($email) || empty($password)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Email dan password harus diisi'
            ]);
        }

        try {
            // Cek di Firebase
            $firebaseUser = $this->firebase->getUserByEmail($email);
            if (!$firebaseUser) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Email tidak terdaftar di Firebase'
                ]);
            }

            // Cek di database lokal
            $akunModel = new AkunModel();
            $user = $akunModel->where('email', $email)->first();

            if (!$user) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Email tidak ditemukan di database lokal'
                ]);
            }

            // Cek password lokal
            if (!password_verify($password, $user['password'])) {
                // Coba login ke Firebase (berarti user habis reset password)
                try {
                    $signIn = $this->firebase->signInWithEmailAndPassword($email, $password);
                    
                    // Jika berhasil, update password di SQL
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $akunModel->update($user['id'], [
                        'password' => $hashedPassword,
                        'password_temp' => $password
                    ]);
                } catch (\Exception $e) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Password salah atau belum disinkronkan'
                    ]);
                }
            }

            // Set session
            $sessionData = [
                'id' => $user['id'],
                'nama' => $user['nama'],
                'email' => $user['email'],
                'tipe_akun' => $user['tipe_akun'],
                'isLoggedIn' => true,
                'firebase_uid' => $firebaseUser->uid,
                'last_activity' => time()
            ];        

            session()->set($sessionData);
            session()->regenerate();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => $this->getRedirectUrl($user['tipe_akun'])
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }


    protected function getRedirectUrl($role)
    {
        switch ($role) {
            case 'Super Admin':
                return base_url('/dashboard/super-admin');
            case 'Admin':
                return base_url('/dashboard/admin');
            case 'Operator':
                return base_url('/dashboard/operator');
        }
    }

    public function logout()
    {
        session()->stop(); // aman meski session belum dimulai
        return redirect()->to('/login');
    }    

    public function forgotPassword()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $email = trim($this->request->getPost('email'));

        if (empty($email)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Silakan masukkan email terlebih dahulu.'
            ]);
        }

        try {
            $firebaseUser = $this->firebase->getUserByEmail($email);

            if (!$firebaseUser) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Email tidak ditemukan di Firebase.'
                ]);
            }

            // Kirim email reset password menggunakan Firebase
            $this->firebase->sendPasswordResetEmail($email);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Link reset password telah dikirim ke email Anda.'
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

}