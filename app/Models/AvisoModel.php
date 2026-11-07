<?php

namespace App\Models;

use CodeIgniter\Model;

class AvisoModel extends Model
{
    protected $table = 'avisos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['titulo', 'mensaje', 'rol_destino', 'fecha_expiracion', 'created_at'];

    // Obtener avisos vigentes para el cliente (que no hayan expirado)
    public function getAvisosCliente()
    {
        return $this->where('rol_destino', 'cliente')
                    ->groupStart()
                        ->where('fecha_expiracion >=', date('Y-m-d'))
                        ->orWhere('fecha_expiracion IS NULL')
                    ->groupEnd()
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }
}