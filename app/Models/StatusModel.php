<?php
namespace App\Models;
use CodeIgniter\Model;

class StatusModel extends Model
{
    protected $table      = 'pedido';
    protected $primaryKey = 'id';
    protected $allowedFields = ['estado_actual', 'monto_pagado', 'tipo_pago'];

    public function actualizarEstado($id, $estado)
    {
        $this->update($id, ['estado_actual' => $estado]);

        $this->db->table('status')
            ->where('id_pedido', $id)
            ->update([
                'estado' => $estado,
                'fecha'  => date('Y-m-d H:i:s')
            ]);

        return true;
    }

    public function actualizarPago($id, $montoPagado, $total)
    {
        $tipoPago    = ($montoPagado >= $total) ? 'contado' : 'credito';
        $estadoNuevo = ($montoPagado >= $total) ? 'Pedido pagado' : 'Pedido a crédito';

        $this->update($id, [
            'monto_pagado'  => $montoPagado,
            'tipo_pago'     => $tipoPago,
            'estado_actual' => $estadoNuevo,
        ]);

        $this->db->table('status')->insert([
            'id_pedido' => $id,
            'estado'    => $estadoNuevo,
            'fecha'     => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    public function validarYConfirmar($idPedido)
    {
        $productos = $this->db->table('producto_pedido')
            ->select('id_producto, cantidad')
            ->where('id_pedido', $idPedido)
            ->get()->getResultArray();

        if (empty($productos)) {
            return ['success' => false, 'message' => 'El pedido no tiene productos'];
        }

        $existenciasModel = new \App\Models\ExistenciasModel();
        $faltantes        = $existenciasModel->validarStockPedido($productos);

        if (!empty($faltantes)) {
            return [
                'success'   => false,
                'sin_stock' => true,
                'faltantes' => $faltantes,
            ];
        }

        // Hay stock: descontar e_total y sumar e_bloqueo
        $existenciasModel->descontarStock($productos);

        $this->update($idPedido, ['estado_actual' => 'Pedido confirmado']);

        $this->db->table('status')->insert([
            'id_pedido' => $idPedido,
            'estado'    => 'Pedido confirmado',
            'fecha'     => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'estado' => 'Pedido confirmado'];
    }

    public function obtenerPedidos()
    {
        return $this->db->table('pedido')
            ->select('pedido.*, CONCAT(clientes.nombre, " ", clientes.apellido_paterno, " ", clientes.apellido_materno) AS nombre_cliente')
            ->join('clientes', 'clientes.id_cliente = pedido.id_cliente', 'left')
            ->get()
            ->getResultArray();
    }
}