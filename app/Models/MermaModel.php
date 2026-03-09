<?php

namespace App\Models;

use CodeIgniter\Model;

class MermaModel extends Model
{
    protected $table      = 'mermas'; 
    protected $primaryKey = 'id_merma';


    protected $allowedFields = ['id_producto', 'fecha', 'cantidad', 'motivo'];

    protected $useTimestamps = false;
    public function pantalla_inventario() {
    $db = \Config\Database::connect();
    
    
    $data['lista_productos'] = $db->table('producto')->select('id_producto, producto as nombre')->get()->getResultArray();


    $data['entradas'] = $db->table('entrada')->get()->getResultArray();
    $data['mermas']   = $db->table('merma')->get()->getResultArray();
    $data['productos'] = $db->table('producto')->get()->getResultArray();

    return view('inventario', $data);
}
}
