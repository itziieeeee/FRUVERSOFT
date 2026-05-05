<?php

namespace App\Models;
use CodeIgniter\Model;

class StatusModel extends Model
{
    protected $table = 'pedido';
    protected $primaryKey = 'id';
    protected $allowedFields = ['estado_actual'];

    public function actualizarEstado($id, $estado)
    {
        // 1. Actualiza en tabla pedido
        $this->update($id, ['estado_actual' => $estado]);

        // 2. Actualiza en tabla status
        $this->db->table('status')
            ->where('id_pedido', $id)
            ->update([
                'estado' => $estado,
                'fecha'  => date('Y-m-d H:i:s')
            ]);

        return true;
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