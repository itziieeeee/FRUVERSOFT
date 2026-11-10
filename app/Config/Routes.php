<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ==========================================
// 1. RUTAS PÚBLICAS Y AUTENTICACIÓN
// ==========================================
$routes->get('/', 'FRUVER::index'); // Landing page o portada

$routes->get('login', 'AuthController::login');
$routes->post('login/ingresar', 'AuthController::ingresar');

$routes->get('registro', 'AuthController::registro');
$routes->post('registro/guardar', 'AuthController::guardarRegistro');

$routes->get('logout', 'AuthController::logout');
$routes->get('salir', 'AuthController::logout');

// Redirección inteligente para el botón "INGRESAR" de la portada
$routes->get('ingresar_portal', function() {
    if (!session()->get('isLoggedIn')) {
        return redirect()->to(base_url('login'));
    }
    return session()->get('rol') === 'admin' 
        ? redirect()->to(base_url('menusolo')) 
        : redirect()->to(base_url('cliente/menu'));
});


// ==========================================
// 2. RUTAS PROTEGIDAS DEL ADMINISTRADOR (admin)
// ==========================================
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
    $routes->post('clientes/actualizar', 'Clientes::actualizar');
    $routes->post('clientes/eliminar/(:num)', 'Clientes::eliminar/$1');

    // Ventas y Pedidos (Administración)
    $routes->get('pantalla_ventas', 'PedidoController::pantalla_ventas');
    $routes->post('pedido/sugerir_repartidor', 'PedidoController::sugerir_repartidor');
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

    // Repartidores
    $routes->get('pantalla_repartidores', 'FRUVER::mostrar_repartidores');
    $routes->post('FRUVER/guardarrepartidor', 'FRUVER::guardarrepartidor');
    $routes->post('FRUVER/editarrepartidor/(:num)', 'FRUVER::editarrepartidor/$1');
    $routes->post('FRUVER/eliminarrepartidor/(:num)', 'FRUVER::eliminarrepartidor/$1');
});


// ==========================================
// 3. RUTAS PROTEGIDAS DEL CLIENTE (cliente)
// ==========================================
$routes->group('cliente', ['filter' => 'authFilter:cliente'], function($routes) {
    $routes->get('menu', 'ClientePedidoController::menu');
    $routes->get('historial', 'ClientePedidoController::historial');
    $routes->get('creditos', 'ClientePedidoController::creditos');
    $routes->get('estado', 'ClientePedidoController::estado');
});

// Catálogo y Carrito
$routes->get('catalogo', 'Catalogo::index');
$routes->get('carrito', 'Catalogo::ver');
$routes->post('carrito/agregar', 'Catalogo::agregar');
$routes->post('carrito/actualizar/(:num)', 'Catalogo::actualizar/$1');
$routes->post('carrito/quitar/(:num)', 'Catalogo::quitar/$1');
$routes->post('carrito/vaciar', 'Catalogo::vaciar');
$routes->get('pedidos', 'ClientePedidoController::index');

// RUTAS DEL REPARTIDOR

$routes->group('repartidor', ['filter' => 'authFilter:repartidor'], function($routes) {
    $routes->get('panel', 'PanelRepartidorController::index');
    $routes->get('inventario', 'PanelRepartidorController::inventario');
    $routes->get('pedidos', 'PanelRepartidorController::mis_pedidos');
    $routes->get('app', 'RepartidorApp::vista');
$routes->get('api/pedidos', 'RepartidorApp::pedidos');
$routes->post('api/estado', 'RepartidorApp::estado');
$routes->post('api/incidencia', 'RepartidorApp::incidencia');
$routes->post('api/ubicacion', 'RepartidorApp::ubicacion');
$routes->get('api/respuestas', 'RepartidorApp::respuestas');
$routes->post('api/respuestas/leer', 'RepartidorApp::respuestasLeer');
    // marquen como entregado
    $routes->post('cambiar_estado', 'PanelRepartidorController::cambiar_estado');
});
    $routes->post('marcar_entregado', 'PanelRepartidorController::marcar_entregado');

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
$routes->get('ticket/ultimo/(:num)',  'Ticket::ultimo/$1');

// Gestión de Avisos (Admin)
    $routes->get('admin/avisos', 'AvisoController::index');
    $routes->post('admin/avisos/guardar', 'AvisoController::guardar');
    $routes->get('admin/avisos/eliminar/(:num)', 'AvisoController::eliminar/$1');    
