<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


$routes->get('/', 'Home::index');

// Mostrar la pantalla de Login 
$routes->get('/usuario', 'Usuario::index');

//  el botón "Iniciar Sesión" 
$routes->post('/validar', 'Usuario::validar');
$routes->get('/pantalla_administrador', 'Usuario::validar');

//pantalla opcion de cliente 
$routes->get('pantalla_clientes', 'Cliente::index');