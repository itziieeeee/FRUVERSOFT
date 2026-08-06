<?php

namespace App\Models;

use CodeIgniter\Model;

class MermaModel extends Model
{
    protected $table = 'merma';

    public function get_lista_productos()
    {
        return $this->db->table('producto')
                        ->select('id_producto, producto as nombre')
                        ->get()
                        ->getResultArray();
    }

    // Productos con existencia disponible para mermar
    public function getProductosConExistencia()
    {
        return $this->db->table('existencias e')
            ->select('p.id as id_p, p.nombre, e.e_total')
            ->join('producto p', 'p.id = e.id_producto')
            ->where('e.e_total >', 0)
            ->get()
            ->getResultArray();
    }

    // Historial de mermas registradas
    public function getHistorialMermas()
    {
        return $this->db->table('merma m')
            ->select('p.nombre, m.cantidad, m.motivo, m.fecha')
            ->join('entrada e', 'e.id = m.id_entrada')
            ->join('producto p', 'p.id = e.id_producto')
            ->orderBy('m.fecha', 'DESC')
            ->get()
            ->getResultArray();
    }

    // Totales para la gráfica de entradas vs mermas
    public function getDatosGrafica()
    {
        return $this->db->query("
            SELECT 
                COALESCE(SUM(e.cantidad_compra), 0) AS total_entradas,
                COALESCE(SUM(m.cantidad), 0) AS total_merma
            FROM entrada e
            LEFT JOIN merma m ON m.id_entrada = e.id
        ")->getRowArray();
    }

    // Busca el lote (entrada) más antiguo con stock disponible
    public function buscarEntradaDisponible($id_producto)
    {
        return $this->db->table('entrada')
            ->where('id_producto', $id_producto)
            ->where('cantidad_venta >', 0)
            ->orderBy('fecha', 'ASC')
            ->get()
            ->getRowArray();
    }

    public function insertarMerma(array $data)
    {
        return $this->db->table('merma')->insert($data);
    }

    public function descontarEntrada($id_entrada, $nuevaCantidad)
    {
        return $this->db->table('entrada')
            ->where('id', $id_entrada)
            ->update(['cantidad_venta' => $nuevaCantidad]);
    }

    public function getExistencia($id_producto)
    {
        return $this->db->table('existencias')
            ->where('id_producto', $id_producto)
            ->get()
            ->getRowArray();
    }

    public function actualizarExistencia($id_producto, $totalNuevo, $mermaNueva)
    {
        return $this->db->table('existencias')
            ->where('id_producto', $id_producto)
            ->update([
                'e_total' => $totalNuevo,
                'e_merma' => $mermaNueva
            ]);
    }

    // Maneja todo el proceso de registrar una merma
    public function registrarMerma($id_producto, $cantidad_mermar, $motivo)
    {
        $entrada = $this->buscarEntradaDisponible($id_producto);

        if (!$entrada) {
            return ['ok' => false, 'error' => 'No hay stock disponible para este producto en las entradas.'];
        }

        $this->insertarMerma([
            'cantidad'   => $cantidad_mermar,
            'fecha'      => date('Y-m-d H:i:s'),
            'motivo'     => $motivo,
            'id_entrada' => $entrada['id'],
            'aplicada'   => 1
        ]);

        $this->descontarEntrada($entrada['id'], $entrada['cantidad_venta'] - $cantidad_mermar);

        $existencia = $this->getExistencia($id_producto);

        if ($existencia) {
            $this->actualizarExistencia(
                $id_producto,
                $existencia['e_total'] - $cantidad_mermar,
                $existencia['e_merma'] + $cantidad_mermar
            );
        }

        return ['ok' => true];
    }
}