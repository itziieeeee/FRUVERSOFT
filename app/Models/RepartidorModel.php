<?php 
namespace App\Models;

use CodeIgniter\Model;

class RepartidorModel extends Model{
    protected $table      = 'repartidor';
    protected $primaryKey = 'id';

protected $allowedFields = ['nombre', 'ap_p', 'ap_m', 'tel', 'direccion', 'notas','foto'];
}