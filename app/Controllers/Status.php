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

        $model  = new StatusModel();
        $pedido = $model->getPedidoConDetalle($id);

        if (!$pedido) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pedido no encontrado']);
        }

        $productos = $model->getProductosDePedido($id);

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