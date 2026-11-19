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
    public function entregar(int $idRepartidor, int $idPedido, ?float $lat = null, ?float $lng = null, ?string $nota = null): array
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
            $this->db->table('salida_pedido')
                ->where('id_salida', $salida['id'])
                ->where('id_pedido', $idPedido)
                ->update([
                    'estado'       => 'entregado',
                    'entregado_at' => $ahora,
                    'entrega_lat'  => $lat,
                    'entrega_lng'  => $lng,
                    'nota'         => $nota,
                ]);

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

        return $this->detalle((int) $salida['id']);
    }

    /* ===================== lo que usa el administrador (monitoreo) ===================== */

    /** Salidas en curso y recientes, con sus paradas y la última ubicación del repartidor. */
    public function listarAdmin(): array
    {
        $salidas = $this->db->table('salida s')
            ->select("s.id, s.id_repartidor, s.estado, s.km_total, s.fuente_distancia, s.iniciada_at, s.finalizada_at, s.local_lat, s.local_lng, CONCAT_WS(' ', r.nombre, r.ap_p, r.ap_m) AS repartidor, r.ultima_lat, r.ultima_lng, r.ultima_actualizacion", false)
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
            ->select("sp.id_salida, sp.id_pedido, sp.orden, sp.estado, sp.direccion, sp.lat, sp.lng, sp.km_desde_anterior, sp.entregado_at, CONCAT_WS(' ', c.nombre, c.apellido_paterno) AS cliente", false)
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
            unset($s['ultima_actualizacion']);
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
        return $salida;
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
            $this->db->table('salida')->insert([
                'id_repartidor'    => $idRepartidor,
                'estado'           => 'borrador',
                'max_pedidos'      => self::MAX_PEDIDOS_SALIDA,
                'km_total'         => $plan['km_total'],
                'fuente_distancia' => $plan['fuente_distancia'],
                'geometria'        => $plan['geometria'] ? json_encode($plan['geometria']) : null,
                'local_lat'        => $plan['local']['lat'],
                'local_lng'        => $plan['local']['lng'],
                'created_at'       => $ahora,
            ]);
            $idSalida = (int) $this->db->insertID();

            foreach ($plan['paradas'] as $parada) {
                $this->db->table('salida_pedido')->insert([
                    'id_salida'         => $idSalida,
                    'id_pedido'         => (int) $parada['id_pedido'],
                    'orden'             => (int) $parada['orden'],
                    'estado'            => 'pendiente',
                    'direccion'         => $parada['direccion'],
                    'lat'               => $parada['lat'],
                    'lng'               => $parada['lng'],
                    'km_desde_anterior' => $parada['km_desde_anterior'],
                    'asignado_at'       => $ahora,
                ]);
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
            ->select('sp.*, p.estado_actual, c.nombre AS cliente, c.apellido_paterno, c.tel')
            ->join('pedido p', 'p.id = sp.id_pedido')
            ->join('clientes c', 'c.id_cliente = p.id_cliente')
            ->where('sp.id_salida', $idSalida)
            ->orderBy('sp.orden', 'ASC')
            ->get()->getResultArray();
    }

    private function pedidosDisponibles(int $idRepartidor, ?array $idsPedidos): array
    {
        $builder = $this->db->table('pedido p')
            ->select('p.id, p.estado_actual')
            ->where('p.id_repartidor', $idRepartidor)
            ->where('p.tipo_entrega', 'domicilio')
            ->whereIn('p.estado_actual', self::ESTADOS_LISTOS)
            ->where($this->sinSalidaActiva(), null, false)
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
