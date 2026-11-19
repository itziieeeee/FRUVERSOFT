<?php

namespace App\Controllers;

use App\Services\DevolucionService;

/** Devoluciones de clientes (solo administrador) */
class Devoluciones extends BaseController
{
    public function index()
    {
        return view('devoluciones', ['devoluciones' => (new DevolucionService())->listar()]);
    }

    /** Lo que se puede devolver de un pedido */
    public function pedido($id)
    {
        $info = (new DevolucionService())->pedido((int) $id);
        if (!$info) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Pedido no encontrado.']);
        }
        return $this->response->setJSON(['success' => true, 'pedido' => $info]);
    }

    public function guardar()
    {
        $json = $this->request->getJSON(true) ?? [];

        try {
            $r = (new DevolucionService())->crear(
                (int) ($json['id_pedido'] ?? 0),
                is_array($json['items'] ?? null) ? $json['items'] : [],
                (string) ($json['motivo'] ?? ''),
                (string) ($json['metodo'] ?? 'efectivo')
            );
            return $this->response->setJSON(['success' => true, 'message' => 'Devolución registrada.'] + $r);
        } catch (\RuntimeException $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            log_message('error', 'Devolución: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo registrar la devolución.']);
        }
    }
}
