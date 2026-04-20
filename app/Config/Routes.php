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
$routes->get('pantalla_clientes', 'FRUVER::pantalla_clientes'); 
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
// ==========================
// 7. General
// ==========================
$routes->get('pantalla_inicio', 'FRUVER::pantalla_inicio');
$routes->get('pantalla_administrador', 'FRUVER::pantalla_administrador');
//$routes->get('pantalla_ventas', 'FRUVER::pantalla_ventas');
//$routes->get('pantalla_repartidores', 'FRUVER::pantalla_repartidores');
$routes->get('pantalla_pedidos', 'FRUVER::pantalla_pedidos');
$routes->get('pantalla_productos', 'FRUVER::pantalla_productos');


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




$routes->get('existencias/getProducto/(:num)',  'Existencias::getProducto/$1');
$routes->post('existencias/actualizar/(:num)',  'Existencias::actualizar/$1');