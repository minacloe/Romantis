<?php

namespace App\Libraries;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FirebaseService
{
    protected $auth;

    public function __construct()
    {
        $serviceAccountPath = config('Firebase')->serviceAccountPath;
        
        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath);
            
        $this->auth = $factory->createAuth();
    }

    public function verifyIdToken($idToken)
    {
        try {
            return $this->auth->verifyIdToken($idToken);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getUserByEmail($email)
    {
        try {
            return $this->auth->getUserByEmail($email);
        } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
            return null;
        }
    }

    public function sendPasswordResetEmail($email)
    {
        $apiKey = config('Firebase')->apiKey;
        $endpoint = "https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key={$apiKey}";
    
        $postData = json_encode([
            'requestType' => 'PASSWORD_RESET',
            'email' => $email
        ]);
    
        $client = \Config\Services::curlrequest();
        $response = $client->request('POST', $endpoint, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => $postData
        ]);
    
        $status = $response->getStatusCode();
        $body = json_decode($response->getBody(), true);
    
        if ($status === 200) {
            return true;
        } else {
            throw new \Exception('Gagal mengirim email reset: ' . ($body['error']['message'] ?? 'Tidak diketahui'));
        }
    }
    
    public function signInWithEmailAndPassword($email, $password)
    {
        $apiKey = config('Firebase')->apiKey;
        $endpoint = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key={$apiKey}";

        $postData = json_encode([
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true
        ]);

        $client = \Config\Services::curlrequest();
        $response = $client->request('POST', $endpoint, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => $postData
        ]);

        $status = $response->getStatusCode();
        $body = json_decode($response->getBody(), true);

        if ($status === 200) {
            return $body;
        } else {
            throw new \Exception('Firebase login error: ' . ($body['error']['message'] ?? 'Tidak diketahui'));
        }
    }

    public function createUser($email, $password)
    {
        return $this->auth->createUser([
            'email' => $email,
            'password' => $password
        ]);
    }

    public function updateUser($oldEmail, $data)
    {
        try {
            // Cari user Firebase berdasarkan email lama
            $user = $this->auth->getUserByEmail($oldEmail);

            // Siapkan data update
            $updateData = [];

            if (isset($data['email'])) {
                $updateData['email'] = $data['email'];
            }

            if (isset($data['password'])) {
                $updateData['password'] = $data['password'];
            }

            // Lakukan update di Firebase
            return $this->auth->updateUser($user->uid, $updateData);
        } catch (\Exception $e) {
            throw new \Exception('Gagal update akun di Firebase: ' . $e->getMessage());
        }
    }


    public function deleteUserByEmail($email)
    {
        try {
            $user = $this->auth->getUserByEmail($email);
            $this->auth->deleteUser($user->uid);
            return true;
        } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
            // User tidak ditemukan, anggap sudah terhapus
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Gagal menghapus akun dari Firebase: ' . $e->getMessage());
        }
    }


}