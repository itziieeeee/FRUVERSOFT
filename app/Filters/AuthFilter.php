<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Si no ha iniciado sesión, mándalo al login
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))->with('error', 'Debes iniciar sesión primero.');
        }

        if (session()->get('rol') === 'repartidor'
            && (int) session()->get('debe_cambiar_password') === 1
            && !str_ends_with($request->getUri()->getPath(), 'cuenta/cambiar-password')) {
            return redirect()->to(base_url('cuenta/cambiar-password'));
        }

        // 2. Si la ruta requiere un rol específico (admin o cliente), verificarlo
        if ($arguments !== null && !empty($arguments)) {
            $rolRequerido = $arguments[0];
            $rolUsuario   = session()->get('rol');

            if ($rolUsuario !== $rolRequerido) {
    if ($rolUsuario === 'admin') return redirect()->to(base_url('menusolo'));
    if ($rolUsuario === 'repartidor') return redirect()->to(base_url('repartidor/app'));
    return redirect()->to(base_url('cliente/menu'));
}
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se requiere ninguna acción después de renderizar
    }
}
