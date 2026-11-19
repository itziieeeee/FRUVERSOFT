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

    /** Pantalla de seguimiento en vivo (mapa) */
    public function seguimiento()
    {
        return view('seguimiento_repartidor');
    }

    /** JSON que consulta el mapa cada pocos segundos */
    public function seguimientoDatos()
    {
        try {
            return $this->response->setJSON(['success' => true] + (new SalidasRepartoService())->seguimiento());
        } catch (\Throwable $e) {
            log_message('error', 'Seguimiento: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudo cargar el seguimiento.']);
        }
    }

    /** El administrador ya avisó al cliente: se guarda para no repetir la alerta */
    public function avisado($idPedido)
    {
        $ok = (new SalidasRepartoService())->marcarAvisoCercania((int) $idPedido);
        return $this->response->setJSON(['success' => $ok]);
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
