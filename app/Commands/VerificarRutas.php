<?php

namespace App\Commands;

use App\Services\RutaRepartidorService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use ReflectionClass;

/**
 * Pruebas de verificación del motor de rutas (Dijkstra, árbol de expansión mínima, mejor orden)
 * y tabla de rutas reales para comparar contra Google Maps.
 * Genera el reporte writable/verificacion_rutas.md
 */
class VerificarRutas extends BaseCommand
{
    protected $group       = 'Rutas';
    protected $name        = 'rutas:verificar';
    protected $description = 'Verifica Dijkstra, el árbol de expansión mínima y el mejor orden de entregas; mide tiempos.';
    protected $usage       = 'rutas:verificar [--sin-osrm]';

    private RutaRepartidorService $servicio;
    private ReflectionClass $reflejo;
    /** @var string[] */
    private array $reporte = [];
    private bool $todoBien = true;

    public function run(array $params)
    {
        ini_set('memory_limit', '-1');
        mt_srand(2026); // semilla fija: los resultados se pueden repetir

        $this->servicio = new RutaRepartidorService();
        $this->reflejo  = new ReflectionClass($this->servicio);

        $this->linea('# Verificación del motor de rutas FRUVERSOFT');
        $this->linea('Generado: ' . date('Y-m-d H:i') . ' (semilla fija 2026, se puede repetir con `php spark rutas:verificar`).');
        $this->linea('');

        $this->pruebaDijkstra();
        $this->pruebaArbol();
        $this->pruebaMejorOrden();
        $this->pruebaTiempos();
        $this->tablaRutasReales(!in_array('--sin-osrm', $params, true) && CLI::getOption('sin-osrm') === null);

        $this->linea('## Resultado general');
        $this->linea($this->todoBien ? 'TODAS LAS PRUEBAS PASARON.' : 'HAY PRUEBAS QUE FALLARON, revisa el detalle arriba.');

        $ruta = WRITEPATH . 'verificacion_rutas.md';
        file_put_contents($ruta, implode("\n", $this->reporte) . "\n");
        CLI::newLine();
        CLI::write('Reporte guardado en ' . $ruta, 'green');

        return $this->todoBien ? EXIT_SUCCESS : EXIT_ERROR;
    }

    /* ----------------------------- utilidades ----------------------------- */

    private function linea(string $texto): void
    {
        $this->reporte[] = $texto;
        CLI::write($texto);
    }

    private function ok(bool $condicion, string $texto): void
    {
        if (!$condicion) {
            $this->todoBien = false;
        }
        $this->linea('- ' . ($condicion ? '[OK] ' : '[FALLA] ') . $texto);
    }

    private function propiedad(string $nombre, $valor): void
    {
        $p = $this->reflejo->getProperty($nombre);
        $p->setAccessible(true);
        $p->setValue($this->servicio, $valor);
    }

    private function leer(string $nombre)
    {
        $p = $this->reflejo->getProperty($nombre);
        $p->setAccessible(true);
        return $p->getValue($this->servicio);
    }

    private function llamar(string $metodo, ...$args)
    {
        $m = $this->reflejo->getMethod($metodo);
        $m->setAccessible(true);
        return $m->invoke($this->servicio, ...$args);
    }

    /* ----------------------------- 1. Dijkstra ----------------------------- */

