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
            'nombre'           => $this->request->getPost('nombre'),
            'apellido_paterno' => $this->request->getPost('ap'),
            'apellido_materno' => $this->request->getPost('am'),
            'tel'              => $this->request->getPost('tel'),
            'rfc'              => $this->request->getPost('rfc'),
        ];

        try {
            $clienteModel->update($id, $dataCliente);
            $estado = $this->request->getPost('estado');
            $direccionModel->actualizarEstado($id, $estado);
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Actualizado']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'msg' => $e->getMessage()]);
        }
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

            $clienteModel->eliminarClienteCompleto($id);

            return $this->response->setJSON(['status' => 'success', 'msg' => 'Cliente eliminado']);

        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'msg' => $e->getMessage()]);
        }
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
            ];

            $direccionModel->insert($dataDireccion);

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