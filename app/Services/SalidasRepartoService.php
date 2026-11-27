<?php

namespace App\Services;

use App\Models\StatusModel;
use RuntimeException;

/**
 * Salidas de reparto.
 *
 * Flujo:
 *  1. El admin asigna pedidos a un repartidor al vender (pedido.id_repartidor).
 *  2. El repartidor ve en su app la vista previa de su recorrido y pulsa "Iniciar recorrido".
 *  3. Ahí se crea la salida (máx. 6 pedidos), se guarda el orden óptimo y los pedidos pasan a "Pedido en tránsito".
 *  4. Cada entrega se marca desde la app; al terminar la última, la salida se finaliza sola.
 *  El admin solo monitorea (listarAdmin / pendientesPorRepartidor).
 */
class SalidasRepartoService
{
    private const MAX_PEDIDOS_SALIDA = 6;
    private const ESTADOS_LISTOS = ['Pedido confirmado', 'Pedido a crédito', 'Pedido pagado'];

    private $db;
    private RutaRepartidorService $rutas;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->rutas = new RutaRepartidorService();
    }

    /* ===================== lo que usa el repartidor ===================== */

    /**
     * Vista previa del recorrido que haría el repartidor si saliera ahora.
     * No guarda nada.
     */
    public function vistaPrevia(int $idRepartidor): array
    {
        $this->exigirRepartidor($idRepartidor);

        $listos = $this->pedidosDisponibles($idRepartidor, null);
        if (!$listos) {
            return ['listos' => 0, 'en_este_recorrido' => 0, 'para_despues' => 0, 'paradas' => []];
        }

        $lote = array_slice($listos, 0, self::MAX_PEDIDOS_SALIDA);
        $ids  = array_map(static fn (array $p): int => (int) $p['id'], $lote);
        $plan = $this->rutas->planSalida($idRepartidor, null, $ids);

        return [
            'listos'            => count($listos),
            'en_este_recorrido' => count($lote),
            'para_despues'      => count($listos) - count($lote),
            'km_total'          => $plan['km_total'],
            'regreso_km'        => $plan['regreso_km'],
            'combustible'       => $plan['combustible'],
            'fuente_distancia'  => $plan['fuente_distancia'],
            'tiempo'            => $plan['tiempo'],
            'comparativo'       => $plan['comparativo'],
            'paradas'           => $plan['paradas'],
            'sin_ubicacion'     => $plan['sin_ubicacion'],
            'advertencias'      => $plan['advertencias'],
            'google_maps_url'   => $plan['google_maps_url'],
        ];
    }

    /**
     * El repartidor inicia su recorrido: crea la salida con sus pedidos listos (máx. 6) y la arranca.
     */
    public function iniciarRecorrido(int $idRepartidor): array
    {
        $this->exigirRepartidor($idRepartidor);

        if ($this->activaDeRepartidor($idRepartidor)) {
            throw new RuntimeException('Ya tienes un recorrido en curso. Termínalo antes de iniciar otro.');
        }

        // Un borrador viejo (de un intento fallido) no debe apartar pedidos.
        $this->db->table('salida')
            ->where('id_repartidor', $idRepartidor)
            ->where('estado', 'borrador')
            ->update(['estado' => 'cancelada']);

        $salida = $this->crear($idRepartidor, null);

        try {
            return $this->iniciar((int) $salida['id']);
        } catch (\Throwable $e) {
            $this->db->table('salida')->where('id', $salida['id'])->update(['estado' => 'cancelada']);
            throw $e;
        }
    }

    /** Salida en curso del repartidor, con sus paradas ordenadas. */
    public function activaDeRepartidor(int $idRepartidor): ?array
    {
        $salida = $this->db->table('salida')
            ->where('id_repartidor', $idRepartidor)
            ->where('estado', 'iniciada')
            ->orderBy('id', 'DESC')
            ->get()->getRowArray();

        return $salida ? $this->detalle((int) $salida['id']) : null;
    }

    /** Marca una parada como entregada y cierra la salida cuando no quedan pendientes. */
    /**
     * @param array $extra evidencia y cobro: foto, firma (nombres de archivo ya guardados), cobro (monto), metodo
     */
    public function entregar(int $idRepartidor, int $idPedido, ?float $lat = null, ?float $lng = null, ?string $nota = null, array $extra = []): array
    {
        $salida = $this->db->table('salida s')
            ->select('s.*')
            ->join('salida_pedido sp', 'sp.id_salida = s.id')
            ->where('s.id_repartidor', $idRepartidor)
            ->where('s.estado', 'iniciada')
            ->where('sp.id_pedido', $idPedido)
            ->whereIn('sp.estado', ['pendiente', 'en_ruta'])
            ->get()->getRowArray();

        if (!$salida) {
            throw new RuntimeException('El pedido no pertenece a una salida activa de este repartidor.');
        }

        $ahora  = date('Y-m-d H:i:s');
        $status = new StatusModel();

        $this->db->transBegin();
        try {
            $cobro  = round((float) ($extra['cobro'] ?? 0), 2);
            $metodo = PagoService::metodoValido($extra['metodo'] ?? 'efectivo');

            $datosEntrega = [
                'estado'       => 'entregado',
                'entregado_at' => $ahora,
                'entrega_lat'  => $lat,
                'entrega_lng'  => $lng,
                'nota'         => $nota,
            ];
            if ($this->db->fieldExists('foto_entrega', 'salida_pedido')) {
                $datosEntrega['foto_entrega']  = $extra['foto'] ?? null;
                $datosEntrega['firma_entrega'] = $extra['firma'] ?? null;
                $datosEntrega['monto_cobrado'] = $cobro > 0 ? $cobro : null;
                $datosEntrega['metodo_cobro']  = $cobro > 0 ? $metodo : null;
            }

            $this->db->table('salida_pedido')
                ->where('id_salida', $salida['id'])
                ->where('id_pedido', $idPedido)
                ->update($datosEntrega);

            // El dinero que el repartidor cobró entra al pedido y a la caja. Si no cuadra (por ejemplo, más que el saldo), se cancela toda la entrega.
            if ($cobro > 0) {
                (new PagoService($this->db))->cobrar($idPedido, $cobro, $metodo, 'entrega', 'Cobrado por el repartidor al entregar');
            }

            if (!$status->actualizarEstado($idPedido, 'Venta confirmada')) {
                throw new RuntimeException($status->ultimoError ?: 'No se pudo confirmar la entrega del pedido.');
            }
            $this->db->table('pedido')->where('id', $idPedido)->update(['entregado_at' => $ahora]);

            $pendientes = $this->db->table('salida_pedido')
                ->where('id_salida', $salida['id'])
                ->whereIn('estado', ['pendiente', 'en_ruta'])
                ->countAllResults();
            if ($pendientes === 0) {
                $this->db->table('salida')->where('id', $salida['id'])->update([
                    'estado'        => 'finalizada',
                    'finalizada_at' => $ahora,
                ]);
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        AuditoriaService::registrar(
            'entrega.registrar', 'pedido', $idPedido,
            AuditoriaService::folio($idPedido) . ' entregado'
                . (!empty($extra['foto']) ? ' con foto' : '') . (!empty($extra['firma']) ? ' con firma' : '')
                . ($cobro > 0 ? ', cobró $' . number_format($cobro, 2) . ' (' . $metodo . ')' : ''),
            ['repartidor' => $idRepartidor, 'cobro' => $cobro, 'metodo' => $metodo, 'foto' => !empty($extra['foto']), 'firma' => !empty($extra['firma']), 'nota' => $nota]
        );

        return $this->detalle((int) $salida['id']);
    }

    /**
     * Motivos por los que una entrega puede no realizarse.
     * 'incidencia' es el tipo con el que se avisa al administrador.
     */
    public const MOTIVOS_FALLO = [
        'cliente_ausente'      => ['texto' => 'Cliente ausente',              'incidencia' => 'cliente_no_responde'],
        'direccion_incorrecta' => ['texto' => 'Dirección incorrecta',         'incidencia' => 'direccion_incorrecta'],
        'cliente_rechazo'      => ['texto' => 'El cliente rechazó el pedido', 'incidencia' => 'cliente_rechazo'],
        'otro'                 => ['texto' => 'Otro motivo',                  'incidencia' => 'entrega_fallida'],
    ];

    /**
     * El repartidor no pudo entregar una parada. La parada queda como "fallido", el pedido regresa a
     * "listo" (con su stock apartado) para reintentar o que el administrador decida, y se avisa al
     * administrador con una incidencia que lleva la ubicación del repartidor.
     * Si era la última parada pendiente, la salida se cierra sola.
     */
    public function fallar(int $idRepartidor, int $idPedido, string $motivo, ?string $nota = null, ?float $lat = null, ?float $lng = null): array
    {
        if (!isset(self::MOTIVOS_FALLO[$motivo])) {
            throw new RuntimeException('Elige el motivo por el que no se pudo entregar.');
        }
        $nota = mb_substr(trim((string) $nota), 0, 300);
        if ($motivo === 'otro' && mb_strlen($nota) < 5) {
            throw new RuntimeException('Explica en la nota por qué no se pudo entregar.');
        }

        $salida = $this->db->table('salida s')
            ->select('s.*')
            ->join('salida_pedido sp', 'sp.id_salida = s.id')
            ->where('s.id_repartidor', $idRepartidor)
            ->where('s.estado', 'iniciada')
            ->where('sp.id_pedido', $idPedido)
            ->whereIn('sp.estado', ['pendiente', 'en_ruta'])
            ->get()->getRowArray();
        if (!$salida) {
            throw new RuntimeException('El pedido no pertenece a una salida activa de este repartidor.');
        }

        $pedido = $this->db->table('pedido')->select('total, monto_pagado, tipo_pago')->where('id', $idPedido)->get()->getRowArray();
        if (!$pedido) {
            throw new RuntimeException('El pedido no existe.');
        }
        $total  = (float) $pedido['total'];
        $pagado = (float) $pedido['monto_pagado'];
        // Vuelve al estado "listo" que le corresponde: el stock sigue apartado para el reintento
        $estadoListo = ($total > 0 && $pagado >= $total - 0.001) ? 'Pedido pagado'
            : (($pedido['tipo_pago'] ?? '') === 'credito' ? 'Pedido a crédito' : 'Pedido confirmado');

        $info  = self::MOTIVOS_FALLO[$motivo];
        $texto = $info['texto'] . ($nota !== '' ? ': ' . $nota : '');
        $ahora = date('Y-m-d H:i:s');
        $status = new StatusModel();

        $this->db->transBegin();
        try {
            // En una parada fallida, entregado_at guarda la hora en que se cerró
            $this->db->table('salida_pedido')
                ->where('id_salida', $salida['id'])
                ->where('id_pedido', $idPedido)
                ->update([
                    'estado'       => 'fallido',
                    'entregado_at' => $ahora,
                    'entrega_lat'  => $lat,
                    'entrega_lng'  => $lng,
                    'nota'         => mb_substr($texto, 0, 500),
                ]);

            if (!$status->actualizarEstado($idPedido, $estadoListo)) {
                throw new RuntimeException($status->ultimoError ?: 'No se pudo regresar el pedido a su estado anterior.');
            }

            $this->db->table('incidencia')->insert([
                'id_pedido'     => $idPedido,
                'id_repartidor' => $idRepartidor,
                'tipo'          => $info['incidencia'],
                'nota'          => mb_substr('Entrega no realizada. ' . $texto, 0, 500),
                'lat'           => $lat,
                'lng'           => $lng,
            ]);

            $pendientes = $this->db->table('salida_pedido')
                ->where('id_salida', $salida['id'])
                ->whereIn('estado', ['pendiente', 'en_ruta'])
                ->countAllResults();
            if ($pendientes === 0) {
                $this->db->table('salida')->where('id', $salida['id'])->update([
                    'estado'        => 'finalizada',
                    'finalizada_at' => $ahora,
                ]);
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        AuditoriaService::registrar(
            'entrega.fallida', 'pedido', $idPedido,
            AuditoriaService::folio($idPedido) . ' no se pudo entregar: ' . $texto,
            ['repartidor' => $idRepartidor, 'motivo' => $motivo, 'nota' => $nota, 'estado_nuevo' => $estadoListo]
        );

        return $this->detalle((int) $salida['id']);
    }

    /**
     * Resumen del día (hora de México) del repartidor: entregas, no entregadas, cobrado por método,
     * km de las rutas que inició y la lista de paradas que cerró hoy (para la tabla).
     */
    public function resumenDelDia(int $idRepartidor): array
    {
        $this->exigirRepartidor($idRepartidor);
        [$desde, $hasta] = $this->ventanaHoy();

        $columnas = $this->db->fieldExists('foto_entrega', 'salida_pedido')
            ? 'sp.foto_entrega, sp.monto_cobrado, sp.metodo_cobro'
            : 'NULL AS foto_entrega, NULL AS monto_cobrado, NULL AS metodo_cobro';

        $filas = $this->db->query(
            "SELECT sp.id_pedido, sp.orden, sp.estado, sp.direccion, sp.lat, sp.lng, sp.entregado_at, sp.nota, {$columnas},
                    p.total AS pedido_total, p.monto_pagado AS pedido_pagado, p.tipo_pago, p.prioritario,
                    c.nombre AS cliente, c.apellido_paterno, c.tel
               FROM salida_pedido sp
               JOIN salida s  ON s.id = sp.id_salida
               JOIN pedido p  ON p.id = sp.id_pedido
               JOIN clientes c ON c.id_cliente = p.id_cliente
              WHERE s.id_repartidor = ?
                AND sp.estado IN ('entregado', 'fallido')
                AND sp.entregado_at >= ? AND sp.entregado_at < ?
              ORDER BY sp.entregado_at DESC, sp.id DESC",
            [$idRepartidor, $desde, $hasta]
        )->getResultArray();

        $cobrado = ['total' => 0.0, 'efectivo' => 0.0, 'transferencia' => 0.0, 'tarjeta' => 0.0];
        $entregas = 0;
        $fallidas = 0;
        foreach ($filas as &$f) {
            if ($f['estado'] === 'entregado') {
                $entregas++;
                $monto = (float) $f['monto_cobrado'];
                if ($monto > 0) {
                    $cobrado[PagoService::metodoValido($f['metodo_cobro'])] += $monto;
                    $cobrado['total'] += $monto;
                }
            } else {
                $fallidas++;
            }
            // El nombre del archivo es interno: la pantalla solo necesita saber si hay foto
            $f['tiene_foto'] = !empty($f['foto_entrega']);
            unset($f['foto_entrega']);
        }
        unset($f);

        $km = (float) $this->db->query(
            "SELECT COALESCE(SUM(km_total), 0) AS km FROM salida
              WHERE id_repartidor = ? AND estado IN ('iniciada', 'finalizada')
                AND iniciada_at >= ? AND iniciada_at < ?",
            [$idRepartidor, $desde, $hasta]
        )->getRow()->km;

        return [
            'entregas' => $entregas,
            'fallidas' => $fallidas,
            'cobrado'  => array_map(static fn (float $n): float => round($n, 2), $cobrado),
            'km'       => round($km, 1),
            'hechas'   => $filas,
        ];
    }

    /** Inicio y fin del día de México, en la zona horaria en que se guardan las fechas. */
    private function ventanaHoy(): array
    {
        $mx  = new \DateTimeZone('America/Mexico_City');
        $bd  = new \DateTimeZone(date_default_timezone_get());
        $ini = new \DateTime('today', $mx);
        $fin = (clone $ini)->modify('+1 day');

        return [
            $ini->setTimezone($bd)->format('Y-m-d H:i:s'),
            $fin->setTimezone($bd)->format('Y-m-d H:i:s'),
        ];
    }

    /* ===================== lo que usa el administrador (monitoreo) ===================== */

    /** Salidas en curso y recientes, con sus paradas y la última ubicación del repartidor. */
    public function listarAdmin(): array
    {
        $salidas = $this->db->table('salida s')
            ->select("s.id, s.id_repartidor, s.estado, s.km_total, s.fuente_distancia, s.iniciada_at, s.finalizada_at, s.local_lat, s.local_lng, " . ($this->db->fieldExists('optimizacion', 'salida') ? 's.optimizacion, ' : '') . "CONCAT_WS(' ', r.nombre, r.ap_p, r.ap_m) AS repartidor, r.ultima_lat, r.ultima_lng, r.ultima_actualizacion", false)
            ->join('repartidor r', 'r.id = s.id_repartidor', 'left')
            ->whereIn('s.estado', ['iniciada', 'finalizada'])
            ->orderBy('s.id', 'DESC')
            ->limit(30)
            ->get()->getResultArray();

        if (!$salidas) {
            return [];
        }

        $ids = array_map(static fn (array $s): int => (int) $s['id'], $salidas);
        $paradas = $this->db->table('salida_pedido sp')
            ->select("sp.id_salida, sp.id_pedido, sp.orden, sp.estado, sp.direccion, sp.lat, sp.lng, sp.km_desde_anterior, sp.entregado_at, p.prioritario, CONCAT_WS(' ', c.nombre, c.apellido_paterno) AS cliente", false)
            ->join('pedido p', 'p.id = sp.id_pedido')
            ->join('clientes c', 'c.id_cliente = p.id_cliente', 'left')
            ->whereIn('sp.id_salida', $ids)
            ->orderBy('sp.id_salida', 'ASC')
            ->orderBy('sp.orden', 'ASC')
            ->get()->getResultArray();

        $porSalida = [];
        foreach ($paradas as $parada) {
            $porSalida[(int) $parada['id_salida']][] = $parada;
        }

        foreach ($salidas as &$s) {
            $s['paradas']       = $porSalida[(int) $s['id']] ?? [];
            $s['total_paradas'] = count($s['paradas']);
            $s['entregadas']    = count(array_filter($s['paradas'], static fn (array $p): bool => $p['estado'] === 'entregado'));

            $minutos = null;
            if (!empty($s['ultima_actualizacion']) && $s['ultima_lat'] !== null && $s['ultima_lng'] !== null) {
                $marca   = strtotime((string) $s['ultima_actualizacion']);
                $minutos = $marca ? max(0, (int) round((time() - $marca) / 60)) : null;
            }
            $s['ultima_minutos']  = $minutos;
            $s['google_maps_url'] = $this->googleMapsUrl($s);

            // De la optimización solo viaja lo que muestra el monitor (no la lista completa de tramos)
            $opt = !empty($s['optimizacion']) ? json_decode((string) $s['optimizacion'], true) : null;
            $s['comparativo'] = $opt['comparativo'] ?? null;
            $s['tiempo']      = isset($opt['tiempo']) ? array_intersect_key($opt['tiempo'], array_flip(['salida', 'regreso', 'total_min', 'traslado_min', 'transito_min'])) : null;
            unset($s['optimizacion'], $s['ultima_actualizacion']);
        }
        unset($s);

        // Primero las que van en ruta, luego las terminadas.
        usort($salidas, static function (array $a, array $b): int {
            $ea = $a['estado'] === 'iniciada' ? 0 : 1;
            $eb = $b['estado'] === 'iniciada' ? 0 : 1;
            return [$ea, -$a['id']] <=> [$eb, -$b['id']];
        });

        return $salidas;
    }

    /** Repartidores con pedidos listos que todavía no han iniciado su recorrido. */
    public function pendientesPorRepartidor(): array
    {
        return $this->db->table('pedido p')
            ->select("r.id, CONCAT_WS(' ', r.nombre, r.ap_p) AS repartidor, COUNT(p.id) AS listos", false)
            ->join('repartidor r', 'r.id = p.id_repartidor')
            ->where('p.tipo_entrega', 'domicilio')
            ->whereIn('p.estado_actual', self::ESTADOS_LISTOS)
            ->where($this->sinSalidaActiva(), null, false)
            ->groupBy('r.id, r.nombre, r.ap_p')
            ->orderBy('listos', 'DESC')
            ->get()->getResultArray();
    }

    /* ===================== seguimiento en vivo y aviso "ya está cerca" ===================== */

    /** Radio (km) a partir del cual se considera que el repartidor ya está cerca del cliente. */
    public const RADIO_CERCANIA_KM = 0.5;

    /** Si la última ubicación es más vieja que esto (minutos), no se confía en ella para avisar. */
    public const MAX_MINUTOS_UBICACION = 15;

    /** Distancia en línea recta entre dos puntos, en km (Haversine). */
    private function distanciaKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r  = 6371.0;
        $a1 = deg2rad($lat1);
        $a2 = deg2rad($lat2);
        $dLat = $a2 - $a1;
        $dLng = deg2rad($lng2 - $lng1);
        $h = sin($dLat / 2) ** 2 + cos($a1) * cos($a2) * sin($dLng / 2) ** 2;
        return 2 * $r * asin(min(1.0, sqrt($h)));
    }

    /**
     * Datos para el mapa en vivo: salidas en curso con la posición del repartidor y sus paradas,
     * más las alertas de cercanía (paradas a menos de RADIO_CERCANIA_KM que aún no se han avisado).
     */
    public function seguimiento(): array
    {
        helper('whatsapp');

        $salidas = array_values(array_filter(
            $this->listarAdmin(),
            static fn (array $s): bool => $s['estado'] === 'iniciada'
        ));

        // Datos de contacto y aviso de cada pedido de las paradas
        $idsPedido = [];
        foreach ($salidas as $s) {
            foreach ($s['paradas'] as $p) {
                $idsPedido[] = (int) $p['id_pedido'];
            }
        }
        $contacto = [];
        if ($idsPedido) {
            $filas = $this->db->table('pedido p')
                ->select("p.id, p.aviso_cercania, c.tel, c.nombre", false)
                ->join('clientes c', 'c.id_cliente = p.id_cliente', 'left')
                ->whereIn('p.id', $idsPedido)
                ->get()->getResultArray();
            foreach ($filas as $f) {
                $contacto[(int) $f['id']] = $f;
            }
        }

        $alertas = [];
        foreach ($salidas as &$s) {
            $tienePos = $s['ultima_lat'] !== null && $s['ultima_lng'] !== null;
            $vigente  = $tienePos && $s['ultima_minutos'] !== null && $s['ultima_minutos'] <= self::MAX_MINUTOS_UBICACION;
            $s['posicion_vigente'] = $vigente;

            foreach ($s['paradas'] as &$p) {
                $c = $contacto[(int) $p['id_pedido']] ?? null;
                $p['avisado']  = $c ? (int) $c['aviso_cercania'] === 1 : false;
                $p['telefono'] = $c['tel'] ?? null;
                $p['distancia_km'] = null;

                if ($vigente && $p['lat'] !== null && $p['lng'] !== null && in_array($p['estado'], ['pendiente', 'en_ruta'], true)) {
                    $p['distancia_km'] = round($this->distanciaKm(
                        (float) $s['ultima_lat'], (float) $s['ultima_lng'], (float) $p['lat'], (float) $p['lng']
                    ), 2);

                    if ($p['distancia_km'] <= self::RADIO_CERCANIA_KM && !$p['avisado']) {
                        $nombre = trim((string) ($c['nombre'] ?? ''));
                        $alertas[] = [
                            'id_pedido'    => (int) $p['id_pedido'],
                            'cliente'      => $p['cliente'],
                            'repartidor'   => $s['repartidor'],
                            'distancia_m'  => (int) round($p['distancia_km'] * 1000),
                            'tiene_tel'    => !empty($p['telefono']),
                            'whatsapp_url' => !empty($p['telefono'])
                                ? 'https://wa.me/' . formato_telefono_wa($p['telefono'])
                                    . '?text=' . rawurlencode($this->mensajeCercania($nombre, (int) $p['id_pedido']))
                                : null,
                        ];
                    }
                }
            }
            unset($p);
        }
        unset($s);

        return ['salidas' => $salidas, 'alertas' => $alertas, 'radio_m' => (int) (self::RADIO_CERCANIA_KM * 1000)];
    }

    /** Texto del aviso. Sin emojis a propósito (WhatsApp para Windows los rompe en links wa.me). */
    private function mensajeCercania(string $nombre, int $idPedido): string
    {
        $folio  = 'PED-' . str_pad((string) $idPedido, 5, '0', STR_PAD_LEFT);
        $saludo = $nombre !== '' ? "Hola {$nombre}," : 'Hola,';
        return "{$saludo} tu pedido *{$folio}* de FRUVER esta cerca. "
             . "Nuestro repartidor llegara en unos minutos, por favor estate atento. Gracias por tu compra.";
    }

    /** Marca que ya se avisó a ese cliente (evita repetir la alerta). */
    public function marcarAvisoCercania(int $idPedido): bool
    {
        $this->db->table('pedido')->where('id', $idPedido)->update(['aviso_cercania' => 1]);
        return $this->db->affectedRows() >= 0;
    }

    public function detalle(int $idSalida): array
    {
        $salida = $this->obtenerSalida($idSalida);
        if (!$salida) {
            throw new RuntimeException('La salida no existe.');
        }
        $salida['paradas']         = $this->paradas($idSalida);
        $salida['geometria']       = $salida['geometria'] ? json_decode($salida['geometria'], true) : null;
        $salida['google_maps_url'] = $this->googleMapsUrl($salida);
        $salida['optimizacion']    = !empty($salida['optimizacion']) ? json_decode((string) $salida['optimizacion'], true) : null;
        return $salida;
    }

    /**
     * Ahorro acumulado de las rutas optimizadas contra el orden de captura, sumando los recorridos
     * iniciados en los últimos $dias días. Solo cuenta los recorridos que guardaron su comparación.
     *
     * Los porcentajes salen de las sumas (no de promediar porcentajes), así un recorrido largo pesa más que uno corto.
     */
    public function resumenOptimizacion(int $dias = 30): array
    {
        $vacio = [
            'dias' => $dias, 'salidas' => 0,
            'km_original' => 0.0, 'km_optimizado' => 0.0, 'km_ahorro' => 0.0, 'pct_km' => 0.0,
            'min_original' => 0.0, 'min_optimizado' => 0.0, 'min_ahorro' => 0.0, 'pct_min' => 0.0,
            'litros_original' => 0.0, 'litros_optimizado' => 0.0, 'litros_ahorro' => 0.0, 'pct_litros' => 0.0,
            'costo_original' => 0.0, 'costo_optimizado' => 0.0, 'costo_ahorro' => 0.0, 'pct_costo' => 0.0,
        ];
        if (!$this->db->fieldExists('optimizacion', 'salida')) {
            return $vacio;
        }

        $desde = date('Y-m-d H:i:s', strtotime('-' . max(1, $dias) . ' days'));
        $filas = $this->db->table('salida')
            ->select('optimizacion')
            ->whereIn('estado', ['iniciada', 'finalizada'])
            ->where('optimizacion IS NOT NULL', null, false)
            ->where('iniciada_at >=', $desde)
            ->get()->getResultArray();

        $r = $vacio;
        foreach ($filas as $fila) {
            $c = json_decode((string) $fila['optimizacion'], true)['comparativo'] ?? null;
            if (!$c) { continue; }
            $r['salidas']++;
            $r['km_original']       += (float) $c['original']['km'];
            $r['km_optimizado']     += (float) $c['optimizado']['km'];
            $r['min_original']      += (float) $c['original']['min'];
            $r['min_optimizado']    += (float) $c['optimizado']['min'];
            $r['litros_original']   += (float) $c['original']['litros'];
            $r['litros_optimizado'] += (float) $c['optimizado']['litros'];
            $r['costo_original']    += (float) $c['original']['costo'];
            $r['costo_optimizado']  += (float) $c['optimizado']['costo'];
        }

        foreach (['km', 'min', 'litros', 'costo'] as $k) {
            $r[$k . '_ahorro'] = $r[$k . '_original'] - $r[$k . '_optimizado'];
            $r['pct_' . $k]    = $r[$k . '_original'] > 0 ? round($r[$k . '_ahorro'] / $r[$k . '_original'] * 100, 1) : 0.0;
        }
        foreach ($r as $clave => $valor) {
            if (is_float($valor) && !str_starts_with($clave, 'pct_')) { $r[$clave] = round($valor, 2); }
        }
        return $r;
    }

    /* ===================== creación interna ===================== */

    /**
     * Crea una salida en borrador con el orden óptimo ya calculado.
     * Sin lista de pedidos toma los listos del repartidor (los 6 más antiguos).
     */
    public function crear(int $idRepartidor, ?array $idsPedidos = null): array
    {
        $this->exigirRepartidor($idRepartidor);

        $disponibles = $this->pedidosDisponibles($idRepartidor, $idsPedidos);
        if (!$disponibles) {
            throw new RuntimeException('No tienes pedidos listos para salir.');
        }
        if ($idsPedidos === null) {
            $disponibles = array_slice($disponibles, 0, self::MAX_PEDIDOS_SALIDA);
        } elseif (count($disponibles) > self::MAX_PEDIDOS_SALIDA) {
            throw new RuntimeException('Una salida no puede tener más de ' . self::MAX_PEDIDOS_SALIDA . ' pedidos.');
        }

        $ids  = array_map(static fn (array $pedido): int => (int) $pedido['id'], $disponibles);
        $plan = $this->rutas->planSalida($idRepartidor, null, $ids);

        if (!empty($plan['sin_ubicacion'])) {
            throw new RuntimeException('Hay pedidos sin ubicación en el mapa. Avisa al administrador para que coloque el pin del cliente.');
        }
        if (count($plan['paradas']) !== count($ids)) {
            throw new RuntimeException('No se pudo ordenar todos los pedidos de la salida.');
        }

        $ahora = date('Y-m-d H:i:s');
        $this->db->transBegin();
        try {
            $datosSalida = [
                'id_repartidor'    => $idRepartidor,
                'estado'           => 'borrador',
                'max_pedidos'      => self::MAX_PEDIDOS_SALIDA,
                'km_total'         => $plan['km_total'],
                'fuente_distancia' => $plan['fuente_distancia'],
                'geometria'        => $plan['geometria'] ? json_encode($plan['geometria']) : null,
                'local_lat'        => $plan['local']['lat'],
                'local_lng'        => $plan['local']['lng'],
                'created_at'       => $ahora,
            ];
            // Tiempo, tráfico y comparación contra el orden de captura: se guardan para medir el ahorro después
            if ($this->db->fieldExists('optimizacion', 'salida')) {
                $datosSalida['optimizacion'] = json_encode([
                    'tiempo'      => $plan['tiempo'],
                    'comparativo' => $plan['comparativo'],
                    'combustible' => $plan['combustible'],
                ], JSON_UNESCAPED_UNICODE);
            }
            $this->db->table('salida')->insert($datosSalida);
            $idSalida = (int) $this->db->insertID();

            $conLlegada = $this->db->fieldExists('llegada_est', 'salida_pedido');
            foreach ($plan['paradas'] as $parada) {
                $datosParada = [
                    'id_salida'         => $idSalida,
                    'id_pedido'         => (int) $parada['id_pedido'],
                    'orden'             => (int) $parada['orden'],
                    'estado'            => 'pendiente',
                    'direccion'         => $parada['direccion'],
                    'lat'               => $parada['lat'],
                    'lng'               => $parada['lng'],
                    'km_desde_anterior' => $parada['km_desde_anterior'],
                    'asignado_at'       => $ahora,
                ];
                if ($conLlegada) {
                    $datosParada['llegada_est'] = $parada['llegada'] ?? null;
                }
                $this->db->table('salida_pedido')->insert($datosParada);
            }
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw new RuntimeException('No se pudo guardar la salida.');
        }

        return $this->detalle($idSalida);
    }

    /** Arranca una salida en borrador y pasa sus pedidos a "Pedido en tránsito". */
    public function iniciar(int $idSalida): array
    {
        $salida = $this->obtenerSalida($idSalida);
        if (!$salida) {
            throw new RuntimeException('La salida no existe.');
        }
        if ($salida['estado'] !== 'borrador') {
            throw new RuntimeException('Solo se puede iniciar una salida en borrador.');
        }

        $paradas = $this->paradas($idSalida);
        if (!$paradas || count($paradas) > self::MAX_PEDIDOS_SALIDA) {
            throw new RuntimeException('La salida debe tener entre 1 y ' . self::MAX_PEDIDOS_SALIDA . ' pedidos.');
        }

        $ahora  = date('Y-m-d H:i:s');
        $status = new StatusModel();

        $this->db->transBegin();
        try {
            $this->db->table('salida')->where('id', $idSalida)->update([
                'estado'      => 'iniciada',
                'iniciada_at' => $ahora,
            ]);

            foreach ($paradas as $parada) {
                $this->db->table('salida_pedido')->where('id', $parada['id'])->update(['estado' => 'en_ruta']);
                if (!$status->actualizarEstado((int) $parada['id_pedido'], 'Pedido en tránsito')) {
                    throw new RuntimeException('No se pudo iniciar el pedido #' . $parada['id_pedido'] . ': ' . ($status->ultimoError ?: 'error desconocido'));
                }
            }
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        return $this->detalle($idSalida);
    }

    /* ===================== auxiliares ===================== */

    private function exigirRepartidor(int $idRepartidor): void
    {
        if ($idRepartidor <= 0) {
            throw new RuntimeException('Tu cuenta no está vinculada a un repartidor. Avisa al administrador.');
        }
    }

    /** Condición SQL: el pedido no está en ninguna salida activa. */
    private function sinSalidaActiva(): string
    {
        return "NOT EXISTS (SELECT 1 FROM salida_pedido sp JOIN salida s ON s.id = sp.id_salida WHERE sp.id_pedido = p.id AND s.estado IN ('borrador','iniciada'))";
    }

    private function googleMapsUrl(array $salida): ?string
    {
        if (empty($salida['local_lat']) || empty($salida['local_lng']) || empty($salida['paradas'])) {
            return null;
        }
        $conPunto = array_values(array_filter($salida['paradas'], static fn (array $p): bool => $p['lat'] !== null && $p['lng'] !== null));
        if (!$conPunto) {
            return null;
        }

        $punto     = static fn (array $p): string => sprintf('%.6f,%.6f', $p['lat'], $p['lng']);
        $local     = sprintf('%.6f,%.6f', $salida['local_lat'], $salida['local_lng']);
        $waypoints = array_map($punto, array_slice($conPunto, 0, 9));

        return 'https://www.google.com/maps/dir/?' . http_build_query([
            'api' => 1, 'origin' => $local, 'destination' => $local,
            'travelmode' => 'driving', 'waypoints' => implode('|', $waypoints),
        ]);
    }

    private function obtenerSalida(int $idSalida): ?array
    {
        return $this->db->table('salida')->where('id', $idSalida)->get()->getRowArray();
    }

    private function paradas(int $idSalida): array
    {
        return $this->db->table('salida_pedido sp')
            ->select('sp.*, p.estado_actual, p.prioritario, p.total AS pedido_total, p.monto_pagado AS pedido_pagado, p.tipo_pago, c.nombre AS cliente, c.apellido_paterno, c.tel')
            ->join('pedido p', 'p.id = sp.id_pedido')
            ->join('clientes c', 'c.id_cliente = p.id_cliente')
            ->where('sp.id_salida', $idSalida)
            ->orderBy('sp.orden', 'ASC')
            ->get()->getResultArray();
    }

    private function pedidosDisponibles(int $idRepartidor, ?array $idsPedidos): array
    {
        $builder = $this->db->table('pedido p')
            ->select('p.id, p.estado_actual, p.prioritario')
            ->where('p.id_repartidor', $idRepartidor)
            ->where('p.tipo_entrega', 'domicilio')
            ->whereIn('p.estado_actual', self::ESTADOS_LISTOS)
            ->where($this->sinSalidaActiva(), null, false)
            // Los urgentes primero: así entran en el recorrido aunque ya haya más de 6 pedidos listos
            ->orderBy('p.prioritario', 'DESC')
            ->orderBy('p.id', 'ASC');

        if ($idsPedidos !== null) {
            $idsPedidos = array_values(array_filter(array_map('intval', $idsPedidos), static fn (int $id): bool => $id > 0));
            if (!$idsPedidos) {
                return [];
            }
            $builder->whereIn('p.id', $idsPedidos);
        }
        return $builder->get()->getResultArray();
    }
}
