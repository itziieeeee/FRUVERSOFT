<?php

namespace App\Controllers;

use App\Services\CorteService;

/** Corte de caja diario (solo administrador) */
class Corte extends BaseController
{
    public function index()
    {
        $servicio = new CorteService();
        $fecha    = CorteService::fechaValida($this->request->getGet('fecha')) ?? CorteService::hoy();

        // Sin fecha futura: no hay nada que cortar
        if ($fecha > CorteService::hoy()) {
            $fecha = CorteService::hoy();
        }

        $historial = $servicio->historial();

        return view('corte_caja', [
            'resumen'    => $servicio->resumen($fecha),
            'guardado'   => $servicio->ultimoDe($fecha),
            'historial'  => $historial,
            'hoy'        => CorteService::hoy(),
            // El fondo del último corte sirve de sugerencia para el siguiente
            'fondoSugerido' => $historial ? (float) $historial[0]['efectivo_contado'] : 0.0,
        ]);
    }

    public function guardar()
    {
        $fecha = CorteService::fechaValida($this->request->getPost('fecha'));
        $fondo   = (float) $this->request->getPost('fondo');
        $contado = (float) $this->request->getPost('contado');

        if (!$fecha || $fecha > CorteService::hoy()) {
            return $this->response->setJSON(['success' => false, 'message' => 'La fecha del corte no es válida.']);
        }
        if ($fondo < 0 || $contado < 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'El fondo y el efectivo contado no pueden ser negativos.']);
        }

        try {
            $r = (new CorteService())->guardar($fecha, $fondo, $contado, trim((string) $this->request->getPost('notas')));
            return $this->response->setJSON(['success' => true, 'message' => 'Corte guardado.'] + $r);
        } catch (\Throwable $e) {
            log_message('error', 'Corte de caja: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo guardar el corte.']);
        }
    }
}
