<?php
namespace App\Controllers;

use App\Models\StatusModel;
use App\Services\SalidasRepartoService;

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

    /** Vista previa del recorrido con los pedidos listos del repartidor (no guarda nada). */
    public function recorridoPrevia()
    {
        try {
            return $this->response->setJSON([
                'success' => true,
                'data'    => (new SalidasRepartoService())->vistaPrevia($this->idRep()),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /** El repartidor arranca su recorrido: se crea la salida y sus pedidos pasan a "en tránsito". */
    public function recorridoIniciar()
    {
        try {
            return $this->response->setJSON([
                'success' => true,
                'data'    => (new SalidasRepartoService())->iniciarRecorrido($this->idRep()),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function salidaActiva()
    {
        try {
            return $this->response->setJSON([
                'success' => true,
                'data'    => (new SalidasRepartoService())->activaDeRepartidor($this->idRep()),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /** Carpeta privada de las fotos y firmas de entrega (fuera de public: solo se ven con sesión de administrador) */
    public static function carpetaEvidencias(): string
    {
        return WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'entregas' . DIRECTORY_SEPARATOR;
    }

    /**
     * Guarda una imagen subida (foto o firma) con un nombre al azar.
     * @return string|null nombre del archivo, o null si no se mandó nada
     * @throws \RuntimeException si el archivo no es una imagen válida
     */
    private function guardarImagen(string $campo): ?string
    {
        $archivo = $this->request->getFile($campo);
        if (!$archivo || $archivo->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (!$archivo->isValid() || $archivo->hasMoved()) {
            throw new \RuntimeException('No se pudo recibir la imagen (' . $campo . '). Intenta de nuevo.');
        }
        if ($archivo->getSize() > 4 * 1024 * 1024) {
            throw new \RuntimeException('La imagen es muy pesada (máximo 4 MB).');
        }

        // Se revisa el contenido real, no solo la extensión
        $info = @getimagesize($archivo->getTempName());
        $tipos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
        if (!$info || !isset($tipos[$info[2]])) {
            throw new \RuntimeException('El archivo no es una imagen válida (JPG, PNG o WEBP).');
        }

        $carpeta = self::carpetaEvidencias();
        if (!is_dir($carpeta) && !mkdir($carpeta, 0775, true) && !is_dir($carpeta)) {
            throw new \RuntimeException('No se pudo preparar la carpeta de evidencias.');
        }

        $nombre = bin2hex(random_bytes(16)) . '.' . $tipos[$info[2]];
        $archivo->move($carpeta, $nombre);
        return $nombre;
    }

    public function entregarSalidaPedido($idPedido)
    {
        $lat = $this->request->getPost('lat');
        $lng = $this->request->getPost('lng');
        $lat = is_numeric($lat) ? (float) $lat : null;
        $lng = is_numeric($lng) ? (float) $lng : null;
        $nota = trim((string) $this->request->getPost('nota'));

        $cobro        = (float) $this->request->getPost('cobro');
        $metodo       = (string) $this->request->getPost('metodo');
        $sinEvidencia = $this->request->getPost('sin_evidencia') === '1';

        $guardados = [];
        try {
            if ($cobro < 0) {
                throw new \RuntimeException('El monto cobrado no puede ser negativo.');
            }

            $foto  = $this->guardarImagen('foto');
            $firma = $this->guardarImagen('firma');
            $guardados = array_filter([$foto, $firma]);

            // La entrega necesita prueba: foto o firma. Si no se puede, debe explicarse en la nota.
            if (!$foto && !$firma) {
                if (!$sinEvidencia || mb_strlen($nota) < 5) {
                    throw new \RuntimeException('Toma una foto o pide la firma del cliente. Si no es posible, marca "Sin evidencia" y explica el motivo en la nota.');
                }
            }

            $data = (new SalidasRepartoService())->entregar(
                $this->idRep(),
                (int) $idPedido,
                $lat,
                $lng,
                $nota !== '' ? $nota : null,
                ['foto' => $foto, 'firma' => $firma, 'cobro' => $cobro, 'metodo' => $metodo]
            );
            return $this->response->setJSON(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            // Si la entrega no se pudo guardar, no quedan archivos huérfanos
            foreach ($guardados as $nombre) {
                @unlink(self::carpetaEvidencias() . $nombre);
            }
            return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function pedidos()
    {
        $rows = \Config\Database::connect()->query("
            SELECT p.id, p.total, p.monto_pagado, p.tipo_pago, p.estado_actual, p.prioritario,
                   CONCAT(c.nombre, ' ', IFNULL(c.apellido_paterno, '')) AS cliente,
                   c.tel, d.calle, d.numero, d.colonia, d.municipio, d.estado AS estado_dir
            FROM pedido p
            JOIN clientes c ON c.id_cliente = p.id_cliente
            LEFT JOIN direccion d
                   ON d.id = (SELECT MAX(id) FROM direccion WHERE id_cliente = c.id_cliente)
            WHERE p.id_repartidor = ?
              AND p.tipo_entrega = 'domicilio'
              AND p.estado_actual NOT IN ('Pedido', 'Venta confirmada', 'Pedido cancelado')
            ORDER BY p.prioritario DESC, p.id DESC", [$this->idRep()])->getResultArray();

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