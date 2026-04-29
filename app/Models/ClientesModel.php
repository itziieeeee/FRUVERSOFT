<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientesModel extends Model{
    protected $table='clientes';
    protected $primaryKey = 'id_cliente';
    protected $returnType = 'array';

    public function getDatosClientes($id_cliente){
        return $this->db->table('clientes as c')
        ->select('c.*,d.calle,d.numero,d.colonia,d.municipio,d.estado')
        ->join('direccion as d','d.id_cliente=c.id_cliente','left')
        ->where('c.id_cliente',$id_cliente)
        ->get()
        ->getRowArray();
    }

}