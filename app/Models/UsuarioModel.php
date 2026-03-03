<?php
namespace App\Models;
use CodeIgniter\Model;

class UsuarioModel extends Model {
    protected $table      = 'userregistrado'; // Nombre de tu tabla en la BD
    protected $primaryKey = 'id';
    protected $allowedFields = ['nusuario', 'contrasena', 'nombre_completo']; // Campos permitidos
}