    private function pruebaDijkstra(): void
    {
        $this->linea('## 1. Dijkstra contra métodos independientes');
        $this->linea('');

        // a) Grafo real de zonas contra Floyd-Warshall (algoritmo distinto)
        $nodos = $this->leer('nodes');
        $grafo = $this->leer('graph');
        $ids   = array_keys($nodos);
        $this->linea('**Grafo real** (tablas `ruta_nodo` y `ruta_arista`, solo los registros activos): ' . count($ids) . ' nodos, '
            . array_sum(array_map('count', $grafo)) . ' conexiones dirigidas.');

        $fw = $this->floydWarshall($ids, $grafo);
        $pares = 0;
        $difs = 0;
        $inalcanzables = 0;
        foreach ($ids as $a) {
            foreach ($ids as $b) {
                $this->propiedad('distanceCache', []);
                $d  = $this->llamar('distanciaEntre', $a, $b);
                $fe = $fw[$a][$b];
                $pares++;
                if ($d === null && !is_finite($fe)) {
                    $inalcanzables++;
                    continue;
                }
                if ($d === null || !is_finite($fe) || abs($d - $fe) > 1e-9) {
                    $difs++;
                }
            }
        }
        $this->ok($difs === 0, "Dijkstra coincide con Floyd-Warshall en los $pares pares de nodos del grafo real ("
            . "$inalcanzables sin camino, devueltos como null).");

        // b) Grafos pequeños aleatorios contra búsqueda exhaustiva de todos los caminos simples
        $grafos = 200;
        $mal = 0;
        $comparados = 0;
        for ($g = 0; $g < $grafos; $g++) {
            $n = mt_rand(4, 8);
            $nodosS = [];
            $grafoS = [];
            for ($i = 1; $i <= $n; $i++) {
                $nodosS[$i] = ['id' => $i];
            }
            $aristas = [];
            for ($i = 1; $i <= $n; $i++) {
                for ($j = $i + 1; $j <= $n; $j++) {
                    if (mt_rand(1, 100) <= 45) {
                        $w = mt_rand(1, 400) / 10;
                        $grafoS[$i][] = ['node' => $j, 'weight' => $w];
                        $grafoS[$j][] = ['node' => $i, 'weight' => $w];
                        $aristas[] = [$i, $j, $w];
                    }
                }
            }
            $this->propiedad('nodes', $nodosS);
            $this->propiedad('graph', $grafoS);
            for ($a = 1; $a <= $n; $a++) {
                for ($b = 1; $b <= $n; $b++) {
                    $this->propiedad('distanceCache', []);
                    $d = $this->llamar('distanciaEntre', $a, $b);
                    $exh = $this->caminoExhaustivo($grafoS, $a, $b);
                    $comparados++;
                    if (($d === null) !== ($exh === null) || ($d !== null && abs($d - $exh) > 1e-9)) {
                        $mal++;
                    }
                }
            }
        }
        $this->ok($mal === 0, "Dijkstra coincide con la búsqueda exhaustiva (todos los caminos posibles) en $grafos grafos aleatorios de 4 a 8 nodos ($comparados pares comparados).");

        // c) Caso a mano: el camino directo es más caro que el rodeo
        $this->propiedad('nodes', [1 => ['id' => 1], 2 => ['id' => 2], 3 => ['id' => 3], 4 => ['id' => 4]]);
        $this->propiedad('graph', [
            1 => [['node' => 2, 'weight' => 10.0], ['node' => 3, 'weight' => 3.0]],
            3 => [['node' => 2, 'weight' => 4.0], ['node' => 1, 'weight' => 3.0]],
            2 => [['node' => 1, 'weight' => 10.0], ['node' => 3, 'weight' => 4.0]],
        ]);
        $this->propiedad('distanceCache', []);
        $d12 = $this->llamar('distanciaEntre', 1, 2);
        $d14 = $this->llamar('distanciaEntre', 1, 4);
        $this->ok($d12 === 7.0, 'Caso a mano: de 1 a 2 hay una arista de 10 km, pero 1→3→2 cuesta 3+4 = 7 km; Dijkstra devuelve ' . $d12 . ' km.');
        $this->ok($d14 === null, 'Caso a mano: el nodo 4 está aislado y Dijkstra devuelve null (sin ruta disponible).');

        // Se restaura el grafo real para las pruebas siguientes
        $this->propiedad('nodes', $nodos);
        $this->propiedad('graph', $grafo);
        $this->propiedad('distanceCache', []);
        $this->linea('');
    }

    /** Distancias mínimas entre todos los pares (Floyd-Warshall) para contrastar con Dijkstra. */
    private function floydWarshall(array $ids, array $grafo): array
    {
        $d = [];
        foreach ($ids as $a) {
            foreach ($ids as $b) {
                $d[$a][$b] = $a === $b ? 0.0 : INF;
            }
        }
        foreach ($grafo as $a => $vecinos) {
            foreach ($vecinos as $v) {
                $d[$a][$v['node']] = min($d[$a][$v['node']], (float) $v['weight']);
            }
        }
        foreach ($ids as $k) {
            foreach ($ids as $i) {
                foreach ($ids as $j) {
                    if ($d[$i][$k] + $d[$k][$j] < $d[$i][$j]) {
                        $d[$i][$j] = $d[$i][$k] + $d[$k][$j];
                    }
                }
            }
        }
        return $d;
    }

