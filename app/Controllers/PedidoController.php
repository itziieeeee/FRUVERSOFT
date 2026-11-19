<?php
namespace App\Controllers;

use App\Models\PedidoModel;
use App\Controllers\BaseController;
use App\Services\RutaRepartidorService;

class PedidoController extends BaseController {

    public function pantalla_ventas() {
        $pedidoModel = new PedidoModel();

        $datos = [
            'secc1'        => $pedidoModel->getPedidosConCliente(),
            'productos'    => $pedidoModel->getProductosConPrecio(),
            'unidades'     => $pedidoModel->getUnidadesEnum(),
            'clientes'     => $pedidoModel->getClientes(),
            'repartidores' => $pedidoModel->getRepartidores(),
        ];

        return view('pantalla_ventas', $datos);

    }
    public function pantalla_ventas_vendedor()
{
    $pedidoModel = new PedidoModel();

    $datos = [
        'secc1'        => $pedidoModel->getPedidosConCliente(),
        'productos'    => $pedidoModel->getProductosConPrecio(),
        'unidades'     => $pedidoModel->getUnidadesEnum(),
        'clientes'     => $pedidoModel->getClientes(),
        'repartidores' => $pedidoModel->getRepartidores(),
        'esVendedor'   => true
    ];

    return view('pantalla_ventas', $datos);
}
 
public function pedidos_vendedor()
{
    $pedidoModel = new PedidoModel();

    $datos = [
        // Pedidos registrados
        'pedidos' => $pedidoModel->getPedidosVendedor(),

        // Productos reales con precios y existencias
        'productos' => $pedidoModel->getProductosConPrecio(),

        // Clientes registrados
        'clientes' => $pedidoModel->getClientes(),

        // Repartidores disponibles
        'repartidores' => $pedidoModel->getRepartidores()
    ];

    return view('vendedor/pedidos', $datos);
}


