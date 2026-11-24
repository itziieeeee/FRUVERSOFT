<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PedidoModel;
use App\Services\AuditoriaService;

/**
 * Cuenta del cliente: sus pedidos, créditos y el seguimiento de cada pedido.
 * Todas las pantallas muestran únicamente los pedidos de la ficha de cliente vinculada a la cuenta
 * (usuarios.id_cliente), que se crea la primera vez que el cliente confirma un pedido desde el carrito.
 */
class ClientePedidoController extends BaseController
{
    /** Estados en los que el pedido ya está confirmado (con el stock apartado) */
    private const ESTADOS_CONFIRMADO = ['Pedido confirmado', 'Pedido a crédito', 'Pedido pagado'];

    /** Con la ubicación más vieja que esto (minutos) no se muestra al repartidor en el mapa */
    private const MAX_MINUTOS_UBICACION = 15;

    /* ============================ listas ============================ */

    public function index()
    {
        return view('Cliente/vista_PedidoCliente', [
            'pedidos' => $this->conEstadoDePago($this->pedidosDelCliente()),
        ]);
    }

    public function menu()
    {
        $avisoModel = new \App\Models\AvisoModel();
        $data['avisos'] = $avisoModel->getAvisosCliente();

        return view('Cliente/menu', $data);
    }

    public function historial()
    {
        return view('Cliente/vista_historial', ['pedidos' => $this->pedidosDelCliente()]);
    }

    public function creditos()
    {
        return view('Cliente/vista_creditos', [
            'pedidos' => $this->conEstadoDePago($this->pedidosDelCliente()),
        ]);
    }

    public function estado()
    {
        return view('Cliente/vista_estado_pedidos', ['pedidos' => $this->pedidosDelCliente()]);
    }

    /* ============================ seguimiento de un pedido ============================ */

    /** Pantalla de seguimiento: estado del pedido y, si va en camino, la posición del repartidor */
    public function seguir($id)
    {
        $pedido = $this->pedidoDelCliente((int) $id);
        if (!$pedido) {
            return redirect()->to(base_url('cliente/estado'))->with('error', 'No encontramos ese pedido en tu cuenta.');
        }

        $productos = \Config\Database::connect()->query(
            'SELECT pr.nombre, pp.cantidad, pp.unidad_venta, pp.precio_venta, pp.subtotal
               FROM producto_pedido pp JOIN producto pr ON pr.id = pp.id_producto
              WHERE pp.id_pedido = ? ORDER BY pp.id',
            [(int) $pedido['id']]
        )->getResultArray();

        return view('Cliente/seguimiento_pedido', [
            'pedido'    => $pedido,
            'productos' => $productos,
            'pasos'     => $this->pasos($pedido['tipo_entrega']),
            'datos'     => $this->datosSeguimiento($pedido),
        ]);
    }

