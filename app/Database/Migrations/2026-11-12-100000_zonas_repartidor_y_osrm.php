<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * - Agrega coordenadas a las zonas (ruta_nodo) para poder usarlas como ubicación aproximada.
 * - Agrega la zona base de cada repartidor (repartidor.zona_base_id).
 * - Registra la configuración de OSRM (km reales por carretera) y de las zonas.
 *
 * Las coordenadas de las zonas salen de OpenStreetMap (Nominatim).
 */
class ZonasRepartidorYOsrm extends Migration
{
    private const COORDENADAS_ZONAS = [
        'local'                 => [19.2206760, -97.7784670],
        'puebla'                => [19.0437975, -98.1981513],
        'tlaxcala'              => [19.3167101, -98.2386138],
        'san-jose-chiapa'       => [19.2486554, -97.7198966],
        'acatzingo'             => [19.0301833, -97.7637600],
        'tepeaca'               => [18.9680556, -97.9025000],
        'cholula'               => [19.0220464, -98.2872661],
        'atlixco'               => [18.9088890, -98.4377780],
        'san-martin-texmelucan' => [19.2659835, -98.4248780],
        'tehuacan'              => [18.4658011, -97.3978348],
        'apizaco'               => [19.4264310, -98.0774334],
        'huamantla'             => [19.3233319, -97.9145665],
        'zacatelco'             => [19.2158330, -98.2397220],
        'calpulalpan'           => [19.5520174, -98.6112927],
    ];

    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->fieldExists('lat', 'ruta_nodo')) {
            $this->forge->addColumn('ruta_nodo', [
                'lat' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true, 'after' => 'estado'],
                'lng' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true, 'after' => 'lat'],
            ]);
        }

        foreach (self::COORDENADAS_ZONAS as $clave => [$lat, $lng]) {
            $db->table('ruta_nodo')->where('clave', $clave)->update(['lat' => $lat, 'lng' => $lng]);
        }

        if (!$db->fieldExists('zona_base_id', 'repartidor')) {
            $this->forge->addColumn('repartidor', [
                'zona_base_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            ]);
        }

        $config = [
            ['clave' => 'usar_osrm',          'valor' => '1',                                'descripcion' => '1 = pedir km reales por carretera a OSRM; 0 = solo estimar.'],
            ['clave' => 'osrm_url',           'valor' => 'https://router.project-osrm.org',  'descripcion' => 'Servidor OSRM. Puede cambiarse por uno propio.'],
            ['clave' => 'radio_zona_km',      'valor' => '40',                               'descripcion' => 'Radio (km en línea recta) que cubre la zona base de un repartidor.'],
            ['clave' => 'tolerancia_zona_km', 'valor' => '10',                               'descripcion' => 'Diferencia de km con la mejor opción dentro de la cual se prefiere al repartidor de la zona.'],
        ];

        foreach ($config as $fila) {
            $existe = $db->table('ruta_configuracion')->where('clave', $fila['clave'])->countAllResults();
            if ($existe === 0) {
                $db->table('ruta_configuracion')->insert($fila);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($db->fieldExists('zona_base_id', 'repartidor')) {
            $this->forge->dropColumn('repartidor', 'zona_base_id');
        }
        if ($db->fieldExists('lat', 'ruta_nodo')) {
            $this->forge->dropColumn('ruta_nodo', ['lat', 'lng']);
        }

        $db->table('ruta_configuracion')
            ->whereIn('clave', ['usar_osrm', 'osrm_url', 'radio_zona_km', 'tolerancia_zona_km'])
            ->delete();
    }
}
