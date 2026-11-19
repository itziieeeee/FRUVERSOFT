<?php

namespace App\Controllers;

use App\Services\AuditoriaService;

/** Historial de cambios (solo administrador) */
class Auditoria extends BaseController
{
    public function index()
    {
        $servicio = new AuditoriaService();

        $filtros = [
            'desde'   => $this->fechaValida($this->request->getGet('desde')),
            'hasta'   => $this->fechaValida($this->request->getGet('hasta')),
            'accion'  => (string) $this->request->getGet('accion'),
            'usuario' => (string) $this->request->getGet('usuario'),
            'q'       => trim((string) $this->request->getGet('q')),
        ];

        // Por defecto, los últimos 7 días
        if ($filtros['desde'] === '' && $filtros['hasta'] === '' && $this->request->getGet('desde') === null) {
            $zona = new \DateTimeZone('America/Mexico_City');
            $filtros['hasta'] = (new \DateTime('now', $zona))->format('Y-m-d');
            $filtros['desde'] = (new \DateTime('now', $zona))->modify('-6 days')->format('Y-m-d');
        }

        $datos = $servicio->listar($filtros, (int) ($this->request->getGet('pagina') ?: 1));

        return view('auditoria', $datos + [
            'filtros'  => $filtros,
            'acciones' => AuditoriaService::ACCIONES,
            'usuarios' => $servicio->usuarios(),
        ]);
    }

    private function fechaValida($valor): string
    {
        $valor = (string) $valor;
        $d = \DateTime::createFromFormat('Y-m-d', $valor);
        return ($d && $d->format('Y-m-d') === $valor) ? $valor : '';
    }
}
