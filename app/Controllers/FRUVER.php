<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\UsuarioModel;

class FRUVER extends BaseController
{
    // 1. Pantalla principal (Bienvenida)
    public function index(): string
    {
        return view('Pantalla_inicio');
    }

    // 2. Sección Usuarios (Registro de Admin)
    public function reuser()
    {
        return view('registrar_usuario');
    }

    public function guardar()
    {
        $model = new UsuarioModel();

        $data = [
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'nusuario'        => $this->request->getPost('nusuario'),
            'contrasena'      => password_hash($this->request->getPost('contrasena'), PASSWORD_DEFAULT),
        ];

        if ($model->insert($data)) {
            return redirect()->to(base_url('pantalla_administrador'));
        }
    }

    // 3. Sección Login
    public function usuario()
    {
        return view('pantalla_usuario');
    }

    public function validar()
    {
        // Nota: Aquí después agregaremos la lógica real de checar usuario/password
        return view('menusolo');
    }

    public function menusolo()
    {
       
        return view('menusolo');
    }

    // 4. Sección Clientes (Unificada)
    public function pantalla_clientes()
    {
        // Esta es la vista general de la tabla de clientes
        return view('pantalla_clientes');
    }

    public function nuevo_cliente()
    {
        // Esta es la vista del formulario (alta_cliente)
        return view('alta_cliente'); 
    }

    public function guardar_cliente()
    {
        $modelo = new ClienteModel();

        $datos = [
            'nombre'           => $this->request->getPost('nombre'),
            'apellido_paterno' => $this->request->getPost('apellido_paterno'),
            'apellido_materno' => $this->request->getPost('apellido_materno'),
            'rfc'              => $this->request->getPost('rfc'),
            'tipo_cliente'     => $this->request->getPost('tipo_cliente')
        ];

        $modelo->insert($datos);

        return redirect()->to(base_url('pantalla_clientes'));
    }

    // 5. Cerrar Sesión
    public function salir()
    {
        return redirect()->to(base_url('/'));
    }
}