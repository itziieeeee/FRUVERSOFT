<?php

namespace App\Controllers;

use App\Models\ClientesModel;

class Clientes extends BaseController{

    public function pantalla_clientes($id = null) {
        $model = new ClientesModel();
        
        $data['lista_clientes'] = $model->findAll(); 
        
        $data['cliente'] = null;
        if ($id !== null) {
            $data['cliente'] = $model->getDatosClientes($id);
        }

        return view('pantalla_clientes', $data);
    }

    // 🔥 ESTE ES EL IMPORTANTE
    public function detalle($id)
    {
        $model = new ClientesModel();
        $pedidoModel = new \App\Models\PedidoModel();

        $cliente = $model->find($id);
        $historial = $pedidoModel->where('id_cliente', $id)->findAll();

        return $this->response->setJSON([
            'cliente' => $cliente,
            'historial' => $historial
        ]);
    }
}

 