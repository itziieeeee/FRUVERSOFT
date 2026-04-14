<?php
namespace App\Models;
use CodeIgniter\Model;

class ExistenciasModel extends Model
{
    protected $table = 'existencias';
    protected $primaryKey = 'id';
    protected $allowedFields = ['e_total', 'e_bloqueo', 'e_merma', 'id_producto'];

    public function getInventario()
    {
        return $this->db->table('existencias e')
            ->select('
                e.id_producto,
                p.nombre,
                p.descripcion,
                ent.precio_sugerido AS precio_venta,
                ent.unidad_venta AS unidad_medida,
                e.e_total AS existencias_totales,
                e.e_bloqueo AS existencias_bloqueadas
            ')
            ->join('producto p', 'p.id = e.id_producto')
            ->join('entrada ent', 'ent.id_producto = e.id_producto', 'left')
            ->groupBy('e.id_producto')  // Evita repetir productos si hay varias entradas
            ->orderBy('p.nombre', 'ASC')
            ->get()
            ->getResultArray();
    }
}