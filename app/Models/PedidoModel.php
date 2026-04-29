<?php
namespace App\Models;

use CodeIgniter\Model;

class PedidoModel extends Model {
    protected $table      = 'pedido';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'fecha',
        'id_cliente',
        'id_repartidor',
        'tipo_entrega',
        'total',
        'estado_actual'
    ];
}
