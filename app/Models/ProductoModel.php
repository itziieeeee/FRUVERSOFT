<?php
namespace App\Models;
use CodeIgniter\Model;
class ProductoModel extends Model
{
    protected $table      = 'producto'; 
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'descripcion', 'imagen']; 

public function busqueda($filtros)
{
    $builder = $this->builder();

    // BÚSQUEDA POR TEXTO
    if (!empty($filtros['q'])) {
        $builder->groupStart()
                ->like('nombre', $filtros['q'])
                ->groupEnd();
    }
    // FILTRO POR CATEGORÍA
    if (!empty($filtros['categoria'])) {
        $builder->where('categoria_id', $filtros['categoria']);
    }
    // ORDEN
    if (!empty($filtros['orden'])) {
        if ($filtros['orden'] == 'menor') {
            $builder->orderBy('precio', 'ASC');
        } else if ($filtros['orden'] == 'mayor') {
            $builder->orderBy('precio', 'DESC');
        }
    }
    return $builder;
}    }
