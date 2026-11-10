<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Guarda en ruta_configuracion el punto de partida real y el factor de carretera,
 * para calcular distancias con coordenadas.
 *
 * El punto del local es el centro de Ciudad Modelo según OpenStreetMap.
 * La calle exacta (Blvd. Audi Sur) no aparece en OpenStreetMap; se puede afinar con UPDATE.
 */
class CoordenadasLocal extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $filas = [
            ['clave' => 'local_lat',        'valor' => '19.2206760',  'descripcion' => 'Latitud del local (Ciudad Modelo, Puebla).'],
            ['clave' => 'local_lng',        'valor' => '-97.7784670', 'descripcion' => 'Longitud del local (Ciudad Modelo, Puebla).'],
            ['clave' => 'factor_carretera', 'valor' => '1.3',         'descripcion' => 'Multiplicador de la distancia en línea recta para estimar km por carretera.'],
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
            ->whereIn('clave', ['local_lat', 'local_lng', 'factor_carretera'])
            ->delete();
    }
}
