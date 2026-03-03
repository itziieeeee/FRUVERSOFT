<?php

namespace App\Controllers;
use App\Models\UsuarioModel; 

class Usuario extends BaseController
{
    public function index()
    {
        return view('pantalla_usuario');
    }

    public function validar()
    {
        
        return view('pantalla_administrador');
    }
}