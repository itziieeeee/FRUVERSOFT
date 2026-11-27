<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


// 1. RUTAS PÚBLICAS Y AUTENTICACIÓN
$routes->get('/', 'FRUVER::index'); 

$routes->get('login', 'AuthController::login');
$routes->post('login/ingresar', 'AuthController::ingresar');

$routes->get('registro', 'AuthController::registro');
$routes->post('registro/guardar', 'AuthController::guardarRegistro');

$routes->get('logout', 'AuthController::logout');
$routes->get('salir', 'AuthController::logout');
$routes->get('cuenta/cambiar-password', 'AuthController::cambiarPassword', ['filter' => 'authFilter']);
$routes->post('cuenta/cambiar-password', 'AuthController::guardarPassword', ['filter' => 'authFilter']);

// Redirección inteligente para el botón "INGRESAR" de la portada
$routes->get('ingresar_portal', function() {

    if (!session()->get('isLoggedIn')) {
        return redirect()->to(base_url('login'));
    }

    $rol = session()->get('rol');

    if ($rol === 'admin') {
        return redirect()->to(base_url('menusolo'));
    }

    if ($rol === 'repartidor') {
        return !empty(session()->get('debe_cambiar_password'))
            ? redirect()->to(base_url('cuenta/cambiar-password'))
            : redirect()->to(base_url('repartidor/panel'));
    }

    if ($rol === 'vendedor') {
        return !empty(session()->get('debe_cambiar_password'))
            ? redirect()->to(base_url('cuenta/cambiar-password'))
            : redirect()->to(base_url('vendedor/panel'));
    }

    return redirect()->to(base_url('cliente/menu'));
});

// 2. RUTAS PROTEGIDAS DEL ADMINISTRADOR (admin)


