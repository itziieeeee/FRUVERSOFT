<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tablas nuevas:
 *  auditoria          historial de cambios (quién, cuándo, qué)
 *  pago               cada cobro y reembolso, para el corte de caja
 *  corte_caja         cortes de caja guardados
 *  devolucion(+det.)  productos que el cliente regresa
 * y columnas de evidencia de entrega y cobro en salida_pedido.
 *
 * Los pagos que ya existían se cargan en `pago` con la fecha del pedido (no se sabe la fecha real del abono).
 */
class AuditoriaCajaDevoluciones extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `auditoria` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `fecha` DATETIME NOT NULL,
            `id_usuario` INT NULL,
            `usuario` VARCHAR(120) NULL,
            `rol` VARCHAR(20) NULL,
            `accion` VARCHAR(60) NOT NULL,
            `entidad` VARCHAR(40) NOT NULL,
            `id_entidad` VARCHAR(40) NULL,
            `resumen` VARCHAR(400) NOT NULL,
            `detalle` TEXT NULL,
            `ip` VARCHAR(45) NULL,
            PRIMARY KEY (`id`),
            KEY `idx_auditoria_fecha` (`fecha`),
            KEY `idx_auditoria_entidad` (`entidad`, `id_entidad`),
            KEY `idx_auditoria_usuario` (`id_usuario`),
            KEY `idx_auditoria_accion` (`accion`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `pago` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_pedido` INT NOT NULL,
            `monto` DECIMAL(10,2) NOT NULL,
            `metodo` VARCHAR(20) NOT NULL DEFAULT 'efectivo',
            `origen` VARCHAR(20) NOT NULL,
            `id_usuario` INT NULL,
            `usuario` VARCHAR(120) NULL,
            `fecha` DATETIME NOT NULL,
            `nota` VARCHAR(200) NULL,
            PRIMARY KEY (`id`),
            KEY `idx_pago_fecha` (`fecha`),
            KEY `idx_pago_pedido` (`id_pedido`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `corte_caja` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `fecha_corte` DATE NOT NULL,
            `id_usuario` INT NULL,
            `usuario` VARCHAR(120) NULL,
            `fondo_inicial` DECIMAL(10,2) NOT NULL DEFAULT 0,
            `efectivo_esperado` DECIMAL(10,2) NOT NULL DEFAULT 0,
            `efectivo_contado` DECIMAL(10,2) NOT NULL DEFAULT 0,
            `diferencia` DECIMAL(10,2) NOT NULL DEFAULT 0,
            `notas` VARCHAR(300) NULL,
            `resumen` LONGTEXT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_corte_fecha` (`fecha_corte`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `devolucion` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_pedido` INT NOT NULL,
            `id_usuario` INT NULL,
            `usuario` VARCHAR(120) NULL,
            `fecha` DATETIME NOT NULL,
            `motivo` VARCHAR(300) NULL,
            `total` DECIMAL(10,2) NOT NULL,
            `reembolso` DECIMAL(10,2) NOT NULL DEFAULT 0,
            `metodo_reembolso` VARCHAR(20) NULL,
            PRIMARY KEY (`id`),
            KEY `idx_devolucion_pedido` (`id_pedido`),
            KEY `idx_devolucion_fecha` (`fecha`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS `devolucion_detalle` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_devolucion` INT UNSIGNED NOT NULL,
            `id_producto` INT NOT NULL,
            `cantidad` DECIMAL(10,2) NOT NULL,
            `unidad_venta` VARCHAR(20) NULL,
            `precio_venta` DECIMAL(10,2) NOT NULL,
            `subtotal` DECIMAL(10,2) NOT NULL,
            `destino` VARCHAR(20) NOT NULL DEFAULT 'inventario',
            PRIMARY KEY (`id`),
            KEY `idx_devdet_devolucion` (`id_devolucion`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // Evidencia de entrega y cobro hecho por el repartidor
        foreach ([
            'foto_entrega'  => 'VARCHAR(80) NULL DEFAULT NULL',
            'firma_entrega' => 'VARCHAR(80) NULL DEFAULT NULL',
            'monto_cobrado' => 'DECIMAL(10,2) NULL DEFAULT NULL',
            'metodo_cobro'  => 'VARCHAR(20) NULL DEFAULT NULL',
        ] as $columna => $definicion) {
            if (! $this->db->fieldExists($columna, 'salida_pedido')) {
                $this->db->query("ALTER TABLE `salida_pedido` ADD COLUMN `{$columna}` {$definicion}");
            }
        }

        // Pagos que ya existían: una fila por pedido con lo que lleva pagado
        $yaCargado = (int) $this->db->query('SELECT COUNT(*) AS n FROM `pago`')->getRow()->n;
        if ($yaCargado === 0) {
            $this->db->query("INSERT INTO `pago` (`id_pedido`, `monto`, `metodo`, `origen`, `usuario`, `fecha`, `nota`)
                SELECT `id`, `monto_pagado`, 'efectivo', 'migracion', 'Sistema', `fecha`, 'Pago anterior al registro de pagos'
                  FROM `pedido` WHERE `monto_pagado` > 0");
        }
    }

    public function down()
    {
        foreach (['metodo_cobro', 'monto_cobrado', 'firma_entrega', 'foto_entrega'] as $columna) {
            if ($this->db->fieldExists($columna, 'salida_pedido')) {
                $this->db->query("ALTER TABLE `salida_pedido` DROP COLUMN `{$columna}`");
            }
        }
        foreach (['devolucion_detalle', 'devolucion', 'corte_caja', 'pago', 'auditoria'] as $tabla) {
            $this->db->query("DROP TABLE IF EXISTS `{$tabla}`");
        }
    }
}
