<?php
namespace App\Controllers;

use App\Services\AlertasStockService;

class Alertas extends BaseController
{
    /** Pantalla completa de alertas */
    public function index()
    {
        $servicio = new AlertasStockService();
        return view('pantalla_alertas', ['alertas' => $servicio->calcular()]);
    }

    /** Conteos y productos más urgentes (para la campanita y el aviso de Mermas) */
    public function resumen()
    {
        try {
            return $this->response->setJSON((new AlertasStockService())->resumenRapido());
        } catch (\Throwable $e) {
            // Si la migración aún no se corrió, no se rompe la pantalla que lo consulta
            return $this->response->setJSON(['criticos' => 0, 'atencion' => 0, 'urgentes' => []]);
        }
    }

    /** Cambia los días que tarda en llegar la mercancía de un producto */
    public function reposicion()
    {
        $ok = (new AlertasStockService())->guardarReposicion(
            (int) $this->request->getPost('id_producto'),
            (float) $this->request->getPost('dias')
        );

        return $this->response->setJSON([
            'success' => $ok,
            'message' => $ok ? 'Tiempo de reposición actualizado.' : 'Escribe un valor entre 0.5 y 90 días.',
        ]);
    }

    /** Parámetros del cálculo */
    public function config()
    {
        $ok = (new AlertasStockService())->guardarConfig(
            (int) $this->request->getPost('ventana_dias'),
            (float) $this->request->getPost('colchon_dias'),
            (float) $this->request->getPost('dias_objetivo')
        );

        return $this->response->setJSON([
            'success' => $ok,
            'message' => $ok ? 'Parámetros guardados.' : 'Revisa los valores (ventana de 7 a 90 días, objetivo de 1 a 60).',
        ]);
    }
}