$routes->group('', ['filter' => 'authFilter:admin'], function($routes) {

    // Menú Principal Admin
    $routes->get('menusolo', 'FRUVER::menusolo');
    $routes->get('pantalla_administrador', 'FRUVER::pantalla_administrador');

    // Inventario y Existencias
    $routes->get('pantalla_inventario', 'FRUVER::inventario');
    $routes->get('inventario', 'FRUVER::inventario');
    $routes->get('existencias', 'Existencias::index');
    $routes->get('existencias/editar/(:num)', 'Existencias::editar/$1');
    $routes->post('existencias/entrada', 'Existencias::registrarEntrada');
    $routes->get('existencias/getProducto/(:num)', 'Existencias::getProducto/$1');
    $routes->post('existencias/actualizar/(:num)', 'Existencias::actualizar/$1');
    $routes->post('existencias/eliminar/(:num)', 'Existencias::eliminar/$1');
    $routes->delete('existencias/eliminar/(:num)', 'Existencias::eliminar/$1');

    // Productos
    $routes->get('producto', 'Producto::index');
    $routes->get('productos', 'Producto::listar');
    $routes->get('alta_producto', 'Producto::altaproducto');
    $routes->post('producto/guardar', 'Producto::guardar');
    $routes->post('guardar_producto', 'Producto::guardar');
    $routes->get('pantalla_productos', 'Producto::pantalla_productos');
    $routes->post('producto/editar/(:num)', 'Producto::editar/$1');
    $routes->post('producto/eliminar/(:num)', 'Producto::eliminar/$1');
    $routes->delete('producto/eliminar/(:num)', 'Producto::eliminar/$1');

    // Clientes (Gestión desde Admin)
    $routes->get('alta_cliente', 'FRUVER::nuevo_cliente');
    // Compatibilidad con el enlace antiguo "Nuevo Cliente".
    $routes->get('pantalla_rcliente', 'FRUVER::nuevo_cliente');
    $routes->post('guardar_cliente', 'Clientes::registrar');
    $routes->post('clientes/registrar', 'Clientes::registrar');
    $routes->get('pantalla_clientes', 'Clientes::pantalla_clientes');
    $routes->get('clientes/detalle/(:num)', 'Clientes::detalle/$1');
    $routes->get('clientes/historial/(:num)', 'Clientes::historial/$1');
    $routes->post('clientes/actualizar', 'Clientes::actualizar');
    $routes->post('clientes/eliminar/(:num)', 'Clientes::eliminar/$1');

    // Ventas y Pedidos (Administración)
    $routes->get('pantalla_ventas', 'PedidoController::pantalla_ventas');
    $routes->post('pedido/sugerir_repartidor', 'PedidoController::sugerir_repartidor');
    $routes->post('pedido/plan_salida', 'PedidoController::plan_salida');
    $routes->get('pedido/ordenar_ruta/(:num)', 'PedidoController::ordenar_ruta/$1');
    $routes->post('pedido/guardar_productos_pedido', 'PedidoController::guardar_productos_pedido');
    $routes->post('pedido/eliminar/(:num)', 'PedidoController::eliminarPedido/$1');
    $routes->get('pantalla_pedidos', 'Status::pantalla_pedidos');
    $routes->post('status/cambiar', 'Status::cambiar');
    $routes->post('pedido/actualizar_pago', 'Status::actualizar_pago');
    $routes->post('pedido/validar', 'Status::validar_pedido');
    $routes->post('pedido/detalle', 'Status::detalle_pedido');
    $routes->post('pedido/actualizar_precios', 'Status::actualizar_precios');

    // Merma y Entradas
    $routes->get('merma', 'Merma::index');
    $routes->get('mermas', 'Merma::index');
    $routes->post('merma/guardar', 'Merma::guardar');
    $routes->post('confirmar-entrada', 'GestionEntradas::guardar');

    // Alertas predictivas de stock
    $routes->get('alertas', 'Alertas::index');
    $routes->get('pantalla_alertas', 'Alertas::index');
    $routes->get('alertas/resumen', 'Alertas::resumen');
    $routes->post('alertas/reposicion', 'Alertas::reposicion');
    $routes->post('alertas/config', 'Alertas::config');

    // Monitoreo de salidas de reparto (las inicia el repartidor desde su app)
    $routes->get('salidas', 'SalidasController::index');
    $routes->get('salidas/lista', 'SalidasController::lista');
    $routes->get('salidas/detalle/(:num)', 'SalidasController::detalle/$1');
    $routes->get('salidas/parametros', 'SalidasController::parametros');
    $routes->post('salidas/parametros', 'SalidasController::guardarParametros');

    // Escáner de códigos (cámara o lector USB) y etiquetas imprimibles de productos
    $routes->get('escaner', 'Escaner::buscar');
    $routes->get('productos/etiquetas', 'Producto::etiquetas');

    // Seguimiento en vivo del repartidor y aviso "ya está cerca"
    $routes->get('seguimiento', 'SalidasController::seguimiento');
    $routes->get('seguimiento/datos', 'SalidasController::seguimientoDatos');
    $routes->post('seguimiento/avisado/(:num)', 'SalidasController::avisado/$1');

    // Historial de cambios
    $routes->get('auditoria', 'Auditoria::index');

    // Corte de caja
    $routes->get('corte', 'Corte::index');
    $routes->post('corte/guardar', 'Corte::guardar');

    // Devoluciones de clientes
    $routes->get('devoluciones', 'Devoluciones::index');
    $routes->get('devoluciones/pedido/(:num)', 'Devoluciones::pedido/$1');
    $routes->post('devoluciones/guardar', 'Devoluciones::guardar');

    // Evidencia de entrega (foto, firma y cobro del repartidor)
    $routes->get('entrega/(:num)', 'Entregas::ver/$1');
    $routes->get('entrega/archivo/(:segment)', 'Entregas::archivo/$1');

    // Cobranza de créditos: recordatorios de pago por WhatsApp
    $routes->get('cobranza', 'Cobranza::index');
    $routes->post('cobranza/vencimiento', 'Cobranza::vencimiento');
    $routes->post('cobranza/recordado', 'Cobranza::recordado');
    $routes->post('cobranza/configuracion', 'Cobranza::configuracion');

    // Exportar a PDF y Excel
    $routes->get('exportar/corte/(pdf|xlsx)', 'Exportar::corte/$1');
    $routes->get('exportar/cobranza/(pdf|xlsx)', 'Exportar::cobranza/$1');
    $routes->get('exportar/inventario/(pdf|xlsx)', 'Exportar::inventario/$1');
    $routes->get('exportar/ventas/(pdf|xlsx)', 'Exportar::ventas/$1');

    // Reporte de ventas con gráficas (por día y por producto)
    $routes->get('reportes', 'Reportes::index');

    // Resumen del inicio del administrador (se actualiza solo)
    $routes->get('dashboard/datos', 'Dashboard::datos');

    // Costos, ganancias y variación de costos
    $routes->get('costos', 'Costos::index');
    $routes->get('costos/pdf', 'Costos::pdf');
    $routes->post('costos/conversion', 'Costos::conversion');

    // Repartidores
    $routes->get('pantalla_repartidores', 'FRUVER::mostrar_repartidores');
    $routes->post('FRUVER/guardarrepartidor', 'FRUVER::guardarrepartidor');
    $routes->post('FRUVER/editarrepartidor/(:num)', 'FRUVER::editarrepartidor/$1');
    $routes->post('FRUVER/eliminarrepartidor/(:num)', 'FRUVER::eliminarrepartidor/$1');
        // Vendedores
    $routes->get('pantalla_vendedores', 'FRUVER::mostrar_vendedores');
    $routes->post('FRUVER/guardarvendedor', 'FRUVER::guardarvendedor');
});
// ==========================================
// 3. RUTAS PROTEGIDAS DEL CLIENTE (cliente)
// ==========================================
$routes->group('cliente', ['filter' => 'authFilter:cliente'], function($routes) {
    $routes->get('menu', 'ClientePedidoController::menu');
    $routes->get('historial', 'ClientePedidoController::historial');
    $routes->get('creditos', 'ClientePedidoController::creditos');
    $routes->get('estado', 'ClientePedidoController::estado');
    // Seguimiento de un pedido (estado y posición del repartidor)
    $routes->get('seguimiento/datos/(:num)', 'ClientePedidoController::seguimientoDatos/$1');
    $routes->get('seguimiento/(:num)', 'ClientePedidoController::seguir/$1');
});

