<?php

namespace App\Services;

use App\Libraries\Exportador;

/**
 * Datos de los reportes que se pueden exportar a PDF y a Excel.
 * Cada método devuelve la estructura que entiende App\Libraries\Exportador.
 */
class ExportacionesService
{
    /** Con este disponible o menos, el producto cuenta como "poco stock" (igual que en el catálogo del repartidor) */
    private const UMBRAL_POCO = 5;

    private const METODOS = ['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'tarjeta' => 'Tarjeta'];
    private const ORIGENES = [
        'venta' => 'Venta', 'abono' => 'Abono', 'entrega' => 'Cobro en entrega',
        'devolucion' => 'Devolución', 'ajuste' => 'Ajuste', 'migracion' => 'Pago anterior al registro',
    ];

    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /* ============================ corte de caja ============================ */

    public function corte(string $fecha): array
    {
        $servicio = new CorteService($this->db);
        $r        = $servicio->resumen($fecha);
        $guardado = $servicio->ultimoDe($fecha);

        $resumen = [
            ['Pedidos del día', $r['ventas']['n'], 'entero'],
            ['Ventas del día', $r['ventas']['total'], 'dinero'],
            ['Cobrado de esas ventas', $r['ventas_cobradas'], 'dinero'],
            ['Quedó por cobrar de esas ventas', $r['ventas_credito'], 'dinero'],
            ['Total cobrado en el día (ventas, abonos y entregas)', $r['cobrado'], 'dinero'],
            ['Reembolsado', $r['reembolsado'], 'dinero'],
            ['Neto del día', $r['neto'], 'dinero'],
            ['Efectivo neto del día', $r['efectivo_neto'], 'dinero'],
            ['Devoluciones del día', $r['devoluciones']['n'], 'entero'],
            ['Créditos por cobrar en total', $r['por_cobrar'], 'dinero'],
        ];

        $notas = [];
        if ($guardado) {
            array_push(
                $resumen,
                ['Corte guardado por', $guardado['usuario'] ?: '—', 'texto'],
                ['Fondo inicial', (float) $guardado['fondo_inicial'], 'dinero'],
                ['Efectivo esperado', (float) $guardado['efectivo_esperado'], 'dinero'],
                ['Efectivo contado', (float) $guardado['efectivo_contado'], 'dinero'],
                ['Diferencia (contado - esperado)', (float) $guardado['diferencia'], 'dinero']
            );
            if (!empty($guardado['notas'])) {
                $notas[] = 'Notas del corte: ' . $guardado['notas'];
            }
        } else {
            $notas[] = 'Este día todavía no tiene un corte guardado: el efectivo esperado se calcula con fondo inicial más el efectivo neto.';
        }

        // Por método de pago
        $filasMetodo = [];
        $tot = ['cobrado' => 0.0, 'reembolsado' => 0.0, 'neto' => 0.0];
        foreach ($r['metodos'] as $clave => $x) {
            $filasMetodo[] = [self::METODOS[$clave] ?? ucfirst($clave), $x['cobrado'], $x['reembolsado'], $x['neto']];
            foreach ($tot as $k => $_) { $tot[$k] += $x[$k]; }
        }

        // Por origen del dinero
        $filasOrigen = [];
        foreach ($r['origenes'] as $origen => $monto) {
            $filasOrigen[] = [self::ORIGENES[$origen] ?? ucfirst((string) $origen), $monto];
        }

        // Movimientos
        $movimientos = [];
        $sumaMov = 0.0;
        foreach ($r['movimientos'] as $m) {
            $movimientos[] = [
                $m['hora'], $m['folio'], trim((string) $m['cliente']) ?: 'Público general',
                self::ORIGENES[$m['origen']] ?? $m['origen'], self::METODOS[$m['metodo']] ?? $m['metodo'],
                $m['monto'], $m['usuario'] ?: '—', $m['nota'] ?: '',
            ];
            $sumaMov += (float) $m['monto'];
        }

        return [
            'titulo'      => 'Corte de caja',
            'subtitulo'   => Exportador::fechaLarga($fecha),
            'archivo'     => 'Corte-de-caja-' . $fecha,
            'orientacion' => 'portrait',
            'resumen'     => $resumen,
            'tablas'      => [
                [
                    'titulo' => 'Por método de pago', 'hoja' => 'Resumen',
                    'columnas' => [['Método', 'texto'], ['Cobrado', 'dinero'], ['Reembolsado', 'dinero'], ['Neto', 'dinero']],
                    'filas' => $filasMetodo,
                    'total' => ['Total', $tot['cobrado'], $tot['reembolsado'], $tot['neto']],
                ],
                [
                    'titulo' => 'Origen del dinero', 'hoja' => 'Resumen',
                    'columnas' => [['Origen', 'texto'], ['Monto', 'dinero']],
                    'filas' => $filasOrigen, 'vacio' => 'Sin movimientos.',
                ],
                [
                    'titulo' => 'Movimientos del día', 'hoja' => 'Movimientos',
                    'columnas' => [['Hora', 'texto'], ['Folio', 'texto'], ['Cliente', 'texto'], ['Origen', 'texto'], ['Método', 'texto'], ['Monto', 'dinero'], ['Usuario', 'texto'], ['Nota', 'texto']],
                    'filas' => $movimientos,
                    'total' => $movimientos ? ['Total', null, null, null, null, $sumaMov, null, null] : null,
                    'vacio' => 'No hubo cobros ni reembolsos este día.',
                ],
            ],
            'notas' => $notas,
        ];
    }

    /* ============================ cobranza ============================ */

    public function cobranza(): array
    {
        $datos = (new CobranzaService($this->db))->listar();
        $res   = $datos['resumen'];
        $etiquetas = ['vencido' => 'Vencido', 'hoy' => 'Vence hoy', 'por_vencer' => 'Vence pronto', 'al_corriente' => 'Al corriente'];

        $porCliente = [];
        $detalle = [];
        $suma = ['total' => 0.0, 'pagado' => 0.0, 'saldo' => 0.0];
        foreach ($datos['clientes'] as $c) {
            $porCliente[] = [$c['cliente'], $c['tel'] ?: 'Sin teléfono', count($c['pedidos']), $c['saldo'], $etiquetas[$c['estado']] ?? $c['estado']];
            foreach ($c['pedidos'] as $p) {
                $detalle[] = [
                    $c['cliente'], $c['tel'] ?: '', $p['folio'], $p['fecha'], $p['total'], $p['pagado'], $p['saldo'],
                    $p['vence_texto'] . ($p['personalizado'] ? ' *' : ''), $p['dias'], $etiquetas[$p['estado']] ?? $p['estado'],
                ];
                $suma['total']  += $p['total'];
                $suma['pagado'] += $p['pagado'];
                $suma['saldo']  += $p['saldo'];
            }
        }

        return [
            'titulo'      => 'Cobranza de créditos',
            'subtitulo'   => 'Al ' . Exportador::fechaLarga(date('Y-m-d')),
            'archivo'     => 'Cobranza-' . date('Y-m-d'),
            'orientacion' => 'landscape',
            'resumen'     => [
                ['Por cobrar', $res['por_cobrar'], 'dinero'],
                ['Vencido', $res['vencido'], 'dinero'],
                ['Clientes con deuda', $res['clientes'], 'entero'],
                ['Clientes con saldo vencido', $res['vencidos'], 'entero'],
                ['Clientes que vencen pronto', $res['por_vencer'], 'entero'],
                ['Plazo del crédito (días)', $datos['config']['plazo'], 'entero'],
                ['Días de aviso antes del vencimiento', $datos['config']['aviso'], 'entero'],
            ],
            'tablas' => [
                [
                    'titulo' => 'Saldo por cliente', 'hoja' => 'Resumen',
                    'columnas' => [['Cliente', 'texto'], ['Teléfono', 'texto'], ['Pedidos', 'entero'], ['Saldo', 'dinero'], ['Estado', 'texto']],
                    'filas' => $porCliente, 'vacio' => 'No hay créditos pendientes de cobro.',
                    'total' => $porCliente ? ['Total', null, null, $suma['saldo'], null] : null,
                ],
                [
                    'titulo' => 'Pedidos a crédito', 'hoja' => 'Créditos',
                    'columnas' => [['Cliente', 'texto'], ['Teléfono', 'texto'], ['Folio', 'texto'], ['Fecha', 'texto'], ['Total', 'dinero'], ['Pagado', 'dinero'], ['Saldo', 'dinero'], ['Vence', 'texto'], ['Días', 'entero'], ['Estado', 'texto']],
                    'filas' => $detalle, 'vacio' => 'No hay créditos pendientes de cobro.',
                    'total' => $detalle ? ['Total', null, null, null, $suma['total'], $suma['pagado'], $suma['saldo'], null, null, null] : null,
                ],
            ],
            'notas' => ['Días: negativo = días de atraso; positivo = días que faltan. Un * en "Vence" indica una fecha límite fijada a mano.'],
        ];
    }

    /* ============================ inventario ============================ */

    public function inventario(): array
    {
        $conv  = $this->db->fieldExists('valor_conversion', 'entrada') ? 'COALESCE(NULLIF(en.valor_conversion, 0), 1)' : '1';
        $ultima = static fn (string $campo): string =>
            "(SELECT {$campo} FROM entrada en WHERE en.id_producto = p.id ORDER BY en.fecha DESC, en.id DESC LIMIT 1)";

        $filas = $this->db->query(
            "SELECT p.nombre, p.codigo,
                    COALESCE(SUM(e.e_total), 0)   AS total,
                    COALESCE(SUM(e.e_bloqueo), 0) AS bloqueado,
                    " . $ultima('en.categoria') . " AS categoria,
                    " . $ultima('en.unidad_venta') . " AS unidad,
                    " . $ultima('en.precio_sugerido') . " AS precio,
                    " . $ultima("en.precio_compra / {$conv}") . " AS costo,
                    (SELECT MIN(en.fecha_cad) FROM entrada en
                      WHERE en.id_producto = p.id AND en.cantidad_venta > 0 AND en.fecha_cad IS NOT NULL) AS caduca
               FROM producto p
               LEFT JOIN existencias e ON e.id_producto = p.id
              GROUP BY p.id, p.nombre, p.codigo
              ORDER BY p.nombre"
        )->getResultArray();

        $datos = [];
        $totales = ['costo' => 0.0, 'venta' => 0.0, 'agotados' => 0, 'poco' => 0, 'con' => 0];
        foreach ($filas as $f) {
            $total = (float) $f['total'];
            $disp  = max(0.0, $total - (float) $f['bloqueado']);
            $precio = $f['precio'] !== null ? (float) $f['precio'] : null;
            $costo  = $f['costo'] !== null ? round((float) $f['costo'], 2) : null;

            if ($disp <= 0) { $estado = 'Agotado';    $totales['agotados']++; }
            elseif ($disp <= self::UMBRAL_POCO) { $estado = 'Poco stock'; $totales['poco']++; $totales['con']++; }
            else { $estado = 'Disponible'; $totales['con']++; }

            $valCosto = $costo !== null ? round($disp * $costo, 2) : null;
            $valVenta = $precio !== null ? round($disp * $precio, 2) : null;
            $totales['costo'] += (float) $valCosto;
            $totales['venta'] += (float) $valVenta;

            $datos[] = [
                $f['nombre'], $f['codigo'] ?: '', $f['categoria'] ?: '', $f['unidad'] ?: '',
                $total, (float) $f['bloqueado'], $disp, $precio, $costo, $valCosto, $valVenta, $estado,
                $f['caduca'] ? date('d/m/Y', strtotime($f['caduca'])) : '',
            ];
        }

        return [
            'titulo'      => 'Inventario',
            'subtitulo'   => 'Existencias al ' . Exportador::fechaLarga(date('Y-m-d')),
            'archivo'     => 'Inventario-' . date('Y-m-d'),
            'orientacion' => 'landscape',
            'resumen'     => [
                ['Productos registrados', count($datos), 'entero'],
                ['Con existencias', $totales['con'], 'entero'],
                ['Poco stock (hasta ' . self::UMBRAL_POCO . ' disponibles)', $totales['poco'], 'entero'],
                ['Agotados', $totales['agotados'], 'entero'],
                ['Valor del inventario a costo', $totales['costo'], 'dinero'],
                ['Valor del inventario a precio de venta', $totales['venta'], 'dinero'],
                ['Utilidad potencial', $totales['venta'] - $totales['costo'], 'dinero'],
            ],
            'tablas' => [[
                'titulo' => 'Existencias por producto', 'hoja' => 'Inventario',
                'columnas' => [
                    ['Producto', 'texto'], ['Código', 'texto'], ['Categoría', 'texto'], ['Unidad', 'texto'],
                    ['Existencias', 'numero'], ['Bloqueadas', 'numero'], ['Disponible', 'numero'],
                    ['Precio de venta', 'dinero'], ['Costo unitario', 'dinero'], ['Valor a costo', 'dinero'], ['Valor a venta', 'dinero'],
                    ['Estado', 'texto'], ['Caduca (lote más próximo)', 'texto'],
                ],
                'filas' => $datos, 'vacio' => 'No hay productos registrados.',
                'total' => $datos ? ['Total', null, null, null, null, null, null, null, null, $totales['costo'], $totales['venta'], null, null] : null,
            ]],
            'notas' => ['El costo unitario sale de la última entrada del producto (precio de compra entre las unidades de venta que trae).'],
        ];
    }

    /* ============================ ventas por periodo ============================ */

    /**
     * Cobros y reembolsos del periodo por método de pago (fechas Y-m-d, ambas incluidas).
     * Usa el mismo criterio de fechas que el reporte de costos para que las cifras coincidan.
     *
     * @return array<string, array{cobrado: float, reembolsado: float}>
     */
    public function metodosPago(string $desde, string $hasta): array
    {
        $salida = [];
        foreach (self::METODOS as $clave => $_) {
            $salida[$clave] = ['cobrado' => 0.0, 'reembolsado' => 0.0];
        }
        if (!$this->db->tableExists('pago')) {
            return $salida;
        }

        $filas = $this->db->query(
            'SELECT metodo,
                    SUM(CASE WHEN monto > 0 THEN monto ELSE 0 END)  AS cobrado,
                    SUM(CASE WHEN monto < 0 THEN -monto ELSE 0 END) AS reembolsado
               FROM pago WHERE fecha BETWEEN ? AND ? GROUP BY metodo',
            [$desde . ' 00:00:00', $hasta . ' 23:59:59']
        )->getResultArray();

        foreach ($filas as $f) {
            $clave = isset($salida[$f['metodo']]) ? $f['metodo'] : 'efectivo';
            $salida[$clave]['cobrado']     += (float) $f['cobrado'];
            $salida[$clave]['reembolsado'] += (float) $f['reembolsado'];
        }
        foreach ($salida as &$s) {
            $s = ['cobrado' => round($s['cobrado'], 2), 'reembolsado' => round($s['reembolsado'], 2)];
        }
        unset($s);

        return $salida;
    }

    /**
     * @param array $periodo  resultado de ReporteCostosService::periodo()
     * @param array $reporte  resultado de ReporteCostosService::reporte()
     * @param array $metodos  cobros del periodo por método: [metodo => ['cobrado' => , 'reembolsado' => ]]
     */
    public function ventas(array $periodo, array $reporte, array $metodos): array
    {
        $t = $reporte['totales'];

        $dias = [];
        foreach ($reporte['dias'] as $d) {
            $dias[] = [date('d/m/Y', strtotime($d['dia'])), $d['ventas'], $d['costo'], $d['merma'], $d['utilidad']];
        }

        $productos = $reporte['productos'];
        usort($productos, static fn ($a, $b) => [$b['ventas'], $a['nombre']] <=> [$a['ventas'], $b['nombre']]);
        $filasProd = array_map(static fn ($p) => [
            $p['nombre'], $p['unidades'], $p['unidad'] ?: '', $p['ventas'], $p['costo'], $p['utilidad'], $p['margen'],
        ], $productos);

        $filasMetodo = [];
        foreach (self::METODOS as $clave => $nombre) {
            $m = $metodos[$clave] ?? ['cobrado' => 0.0, 'reembolsado' => 0.0];
            $filasMetodo[] = [$nombre, $m['cobrado'], $m['reembolsado']];
        }

        return [
            'titulo'      => 'Reporte de ventas',
            'subtitulo'   => $periodo['etiqueta'] . ' · ' . $periodo['texto'],
            'archivo'     => 'Reporte-ventas-' . $periodo['desde'] . ($periodo['desde'] !== $periodo['hasta'] ? '_a_' . $periodo['hasta'] : ''),
            'orientacion' => 'portrait',
            'resumen'     => [
                ['Ventas', $t['ventas'], 'dinero'],
                ['Pedidos', $t['pedidos'], 'entero'],
                ['Ticket promedio', $t['ticket'], 'dinero'],
                ['Costo de lo vendido', $t['costo'], 'dinero'],
                ['Utilidad', $t['utilidad'], 'dinero'],
                ['Margen', $t['margen'], 'porcentaje'],
                ['Merma', $t['merma'], 'dinero'],
                ['Utilidad neta (después de merma)', $t['utilidad_neta'], 'dinero'],
            ],
            'tablas' => [
                [
                    'titulo' => 'Ventas por día', 'hoja' => 'Por día',
                    'columnas' => [['Día', 'texto'], ['Ventas', 'dinero'], ['Costo', 'dinero'], ['Merma', 'dinero'], ['Utilidad', 'dinero']],
                    'filas' => $dias,
                    'total' => $dias ? ['Total', $t['ventas'], $t['costo'], $t['merma'], $t['utilidad_neta']] : null,
                ],
                [
                    'titulo' => 'Ventas por producto', 'hoja' => 'Por producto',
                    'columnas' => [['Producto', 'texto'], ['Cantidad', 'numero'], ['Unidad', 'texto'], ['Ventas', 'dinero'], ['Costo', 'dinero'], ['Utilidad', 'dinero'], ['Margen', 'porcentaje']],
                    'filas' => $filasProd, 'vacio' => 'No hubo ventas en este periodo.',
                    'total' => $filasProd ? ['Total', null, null, $t['ventas'], $t['costo'], $t['utilidad'], $t['margen']] : null,
                ],
                [
                    'titulo' => 'Cobros por método de pago', 'hoja' => 'Resumen',
                    'columnas' => [['Método', 'texto'], ['Cobrado', 'dinero'], ['Reembolsado', 'dinero']],
                    'filas' => $filasMetodo,
                ],
            ],
            'notas' => ['Ventas: pedidos confirmados del periodo (incluye los de crédito, estén cobrados o no). La utilidad por día ya descuenta la merma.'],
        ];
    }
}
