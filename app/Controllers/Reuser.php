<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Reuser extends BaseController
{
    public function index()
    {
        return view('registrar_usuario');
    }

    public function guardar() {
    $model = new UsuarioModel(); // O el modelo que uses para registrar
    
    $data = [
        'nombre_completo' => $this->request->getPost('nombre_completo'),
        'nusuario'        => $this->request->getPost('nusuario'),
        'contrasena'      => password_hash($this->request->getPost('contrasena'), PASSWORD_DEFAULT),
    ];

    if ($model->insert($data)) {
        // Esto es lo que hace que viaje a la otra pantalla
        return redirect()->to(base_url('/pantalla_administrador'));
    } 
}
}
