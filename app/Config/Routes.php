<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ==========================
// 1. Inicio y sesión
// ==========================
$routes->get('/', 'FRUVER::index');
$routes->get('usuario', 'FRUVER::usuario');
$routes->get('salir', 'FRUVER::salir');
$routes->post('validar', 'FRUVER::validar');

// ==========================
// 2. Usuarios (Admin)
// ==========================
$routes->get('registro', 'FRUVER::reuser');
$routes->post('guardar_usuario', 'FRUVER::guardar');
$routes->get('menusolo', 'FRUVER::menusolo');

// ==========================
// 3. Clientes
// ==========================
$routes->get('alta_cliente', 'FRUVER::nuevo_cliente');
$routes->post('guardar_cliente', 'FRUVER::guardar_cliente');
$routes->get('pantalla_clientes', 'Clientes::pantalla_clientes');
$routes->get('clientes/detalle/(:num)', 'Clientes::detalle/$1');
$routes->get('pantalla_rcliente', 'FRUVER::nuevo_cliente');


// ==========================
// 4. Inventario
// ==========================
$routes->get('pantalla_inventario', 'FRUVER::inventario');
$routes->get('inventario', 'FRUVER::inventario');

// ==========================
// 5. Productos
// ==========================
$routes->get('producto', 'Producto::index');
$routes->post('producto/guardar', 'Producto::guardar');
$routes->get('productos', 'Producto::listar');

// ==========================
// 6. Merma
// ==========================
$routes->get('merma', 'Merma::index');
$routes->post('merma/guardar', 'Merma::guardar');
$routes->post('confirmar-entrada', 'GestionEntradas::guardar');
$routes->post('merma/guardar', 'Merma::guardar');
$routes->get('mermas', 'Merma::index');
// ==========================
// 7. General
// ==========================
$routes->get('pantalla_inicio', 'FRUVER::pantalla_inicio');
$routes->get('pantalla_administrador', 'FRUVER::pantalla_administrador');
//$routes->get('pantalla_ventas', 'FRUVER::pantalla_ventas');
//$routes->get('pantalla_repartidores', 'FRUVER::pantalla_repartidores');
$routes->get('pantalla_pedidos', 'FRUVER::pantalla_pedidos');
//$routes->get('pantalla_productos', 'FRUVER::pantalla_productos');


//CONEXION BD SECCION 1 DE VENTAS
$routes->get('pantalla_ventas', 'PedidoController::pantalla_ventas');
$routes->post('pedido/guardar_productos_pedido', 'PedidoController::guardar_productos_pedido');




$routes->get('existencias', 'Existencias::index');        // lista productos
$routes->get('existencias/editar/(:num)', 'Existencias::editar/$1'); // editar producto por id
$routes->post('existencias/entrada', 'Existencias::registrarEntrada'); // registrar entrada

//conexion para la bd de los repartidores
$routes->post('FRUVER/guardarrepartidor', 'FRUVER::guardarrepartidor');
$routes->get('pantalla_repartidores', 'FRUVER::mostrar_repartidores');
$routes->post('FRUVER/editarrepartidor/(:num)', 'FRUVER::editarrepartidor/$1');
$routes->post('FRUVER/eliminarrepartidor/(:num)', 'FRUVER::eliminarrepartidor/$1');


$routes->post('status/cambiar', 'Status::cambiar');
$routes->get('pantalla_pedidos', 'Status::pantalla_pedidos');




$routes->get('existencias/getProducto/(:num)',  'Existencias::getProducto/$1');
$routes->post('existencias/actualizar/(:num)',  'Existencias::actualizar/$1');

//boton nuevo producto 
$routes->get('alta_producto', 'Producto::altaproducto');
$routes->post('guardar_producto', 'Producto::guardar');
$routes->get('pantalla_productos', 'Producto::pantalla_productos');
$routes->delete('producto/eliminar/(:num)', 'Producto::eliminar/$1');

$routes->post('pedido/guardar_productos_pedido', 'PedidoController::guardar_productos_pedido');
$routes->post('status/cambiar',       'PedidoController::cambiarEstado');
$routes->delete('pedido/eliminar/(:num)', 'PedidoController::eliminarPedido/$1');

// En Routes.php debería estar:
$routes->get('pantalla_ventas', 'PedidoController::pantalla_ventas');


//clientes
$routes->post('clientes/registrar', 'Clientes::registrar');

$routes->get('pantalla_rcliente', 'Clientes::pantalla_rcliente');
$routes->post('clientes/registrar', 'Clientes::registrar');

$routes->delete('existencias/eliminar/(:num)', 'Existencias::eliminar/$1');
$routes->post('existencias/eliminar/(:num)', 'Existencias::eliminar/$1');

$routes->post('clientes/eliminar/(:num)', 'Clientes::eliminar/$1');
$routes->post('clientes/actualizar', 'Clientes::actualizar');
$routes->post('pedido/actualizar_pago', 'Status::actualizar_pago');
$routes->post('pedido/validar', 'Status::validar_pedido');
$routes->post('pedido/detalle', 'Status::detalle_pedido');

$routes->post('guardar_cliente', 'Clientes::registrar');

$routes->get('pantalla_productos',  'Producto::pantalla_productos');
 
// Alta
$routes->get('alta_producto',       'Producto::altaproducto');
$routes->post('producto/guardar',   'Producto::guardar');
 
$routes->post('producto/editar/(:num)',   'Producto::editar/$1');
 
$routes->post('producto/eliminar/(:num)', 'Producto::eliminar/$1');
 