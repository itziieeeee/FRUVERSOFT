<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * El módulo de vendedores guarda usuarios con rol 'vendedor', pero la columna
 * usuarios.rol solo aceptaba admin, cliente y repartidor. Se agrega 'vendedor'.
 */
class RolVendedor extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `usuarios` MODIFY `rol` ENUM('admin','cliente','repartidor','vendedor') NOT NULL DEFAULT 'cliente'");
    }

    public function down()
    {
        // Los vendedores existentes pasan a cliente para no perder las cuentas.
        $this->db->query("UPDATE `usuarios` SET `rol` = 'cliente' WHERE `rol` = 'vendedor'");
        $this->db->query("ALTER TABLE `usuarios` MODIFY `rol` ENUM('admin','cliente','repartidor') NOT NULL DEFAULT 'cliente'");
    }
}
