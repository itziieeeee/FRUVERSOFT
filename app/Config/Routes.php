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
$routes->get('pantalla_clientes', 'FRUVER::pantalla_clientes'); 
$routes->get('pantalla_rcliente', 'FRUVER::nuevo_cliente');
$routes->post('alta_clientes/guardar', 'FRUVER::guardar_cliente');

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

// ==========================
// 6. Merma
// ==========================
$routes->get('merma', 'Merma::index');
$routes->post('merma/guardar', 'Merma::guardar');

// ==========================
// 7. General
// ==========================
$routes->get('productos', 'FRUVER::productos');
$routes->get('pantalla_inicio', 'FRUVER::pantalla_inicio');
$routes->get('pantalla_administrador', 'FRUVER::pantalla_administrador');