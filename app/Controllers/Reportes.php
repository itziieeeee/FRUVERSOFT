<?php

namespace App\Controllers;

use App\Services\ExportacionesService;
use App\Services\ReporteCostosService;

/** Reporte de ventas por día y por producto, con gráficas (solo administrador) */
class Reportes extends BaseController
{
    public function index()
    {
        $costos  = new ReporteCostosService();
        $periodo = $costos->periodo(
            (string) ($this->request->getGet('periodo') ?: 'semana'),
            $this->request->getGet('desde'),
            $this->request->getGet('hasta')
        );

        return view('reportes_ventas', [
            'periodo' => $periodo,
            'reporte' => $costos->reporte($periodo['desde'], $periodo['hasta']),
            'metodos' => (new ExportacionesService())->metodosPago($periodo['desde'], $periodo['hasta']),
        ]);
    }
}
