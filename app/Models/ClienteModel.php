<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table      = 'clientes';
    protected $primaryKey = 'id_cliente';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    // Estos campos deben coincidir exctamente con tu base de datos
    protected $allowedFields = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'rfc',
        'tipo_cliente',
        'tel'
    ];
}