<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table      = 'producto'; 
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'descripcion', 'unidad_compra', 'unidad_venta', 'categoria', 'stock',  'precio_compra', 'precio_venta']; 
}