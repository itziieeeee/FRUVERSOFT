<?php
namespace App\Models;

use CodeIgniter\Model;

class ProductoPedidoModel extends Model {
    protected $table      = 'producto_pedido';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'id_pedido',
        'id_producto',
        'cantidad',
        'precio_venta',
        'unidad_venta',
        'tipo_venta',
        'subtotal',
        'total'
    ];
}