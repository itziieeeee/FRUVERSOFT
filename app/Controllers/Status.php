<?php

namespace App\Controllers;
use App\Models\StatusModel;

class Status extends BaseController
{
    public function cambiar()
    {
        $model = new StatusModel();

        $id     = $this->request->getPost('id');
        $estado = $this->request->getPost('estado');

        if (!$id || !$estado) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos inválidos']);
        }

        $estadosValidos = [
            'Pedido',
            'Pedido confirmado',
            'Pedido en tránsito',
            'Venta confirmada',
            'Pedido a crédito',
            'Pedido pagado',
            'Pedido cancelado'
        ];

        if (!in_array($estado, $estadosValidos)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Estado no válido']);
        }

        $result = $model->actualizarEstado($id, $estado);

        return $this->response->setJSON(['success' => (bool)$result]);
    }

    public function pantalla_pedidos()
    {
        $model = new StatusModel();
        $data['sp'] = $model->obtenerPedidos();
        return view('pantalla_pedidos', $data);
    }
}