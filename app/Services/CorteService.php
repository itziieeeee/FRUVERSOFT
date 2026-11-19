<?php
namespace App\Services;

/**
 * CORTE DE CAJA
 * ------------------------------------------------------------
 * Resume un día (hora de México) con lo que dice la tabla `pago`:
 *   - lo cobrado (ventas de contado, abonos de crédito, cobros del repartidor), por método de pago
 *   - lo reembolsado (devoluciones y correcciones)
 *   - el efectivo que debería haber en caja = fondo inicial + efectivo cobrado - efectivo reembolsado
 * Con el efectivo contado se calcula la diferencia.
 */
class CorteService
{
    private const ZONA = 'America/Mexico_City';

    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public static function hoy(): string
    {
        return (new \DateTime('now', new \DateTimeZone(self::ZONA)))->format('Y-m-d');
    }

    public static function fechaValida($valor): ?string
    {
        $valor = (string) $valor;
        $d = \DateTime::createFromFormat('Y-m-d', $valor);
        return ($d && $d->format('Y-m-d') === $valor) ? $valor : null;
    }

    /** Inicio y fin del día de México, en la hora del servidor (UTC), que es como se guardan las fechas */
    private function ventana(string $fecha): array
    {
        $tz  = new \DateTimeZone(self::ZONA);
        $utc = new \DateTimeZone('UTC');
        $ini = (new \DateTime($fecha . ' 00:00:00', $tz));
        $fin = (clone $ini)->modify('+1 day');
        return [$ini->setTimezone($utc)->format('Y-m-d H:i:s'), $fin->setTimezone($utc)->format('Y-m-d H:i:s')];
    }

    private function aLocal(string $fecha): string
    {
        return (new \DateTime($fecha, new \DateTimeZone('UTC')))->setTimezone(new \DateTimeZone(self::ZONA))->format('H:i');
    }

    public function resumen(string $fecha): array
    {
        [$a, $b] = $this->ventana($fecha);

        // ---- ventas del día (no canceladas) ----
        $v = $this->db->query(
            "SELECT COUNT(*) AS n, COALESCE(SUM(total), 0) AS total
               FROM pedido WHERE fecha >= ? AND fecha < ? AND estado_actual <> 'Pedido cancelado'",
            [$a, $b]
        )->getRowArray();

        // ---- de esas ventas, cuánto se cobró hasta el cierre del día ----
        $cobradoVentas = (float) $this->db->query(
            "SELECT COALESCE(SUM(pg.monto), 0) AS t
               FROM pago pg
               JOIN pedido p ON p.id = pg.id_pedido
              WHERE p.fecha >= ? AND p.fecha < ? AND p.estado_actual <> 'Pedido cancelado' AND pg.fecha < ?",
            [$a, $b, $b]
        )->getRow()->t;

        // ---- movimientos de dinero del día ----
        $movs = $this->db->query(
            "SELECT pg.id, pg.id_pedido, pg.monto, pg.metodo, pg.origen, pg.usuario, pg.fecha, pg.nota,
                    CONCAT_WS(' ', c.nombre, c.apellido_paterno) AS cliente
               FROM pago pg
               LEFT JOIN pedido p ON p.id = pg.id_pedido
               LEFT JOIN clientes c ON c.id_cliente = p.id_cliente
              WHERE pg.fecha >= ? AND pg.fecha < ?
              ORDER BY pg.fecha, pg.id",
            [$a, $b]
        )->getResultArray();

        $metodos = [];
        foreach (PagoService::METODOS as $m) {
            $metodos[$m] = ['cobrado' => 0.0, 'reembolsado' => 0.0, 'neto' => 0.0];
        }
        $origenes = [];
        foreach ($movs as &$mov) {
            $monto  = (float) $mov['monto'];
            $metodo = isset($metodos[$mov['metodo']]) ? $mov['metodo'] : 'efectivo';
            if ($monto >= 0) { $metodos[$metodo]['cobrado'] += $monto; }
            else             { $metodos[$metodo]['reembolsado'] += -$monto; }
            $metodos[$metodo]['neto'] += $monto;
            $origenes[$mov['origen']] = ($origenes[$mov['origen']] ?? 0.0) + $monto;

            $mov['hora']  = $this->aLocal($mov['fecha']);
            $mov['folio'] = AuditoriaService::folio($mov['id_pedido']);
            $mov['monto'] = $monto;
        }
        unset($mov);

        $cobrado     = array_sum(array_column($metodos, 'cobrado'));
        $reembolsado = array_sum(array_column($metodos, 'reembolsado'));

        // ---- devoluciones del día ----
        $d = $this->db->query(
            'SELECT COUNT(*) AS n, COALESCE(SUM(total), 0) AS total, COALESCE(SUM(reembolso), 0) AS reembolso
               FROM devolucion WHERE fecha >= ? AND fecha < ?',
            [$a, $b]
        )->getRowArray();

        // ---- lo que sigue por cobrar en total (informativo) ----
        $porCobrar = (float) $this->db->query(
            "SELECT COALESCE(SUM(total - monto_pagado), 0) AS t FROM pedido
              WHERE tipo_pago = 'credito' AND estado_actual <> 'Pedido cancelado' AND (total - monto_pagado) > 0.009"
        )->getRow()->t;

        $ventasTotal = round((float) $v['total'], 2);

        return [
            'fecha'          => $fecha,
            'ventas'         => ['n' => (int) $v['n'], 'total' => $ventasTotal],
            'ventas_cobradas'=> round($cobradoVentas, 2),
            'ventas_credito' => round(max(0, $ventasTotal - $cobradoVentas), 2),
            'cobrado'        => round($cobrado, 2),
            'reembolsado'    => round($reembolsado, 2),
            'neto'           => round($cobrado - $reembolsado, 2),
            'metodos'        => array_map(static fn ($m) => array_map(static fn ($x) => round($x, 2), $m), $metodos),
            'origenes'       => array_map(static fn ($x) => round($x, 2), $origenes),
            'devoluciones'   => ['n' => (int) $d['n'], 'total' => round((float) $d['total'], 2), 'reembolso' => round((float) $d['reembolso'], 2)],
            'por_cobrar'     => round($porCobrar, 2),
            'movimientos'    => $movs,
            'efectivo_neto'  => round($metodos['efectivo']['neto'], 2),
        ];
    }

