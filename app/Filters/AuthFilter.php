<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Jika belum login
        if (!$session->get('isLoggedIn')) {
            // Simpan URL yang diminta untuk redirect setelah login
            $session->set('redirect_url', current_url());
            
            return redirect()->to('/login')
                ->with('error', 'Silakan login terlebih dahulu');
        }
        
        // Periksa waktu aktivitas terakhir
        $lastActivity = $session->get('last_activity');
        if ($lastActivity && (time() - $lastActivity > 3600)) { // 1 jam timeout
            $session->destroy();
            return redirect()->to('/login')
                ->with('error', 'Sesi telah berakhir, silakan login kembali');
        }
        
        // Update waktu aktivitas terakhir
        $session->set('last_activity', time());
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu melakukan apa-apa setelah request
    }
}