// Catálogo y Carrito
$routes->get('catalogo', 'Catalogo::index');
$routes->get('carrito', 'Catalogo::ver');
$routes->post('carrito/agregar', 'Catalogo::agregar');
$routes->post('carrito/actualizar/(:num)', 'Catalogo::actualizar/$1');
$routes->post('carrito/quitar/(:num)', 'Catalogo::quitar/$1');
$routes->post('carrito/vaciar', 'Catalogo::vaciar');
// Solo un cliente con sesión puede confirmar; si no la tiene, authFilter lo manda a iniciar sesión (el carrito se conserva)
$routes->post('carrito/confirmar', 'Catalogo::confirmar', ['filter' => 'authFilter:cliente']);
$routes->get('pedidos', 'ClientePedidoController::index', ['filter' => 'authFilter:cliente']);

// RUTAS DEL VENDEDOR
$routes->group('vendedor', ['filter' => 'authFilter:vendedor'], function($routes) {

    $routes->get('panel', 'FRUVER::pantalla_vendedor');

    $routes->get('buscar-producto', 'FRUVER::buscar_producto');
    $routes->get('escaner', 'Escaner::buscar');

    $routes->get('pedidos', 'PedidoController::pedidos_vendedor');

    $routes->get('nuevo-pedido', 'PedidoController::pantalla_ventas_vendedor');

    $routes->get('stock', 'PedidoController::stock_vendedor');

    $routes->post('guardar-pedido', 'PedidoController::guardar_productos_pedido');

});


// RUTAS DEL REPARTIDOR

// El panel es la única pantalla del repartidor: catálogo de stock y recorrido con sus pedidos y evidencia.
$routes->group('repartidor', ['filter' => 'authFilter:repartidor'], function($routes) {
    $routes->get('panel', 'PanelRepartidorController::index');
    // Enlace anterior: se conserva solo para que los accesos guardados lleguen al panel
    $routes->get('app', 'RepartidorApp::vista');

    $routes->post('api/ubicacion', 'RepartidorApp::ubicacion');
    $routes->get('api/pedidos', 'RepartidorApp::pedidos');
    $routes->get('api/catalogo', 'RepartidorApp::catalogo');
    $routes->get('api/resumen', 'RepartidorApp::resumen');
    $routes->get('api/evidencia/(:num)', 'RepartidorApp::evidencia/$1');

    // Ayuda al administrador: el repartidor avisa y lee su respuesta
    $routes->post('api/incidencia', 'RepartidorApp::incidencia');
    $routes->get('api/respuestas', 'RepartidorApp::respuestas');
    $routes->post('api/respuestas/leer', 'RepartidorApp::respuestasLeer');
    $routes->get('api/recorrido/previa', 'RepartidorApp::recorridoPrevia');
    $routes->post('api/recorrido/iniciar', 'RepartidorApp::recorridoIniciar');
    $routes->get('api/salida-activa', 'RepartidorApp::salidaActiva');
    $routes->post('api/salida/pedido/entregar/(:num)', 'RepartidorApp::entregarSalidaPedido/$1');
    $routes->post('api/salida/pedido/fallar/(:num)', 'RepartidorApp::noEntregado/$1');
});

$routes->get('clientes/obtener_ultimo_pedido/(:num)', 'Clientes::obtener_ultimo_pedido/$1'); //ruta ticket ws
$routes->group('admin_api', ['filter' => 'authFilter:admin'], function($routes) {
    $routes->get('cambios', 'AdminCambios::cambios');
    $routes->get('incidencias/historial', 'AdminCambios::historial');
    $routes->post('incidencia/resolver/(:num)', 'AdminCambios::resolver/$1');
    $routes->post('incidencia/responder/(:num)', 'AdminCambios::responder/$1');
});
// Rutas para configuraciones / plantillas de pedidos frecuentes
    $routes->post('pedido/guardar_configuracion', 'PedidoController::guardar_configuracion');
    $routes->get('pedido/listar_configuraciones', 'PedidoController::listar_configuraciones');
    $routes->get('pedido/obtener_detalle_configuracion/(:num)', 'PedidoController::obtener_detalle_configuracion/$1');
$routes->get('ticket/ver/(:num)',     'Ticket::ver/$1');
$routes->get('ticket/pdf/(:num)',     'Ticket::pdf/$1');
$routes->get('ticket/termico/(:num)', 'Ticket::termico/$1');
$routes->get('ticket/ultimo/(:num)',  'Ticket::ultimo/$1');

// Gestión de Avisos (Admin)
    $routes->get('admin/avisos', 'AvisoController::index');
    $routes->post('admin/avisos/guardar', 'AvisoController::guardar');
    $routes->get('admin/avisos/eliminar/(:num)', 'AvisoController::eliminar/$1');    
