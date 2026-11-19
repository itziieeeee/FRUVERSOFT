<?php
namespace App\Controllers;
use App\Models\ClienteModel;
use App\Models\DireccionModel;

class Clientes extends BaseController
{
    public function pantalla_clientes()
    {
        $clienteModel = new ClienteModel();
        $data['lista_clientes'] = $clienteModel->getListaClientes();

        return view('pantalla_clientes', $data);
    }

    public function actualizar()
    {
        $clienteModel   = new ClienteModel();
        $direccionModel = new DireccionModel();
        $id = $this->request->getPost('id_cliente');

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'ID no recibido']);
        }

        $dataCliente = [
            'nombre'           => trim((string) $this->request->getPost('nombre')),
            'apellido_paterno' => $this->request->getPost('ap'),
            'apellido_materno' => $this->request->getPost('am'),
            'tel'              => $this->request->getPost('tel'),
            'rfc'              => $this->request->getPost('rfc'),
        ];

        if ($dataCliente['nombre'] === '') {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'El nombre es obligatorio.']);
        }

        $tipo = $this->request->getPost('tipo_cliente');
        if (in_array($tipo, ['mayoreo', 'menudeo'], true)) {
            $dataCliente['tipo_cliente'] = $tipo;
        }

        // Dirección completa (antes solo se podía cambiar el estado)
        $dataDireccion = [
            'calle'     => trim((string) $this->request->getPost('calle')),
            'numero'    => trim((string) $this->request->getPost('numero')),
            'colonia'   => trim((string) $this->request->getPost('colonia')),
            'municipio' => trim((string) $this->request->getPost('municipio')),
            'estado'    => trim((string) $this->request->getPost('estado')),
        ];

        foreach (['calle' => 'la calle', 'numero' => 'el número', 'colonia' => 'la colonia', 'estado' => 'el estado'] as $campo => $texto) {
            if ($dataDireccion[$campo] === '') {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'Escribe ' . $texto . ' de la dirección.']);
            }
        }

        // Pin del mapa: o se guardan las dos coordenadas o ninguna. Si el formulario no las manda, no se tocan.
        $manda = $this->request->getPost('lat') !== null || $this->request->getPost('lng') !== null;
        if ($manda) {
            $lat = $this->coordenadaValida($this->request->getPost('lat'), -90, 90);
            $lng = $this->coordenadaValida($this->request->getPost('lng'), -180, 180);
            if ($lat === null || $lng === null) {
                $lat = $lng = null;
            }
            $dataDireccion['lat'] = $lat;
            $dataDireccion['lng'] = $lng;
        }

        $antes = $clienteModel->getDetalleCliente($id);

        $db = \Config\Database::connect();
        $db->transStart();
        try {
            $clienteModel->update($id, $dataCliente);

            $actual = $direccionModel->where('id_cliente', $id)->orderBy('id', 'DESC')->first();
            if ($actual) {
                $direccionModel->update($actual['id'], $dataDireccion);
            } else {
                $direccionModel->insert($dataDireccion + ['id_cliente' => $id]);
            }

            $db->transComplete();
            if (!$db->transStatus()) {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'No se pudo guardar el cliente.']);
            }

            // Solo se anota lo que de verdad cambió
            $nuevo = [
                'nombre' => $dataCliente['nombre'], 'apellido_paterno' => $dataCliente['apellido_paterno'], 'apellido_materno' => $dataCliente['apellido_materno'],
                'tel' => $dataCliente['tel'], 'rfc' => $dataCliente['rfc'], 'tipo_cliente' => $dataCliente['tipo_cliente'] ?? ($antes['tipo_cliente'] ?? null),
                'calle' => $dataDireccion['calle'], 'numero' => $dataDireccion['numero'], 'colonia' => $dataDireccion['colonia'],
                'municipio' => $dataDireccion['municipio'], 'estado' => $dataDireccion['estado'],
                'lat' => $dataDireccion['lat'] ?? ($antes['lat'] ?? null), 'lng' => $dataDireccion['lng'] ?? ($antes['lng'] ?? null),
            ];
            $cambios = [];
            foreach ($nuevo as $campo => $valor) {
                $anterior = $antes[$campo] ?? null;
                $esNumero = in_array($campo, ['lat', 'lng'], true);
                $igual = $esNumero ? (round((float) $anterior, 6) === round((float) $valor, 6) && ($anterior === null) === ($valor === null))
                                   : ((string) $anterior === (string) $valor);
                if (!$igual) { $cambios[$campo] = ['antes' => $anterior, 'despues' => $valor]; }
            }
            if ($cambios) {
                \App\Services\AuditoriaService::registrar(
                    'cliente.editar', 'cliente', $id,
                    'Cliente editado: ' . trim($dataCliente['nombre'] . ' ' . ($dataCliente['apellido_paterno'] ?? '')) . ' (cambió: ' . implode(', ', array_keys($cambios)) . ')',
                    $cambios
                );
            }

            return $this->response->setJSON(['status' => 'success', 'msg' => 'Actualizado']);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }

    /** Compras del cliente para el botón "Historial de compras" */
    public function historial($id = null)
    {
        $clienteModel = new ClienteModel();
        $cliente = $clienteModel->find((int) $id);

        if (!$cliente) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'msg' => 'Cliente no encontrado']);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'cliente' => trim($cliente['nombre'] . ' ' . ($cliente['apellido_paterno'] ?? '')),
            'pedidos' => $clienteModel->getHistorialCompras((int) $id),
        ]);
    }

    public function detalle($id = null)
    {
        $clienteModel = new ClienteModel();
        $cliente = $clienteModel->getDetalleCliente($id);
        return $this->response->setJSON(['cliente' => $cliente]);
    }

    public function eliminar($id = null)
    {
        $clienteModel = new ClienteModel();

        try {
            $tienePedidos = $clienteModel->contarPedidos($id);

            if ($tienePedidos > 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'msg'    => 'No se puede eliminar: este cliente tiene ' . $tienePedidos . ' pedido(s) registrado(s).'
                ]);
            }

            $antes = $clienteModel->find($id);
            $clienteModel->eliminarClienteCompleto($id);

            \App\Services\AuditoriaService::registrar(
                'cliente.eliminar', 'cliente', $id,
                'Cliente eliminado: ' . trim(($antes['nombre'] ?? '') . ' ' . ($antes['apellido_paterno'] ?? '')) ?: ('#' . $id)
            );

            return $this->response->setJSON(['status' => 'success', 'msg' => 'Cliente eliminado']);

        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Devuelve la coordenada como float si es numérica y está dentro del rango; si no, null.
     */
    private function coordenadaValida($valor, float $min, float $max): ?float
    {
        if ($valor === null || $valor === '' || !is_numeric($valor)) {
            return null;
        }

        $numero = (float) $valor;
        return ($numero >= $min && $numero <= $max) ? round($numero, 7) : null;
    }

    public function registrar()
    {
        $clienteModel   = new ClienteModel();
        $direccionModel = new DireccionModel();

        $dataCliente = [
            'nombre'           => $this->request->getPost('nombre'),
            'apellido_paterno' => $this->request->getPost('apellido_paterno'),
            'apellido_materno' => $this->request->getPost('apellido_materno'),
            'tel'              => $this->request->getPost('tel'),
            'rfc'              => $this->request->getPost('rfc'),
            'tipo_cliente'     => $this->request->getPost('tipo_cliente'),
        ];

        try {
            $clienteModel->insert($dataCliente);
            $idCliente = $clienteModel->getInsertID();

            $dataDireccion = [
                'calle'      => $this->request->getPost('calle'),
                'numero'     => $this->request->getPost('numero'),
                'colonia'    => $this->request->getPost('colonia'),
                'municipio'  => $this->request->getPost('municipio'),
                'estado'     => $this->request->getPost('estado'),
                'id_cliente' => $idCliente,
                'lat'        => $this->coordenadaValida($this->request->getPost('lat'), -90, 90),
                'lng'        => $this->coordenadaValida($this->request->getPost('lng'), -180, 180),
            ];

            // Un pin a medias no sirve: o se guardan las dos coordenadas o ninguna.
            if ($dataDireccion['lat'] === null || $dataDireccion['lng'] === null) {
                $dataDireccion['lat'] = null;
                $dataDireccion['lng'] = null;
            }

            $direccionModel->insert($dataDireccion);

            \App\Services\AuditoriaService::registrar(
                'cliente.crear', 'cliente', $idCliente,
                'Cliente nuevo: ' . trim(($dataCliente['nombre'] ?? '') . ' ' . ($dataCliente['apellido_paterno'] ?? '')) . ' (' . ($dataCliente['tipo_cliente'] ?? 'sin tipo') . ')'
            );

            return redirect()->to(base_url('pantalla_clientes'))
                             ->with('flash_success', 'Cliente registrado exitosamente');

        } catch (\Exception $e) {
            return redirect()->back()->with('flash_error', 'Error al registrar: ' . $e->getMessage());
        }
    }

    
