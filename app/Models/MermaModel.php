<?php

namespace App\Models;

use CodeIgniter\Model;

class MermaModel extends Model
{
    public function get_lista_productos() {
    return $this->db->table('producto')
                    ->select('id_producto, producto as nombre')
                    ->get()
                    ->getResultArray();
}
}
   