    /** Lo que la pantalla de seguimiento consulta cada pocos segundos */
    public function seguimientoDatos($id)
    {
        $pedido = $this->pedidoDelCliente((int) $id);
        if (!$pedido) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false]);
        }

        return $this->response
            ->setHeader('Cache-Control', 'no-store')
            ->setJSON(['success' => true, 'data' => $this->datosSeguimiento($pedido)]);
    }

    /* ============================ apoyo ============================ */

    private function idClienteSesion(): int
    {
        $fila = \Config\Database::connect()->table('usuarios')
            ->select('id_cliente')
            ->where('id', (int) session()->get('id_usuario'))
            ->get()->getRowArray();

        return (int) ($fila['id_cliente'] ?? 0);
    }

    private function pedidosDelCliente(): array
    {
        $idCliente = $this->idClienteSesion();
        return $idCliente > 0 ? (new PedidoModel())->getPedidosDeCliente($idCliente) : [];
    }

    /** Un pedido solo se devuelve si es de la ficha de cliente de esta cuenta */
    private function pedidoDelCliente(int $idPedido): ?array
    {
        $idCliente = $this->idClienteSesion();
        if ($idCliente <= 0 || $idPedido <= 0) {
            return null;
        }

        return \Config\Database::connect()->table('pedido')
            ->where('id', $idPedido)
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray() ?: null;
    }

    /** Etapas que ve el cliente; el envío a domicilio tiene una más (en camino) */
    private function pasos(string $tipoEntrega): array
    {
        $pasos = [
            ['texto' => 'Pedido recibido', 'icono' => 'fa-receipt'],
            ['texto' => $tipoEntrega === 'domicilio' ? 'Confirmado, preparando tu pedido' : 'Confirmado, listo para recoger', 'icono' => 'fa-box-open'],
        ];
        if ($tipoEntrega === 'domicilio') {
            $pasos[] = ['texto' => 'En camino', 'icono' => 'fa-truck-fast'];
        }
        $pasos[] = ['texto' => $tipoEntrega === 'domicilio' ? 'Entregado' : 'Entregado en tienda', 'icono' => 'fa-circle-check'];

        return $pasos;
    }

    /** Posición (desde 0) de la etapa en la que va el pedido; -1 si se canceló */
    private function posicion(string $estado, string $tipoEntrega): int
    {
        $ultima = count($this->pasos($tipoEntrega)) - 1;

        if ($estado === 'Pedido cancelado') {
            return -1;
        }
        if ($estado === 'Venta confirmada') {
            return $ultima;
        }
        if ($estado === 'Pedido en tránsito') {
            return $tipoEntrega === 'domicilio' ? 2 : 1;
        }
        return in_array($estado, self::ESTADOS_CONFIRMADO, true) ? 1 : 0;
    }

    /**
     * Estado del pedido y datos del mapa. La posición del repartidor solo se comparte mientras el
     * pedido va en camino y la ubicación es reciente; nunca se manda su teléfono ni otros datos.
     */
    private function datosSeguimiento(array $pedido): array
    {
        $db      = \Config\Database::connect();
        $estado  = (string) $pedido['estado_actual'];
        $tipo    = (string) $pedido['tipo_entrega'];
        $total   = (float) $pedido['total'];
        $pagado  = (float) $pedido['monto_pagado'];
        $posicion = $this->posicion($estado, $tipo);

        $datos = [
            'id'         => (int) $pedido['id'],
            'folio'      => AuditoriaService::folio($pedido['id']),
            'estado'     => $estado,
            'paso'       => $posicion,
            'cancelado'  => $posicion < 0,
            'entregado'  => $estado === 'Venta confirmada',
            'domicilio'  => $tipo === 'domicilio',
            'en_camino'  => $estado === 'Pedido en tránsito' && $tipo === 'domicilio',
            'saldo'      => round(max(0, $total - $pagado), 2),
            'mapa'       => null,
        ];

        if ($tipo !== 'domicilio' || $posicion < 0 || $datos['entregado']) {
            return $datos;
        }

        // Destino: el punto de la parada del recorrido; si todavía no hay, la dirección del cliente
        $destino = $db->query(
            'SELECT lat, lng, direccion FROM salida_pedido WHERE id_pedido = ? AND lat IS NOT NULL AND lng IS NOT NULL ORDER BY id DESC LIMIT 1',
            [(int) $pedido['id']]
        )->getRowArray();
        if (!$destino) {
            $destino = $db->query(
                "SELECT lat, lng, CONCAT_WS(', ', CONCAT(calle, ' #', numero), colonia, municipio) AS direccion
                   FROM direccion WHERE id_cliente = ? AND lat IS NOT NULL AND lng IS NOT NULL ORDER BY id DESC LIMIT 1",
                [(int) $pedido['id_cliente']]
            )->getRowArray();
        }

        $mapa = [
            'destino'      => $destino ? ['lat' => (float) $destino['lat'], 'lng' => (float) $destino['lng'], 'texto' => (string) $destino['direccion']] : null,
            'repartidor'   => null,
            'distancia_km' => null,
        ];

        if ($datos['en_camino'] && !empty($pedido['id_repartidor'])) {
            $r = $db->table('repartidor')
                ->select('nombre, ultima_lat, ultima_lng, ultima_actualizacion')
                ->where('id', (int) $pedido['id_repartidor'])
                ->get()->getRowArray();

            if ($r && $r['ultima_lat'] !== null && $r['ultima_lng'] !== null && !empty($r['ultima_actualizacion'])) {
                $minutos = max(0, (int) round((time() - strtotime((string) $r['ultima_actualizacion'])) / 60));
                if ($minutos <= self::MAX_MINUTOS_UBICACION) {
                    $mapa['repartidor'] = [
                        'nombre'  => trim(explode(' ', trim((string) $r['nombre']))[0]),
                        'lat'     => (float) $r['ultima_lat'],
                        'lng'     => (float) $r['ultima_lng'],
                        'minutos' => $minutos,
                    ];
                    if ($destino) {
                        $mapa['distancia_km'] = round($this->distanciaKm(
                            (float) $r['ultima_lat'], (float) $r['ultima_lng'], (float) $destino['lat'], (float) $destino['lng']
                        ), 1);
                    }
                }
            }
        }

        $datos['mapa'] = $mapa;
        return $datos;
    }

    /** Distancia en línea recta entre dos puntos, en km (Haversine) */
    private function distanciaKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $a1 = deg2rad($lat1);
        $a2 = deg2rad($lat2);
        $h  = sin(($a2 - $a1) / 2) ** 2 + cos($a1) * cos($a2) * sin(deg2rad($lng2 - $lng1) / 2) ** 2;

        return 2 * 6371.0 * asin(min(1.0, sqrt($h)));
    }

    /** Saldo pendiente y etiqueta del estado de pago de cada pedido */
    private function conEstadoDePago(array $pedidos): array
    {
        foreach ($pedidos as &$pedido) {
            $total  = (float) ($pedido['total'] ?? 0);
            $pagado = (float) ($pedido['monto_pagado'] ?? 0);
            $tipo   = $pedido['tipo_pago'] ?? 'contado';
            $saldo  = max(0, $total - $pagado);   // sin saldos negativos si se pagó de más

            $pedido['saldo_pendiente'] = $saldo;

            if ($tipo === 'credito') {
                if ($saldo <= 0) {
                    $pedido['estado_liquidado'] = 'Liquidado';
                    $pedido['badge_clase'] = 'bg-success';
                } elseif ($pagado > 0) {
                    $pedido['estado_liquidado'] = 'Pago parcial';
                    $pedido['badge_clase'] = 'bg-warning text-dark';
                } else {
                    $pedido['estado_liquidado'] = 'Pendiente de pago';
                    $pedido['badge_clase'] = 'bg-danger';
                }
            } elseif ($saldo <= 0) {
                $pedido['estado_liquidado'] = 'Pagado de contado';
                $pedido['badge_clase'] = 'bg-success';
            } else {
                $pedido['estado_liquidado'] = 'Contado pendiente';
                $pedido['badge_clase'] = 'bg-danger';
            }
        }
        unset($pedido);

        return $pedidos;
    }
}
