<?php
namespace App\Controllers;

/**
 * Panel principal del repartidor: consultar stock (escáner) e iniciar el recorrido.
 * Los datos los pide la propia pantalla a RepartidorApp (repartidor/api/...).
 */
class PanelRepartidorController extends BaseController
{
    public function index()
    {
        return view('Repartidor/panel_inicio');
    }
}
