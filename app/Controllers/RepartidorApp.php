<?php
namespace App\Controllers;

use App\Models\StatusModel;

class RepartidorApp extends BaseController
{
    private function idRep(): int
    {
        return (int) session()->get('id_repartidor');
    }

    public function vista()
    {
        return view('Repartidor/app_movil');
    }

    public function pedidos()
    {
        $rows = \Config\Database::connect()->query("
            SELECT p.id, p.total, p.monto_pagado, p.tipo_pago, p.estado_actual,
                   CONCAT(c.nombre, ' ', IFNULL(c.apellido_paterno, '')) AS cliente,
                   c.tel, d.calle, d.numero, d.colonia, d.municipio, d.estado AS estado_dir
            FROM pedido p
            JOIN clientes c ON c.id_cliente = p.id_cliente
            LEFT JOIN direccion d
                   ON d.id = (SELECT MAX(id) FROM direccion WHERE id_cliente = c.id_cliente)
            WHERE p.id_repartidor = ?
              AND p.tipo_entrega = 'domicilio'
              AND p.estado_actual NOT IN ('Pedido', 'Venta confirmada', 'Pedido cancelado')
            ORDER BY p.id DESC", [$this->idRep()])->getResultArray();

        return $this->response->setJSON(['data' => $rows]);
    }

    public function estado()
    {
        $id     = (int) $this->request->getPost('id');
        $estado = $this->request->getPost('estado');

        if (!in_array($estado, ['Pedido en tránsito', 'Venta confirmada'], true)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false]);
        }

        $db = \Config\Database::connect();
        $esSuyo = $db->table('pedido')
            ->where('id', $id)->where('id_repartidor', $this->idRep())
            ->countAllResults();

        if (!$esSuyo) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false]);
        }

        (new StatusModel())->actualizarEstado($id, $estado);

        if ($estado === 'Venta confirmada') {
            $db->table('pedido')->where('id', $id)->update(['entregado_at' => date('Y-m-d H:i:s')]);
        }
        // Aquí después: si es 'Pedido en tránsito' → link de WhatsApp "pedido en camino"

        return $this->response->setJSON(['success' => true]);
    }

    public function incidencia()
    {
        $p = $this->request;
        \Config\Database::connect()->table('incidencia')->insert([
            'id_pedido'     => $p->getPost('id_pedido') ?: null,
            'id_repartidor' => $this->idRep(),
            'tipo'          => $p->getPost('tipo') ?: 'otro',
            'nota'          => $p->getPost('nota'),
            'lat'           => $p->getPost('lat') ?: null,
            'lng'           => $p->getPost('lng') ?: null,
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Respuestas del administrador a las ayudas de este repartidor, con los datos del pedido.
     */
    public function respuestas()
    {
        $db = \Config\Database::connect();
        $id = $this->idRep();

        $rows = $db->query("
            SELECT i.id, i.tipo, i.id_pedido, i.respuesta, i.respondido_at, i.respuesta_leida,
                   CONCAT(c.nombre, ' ', IFNULL(c.apellido_paterno, '')) AS cliente,
                   CONCAT_WS(', ', CONCAT('Calle ', d.calle, ' #', d.numero), d.colonia, d.municipio) AS direccion
            FROM incidencia i
            LEFT JOIN pedido p ON p.id = i.id_pedido
            LEFT JOIN clientes c ON c.id_cliente = p.id_cliente
            LEFT JOIN direccion d ON d.id = (SELECT MAX(id) FROM direccion WHERE id_cliente = c.id_cliente)
            WHERE i.id_repartidor = ? AND i.respuesta IS NOT NULL
            ORDER BY i.id DESC
            LIMIT 30", [$id])->getResultArray();

        $noLeidas = $db->table('incidencia')
            ->where('id_repartidor', $id)
            ->where('respuesta IS NOT NULL', null, false)
            ->where('respuesta_leida', 0)
            ->countAllResults();

        return $this->response->setJSON(['data' => $rows, 'no_leidas' => $noLeidas]);
    }

    /**
     * Marca como leídas todas las respuestas pendientes de este repartidor.
     */
    public function respuestasLeer()
    {
        \Config\Database::connect()->table('incidencia')
            ->where('id_repartidor', $this->idRep())
            ->where('respuesta IS NOT NULL', null, false)
            ->where('respuesta_leida', 0)
            ->update(['respuesta_leida' => 1]);

        return $this->response->setJSON(['success' => true]);
    }

    public function ubicacion()
    {
        \Config\Database::connect()->table('repartidor')
            ->where('id', $this->idRep())
            ->update([
                'ultima_lat'            => $this->request->getPost('lat'),
                'ultima_lng'            => $this->request->getPost('lng'),
                'ultima_actualizacion'  => date('Y-m-d H:i:s'),
            ]);
        return $this->response->setJSON(['success' => true]);
    }
}