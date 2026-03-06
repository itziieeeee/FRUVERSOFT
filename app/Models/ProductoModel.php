<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nombre',
        'precio',
        'descripcion',
        'uni_medida',
        'stock'
    ];
}
