<?php
namespace App\Models;
use CodeIgniter\Model;

class ProductoModel extends Model
{
    // 1. Configuración básica
    protected $table      = 'producto'; 
    protected $primaryKey = 'id';

    // 2. Campos que permiten que se guarden desde formularios
    protected $allowedFields = ['nombre', 'descripcion', 'imagen', 'e_total', 'precio']; 

    // 3. Retorno de datos
    protected $returnType = 'array';

    // 4. Timestamps
    protected $useTimestamps = false; 
}
