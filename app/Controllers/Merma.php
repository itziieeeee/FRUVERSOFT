<?php

namespace App\Controllers;
use App\Models\MermaModel; 

class Merma extends BaseController {
    
    public function index() {
    $model = new MermaModel();
    
    // Obtenemos los datos del modelo
    $data['lista_productos'] = $model->get_lista_productos();
    
    // Si necesitas los otros datos que tenías en el modelo:
    $db = \Config\Database::connect();
    $data['entradas'] = $db->table('entrada')->get()->getResultArray();
    $data['mermas']   = $db->table('merma')->get()->getResultArray();
    $data['productos'] = $db->table('producto')->get()->getResultArray();
// En app/Controllers/Merma.php
echo "<pre>";
print_r($data['lista_productos']);
echo "</pre>";
die(); // Esto detiene todo y muestra los datos en bruto
    // AQUÍ es donde se carga la vista
    return view('inventario', $data);
}
}