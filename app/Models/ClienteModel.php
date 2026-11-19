<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table      = 'clientes';
    protected $primaryKey = 'id_cliente';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'rfc',
        'tipo_cliente',
        'tel'
    ];

    /** Une la dirección más reciente del cliente (así un cliente con dos direcciones no sale repetido) */
    private const UNION_DIRECCION_ACTUAL =
        'direccion.id = (SELECT MAX(d2.id) FROM direccion d2 WHERE d2.id_cliente = clientes.id_cliente)';

    public function getListaClientes()
    {
        return $this->select('clientes.*, direccion.estado, direccion.colonia, direccion.municipio, direccion.lat, direccion.lng, (SELECT COUNT(id) FROM pedido WHERE pedido.id_cliente = clientes.id_cliente) as total_pedidos')
            ->join('direccion', self::UNION_DIRECCION_ACTUAL, 'left', false)
            // El cliente interno "Público general" no se muestra en el directorio
            ->groupStart()->where('clientes.tipo_cliente IS NULL', null, false)->orWhere('clientes.tipo_cliente !=', 'publico')->groupEnd()
            ->findAll();
    }

    public function getDetalleCliente($id)
    {
        return $this->select('clientes.*, direccion.id AS id_direccion, direccion.calle, direccion.numero, direccion.colonia, direccion.municipio, direccion.estado, direccion.lat, direccion.lng')
            ->join('direccion', self::UNION_DIRECCION_ACTUAL, 'left', false)
            ->where('clientes.id_cliente', $id)
            ->first();
    }

    /** Todas las compras del cliente (más recientes primero) con los productos de cada una */
    public function getHistorialCompras(int $idCliente, int $limite = 500): array
    {
        return $this->db->query(
            "SELECT p.id, p.fecha, p.total, p.monto_pagado, p.estado_actual, p.tipo_pago, p.tipo_entrega, p.prioritario,
                    (SELECT GROUP_CONCAT(pr.nombre ORDER BY pp.id SEPARATOR ', ')
                       FROM producto_pedido pp
                       JOIN producto pr ON pr.id = pp.id_producto
                      WHERE pp.id_pedido = p.id) AS productos
               FROM pedido p
              WHERE p.id_cliente = ?
              ORDER BY p.id DESC
              LIMIT " . (int) $limite,
            [$idCliente]
        )->getResultArray();
    }

    public function contarPedidos($id)
{
    return $this->db->table('pedido')
        ->where('id_cliente', $id)
        ->whereNotIn('estado_actual', [
            'Venta confirmada',
            'Pedido pagado',
            'Pedido cancelado'
        ])
        ->countAllResults();
}

    public function eliminarClienteCompleto($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $db->table('direccion')->where('id_cliente', $id)->delete();
        $db->table('clientes')->where('id_cliente', $id)->delete();

        $db->transComplete();

        return $db->transStatus();
    }
}