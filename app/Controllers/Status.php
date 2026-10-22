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

public function actualizar_precios()
{
    $idPedido = (int) $this->request->getPost('id');

    $preciosJson = $this->request->getPost('precios');
    $precios = json_decode($preciosJson ?? '', true);


    if (!$idPedido || !is_array($precios) || empty($precios)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Datos del pedido inválidos.'
        ]);
    }

    $db = \Config\Database::connect();

    $db->transBegin();

    try {
        // Obtener el pedido y bloquearlo durante la actualización.
        $pedido = $db->table('pedido')
            ->where('id', $idPedido)
            ->get()
            ->getRowArray();

        if (!$pedido) {
            throw new \RuntimeException('Pedido no encontrado.');
        }

        $productos = $db->table('producto_pedido')
            ->where('id_pedido', $idPedido)
            ->get()
            ->getResultArray();

        if (empty($productos)) {
            throw new \RuntimeException('El pedido no tiene productos.');
        }

        $totalNuevo = 0;

        foreach ($productos as $producto) {
            $idDetalle = (int) $producto['id'];

            // No permitir modificar líneas que no pertenecen al pedido.
            if (!array_key_exists((string) $idDetalle, $precios)
                && !array_key_exists($idDetalle, $precios)) {
                throw new \RuntimeException(
                    'Falta el precio de un producto del pedido.'
                );
            }

            $precio = $precios[$idDetalle] ?? $precios[(string) $idDetalle];

            if (!is_numeric($precio) || (float) $precio < 0) {
                throw new \RuntimeException(
                    'Todos los precios deben ser números no negativos.'
                );
            }

            $precio = round((float) $precio, 2);
            $cantidad = (float) $producto['cantidad'];
            $subtotal = round($cantidad * $precio, 2);

            $actualizado = $db->table('producto_pedido')
                ->where('id', $idDetalle)
                ->where('id_pedido', $idPedido)
                ->update([
                    'precio_venta' => $precio,
                    'subtotal' => $subtotal,
                    'total' => $subtotal
                ]);

            if ($actualizado === false) {
                throw new \RuntimeException(
                    'No se pudo actualizar el precio del producto.'
                );
            }

            $totalNuevo += $subtotal;
        }

        $totalNuevo = round($totalNuevo, 2);
        $montoPagado = (float) $pedido['monto_pagado'];

        if ($totalNuevo < $montoPagado) {
            throw new \RuntimeException(
                'El nuevo total no puede ser menor que el monto ya pagado.'
            );
        }

        $actualizadoPedido = $db->table('pedido')
            ->where('id', $idPedido)
            ->update(['total' => $totalNuevo]);

        if ($actualizadoPedido === false) {
            throw new \RuntimeException(
                'No se pudo actualizar el total del pedido.'
            );
        }

        if ($db->transStatus() === false) {
            throw new \RuntimeException(
                'No se pudieron guardar todos los cambios.'
            );
        }

        $db->transCommit();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Precios actualizados correctamente.',
            'total' => $totalNuevo,
            'monto_pagado' => $montoPagado,
            'saldo_pendiente' => round($totalNuevo - $montoPagado, 2)
        ]);

    } catch (\Throwable $e) {
        $db->transRollback();

        return $this->response->setJSON([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
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