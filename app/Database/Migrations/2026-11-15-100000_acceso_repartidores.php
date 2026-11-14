<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AccesoRepartidores extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->fieldExists('usuario', 'usuarios')) {
            $this->forge->addColumn('usuarios', [
                'usuario' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 80,
                    'null'       => true,
                    'after'      => 'nombre',
                ],
            ]);
        }
        if (!$db->fieldExists('debe_cambiar_password', 'usuarios')) {
            $this->forge->addColumn('usuarios', [
                'debe_cambiar_password' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'after'      => 'rol',
                ],
            ]);
        }
        if (!$db->fieldExists('activo', 'usuarios')) {
            $this->forge->addColumn('usuarios', [
                'activo' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                    'after'      => 'debe_cambiar_password',
                ],
            ]);
        }
        if (!$db->fieldExists('password_actualizado_at', 'usuarios')) {
            $this->forge->addColumn('usuarios', [
                'password_actualizado_at' => [
                    'type'  => 'DATETIME',
                    'null'  => true,
                    'after' => 'activo',
                ],
            ]);
        }

        // No se agregan claves foráneas porque el esquema original de usuarios
        // y repartidor no está versionado en este repositorio.
        $this->db->query('CREATE INDEX idx_usuarios_usuario ON usuarios (usuario)');
        $this->db->query('CREATE INDEX idx_usuarios_repartidor ON usuarios (id_repartidor)');
    }

    public function down()
    {
        // Se conservan las columnas para no borrar credenciales en una instalación activa.
    }
}
