<?php
namespace App\Controllers;

class AdminCambios extends BaseController
{
    public function cambios()
    {
        $db    = \Config\Database::connect();
        $ahora = $db->query('SELECT NOW() AS n')->getRow()->n;
        $desde = $this->request->getGet('desde');

        $pedidos = [];
        if ($desde) {
            $pedidos = $db->query("
                SELECT p.id, p.estado_actual, CONCAT(r.nombre, ' ', r.ap_p) AS repartidor
                FROM pedido p LEFT JOIN repartidor r ON r.id = p.id_repartidor
                WHERE p.updated_at >= ?", [$desde])->getResultArray();
        }

        $incidencias = $db->query("
            SELECT i.id, i.id_pedido, i.tipo, i.nota, i.lat, i.lng, i.fecha,
                   CONCAT(r.nombre, ' ', r.ap_p) AS repartidor
            FROM incidencia i JOIN repartidor r ON r.id = i.id_repartidor
            WHERE i.estado = 'abierta' ORDER BY i.id DESC")->getResultArray();

        $repartidores = $db->table('repartidor')
            ->select('id, nombre, ap_p, ultima_lat, ultima_lng, ultima_actualizacion')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'ahora'        => $ahora,
            'pedidos'      => $pedidos,
            'incidencias'  => $incidencias,
            'repartidores' => $repartidores,
        ]);
    }

    public function resolver($id)
    {
        \Config\Database::connect()->table('incidencia')
            ->where('id', $id)->update(['estado' => 'resuelta']);
        return $this->response->setJSON(['success' => true]);
    }
}