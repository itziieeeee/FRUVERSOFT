<?php

namespace App\Services;

/**
 * Resumen del inicio del administrador: ventas del día, lo que falta por cobrar, productos críticos,
 * entregas en curso y diferencia del último corte.
 *
 * Reutiliza los servicios que ya calculan cada cosa. Cada bloque se arma por separado: si uno falla
 * (por ejemplo, una tabla que todavía no existe) devuelve null y el resto del resumen sigue funcionando.
 */
class DashboardService
{
    private const ZONA = 'America/Mexico_City';
    private const ESTADOS_LISTOS = ['Pedido confirmado', 'Pedido a crédito', 'Pedido pagado'];

    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function resumen(): array
    {
        return [
            'generado'   => (new \DateTime('now', new \DateTimeZone(self::ZONA)))->format('h:i:s A'),
            'ventas'     => $this->seguro(fn () => $this->ventas()),
            'por_cobrar' => $this->seguro(fn () => $this->porCobrar()),
            'criticos'   => $this->seguro(fn () => $this->criticos()),
            'entregas'   => $this->seguro(fn () => $this->entregas()),
            'corte'      => $this->seguro(fn () => $this->corte()),
        ];
    }

    private function seguro(callable $bloque)
    {
        try {
            return $bloque();
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard: {msg}', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /* ============================ bloques ============================ */

    private function ventas(): array
    {
        $r = (new CorteService($this->db))->resumen(CorteService::hoy());

        return [
            'n'       => $r['ventas']['n'],
            'total'   => $r['ventas']['total'],
            'cobrado' => $r['cobrado'],
        ];
    }

    private function porCobrar(): array
    {
        $r = (new CobranzaService($this->db))->listar()['resumen'];

        return [
            'total'    => $r['por_cobrar'],
            'vencido'  => $r['vencido'],
            'clientes' => $r['clientes'],
            'vencidos' => $r['vencidos'],
        ];
    }

    private function criticos(): array
    {
        $r = (new AlertasStockService($this->db))->resumenRapido(3);

        return [
            'criticos' => $r['criticos'],
            'atencion' => $r['atencion'],
            'urgentes' => array_map(static fn (array $p): string => (string) $p['nombre'], $r['urgentes']),
        ];
    }

    private function entregas(): array
    {
        [$desde, $hasta] = $this->ventana();

        $enRuta = (int) $this->db->table('salida')->where('estado', 'iniciada')->countAllResults();

        $pendientes = (int) $this->db->query(
            "SELECT COUNT(*) AS n FROM salida_pedido sp JOIN salida s ON s.id = sp.id_salida
              WHERE s.estado = 'iniciada' AND sp.estado IN ('pendiente', 'en_ruta')"
        )->getRow()->n;

        $hoy = $this->db->query(
            "SELECT SUM(estado = 'entregado') AS entregadas, SUM(estado = 'fallido') AS fallidas
               FROM salida_pedido WHERE entregado_at >= ? AND entregado_at < ?",
            [$desde, $hasta]
        )->getRow();

        // Pedidos a domicilio ya listos que el repartidor todavía no sacó
        $listos = (int) $this->db->query(
            "SELECT COUNT(*) AS n FROM pedido p
              WHERE p.tipo_entrega = 'domicilio'
                AND p.estado_actual IN ('" . implode("','", self::ESTADOS_LISTOS) . "')
                AND NOT EXISTS (SELECT 1 FROM salida_pedido sp JOIN salida s ON s.id = sp.id_salida
                                 WHERE sp.id_pedido = p.id AND s.estado IN ('borrador', 'iniciada'))"
        )->getRow()->n;

        return [
            'en_ruta'    => $enRuta,
            'pendientes' => $pendientes,
            'entregadas' => (int) ($hoy->entregadas ?? 0),
            'fallidas'   => (int) ($hoy->fallidas ?? 0),
            'sin_salir'  => $listos,
        ];
    }

    private function corte(): array
    {
        $servicio  = new CorteService($this->db);
        $historial = $servicio->historial(1);
        $ultimo    = $historial[0] ?? null;

        return [
            'ultimo' => $ultimo ? [
                'fecha'      => date('d/m/Y', strtotime($ultimo['fecha_corte'])),
                'diferencia' => (float) $ultimo['diferencia'],
                'esperado'   => (float) $ultimo['efectivo_esperado'],
                'contado'    => (float) $ultimo['efectivo_contado'],
                'usuario'    => (string) ($ultimo['usuario'] ?? ''),
            ] : null,
            'hoy_guardado' => $servicio->ultimoDe(CorteService::hoy()) !== null,
        ];
    }

    /** Inicio y fin del día de hoy en México, en la zona en que se guardan las fechas */
    private function ventana(): array
    {
        $mx  = new \DateTimeZone(self::ZONA);
        $bd  = new \DateTimeZone(date_default_timezone_get());
        $ini = new \DateTime('today', $mx);
        $fin = (clone $ini)->modify('+1 day');

        return [
            $ini->setTimezone($bd)->format('Y-m-d H:i:s'),
            $fin->setTimezone($bd)->format('Y-m-d H:i:s'),
        ];
    }
}
