<?php

namespace App\Controllers;

use App\Services\AuditoriaService;
use App\Services\RutaRepartidorService;
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
            'salidas'      => $servicio->listarAdmin(),
            'pendientes'   => $servicio->pendientesPorRepartidor(),
            'optimizacion' => $servicio->resumenOptimizacion(30),
            'generado'     => date('H:i:s'),
        ];
    }

    /** Parámetros con los que se calculan tiempos, tráfico y gasolina, y los horarios de tráfico pesado */
    public function parametros()
    {
        return $this->response->setJSON([
            'success'    => true,
            'parametros' => RutaRepartidorService::parametros(),
            'limites'    => array_map(static fn (array $p): array => ['min' => $p[0], 'max' => $p[1], 'nombre' => $p[2]], RutaRepartidorService::PARAMETROS),
            'horarios'   => RutaRepartidorService::horariosTransito(),
        ]);
    }

    public function guardarParametros()
    {
        $campos = array_keys(RutaRepartidorService::PARAMETROS);
        $nuevos = [];
        foreach ($campos as $campo) {
            $valor = $this->request->getPost($campo);
            if ($valor !== null) {
                $nuevos[$campo] = $valor;
            }
        }

        try {
            $r = RutaRepartidorService::guardarParametros($nuevos);
        } catch (\InvalidArgumentException $e) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            log_message('error', 'Parámetros de ruta: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'No se pudieron guardar los parámetros.']);
        }

        $cambios = [];
        foreach ($r['despues'] as $clave => $valor) {
            if (abs((float) $valor - (float) ($r['antes'][$clave] ?? 0)) > 0.0001) {
                $cambios[] = RutaRepartidorService::PARAMETROS[$clave][2] . ' ' . $r['antes'][$clave] . ' → ' . $valor;
            }
        }
        if ($cambios) {
            AuditoriaService::registrar('config.rutas', 'config', 'rutas', 'Parámetros de ruta: ' . implode(', ', $cambios), $r);
        }

        return $this->response->setJSON(['success' => true, 'parametros' => $r['despues'], 'message' => $cambios ? 'Parámetros guardados.' : 'No había cambios.']);
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
