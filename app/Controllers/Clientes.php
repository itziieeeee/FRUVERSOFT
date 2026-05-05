<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\DireccionModel;

class Clientes extends BaseController
{
    public function pantalla_clientes($id = null)
    {
        $model = new ClienteModel();

        $data['lista_clientes'] = $model->findAll();

        $data['cliente'] = null;
        if ($id !== null) {
            $data['cliente'] = $model->getDatosClientes($id);
        }

        return view('pantalla_clientes', $data);
    }

    public function pantalla_rcliente()
    {
        return view('pantalla_rcliente', [
            'errores'   => [],
            'old_input' => []
        ]);
    }

    public function registrar()
    {
        $clienteModel   = new ClienteModel();
        $direccionModel = new DireccionModel();

        $rules = [
            'nombre'           => 'required|min_length[2]',
            'apellido_paterno' => 'permit_empty',
            'apellido_materno' => 'permit_empty',
            'rfc'              => 'permit_empty|max_length[13]',
            'tipo_cliente'     => 'required',
            'tel'              => 'permit_empty|max_length[12]',
            'calle'            => 'required',
            'numero'           => 'required',
            'colonia'          => 'required',
            'municipio'        => 'permit_empty',
            'estado'           => 'required',
        ];

        if (! $this->validate($rules)) {
            return view('pantalla_rcliente', [
                'errores'   => $this->validator->getErrors(),
                'old_input' => $this->request->getPost()
            ]);
        }

        $idCliente = $clienteModel->insert([
            'nombre'           => $this->request->getPost('nombre'),
            'apellido_paterno' => $this->request->getPost('apellido_paterno'),
            'apellido_materno' => $this->request->getPost('apellido_materno'),
            'rfc'              => $this->request->getPost('rfc'),
            'tipo_cliente'     => $this->request->getPost('tipo_cliente'),
            'tel'              => $this->request->getPost('tel'),
        ]);

        $direccionModel->insert([
            'calle'      => $this->request->getPost('calle'),
            'numero'     => $this->request->getPost('numero'),
            'colonia'    => $this->request->getPost('colonia'),
            'municipio'  => $this->request->getPost('municipio'),
            'estado'     => $this->request->getPost('estado'),
            'id_cliente' => $idCliente,
        ]);

        return redirect()->to(base_url('pantalla_clientes'));
    }

    public function detalle($id = null)
    {
        if (!$id) return $this->response->setJSON(['error' => 'No ID']);

        $db = \Config\Database::connect();
        
        $query = $db->table('clientes')
                    ->select('clientes.*, direccion.calle, direccion.numero, direccion.colonia, direccion.municipio, direccion.estado')
                    ->join('direccion', 'direccion.id_cliente = clientes.id_cliente', 'left')
                    ->where('clientes.id_cliente', $id)
                    ->get();

        $cliente = $query->getRow();
        
        $pedidoModel = new \App\Models\PedidoModel();
        $historial = $pedidoModel->where('id_cliente', $id)->findAll();

        return $this->response->setJSON([
            'cliente'   => $cliente,
            'historial' => $historial
        ]);
    }
}