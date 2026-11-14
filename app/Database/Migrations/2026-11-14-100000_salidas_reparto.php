<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SalidasReparto extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_repartidor' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'estado' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'borrador'],
            'max_pedidos' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 6],
            'km_total' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'fuente_distancia' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'geometria' => ['type' => 'LONGTEXT', 'null' => true],
            'local_lat' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'local_lng' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'iniciada_at' => ['type' => 'DATETIME', 'null' => true],
            'finalizada_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['id_repartidor', 'estado']);
        $this->forge->createTable('salida', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_salida' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_pedido' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'orden' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true],
            'estado' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pendiente'],
            'direccion' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'lat' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'lng' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'km_desde_anterior' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'asignado_at' => ['type' => 'DATETIME', 'null' => true],
            'entregado_at' => ['type' => 'DATETIME', 'null' => true],
            'entrega_lat' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'entrega_lng' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'nota' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['id_salida', 'orden']);
        $this->forge->addKey(['id_salida', 'estado']);
        $this->forge->addKey('id_pedido');
        $this->forge->createTable('salida_pedido', true);
    }

    public function down()
    {
        $this->forge->dropTable('salida_pedido', true);
        $this->forge->dropTable('salida', true);
    }
}
