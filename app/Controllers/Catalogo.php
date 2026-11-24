<?php

namespace App\Controllers;

use App\Models\PedidoModel;
use App\Models\ProductoModel;
use App\Models\ProductoPedidoModel;
use App\Models\StatusModel;
use App\Services\AuditoriaService;

class Catalogo extends BaseController
{
    private $session;
    private $productoModel;

    // Clave de la sesión donde vive el carrito (equivale a 'libro_carrito' del manual)
    private const CLAVE = 'carrito_pedido';

    public function __construct()
    {
        $this->session       = session();
        $this->productoModel = new ProductoModel();
    }


    /**
     * Producto + existencias (e_total) + precio y unidad de la entrada más reciente.
     * Las columnas e_total / precio / unidad_venta NO están en la tabla producto.
     */
    private function consultaProductos()
    {
        return $this->productoModel
            ->select("producto.*,
                COALESCE(existencias.e_total, 0) AS e_total,
                COALESCE(existencias.e_bloqueo, 0) AS e_bloqueo,
                (COALESCE(existencias.e_total, 0) - COALESCE(existencias.e_bloqueo, 0)) AS stock_disponible,
                (SELECT e.precio_sugerido FROM entrada e WHERE e.id_producto = producto.id ORDER BY e.fecha DESC, e.id DESC LIMIT 1) AS precio,
                (SELECT e.unidad_venta FROM entrada e WHERE e.id_producto = producto.id ORDER BY e.fecha DESC, e.id DESC LIMIT 1) AS unidad_venta", false)
            ->join('existencias', 'existencias.id_producto = producto.id', 'left');
    }

    private function buscarProducto($id)
    {
        return $this->consultaProductos()->where('producto.id', $id)->first();
    }

    /** Catálogo con búsqueda y paginación (cliente y vendedor) */
    public function index()
    {
        $q = $this->request->getGet('q');

        $this->consultaProductos();

        if (!empty($q)) {
            $this->productoModel->groupStart()
                ->like('producto.nombre', $q)
                ->orLike('producto.descripcion', $q)
                ->groupEnd();
        }

        $productos = $this->productoModel->paginate(8);
        $carrito   = $this->session->get(self::CLAVE) ?? [];

        foreach ($productos as &$p) {
            $enCarrito         = (float) ($carrito[$p['id']]['cantidad'] ?? 0);
            $p['en_carrito']   = $enCarrito;
            // disponible = (e_total - e_bloqueo) - lo que ya está en el carrito
            $p['disponible']   = max(0, (float) $p['stock_disponible'] - $enCarrito);
        }
        unset($p);

        $data = [
            'productos'    => $productos,
            'pager'        => $this->productoModel->pager,
            'q'            => $q,
            'totalItems'   => count($this->session->get(self::CLAVE) ?? []),
            'urlRegresar'  => $this->urlRegresar(),
        ];

        return view('catalogo_cliente', $data);
    }

    /** Agrega un producto al carrito validando existencias */
    public function agregar()
    {
        $id       = (int) $this->request->getPost('id');
        $cantidad = (float) $this->request->getPost('cantidad');

        $producto = $this->buscarProducto($id);

        if (!$producto) {
            return redirect()->back()->with('error', 'El producto no existe.');
        }

        $stock = (float) ($producto['stock_disponible'] ?? 0);

        if ($cantidad <= 0 || $cantidad > $stock) {
            return redirect()->back()->with('error', "Cantidad no disponible (máximo: $stock).");
        }

        $carrito = $this->session->get(self::CLAVE) ?? [];

        if (isset($carrito[$id])) {
            $nueva = $carrito[$id]['cantidad'] + $cantidad;
            if ($nueva > $stock) {
                return redirect()->back()->with('error', 'No puedes agregar más: supera las existencias.');
            }
            $carrito[$id]['cantidad'] = $nueva;
            $carrito[$id]['subtotal'] = $nueva * $carrito[$id]['precio'];
        } else {
            $precio = (float) ($producto['precio'] ?? 0);
            $carrito[$id] = [
                'id'       => $producto['id'],
                'nombre'   => $producto['nombre'],
                'imagen'   => $producto['imagen'],
                'unidad'   => $producto['unidad_venta'] ?? 'pieza',
                'precio'   => $precio,
                'cantidad' => $cantidad,
                'subtotal' => $cantidad * $precio,
            ];
        }

        $this->session->set(self::CLAVE, $carrito);

        return redirect()->back()->with('mensaje', '"' . $producto['nombre'] . '" agregado al carrito.');
    }

    /** Muestra el carrito y el total */
    public function ver()
    {
        $carrito = $this->session->get(self::CLAVE) ?? [];
        $total   = array_sum(array_column($carrito, 'subtotal'));

        return view('carrito_cliente', [
            'carrito' => $carrito,
            'total'   => $total,
        ]);
    }

    /** Cambia la cantidad de un renglón */
    public function actualizar($id)
    {
        $cantidad = (float) $this->request->getPost('cantidad');
        $carrito  = $this->session->get(self::CLAVE) ?? [];

        if (!isset($carrito[$id])) {
            return redirect()->to(base_url('carrito'));
        }

        $producto = $this->buscarProducto($id);
        $stock    = (float) ($producto['stock_disponible'] ?? 0);

        if ($cantidad <= 0) {
            unset($carrito[$id]);
        } elseif ($cantidad > $stock) {
            return redirect()->to(base_url('carrito'))->with('error', "Solo hay $stock disponibles.");
        } else {
            $carrito[$id]['cantidad'] = $cantidad;
            $carrito[$id]['subtotal'] = $cantidad * $carrito[$id]['precio'];
        }

        $this->session->set(self::CLAVE, $carrito);
        return redirect()->to(base_url('carrito'))->with('mensaje', 'Carrito actualizado.');
    }

    public function quitar($id)
    {
        $carrito = $this->session->get(self::CLAVE) ?? [];
        unset($carrito[$id]);
        $this->session->set(self::CLAVE, $carrito);

        return redirect()->to(base_url('carrito'))->with('mensaje', 'Producto quitado.');
    }

    public function vaciar()
    {
        $this->session->remove(self::CLAVE);
        return redirect()->to(base_url('carrito'))->with('mensaje', 'Carrito vaciado.');
    }

    /**
     * Convierte el carrito en un pedido de contado para recoger en tienda.
     * El precio y las existencias se leen otra vez de la base de datos: lo que quedó en la
     * sesión pudo cambiar desde que el cliente agregó el producto.
     */
    public function confirmar()
    {
        $carrito = $this->session->get(self::CLAVE) ?? [];

        if (!$carrito) {
            return redirect()->to(base_url('carrito'))->with('error', 'Tu carrito está vacío.');
        }

        $productos = [];
        $total     = 0.0;

        foreach ($carrito as $id => $item) {
            $nombre   = (string) ($item['nombre'] ?? 'Un producto');
            $producto = $this->buscarProducto((int) $id);

            if (!$producto) {
                return redirect()->to(base_url('carrito'))->with('error', '"' . $nombre . '" ya no está disponible. Quítalo del carrito.');
            }

            $precio   = (float) ($producto['precio'] ?? 0);
            $cantidad = (float) ($item['cantidad'] ?? 0);
            $stock    = (float) ($producto['stock_disponible'] ?? 0);

            if ($precio <= 0) {
                return redirect()->to(base_url('carrito'))->with('error', '"' . $nombre . '" todavía no tiene precio. Quítalo del carrito.');
            }
            if ($cantidad <= 0 || $cantidad > $stock) {
                $maximo = rtrim(rtrim(number_format($stock, 2, '.', ''), '0'), '.');
                return redirect()->to(base_url('carrito'))->with('error', 'De "' . $nombre . '" solo hay ' . ($maximo === '' ? '0' : $maximo) . ' disponibles. Ajusta la cantidad.');
            }

            $productos[] = [
                'id_producto'  => (int) $producto['id'],
                'cantidad'     => $cantidad,
                'precio_venta' => $precio,
                'unidad'       => $producto['unidad_venta'] ?? 'pieza',
            ];
            $total += $cantidad * $precio;
        }

        $total = round($total, 2);

        try {
            $idCliente   = $this->clienteDelUsuario();
            $pedidoModel = new PedidoModel();

            $resultado = $pedidoModel->crearPedidoCompleto([
                'fecha'         => date('Y-m-d H:i:s'),
                'id_cliente'    => $idCliente,
                'id_repartidor' => null,
                'tipo_entrega'  => 'tienda',
                'total'         => $total,
                'estado_actual' => 'Pedido',
                'tipo_pago'     => 'contado',
                'monto_pagado'  => 0,
                'prioritario'   => 0,
            ], $productos, new ProductoPedidoModel());

            if (empty($resultado['ok'])) {
                log_message('error', 'Carrito: no se pudo registrar el pedido del cliente: ' . ($resultado['error']['message'] ?? 'sin detalle'));
                return redirect()->to(base_url('carrito'))->with('error', 'No se pudo registrar tu pedido. Intenta de nuevo.');
            }

            $idPedido = (int) $resultado['idPedido'];

            AuditoriaService::registrar(
                'pedido.crear', 'pedido', $idPedido,
                AuditoriaService::folio($idPedido) . ' para ' . $pedidoModel->getNombreCliente($idCliente)
                    . ': $' . number_format($total, 2) . ' (contado, recoger en tienda, desde el carrito)',
                ['total' => $total, 'pagado' => 0, 'productos' => count($productos), 'origen' => 'carrito']
            );

            // Mismo mecanismo que en la venta del administrador: si hay existencias, se descuentan y el pedido queda confirmado
            $confirmacion = (new StatusModel())->validarYConfirmar($idPedido);
        } catch (\Throwable $e) {
            log_message('error', 'Carrito: ' . $e->getMessage());
            return redirect()->to(base_url('carrito'))->with('error', 'No se pudo registrar tu pedido. Intenta de nuevo.');
        }

        $this->session->remove(self::CLAVE);

        $folio   = AuditoriaService::folio($idPedido);
        $mensaje = !empty($confirmacion['success'])
            ? "Pedido {$folio} confirmado. Pasa a recogerlo y pagarlo en tienda."
            : "Pedido {$folio} recibido. Estamos verificando las existencias.";

        return redirect()->to(base_url('ticket/ver/' . $idPedido))->with('mensaje', $mensaje);
    }

    /**
     * Ficha de cliente (tabla clientes) de la cuenta que inició sesión.
     * Si todavía no tiene, se crea con su nombre y queda vinculada a su usuario.
     */
    private function clienteDelUsuario(): int
    {
        $db      = \Config\Database::connect();
        $usuario = $db->table('usuarios')
            ->select('id, nombre, id_cliente')
            ->where('id', (int) $this->session->get('id_usuario'))
            ->get()->getRowArray();

        if (!$usuario) {
            throw new \RuntimeException('No se encontró la cuenta del cliente.');
        }

        if (!empty($usuario['id_cliente'])
            && $db->table('clientes')->where('id_cliente', (int) $usuario['id_cliente'])->countAllResults() > 0) {
            return (int) $usuario['id_cliente'];
        }

        // "Gabriel Ramirez Coyotecatl" -> nombre, apellido paterno y materno
        $partes = preg_split('/\s+/', trim((string) $usuario['nombre'])) ?: [];
        $materno = '';
        $paterno = null;
        if (count($partes) >= 3) {
            $materno = (string) array_pop($partes);
            $paterno = (string) array_pop($partes);
        } elseif (count($partes) === 2) {
            $paterno = (string) array_pop($partes);
        }
        $nombre = implode(' ', $partes);

        $db->table('clientes')->insert([
            'nombre'           => mb_substr($nombre !== '' ? $nombre : 'Cliente', 0, 50),
            'apellido_paterno' => $paterno !== null ? mb_substr($paterno, 0, 50) : null,
            'apellido_materno' => mb_substr($materno, 0, 50),
            'tipo_cliente'     => 'menudeo',
        ]);
        $idCliente = (int) $db->insertID();

        $db->table('usuarios')->where('id', (int) $usuario['id'])->update(['id_cliente' => $idCliente]);

        return $idCliente;
    }

    /**
     * Botón "Regresar": ajusta la condición a como guardes la sesión del vendedor.
     */
    private function urlRegresar(): string
    {
        return $this->session->get('usuario') ? base_url('menusolo') : base_url('/');
    }
}