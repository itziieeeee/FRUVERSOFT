<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model {
    protected $table      = 'userregistrado'; // Asegúrate de que así se llame en tu BD
    protected $primaryKey = 'id';
    
    // Estos campos DEBEN coincidir con los nombres de las columnas en tu base de datos
    protected $allowedFields = ['nombre_completo', 'nusuario', 'contrasena']; 

    // Opcional: Esto ayuda a que CodeIgniter maneje las fechas automáticamente
    protected $useTimestamps = false; 
}