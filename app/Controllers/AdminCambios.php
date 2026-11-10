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

        $incidencias = $this->consultarIncidencias('abierta');

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

    /**
     * Incidencias ya atendidas (respondidas o cerradas), las más recientes primero.
     */
    public function historial()
    {
        try {
            return $this->response->setJSON([
                'success' => true,
                'data'    => $this->consultarIncidencias('resuelta', 40, true),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error al leer historial de incidencias: {msg}', ['msg' => $e->getMessage()]);
            return $this->response->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'No se pudo cargar el historial.']);
        }
    }

    /**
     * Incidencias con datos del repartidor, cliente y dirección del pedido.
     */
    private function consultarIncidencias(string $estado, int $limite = 0, bool $conRespuesta = false): array
    {
        $columnasRespuesta = $conRespuesta ? ', i.respuesta, i.respondido_at' : '';
        $limiteSql         = $limite > 0 ? ' LIMIT ' . (int) $limite : '';

        return \Config\Database::connect()->query("
            SELECT i.id, i.id_pedido, i.tipo, i.nota, i.lat, i.lng, i.fecha{$columnasRespuesta},
                   CONCAT(r.nombre, ' ', r.ap_p) AS repartidor,
                   CONCAT(c.nombre, ' ', IFNULL(c.apellido_paterno, '')) AS cliente,
                   CONCAT_WS(', ', CONCAT('Calle ', d.calle, ' #', d.numero), d.colonia, d.municipio) AS direccion
            FROM incidencia i
            JOIN repartidor r ON r.id = i.id_repartidor
            LEFT JOIN pedido p ON p.id = i.id_pedido
            LEFT JOIN clientes c ON c.id_cliente = p.id_cliente
            LEFT JOIN direccion d ON d.id = (SELECT MAX(id) FROM direccion WHERE id_cliente = c.id_cliente)
            WHERE i.estado = ? ORDER BY i.id DESC{$limiteSql}", [$estado])->getResultArray();
    }

    /**
     * Cierra la incidencia sin enviar respuesta.
     */
    public function resolver($id)
    {
        try {
            \Config\Database::connect()->table('incidencia')
                ->where('id', (int) $id)->update(['estado' => 'resuelta']);
            return $this->response->setJSON(['success' => true]);
        } catch (\Throwable $e) {
            log_message('error', 'Error al resolver incidencia {id}: {msg}', ['id' => $id, 'msg' => $e->getMessage()]);
            return $this->response->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'No se pudo cerrar la alerta: ' . $e->getMessage()]);
        }
    }

    /**
     * Guarda la respuesta del administrador para el repartidor y cierra la incidencia.
     */
    public function responder($id)
    {
        $texto = trim((string) $this->request->getPost('respuesta'));

        if ($texto === '' || mb_strlen($texto) > 1000) {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'La respuesta debe tener entre 1 y 1000 caracteres.']);
        }

        try {
            \Config\Database::connect()->table('incidencia')
                ->where('id', (int) $id)
                ->update([
                    'respuesta'       => $texto,
                    'respondido_at'   => date('Y-m-d H:i:s'),
                    'respuesta_leida' => 0,
                    'estado'          => 'resuelta',
                ]);
            return $this->response->setJSON(['success' => true]);
        } catch (\Throwable $e) {
            log_message('error', 'Error al responder incidencia {id}: {msg}', ['id' => $id, 'msg' => $e->getMessage()]);
            return $this->response->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'No se pudo enviar la respuesta: ' . $e->getMessage()]);
        }
    }
}
