<?php

namespace App\Controllers;
use App\Models\MermaModel; 

class Merma extends BaseController {
    
    public function guardar() {
        $model = new MermaModel();

        $data = [
            'id_producto' => $this->request->getPost('id_producto'),
            'fecha'       => date('Y-m-d'),
            'cantidad'    => $this->request->getPost('cantidad'),
            'motivo'      => $this->request->getPost('descripcion')
        ];

        if ($model->insert($data)) {
            return $this->response->setJSON(['status' => 'success']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al insertar']);
    }
}