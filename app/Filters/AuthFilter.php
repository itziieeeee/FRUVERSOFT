<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Verificar que haya iniciado sesión
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))
                ->with('error', 'Debes iniciar sesión primero.');
        }

        $rolUsuario = session()->get('rol');

        // 2. Obligar a repartidores y vendedores a cambiar
        // su contraseña temporal
        if (
            in_array($rolUsuario, ['repartidor', 'vendedor'], true)
            && (int) session()->get('debe_cambiar_password') === 1
            && !str_ends_with(
                $request->getUri()->getPath(),
                'cuenta/cambiar-password'
            )
        ) {
            return redirect()->to(base_url('cuenta/cambiar-password'));
        }

        // 3. Verificar el rol requerido por la ruta
        if ($arguments !== null && !empty($arguments)) {

            $rolRequerido = $arguments[0];

            if ($rolUsuario !== $rolRequerido) {

                if ($rolUsuario === 'admin') {
                    return redirect()->to(base_url('menusolo'));
                }

                if ($rolUsuario === 'repartidor') {
                    return redirect()->to(base_url('repartidor/app'));
                }

                if ($rolUsuario === 'vendedor') {
                    return redirect()->to(base_url('vendedor/panel'));
                }

                return redirect()->to(base_url('cliente/menu'));
            }
        }
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
        // No se requiere ninguna acción después de renderizar
    }
}
