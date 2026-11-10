<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RutaRepartidores extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'clave' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
            ],
            'tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'zona',
            ],
            'colonia' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'municipio' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'estado' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'aliases' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'es_demo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('clave');
        $this->forge->addKey(['activo', 'tipo']);
        $this->forge->createTable('ruta_nodo', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nodo_origen_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nodo_destino_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'distancia_km' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'bidireccional' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'es_demo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['nodo_origen_id', 'activo']);
        $this->forge->addKey(['nodo_destino_id', 'activo']);
        $this->forge->createTable('ruta_arista', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'clave' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'valor' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'descripcion' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('clave');
        $this->forge->createTable('ruta_configuracion', true);

        $this->insertarConfiguracionInicial();
        $this->insertarGrafoDemo();
    }

    public function down()
    {
        $this->forge->dropTable('ruta_arista', true);
        $this->forge->dropTable('ruta_nodo', true);
        $this->forge->dropTable('ruta_configuracion', true);
    }

    private function insertarConfiguracionInicial(): void
    {
        $db = \Config\Database::connect();

        $configuracion = [
            [
                'clave'        => 'max_pedidos',
                'valor'        => '6',
                'descripcion' => 'Máximo de pedidos activos por repartidor en una salida.',
            ],
            [
                'clave'        => 'max_km_extra',
                'valor'        => '20',
                'descripcion' => 'Máximo de kilómetros extra permitidos para una sugerencia.',
            ],
            [
                'clave'        => 'local_clave',
                'valor'        => 'local',
                'descripcion' => 'Clave del nodo que representa el local.',
            ],
            [
                'clave'        => 'modo_demo',
                'valor'        => '1',
                'descripcion' => 'Indica que las zonas y distancias iniciales son demostrativas.',
            ],
        ];

        foreach ($configuracion as $fila) {
            $existe = $db->table('ruta_configuracion')
                ->where('clave', $fila['clave'])
                ->countAllResults();

            if ($existe === 0) {
                $db->table('ruta_configuracion')->insert($fila);
            }
        }
    }

    private function insertarGrafoDemo(): void
    {
        $db = \Config\Database::connect();
        $ahora = date('Y-m-d H:i:s');

        $nodos = [
            ['clave' => 'local',             'nombre' => 'Local demo',          'tipo' => 'local',     'colonia' => 'Centro',       'municipio' => 'Veracruz',       'estado' => 'Veracruz',       'aliases' => 'local|centro',                         'activo' => 1, 'es_demo' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['clave' => 'veracruz-centro',   'nombre' => 'Veracruz Centro',     'tipo' => 'zona_demo', 'colonia' => 'Centro',       'municipio' => 'Veracruz',       'estado' => 'Veracruz',       'aliases' => 'centro|zona centro|centro historico',       'activo' => 1, 'es_demo' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['clave' => 'boca-del-rio',      'nombre' => 'Boca del Río',        'tipo' => 'zona_demo', 'colonia' => null,           'municipio' => 'Boca del Río',   'estado' => 'Veracruz',       'aliases' => 'boca del rio|boca del río',                'activo' => 1, 'es_demo' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['clave' => 'medellin',          'nombre' => 'Medellín de Bravo',   'tipo' => 'zona_demo', 'colonia' => null,           'municipio' => 'Medellín',       'estado' => 'Veracruz',       'aliases' => 'medellin de bravo|medellín de bravo',       'activo' => 1, 'es_demo' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['clave' => 'alvarado',          'nombre' => 'Alvarado',            'tipo' => 'zona_demo', 'colonia' => null,           'municipio' => 'Alvarado',       'estado' => 'Veracruz',       'aliases' => 'alvarado',                              'activo' => 1, 'es_demo' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['clave' => 'xalapa',            'nombre' => 'Xalapa',              'tipo' => 'zona_demo', 'colonia' => null,           'municipio' => 'Xalapa',         'estado' => 'Veracruz',       'aliases' => 'xalapa|jalapa',                         'activo' => 1, 'es_demo' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['clave' => 'cordoba',           'nombre' => 'Córdoba',             'tipo' => 'zona_demo', 'colonia' => null,           'municipio' => 'Córdoba',        'estado' => 'Veracruz',       'aliases' => 'cordoba|córdoba',                        'activo' => 1, 'es_demo' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['clave' => 'orizaba',           'nombre' => 'Orizaba',             'tipo' => 'zona_demo', 'colonia' => null,           'municipio' => 'Orizaba',        'estado' => 'Veracruz',       'aliases' => 'orizaba',                               'activo' => 1, 'es_demo' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['clave' => 'puebla',            'nombre' => 'Puebla',              'tipo' => 'zona_demo', 'colonia' => null,           'municipio' => 'Puebla',         'estado' => 'Puebla',         'aliases' => 'puebla',                                'activo' => 1, 'es_demo' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['clave' => 'oaxaca',            'nombre' => 'Oaxaca',              'tipo' => 'zona_demo', 'colonia' => null,           'municipio' => 'Oaxaca',         'estado' => 'Oaxaca',         'aliases' => 'oaxaca',                                'activo' => 1, 'es_demo' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['clave' => 'villahermosa',      'nombre' => 'Villahermosa',        'tipo' => 'zona_demo', 'colonia' => null,           'municipio' => 'Centro',         'estado' => 'Tabasco',        'aliases' => 'villahermosa|centro tabasco',               'activo' => 1, 'es_demo' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['clave' => 'tampico',           'nombre' => 'Tampico',             'tipo' => 'zona_demo', 'colonia' => null,           'municipio' => 'Tampico',        'estado' => 'Tamaulipas',     'aliases' => 'tampico',                               'activo' => 1, 'es_demo' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
        ];

        foreach ($nodos as $nodo) {
            $existe = $db->table('ruta_nodo')->where('clave', $nodo['clave'])->get()->getRowArray();
            if (!$existe) {
                $db->table('ruta_nodo')->insert($nodo);
            }
        }

        $ids = [];
        foreach (array_keys(array_column($nodos, 'clave')) as $indice) {
            $clave = $nodos[$indice]['clave'];
            $fila = $db->table('ruta_nodo')->select('id')->where('clave', $clave)->get()->getRowArray();
            if ($fila) {
                $ids[$clave] = (int) $fila['id'];
            }
        }

        $conexiones = [
            ['local', 'veracruz-centro', 2],
            ['veracruz-centro', 'boca-del-rio', 8],
            ['boca-del-rio', 'medellin', 12],
            ['medellin', 'alvarado', 25],
            ['boca-del-rio', 'xalapa', 105],
            ['xalapa', 'cordoba', 100],
            ['cordoba', 'orizaba', 25],
            ['xalapa', 'puebla', 125],
            ['orizaba', 'puebla', 150],
            ['cordoba', 'oaxaca', 180],
            ['alvarado', 'villahermosa', 260],
            ['xalapa', 'tampico', 330],
        ];

        foreach ($conexiones as [$origen, $destino, $distancia]) {
            if (!isset($ids[$origen], $ids[$destino])) {
                continue;
            }

            $existe = $db->table('ruta_arista')
                ->where('nodo_origen_id', $ids[$origen])
                ->where('nodo_destino_id', $ids[$destino])
                ->countAllResults();

            if ($existe === 0) {
                $db->table('ruta_arista')->insert([
                    'nodo_origen_id' => $ids[$origen],
                    'nodo_destino_id' => $ids[$destino],
                    'distancia_km'   => $distancia,
                    'bidireccional'  => 1,
                    'activo'         => 1,
                    'es_demo'        => 1,
                ]);
            }
        }
    }
}
