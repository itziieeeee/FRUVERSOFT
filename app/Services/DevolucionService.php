<?php
namespace App\Services;

use App\Models\ExistenciasModel;
use App\Models\StatusModel;

/**
 * DEVOLUCIONES DE CLIENTES
 * ------------------------------------------------------------
 * Cuando un cliente regresa producto:
 *  - Las líneas del pedido se reducen (si se regresa todo, se quitan), el total del pedido baja y el ticket queda con lo que de verdad se vendió.
 *  - El producto regresa al inventario, o se manda a merma si ya no sirve (la pérdida queda en el reporte de costos).
 *  - Si el cliente ya había pagado más de lo que ahora vale el pedido, la diferencia se reembolsa (sale de caja).
 *    Si el pedido es a crédito y no alcanza a cubrirse, simplemente baja el saldo que debe.
 *  - Todo queda en el historial de cambios.
 */
class DevolucionService
{
    /** Estados en los que ya salió mercancía del inventario y tiene sentido una devolución */
    public const ESTADOS_PERMITIDOS = ['Pedido confirmado', 'Pedido en tránsito', 'Venta confirmada', 'Pedido a crédito', 'Pedido pagado'];

    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /** Datos del pedido y de lo que se puede devolver */
    public function pedido(int $idPedido): ?array
    {
        $p = $this->db->query(
            "SELECT p.id, p.total, p.monto_pagado, p.tipo_pago, p.estado_actual, p.stock_descontado,
                    CONCAT_WS(' ', c.nombre, c.apellido_paterno) AS cliente
               FROM pedido p LEFT JOIN clientes c ON c.id_cliente = p.id_cliente
              WHERE p.id = ?",
            [$idPedido]
        )->getRowArray();

        if (!$p) {
            return null;
        }

        $motivoBloqueo = null;
        if (!in_array($p['estado_actual'], self::ESTADOS_PERMITIDOS, true)) {
            $motivoBloqueo = $p['estado_actual'] === 'Pedido cancelado'
                ? 'El pedido está cancelado.'
                : 'El pedido todavía no está confirmado: si no se va a entregar, cancélalo.';
        } elseif ((int) $p['stock_descontado'] !== 1) {
            $motivoBloqueo = 'Este pedido no descontó inventario, así que no hay nada que devolver.';
        }

        $lineas = $this->db->query(
            'SELECT pp.id AS id_linea, pp.id_producto, pr.nombre, pp.cantidad, pp.unidad_venta AS unidad, pp.precio_venta
               FROM producto_pedido pp JOIN producto pr ON pr.id = pp.id_producto
              WHERE pp.id_pedido = ? ORDER BY pp.id',
            [$idPedido]
        )->getResultArray();

        return [
            'id'          => (int) $p['id'],
            'folio'       => AuditoriaService::folio($p['id']),
            'cliente'     => $p['cliente'] ?: 'Público general',
            'total'       => (float) $p['total'],
            'pagado'      => (float) $p['monto_pagado'],
            'saldo'       => round((float) $p['total'] - (float) $p['monto_pagado'], 2),
            'tipo_pago'   => $p['tipo_pago'],
            'estado'      => $p['estado_actual'],
            'bloqueado'   => $motivoBloqueo,
            'lineas'      => array_map(static fn ($l) => [
                'id_linea'     => (int) $l['id_linea'],
                'id_producto'  => (int) $l['id_producto'],
                'nombre'       => $l['nombre'],
                'cantidad'     => (float) $l['cantidad'],
                'unidad'       => $l['unidad'],
                'precio_venta' => (float) $l['precio_venta'],
            ], $lineas),
        ];
    }