public function obtener_ultimo_pedido($idCliente = null)
    {
        if (!$idCliente) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'ID no proporcionado']);
        }

        helper('whatsapp');
        $db = \Config\Database::connect();

        $cliente = $db->table('clientes')
            ->select('nombre, apellido_paterno, tel')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Cliente no encontrado']);
        }
        if (empty($cliente['tel'])) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'El cliente no tiene teléfono registrado']);
        }

        // Último pedido que no esté cancelado
        $pedido = $db->table('pedido')
            ->where('id_cliente', $idCliente)
            ->where('estado_actual !=', 'Pedido cancelado')
            ->orderBy('id', 'DESC')
            ->get()->getRowArray();

        if (!$pedido) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'El cliente todavía no tiene pedidos']);
        }

        // La clave de producto es "id" (no "id_producto") y el precio sale del pedido, no del catálogo
        $items = $db->table('producto_pedido pp')
            ->select('pp.cantidad, pp.unidad_venta AS unidad, pp.precio_venta AS precio_unitario, pp.subtotal, p.nombre AS nombre_producto')
            ->join('producto p', 'p.id = pp.id_producto')
            ->where('pp.id_pedido', $pedido['id'])
            ->get()->getResultArray();

        $nombre = trim($cliente['nombre'] . ' ' . $cliente['apellido_paterno']);

        return $this->response->setJSON([
            'status'   => 'success',
            'telefono' => formato_telefono_wa($cliente['tel']),
            'mensaje'  => armar_mensaje_ticket($pedido, $items, $nombre),
            'pedido'   => $pedido,
            'items'    => $items,
        ]);
    }

}