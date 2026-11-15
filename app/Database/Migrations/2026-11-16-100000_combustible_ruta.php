<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Parámetros para estimar la gasolina de una salida de reparto.
 * Se guardan en ruta_configuracion y se pueden ajustar con UPDATE sin tocar el código.
 */
class CombustibleRuta extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $filas = [
            ['clave' => 'rendimiento_km_l', 'valor' => '10',   'descripcion' => 'Rendimiento promedio de la unidad de reparto (km por litro).'],
            ['clave' => 'precio_litro',     'valor' => '24.00', 'descripcion' => 'Precio de la gasolina por litro (MXN). Actualizar cuando cambie.'],
        ];

        foreach ($filas as $fila) {
            $existe = $db->table('ruta_configuracion')->where('clave', $fila['clave'])->countAllResults();
            if ($existe === 0) {
                $db->table('ruta_configuracion')->insert($fila);
            }
        }
    }

    public function down()
    {
        \Config\Database::connect()->table('ruta_configuracion')
            ->whereIn('clave', ['rendimiento_km_l', 'precio_litro'])
            ->delete();
    }
}
