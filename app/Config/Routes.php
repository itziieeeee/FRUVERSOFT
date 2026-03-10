<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// 1. Inicio y Login
$routes->get('/', 'FRUVER::index');
$routes->get('usuario', 'FRUVER::usuario');
$routes->get('salir', 'FRUVER::salir');

// 2. Usuarios Admin
$routes->get('registro', 'FRUVER::reuser');
$routes->post('guardar_usuario', 'FRUVER::guardar');
$routes->post('validar', 'FRUVER::validar');
$routes->get('menusolo', 'FRUVER::menusolo');

// 3. Clientes (Rutas Unificadas)
// --- SECCIÓN CLIENTES ---
$routes->get('pantalla_clientes', 'FRUVER::pantalla_clientes'); 
$routes->get('pantalla_rcliente', 'FRUVER::nuevo_cliente');
$routes->post('alta_clientes/guardar', 'FRUVER::guardar_cliente');
// 4. Sección Inventario
$routes->get('pantalla_inventario', 'FRUVER::inventario');


//entrada 
$routes->get('inventario/producto', 'Producto::index');
$routes->post('inventario/guardar', 'Producto::guardar');

$routes->get('producto', 'Producto::index');
$routes->post('producto/guardar', 'Producto::guardar');
$routes->get('menusolo', 'FRUVER::menusolo');
$routes->get('pantalla_inicio', 'FRUVER::pantalla_inicio');

//
// Ruta para procesar el guardado de la merma
$routes->post('merma/guardar', 'Merma::guardar');
$routes->get('productos', 'FRUVER::productos');
