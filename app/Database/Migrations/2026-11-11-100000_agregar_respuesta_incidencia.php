<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AgregarRespuestaIncidencia extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('incidencia')) {
            return;
        }

        $campos = [];
        if (! $this->db->fieldExists('respuesta', 'incidencia')) {
            $campos['respuesta'] = ['type' => 'TEXT', 'null' => true];
        }
        if (! $this->db->fieldExists('respondido_at', 'incidencia')) {
            $campos['respondido_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (! $this->db->fieldExists('respuesta_leida', 'incidencia')) {
            $campos['respuesta_leida'] = ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0];
        }

        if ($campos !== []) {
            $this->forge->addColumn('incidencia', $campos);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('incidencia')) {
            return;
        }

        foreach (['respuesta', 'respondido_at', 'respuesta_leida'] as $columna) {
            if ($this->db->fieldExists($columna, 'incidencia')) {
                $this->forge->dropColumn('incidencia', $columna);
            }
        }
    }
}
