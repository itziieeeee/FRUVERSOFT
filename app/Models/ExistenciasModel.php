<?php
namespace App\Models;
use CodeIgniter\Model;
class ExistenciasModel extends Model
{
    protected $table = 'existencias';
    protected $primaryKey = 'id';
    protected $allowedFields = ['e_total', 'e_bloqueo', 'e_merma', 'id_producto'];

   public function getInventario($perPage = 10)
{
    $builder = $this->db->table('existencias e')
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
        ->groupBy('e.id_producto')
        ->orderBy('p.nombre', 'ASC');
    $this->pager = \Config\Services::pager();
    $page = (int)($_GET['page'] ?? 1);
    $offset = ($page - 1) * $perPage;
    $total = $builder->countAllResults(false);
    $this->pager->makeLinks($page, $perPage, $total);
    return $builder->limit($perPage, $offset)->get()->getResultArray();
}
public function getProductoById($id)
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
        ->groupBy('e.id_producto')
        ->where('e.id_producto', $id)
        ->get()
        ->getRowArray();
}

public function actualizarExistencia($id, $datos)
{
    // Actualizar descripción en tabla producto
    if (isset($datos['descripcion'])) {
        $this->db->table('producto')
            ->where('id', $id)
            ->update(['descripcion' => $datos['descripcion']]);
    }

    // Actualizar unidad_venta en tabla entrada
    if (isset($datos['unidad_medida'])) {
        $this->db->table('entrada')
            ->where('id_producto', $id)
            ->update(['unidad_venta' => $datos['unidad_medida']]);
    }

    // Actualizar existencias en tabla existencias
    $updateData = [];
    if (isset($datos['existencias_totales'])) {
        $updateData['e_total'] = $datos['existencias_totales'];
    }
    if (isset($datos['existencias_bloqueadas'])) {
        $updateData['e_bloqueo'] = $datos['existencias_bloqueadas'];
    }

    if (!empty($updateData)) {
        $this->db->table('existencias')
            ->where('id_producto', $id)
            ->update($updateData);
    }

    return true;
}
}