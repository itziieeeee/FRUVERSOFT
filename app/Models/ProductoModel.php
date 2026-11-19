<?php
namespace App\Models;
use CodeIgniter\Model;

class ProductoModel extends Model
{
    // 1. Configuración básica
    protected $table      = 'producto'; 
    protected $primaryKey = 'id';

    // 2. Campos que permiten que se guarden desde formularios
     protected $allowedFields = ['codigo', 'codigo_barras', 'nombre', 'descripcion', 'imagen', 'e_total', 'precio']; 

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

    // ------- Escáner -------

    /** ¿Ese código ya lo usa otro producto (como identificador PRD o como código de barras)? */
    public function codigoEnUso(string $codigo, ?int $exceptoId = null): bool
    {
        $builder = $this->db->table('producto')
            ->groupStart()
                ->where('codigo', $codigo)
                ->orWhere('codigo_barras', $codigo)
            ->groupEnd();

        if ($exceptoId !== null) {
            $builder->where('id !=', $exceptoId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Busca un producto por lo que se escaneó: el código de barras del empaque (EAN/UPC)
     * o su identificador propio (PRD-XXXXXX). Devuelve también stock, unidad y precio.
     */
    public function buscarPorCodigo(string $codigo): ?array
    {
        $fila = $this->db->table('producto p')
            ->select("
                p.id, p.codigo, p.codigo_barras, p.nombre, p.descripcion, p.imagen,
                (SELECT ent.unidad_venta FROM entrada ent WHERE ent.id_producto = p.id ORDER BY ent.id DESC LIMIT 1) AS unidad_venta,
                (SELECT ent.precio_sugerido FROM entrada ent WHERE ent.id_producto = p.id ORDER BY ent.id DESC LIMIT 1) AS precio_sugerido,
                COALESCE(SUM(ex.e_total), 0) AS existencias_totales,
                COALESCE(SUM(ex.e_bloqueo), 0) AS existencias_bloqueadas,
                COALESCE(SUM(ex.e_total - ex.e_bloqueo), 0) AS stock_disponible
            ", false)
            ->join('existencias ex', 'ex.id_producto = p.id', 'left')
            ->groupStart()
                ->where('p.codigo_barras', $codigo)
                ->orWhere('p.codigo', $codigo)
            ->groupEnd()
            ->groupBy('p.id, p.codigo, p.codigo_barras, p.nombre, p.descripcion, p.imagen')
            ->get()
            ->getRowArray();

        return $fila ?: null;
    }

    // ------- Consultas para entradas de inventario -------

    // De una lista de ids, devuelve solo los que existen en la tabla producto
    public function getIdsExistentes(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return [];
        }

        $filas = $this->select('id')->whereIn('id', $ids)->findAll();
        return array_map('intval', array_column($filas, 'id'));
    }
}