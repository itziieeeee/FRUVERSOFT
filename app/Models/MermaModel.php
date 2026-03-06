<?php

namespace App\Models;

use CodeIgniter\Model;

class MermaModel extends Model
{
    protected $table = 'merma';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'id_producto',
        'cantidad',
        'motivo'
    ];
}