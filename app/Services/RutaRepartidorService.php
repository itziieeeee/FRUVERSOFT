<?php

namespace App\Services;

use RuntimeException;
use SplPriorityQueue;

class RutaRepartidorService
{
    private const DEFAULT_MAX_PEDIDOS = 6;
    private const DEFAULT_MAX_KM_EXTRA = 20.0;

    private $db;
    private array $nodes = [];
    private array $nodeIndexes = [];
    private array $graph = [];
    private array $distanceCache = [];
    private array $config = [];
    /** Distancias en km entre puntos, indexadas por clavePunto(). */
    private array $matrizKm = [];
    /** De dónde salieron las distancias: osrm, mixta o estimada. */
    private string $fuenteDistancia = 'estimada';

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->cargarConfiguracion();
        $this->cargarGrafo();
    }

    /**
     * Sugiere un repartidor evaluando dónde insertar la nueva entrega
     * dentro de los pedidos que ya tiene asignados.
     *
     * - Con pin en el mapa (o, si no lo hay, el centro de su zona) se calculan km por carretera.
     * - Sin ninguna coordenada se usa el grafo de zonas con Dijkstra.
     * - Si varios repartidores quedan a una diferencia menor que la tolerancia, se prefiere
     *   al que tiene asignada una zona que cubre el destino.
     */
    public function sugerir(int $idCliente): array
    {
        $direccion = $this->obtenerDireccionCliente($idCliente);
        if (!$direccion) {
            throw new RuntimeException('El cliente no tiene una dirección registrada.');
        }

        $coordLocal = $this->coordenadasLocal();
        $destino    = $this->resolverNodoDireccion($direccion);
        $local      = $this->obtenerNodoLocal();

        $ubicacionNueva = $this->ubicacionDireccion($direccion, $destino);
        $usaCoordenadas = $coordLocal !== null && $ubicacionNueva !== null;

        if (!$usaCoordenadas && !$destino) {
            throw new RuntimeException(
                'No se puede ubicar la dirección. Pon el pin del cliente en el mapa o agrega su zona al grafo: ' .
                $this->textoDireccion($direccion)
            );
        }
        if (!$usaCoordenadas && !$local) {
            throw new RuntimeException('No existe un nodo local configurado para iniciar las rutas.');
        }

        $nombreZona = $destino['nombre'] ?? ($direccion['municipio'] ?: ($direccion['estado'] ?? ''));

        $repartidores = $this->db->table('repartidor r')
            ->select('r.id, r.nombre, r.ap_p, r.ap_m, r.zona_base_id, z.nombre AS zona_nombre, z.lat AS zona_lat, z.lng AS zona_lng')
            ->join('ruta_nodo z', 'z.id = r.zona_base_id', 'left')
            ->orderBy('r.nombre', 'ASC')
            ->get()
            ->getResultArray();

        // Primera pasada: ubicar los pedidos de cada repartidor y juntar todos los puntos,
        // para pedir a OSRM una sola matriz de distancias.
        $datos  = [];
        $puntos = [];
        if ($usaCoordenadas) {
            $puntos[] = $coordLocal;
            $puntos[] = $ubicacionNueva;
        }

        foreach ($repartidores as $repartidor) {
            $id      = (int) $repartidor['id'];
            $pedidos = $this->obtenerPedidosActivos($id);
            $paradas = null;

            if ($usaCoordenadas && count($pedidos) < $this->maxPedidos()) {
                $paradas = [];
                foreach ($pedidos as $pedido) {
                    $ubicacion = $this->ubicacionDireccion($pedido, $this->resolverNodoDireccion($pedido));
                    if ($ubicacion === null) {
                        $paradas = null;
                        break;
                    }
                    $paradas[] = $ubicacion;
                    $puntos[]  = $ubicacion;
                }
            }

            $datos[$id] = ['pedidos' => $pedidos, 'paradas' => $paradas];
        }

        if ($usaCoordenadas) {
            $this->prepararDistancias($puntos);
        }

        $candidatos = [];
        foreach ($repartidores as $repartidor) {
            $idRepartidor    = (int) $repartidor['id'];
            $pedidos         = $datos[$idRepartidor]['pedidos'];
            $paradas         = $datos[$idRepartidor]['paradas'];
            $cantidadPedidos = count($pedidos);

            $enZona    = false;
            $puntoZona = $this->coordenadas(['lat' => $repartidor['zona_lat'], 'lng' => $repartidor['zona_lng']]);
            if ($puntoZona !== null && $ubicacionNueva !== null) {
                $enZona = $this->distanciaLineaRecta($ubicacionNueva, $puntoZona) <= $this->radioZonaKm();
            }

            $base = [
                'id'                => $idRepartidor,
                'nombre'            => trim(($repartidor['nombre'] ?? '') . ' ' . ($repartidor['ap_p'] ?? '') . ' ' . ($repartidor['ap_m'] ?? '')),
                'pedidos_activos'   => $cantidadPedidos,
                'distancia_extra'   => null,
                'distancia_ruta'    => null,
                'zona_nueva'        => $nombreZona,
                'zona_base'         => $repartidor['zona_nombre'] ?? null,
                'en_zona'           => $enZona,
                'aproximado'        => false,
                'disponible'        => false,
                'motivo'            => null,
                'posicion_sugerida' => null,
                'metodo'            => null,
            ];

            if ($cantidadPedidos >= $this->maxPedidos()) {
                $base['motivo'] = 'Alcanzó el límite de ' . $this->maxPedidos() . ' pedidos activos.';
                $candidatos[] = $base;
                continue;
            }

            // Cálculo por coordenadas (km por carretera, con respaldo estimado).
            if ($paradas !== null) {
                $evaluacion = $this->evaluarInsercionesCoordenadas($coordLocal, $paradas, $ubicacionNueva);
                $aproximado = $ubicacionNueva['aproximada'];
                foreach ($paradas as $parada) {
                    $aproximado = $aproximado || $parada['aproximada'];
                }

                $base['metodo']            = 'coordenadas';
                $base['aproximado']        = $aproximado;
                $base['distancia_extra']   = round($evaluacion['extra'], 2);
                $base['distancia_ruta']    = round($evaluacion['total'], 2);
                $base['posicion_sugerida'] = $evaluacion['posicion'];
                $base['disponible']        = $evaluacion['extra'] <= $this->maxKmExtra();
                if (!$base['disponible']) {
                    $base['motivo'] = 'La ruta agregaría más de ' . $this->formatoKm($this->maxKmExtra()) . ' km.';
                }
                $candidatos[] = $base;
                continue;
            }

            // Respaldo: grafo de zonas con Dijkstra.
            if (!$destino || !$local) {
                $base['motivo'] = 'Falta ubicar en el mapa alguno de sus pedidos.';
                $candidatos[] = $base;
                continue;
            }
            $base['metodo'] = 'zonas';

            $nodosActuales     = [];
            $rutaNoConfigurada = null;
            foreach ($pedidos as $pedido) {
                $nodoPedido = $this->resolverNodoDireccion($pedido);
                if (!$nodoPedido) {
                    $rutaNoConfigurada = $this->textoDireccion($pedido);
                    break;
                }
                $nodosActuales[] = $nodoPedido['id'];
            }

            if ($rutaNoConfigurada !== null) {
                $base['motivo'] = 'Uno de sus pedidos no tiene zona configurada: ' . $rutaNoConfigurada;
                $candidatos[] = $base;
                continue;
            }

            $evaluacion = $this->evaluarInserciones(
                (int) $local['id'],
                $nodosActuales,
                (int) $destino['id']
            );

            if ($evaluacion === null) {
                $base['motivo'] = 'No existe un camino en el grafo para completar la ruta.';
                $candidatos[] = $base;
                continue;
            }

            $base['distancia_extra']   = round($evaluacion['extra'], 2);
            $base['distancia_ruta']    = round($evaluacion['total'], 2);
            $base['posicion_sugerida'] = $evaluacion['posicion'];
            $base['disponible']        = $evaluacion['extra'] <= $this->maxKmExtra();
            if (!$base['disponible']) {
                $base['motivo'] = 'La ruta agregaría más de ' . $this->formatoKm($this->maxKmExtra()) . ' km.';
            }
            $candidatos[] = $base;
        }

        usort($candidatos, static function (array $a, array $b): int {
            $distanciaA = $a['distancia_extra'] === null ? PHP_FLOAT_MAX : $a['distancia_extra'];
            $distanciaB = $b['distancia_extra'] === null ? PHP_FLOAT_MAX : $b['distancia_extra'];
            return $distanciaA <=> $distanciaB;
        });

        // El más cercano; pero si un repartidor de la zona queda dentro de la tolerancia, gana ese.
        $recomendado = null;
        foreach ($candidatos as $candidato) {
            if ($candidato['disponible']) {
                $recomendado = $candidato;
                $recomendado['criterio'] = 'km';
                break;
            }
        }

        if ($recomendado !== null) {
            $limite = $recomendado['distancia_extra'] + $this->toleranciaZonaKm();
            foreach ($candidatos as $candidato) {
                if ($candidato['disponible'] && $candidato['en_zona'] && $candidato['distancia_extra'] <= $limite) {
                    if ($candidato['id'] !== $recomendado['id']) {
                        $recomendado = $candidato;
                        $recomendado['criterio'] = 'zona';
                    }
                    break;
                }
            }
        }

        return [
            'destino' => [
                'nodo_id'   => $destino ? (int) $destino['id'] : null,
                'zona'      => $nombreZona,
                'lat'       => $ubicacionNueva['lat'] ?? null,
                'lng'       => $ubicacionNueva['lng'] ?? null,
                'aproximada'=> $ubicacionNueva['aproximada'] ?? null,
                'colonia'   => $direccion['colonia'] ?? null,
                'municipio' => $direccion['municipio'] ?? null,
                'estado'    => $direccion['estado'] ?? null,
            ],
            'configuracion' => [
                'max_pedidos'      => $this->maxPedidos(),
                'max_km_extra'     => $this->maxKmExtra(),
                'modo_demo'        => $this->config['modo_demo'] ?? '0',
                'fuente_distancia' => $usaCoordenadas ? $this->fuenteDistancia : 'zonas',
            ],
            'recomendado' => $recomendado,
            'candidatos'  => $candidatos,
            'advertencias'=> $recomendado ? [] : ['Sin ruta cercana, se agenda para la siguiente salida.'],
        ];
    }

    /**
     * Ubicación de una dirección: su pin exacto o, si no tiene, el centro de su zona (aproximada).
     */
    private function ubicacionDireccion(array $fila, ?array $nodo): ?array
    {
        $pin = $this->coordenadas($fila);
        if ($pin !== null) {
            return $pin + ['aproximada' => false];
        }

        if ($nodo !== null) {
            $centro = $this->coordenadas($nodo);
            if ($centro !== null) {
                return $centro + ['aproximada' => true];
            }
        }

        return null;
    }

    private function clavePunto(array $punto): string
    {
        return sprintf('%.5f,%.5f', $punto['lat'], $punto['lng']);
    }

    /**
     * Carga la matriz de distancias entre todos los puntos con una sola consulta a OSRM.
     * Los pares que OSRM no resuelve se estiman con línea recta por el factor de carretera.
     */
    private function prepararDistancias(array $puntos): void
    {
        $unicos = [];
        foreach ($puntos as $punto) {
            $unicos[$this->clavePunto($punto)] = ['lat' => $punto['lat'], 'lng' => $punto['lng']];
        }

        $claves = array_keys($unicos);
        $lista  = array_values($unicos);

        $this->matrizKm        = [];
        $this->fuenteDistancia = 'estimada';
        if (count($lista) < 2) {
            return;
        }

        $osrm = $this->consultarOsrm($lista);
        $deOsrm = 0;
        $estimados = 0;

        foreach ($lista as $i => $a) {
            foreach ($lista as $j => $b) {
                if ($i === $j) {
                    $this->matrizKm[$claves[$i]][$claves[$j]] = 0.0;
                    continue;
                }

                $metros = $osrm[$i][$j] ?? null;
                if ($metros !== null && is_numeric($metros)) {
                    $this->matrizKm[$claves[$i]][$claves[$j]] = (float) $metros / 1000;
                    $deOsrm++;
                } else {
                    $this->matrizKm[$claves[$i]][$claves[$j]] = $this->estimarKm($a, $b);
                    $estimados++;
                }
            }
        }

        $this->fuenteDistancia = $estimados === 0 ? 'osrm' : ($deOsrm === 0 ? 'estimada' : 'mixta');
    }

    /**
     * Pide a OSRM la matriz de distancias (en metros) por carretera. Devuelve null si falla.
     */
    private function consultarOsrm(array $lista): ?array
    {
        if (($this->config['usar_osrm'] ?? '1') !== '1' || !function_exists('curl_init')) {
            return null;
        }
        // El servidor público limita las peticiones a unos 100 puntos.
        if (count($lista) > 90) {
            return null;
        }

        $base = rtrim((string) ($this->config['osrm_url'] ?? 'https://router.project-osrm.org'), '/');
        if (!preg_match('#^https?://#i', $base)) {
            return null;
        }

        $coordenadas = implode(';', array_map(
            static fn (array $p): string => sprintf('%.6f,%.6f', $p['lng'], $p['lat']),
            $lista
        ));

        $curl = curl_init($base . '/table/v1/driving/' . $coordenadas . '?annotations=distance');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_USERAGENT      => 'FRUVERSOFT-escolar/1.0',
        ]);
        $respuesta = curl_exec($curl);
        $codigo    = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($respuesta === false || $codigo !== 200) {
            return null;
        }

        $datos = json_decode($respuesta, true);
        if (!is_array($datos) || ($datos['code'] ?? '') !== 'Ok' || !isset($datos['distances'])) {
            return null;
        }

        return $datos['distances'];
    }

    private function radioZonaKm(): float
    {
        return max(0.0, (float) ($this->config['radio_zona_km'] ?? 40));
    }

    private function toleranciaZonaKm(): float
    {
        return max(0.0, (float) ($this->config['tolerancia_zona_km'] ?? 10));
    }

    /**
     * Valida la capacidad antes de guardar un pedido a domicilio.
     * La elección manual del vendedor sigue siendo válida si cumple las reglas.
     */
    public function validarAsignacion(int $idRepartidor): array
    {
        $repartidor = $this->db->table('repartidor')
            ->select('id, nombre, ap_p, ap_m')
            ->where('id', $idRepartidor)
            ->get()
            ->getRowArray();

        if (!$repartidor) {
            return ['ok' => false, 'message' => 'El repartidor seleccionado no existe.'];
        }

        $cantidad = count($this->obtenerPedidosActivos($idRepartidor));
        if ($cantidad >= $this->maxPedidos()) {
            return [
                'ok'      => false,
                'message' => 'El repartidor seleccionado ya tiene el límite de ' . $this->maxPedidos() . ' pedidos activos.',
            ];
        }

        return ['ok' => true];
    }

    /**
     * Ordena los pedidos activos de un repartidor por distancia desde el local.
     * Si dos entregas están en la misma zona, conserva el orden del pedido.
     */
    public function ordenarEntregas(int $idRepartidor): array
    {
        $local = $this->obtenerNodoLocal();
        if (!$local) {
            throw new RuntimeException('No existe un nodo local configurado.');
        }

        $pedidos = $this->obtenerPedidosActivos($idRepartidor);
        $ordenados = [];
        $sinZona = [];

        foreach ($pedidos as $pedido) {
            $nodo = $this->resolverNodoDireccion($pedido);
            if (!$nodo) {
                $sinZona[] = [
                    'id_pedido' => (int) $pedido['id'],
                    'direccion' => $this->textoDireccion($pedido),
                    'motivo'    => 'Zona sin configurar',
                ];
                continue;
            }

            $distancia = $this->distanciaEntre((int) $local['id'], (int) $nodo['id']);
            if ($distancia === null) {
                $sinZona[] = [
                    'id_pedido' => (int) $pedido['id'],
                    'direccion' => $this->textoDireccion($pedido),
                    'motivo'    => 'No existe camino desde el local',
                ];
                continue;
            }

            $ordenados[] = [
                'id_pedido' => (int) $pedido['id'],
                'zona'      => $nodo['nombre'],
                'direccion' => $this->textoDireccion($pedido),
                'distancia_desde_local' => round($distancia, 2),
                'estado'    => $pedido['estado_actual'],
            ];
        }

        usort($ordenados, static function (array $a, array $b): int {
            $distancia = $a['distancia_desde_local'] <=> $b['distancia_desde_local'];
            return $distancia !== 0 ? $distancia : $a['id_pedido'] <=> $b['id_pedido'];
        });

        return [
            'repartidor_id' => $idRepartidor,
            'pedidos'       => $ordenados,
            'sin_zona'      => $sinZona,
        ];
    }

    private function cargarConfiguracion(): void
    {
        $this->config = [
            'max_pedidos'  => (string) self::DEFAULT_MAX_PEDIDOS,
            'max_km_extra' => (string) self::DEFAULT_MAX_KM_EXTRA,
            'local_clave'  => 'local',
            'modo_demo'    => '0',
        ];

        try {
            $filas = $this->db->table('ruta_configuracion')->get()->getResultArray();
            foreach ($filas as $fila) {
                $this->config[$fila['clave']] = $fila['valor'];
            }
        } catch (\Throwable $e) {
            // Permite que el código responda con valores seguros mientras se ejecuta la migración.
        }
    }

    private function cargarGrafo(): void
    {
        try {
            $filas = $this->db->table('ruta_nodo')
                ->where('activo', 1)
                ->get()
                ->getResultArray();

            foreach ($filas as $fila) {
                $id = (int) $fila['id'];
                $this->nodes[$id] = $fila;
                $this->indexarNodo($fila);
            }

            $aristas = $this->db->table('ruta_arista')
                ->where('activo', 1)
                ->get()
                ->getResultArray();

            foreach ($aristas as $arista) {
                $origen = (int) $arista['nodo_origen_id'];
                $destino = (int) $arista['nodo_destino_id'];
                $peso = (float) $arista['distancia_km'];

                if (!isset($this->nodes[$origen], $this->nodes[$destino]) || $peso < 0) {
                    continue;
                }

                $this->graph[$origen][] = ['node' => $destino, 'weight' => $peso];
                if ((int) $arista['bidireccional'] === 1) {
                    $this->graph[$destino][] = ['node' => $origen, 'weight' => $peso];
                }
            }
        } catch (\Throwable $e) {
            throw new RuntimeException('El grafo de rutas no está disponible. Ejecuta la migración de rutas.');
        }
    }

    private function indexarNodo(array $nodo): void
    {
        $claves = [
            $nodo['clave'] ?? '',
            $nodo['nombre'] ?? '',
        ];

        if (!empty($nodo['colonia'])) {
            $claves[] = $nodo['colonia'] . '|' . ($nodo['municipio'] ?? '') . '|' . ($nodo['estado'] ?? '');
        }
        if (!empty($nodo['municipio'])) {
            $claves[] = $nodo['municipio'] . '|' . ($nodo['estado'] ?? '');
        }
        if (!empty($nodo['aliases'])) {
            $claves = array_merge($claves, explode('|', $nodo['aliases']));
        }
        if (($nodo['tipo'] ?? '') === 'estado' && !empty($nodo['estado'])) {
            $claves[] = 'estado:' . $nodo['estado'];
        }

        foreach ($claves as $clave) {
            $claveNormalizada = $this->normalizar($clave);
            if ($claveNormalizada === '') {
                continue;
            }
            $this->nodeIndexes[$claveNormalizada][] = (int) $nodo['id'];
        }
    }

    private function resolverNodoDireccion(array $direccion): ?array
    {
        $candidatos = [];
        $colonia = trim((string) ($direccion['colonia'] ?? ''));
        $municipio = trim((string) ($direccion['municipio'] ?? ''));
        $estado = trim((string) ($direccion['estado'] ?? ''));

        if ($colonia !== '') {
            $candidatos[] = $colonia . '|' . $municipio . '|' . $estado;
        }
        if ($municipio !== '') {
            $candidatos[] = $municipio . '|' . $estado;
            $candidatos[] = $municipio;
        }
        if ($colonia !== '') {
            $candidatos[] = $colonia;
        }
        if ($estado !== '') {
            // Cobertura general para municipios nuevos del estado.
            $candidatos[] = 'estado:' . $estado;
        }

        foreach ($candidatos as $clave) {
            $ids = array_values(array_unique($this->nodeIndexes[$this->normalizar($clave)] ?? []));

            // El local puede compartir colonia o municipio con una zona de entrega.
            // Nunca debe elegirse como destino de un cliente.
            $ids = array_values(array_filter($ids, function (int $id): bool {
                return isset($this->nodes[$id]) && ($this->nodes[$id]['tipo'] ?? '') !== 'local';
            }));

            if (count($ids) === 1 && isset($this->nodes[$ids[0]])) {
                return $this->nodes[$ids[0]];
            }
        }

        return null;
    }

    private function obtenerNodoLocal(): ?array
    {
        $clave = $this->normalizar((string) ($this->config['local_clave'] ?? 'local'));
        $ids = array_values(array_unique($this->nodeIndexes[$clave] ?? []));
        foreach ($ids as $id) {
            if (($this->nodes[$id]['tipo'] ?? '') === 'local') {
                return $this->nodes[$id];
            }
        }
        return $ids && isset($this->nodes[$ids[0]]) ? $this->nodes[$ids[0]] : null;
    }

    private function obtenerDireccionCliente(int $idCliente): ?array
    {
        // Si el cliente tiene varias direcciones se usa la más reciente (mismo criterio que obtenerPedidosActivos).
        return $this->db->table('direccion')
            ->where('id_cliente', $idCliente)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
    }

    private function obtenerPedidosActivos(int $idRepartidor): array
    {
        return $this->db->table('pedido p')
            ->select('p.id, p.estado_actual, d.calle, d.numero, d.colonia, d.municipio, d.estado, d.lat, d.lng')
            // Una sola dirección por cliente (la más reciente), para no duplicar pedidos.
            ->join('direccion d', 'd.id = (SELECT MAX(d2.id) FROM direccion d2 WHERE d2.id_cliente = p.id_cliente)', 'left', false)
            ->where('p.id_repartidor', $idRepartidor)
            ->where('p.tipo_entrega', 'domicilio')
            ->whereNotIn('p.estado_actual', [
                'Venta confirmada',
                'Pedido pagado',
                'Pedido entregado',
                'Entregado',
                'Cancelado',
                'Pedido cancelado',
            ])
            ->orderBy('p.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Lee lat/lng de una fila y las valida. Devuelve null si faltan o están fuera de rango.
     */
    private function coordenadas(array $fila): ?array
    {
        $lat = $fila['lat'] ?? null;
        $lng = $fila['lng'] ?? null;
        if ($lat === null || $lng === null || $lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
            return null;
        }

        $lat = (float) $lat;
        $lng = (float) $lng;
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }
        return ['lat' => $lat, 'lng' => $lng];
    }

    private function coordenadasLocal(): ?array
    {
        return $this->coordenadas([
            'lat' => $this->config['local_lat'] ?? null,
            'lng' => $this->config['local_lng'] ?? null,
        ]);
    }

    /**
     * Km entre dos puntos: el valor de la matriz (OSRM) y, si no está, una estimación.
     */
    private function distanciaCoordenadas(array $a, array $b): float
    {
        $km = $this->matrizKm[$this->clavePunto($a)][$this->clavePunto($b)] ?? null;
        return $km !== null ? $km : $this->estimarKm($a, $b);
    }

    /**
     * Km por carretera estimados: línea recta por un factor de carretera (ruta_configuracion).
     */
    private function estimarKm(array $a, array $b): float
    {
        $factor = max(1.0, (float) ($this->config['factor_carretera'] ?? 1.3));
        return $this->distanciaLineaRecta($a, $b) * $factor;
    }

    /**
     * Distancia en línea recta (Haversine) en km.
     */
    private function distanciaLineaRecta(array $a, array $b): float
    {
        $radioTierraKm = 6371.0;
        $dLat = deg2rad($b['lat'] - $a['lat']);
        $dLng = deg2rad($b['lng'] - $a['lng']);

        $h = sin($dLat / 2) ** 2
            + cos(deg2rad($a['lat'])) * cos(deg2rad($b['lat'])) * sin($dLng / 2) ** 2;
        return 2 * $radioTierraKm * asin(min(1.0, sqrt($h)));
    }

    /**
     * Costo de salir del local, pasar por todas las paradas en orden y regresar al local.
     */
    private function costoRutaCoordenadas(array $local, array $paradas): float
    {
        $total = 0.0;
        $anterior = $local;
        foreach ($paradas as $parada) {
            $total += $this->distanciaCoordenadas($anterior, $parada);
            $anterior = $parada;
        }
        return $total + $this->distanciaCoordenadas($anterior, $local);
    }

    /**
     * Prueba la nueva entrega en cada posición de la ruta actual (ida y vuelta al local)
     * y conserva la que agrega menos kilómetros.
     */
    private function evaluarInsercionesCoordenadas(array $local, array $actuales, array $nuevo): array
    {
        $costoBase = $this->costoRutaCoordenadas($local, $actuales);
        $mejor = null;

        for ($posicion = 0, $n = count($actuales); $posicion <= $n; $posicion++) {
            $ruta = $actuales;
            array_splice($ruta, $posicion, 0, [$nuevo]);
            $costo = $this->costoRutaCoordenadas($local, $ruta);
            $extra = max(0.0, $costo - $costoBase);

            if ($mejor === null || $extra < $mejor['extra']) {
                $mejor = ['extra' => $extra, 'total' => $costo, 'posicion' => $posicion + 1];
            }
        }
        return $mejor;
    }

    private function evaluarInserciones(int $localId, array $actuales, int $nuevoId): ?array
    {
        $rutaBase = array_merge([$localId], $actuales);
        $costoBase = $this->costoRuta($rutaBase);
        if ($costoBase === null) {
            return null;
        }

        $mejor = null;
        $cantidad = count($actuales);
        for ($posicion = 0; $posicion <= $cantidad; $posicion++) {
            $ruta = $actuales;
            array_splice($ruta, $posicion, 0, [$nuevoId]);
            $ruta = array_merge([$localId], $ruta);
            $costo = $this->costoRuta($ruta);
            if ($costo === null) {
                continue;
            }

            $extra = max(0, $costo - $costoBase);
            if ($mejor === null || $extra < $mejor['extra']) {
                $mejor = [
                    'extra'    => $extra,
                    'total'    => $costo,
                    'posicion' => $posicion + 1,
                ];
            }
        }

        return $mejor;
    }

    private function costoRuta(array $ruta): ?float
    {
        $total = 0.0;
        for ($i = 0, $n = count($ruta) - 1; $i < $n; $i++) {
            $distancia = $this->distanciaEntre((int) $ruta[$i], (int) $ruta[$i + 1]);
            if ($distancia === null) {
                return null;
            }
            $total += $distancia;
        }
        return $total;
    }

    /**
     * Dijkstra sobre el grafo de zonas almacenado en ruta_nodo/ruta_arista.
     */
    private function distanciaEntre(int $origen, int $destino): ?float
    {
        if ($origen === $destino) {
            return 0.0;
        }

        $cacheKey = $origen . ':' . $destino;
        if (array_key_exists($cacheKey, $this->distanceCache)) {
            return $this->distanceCache[$cacheKey];
        }

        $distancias = [];
        foreach ($this->nodes as $id => $_nodo) {
            $distancias[$id] = INF;
        }
        $distancias[$origen] = 0.0;

        $cola = new SplPriorityQueue();
        $cola->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
        $cola->insert($origen, 0.0);

        while (!$cola->isEmpty()) {
            $actual = $cola->extract();
            $nodoActual = (int) $actual['data'];
            $distanciaActual = -((float) $actual['priority']);

            if ($distanciaActual > $distancias[$nodoActual]) {
                continue;
            }
            if ($nodoActual === $destino) {
                break;
            }

            foreach ($this->graph[$nodoActual] ?? [] as $vecino) {
                $nuevaDistancia = $distanciaActual + (float) $vecino['weight'];
                $vecinoId = (int) $vecino['node'];
                if ($nuevaDistancia < ($distancias[$vecinoId] ?? INF)) {
                    $distancias[$vecinoId] = $nuevaDistancia;
                    $cola->insert($vecinoId, -$nuevaDistancia);
                }
            }
        }

        $resultado = isset($distancias[$destino]) && is_finite($distancias[$destino])
            ? $distancias[$destino]
            : null;
        $this->distanceCache[$cacheKey] = $resultado;
        return $resultado;
    }

    private function maxPedidos(): int
    {
        return max(1, (int) ($this->config['max_pedidos'] ?? self::DEFAULT_MAX_PEDIDOS));
    }

    private function maxKmExtra(): float
    {
        return max(0.0, (float) ($this->config['max_km_extra'] ?? self::DEFAULT_MAX_KM_EXTRA));
    }

    private function textoDireccion(array $direccion): string
    {
        return implode(', ', array_filter([
            $direccion['calle'] ?? null,
            $direccion['numero'] ?? null,
            $direccion['colonia'] ?? null,
            $direccion['municipio'] ?? null,
            $direccion['estado'] ?? null,
        ], static fn ($valor) => trim((string) $valor) !== ''));
    }

    private function formatoKm(float $km): string
    {
        return rtrim(rtrim(number_format($km, 2, '.', ''), '0'), '.');
    }

    private function normalizar(string $valor): string
    {
        $valor = trim($valor);
        if ($valor === '') {
            return '';
        }

        if (function_exists('iconv')) {
            $convertido = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
            if ($convertido !== false) {
                $valor = $convertido;
            }
        }

        $valor = strtolower($valor);
        $valor = preg_replace('/[^a-z0-9]+/', '-', $valor) ?? '';
        return trim($valor, '-');
    }
}
