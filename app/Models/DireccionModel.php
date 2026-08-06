<?php

namespace App\Models;

use CodeIgniter\Model;

class DireccionModel extends Model
{
    protected $table      = 'direccion';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'colonia',
        'calle',
        'numero',
        'municipio',
        'estado',
        'id_cliente'
    ];

    // Actualiza solo el estado de la dirección de un cliente
    public function actualizarEstado($idCliente, $estado)
    {
        return $this->where('id_cliente', $idCliente)
            ->set(['estado' => $estado])
            ->update();
    }
}