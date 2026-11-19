<?php

namespace App\Controllers;

use App\Services\CobranzaService;

/**
 * Cobranza de créditos: lista lo que los clientes deben y arma el recordatorio de WhatsApp.
 * El pago de un abono usa la misma ruta de siempre (pedido/actualizar_pago).
 */
class Cobranza extends BaseController
{
    public function index()
    {
        return view('cobranza', (new CobranzaService())->listar());
    }

    /** Cambia la fecha límite de un pedido */
    public function vencimiento()
    {
        $fecha = trim((string) $this->request->getPost('fecha'));
        $ok = (new CobranzaService())->guardarVencimiento(
            (int) $this->request->getPost('id_pedido'),
            $fecha === '' ? null : $fecha
        );

        return $this->response->setJSON([
            'success' => $ok,
            'message' => $ok ? 'Fecha de vencimiento guardada.' : 'La fecha no es válida.',
        ]);
    }

    /** Se abrió el recordatorio de WhatsApp de estos pedidos */
    public function recordado()
    {
        $ids = $this->request->getPost('ids');
        $n = (new CobranzaService())->marcarRecordado(is_array($ids) ? $ids : explode(',', (string) $ids));

        return $this->response->setJSON(['success' => true, 'actualizados' => $n]);
    }

    /** Plazo general del crédito y días de aviso */
    public function configuracion()
    {
        $ok = (new CobranzaService())->guardarConfig(
            (int) $this->request->getPost('plazo'),
            (int) $this->request->getPost('aviso')
        );

        return $this->response->setJSON([
            'success' => $ok,
            'message' => $ok ? 'Configuración guardada.' : 'El plazo debe ser de 1 a 365 días y el aviso de 0 a 60.',
        ]);
    }
}
