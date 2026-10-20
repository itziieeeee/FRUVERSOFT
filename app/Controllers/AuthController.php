<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return session()->get('rol') === 'admin' 
                ? redirect()->to(base_url('menusolo'))
                : redirect()->to(base_url('cliente/menu'));
        }
        return view('auth/login');
    }

    public function ingresar()
    {
        $db = \Config\Database::connect();
        $correo = trim($this->request->getPost('correo'));
        $password = $this->request->getPost('password');

        $usuario = $db->table('usuarios')->where('correo', $correo)->get()->getRowArray();

        if ($usuario && password_verify($password, $usuario['password'])) {
            session()->set([
                'id_usuario' => $usuario['id'],
                'nombre'     => $usuario['nombre'],
                'correo'     => $usuario['correo'],
                'rol'        => $usuario['rol'],
                'isLoggedIn' => true
            ]);

            if ($usuario['rol'] === 'admin') {
                return redirect()->to(base_url('menusolo'));
            } else {
                return redirect()->to(base_url('cliente/menu'));
            }
        }

        return redirect()->back()->with('error', 'Correo o contraseña incorrectos.');
    }

    public function registro()
    {
        return view('auth/registro');
    }

    public function guardarRegistro()
    {
        $db = \Config\Database::connect();
        $correo = $this->request->getPost('correo');

        $existe = $db->table('usuarios')->where('correo', $correo)->get()->getRowArray();
        if ($existe) {
            return redirect()->back()->with('error', 'El correo ya está registrado.');
        }

        $passwordHash = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);

        $db->table('usuarios')->insert([
            'nombre'   => $this->request->getPost('nombre'),
            'correo'   => $correo,
            'password' => $passwordHash,
            'rol'      => 'cliente'
        ]);

        return redirect()->to(base_url('login'))->with('mensaje', 'Registro exitoso. Inicia sesión.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/'));
    }
}