    /** Menor costo probando TODOS los caminos simples de a a b (solo para grafos pequeños). */
    private function caminoExhaustivo(array $grafo, int $a, int $b): ?float
    {
        $mejor = INF;
        $visitados = [$a => true];
        $buscar = function (int $actual, float $costo) use (&$buscar, $grafo, $b, &$visitados, &$mejor): void {
            if ($actual === $b) {
                $mejor = min($mejor, $costo);
                return;
            }
            foreach ($grafo[$actual] ?? [] as $v) {
                if (isset($visitados[$v['node']])) {
                    continue;
                }
                $visitados[$v['node']] = true;
                $buscar($v['node'], $costo + $v['weight']);
                unset($visitados[$v['node']]);
            }
        };
        $buscar($a, 0.0);
        return is_finite($mejor) ? $mejor : null;
    }

    /* ----------------------------- 2. Árbol de expansión mínima ----------------------------- */

    private function pruebaArbol(): void
    {
        $this->linea('## 2. Árbol de expansión mínima (Prim)');
        $this->linea('');

        $fallas = 0;
        $casos = 0;
        $distintoKruskal = 0;
        for ($n = 2; $n <= 10; $n++) {
            for ($rep = 0; $rep < 25; $rep++) {
                $casos++;
                $puntos = $this->puntosAleatorios($n);
                $arbol = $this->llamar('arbolExpansionMinima', $puntos);
                $aristas = $arbol['aristas'];

                // N-1 aristas
                if (count($aristas) !== $n - 1) {
                    $fallas++;
                    continue;
                }
                // Conectado y sin ciclos (con union-find: cada arista debe unir dos grupos distintos)
                $padre = range(0, $n - 1);
                $buscar = function (int $x) use (&$padre, &$buscar): int {
                    return $padre[$x] === $x ? $x : ($padre[$x] = $buscar($padre[$x]));
                };
                $ciclo = false;
                foreach ($aristas as $ar) {
                    $ra = $buscar($ar['desde'] - 1);
                    $rb = $buscar($ar['hasta'] - 1);
                    if ($ra === $rb) {
                        $ciclo = true;
                        break;
                    }
                    $padre[$ra] = $rb;
                }
                $raices = [];
                for ($i = 0; $i < $n; $i++) {
                    $raices[$buscar($i)] = true;
                }
                if ($ciclo || count($raices) !== 1) {
                    $fallas++;
                    continue;
                }
                // Mismo costo total que Kruskal (otro algoritmo)
                $kruskal = $this->kruskal($puntos);
                if (abs($kruskal - $arbol['km_total']) > 0.01) {
                    $distintoKruskal++;
                }
            }
        }
        $this->ok($fallas === 0, "En $casos casos aleatorios (2 a 10 puntos) el árbol tiene exactamente N-1 aristas, une todos los puntos en un solo grupo y no tiene ciclos.");
        $this->ok($distintoKruskal === 0, 'El costo total del árbol de Prim es igual al de Kruskal (algoritmo independiente) en todos los casos.');

        $a = $this->llamar('arbolExpansionMinima', [['lat' => 19.0, 'lng' => -98.0], ['lat' => 19.0, 'lng' => -98.0]]);
        $this->ok(count($a['aristas']) === 1, 'Con 2 puntos el árbol tiene 1 arista.');
        $b = $this->llamar('arbolExpansionMinima', [['lat' => 19.0, 'lng' => -98.0]]);
        $this->ok(count($b['aristas']) === 0, 'Con 1 punto el árbol no tiene aristas.');
        $this->linea('');
    }

