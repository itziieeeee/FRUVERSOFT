<?php
namespace App\Services;

/**
 * COSTOS Y GANANCIAS
 * ------------------------------------------------------------
 *  Costo por unidad de venta de una entrada = precio_compra ÷ valor_conversion
 *      (precio por caja ÷ kilos por caja = precio por kilo; sin conversión se asume 1).
 *
 *  Venta       : cada línea de un pedido confirmado (pedido.stock_descontado = 1).
 *  Costo       : cantidad vendida × costo de la compra más reciente anterior a la venta
 *                (si la venta es anterior a toda compra, se usa la primera compra).
 *  Merma       : cantidad × costo del lote del que salió (incluye la caducidad automática).
 *  Utilidad bruta        = ventas − costo de lo vendido.
 *  Utilidad tras merma   = utilidad bruta − costo de la merma.
 *
 * Solo usa $db->query($sql, $binds)->getResultArray(): corre con CodeIgniter y
 * también se puede probar fuera de él.
 */
class ReporteCostosService
{
    private const ZONA  = 'America/Mexico_City';
    private const MESES = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                           'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    private $db;
    private ?bool $tieneConversion = null;

    public function __construct($db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    private function filas(string $sql, array $binds = []): array
    {
        return $this->db->query($sql, $binds)->getResultArray();
    }

    public function tieneConversion(): bool
    {
        if ($this->tieneConversion === null) {
            $this->tieneConversion = count($this->filas("SHOW COLUMNS FROM entrada LIKE 'valor_conversion'")) > 0;
        }
        return $this->tieneConversion;
    }

    /** Expresión SQL del factor de conversión de una entrada (1 si no hay) */
    private function conv(string $alias): string
    {
        return $this->tieneConversion() ? "COALESCE(NULLIF({$alias}.valor_conversion, 0), 1)" : '1';
    }

    /* ============================ periodos ============================ */

    private function fechaLarga(\DateTime $d): string
    {
        return $d->format('j') . ' de ' . self::MESES[(int) $d->format('n')] . ' de ' . $d->format('Y');
    }

    /**
     * Convierte "hoy", "ayer", "semana", "mes", "mes_pasado" o "rango" en fechas.
     * $ahora solo se usa en pruebas.
     */
    public function periodo(string $clave = 'hoy', ?string $desde = null, ?string $hasta = null, ?string $ahora = null): array
    {
        $tz  = new \DateTimeZone(self::ZONA);
        $hoy = (new \DateTime($ahora ?? 'now', $tz))->setTime(0, 0, 0);
        $d   = clone $hoy;
        $h   = clone $hoy;

        switch ($clave) {
            case 'ayer':
                $d->modify('-1 day'); $h = clone $d;
                $etq = 'Ayer';
                break;
            case 'semana':
                $d->modify('-6 days');
                $etq = 'Últimos 7 días';
                break;
            case 'mes':
                $d->modify('first day of this month');
                $etq = 'Este mes';
                break;
            case 'mes_pasado':
                $d->modify('first day of last month');
                $h = (clone $d)->modify('last day of this month');
                $etq = 'Mes pasado';
                break;
            case 'rango':
                $dd = \DateTime::createFromFormat('Y-m-d', (string) $desde, $tz);
                $hh = \DateTime::createFromFormat('Y-m-d', (string) $hasta, $tz);
                if ($dd && $hh) {
                    if ($dd > $hh) { [$dd, $hh] = [$hh, $dd]; }
                    if ($dd->diff($hh)->days > 365) { $dd = (clone $hh)->modify('-365 days'); }
                    $d = $dd->setTime(0, 0, 0);
                    $h = $hh->setTime(0, 0, 0);
                    $etq = 'Rango personalizado';
                    break;
                }
                // fechas inválidas: se cae a "hoy"
                $clave = 'hoy';
                // no break
            default:
                $clave = 'hoy';
                $etq   = 'Hoy';
        }

        $texto = $d == $h ? $this->fechaLarga($d)
                          : 'del ' . $this->fechaLarga($d) . ' al ' . $this->fechaLarga($h);

        return [
            'clave'    => $clave,
            'desde'    => $d->format('Y-m-d'),
            'hasta'    => $h->format('Y-m-d'),
            'etiqueta' => $etq,
            'texto'    => $texto,
            'dias'     => (int) $d->diff($h)->days + 1,
        ];
    }

    /* ============================ reporte ============================ */

    /** Ventas, costo, merma y utilidad del rango (fechas Y-m-d, ambas incluidas) */
    public function reporte(string $desde, string $hasta): array
    {
        $ini = $desde . ' 00:00:00';
        $fin = $hasta . ' 23:59:59';
        $cv  = $this->conv('en');

        // ---- una fila por línea vendida, con el costo vigente al día de la venta ----
        $lineas = $this->filas(
            "SELECT pp.id_producto, p.nombre, pe.id AS id_pedido, DATE(pe.fecha) AS dia,
                    pp.cantidad, pp.unidad_venta AS unidad,
                    COALESCE(pp.subtotal, pp.cantidad * pp.precio_venta) AS importe,
                    (SELECT en.precio_compra / {$cv} FROM entrada en
                      WHERE en.id_producto = pp.id_producto AND en.fecha <= pe.fecha
                      ORDER BY en.fecha DESC, en.id DESC LIMIT 1) AS costo_antes,
                    (SELECT en.precio_compra / {$cv} FROM entrada en
                      WHERE en.id_producto = pp.id_producto
                      ORDER BY en.fecha ASC, en.id ASC LIMIT 1) AS costo_primero,
                    (SELECT en.unidad_venta FROM entrada en
                      WHERE en.id_producto = pp.id_producto AND en.fecha <= pe.fecha
                      ORDER BY en.fecha DESC, en.id DESC LIMIT 1) AS unidad_costo
               FROM producto_pedido pp
               JOIN pedido pe ON pe.id = pp.id_pedido
               JOIN producto p ON p.id = pp.id_producto
              WHERE pe.stock_descontado = 1 AND pe.fecha BETWEEN ? AND ?
              ORDER BY pe.fecha, pp.id",
            [$ini, $fin]
        );

        // ---- mermas con el costo de su propio lote ----
        $mermas = $this->filas(
            "SELECT e.id_producto, p.nombre, DATE(m.fecha) AS dia, m.cantidad, m.motivo,
                    (e.precio_compra / " . $this->conv('e') . ") AS costo_unit
               FROM merma m
               JOIN entrada e ON e.id = m.id_entrada
               JOIN producto p ON p.id = e.id_producto
              WHERE m.fecha BETWEEN ? AND ?",
            [$ini, $fin]
        );

        $productos = [];
        $dias      = [];
        $pedidos   = [];
        $tot = ['ventas' => 0.0, 'costo' => 0.0, 'merma' => 0.0, 'merma_auto' => 0.0];

        $nuevoProd = fn($id, $nombre) => [
            'id' => $id, 'nombre' => $nombre, 'unidades' => 0.0, 'ventas' => 0.0, 'costo' => 0.0,
            'merma_unidades' => 0.0, 'merma_costo' => 0.0, 'sin_costo' => false, 'unidad_distinta' => false,
            'unidad' => '',
        ];

        foreach ($lineas as $l) {
            $id    = (int) $l['id_producto'];
            $cant  = (float) $l['cantidad'];
            $imp   = (float) $l['importe'];
            $costoU = $l['costo_antes'] !== null ? (float) $l['costo_antes']
                    : ($l['costo_primero'] !== null ? (float) $l['costo_primero'] : null);
            $costo = $costoU !== null ? $cant * $costoU : 0.0;

            $productos[$id] ??= $nuevoProd($id, $l['nombre']);
            $productos[$id]['unidades'] += $cant;
            $productos[$id]['ventas']   += $imp;
            $productos[$id]['costo']    += $costo;
            $productos[$id]['unidad']    = $l['unidad'] ?: $productos[$id]['unidad'];
            if ($costoU === null) { $productos[$id]['sin_costo'] = true; }
            if ($l['unidad_costo'] !== null && $l['unidad'] !== null && $l['unidad_costo'] !== $l['unidad']) {
                $productos[$id]['unidad_distinta'] = true;
            }

            $dia = $l['dia'];
            $dias[$dia] ??= ['dia' => $dia, 'ventas' => 0.0, 'costo' => 0.0, 'merma' => 0.0];
            $dias[$dia]['ventas'] += $imp;
            $dias[$dia]['costo']  += $costo;

            $pedidos[(int) $l['id_pedido']] = true;
            $tot['ventas'] += $imp;
            $tot['costo']  += $costo;
        }

        foreach ($mermas as $m) {
            $id    = (int) $m['id_producto'];
            $valor = (float) $m['cantidad'] * (float) $m['costo_unit'];

            $productos[$id] ??= $nuevoProd($id, $m['nombre']);
            $productos[$id]['merma_unidades'] += (float) $m['cantidad'];
            $productos[$id]['merma_costo']    += $valor;

            $dia = $m['dia'];
            $dias[$dia] ??= ['dia' => $dia, 'ventas' => 0.0, 'costo' => 0.0, 'merma' => 0.0];
            $dias[$dia]['merma'] += $valor;

            $tot['merma'] += $valor;
            if (stripos((string) $m['motivo'], 'SISTEMA:') === 0) { $tot['merma_auto'] += $valor; }
        }

        // ---- cierres por producto ----
        foreach ($productos as &$p) {
            $p['utilidad'] = $p['ventas'] - $p['costo'];
            $p['margen']   = $p['ventas'] > 0 ? round($p['utilidad'] / $p['ventas'] * 100, 1) : null;
            foreach (['unidades', 'ventas', 'costo', 'utilidad', 'merma_unidades', 'merma_costo'] as $k) {
                $p[$k] = round($p[$k], 2);
            }
        }
        unset($p);
        usort($productos, fn($a, $b) => [$b['utilidad'], $a['nombre']] <=> [$a['utilidad'], $b['nombre']]);

        // ---- un renglón por día del rango (también los días sin movimiento) ----
        $serie = [];
        $cursor = new \DateTime($desde);
        $limite = new \DateTime($hasta);
        while ($cursor <= $limite) {
            $k = $cursor->format('Y-m-d');
            $d = $dias[$k] ?? ['dia' => $k, 'ventas' => 0.0, 'costo' => 0.0, 'merma' => 0.0];
            $d['utilidad'] = round($d['ventas'] - $d['costo'] - $d['merma'], 2);
            foreach (['ventas', 'costo', 'merma'] as $c) { $d[$c] = round($d[$c], 2); }
            $serie[] = $d;
            $cursor->modify('+1 day');
        }

        $utilBruta = $tot['ventas'] - $tot['costo'];
        $totales = [
            'ventas'        => round($tot['ventas'], 2),
            'costo'         => round($tot['costo'], 2),
            'utilidad'      => round($utilBruta, 2),
            'margen'        => $tot['ventas'] > 0 ? round($utilBruta / $tot['ventas'] * 100, 1) : null,
            'merma'         => round($tot['merma'], 2),
            'merma_auto'    => round($tot['merma_auto'], 2),
            'utilidad_neta' => round($utilBruta - $tot['merma'], 2),
            'pedidos'       => count($pedidos),
            'ticket'        => count($pedidos) ? round($tot['ventas'] / count($pedidos), 2) : 0.0,
        ];

        return [
            'totales'   => $totales,
            'productos' => $productos,
            'dias'      => $serie,
            'avisos'    => [
                'sin_costo'       => array_values(array_map(fn($p) => $p['nombre'], array_filter($productos, fn($p) => $p['sin_costo']))),
                'unidad_distinta' => array_values(array_map(fn($p) => $p['nombre'], array_filter($productos, fn($p) => $p['unidad_distinta']))),
            ],
        ];
    }

    /* ============================ variación de costos ============================ */

    /** Todas las compras con su costo por unidad de venta y su variación contra la compra anterior */
    private function compras(?int $idProducto = null): array
    {
        $sql = "SELECT en.id, en.id_producto, p.nombre, en.fecha, en.cantidad_compra, en.unidad_compra,
                       en.unidad_venta, en.precio_compra, en.precio_sugerido,
                       " . ($this->tieneConversion() ? 'en.valor_conversion' : 'NULL') . " AS conversion,
                       (en.precio_compra / " . $this->conv('en') . ") AS costo_unit
                  FROM entrada en
                  JOIN producto p ON p.id = en.id_producto"
             . ($idProducto ? ' WHERE en.id_producto = ?' : '')
             . ' ORDER BY en.id_producto, en.fecha, en.id';

        $res   = [];
        $prev  = [];
        foreach ($this->filas($sql, $idProducto ? [$idProducto] : []) as $f) {
            $id    = (int) $f['id_producto'];
            $costo = round((float) $f['costo_unit'], 2);
            $ant   = $prev[$id] ?? null;

            $res[] = [
                'id'              => (int) $f['id'],
                'id_producto'     => $id,
                'nombre'          => $f['nombre'],
                'fecha'           => $f['fecha'],
                'cantidad_compra' => (float) $f['cantidad_compra'],
                'unidad_compra'   => $f['unidad_compra'],
                'unidad_venta'    => $f['unidad_venta'],
                'precio_compra'   => (float) $f['precio_compra'],
                'conversion'      => $f['conversion'] !== null ? (float) $f['conversion'] : null,
                'costo'           => $costo,
                'anterior'        => $ant,
                'var_abs'         => $ant !== null ? round($costo - $ant, 2) : null,
                'var_pct'         => ($ant !== null && $ant > 0) ? round(($costo - $ant) / $ant * 100, 1) : null,
                'precio_sugerido' => (float) $f['precio_sugerido'],
                'margen_sugerido' => ((float) $f['precio_sugerido'] > 0)
                                        ? round(((float) $f['precio_sugerido'] - $costo) / (float) $f['precio_sugerido'] * 100, 1) : null,
            ];
            $prev[$id] = $costo;
        }
        return $res;
    }

    /** Cambios de costo ocurridos dentro del periodo, los más recientes primero */
    public function variaciones(string $desde, string $hasta): array
    {
        $filas = array_filter(
            $this->compras(),
            fn($c) => $c['anterior'] !== null
                   && substr($c['fecha'], 0, 10) >= $desde && substr($c['fecha'], 0, 10) <= $hasta
        );
        usort($filas, fn($a, $b) => [$b['fecha'], $b['id']] <=> [$a['fecha'], $a['id']]);
        return array_values($filas);
    }

    /** Historial completo de compras de un producto (más reciente primero) */
    public function historialProducto(int $idProducto): array
    {
        return array_reverse($this->compras($idProducto));
    }

    /** Lista de productos para el selector del historial */
    public function listaProductos(): array
    {
        return $this->filas('SELECT id, nombre FROM producto ORDER BY nombre');
    }

    /** Corrige cuántas unidades de venta trae una unidad de compra en una entrada */
    public function guardarConversion(int $idEntrada, float $valor): bool
    {
        if (!$this->tieneConversion() || $idEntrada <= 0 || $valor <= 0 || $valor > 100000) {
            return false;
        }
        $this->db->query('UPDATE entrada SET valor_conversion = ? WHERE id = ?', [$valor, $idEntrada]);
        return true;
    }
}