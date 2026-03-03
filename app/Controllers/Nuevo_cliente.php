<?php

namespace App\Controllers;
use App\Models\UsuarioModel; 

class Nuevo_cliente extends BaseController
{
    public function index()
    {
        return view('pantalla_rcliente');
    }

}