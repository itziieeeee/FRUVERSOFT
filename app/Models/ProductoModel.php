<?php
namespace App\Models;
use CodeIgniter\Model;

class ProductoModel extends Model
{
    // 1. Configuración básica
    protected $table      = 'producto'; 
    protected $primaryKey = 'id';

    // 2. Campos que permiten que se guarden desde formularios
    protected $allowedFields = ['nombre', 'descripcion', 'imagen', 'e_total', 'precio']; 

    // 3. Retorno de datos
    protected $returnType = 'array';

    // 4. Timestamps
    protected $useTimestamps = false; 

    // ------- Consultas para guardar() -------

    public function existeNombre($nombreRaw)
    {
        return $this->where('LOWER(nombre)', strtolower($nombreRaw))->first();
    }

    // ------- Consultas para pantalla_productos() -------

    public function getProductosFiltrados($q = null, $orden = null)
    {
        if (!empty($q)) {
            $this->groupStart()
                 ->like('nombre', $q)
                 ->orLike('descripcion', $q)
                 ->groupEnd();
        }

        if ($orden == 'stock_mayor') {
            $this->orderBy('e_total', 'DESC');
        }

        return $this->paginate(8);
    }

    // ------- Consultas para editar() -------

    public function existeNombreEnOtro($nombre, $id)
    {
        return $this->where('LOWER(nombre)', strtolower($nombre))
                    ->where('id !=', $id)
                    ->first();
    }

    // ------- Consultas para eliminar() -------

    public function estaEnPedido($id)
    {
        return $this->db->table('producto_pedido')
                        ->where('id_producto', $id)
                        ->countAllResults();
    }
}