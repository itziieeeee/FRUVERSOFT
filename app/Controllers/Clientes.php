<?php

namespace App\Controllers;

use App\Models\ClientesModel;

class Clientes extends BaseController {

    public function pantalla_clientes($id = null) {
        $model = new ClientesModel();
        
        // 1. Cargamos TODOS los clientes para las tablas de Mayoreo/Menudeo
        $data['lista_clientes'] = $model->findAll(); 
        
        // 2. Cargamos el detalle solo si hay un ID
        $data['cliente'] = null;
        if ($id !== null) {
            $data['cliente'] = $model->getDatosClientes($id);
        }

        // 3. PASAMOS LA VARIABLE $data A LA VISTA
        return view('pantalla_clientes', $data);
    } 
}