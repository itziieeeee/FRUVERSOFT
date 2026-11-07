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
        return $this->response->setJSON([
            'status' => 'error',
            'msg' => 'ID no proporcionado'
        ]);
    }

    $db = \Config\Database::connect();

    // 1. Obtener el pedido más reciente del cliente.
    // La clave primaria real de pedido es "id".
    $pedido = $db->table('pedido')
        ->where('id_cliente', $idCliente)
        ->orderBy('id', 'DESC')
        ->get()
        ->getRowArray();

    $items = [];

    if ($pedido) {
        // 2. Obtener los productos asociados al pedido.
        $items = $db->table('producto_pedido')
            ->select('producto_pedido.*, producto.nombre AS nombre_producto, producto.precio AS precio_catalogo')
            ->join(
                'producto',
                'producto.id_producto = producto_pedido.id_producto'
            )
            ->where('producto_pedido.id_pedido', $pedido['id'])
            ->get()
            ->getResultArray();
    }

    return $this->response->setJSON([
        'status' => 'success',
        'pedido' => $pedido,
        'items' => $items
    ]);
}

}