    /** Guarda el corte con lo que había en caja. El resumen se vuelve a calcular aquí, no se confía en el navegador. */
    public function guardar(string $fecha, float $fondo, float $contado, string $notas): array
    {
        $resumen   = $this->resumen($fecha);
        $esperado  = round($fondo + $resumen['efectivo_neto'], 2);
        $diferencia = round($contado - $esperado, 2);

        $idUsuario = null;
        $usuario   = 'Sistema';
        if (!is_cli()) {
            $s         = session();
            $idUsuario = $s->get('id_usuario') !== null ? (int) $s->get('id_usuario') : null;
            $usuario   = (string) ($s->get('nombre') ?: 'Sistema');
        }

        // Los movimientos ya están en la tabla `pago`; al corte solo se guarda el resumen
        $copia = $resumen;
        unset($copia['movimientos']);

        $this->db->table('corte_caja')->insert([
            'fecha_corte'       => $fecha,
            'id_usuario'        => $idUsuario,
            'usuario'           => mb_substr($usuario, 0, 120),
            'fondo_inicial'     => round($fondo, 2),
            'efectivo_esperado' => $esperado,
            'efectivo_contado'  => round($contado, 2),
            'diferencia'        => $diferencia,
            'notas'             => $notas !== '' ? mb_substr($notas, 0, 300) : null,
            'resumen'           => json_encode($copia, JSON_UNESCAPED_UNICODE),
            'created_at'        => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();

        AuditoriaService::registrar(
            'corte.guardar', 'corte', $id,
            'Corte del ' . date('d/m/Y', strtotime($fecha)) . ': esperado $' . number_format($esperado, 2) . ', contado $' . number_format($contado, 2)
                . ', diferencia ' . ($diferencia >= 0 ? '+' : '-') . '$' . number_format(abs($diferencia), 2),
            ['fondo' => $fondo, 'esperado' => $esperado, 'contado' => $contado, 'diferencia' => $diferencia]
        );

        return ['id' => $id, 'esperado' => $esperado, 'diferencia' => $diferencia];
    }

    /** Últimos cortes guardados */
    public function historial(int $limite = 15): array
    {
        $filas = $this->db->query('SELECT * FROM corte_caja ORDER BY id DESC LIMIT ' . (int) $limite)->getResultArray();
        foreach ($filas as &$f) {
            $f['creado_local'] = (new \DateTime($f['created_at'], new \DateTimeZone('UTC')))
                ->setTimezone(new \DateTimeZone(self::ZONA))->format('d/m/Y H:i');
        }
        unset($f);
        return $filas;
    }

    /** Último corte guardado de esa fecha (si existe) */
    public function ultimoDe(string $fecha): ?array
    {
        $fila = $this->db->query('SELECT * FROM corte_caja WHERE fecha_corte = ? ORDER BY id DESC LIMIT 1', [$fecha])->getRowArray();
        return $fila ?: null;
    }
}
