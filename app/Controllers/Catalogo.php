<?php

namespace App\Controllers;

use App\Models\PedidoModel;
use App\Models\ProductoModel;
use App\Models\ProductoPedidoModel;
use App\Models\StatusModel;
use App\Services\AuditoriaService;
use App\Services\RutaRepartidorService;

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
            'entrega' => $this->datosEntrega(),
        ]);
    }

    /**
     * Datos para llenar el formulario de entrega: lo que el cliente escribió si hubo un error,
     * o si no su dirección y teléfono guardados. También trae el centro del mapa (el local).
     */
    private function datosEntrega(): array
    {
        $datos = [
            'logueado' => false, 'tipo' => 'tienda',
            'tel' => '', 'calle' => '', 'numero' => '', 'colonia' => '', 'municipio' => '', 'estado' => '',
            'lat' => '', 'lng' => '',
            'centro' => [19.0414, -98.2063],
        ];

        try {
            $db = \Config\Database::connect();

            if ($db->tableExists('ruta_configuracion')) {
                $cfg = array_column(
                    $db->table('ruta_configuracion')->whereIn('clave', ['local_lat', 'local_lng'])->get()->getResultArray(),
                    'valor', 'clave'
                );
                if (isset($cfg['local_lat'], $cfg['local_lng']) && is_numeric($cfg['local_lat']) && is_numeric($cfg['local_lng'])) {
                    $datos['centro'] = [(float) $cfg['local_lat'], (float) $cfg['local_lng']];
                }
            }

            if ($this->session->get('isLoggedIn') && $this->session->get('rol') === 'cliente') {
                $datos['logueado'] = true;

                $u = $db->table('usuarios')->select('id_cliente')->where('id', (int) $this->session->get('id_usuario'))->get()->getRowArray();
                $idCliente = (int) ($u['id_cliente'] ?? 0);
                if ($idCliente > 0) {
                    $c = $db->table('clientes')->select('tel')->where('id_cliente', $idCliente)->get()->getRowArray();
                    $d = $db->table('direccion')->where('id_cliente', $idCliente)->orderBy('id', 'DESC')->get()->getRowArray();
                    $datos['tel'] = (string) ($c['tel'] ?? '');
                    if ($d) {
                        foreach (['calle', 'numero', 'colonia', 'municipio', 'estado'] as $campo) {
                            $datos[$campo] = (string) ($d[$campo] ?? '');
                        }
                        $datos['lat'] = $d['lat'] !== null ? (string) $d['lat'] : '';
                        $datos['lng'] = $d['lng'] !== null ? (string) $d['lng'] : '';
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Carrito: no se pudo leer la dirección guardada: ' . $e->getMessage());
        }

        // Tras un intento fallido el punto del mapa va con lo que el cliente acababa de escribir: si no lo marcó,
        // no se reutiliza el de su dirección anterior (podría haber cambiado de calle y recibirlo en otro lugar)
        if ($this->request->getOldInput('tipo_entrega') !== null) {
            $datos['lat'] = (string) ($this->request->getOldInput('lat') ?? '');
            $datos['lng'] = (string) ($this->request->getOldInput('lng') ?? '');
        }

        // Lo que se escribió antes de un error pesa más que lo guardado
        foreach (['tel', 'calle', 'numero', 'colonia', 'municipio', 'estado', 'lat', 'lng'] as $campo) {
            $anterior = $this->request->getOldInput($campo);
            if ($anterior !== null && $anterior !== '') {
                $datos[$campo] = (string) $anterior;
            }
        }
        if ($this->request->getOldInput('tipo_entrega') === 'domicilio') {
            $datos['tipo'] = 'domicilio';
        }

        return $datos;
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
     * Convierte el carrito en un pedido de contado, para recoger en tienda o para entregar a domicilio
     * (el pago se hace al recoger o al recibir). El precio y las existencias se leen otra vez de la
     * base de datos: lo que quedó en la sesión pudo cambiar desde que el cliente agregó el producto.
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

        $total      = round($total, 2);
        $entrega    = (string) $this->request->getPost('tipo_entrega') === 'domicilio' ? 'domicilio' : 'tienda';
        $repartidor = null;

        try {
            $idCliente   = $this->clienteDelUsuario();
            $pedidoModel = new PedidoModel();

            // A domicilio: se valida y guarda la dirección y se elige al repartidor con la lógica de rutas
            if ($entrega === 'domicilio') {
                $repartidor = $this->prepararDomicilio($idCliente);
            }

            $resultado = $pedidoModel->crearPedidoCompleto([
                'fecha'         => date('Y-m-d H:i:s'),
                'id_cliente'    => $idCliente,
                'id_repartidor' => $repartidor['id'] ?? null,
                'tipo_entrega'  => $entrega,
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
                AuditoriaService::folio($idPedido) . ' para ' . (trim((string) $this->session->get('nombre')) ?: 'cliente')
                    . ': $' . number_format($total, 2)
                    . ($entrega === 'domicilio'
                        ? ' (contado, a domicilio con ' . $repartidor['nombre'] . ', desde el carrito)'
                        : ' (contado, recoger en tienda, desde el carrito)'),
                ['total' => $total, 'pagado' => 0, 'productos' => count($productos), 'origen' => 'carrito', 'entrega' => $entrega, 'repartidor' => $repartidor['id'] ?? null]
            );

            // Mismo mecanismo que en la venta del administrador: si hay existencias, se descuentan y el pedido queda confirmado
            $confirmacion = (new StatusModel())->validarYConfirmar($idPedido);
        } catch (\InvalidArgumentException $e) {
            // Problema con lo que escribió el cliente (dirección, teléfono, mapa): se muestra tal cual y se conserva lo capturado
            return redirect()->to(base_url('carrito'))->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Carrito: ' . $e->getMessage());
            return redirect()->to(base_url('carrito'))->withInput()->with('error', 'No se pudo registrar tu pedido. Intenta de nuevo.');
        }

        $this->session->remove(self::CLAVE);

        $folio      = AuditoriaService::folio($idPedido);
        $confirmado = !empty($confirmacion['success']);

        // A domicilio: directo al seguimiento, para que el cliente vea cómo avanza su pedido
        if ($entrega === 'domicilio') {
            $mensaje = $confirmado
                ? "Pedido {$folio} confirmado. {$repartidor['nombre']} te lo llevará a domicilio"
                    . ($repartidor['programado'] ? ' en la siguiente ruta de reparto.' : '.')
                    . ' Pagas al recibirlo.'
                : "Pedido {$folio} recibido. Estamos verificando las existencias.";

            return redirect()->to(base_url('cliente/seguimiento/' . $idPedido))->with('mensaje', $mensaje);
        }

        $mensaje = $confirmado
            ? "Pedido {$folio} confirmado. Pasa a recogerlo y pagarlo en tienda."
            : "Pedido {$folio} recibido. Estamos verificando las existencias.";

        return redirect()->to(base_url('ticket/ver/' . $idPedido))->with('mensaje', $mensaje);
    }

    /**
     * Prepara una entrega a domicilio: valida la dirección y el teléfono, guarda la dirección en la ficha
     * del cliente y elige al repartidor con la misma lógica de rutas que usa el administrador.
     *
     * Los mensajes de InvalidArgumentException se muestran tal cual al cliente.
     *
     * @return array{id: int, nombre: string, programado: bool}  programado = sin ruta cercana, sale en la siguiente salida
     */
    private function prepararDomicilio(int $idCliente): array
    {
        $texto = fn (string $campo, int $max): string => mb_substr(trim((string) $this->request->getPost($campo)), 0, $max);

        $calle     = $texto('calle', 200);
        $numero    = $texto('numero', 10);
        $colonia   = $texto('colonia', 100);
        $municipio = $texto('municipio', 100);
        $estado    = $texto('estado', 100);

        if ($calle === '' || $numero === '' || $colonia === '' || $estado === '') {
            throw new \InvalidArgumentException('Completa tu dirección: calle, número, colonia y estado.');
        }

        // El repartidor y el aviso de "ya llegué" necesitan un teléfono al que llamar
        $tel = preg_replace('/\D+/', '', (string) $this->request->getPost('tel')) ?? '';
        if (strlen($tel) === 12 && str_starts_with($tel, '52')) {
            $tel = substr($tel, 2);
        }
        if (strlen($tel) !== 10) {
            throw new \InvalidArgumentException('Escribe un teléfono de 10 dígitos para que el repartidor pueda comunicarse contigo.');
        }

        // Sin el punto exacto no se puede calcular la ruta: el repartidor no podría salir con este pedido
        $lat = $this->request->getPost('lat');
        $lng = $this->request->getPost('lng');
        if (!is_numeric($lat) || !is_numeric($lng) || abs((float) $lat) > 90 || abs((float) $lng) > 180
            || ((float) $lat === 0.0 && (float) $lng === 0.0)) {
            throw new \InvalidArgumentException('Marca en el mapa el punto exacto donde quieres recibir tu pedido.');
        }
        $lat = round((float) $lat, 7);
        $lng = round((float) $lng, 7);

        // Se guarda la dirección solo si es distinta de la última, para no llenar la ficha de copias
        $db     = \Config\Database::connect();
        $ultima = $db->table('direccion')->where('id_cliente', $idCliente)->orderBy('id', 'DESC')->get()->getRowArray();
        $igual  = $ultima
            && (string) $ultima['calle'] === $calle && (string) $ultima['numero'] === $numero
            && (string) $ultima['colonia'] === $colonia && (string) ($ultima['municipio'] ?? '') === $municipio
            && (string) $ultima['estado'] === $estado
            && round((float) $ultima['lat'], 6) === round($lat, 6) && round((float) $ultima['lng'], 6) === round($lng, 6);

        if (!$igual) {
            $db->table('direccion')->insert([
                'id_cliente' => $idCliente,
                'calle'      => $calle,
                'numero'     => $numero,
                'colonia'    => $colonia,
                'municipio'  => $municipio !== '' ? $municipio : null,
                'estado'     => $estado,
                'lat'        => $lat,
                'lng'        => $lng,
            ]);
        }
        $db->table('clientes')->where('id_cliente', $idCliente)->update(['tel' => $tel]);

        // Repartidor: el que menos kilómetros agrega a su ruta (con preferencia por quien cubre la zona)
        $ruta = new RutaRepartidorService();
        try {
            $sugerencia = $ruta->sugerir($idCliente);
        } catch (\Throwable $e) {
            log_message('error', 'Carrito: no se pudo calcular la ruta del cliente ' . $idCliente . ': ' . $e->getMessage());
            throw new \InvalidArgumentException('No pudimos calcular la ruta a esa dirección. Revisa el punto del mapa o elige recoger en tienda.');
        }

        $elegido    = $sugerencia['recomendado'] ?? null;
        $programado = false;
        if ($elegido === null) {
            // Ninguno queda dentro del rango ideal: el pedido sale en la siguiente ruta del repartidor que menos desvío tenga
            foreach ($sugerencia['candidatos'] as $candidato) {
                if ($candidato['distancia_extra'] !== null) {
                    $elegido    = $candidato;
                    $programado = true;
                    break;
                }
            }
        }

        $sinRepartidor = 'Por ahora no hay repartidores disponibles para tu dirección. Puedes elegir recoger tu pedido en tienda.';
        if ($elegido === null) {
            throw new \InvalidArgumentException($sinRepartidor);
        }

        $validacion = $ruta->validarAsignacion((int) $elegido['id']);
        if (empty($validacion['ok'])) {
            throw new \InvalidArgumentException($sinRepartidor);
        }

        return [
            'id'         => (int) $elegido['id'],
            'nombre'     => trim(explode(' ', trim((string) $elegido['nombre']))[0]) ?: 'Tu repartidor',
            'programado' => $programado,
        ];
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
        // Vacíos (no NULL): varias pantallas arman el nombre con CONCAT y un NULL lo dejaría en blanco
        $materno = '';
        $paterno = '';
        if (count($partes) >= 3) {
            $materno = (string) array_pop($partes);
            $paterno = (string) array_pop($partes);
        } elseif (count($partes) === 2) {
            $paterno = (string) array_pop($partes);
        }
        $nombre = implode(' ', $partes);

        $db->table('clientes')->insert([
            'nombre'           => mb_substr($nombre !== '' ? $nombre : 'Cliente', 0, 50),
            'apellido_paterno' => mb_substr($paterno, 0, 50),
            'apellido_materno' => mb_substr($materno, 0, 50),
            'tipo_cliente'     => 'menudeo',
        ]);
        $idCliente = (int) $db->insertID();

        $db->table('usuarios')->where('id', (int) $usuario['id'])->update(['id_cliente' => $idCliente]);

        return $idCliente;
    }

    /**
     * Botón "Regresar": lleva al panel principal de quien tiene la sesión (no la cierra).
     * Sin sesión, a la portada.
     */
    private function urlRegresar(): string
    {
        if (!$this->session->get('isLoggedIn')) {
            return base_url('/');
        }

        $paneles = [
            'admin'      => 'menusolo',
            'vendedor'   => 'vendedor/panel',
            'repartidor' => 'repartidor/panel',
            'cliente'    => 'cliente/menu',
        ];

        return base_url($paneles[$this->session->get('rol')] ?? 'cliente/menu');
    }
}