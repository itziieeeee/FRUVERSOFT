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

    public function getListaClientes()
    {
        return $this->select('clientes.*, direccion.estado')
            ->join('direccion', 'direccion.id_cliente = clientes.id_cliente', 'left')
            ->findAll();
    }

    public function getDetalleCliente($id)
    {
        return $this->select('clientes.*, direccion.estado')
            ->join('direccion', 'direccion.id_cliente = clientes.id_cliente', 'left')
            ->where('clientes.id_cliente', $id)
            ->first();
    }

    public function contarPedidos($id)
    {
        return $this->db->table('pedido')
            ->where('id_cliente', $id)
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