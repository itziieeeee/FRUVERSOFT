<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Historial de costos y ganancias.
 *
 * entrada.precio_compra es el precio por UNIDAD DE COMPRA (por ejemplo, por caja)
 * y el stock y las ventas se manejan en UNIDAD DE VENTA (por ejemplo, kilo).
 * Para saber cuánto cuesta cada kilo hay que guardar cuántos kilos trae una caja
 * (valor_conversion). EntradaModel::registrarEntradas() ya lo guarda solo cuando
 * la columna existe; las entradas anteriores quedan en NULL (se asume 1 a 1 y se
 * pueden corregir desde la pantalla de costos).
 */
class CostosConversion extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('valor_conversion', 'entrada')) {
            $this->db->query('ALTER TABLE `entrada` ADD COLUMN `valor_conversion` DECIMAL(10,4) NULL DEFAULT NULL');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('valor_conversion', 'entrada')) {
            $this->db->query('ALTER TABLE `entrada` DROP COLUMN `valor_conversion`');
        }
    }
}
