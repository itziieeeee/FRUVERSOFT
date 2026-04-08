<?php
namespace App\Controllers;

use App\Models\PedidoModel;
use App\Controllers\BaseController;

class PedidoController extends BaseController {


    // MOSTRAR VISTA (PEDIDOS)
    
    public function pantalla_ventas() {
    $pedidoModel = new PedidoModel();
    $db = \Config\Database::connect();

    //  Obtener pedidos con nombre del cliente
    $pedidos = $pedidoModel
        ->select("pedido.*, CONCAT(clientes.nombre, ' ', clientes.apellido_paterno) as nombre_cliente")
        ->join('clientes', 'clientes.id_cliente = pedido.id_cliente')
        ->findAll();

    //  OBTENER CLIENTES (ESTO TE FALTABA)
    $clientes = $db->table('clientes')
        ->select('id_cliente, nombre, apellido_paterno, apellido_materno, tipo_cliente')
        ->get()
        ->getResultArray();

    //  Obtener productos
    $productos = $db->table('producto p')
        ->select('p.id, p.nombre, e.unidad_compra, e.precio_sugerido')
        ->join('entrada e', 'e.id_producto = p.id')
        ->get()
        ->getResultArray();

    //  Obtener ENUM unidad_venta
    $query = $db->query("SHOW COLUMNS FROM producto_pedido LIKE 'unidad_venta'");
    $row = $query->getRow();

    preg_match_all("/'([^']+)'/", $row->Type, $matches);
    $unidadesEnum = $matches[1];

    // Enviar TODO a la vista
    $datos = [
        'secc1'     => $pedidos,
        'productos' => $productos,
        'unidades'  => $unidadesEnum,
        'clientes'  => $clientes 
    ];

    return view('pantalla_ventas', $datos);
}

}

