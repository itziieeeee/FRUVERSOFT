<?php

namespace App\Controllers;

use App\Libraries\Exportador;
use App\Services\CorteService;
use App\Services\ExportacionesService;
use App\Services\ReporteCostosService;

/**
 * Exporta reportes a PDF y a Excel (solo administrador).
 * Rutas: exportar/{corte|cobranza|inventario|ventas}/{pdf|xlsx}
 */
class Exportar extends BaseController
{
    public function corte($formato)
    {
        $hoy   = CorteService::hoy();
        $fecha = CorteService::fechaValida($this->request->getGet('fecha')) ?? $hoy;
        if ($fecha > $hoy) {
            $fecha = $hoy;
        }

        return $this->entregar((new ExportacionesService())->corte($fecha), (string) $formato, 'corte');
    }

    public function cobranza($formato)
    {
        return $this->entregar((new ExportacionesService())->cobranza(), (string) $formato, 'cobranza');
    }

    public function inventario($formato)
    {
        return $this->entregar((new ExportacionesService())->inventario(), (string) $formato, 'pantalla_inventario');
    }

    public function ventas($formato)
    {
        $costos  = new ReporteCostosService();
        $periodo = $costos->periodo(
            (string) ($this->request->getGet('periodo') ?: 'semana'),
            $this->request->getGet('desde'),
            $this->request->getGet('hasta')
        );
        $exportes = new ExportacionesService();

        return $this->entregar(
            $exportes->ventas($periodo, $costos->reporte($periodo['desde'], $periodo['hasta']), $exportes->metodosPago($periodo['desde'], $periodo['hasta'])),
            (string) $formato,
            'reportes'
        );
    }

    /** Responde con el PDF o el Excel; si algo falla regresa a la pantalla de origen con el motivo */
    private function entregar(array $reporte, string $formato, string $volverA)
    {
        try {
            if ($formato === 'xlsx') {
                return Exportador::xlsx($this->response, $reporte);
            }
            if ($formato === 'pdf') {
                return Exportador::pdf($this->response, $reporte);
            }
            return $this->response->setStatusCode(404)->setBody('Formato no disponible.');
        } catch (\Throwable $e) {
            log_message('error', 'Exportar {formato}: {msg}', ['formato' => $formato, 'msg' => $e->getMessage()]);
            return redirect()->to(base_url($volverA))->with('error', 'No se pudo generar el archivo: ' . $e->getMessage());
        }
    }
}
