<?php

namespace App\Controllers;

use App\Services\AuditoriaService;

/**
 * Evidencia de entrega para el administrador: foto, firma, cobro y ubicación donde se entregó.
 * Los archivos viven en writable/uploads/entregas (no son públicos) y solo se sirven con sesión de administrador.
 */
class Entregas extends BaseController
{
    public function ver($idPedido)
    {
        $db = \Config\Database::connect();

        $filas = $db->query(
            "SELECT sp.*, s.id_repartidor, p.total, p.monto_pagado,
                    CONCAT_WS(' ', r.nombre, r.ap_p) AS repartidor,
                    CONCAT_WS(' ', c.nombre, c.apellido_paterno) AS cliente
               FROM salida_pedido sp
               JOIN salida s ON s.id = sp.id_salida
               JOIN pedido p ON p.id = sp.id_pedido
               LEFT JOIN repartidor r ON r.id = s.id_repartidor
               LEFT JOIN clientes c ON c.id_cliente = p.id_cliente
              WHERE sp.id_pedido = ? AND sp.estado = 'entregado'
              ORDER BY sp.id DESC",
            [(int) $idPedido]
        )->getResultArray();

        if (!$filas) {
            return $this->response->setStatusCode(404)->setBody('Este pedido no tiene una entrega registrada por el repartidor.');
        }

        $e = $filas[0];
        $local = $e['entregado_at']
            ? (new \DateTime($e['entregado_at'], new \DateTimeZone(date_default_timezone_get())))->setTimezone(new \DateTimeZone('America/Mexico_City'))->format('d/m/Y H:i')
            : null;

        // A cuántos metros del domicilio se marcó la entrega (si hay las dos ubicaciones)
        $metros = null;
        if ($e['lat'] !== null && $e['entrega_lat'] !== null) {
            $r = 6371000.0;
            $a1 = deg2rad((float) $e['lat']); $a2 = deg2rad((float) $e['entrega_lat']);
            $dl = deg2rad((float) $e['entrega_lng'] - (float) $e['lng']);
            $h = sin(($a2 - $a1) / 2) ** 2 + cos($a1) * cos($a2) * sin($dl / 2) ** 2;
            $metros = (int) round(2 * $r * asin(min(1.0, sqrt($h))));
        }

        return view('entrega_evidencia', [
            'e'      => $e,
            'folio'  => AuditoriaService::folio($idPedido),
            'local'  => $local,
            'metros' => $metros,
        ]);
    }

    /** Entrega el archivo de una foto o firma (solo nombres generados por el sistema) */
    public function archivo($nombre)
    {
        if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', (string) $nombre)) {
            return $this->response->setStatusCode(404)->setBody('No encontrado');
        }

        $ruta = RepartidorApp::carpetaEvidencias() . $nombre;
        if (!is_file($ruta)) {
            return $this->response->setStatusCode(404)->setBody('No encontrado');
        }

        $tipos = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $ext = pathinfo($nombre, PATHINFO_EXTENSION);

        return $this->response
            ->setHeader('Content-Type', $tipos[$ext])
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody(file_get_contents($ruta));
    }
}
