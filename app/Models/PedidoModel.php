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
        'estado_actual',
        'monto_pagado',
        'tipo_pago',
    ];

    // ------- Consultas para pantalla_ventas() -------

    public function getPedidosConCliente()
    {
        return $this
            ->select("pedido.*, CONCAT(clientes.nombre, ' ', clientes.apellido_paterno) as nombre_cliente")
            ->join('clientes', 'clientes.id_cliente = pedido.id_cliente')
            ->findAll();
    }

    public function getClientes()
    {
        return $this->db->table('clientes')
            ->select('id_cliente, nombre, apellido_paterno, apellido_materno, tipo_cliente')
            ->get()
            ->getResultArray();
    }

    public function getProductosConPrecio()
    {
        return $this->db->table('producto p')
            ->select('p.id, p.nombre, MAX(e.unidad_venta) as unidad_venta, MAX(e.precio_sugerido) as precio_sugerido')
            ->join('entrada e', 'e.id_producto = p.id', 'left')
            ->groupBy('p.id, p.nombre')
            ->get()
            ->getResultArray();
    }

    public function getUnidadesEnum()
    {
        $query = $this->db->query("SHOW COLUMNS FROM producto_pedido LIKE 'unidad_venta'");
        $row = $query->getRow();
        preg_match_all("/'([^']+)'/", $row->Type, $matches);
        return $matches[1];
    }

    public function getRepartidores()
    {
        return $this->db->table('repartidor')
            ->select('id, nombre, ap_p, ap_m')
            ->get()
            ->getResultArray();
    }

    // ------- Consultas para guardar_productos_pedido() -------

    public function getNombreCliente($idCliente)
    {
        $clienteRow = $this->db->table('clientes')
            ->select("CONCAT(nombre, ' ', apellido_paterno) as nombre_completo")
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        return $clienteRow['nombre_completo'] ?? 'Desconocido';
    }

    public function getNombreRepartidor($idRepartidor)
    {
        $rep = $this->db->table('repartidor')
            ->select("CONCAT(nombre, ' ', ap_p) as nombre_completo")
            ->where('id', $idRepartidor)
            ->get()->getRowArray();

        return $rep['nombre_completo'] ?? null;
    }

    public function insertarStatus($idPedido, $estado)
    {
        return $this->db->table('status')->insert([
            'id_pedido' => $idPedido,
            'estado'    => $estado,
            'fecha'     => date('Y-m-d H:i:s'),
        ]);
    }

    // Crea el pedido + sus productos + su status inicial dentro de una transacción
    public function crearPedidoCompleto($pedidoData, array $productos, $productoPedModel)
    {
        $this->db->transStart();

        $this->insert($pedidoData);
        $idPedido = $this->db->insertID();

        foreach ($productos as $prod) {
            $cantidad = (float) $prod['cantidad'];
            $precio   = (float) $prod['precio_venta'];
            $subtotal = $cantidad * $precio;

            $productoPedModel->insert([
                'id_pedido'    => $idPedido,
                'id_producto'  => (int) $prod['id_producto'],
                'cantidad'     => $cantidad,
                'precio_venta' => $precio,
                'unidad_venta' => $prod['unidad'],
                'tipo_venta'   => $pedidoData['tipo_pago'],
                'subtotal'     => $subtotal,
                'total'        => $subtotal,
            ]);
        }

        $this->insertarStatus($idPedido, $pedidoData['estado_actual']);

        $this->db->transComplete();

        return [
            'ok'       => $this->db->transStatus() !== false,
            'idPedido' => $idPedido,
            'error'    => $this->db->error(),
        ];
    }

    // ------- Consultas para eliminarPedido() -------

    public function eliminarPedidoCompleto($id)
    {
        $this->db->transStart();

        $this->db->table('producto_pedido')->where('id_pedido', $id)->delete();
        $this->db->table('status')->where('id_pedido', $id)->delete();
        $this->db->table('pedido')->where('id', $id)->delete();

        $this->db->transComplete();

        return $this->db->transStatus() !== false;
    }

    // ------- Consultas para cambiarEstado() -------

    public function cambiarEstadoPedido($id, $estado)
    {
        $this->db->table('pedido')->where('id', $id)->update(['estado_actual' => $estado]);
        $this->insertarStatus($id, $estado);
    }
}