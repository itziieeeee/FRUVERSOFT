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