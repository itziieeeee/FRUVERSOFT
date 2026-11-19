<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Cobranza de créditos.
 *
 *  pedido.fecha_vencimiento       fecha límite de pago de ese pedido (opcional: si está vacía se calcula con el plazo general)
 *  pedido.recordatorio_credito_at cuándo se abrió por última vez el recordatorio de WhatsApp de ese pedido
 *  alertas_config                 plazo general del crédito y días de aviso antes de vencer
 */
class CreditoVencimiento extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('fecha_vencimiento', 'pedido')) {
            $this->db->query('ALTER TABLE `pedido` ADD COLUMN `fecha_vencimiento` DATE NULL DEFAULT NULL');
        }
        if (! $this->db->fieldExists('recordatorio_credito_at', 'pedido')) {
            $this->db->query('ALTER TABLE `pedido` ADD COLUMN `recordatorio_credito_at` DATETIME NULL DEFAULT NULL');
        }

        if ($this->db->tableExists('alertas_config')) {
            $this->db->query("INSERT IGNORE INTO `alertas_config` (`clave`, `valor`) VALUES ('credito_plazo_dias', '15'), ('credito_aviso_dias', '3')");
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('recordatorio_credito_at', 'pedido')) {
            $this->db->query('ALTER TABLE `pedido` DROP COLUMN `recordatorio_credito_at`');
        }
        if ($this->db->fieldExists('fecha_vencimiento', 'pedido')) {
            $this->db->query('ALTER TABLE `pedido` DROP COLUMN `fecha_vencimiento`');
        }
        if ($this->db->tableExists('alertas_config')) {
            $this->db->query("DELETE FROM `alertas_config` WHERE `clave` IN ('credito_plazo_dias', 'credito_aviso_dias')");
        }
    }
}
