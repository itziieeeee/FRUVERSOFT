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

// ==========================
// 7. General
// ==========================
$routes->get('pantalla_inicio', 'FRUVER::pantalla_inicio');
$routes->get('pantalla_administrador', 'FRUVER::pantalla_administrador');
$routes->get('pantalla_ventas', 'FRUVER::pantalla_ventas');
$routes->get('pantalla_repartidores', 'FRUVER::pantalla_repartidores');
$routes->get('pantalla_pedidos', 'FRUVER::pantalla_pedidos');
$routes->get('pantalla_productos', 'FRUVER::pantalla_productos');