<?php

namespace App\Controllers;
use App\Models\StatusModel;

class Status extends BaseController
{
    // CAMBIAR ESTADO (AJAX)
    public function cambiar()
    {
        $model = new StatusModel();

        $id = $this->request->getPost('id');
        $estado = $this->request->getPost('estado');

        $model->actualizarEstado($id, $estado);

        return $this->response->setJSON([
            'success' => true
        ]);
    }

    // MOSTRAR VISTA
   public function pantalla_pedidos()
{
    $model = new StatusModel();

    $data['sp'] = $model->obtenerPedidos();

    return view('pantalla_pedidos', $data);
}
}
