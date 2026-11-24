<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Vincula la cuenta de acceso de un cliente (usuarios) con su ficha de cliente (clientes).
 * Hasta ahora no había relación, así que un cliente que se registraba no podía hacer pedidos.
 * La ficha se crea sola la primera vez que el cliente confirma un pedido desde el carrito.
 */
class UsuarioCliente extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('id_cliente', 'usuarios')) {
            $this->db->query('ALTER TABLE `usuarios` ADD COLUMN `id_cliente` INT NULL DEFAULT NULL');
            $this->db->query('ALTER TABLE `usuarios` ADD KEY `idx_usuarios_cliente` (`id_cliente`)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('id_cliente', 'usuarios')) {
            $this->db->query('ALTER TABLE `usuarios` DROP KEY `idx_usuarios_cliente`');
            $this->db->query('ALTER TABLE `usuarios` DROP COLUMN `id_cliente`');
        }
    }
}
