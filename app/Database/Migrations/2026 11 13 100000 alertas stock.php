<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Alertas predictivas de stock crítico:
 *  - producto.dias_reposicion: cuántos días tarda en llegar la mercancía de ese producto.
 *  - alertas_config: parámetros del cálculo (ventana de días, colchón y días objetivo).
 */
class AlertasStock extends Migration
{
    public function up()
    {
        $this->db->query(
            'ALTER TABLE `producto` ADD COLUMN IF NOT EXISTS `dias_reposicion` DECIMAL(4,1) NOT NULL DEFAULT 2.0'
        );

        $this->db->query(
            'CREATE TABLE IF NOT EXISTS `alertas_config` (
                `clave` VARCHAR(50) NOT NULL,
                `valor` VARCHAR(50) NOT NULL,
                PRIMARY KEY (`clave`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
        );

        $this->db->query(
            "INSERT IGNORE INTO `alertas_config` (`clave`, `valor`) VALUES
                ('ventana_dias', '14'),
                ('colchon_dias', '1'),
                ('dias_objetivo', '5')"
        );
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `alertas_config`');
        $this->db->query('ALTER TABLE `producto` DROP COLUMN IF EXISTS `dias_reposicion`');
    }
}