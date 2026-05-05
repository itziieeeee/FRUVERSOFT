<?php

namespace App\Models;

use CodeIgniter\Model;

class DireccionModel extends Model
{
    protected $table      = 'direccion';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'colonia',
        'calle',
        'numero',
        'municipio',
        'estado',
        'id_cliente'
    ];
}