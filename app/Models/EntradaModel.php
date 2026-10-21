<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\ExistenciasModel;
use App\Models\PedidoModel;

class EntradaModel extends Model
{
    // Periodo (en días) y cantidad máxima de productos que se marcan como "frecuentes"
    public const DIAS_ROTACION    = 30;
    public const TOP_FRECUENTES   = 8;

    protected $table      = 'entrada';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_producto',
        'precio_compra',
        'cantidad_compra',
        'unidad_compra',
        'precio_sugerido',
        'unidad_venta',
        'cantidad_venta',
        'categoria',
        'fecha',
        'fecha_cad'
    ];

    /**
     * Catálogo para el modal de entrada: un registro por producto con su imagen,
     * existencias actuales y los datos de su ÚLTIMA entrada (costo, precio sugerido,
     * unidades, categoría y conversión). Solo lee; no modifica nada.
     */
    public function getCatalogoEntrada(): array
    {
        // Si algún día se agrega la columna valor_conversion, se aprovecha sola
        $tieneConv = $this->db->fieldExists('valor_conversion', $this->table);
        $colConv   = $tieneConv ? ', le.valor_conversion' : '';

        $sql = "SELECT p.id, p.nombre, p.imagen,
                       (SELECT COALESCE(SUM(ex.e_total), 0)
                          FROM existencias ex
                         WHERE ex.id_producto = p.id) AS stock,
                       le.precio_compra, le.precio_sugerido,
                       le.unidad_compra, le.unidad_venta, le.categoria,
                       le.cantidad_compra, le.cantidad_venta,
                       le.fecha AS fecha_ultima{$colConv}
                  FROM producto p
             LEFT JOIN entrada le
                    ON le.id = (SELECT MAX(x.id) FROM entrada x WHERE x.id_producto = p.id)
              ORDER BY p.nombre ASC";

        $filas = $this->db->query($sql)->getResultArray();

        foreach ($filas as &$f) {
            $conv = 0;
            if ($tieneConv && (float) ($f['valor_conversion'] ?? 0) > 0) {
                $conv = (float) $f['valor_conversion'];
            } elseif ((float) ($f['cantidad_compra'] ?? 0) > 0 && (float) ($f['cantidad_venta'] ?? 0) > 0) {
                // Se deduce de la última entrada (cantidad_venta / cantidad_compra)
                $conv = round($f['cantidad_venta'] / $f['cantidad_compra'], 4);
            }
            $f['conversion'] = $conv > 0 ? $conv : null;
            unset($f['cantidad_compra'], $f['cantidad_venta']);
        }
        unset($f);

        // Rotación por ventas: los más vendidos van primero y se marcan como frecuentes
        $rotacion = (new PedidoModel())->getRotacionPorProducto(self::DIAS_ROTACION);
        foreach ($filas as &$f) {
            $r = $rotacion[(int) $f['id']] ?? null;
            $f['ventas_periodo']  = $r['ventas']  ?? 0;
            $f['ingreso_periodo'] = $r['ingreso'] ?? 0;
        }
        unset($f);

        usort($filas, static function ($a, $b) {
            return [$b['ventas_periodo'], $b['ingreso_periodo'], $a['nombre']]
               <=> [$a['ventas_periodo'], $a['ingreso_periodo'], $b['nombre']];
        });

        $marcados = 0;
        foreach ($filas as &$f) {
            $f['frecuente'] = $f['ventas_periodo'] > 0 && $marcados < self::TOP_FRECUENTES;
            if ($f['frecuente']) {
                $marcados++;
            }
        }
        unset($f);

        // Los que no son frecuentes vuelven a orden alfabético
        $frec = array_values(array_filter($filas, static fn($f) => $f['frecuente']));
        $resto = array_values(array_filter($filas, static fn($f) => !$f['frecuente']));
        usort($resto, static fn($a, $b) => strcasecmp($a['nombre'], $b['nombre']));

        return array_merge($frec, $resto);
    }

    /**
     * Guarda todas las entradas y suma el stock en UNA transacción (todo o nada).
     * Cada entrada es un registro nuevo, así que el historial de costos nunca se sobrescribe.
     * Usa el Query Builder / Model (consultas preparadas), sin concatenar datos del usuario.
     *
     * @param array $filas Filas ya validadas por el controlador. Cada una trae las columnas de
     *                     `entrada` más '_conv' (factor de conversión).
     */
    public function registrarEntradas(array $filas): bool
    {
        $existenciasModel = new ExistenciasModel();

        // Si existe la columna valor_conversion en la tabla, se guarda también
        $guardaConv = $this->db->fieldExists('valor_conversion', $this->table);
        if ($guardaConv) {
            $this->setAllowedFields(array_merge($this->allowedFields, ['valor_conversion']));
        }

        $fecha     = date('Y-m-d H:i:s');
        $caducidad = date('Y-m-d', strtotime('+5 days'));

        $this->db->transBegin();
        try {
            foreach ($filas as $fila) {
                $conv = $fila['_conv'];
                unset($fila['_conv']);

                $fila['fecha']     = $fecha;
                $fila['fecha_cad'] = $caducidad;
                if ($guardaConv) {
                    $fila['valor_conversion'] = $conv;
                }

                if ($this->insert($fila) === false) {
                    throw new \RuntimeException('No se pudo guardar una de las entradas.');
                }

                $existenciasModel->agregarOActualizarStock($fila['id_producto'], $fila['cantidad_venta']);
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Error en la base de datos.');
            }

            $this->db->transCommit();
            return true;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Error al guardar entradas: ' . $e->getMessage());
            return false;
        }
    }
}