    private function kruskal(array $puntos): float
    {
        $n = count($puntos);
        $lista = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $lista[] = [$this->llamar('distanciaSimetrica', $puntos[$i], $puntos[$j]), $i, $j];
            }
        }
        usort($lista, static fn ($x, $y) => $x[0] <=> $y[0]);
        $padre = range(0, $n - 1);
        $buscar = function (int $x) use (&$padre, &$buscar): int {
            return $padre[$x] === $x ? $x : ($padre[$x] = $buscar($padre[$x]));
        };
        $total = 0.0;
        foreach ($lista as [$km, $i, $j]) {
            $ri = $buscar($i);
            $rj = $buscar($j);
            if ($ri !== $rj) {
                $padre[$ri] = $rj;
                $total += $km;
            }
        }
        return $total;
    }

    /** Puntos al azar alrededor del local (≈ 40 km a la redonda). */
    private function puntosAleatorios(int $n): array
    {
        $puntos = [];
        for ($i = 0; $i < $n; $i++) {
            $puntos[] = [
                'lat' => 19.2206760 + (mt_rand(-3000, 3000) / 10000),
                'lng' => -97.7784670 + (mt_rand(-3000, 3000) / 10000),
            ];
        }
        return $puntos;
    }

    /* ----------------------------- 3. Mejor orden ----------------------------- */

    private function pruebaMejorOrden(): void
    {
        $this->linea('## 3. El orden elegido es el de menor costo');
        $this->linea('');
        $local = ['lat' => 19.220676, 'lng' => -97.778467];
        $this->propiedad('matrizKm', []); // sin OSRM: km estimados con línea recta por el factor de carretera

        $mal = 0;
        $casos = 0;
        $maxDif = 0.0;
        foreach ([2, 3, 4, 5, 6, 7] as $n) {
            for ($rep = 0; $rep < 20; $rep++) {
                $casos++;
                $paradas = $this->puntosAleatorios($n);
                $elegido = $this->llamar('mejorOrden', $local, $paradas);
                $costoElegido = $this->llamar('costoRutaCoordenadas', $local, $elegido);
                $minimo = INF;
                foreach ($this->llamar('permutaciones', $paradas) as $perm) {
                    $minimo = min($minimo, $this->llamar('costoRutaCoordenadas', $local, $perm));
                }
                $dif = $costoElegido - $minimo;
                $maxDif = max($maxDif, $dif);
                // El sistema acepta hasta 0.5 km de diferencia para empezar por el punto más cercano
                if ($dif > 0.5 + 1e-9 || count($elegido) !== $n) {
                    $mal++;
                }
            }
        }
        $this->ok($mal === 0, "En $casos casos (2 a 7 puntos) el orden elegido cuesta como máximo "
            . number_format($maxDif, 3) . ' km más que el mínimo absoluto (el sistema tolera 0.5 km para empezar por la parada más cercana).');

        // Comparado con el orden en que se capturaron los pedidos
        $mejoras = 0;
        $empeoras = 0;
        for ($rep = 0; $rep < 100; $rep++) {
            $paradas = $this->puntosAleatorios(6);
            $orig = $this->llamar('costoRutaCoordenadas', $local, $paradas);
            $opt  = $this->llamar('costoRutaCoordenadas', $local, $this->llamar('mejorOrden', $local, $paradas));
            if ($opt <= $orig + 1e-9) {
                $mejoras++;
            } else {
                $empeoras++;
            }
        }
        $this->ok($empeoras === 0, "En 100 casos de 6 paradas el orden optimizado nunca es más largo que el orden de captura ($mejoras de 100 igual o mejor).");
        $this->linea('');
    }

    /* ----------------------------- 4. Tiempos de cálculo ----------------------------- */

    private function pruebaTiempos(): void
    {
        $this->linea('## 4. Tiempo de cálculo según el número de puntos');
        $this->linea('');
        $this->linea('Se mide el cálculo del mejor orden (sin consultar OSRM), promedio de 5 repeticiones (2 con 8 puntos). La memoria es el pico acumulado del proceso.');
        $this->linea('');
        $this->linea('| Puntos | Combinaciones evaluadas | Método | Tiempo promedio | Memoria máx. |');
        $this->linea('|---|---|---|---|---|');

        $local = ['lat' => 19.220676, 'lng' => -97.778467];
        $this->propiedad('matrizKm', []);
        foreach ([2, 4, 6, 7, 8, 10, 15] as $n) {
            $paradas = $this->puntosAleatorios($n);
            $veces = $n >= 8 && $n <= 8 ? 2 : 5;
            $t = [];
            for ($i = 0; $i < $veces; $i++) {
                $ini = microtime(true);
                $this->llamar('mejorOrden', $local, $paradas);
                $t[] = (microtime(true) - $ini) * 1000;
            }
            $prom = array_sum($t) / count($t);
            $exhaustivo = $n <= 8;
            $combos = $exhaustivo ? $this->factorial($n) : $n . ' (vecino más cercano)';
            $this->linea(sprintf(
                '| %d | %s | %s | %s | %.1f MB |',
                $n,
                is_int($combos) || is_float($combos) ? number_format($combos, 0, '.', ',') : $combos,
                $exhaustivo ? 'Todas las combinaciones' : 'Vecino más cercano (más de 8)',
                $prom < 1000 ? number_format($prom, 2) . ' ms' : number_format($prom / 1000, 2) . ' s',
                memory_get_peak_usage(true) / 1048576
            ));
        }
        $this->linea('');
        $this->linea('Con más de 8 puntos el sistema cambia solo al método del vecino más cercano para no tardar. Además, el máximo configurado por salida es de 6 pedidos.');
        $this->linea('');
    }

    private function factorial(int $n)
    {
        $r = 1;
        for ($i = 2; $i <= $n; $i++) {
            $r *= $i;
        }
        return $r;
    }

    /* ----------------------------- 5. Rutas reales ----------------------------- */

    private function tablaRutasReales(bool $usarOsrm): void
    {
        $this->linea('## 5. Rutas con direcciones reales de la base de datos');
        $this->linea('');

        $db = \Config\Database::connect();
        $filas = $db->table('direccion')->select('id, id_cliente, municipio, estado, lat, lng')
            ->where('lat IS NOT NULL', null, false)->where('lng IS NOT NULL', null, false)
            ->orderBy('id', 'ASC')->get()->getResultArray();
        $coord = $this->llamar('coordenadasLocal');
        if (!$coord || count($filas) < 3) {
            $this->linea('No hay suficientes direcciones con pin en el mapa para armar las rutas.');
            $this->linea('');
            return;
        }
        $local = ['lat' => (float) $coord['lat'], 'lng' => (float) $coord['lng']];
        $porId = [];
        foreach ($filas as $f) {
            $porId[(int) $f['id']] = $f;
        }

        $escenarios = [
            'Ruta A (Tlaxcala oriente)'   => [1, 4, 8],
            'Ruta B (Puebla sur y capital)' => [2, 3, 4],
            'Ruta C (las 5 direcciones)'  => [1, 2, 3, 4, 8],
        ];

        $this->linea('Local: ' . $local['lat'] . ', ' . $local['lng'] . '. '
            . ($usarOsrm ? 'Distancias por carretera de OSRM cuando responde.' : 'Distancias estimadas (sin OSRM).'));
        $this->linea('');
        $this->linea('| Ruta | Paradas | Orden de captura (km) | Orden optimizado (km) | Ahorro | Línea recta (km) | Fuente | Google Maps (km) | Diferencia |');
        $this->linea('|---|---|---|---|---|---|---|---|---|');

        $detalles = [];
        foreach ($escenarios as $nombre => $ids) {
            $paradas = [];
            foreach ($ids as $id) {
                if (isset($porId[$id])) {
                    $paradas[] = ['lat' => (float) $porId[$id]['lat'], 'lng' => (float) $porId[$id]['lng'], 'id' => $id];
                }
            }
            if (count($paradas) < 2) {
                continue;
            }
            $this->propiedad('matrizKm', []);
            if ($usarOsrm) {
                $this->llamar('prepararDistancias', array_merge([$local], $paradas));
            }
            $fuente = $usarOsrm ? (string) $this->leer('fuenteDistancia') : 'estimada';

            $orig = $this->llamar('costoRutaCoordenadas', $local, $paradas);
            $ordenOpt = $this->llamar('mejorOrden', $local, $paradas);
            $opt = $this->llamar('costoRutaCoordenadas', $local, $ordenOpt);

            $recta = 0.0;
            $ant = $local;
            foreach ($ordenOpt as $p) {
                $recta += $this->llamar('distanciaLineaRecta', $ant, $p);
                $ant = $p;
            }
            $recta += $this->llamar('distanciaLineaRecta', $ant, $local);

            $ahorro = $orig > 0 ? ($orig - $opt) / $orig * 100 : 0;
            $this->linea(sprintf(
                '| %s | %d | %.2f | %.2f | %.2f km (%.1f%%) | %.2f | %s | ______ | ______ |',
                $nombre, count($paradas), $orig, $opt, $orig - $opt, $ahorro, $recta, $fuente
            ));
            $detalles[$nombre] = [$ordenOpt, $local];
        }

        $this->linea('');
        $this->linea('Orden optimizado de cada ruta (para capturarlo igual en Google Maps):');
        $this->linea('');
        foreach ($detalles as $nombre => [$orden, $loc]) {
            $pasos = ['Local'];
            foreach ($orden as $p) {
                $pasos[] = 'dirección ' . $p['id'] . ' (' . number_format($p['lat'], 5) . ', ' . number_format($p['lng'], 5) . ')';
            }
            $pasos[] = 'Local';
            $this->linea('- **' . $nombre . ':** ' . implode(' → ', $pasos));
        }
        $this->linea('');
        $this->linea('Para llenar las dos últimas columnas: en Google Maps captura el mismo orden (origen y destino = local, paradas intermedias en el orden indicado), en modo auto, y anota los km totales.');
        $this->linea('');
    }
}
