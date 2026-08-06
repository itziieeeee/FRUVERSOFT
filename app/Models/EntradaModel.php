<?php

namespace App\Models;

use CodeIgniter\Model;

class EntradaModel extends Model
{
    protected $table      = 'entrada';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_producto',
        'precio_compra',
        'cantidad_compra',
        'unidad_compra',
        'precio_sugerido',
        'unidad_venta',
        'cantidad_venta',
        'categoria',
        'fecha',
        'fecha_cad'
    ];
}