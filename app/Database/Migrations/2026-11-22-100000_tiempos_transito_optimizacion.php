<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tiempos de traslado, tráfico y comparación de rutas.
 *
 *  ruta_configuracion  velocidad_promedio_kmh, minutos_por_entrega y usar_transito (se editan en Monitor de salidas).
 *  ruta_transito       horarios con tráfico pesado y cuánto alargan el traslado (factor). Valores de referencia
 *                      para una ciudad media; se pueden ajustar. No es tráfico en vivo.
 *  salida.optimizacion       comparación de cada recorrido: orden de captura contra ruta optimizada (km, tiempo, gasolina).
 *  salida_pedido.llegada_est hora estimada de llegada a cada parada, calculada al armar el recorrido.
 */
class TiemposTransitoOptimizacion extends Migration
{
    public function up()
    {
        $db = $this->db;

        $parametros = [
            ['clave' => 'velocidad_promedio_kmh', 'valor' => '40', 'descripcion' => 'Velocidad promedio (km/h) para estimar el tiempo cuando el servicio de mapas no responde.'],
            ['clave' => 'minutos_por_entrega',    'valor' => '5',  'descripcion' => 'Minutos que se quedan en cada entrega (descargar, cobrar, tomar la evidencia).'],
            ['clave' => 'usar_transito',          'valor' => '1',  'descripcion' => '1 = el tiempo se alarga en los horarios de ruta_transito; 0 = sin ajuste por tráfico.'],
        ];
        foreach ($parametros as $fila) {
            if ($db->table('ruta_configuracion')->where('clave', $fila['clave'])->countAllResults() === 0) {
                $db->table('ruta_configuracion')->insert($fila);
            }
        }

        if (! $db->tableExists('ruta_transito')) {
            $db->query("CREATE TABLE `ruta_transito` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `nombre` VARCHAR(80) NOT NULL,
                `dias` VARCHAR(20) NOT NULL DEFAULT '1,2,3,4,5' COMMENT 'Días de la semana: 1 = lunes ... 7 = domingo',
                `hora_inicio` TIME NOT NULL,
                `hora_fin` TIME NOT NULL,
                `factor` DECIMAL(4,2) NOT NULL DEFAULT 1.00 COMMENT 'Multiplica el tiempo de traslado: 1.40 = 40 % más lento',
                `activo` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $db->table('ruta_transito')->insertBatch([
                ['nombre' => 'Hora pico de la mañana', 'dias' => '1,2,3,4,5', 'hora_inicio' => '07:00:00', 'hora_fin' => '09:30:00', 'factor' => 1.40, 'activo' => 1],
                ['nombre' => 'Hora de la comida',      'dias' => '1,2,3,4,5', 'hora_inicio' => '13:30:00', 'hora_fin' => '15:30:00', 'factor' => 1.25, 'activo' => 1],
                ['nombre' => 'Hora pico de la tarde',  'dias' => '1,2,3,4,5', 'hora_inicio' => '18:00:00', 'hora_fin' => '20:30:00', 'factor' => 1.45, 'activo' => 1],
            ]);
        }

        if (! $db->fieldExists('optimizacion', 'salida')) {
            $db->query('ALTER TABLE `salida` ADD COLUMN `optimizacion` LONGTEXT NULL');
        }
        if (! $db->fieldExists('llegada_est', 'salida_pedido')) {
            $db->query('ALTER TABLE `salida_pedido` ADD COLUMN `llegada_est` VARCHAR(5) NULL DEFAULT NULL');
        }
    }

    public function down()
    {
        $db = $this->db;

        if ($db->fieldExists('llegada_est', 'salida_pedido')) {
            $db->query('ALTER TABLE `salida_pedido` DROP COLUMN `llegada_est`');
        }
        if ($db->fieldExists('optimizacion', 'salida')) {
            $db->query('ALTER TABLE `salida` DROP COLUMN `optimizacion`');
        }
        $db->query('DROP TABLE IF EXISTS `ruta_transito`');
        $db->table('ruta_configuracion')->whereIn('clave', ['velocidad_promedio_kmh', 'minutos_por_entrega', 'usar_transito'])->delete();
    }
}
