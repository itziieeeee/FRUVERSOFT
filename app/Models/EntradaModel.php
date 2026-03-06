<?php

namespace App\Models;

use CodeIgniter\Model;

class EntradaModel extends Model
{
    protected $table = 'entrada';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'id_producto',
        'cantidad',
        'fecha'
    ];
}