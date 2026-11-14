<?php
namespace App\Services;

/**
 * ALERTAS PREDICTIVAS DE STOCK CRÍTICO
 * ------------------------------------------------------------
 *  consumo diario = ventas por día + merma por día
 *                   (60 % lo de los últimos 7 días, 40 % el resto de la ventana,
 *                    porque en fruver la demanda cambia rápido)
 *  cobertura      = stock disponible ÷ consumo diario        (días que alcanza)
 *
 *  CRÍTICO   : la cobertura es menor al tiempo de reposición del producto
 *              (se acaba antes de que llegue la mercancía) o ya no hay stock.
 *  EN ATENCIÓN: la cobertura no rebasa el tiempo de reposición + el colchón.
 *  OK        : alcanza con margen.
 *  SIN VENTAS: no hay consumo en la ventana, no se puede predecir.
 *
 * Solo usa $db->query($sql, $binds)->getResultArray(), así que corre con
 * CodeIgniter (Config\Database) y también se puede probar fuera de él.
 */
class AlertasStockService
{
    private const PESO_RECIENTE = 0.6;     // peso de los últimos 7 días
    private const ZONA          = 'America/Mexico_City';

    private $db;
    private ?array $configCache = null;

    public function __construct($db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    private function filas(string $sql, array $binds = []): array
    {
        return $this->db->query($sql, $binds)->getResultArray();
    }

    /* ============================ configuración ============================ */

    public function config(): array
    {
        if ($this->configCache === null) {
            $cfg = ['ventana_dias' => 14.0, 'colchon_dias' => 1.0, 'dias_objetivo' => 5.0];
            foreach ($this->filas('SELECT clave, valor FROM alertas_config') as $f) {
                if (isset($cfg[$f['clave']])) {
                    $cfg[$f['clave']] = (float) $f['valor'];
                }
            }
            $cfg['ventana_dias'] = max(7, (int) $cfg['ventana_dias']);
            $this->configCache = $cfg;
        }
        return $this->configCache;
    }

    public function guardarConfig(int $ventana, float $colchon, float $objetivo): bool
    {
        if ($ventana < 7 || $ventana > 90 || $colchon < 0 || $colchon > 30 || $objetivo < 1 || $objetivo > 60) {
            return false;
        }
        foreach (['ventana_dias' => $ventana, 'colchon_dias' => $colchon, 'dias_objetivo' => $objetivo] as $k => $v) {
            $this->db->query(
                'INSERT INTO alertas_config (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)',
                [$k, (string) $v]
            );
        }
        $this->configCache = null;
        return true;
    }

    public function guardarReposicion(int $idProducto, float $dias): bool
    {
        if ($idProducto <= 0 || $dias < 0.5 || $dias > 90) {
            return false;
        }
        $this->db->query('UPDATE producto SET dias_reposicion = ? WHERE id = ?', [$dias, $idProducto]);
        return true;
    }

    /* ============================ cálculo ============================ */

    /** Tasa diaria combinando "últimos 7 días" y "resto de la ventana" */
    private static function tasa(float $total, float $reciente, int $diasObs): float
    {
        if ($diasObs <= 7) {
            return $total / max(1, $diasObs);
        }
        return self::PESO_RECIENTE * ($reciente / 7)
             + (1 - self::PESO_RECIENTE) * (($total - $reciente) / ($diasObs - 7));
    }

    /**
     * Calcula el estado de cada producto.
     * $ahora solo se usa en pruebas (formato Y-m-d H:i:s).
     */
    public function calcular(?string $ahora = null): array
    {
        $cfg = $this->config();
        $W   = (int) $cfg['ventana_dias'];

        $ahoraDT = $ahora
            ? new \DateTime($ahora, new \DateTimeZone(self::ZONA))
            : new \DateTime('now', new \DateTimeZone(self::ZONA));
        $hoy     = (clone $ahoraDT)->setTime(0, 0, 0);
        $desdeW  = (clone $hoy)->modify('-' . ($W - 1) . ' days')->format('Y-m-d H:i:s');
        $desde7  = (clone $hoy)->modify('-6 days')->format('Y-m-d H:i:s');

        // --- stock disponible por producto (e_total - e_bloqueo) y unidad del último lote ---
        $productos = $this->filas(
            'SELECT p.id, p.codigo, p.nombre, p.dias_reposicion,
                    COALESCE(SUM(e.e_total), 0)   AS total,
                    COALESCE(SUM(e.e_bloqueo), 0) AS bloqueado,
                    (SELECT ent.unidad_venta FROM entrada ent
                      WHERE ent.id_producto = p.id ORDER BY ent.id DESC LIMIT 1) AS unidad
               FROM producto p
               LEFT JOIN existencias e ON e.id_producto = p.id
              GROUP BY p.id, p.codigo, p.nombre, p.dias_reposicion
              ORDER BY p.nombre'
        );

        // --- ventas reales (pedidos que ya descontaron stock) dentro de la ventana ---
        $ventas = [];
        foreach ($this->filas(
            'SELECT pp.id_producto,
                    SUM(pp.cantidad) AS total,
                    SUM(CASE WHEN pe.fecha >= ? THEN pp.cantidad ELSE 0 END) AS reciente
               FROM producto_pedido pp
               JOIN pedido pe ON pe.id = pp.id_pedido
              WHERE pe.stock_descontado = 1 AND pe.fecha >= ?
              GROUP BY pp.id_producto',
            [$desde7, $desdeW]
        ) as $f) {
            $ventas[(int) $f['id_producto']] = ['total' => (float) $f['total'], 'rec' => (float) $f['reciente']];
        }

        // --- mermas dentro de la ventana ---
        // Las "SISTEMA: CADUCIDAD AUTOMÁTICA" son bajas de lotes completos que nadie vendió:
        // no son un ritmo de consumo, así que no entran a la tasa diaria (sí al % de merma).
        $mermas = [];
        foreach ($this->filas(
            "SELECT e.id_producto,
                    SUM(CASE WHEN m.motivo LIKE 'SISTEMA:%' THEN 0 ELSE m.cantidad END) AS total,
                    SUM(CASE WHEN m.motivo LIKE 'SISTEMA:%' OR m.fecha < ? THEN 0 ELSE m.cantidad END) AS reciente,
                    SUM(m.cantidad) AS todas
               FROM merma m
               JOIN entrada e ON e.id = m.id_entrada
              WHERE m.fecha >= ?
              GROUP BY e.id_producto",
            [$desde7, $desdeW]
        ) as $f) {
            $mermas[(int) $f['id_producto']] = [
                'total' => (float) $f['total'], 'rec' => (float) $f['reciente'], 'todas' => (float) $f['todas'],
            ];
        }

        // --- motivo de merma más frecuente por producto ---
        $motivos = [];
        foreach ($this->filas(
            'SELECT e.id_producto, LOWER(TRIM(m.motivo)) AS motivo, SUM(m.cantidad) AS c
               FROM merma m
               JOIN entrada e ON e.id = m.id_entrada
              WHERE m.fecha >= ?
              GROUP BY e.id_producto, LOWER(TRIM(m.motivo))
              ORDER BY c DESC',
            [$desdeW]
        ) as $f) {
            $id = (int) $f['id_producto'];
            if (!isset($motivos[$id]) && trim((string) $f['motivo']) !== '') {
                $motivos[$id] = $f['motivo'];
            }
        }

        // --- desde cuándo hay movimiento (para no diluir el promedio de productos nuevos) ---
        $primera = [];
        foreach ($this->filas(
            'SELECT id_producto, MIN(fecha) AS primera FROM (
                 SELECT id_producto, fecha FROM entrada
                 UNION ALL
                 SELECT pp.id_producto, pe.fecha
                   FROM producto_pedido pp JOIN pedido pe ON pe.id = pp.id_pedido
                  WHERE pe.stock_descontado = 1
             ) t GROUP BY id_producto'
        ) as $f) {
            $primera[(int) $f['id_producto']] = $f['primera'];
        }

        // --- cálculo por producto ---
        $orden = ['critico' => 0, 'atencion' => 1, 'ok' => 2, 'sin_consumo' => 3];
        $res   = [];
        $resumen = ['critico' => 0, 'atencion' => 0, 'ok' => 0, 'sin_consumo' => 0];

        foreach ($productos as $p) {
            $id = (int) $p['id'];

            $diasObs = $W;
            if (!empty($primera[$id])) {
                $inicio  = (new \DateTime($primera[$id], new \DateTimeZone(self::ZONA)))->setTime(0, 0, 0);
                $diasObs = max(1, min($W, (int) $inicio->diff($hoy)->days + 1));
            }

            $v = $ventas[$id] ?? ['total' => 0.0, 'rec' => 0.0];
            $m = $mermas[$id] ?? ['total' => 0.0, 'rec' => 0.0, 'todas' => 0.0];

            $ventasDia = self::tasa($v['total'], $v['rec'], $diasObs);
            $mermaDia  = self::tasa($m['total'], $m['rec'], $diasObs);
            $consumo   = max(0.0, $ventasDia + $mermaDia);

            $stock    = max(0.0, (float) $p['total'] - (float) $p['bloqueado']);
            $reposDias = (float) $p['dias_reposicion'];

            $cobertura = $consumo > 0 ? $stock / $consumo : null;

            if ($consumo <= 0) {
                $nivel = 'sin_consumo';
            } elseif ($stock <= 0 || $cobertura < $reposDias) {
                $nivel = 'critico';
            } elseif ($cobertura < $reposDias + $cfg['colchon_dias']) {
                $nivel = 'atencion';
            } else {
                $nivel = 'ok';
            }
            $resumen[$nivel]++;

            // Cuánto pedir: lo que se consumirá mientras llega + los días objetivo, menos lo que hay
            $pedir = ($consumo > 0 && in_array($nivel, ['critico', 'atencion'], true))
                ? (int) max(0, ceil($consumo * ($reposDias + $cfg['dias_objetivo']) - $stock))
                : 0;

            $salidas = $v['total'] + $m['todas'];      // para el % de merma entran todas las bajas

            $res[] = [
                'id'              => $id,
                'codigo'          => $p['codigo'],
                'nombre'          => $p['nombre'],
                'unidad'          => $p['unidad'] ?: 'unidad',
                'stock'           => round($stock, 2),
                'dias_reposicion' => $reposDias,
                'ventas_dia'      => round($ventasDia, 2),
                'merma_dia'       => round($mermaDia, 2),
                'consumo_dia'     => round($consumo, 2),
                'cobertura'       => $cobertura === null ? null : round($cobertura, 1),
                'agota'           => $cobertura === null ? null
                                       : (clone $ahoraDT)->modify('+' . (int) floor($cobertura) . ' days')->format('Y-m-d'),
                'nivel'           => $nivel,
                'pedir'           => $pedir,
                'merma_pct'       => $salidas > 0 ? round($m['todas'] / $salidas * 100, 1) : 0.0,
                'motivo_top'      => isset($motivos[$id])
                                       ? (str_starts_with($motivos[$id], 'sistema') ? 'caducidad automática' : $motivos[$id])
                                       : null,
                'dias_obs'        => $diasObs,
            ];
        }

        usort($res, function ($a, $b) use ($orden) {
            return [$orden[$a['nivel']], $a['cobertura'] ?? INF, $a['nombre']]
               <=> [$orden[$b['nivel']], $b['cobertura'] ?? INF, $b['nombre']];
        });

        return [
            'productos' => $res,
            'resumen'   => $resumen,
            'config'    => $cfg,
            'generado'  => $ahoraDT->format('Y-m-d H:i:s'),
        ];
    }

    /** Versión corta para la campanita del menú: conteos y los más urgentes */
    public function resumenRapido(int $limite = 5): array
    {
        $r = $this->calcular();
        $urgentes = array_values(array_filter($r['productos'], fn($p) => $p['nivel'] === 'critico'));
        return [
            'criticos'  => $r['resumen']['critico'],
            'atencion'  => $r['resumen']['atencion'],
            'urgentes'  => array_map(fn($p) => [
                'id' => $p['id'], 'nombre' => $p['nombre'], 'cobertura' => $p['cobertura'],
                'stock' => $p['stock'], 'unidad' => $p['unidad'],
            ], array_slice($urgentes, 0, $limite)),
        ];
    }
}