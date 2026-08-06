<?php
namespace App\Models;
use CodeIgniter\Model;

class ExistenciasModel extends Model
{
    protected $table      = 'existencias';
    protected $primaryKey = 'id';
    protected $allowedFields = ['e_total', 'e_bloqueo', 'e_merma', 'id_producto'];

    public function getInventario($perPage = 10)
    {
        $builder = $this->db->table('existencias e')
            ->select('
                e.id_producto,
                p.nombre,
                p.descripcion,
                (SELECT ent2.precio_sugerido FROM entrada ent2
                 WHERE ent2.id_producto = e.id_producto
                 ORDER BY ent2.id DESC LIMIT 1) AS precio_venta,
                (SELECT ent2.unidad_venta FROM entrada ent2
                 WHERE ent2.id_producto = e.id_producto
                 ORDER BY ent2.id DESC LIMIT 1) AS unidad_venta,
                (SELECT ent2.categoria FROM entrada ent2
                 WHERE ent2.id_producto = e.id_producto
                 ORDER BY ent2.id DESC LIMIT 1) AS categoria,
                SUM(e.e_total)   AS existencias_totales,
                SUM(e.e_bloqueo) AS existencias_bloqueadas
            ')
            ->join('producto p', 'p.id = e.id_producto')
            ->groupBy('e.id_producto, p.nombre, p.descripcion')
            ->orderBy('p.nombre', 'ASC');

        $this->pager = \Config\Services::pager();
        $page   = (int)($_GET['page'] ?? 1);
        $offset = ($page - 1) * $perPage;
        $total  = $builder->countAllResults(false);
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
                (SELECT ent2.precio_sugerido FROM entrada ent2
                 WHERE ent2.id_producto = e.id_producto
                 ORDER BY ent2.id DESC LIMIT 1) AS precio_venta,
                (SELECT ent2.unidad_venta FROM entrada ent2
                 WHERE ent2.id_producto = e.id_producto
                 ORDER BY ent2.id DESC LIMIT 1) AS unidad_venta,
                (SELECT ent2.categoria FROM entrada ent2
                 WHERE ent2.id_producto = e.id_producto
                 ORDER BY ent2.id DESC LIMIT 1) AS categoria,
                SUM(e.e_total)   AS existencias_totales,
                SUM(e.e_bloqueo) AS existencias_bloqueadas
            ')
            ->join('producto p', 'p.id = e.id_producto')
            ->groupBy('e.id_producto, p.nombre, p.descripcion')
            ->where('e.id_producto', $id)
            ->get()
            ->getRowArray();
    }

    public function actualizarExistencia($id, $datos)
    {
        $productoData = [];
        if (isset($datos['nombre']))      $productoData['nombre']      = $datos['nombre'];
        if (isset($datos['descripcion'])) $productoData['descripcion'] = $datos['descripcion'];

        if (!empty($productoData)) {
            $this->db->table('producto')->where('id', $id)->update($productoData);
        }

        if (isset($datos['unidad_medida'])) {
            $this->db->table('entrada')
                ->where('id_producto', $id)
                ->update(['unidad_venta' => $datos['unidad_medida']]);
        }

        $updateData = [];
        if (isset($datos['existencias_totales']))    $updateData['e_total']   = $datos['existencias_totales'];
        if (isset($datos['existencias_bloqueadas'])) $updateData['e_bloqueo'] = $datos['existencias_bloqueadas'];

        if (!empty($updateData)) {
            $this->db->table('existencias')->where('id_producto', $id)->update($updateData);
        }

        return true;
    }

    public function validarStockPedido($productos)
    {
        $faltantes = [];
        foreach ($productos as $prod) {
            $id_producto = (int) $prod['id_producto'];
            $cantidad    = (float) $prod['cantidad'];

            $existencia = $this->db->table('existencias')
                ->select('e_total, e_bloqueo')
                ->where('id_producto', $id_producto)
                ->get()->getRowArray();

            $disponible = $existencia ? ($existencia['e_total'] - $existencia['e_bloqueo']) : 0;

            if ($disponible < $cantidad) {
                $faltantes[] = [
                    'id_producto' => $id_producto,
                    'requerido'   => $cantidad,
                    'disponible'  => $disponible,
                ];
            }
        }
        return $faltantes;
    }

    public function descontarStock($productos)
    {
        foreach ($productos as $prod) {
            $this->db->table('existencias')
                ->where('id_producto', (int) $prod['id_producto'])
                ->set('e_total',   'e_total - '   . (float)$prod['cantidad'], false)
                ->set('e_bloqueo', 'e_bloqueo + ' . (float)$prod['cantidad'], false)
                ->update();
        }
        return true;
    }
    public function contarProductoEnPedidos($id)
    {
        return $this->db->table('producto_pedido')
            ->where('id_producto', $id)
            ->countAllResults();
    }

    public function eliminarProductoCompleto($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $db->table('existencias')->where('id_producto', $id)->delete();
        $db->table('entrada')->where('id_producto', $id)->delete();

        $db->transComplete();

        return $db->transStatus();
    }
    // Revisa entradas caducadas, las manda a merma y ajusta existencias
    public function procesarCaducados()
    {
        $hoy = date('Y-m-d');

        $caducados = $this->db->table('entrada')
            ->where('fecha_cad <=', $hoy)
            ->where('cantidad_venta >', 0)
            ->get()
            ->getResultArray();

        foreach ($caducados as $fila) {
            $cantidad_mermar = $fila['cantidad_venta'];

            $this->db->table('merma')->insert([
                'id_entrada' => $fila['id'],
                'cantidad'   => $cantidad_mermar,
                'motivo'     => 'SISTEMA: CADUCIDAD AUTOMÁTICA (5 DÍAS)',
                'fecha'      => $hoy
            ]);

            $existencia = $this->db->table('existencias')
                ->where('id_producto', $fila['id_producto'])
                ->get()
                ->getRowArray();

            if ($existencia) {
                $this->db->table('existencias')
                    ->where('id_producto', $fila['id_producto'])
                    ->update([
                        'e_total' => $existencia['e_total'] - $cantidad_mermar,
                        'e_merma' => $existencia['e_merma'] + $cantidad_mermar
                    ]);
            }

            $this->db->table('entrada')
                ->where('id', $fila['id'])
                ->update(['cantidad_venta' => 0]);
        }

        return true;
    }

    // Stock consolidado (nombre, total, merma) para la tabla principal de inventario
    public function getStockConsolidado()
    {
        return $this->db->table('existencias e')
            ->select('p.nombre, e.e_total, e.e_merma')
            ->join('producto p', 'p.id = e.id_producto')
            ->get()
            ->getResultArray();
    }

    // Productos con stock real disponible (para poder hacerles merma manual)
    public function getProductosConStock()
    {
        return $this->db->table('existencias e')
            ->select('p.id as id_p, p.nombre, e.e_total')
            ->join('producto p', 'p.id = e.id_producto')
            ->where('e.e_total >', 0)
            ->get()
            ->getResultArray();
    }
    // Si el producto ya tiene existencias, suma; si no, crea el registro
    public function agregarOActualizarStock($id_producto, $cantidad)
    {
        $existencia = $this->db->table('existencias')
            ->where('id_producto', $id_producto)
            ->get()
            ->getRowArray();

        if ($existencia) {
            $nuevoTotal = $existencia['e_total'] + $cantidad;

            $this->db->table('existencias')
                ->where('id_producto', $id_producto)
                ->update(['e_total' => $nuevoTotal]);
        } else {
            $this->db->table('existencias')->insert([
                'id_producto' => $id_producto,
                'e_total'     => $cantidad,
                'e_bloqueo'   => 0,
                'e_merma'     => 0,
            ]);
        }

        return true;
    }
}