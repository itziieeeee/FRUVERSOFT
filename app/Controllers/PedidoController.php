<?php
namespace App\Controllers;

use App\Models\PedidoModel;
use App\Controllers\BaseController;

class PedidoController extends BaseController {

    public function pantalla_ventas() {
        $pedidoModel = new PedidoModel();
        $db = \Config\Database::connect();

        // Obtener pedidos con nombre del cliente
        $pedidos = $pedidoModel
            ->select("pedido.*, CONCAT(clientes.nombre, ' ', clientes.apellido_paterno) as nombre_cliente")
            ->join('clientes', 'clientes.id_cliente = pedido.id_cliente')
            ->findAll();

        // Obtener clientes
        $clientes = $db->table('clientes')
            ->select('id_cliente, nombre, apellido_paterno, apellido_materno, tipo_cliente')
            ->get()
            ->getResultArray();

        // Obtener productos con precio sugerido desde entrada
        $productos = $db->table('producto p')
            ->select('p.id, p.nombre, e.unidad_venta, e.precio_sugerido')
            ->join('entrada e', 'e.id_producto = p.id')
            ->get()
            ->getResultArray();

        // Obtener ENUM unidad_venta de producto_pedido
        $query = $db->query("SHOW COLUMNS FROM producto_pedido LIKE 'unidad_venta'");
        $row = $query->getRow();
        preg_match_all("/'([^']+)'/", $row->Type, $matches);
        $unidadesEnum = $matches[1];

        // Obtener repartidores
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

        // Validación básica
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

        // Calcular total general
        $totalGeneral = 0;
        foreach ($json['productos'] as $prod) {
            $totalGeneral += (float)$prod['cantidad'] * (float)$prod['precio_venta'];
        }

        // Estado inicial según tipo de venta
        $estadoInicial = ($tipoVenta === 'credito') ? 'Pedido a credito' : 'Pedido';

        // Nombre del cliente para la respuesta
        if ($idCliente === 0) {
            $nombreCliente = 'Público general';
        } else {
            $clienteRow = $db->table('clientes')
                ->select("CONCAT(nombre, ' ', apellido_paterno) as nombre_completo")
                ->where('id_cliente', $idCliente)
                ->get()->getRowArray();
            $nombreCliente = $clienteRow['nombre_completo'] ?? 'Desconocido';
        }

        // Nombre del repartidor para la respuesta
        $nombreRepartidor = null;
        if ($idRepartidor) {
            $rep = $db->table('repartidor')
                ->select("CONCAT(nombre, ' ', ap_p) as nombre_completo")
                ->where('id', $idRepartidor)
                ->get()->getRowArray();
            $nombreRepartidor = $rep['nombre_completo'] ?? null;
        }

        // Iniciar transacción
        $db->transStart();

        // 1. Insertar pedido
        $pedidoModel->insert([
            'fecha'         => date('Y-m-d H:i:s'),
            'id_cliente'    => $idCliente,
            'id_repartidor' => $idRepartidor,
            'tipo_entrega'  => $tipoEntrega,
            'total'         => $totalGeneral,
            'estado_actual' => $estadoInicial,
        ]);
        $idPedido = $db->insertID();

        // 2. Insertar productos del pedido
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

        // 3. Insertar estado inicial en tabla status
        $db->table('status')->insert([
            'id_pedido' => $idPedido,
            'estado'    => $estadoInicial,
            'fecha'     => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Error al guardar en base de datos. Verifica los datos e intenta de nuevo.'
            ]);
        }
$db->transComplete();

if ($db->transStatus() === false) {
    // Capturar el error real
    $error = $db->error();
    return $this->response->setJSON([
        'status'  => 'error',
        'message' => 'Error: ' . ($error['message'] ?? 'desconocido')
    ]);
}
        $folio = 'PED-' . str_pad($idPedido, 5, '0', STR_PAD_LEFT);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'folio'        => $folio,
                'cliente'      => $nombreCliente,
                'tipo_venta'   => $tipoVenta,
                'tipo_entrega' => $tipoEntrega,
                'repartidor'   => $nombreRepartidor,
                'total'        => number_format($totalGeneral, 2),
            ]
        ]);
    }
}