<?php


//ESTE ES EL MODELO DE LA SECCION 1 DE VENTAS 


namespace App\Models;

use CodeIgniter\Model;

class PedidoModel extends Model {
    
protected $table      = 'pedido'; 
protected $primaryKey = 'id';
protected $returnType = 'array'; 

    protected $allowedFields = [
        'fecha', 
        'id_cliente', 
        'id_repartidor', 
        'estado_actual', 
        'total'
    ];
}