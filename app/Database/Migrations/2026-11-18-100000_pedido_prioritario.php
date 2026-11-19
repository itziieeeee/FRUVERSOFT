<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Pedidos urgentes: pedido.prioritario = 1 hace que el pedido pase primero en la ruta del
 * repartidor, que entre aunque ya haya 6 pedidos listos y que se vea marcado como URGENTE.
 */
class PedidoPrioritario extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('prioritario', 'pedido')) {
            $this->db->query('ALTER TABLE `pedido` ADD COLUMN `prioritario` TINYINT(1) NOT NULL DEFAULT 0');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('prioritario', 'pedido')) {
            $this->db->query('ALTER TABLE `pedido` DROP COLUMN `prioritario`');
        }
    }
}
