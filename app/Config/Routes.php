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
$routes->get('pantalla_administrador', 'FRUVER::validar');

// 3. Clientes (Rutas Unificadas)
// --- SECCIÓN CLIENTES ---
// Esta ruta es la que fallaba (404). Ahora apunta a la función correcta.
$routes->get('alta_cliente', 'FRUVER::nuevo_cliente');
$routes->post('guardar_cliente', 'FRUVER::guardar_cliente');
$routes->get('prueba', 'FRUVER::prueba');
