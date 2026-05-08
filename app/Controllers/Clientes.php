<?php
namespace App\Controllers;
use App\Models\ClienteModel;
use App\Models\DireccionModel;

class Clientes extends BaseController
{
  public function pantalla_clientes()
{
    $db = \Config\Database::connect();

    $data['lista_clientes'] = $db->table('clientes')
        ->select('clientes.*, direccion.estado')
        ->join('direccion', 'direccion.id_cliente = clientes.id_cliente', 'left')
        ->get()
        ->getResultArray();

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
            $direccionModel->where('id_cliente', $id)->set(['estado' => $estado])->update();
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Actualizado']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }

    public function detalle($id = null)
    {
        $db = \Config\Database::connect();
        $cliente = $db->table('clientes')
            ->select('clientes.*, direccion.estado')
            ->join('direccion', 'direccion.id_cliente = clientes.id_cliente', 'left')
            ->where('clientes.id_cliente', $id)
            ->get()->getRow();
        return $this->response->setJSON(['cliente' => $cliente]);
    }

  
    public function eliminar($id = null)
{
    try {
        $db = \Config\Database::connect();

        // Verificar si tiene pedidos
        $tienePedidos = $db->table('pedido')
                           ->where('id_cliente', $id)
                           ->countAllResults();

        if ($tienePedidos > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'No se puede eliminar: este cliente tiene ' . $tienePedidos . ' pedido(s) registrado(s).'
            ]);
        }

        $db->table('direccion')->where('id_cliente', $id)->delete();
        $db->table('clientes')->where('id_cliente', $id)->delete();

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

        // Redirige al CRUD 
        return redirect()->to(base_url('pantalla_clientes'))
                         ->with('flash_success', 'Cliente registrado exitosamente');

    } catch (\Exception $e) {
        return redirect()->back()->with('flash_error', 'Error al registrar: ' . $e->getMessage());
    }


    }
}