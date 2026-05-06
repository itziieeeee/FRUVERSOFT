<?php
namespace App\Controllers;
use App\Models\StatusModel;

class Status extends BaseController
{
    public function cambiar()
    {
        $model  = new StatusModel();
        $id     = $this->request->getPost('id');
        $estado = $this->request->getPost('estado');

        if (!$id || !$estado) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos inválidos']);
        }

        $estadosValidos = [
            'Pedido', 'Pedido confirmado', 'Pedido en tránsito',
            'Venta confirmada', 'Pedido a crédito', 'Pedido pagado', 'Pedido cancelado'
        ];

        if (!in_array($estado, $estadosValidos)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Estado no válido']);
        }

        $result = $model->actualizarEstado($id, $estado);
        return $this->response->setJSON(['success' => (bool)$result]);
    }
public function detalle_pedido()
{
    $id = (int) $this->request->getPost('id');

    if (!$id) {
        return $this->response->setJSON(['success' => false, 'message' => 'ID inválido']);
    }

    $db = \Config\Database::connect();

    // Datos del pedido + cliente
    $pedido = $db->table('pedido')
        ->select('
            pedido.id,
            pedido.fecha,
            pedido.total,
            pedido.estado_actual,
            pedido.tipo_pago,
            pedido.monto_pagado,
            pedido.tipo_entrega,
            CONCAT(clientes.nombre, " ", clientes.apellido_paterno, " ", clientes.apellido_materno) AS nombre_cliente,
            CONCAT(repartidor.nombre, " ", repartidor.ap_p) AS nombre_repartidor
        ')
        ->join('clientes', 'clientes.id_cliente = pedido.id_cliente', 'left')
        ->join('repartidor', 'repartidor.id = pedido.id_repartidor', 'left')
        ->where('pedido.id', $id)
        ->get()->getRowArray();

    if (!$pedido) {
        return $this->response->setJSON(['success' => false, 'message' => 'Pedido no encontrado']);
    }

    // Productos del pedido
    $productos = $db->table('producto_pedido pp')
        ->select('
            p.nombre,
            pp.cantidad,
            pp.unidad_venta,
            pp.precio_venta,
            pp.subtotal,
            pp.tipo_venta
        ')
        ->join('producto p', 'p.id = pp.id_producto')
        ->where('pp.id_pedido', $id)
        ->get()->getResultArray();

    return $this->response->setJSON([
        'success'   => true,
        'pedido'    => $pedido,
        'productos' => $productos,
    ]);
}
    public function actualizar_pago()
    {
        $model       = new StatusModel();
        $id          = $this->request->getPost('id');
        $montoPagado = (float) $this->request->getPost('monto_pagado');

        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID inválido']);
        }

        // Obtener total del pedido
        $pedido = $model->find($id);
        if (!$pedido) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pedido no encontrado']);
        }

        $total = (float) $pedido['total'];
        if ($montoPagado < 0)     $montoPagado = 0;
        if ($montoPagado > $total) $montoPagado = $total;

        $result = $model->actualizarPago($id, $montoPagado, $total);
        return $this->response->setJSON(['success' => (bool)$result]);
    }
public function validar_pedido()
{
    $model    = new StatusModel();
    $idPedido = (int) $this->request->getPost('id');

    if (!$idPedido) {
        return $this->response->setJSON(['success' => false, 'message' => 'ID inválido']);
    }

    $resultado = $model->validarYConfirmar($idPedido);
    return $this->response->setJSON($resultado);
}
    public function pantalla_pedidos()
    {
        $model      = new StatusModel();
        $data['sp'] = $model->obtenerPedidos();
        return view('pantalla_pedidos', $data);
    }
}