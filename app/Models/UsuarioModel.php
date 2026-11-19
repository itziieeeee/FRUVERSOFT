<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table      = 'usuarios';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nombre',
        'usuario',
        'correo',
        'password',
        'rol',
        'debe_cambiar_password',
        'activo',
        'password_actualizado_at',
        'id_repartidor'
    ];

    protected $useTimestamps = false;
}
