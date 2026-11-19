<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class AuthController extends BaseController
{
    public function login()
{
    if (session()->get('isLoggedIn')) {
        $rol = session()->get('rol');

        if ($rol === 'admin') {
            return redirect()->to(base_url('menusolo'));
        }

        if ($rol === 'repartidor') {
            return !empty(session()->get('debe_cambiar_password'))
                ? redirect()->to(base_url('cuenta/cambiar-password'))
                : redirect()->to(base_url('repartidor/app'));
        }

        if ($rol === 'vendedor') {
            return !empty(session()->get('debe_cambiar_password'))
                ? redirect()->to(base_url('cuenta/cambiar-password'))
                : redirect()->to(base_url('vendedor/panel'));
        }

        return redirect()->to(base_url('cliente/menu'));
    }

    return view('auth/login');
}
    public function ingresar()
    {
        $db = \Config\Database::connect();

        $identificador = trim(
            (string) ($this->request->getPost('identificador')
            ?: $this->request->getPost('correo'))
        );

        $password = (string) $this->request->getPost('password');

        $usuario = $db->table('usuarios')
            ->groupStart()
                ->where('correo', $identificador)
                ->orWhere('usuario', $identificador)
            ->groupEnd()
            ->get()
            ->getRowArray();

        if (
            $usuario &&
            (int) ($usuario['activo'] ?? 1) !== 0 &&
            password_verify($password, $usuario['password'])
        ) {
            session()->regenerate(true);

            session()->set([
                'id_usuario' => $usuario['id'],
                'id_repartidor' => $usuario['id_repartidor'] ?? null,
                'nombre' => $usuario['nombre'],
                'correo' => $usuario['correo'],
                'rol' => $usuario['rol'],
                'debe_cambiar_password' =>
                    (int) ($usuario['debe_cambiar_password'] ?? 0),
                'isLoggedIn' => true,
            ]);

            if (password_needs_rehash(
                $usuario['password'],
                PASSWORD_DEFAULT
            )) {
                $db->table('usuarios')
                    ->where('id', $usuario['id'])
                    ->update([
                        'password' => password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        ),
                    ]);
            }

            if ($usuario['rol'] === 'admin') {
                return redirect()->to(base_url('menusolo'));
            }

            if ($usuario['rol'] === 'repartidor') {
                return !empty($usuario['debe_cambiar_password'])
                    ? redirect()->to(base_url('cuenta/cambiar-password'))
                    : redirect()->to(base_url('repartidor/app'));
            }

            if ($usuario['rol'] === 'vendedor') {
    return !empty($usuario['debe_cambiar_password'])
        ? redirect()->to(base_url('cuenta/cambiar-password'))
        : redirect()->to(base_url('vendedor/panel'));
}

            return redirect()->to(base_url('cliente/menu'));
        }

        return redirect()->back()->with(
            'error',
            'Correo, usuario o contraseña incorrectos.'
        );
    }

    public function registro()
    {
        return view('auth/registro');
    }

    public function guardarRegistro()
    {
        $db = \Config\Database::connect();

        $correo = $this->request->getPost('correo');

        $existe = $db->table('usuarios')
            ->where('correo', $correo)
            ->get()
            ->getRowArray();

        if ($existe) {
            return redirect()->back()->with(
                'error',
                'El correo ya está registrado.'
            );
        }

        $passwordHash = password_hash(
            $this->request->getPost('password'),
            PASSWORD_DEFAULT
        );

        $db->table('usuarios')->insert([
            'nombre' => $this->request->getPost('nombre'),
            'correo' => $correo,
            'password' => $passwordHash,
            'rol' => 'cliente'
        ]);

        return redirect()->to(base_url('login'))->with(
            'mensaje',
            'Registro exitoso. Inicia sesión.'
        );
    }

    public function cambiarPassword()
    {
        return view('auth/cambiar_password');
    }

    public function guardarPassword()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        $actual = (string) $this->request->getPost('password_actual');
        $nueva = (string) $this->request->getPost('password_nueva');
        $confirmacion = (string) $this->request->getPost('password_confirmacion');

        if (strlen($nueva) < 8 || $nueva !== $confirmacion) {
            return redirect()->back()->with(
                'error',
                'La nueva contraseña debe tener al menos 8 caracteres y coincidir con la confirmación.'
            );
        }

        $db = \Config\Database::connect();

        $usuario = $db->table('usuarios')
            ->where('id', (int) session()->get('id_usuario'))
            ->get()
            ->getRowArray();

        if (!$usuario || !password_verify($actual, $usuario['password'])) {
            return redirect()->back()->with(
                'error',
                'La contraseña actual no es correcta.'
            );
        }

        $db->table('usuarios')
            ->where('id', $usuario['id'])
            ->update([
                'password' => password_hash($nueva, PASSWORD_DEFAULT),
                'debe_cambiar_password' => 0,
                'password_actualizado_at' => date('Y-m-d H:i:s'),
            ]);

        session()->set('debe_cambiar_password', 0);

        $rol = session()->get('rol');

        if ($rol === 'repartidor') {
            $destino = 'repartidor/app';
        } elseif ($rol === 'vendedor') {
            $destino = 'vendedor/panel';
        } else {
            $destino = 'menusolo';
        }

        return redirect()->to(base_url($destino))->with(
            'mensaje',
            'Contraseña actualizada correctamente.'
        );
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(base_url('/'));
    }
}