    /**
     * @param array  $items  [['id_linea' => 12, 'cantidad' => 1.5, 'destino' => 'inventario'|'merma'], ...]
     * @return array{id: int, total_devuelto: float, reembolso: float, nuevo_total: float, cancelado: bool}
     * @throws \RuntimeException con un mensaje que se puede mostrar al usuario
     */
    public function crear(int $idPedido, array $items, string $motivo, string $metodo): array
    {
        $info = $this->pedido($idPedido);
        if (!$info) {
            throw new \RuntimeException('El pedido no existe.');
        }
        if ($info['bloqueado']) {
            throw new \RuntimeException($info['bloqueado']);
        }

        $porLinea = [];
        foreach ($info['lineas'] as $l) {
            $porLinea[$l['id_linea']] = $l;
        }

        // ---- validar todo antes de tocar nada ----
        $detalles = [];
        $totalDevuelto = 0.0;
        $usadas = [];
        foreach ($items as $it) {
            $idLinea  = (int) ($it['id_linea'] ?? 0);
            $cantidad = round((float) ($it['cantidad'] ?? 0), 2);
            $destino  = ($it['destino'] ?? 'inventario') === 'merma' ? 'merma' : 'inventario';
            if ($cantidad <= 0) {
                continue;
            }
            if (!isset($porLinea[$idLinea]) || isset($usadas[$idLinea])) {
                throw new \RuntimeException('Una de las líneas no pertenece al pedido.');
            }
            $usadas[$idLinea] = true;

            $linea = $porLinea[$idLinea];
            if ($cantidad > $linea['cantidad'] + 0.001) {
                throw new \RuntimeException('No puedes devolver más de lo que se vendió de ' . $linea['nombre'] . ' (' . $linea['cantidad'] . ').');
            }

            $subtotal = round($cantidad * $linea['precio_venta'], 2);
            $totalDevuelto += $subtotal;
            $detalles[] = $linea + ['devolver' => $cantidad, 'destino' => $destino, 'subtotal_dev' => $subtotal];
        }
        if (!$detalles) {
            throw new \RuntimeException('Escribe la cantidad de al menos un producto a devolver.');
        }

        $motivo = mb_substr(trim($motivo), 0, 300);
        $folio  = $info['folio'];
        $nuevoTotal = round($info['total'] - $totalDevuelto, 2);
        $exceso     = max(0.0, round($info['pagado'] - $nuevoTotal, 2));   // lo que ya pagó de más
        $metodo     = PagoService::metodoValido($metodo);

        $idUsuario = null;
        $usuario   = 'Sistema';
        if (!is_cli()) {
            $s = session();
            $idUsuario = $s->get('id_usuario') !== null ? (int) $s->get('id_usuario') : null;
            $usuario   = (string) ($s->get('nombre') ?: 'Sistema');
        }

        $existencias = new ExistenciasModel();

        $this->db->transBegin();
        try {
            // ---- 1. reducir o quitar las líneas del pedido ----
            foreach ($detalles as $d) {
                $quedan = round($d['cantidad'] - $d['devolver'], 2);
                if ($quedan <= 0.001) {
                    $this->db->table('producto_pedido')->where('id', $d['id_linea'])->delete();
                } else {
                    $sub = round($quedan * $d['precio_venta'], 2);
                    $this->db->table('producto_pedido')->where('id', $d['id_linea'])
                        ->update(['cantidad' => $quedan, 'subtotal' => $sub, 'total' => $sub]);
                }
            }

            // ---- 2. el producto regresa al inventario o se manda a merma ----
            foreach ($detalles as $d) {
                if ($d['destino'] === 'inventario') {
                    $existencias->agregarOActualizarStock($d['id_producto'], $d['devolver']);
                } else {
                    $lote = $this->db->table('entrada')->select('id')->where('id_producto', $d['id_producto'])->orderBy('id', 'DESC')->get()->getRowArray();
                    if ($lote) {
                        $this->db->table('merma')->insert([
                            'id_entrada' => (int) $lote['id'],
                            'cantidad'   => $d['devolver'],
                            'fecha'      => date('Y-m-d H:i:s'),
                            'motivo'     => 'DEVOLUCIÓN DE CLIENTE (' . $folio . ')' . ($motivo !== '' ? ': ' . $motivo : ''),
                            'aplicada'   => 1,
                        ]);
                    }
                    $this->db->table('existencias')->where('id_producto', $d['id_producto'])
                        ->set('e_merma', 'e_merma + ' . (float) $d['devolver'], false)->update();
                }
            }

            // ---- 3. nuevo total y pago del pedido ----
            $cambios = [
                'total'        => $nuevoTotal,
                'monto_pagado' => min($info['pagado'], $nuevoTotal),
            ];
            if ($info['tipo_pago'] === 'credito' && $cambios['monto_pagado'] >= $nuevoTotal - 0.001) {
                $cambios['tipo_pago'] = 'contado';          // con la devolución el crédito quedó liquidado
            }
            $this->db->table('pedido')->where('id', $idPedido)->update($cambios);

            if ($exceso > 0.004) {
                (new PagoService($this->db))->registrar($idPedido, -$exceso, $metodo, 'devolucion', 'Reembolso por devolución de ' . $folio);
            }

            // ---- 4. historial de la devolución ----
            $this->db->table('devolucion')->insert([
                'id_pedido'        => $idPedido,
                'id_usuario'       => $idUsuario,
                'usuario'          => mb_substr($usuario, 0, 120),
                'fecha'            => date('Y-m-d H:i:s'),
                'motivo'           => $motivo !== '' ? $motivo : null,
                'total'            => round($totalDevuelto, 2),
                'reembolso'        => $exceso,
                'metodo_reembolso' => $exceso > 0.004 ? $metodo : null,
            ]);
            $idDevolucion = (int) $this->db->insertID();

            foreach ($detalles as $d) {
                $this->db->table('devolucion_detalle')->insert([
                    'id_devolucion' => $idDevolucion,
                    'id_producto'   => $d['id_producto'],
                    'cantidad'      => $d['devolver'],
                    'unidad_venta'  => $d['unidad'],
                    'precio_venta'  => $d['precio_venta'],
                    'subtotal'      => $d['subtotal_dev'],
                    'destino'       => $d['destino'],
                ]);
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('No se pudo guardar la devolución.');
            }
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        // ---- 5. si ya no queda nada en el pedido, se cancela; si era crédito y quedó pagado, se marca pagado ----
        $quedanLineas = (int) $this->db->table('producto_pedido')->where('id_pedido', $idPedido)->countAllResults();
        $cancelado = false;
        $status = new StatusModel();
        if ($quedanLineas === 0) {
            $status->actualizarEstado($idPedido, 'Pedido cancelado');
            $cancelado = true;
        } elseif ($info['estado'] === 'Pedido a crédito' && ($cambios['tipo_pago'] ?? '') === 'contado') {
            $status->actualizarEstado($idPedido, 'Pedido pagado');
        }

        AuditoriaService::registrar(
            'devolucion.crear', 'pedido', $idPedido,
            $folio . ': devolución de $' . number_format($totalDevuelto, 2) . ' (' . count($detalles) . ' producto' . (count($detalles) === 1 ? '' : 's') . ')'
                . ($exceso > 0.004 ? ', reembolso $' . number_format($exceso, 2) . ' en ' . $metodo : ', baja el saldo del cliente')
                . ($motivo !== '' ? ' · ' . $motivo : ''),
            [
                'devolucion' => $idDevolucion,
                'productos'  => array_map(static fn ($d) => ['producto' => $d['nombre'], 'cantidad' => $d['devolver'], 'destino' => $d['destino']], $detalles),
                'total_antes' => $info['total'], 'total_despues' => $nuevoTotal, 'reembolso' => $exceso,
            ]
        );

        return [
            'id'             => $idDevolucion,
            'total_devuelto' => round($totalDevuelto, 2),
            'reembolso'      => $exceso,
            'nuevo_total'    => $nuevoTotal,
            'cancelado'      => $cancelado,
        ];
    }

    /** Historial para la pantalla de devoluciones */
    public function listar(int $limite = 300): array
    {
        $filas = $this->db->query(
            "SELECT d.id, d.id_pedido, d.fecha, d.motivo, d.total, d.reembolso, d.metodo_reembolso, d.usuario,
                    CONCAT_WS(' ', c.nombre, c.apellido_paterno) AS cliente,
                    (SELECT GROUP_CONCAT(CONCAT(TRIM(TRAILING '.' FROM TRIM(TRAILING '0' FROM dd.cantidad)), ' ', pr.nombre,
                                         IF(dd.destino = 'merma', ' (a merma)', '')) ORDER BY dd.id SEPARATOR ', ')
                       FROM devolucion_detalle dd JOIN producto pr ON pr.id = dd.id_producto
                      WHERE dd.id_devolucion = d.id) AS productos
               FROM devolucion d
               LEFT JOIN pedido p ON p.id = d.id_pedido
               LEFT JOIN clientes c ON c.id_cliente = p.id_cliente
              ORDER BY d.id DESC
              LIMIT " . (int) $limite
        )->getResultArray();

        $tz = new \DateTimeZone('America/Mexico_City');
        foreach ($filas as &$f) {
            $f['folio']       = AuditoriaService::folio($f['id_pedido']);
            $f['fecha_local'] = (new \DateTime($f['fecha'], new \DateTimeZone('UTC')))->setTimezone($tz)->format('d/m/Y H:i');
        }
        unset($f);

        return $filas;
    }
}