    /**
     * Calcula una sugerencia de repartidor usando el grafo de rutas persistido.
     */
    public function sugerir_repartidor()
    {
        $json = $this->request->getJSON(true) ?? [];
        $idCliente = (int) ($json['id_cliente'] ?? 0);

        if ($idCliente <= 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Selecciona un cliente con dirección para calcular la ruta.',
            ]);
        }

        try {
            $datos = (new RutaRepartidorService())->sugerir($idCliente);
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $datos,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Plan de salida del repartidor elegido en la venta, incluyendo la entrega que se está capturando.
     */
    public function plan_salida()
    {
        $json = $this->request->getJSON(true) ?? [];
        $idRepartidor = (int) ($json['id_repartidor'] ?? 0);
        $idCliente    = (int) ($json['id_cliente'] ?? 0);

        if ($idRepartidor <= 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Selecciona un repartidor para ver su ruta.',
            ]);
        }

        try {
            $plan = (new RutaRepartidorService())->planSalida(
                $idRepartidor,
                $idCliente > 0 ? $idCliente : null,
                null,
                !empty($json['prioritario'])
            );
            return $this->response->setJSON(['status' => 'success', 'data' => $plan]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Devuelve el orden recomendado de entregas para la salida de un repartidor.
     */
    public function ordenar_ruta($idRepartidor = null)
    {
        $idRepartidor = (int) $idRepartidor;
        if ($idRepartidor <= 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Repartidor inválido.',
            ]);
        }

        try {
            $datos = (new RutaRepartidorService())->planSalida($idRepartidor);
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $datos,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

public function guardar_productos_pedido()
{
    $json = $this->request->getJSON(true);

    // monto_pagado es opcional: la venta del administrador no lo manda (el pago se registra después)
    if (
        empty($json['productos']) ||
        !isset($json['id_cliente'])
    ) {
        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Datos incompletos.'
        ]);
    }

    $pedidoModel      = new \App\Models\PedidoModel();
    $productoPedModel = new \App\Models\ProductoPedidoModel();

    $idCliente    = (int) $json['id_cliente'];
    $tipoVenta    = $json['tipo_venta'] ?? 'contado';
    $tipoEntrega  = $json['tipo_entrega'] ?? 'tienda';
    $montoPagado  = (float) ($json['monto_pagado'] ?? 0);

    $idRepartidor = (
        $tipoEntrega === 'domicilio' &&
        !empty($json['id_repartidor'])
    ) ? (int) $json['id_repartidor'] : null;

    // Validar datos generales
    if (!in_array($tipoVenta, ['contado', 'credito'], true)) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Tipo de venta inválido.'
        ]);
    }

    if (!in_array($tipoEntrega, ['tienda', 'domicilio'], true)) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Tipo de entrega inválido.'
        ]);
    }

    if ($idCliente < 0 || $montoPagado < 0) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'El cliente o el monto pagado no son válidos.'
        ]);
    }

    // Validar entrega a domicilio
    if ($tipoEntrega === 'domicilio') {

        if ($idCliente <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Una entrega a domicilio requiere un cliente registrado.'
            ]);
        }

        if (!$idRepartidor) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Selecciona un repartidor.'
            ]);
        }

        try {
            $asignacion = (new RutaRepartidorService())
                ->validarAsignacion($idRepartidor);

            if (!$asignacion['ok']) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $asignacion['message']
                ]);
            }

        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No se pudo validar la ruta.'
            ]);
        }

    } else {
        $idRepartidor = null;
    }

    // Validar productos y calcular total
    $totalGeneral = 0;

    foreach ($json['productos'] as $prod) {

        if (
            empty($prod['id_producto']) ||
            !isset($prod['cantidad']) ||
            !isset($prod['precio_venta']) ||
            (float) $prod['cantidad'] <= 0 ||
            (float) $prod['precio_venta'] < 0
        ) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Uno de los productos contiene datos inválidos.'
            ]);
        }

        $totalGeneral +=
            (float) $prod['cantidad'] *
            (float) $prod['precio_venta'];
    }

    $totalGeneral = round($totalGeneral, 2);

    // El crédito puede tener un pago parcial.
    // El cambio solamente existe cuando el pago supera el total.
    $cambio = max(0, round($montoPagado - $totalGeneral, 2));
    $saldo  = max(0, round($totalGeneral - $montoPagado, 2));

    // Estado inicial
    $estadoInicial = ($tipoVenta === 'credito')
        ? 'Pedido a crédito'
        : 'Pedido';

    // Nombre del cliente
    $nombreCliente = ($idCliente === 0)
        ? 'Público general'
        : $pedidoModel->getNombreCliente($idCliente);

    if (!$nombreCliente) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'El cliente seleccionado no existe.'
        ]);
    }

    // Nombre del repartidor
    $nombreRepartidor = $idRepartidor
        ? $pedidoModel->getNombreRepartidor($idRepartidor)
        : null;

    // Urgente: solo tiene sentido en una entrega a domicilio
    $prioritario = ($tipoEntrega === 'domicilio' && !empty($json['prioritario'])) ? 1 : 0;

    // Datos del pedido
    $pedidoData = [
        'fecha'         => date('Y-m-d H:i:s'),
        // "Público general" (0) se guarda con un cliente real del sistema: pedido.id_cliente tiene llave foránea
        'id_cliente'    => $idCliente === 0 ? $pedidoModel->idClientePublicoGeneral() : $idCliente,
        'id_repartidor' => $idRepartidor,
        'tipo_entrega'  => $tipoEntrega,
        'total'         => $totalGeneral,
        'estado_actual' => $estadoInicial,
        'tipo_pago'     => $tipoVenta,
        'monto_pagado'  => min($montoPagado, $totalGeneral),
        'prioritario'   => $prioritario,
    ];

    // Guardar pedido
    $resultado = $pedidoModel->crearPedidoCompleto(
        $pedidoData,
        $json['productos'],
        $productoPedModel
    );

    if (empty($resultado['ok'])) {
        $detalle = trim((string) ($resultado['error']['message'] ?? ''));
        log_message('error', 'No se pudo registrar el pedido: ' . ($detalle !== '' ? $detalle : 'sin detalle de la base de datos'));

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'No se pudo registrar el pedido.' . ($detalle !== '' ? ' (' . $detalle . ')' : ''),
            'debug'   => $resultado['error'] ?? null
        ]);
    }

    $idPedido = $resultado['idPedido'];

    // Lo que el cliente pagó al momento de la venta entra a caja
    $pagadoAhora = min($montoPagado, $totalGeneral);
    if ($pagadoAhora > 0) {
        (new \App\Services\PagoService())->registrar(
            (int) $idPedido,
            $pagadoAhora,
            \App\Services\PagoService::metodoValido($json['metodo_pago'] ?? 'efectivo'),
            'venta'
        );
    }

    \App\Services\AuditoriaService::registrar(
        'pedido.crear', 'pedido', $idPedido,
        \App\Services\AuditoriaService::folio($idPedido) . ' para ' . $nombreCliente . ': $' . number_format($totalGeneral, 2)
            . ' (' . ($tipoVenta === 'credito' ? 'crédito' : 'contado') . ', ' . ($tipoEntrega === 'domicilio' ? 'domicilio' : 'tienda') . ')',
        ['total' => $totalGeneral, 'pagado' => $pagadoAhora, 'productos' => count($json['productos'])]
    );

    // Validar y descontar inventario usando el mecanismo existente
    $statusModel = new \App\Models\StatusModel();

    $validacion = $statusModel->validarYConfirmar($idPedido);

    $autoConfirmado = $validacion['success'] ?? false;
    $faltantes = $validacion['faltantes'] ?? [];

    // Folio
    $folio = 'PED-' . str_pad($idPedido, 5, '0', STR_PAD_LEFT);

    
    // WhatsApp: compartir enlace al ticket PDF
    $whatsappUrl = null;

    if ($idCliente > 0) {

        $db = \Config\Database::connect();

        $cliente = $db->table('clientes')
            ->select('tel, nombre, apellido_paterno')
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        if (!empty($cliente['tel'])) {

            // Preparar teléfono mexicano
            $telefono = preg_replace(
                '/\D+/',
                '',
                (string) $cliente['tel']
            );

            if (strlen($telefono) === 10) {
                $telefono = '52' . $telefono;
            }

            // Obtener URL base configurada en .env
            $basePublica = rtrim(
                (string) env('FRUVER_PUBLIC_URL', ''),
                '/'
            );

            // Construir enlace al PDF
            if ($basePublica !== '') {

                $urlPdf = $basePublica . '/ticket/pdf/' . $idPedido;

                $mensaje = "¡Hola, " .
                    trim($cliente['nombre'] . ' ' . $cliente['apellido_paterno']) .
                    "!\n\n";

                $mensaje .= "Gracias por comprar en FRUVER.\n";
                $mensaje .= "Tu pedido ha sido registrado correctamente.\n\n";
                $mensaje .= "Folio: {$folio}\n";
                $mensaje .= "Total: $" . number_format($totalGeneral, 2) . "\n\n";
                $mensaje .= "Consulta tu ticket en PDF aquí:\n";
                $mensaje .= $urlPdf;

                $whatsappUrl = 'https://wa.me/' .
                    $telefono .
                    '?text=' .
                    rawurlencode($mensaje);
            }
        }
    }


    // Respuesta
    return $this->response->setJSON([
        'status' => 'success',
        'data' => [
            'folio'           => $folio,
            'id_pedido'       => (int) $idPedido,
            'cliente'         => $nombreCliente,
            'tipo_venta'      => $tipoVenta,
            'tipo_entrega'    => $tipoEntrega,
            'repartidor'      => $nombreRepartidor,
            'total'           => number_format($totalGeneral, 2, '.', ''),
            'monto_pagado'    => number_format(min($montoPagado, $totalGeneral), 2, '.', ''),
            'cambio'          => number_format($cambio, 2, '.', ''),
            'saldo'           => number_format($saldo, 2, '.', ''),
            'auto_confirmado' => $autoConfirmado,
            'faltantes'       => $faltantes,
            'whatsapp_url'    => $whatsappUrl
        ]
    ]);

    }
    public function eliminarPedido($id) {
        $pedidoModel = new PedidoModel();

        try {
            // Se guarda cómo era el pedido antes de borrarlo, para el historial de cambios
            $antes = \Config\Database::connect()->table('pedido')->select('total, monto_pagado, estado_actual, id_cliente')->where('id', $id)->get()->getRowArray();

            $ok = $pedidoModel->eliminarPedidoCompleto($id);

            if (!$ok) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al eliminar el pedido'
                ]);
            }

            \App\Services\AuditoriaService::registrar(
                'pedido.eliminar', 'pedido', $id,
                \App\Services\AuditoriaService::folio($id) . ' eliminado (total $' . number_format((float) ($antes['total'] ?? 0), 2)
                    . ', ' . ($antes['estado_actual'] ?? 'sin estado') . ')',
                $antes ?: null
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Pedido eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function cambiarEstado() {
        $id     = $this->request->getPost('id');
        $estado = $this->request->getPost('estado');

        if (!$id || !$estado) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos'
            ]);
        }

        $pedidoModel = new PedidoModel();
        $pedidoModel->cambiarEstadoPedido($id, $estado);

        return $this->response->setJSON(['success' => true]);
    }
    /**
     * Guarda la configuración actual del pedido como plantilla frecuente
     */
    public function guardar_configuracion()
    {
        $json = $this->request->getJSON(true);

        if (empty($json['nombre_configuracion']) || empty($json['productos']) || !isset($json['id_cliente'])) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Faltan datos (nombre de la configuración o productos).'
            ]);
        }

        $db = \Config\Database::connect();
        
        try {
            $db->transStart();

            // 1. Guardar la cabecera de la configuración
            $db->table('configuraciones_pedidos')->insert([
                'nombre_configuracion' => $json['nombre_configuracion'],
                'id_cliente'           => (int) $json['id_cliente'],
                'tipo_venta'           => $json['tipo_venta'] ?? 'contado',
                'tipo_entrega'         => $json['tipo_entrega'] ?? 'tienda',
                'id_repartidor'        => !empty($json['id_repartidor']) ? (int) $json['id_repartidor'] : null,
                'created_at'           => date('Y-m-d H:i:s')
            ]);

            $idConfiguracion = $db->insertID();

            // 2. Guardar los productos de la configuración
            foreach ($json['productos'] as $prod) {
                $db->table('detalle_configuracion_pedidos')->insert([
                    'id_configuracion' => $idConfiguracion,
                    'id_producto'      => (int) $prod['id_producto'],
                    'unidad'           => $prod['unidad'],
                    'cantidad'         => (float) $prod['cantidad'],
                    'precio_unitario'  => (float) $prod['precio_venta']
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Error al guardar la configuración en la base de datos.'
                ]);
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => '¡Configuración guardada con éxito!'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene la lista de todas las configuraciones guardadas para mostrarlas al administrador
     */
    public function listar_configuraciones()
    {
        $db = \Config\Database::connect();
        
        $configuraciones = $db->table('configuraciones_pedidos')
            ->select('configuraciones_pedidos.*, clientes.nombre as cliente_nombre, clientes.apellido_paterno')
            ->join('clientes', 'clientes.id_cliente = configuraciones_pedidos.id_cliente', 'left')
            ->orderBy('configuraciones_pedidos.id', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $configuraciones
        ]);
    }

    /**
     * Carga los detalles (productos) de una configuración específica
     */
    public function obtener_detalle_configuracion($idConfig)
    {
        $db = \Config\Database::connect();

        $config = $db->table('configuraciones_pedidos')
            ->where('id', $idConfig)
            ->get()
            ->getRowArray();

        if (!$config) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Configuración no encontrada.'
            ]);
        }

        // Corregido: ya no solicitamos producto.precio_sugerido porque usamos el precio guardado en el detalle
        $productos = $db->table('detalle_configuracion_pedidos')
            ->select('detalle_configuracion_pedidos.*, producto.nombre as nombre_producto')
            ->join('producto', 'producto.id = detalle_configuracion_pedidos.id_producto')
            ->where('id_configuracion', $idConfig)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'config' => $config,
            'productos' => $productos
        ]);
    }
    public function stock_vendedor()
{
    $pedidoModel = new \App\Models\PedidoModel();

    $productos = $pedidoModel->getProductosConPrecio();

    return view('vendedor/stock', [
        'productos' => $productos
    ]);
}
}