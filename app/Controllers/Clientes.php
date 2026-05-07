<?php
namespace App\Controllers;
use App\Models\ClienteModel;
use App\Models\DireccionModel;

class Clientes extends BaseController
{
    public function pantalla_clientes()
    {
        $model = new ClienteModel();
        $data['lista_clientes'] = $model->findAll();
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
            $db->table('direccion')->where('id_cliente', $id)->delete();
            $db->table('clientes')->where('id_cliente', $id)->delete();
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Cliente eliminado']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }

    public function registrar()
    {
        // tu código existente de registrar
    }

    public function pantalla_rcliente()
    {
        return view('pantalla_rcliente');
    }
}