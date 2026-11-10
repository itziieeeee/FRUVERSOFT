<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ReubicarGrafoPuebla extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Conservamos el grafo anterior, pero lo desactivamos para no mezclarlo
        // con la nueva base operativa de Ciudad Modelo, Puebla.
        $db->table('ruta_nodo')->update(['activo' => 0]);
        $db->table('ruta_arista')->update(['activo' => 0]);

        $ahora = date('Y-m-d H:i:s');
        $nodos = [
            ['clave' => 'local',                    'nombre' => 'Local demo - Ciudad Modelo', 'tipo' => 'local',     'colonia' => 'Ciudad Modelo', 'municipio' => 'Ciudad Modelo', 'estado' => 'Puebla',    'aliases' => 'local|ciudad modelo|san jose chiapa|san josé chiapa',                  'es_demo' => 1],
            ['clave' => 'puebla-general',            'nombre' => 'Puebla - cobertura general', 'tipo' => 'estado',    'colonia' => null,             'municipio' => null,             'estado' => 'Puebla',    'aliases' => 'puebla estado',                                                       'es_demo' => 1],
            ['clave' => 'tlaxcala-general',          'nombre' => 'Tlaxcala - cobertura general','tipo' => 'estado',    'colonia' => null,             'municipio' => null,             'estado' => 'Tlaxcala',  'aliases' => 'tlaxcala estado',                                                     'es_demo' => 1],
            ['clave' => 'puebla',                    'nombre' => 'Puebla capital',              'tipo' => 'zona_demo', 'colonia' => null,             'municipio' => 'Puebla',         'estado' => 'Puebla',    'aliases' => 'puebla|puebla capital|centro puebla',                                    'es_demo' => 1],
            ['clave' => 'tlaxcala',                  'nombre' => 'Tlaxcala capital',            'tipo' => 'zona_demo', 'colonia' => null,             'municipio' => 'Tlaxcala',       'estado' => 'Tlaxcala',  'aliases' => 'tlaxcala|tlaxcala capital',                                            'es_demo' => 1],
            ['clave' => 'san-jose-chiapa',            'nombre' => 'San José Chiapa',            'tipo' => 'zona_demo', 'colonia' => null,             'municipio' => 'San José Chiapa','estado' => 'Puebla',    'aliases' => 'san jose chiapa|san josé chiapa',                                    'es_demo' => 1],
            ['clave' => 'acatzingo',                 'nombre' => 'Acatzingo',                   'tipo' => 'zona_demo', 'colonia' => null,             'municipio' => 'Acatzingo',      'estado' => 'Puebla',    'aliases' => 'acatzingo',                                                           'es_demo' => 1],
            ['clave' => 'tepeaca',                   'nombre' => 'Tepeaca',                     'tipo' => 'zona_demo', 'colonia' => null,             'municipio' => 'Tepeaca',        'estado' => 'Puebla',    'aliases' => 'tepeaca',                                                             'es_demo' => 1],
            ['clave' => 'cholula',                   'nombre' => 'San Andrés Cholula',          'tipo' => 'zona_demo', 'colonia' => null,             'municipio' => 'San Andrés Cholula','estado' => 'Puebla','aliases' => 'cholula|san andres cholula|san andrés cholula',             'es_demo' => 1],
            ['clave' => 'atlixco',                   'nombre' => 'Atlixco',                     'tipo' => 'zona_demo', 'colonia' => null,             'municipio' => 'Atlixco',        'estado' => 'Puebla',    'aliases' => 'atlixco',                                                             'es_demo' => 1],
            ['clave' => 'san-martin-texmelucan',     'nombre' => 'San Martín Texmelucan',       'tipo' => 'zona_demo', 'colonia' => null,             'municipio' => 'San Martín Texmelucan','estado' => 'Puebla','aliases' => 'san martin texmelucan|san martín texmelucan',              'es_demo' => 1],
            ['clave' => 'tehuacan',                  'nombre' => 'Tehuacán',                    'tipo' => 'zona_demo', 'colonia' => null,             'municipio' => 'Tehuacán',       'estado' => 'Puebla',    'aliases' => 'tehuacan|tehuacán',                                                     'es_demo' => 1],
            ['clave' => 'apizaco',                  'nombre' => 'Apizaco',                     'tipo' => 'zona_demo', 'colonia' => null,             'municipio' => 'Apizaco',        'estado' => 'Tlaxcala',  'aliases' => 'apizaco',                                                              'es_demo' => 1],
            ['clave' => 'huamantla',                'nombre' => 'Huamantla',                   'tipo' => 'zona_demo', 'colonia' => null,             'municipio' => 'Huamantla',      'estado' => 'Tlaxcala',  'aliases' => 'huamantla',                                                            'es_demo' => 1],
            ['clave' => 'zacatelco',                'nombre' => 'Zacatelco',                   'tipo' => 'zona_demo', 'colonia' => null,             'municipio' => 'Zacatelco',      'estado' => 'Tlaxcala',  'aliases' => 'zacatelco',                                                            'es_demo' => 1],
            ['clave' => 'calpulalpan',              'nombre' => 'Calpulalpan',                 'tipo' => 'zona_demo', 'colonia' => null,             'municipio' => 'Calpulalpan',    'estado' => 'Tlaxcala',  'aliases' => 'calpulalpan',                                                          'es_demo' => 1],
        ];

        $ids = [];
        foreach ($nodos as $nodo) {
            $clave = $nodo['clave'];
            $fila = $db->table('ruta_nodo')->where('clave', $clave)->get()->getRowArray();
            $datos = $nodo + [
                'activo'     => 1,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];

            if ($fila) {
                $db->table('ruta_nodo')->where('id', $fila['id'])->update($datos);
                $ids[$clave] = (int) $fila['id'];
            } else {
                $db->table('ruta_nodo')->insert($datos);
                $ids[$clave] = (int) $db->insertID();
            }
        }

        $conexiones = [
            ['local', 'san-jose-chiapa', 3],
            ['san-jose-chiapa', 'acatzingo', 15],
            ['acatzingo', 'tepeaca', 25],
            ['tepeaca', 'puebla', 35],
            ['puebla', 'cholula', 15],
            ['puebla', 'atlixco', 35],
            ['puebla', 'san-martin-texmelucan', 40],
            ['puebla', 'tehuacan', 130],
            ['local', 'puebla-general', 65],
            ['local', 'tlaxcala-general', 75],
            ['local', 'tlaxcala', 75],
            ['tlaxcala', 'apizaco', 15],
            ['tlaxcala', 'huamantla', 35],
            ['tlaxcala', 'zacatelco', 20],
            ['tlaxcala', 'calpulalpan', 55],
            ['puebla', 'tlaxcala', 75],
        ];

        foreach ($conexiones as [$origen, $destino, $distancia]) {
            if (!isset($ids[$origen], $ids[$destino])) {
                continue;
            }

            $existe = $db->table('ruta_arista')
                ->where('nodo_origen_id', $ids[$origen])
                ->where('nodo_destino_id', $ids[$destino])
                ->get()->getRowArray();

            $datosArista = [
                'nodo_origen_id' => $ids[$origen],
                'nodo_destino_id' => $ids[$destino],
                'distancia_km'   => $distancia,
                'bidireccional'  => 1,
                'activo'         => 1,
                'es_demo'        => 1,
            ];

            if ($existe) {
                $db->table('ruta_arista')->where('id', $existe['id'])->update($datosArista);
            } else {
                $db->table('ruta_arista')->insert($datosArista);
            }
        }

        $db->table('ruta_configuracion')
            ->where('clave', 'max_km_extra')
            ->update(['valor' => '250']);
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->table('ruta_arista')->where('es_demo', 1)->update(['activo' => 0]);
        $db->table('ruta_nodo')->where('es_demo', 1)->update(['activo' => 0]);
    }
}
