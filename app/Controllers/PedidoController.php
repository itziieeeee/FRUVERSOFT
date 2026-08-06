<?php
namespace App\Controllers;

use App\Models\PedidoModel;
use App\Controllers\BaseController;

class PedidoController extends BaseController {

    public function pantalla_ventas() {
        $pedidoModel = new PedidoModel();

        $datos = [
            'secc1'        => $pedidoModel->getPedidosConCliente(),
            'productos'    => $pedidoModel->getProductosConPrecio(),
            'unidades'     => $pedidoModel->getUnidadesEnum(),
            'clientes'     => $pedidoModel->getClientes(),
            'repartidores' => $pedidoModel->getRepartidores(),
        ];

        return view('pantalla_ventas', $datos);
    }

    public function guardar_productos_pedido() {
        $json = $this->request->getJSON(true);

        if (empty($json['productos']) || !isset($json['id_cliente']) || $json['id_cliente'] === '') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Datos incompletos.'
            ]);
        }

        $pedidoModel      = new \App\Models\PedidoModel();
        $productoPedModel = new \App\Models\ProductoPedidoModel();

        $idCliente    = (int) $json['id_cliente'];
        $tipoVenta    = $json['tipo_venta']   ?? 'contado';
        $tipoEntrega  = $json['tipo_entrega'] ?? 'tienda';
        $idRepartidor = ($tipoEntrega === 'domicilio' && !empty($json['id_repartidor']))
                        ? (int) $json['id_repartidor']
                        : null;

        // 1. Calcular total
        $totalGeneral = 0;
        foreach ($json['productos'] as $prod) {
            $totalGeneral += (float)$prod['cantidad'] * (float)$prod['precio_venta'];
        }

        // 2. Estado inicial
        $estadoInicial = ($tipoVenta === 'credito') ? 'Pedido a crédito' : 'Pedido';

        // 3. Nombre del cliente
        $nombreCliente = ($idCliente === 0)
            ? 'Público general'
            : $pedidoModel->getNombreCliente($idCliente);

        // 4. Nombre del repartidor
        $nombreRepartidor = $idRepartidor ? $pedidoModel->getNombreRepartidor($idRepartidor) : null;

        // 5. Transacción (pedido + productos + status)
        $pedidoData = [
            'fecha'         => date('Y-m-d H:i:s'),
            'id_cliente'    => $idCliente,
            'id_repartidor' => $idRepartidor,
            'tipo_entrega'  => $tipoEntrega,
            'total'         => $totalGeneral,
            'estado_actual' => $estadoInicial,
            'tipo_pago'     => $tipoVenta,
            'monto_pagado'  => 0.00,
        ];

        $resultado = $pedidoModel->crearPedidoCompleto($pedidoData, $json['productos'], $productoPedModel);
        $idPedido  = $resultado['idPedido'];

        if (!$resultado['ok']) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => json_encode($resultado['error']),
                'debug'   => $resultado['error']
            ]);
        }

        // 6. Validación DESPUÉS de la transacción exitosa
        $statusModel    = new \App\Models\StatusModel();
        $validacion     = $statusModel->validarYConfirmar($idPedido);
        $autoConfirmado = $validacion['success'];
        $faltantes      = $validacion['faltantes'] ?? [];

        $folio = 'PED-' . str_pad($idPedido, 5, '0', STR_PAD_LEFT);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'folio'           => $folio,
                'cliente'         => $nombreCliente,
                'tipo_venta'      => $tipoVenta,
                'tipo_entrega'    => $tipoEntrega,
                'repartidor'      => $nombreRepartidor,
                'total'           => number_format($totalGeneral, 2),
                'auto_confirmado' => $autoConfirmado,
                'faltantes'       => $faltantes,
            ]
        ]);
    }

    public function eliminarPedido($id) {
        $pedidoModel = new PedidoModel();

        try {
            $ok = $pedidoModel->eliminarPedidoCompleto($id);

            if (!$ok) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al eliminar el pedido'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Pedido eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function cambiarEstado() {
        $id     = $this->request->getPost('id');
        $estado = $this->request->getPost('estado');

        if (!$id || !$estado) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos'
            ]);
        }

        $pedidoModel = new PedidoModel();
        $pedidoModel->cambiarEstadoPedido($id, $estado);

        return $this->response->setJSON(['success' => true]);
    }
}