<?php
namespace App\Controllers;

use App\Models\PedidoModel;
use App\Controllers\BaseController;

class PedidoController extends BaseController {

    public function pantalla_ventas() {
        $pedidoModel = new PedidoModel();
        $db = \Config\Database::connect();

        $pedidos = $pedidoModel
            ->select("pedido.*, CONCAT(clientes.nombre, ' ', clientes.apellido_paterno) as nombre_cliente")
            ->join('clientes', 'clientes.id_cliente = pedido.id_cliente')
            ->findAll();

        $clientes = $db->table('clientes')
            ->select('id_cliente, nombre, apellido_paterno, apellido_materno, tipo_cliente')
            ->get()
            ->getResultArray();

        $productos = $db->table('producto p')
            ->select('p.id, p.nombre, MAX(e.unidad_venta) as unidad_venta, MAX(e.precio_sugerido) as precio_sugerido')
            ->join('entrada e', 'e.id_producto = p.id', 'left')
            ->groupBy('p.id, p.nombre')
            ->get()
            ->getResultArray();

        $query = $db->query("SHOW COLUMNS FROM producto_pedido LIKE 'unidad_venta'");
        $row = $query->getRow();
        preg_match_all("/'([^']+)'/", $row->Type, $matches);
        $unidadesEnum = $matches[1];

        $repartidores = $db->table('repartidor')
            ->select('id, nombre, ap_p, ap_m')
            ->get()
            ->getResultArray();

        $datos = [
            'secc1'        => $pedidos,
            'productos'    => $productos,
            'unidades'     => $unidadesEnum,
            'clientes'     => $clientes,
            'repartidores' => $repartidores,
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

        $db               = \Config\Database::connect();
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
        if ($idCliente === 0) {
            $nombreCliente = 'Público general';
        } else {
            $clienteRow = $db->table('clientes')
                ->select("CONCAT(nombre, ' ', apellido_paterno) as nombre_completo")
                ->where('id_cliente', $idCliente)
                ->get()->getRowArray();
            $nombreCliente = $clienteRow['nombre_completo'] ?? 'Desconocido';
        }

        // 4. Nombre del repartidor
        $nombreRepartidor = null;
        if ($idRepartidor) {
            $rep = $db->table('repartidor')
                ->select("CONCAT(nombre, ' ', ap_p) as nombre_completo")
                ->where('id', $idRepartidor)
                ->get()->getRowArray();
            $nombreRepartidor = $rep['nombre_completo'] ?? null;
        }

        // 5. Transacción
        $db->transStart();

        $pedidoModel->insert([
            'fecha'         => date('Y-m-d H:i:s'),
            'id_cliente'    => $idCliente,
            'id_repartidor' => $idRepartidor,
            'tipo_entrega'  => $tipoEntrega,
            'total'         => $totalGeneral,
            'estado_actual' => $estadoInicial,
            'tipo_pago'     => $tipoVenta,      
    'monto_pagado'  => 0.00,    
        ]);
        $idPedido = $db->insertID();

        foreach ($json['productos'] as $prod) {
            $cantidad = (float) $prod['cantidad'];
            $precio   = (float) $prod['precio_venta'];
            $subtotal = $cantidad * $precio;

            $productoPedModel->insert([
                'id_pedido'    => $idPedido,
                'id_producto'  => (int) $prod['id_producto'],
                'cantidad'     => $cantidad,
                'precio_venta' => $precio,
                'unidad_venta' => $prod['unidad'],
                'tipo_venta'   => $tipoVenta,
                'subtotal'     => $subtotal,
                'total'        => $subtotal,
            ]);
        }

        $db->table('status')->insert([
            'id_pedido' => $idPedido,
            'estado'    => $estadoInicial,
            'fecha'     => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            $error = $db->error();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Error de Base de Datos: ' . ($error['message'] ?? 'Error desconocido'),
                'debug'   => $error
            ]);
        }

        // 6. ← AQUÍ va la validación, DESPUÉS de la transacción exitosa
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
}