<?php

namespace App\Controllers;

use App\Services\SalidasRepartoService;

/**
 * Monitoreo de salidas para el administrador.
 * Las salidas las inicia el repartidor desde su app; aquí solo se consultan.
 */
class SalidasController extends BaseController
{
    private function datos(): array
    {
        $servicio = new SalidasRepartoService();
        return [
            'salidas'    => $servicio->listarAdmin(),
            'pendientes' => $servicio->pendientesPorRepartidor(),
            'generado'   => date('H:i:s'),
        ];
    }

    public function index()
    {
        try {
            return view('salidas', ['datos' => $this->datos()]);
        } catch (\Throwable $e) {
            return view('salidas', [
                'datos' => ['salidas' => [], 'pendientes' => [], 'generado' => date('H:i:s')],
                'error' => 'No se pudo cargar el monitor de salidas: ' . $e->getMessage(),
            ]);
        }
    }

    public function lista()
    {
        try {
            return $this->response->setJSON(['success' => true] + $this->datos());
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function detalle($idSalida)
    {
        try {
            return $this->response->setJSON([
                'success' => true,
                'data'    => (new SalidasRepartoService())->detalle((int) $idSalida),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
