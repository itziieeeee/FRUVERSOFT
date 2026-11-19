<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Escáner de códigos.
 *
 * Todos los productos ya tienen su identificador propio (producto.codigo, tipo PRD-A1B2C3),
 * que se imprime como etiqueta Code128 para lo que no trae código de fábrica (costales, cajas, granel).
 * Los productos empacados (abarrotes) además pueden guardar el código de barras del empaque
 * (EAN-13, EAN-8, UPC...) en codigo_barras. Es opcional y único.
 */
class ProductoCodigoBarras extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('codigo_barras', 'producto')) {
            $this->db->query('ALTER TABLE `producto` ADD COLUMN `codigo_barras` VARCHAR(32) NULL DEFAULT NULL');
            $this->db->query('ALTER TABLE `producto` ADD UNIQUE KEY `uq_producto_codigo_barras` (`codigo_barras`)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('codigo_barras', 'producto')) {
            $this->db->query('ALTER TABLE `producto` DROP INDEX `uq_producto_codigo_barras`');
            $this->db->query('ALTER TABLE `producto` DROP COLUMN `codigo_barras`');
        }
    }
}
