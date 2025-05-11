<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $userRole = strtolower($session->get('tipe_akun'));

        if (empty($arguments)) {
            return;
        }

        $requiredRoles = is_array($arguments) ? $arguments : explode(',', $arguments);
        $requiredRoles = array_map(fn($r) => strtolower(trim($r)), $requiredRoles);

        if (!in_array($userRole, $requiredRoles)) {
            return redirect()->to('/login')->with('error', 'Anda tidak memiliki akses.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here if needed
    }
}
