<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Ajusta las aristas de Puebla y Tlaxcala con distancias por carretera verificadas.
 *
 * Fuentes consultadas (km por carretera, aproximados):
 * - Puebla centro a Tlaxcala capital: 34 a 35 km.
 * - Ciudad Modelo (San José Chiapa) a Puebla: 61 a 64 km.
 *
 * Las aristas que no tienen fuente (cabeceras de cobertura general) quedan como estimación.
 */
class AjustarDistanciasPueblaTlaxcala extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Rutas que pasaban por aristas de 75 km sin sustento.
        $this->desactivarArista($db, 'local', 'puebla-general');
        $this->desactivarArista($db, 'local', 'tlaxcala-general');
        $this->desactivarArista($db, 'local', 'tlaxcala');
        $this->desactivarArista($db, 'san-jose-chiapa', 'acatzingo');

        $this->guardarArista($db, 'local', 'puebla', 62.00, 'Ciudad Modelo a Puebla (61-64 km, carretera)');
        $this->guardarArista($db, 'puebla', 'tlaxcala', 35.00, 'Puebla a Tlaxcala (34-35 km, carretera)');
        $this->guardarArista($db, 'puebla-general', 'puebla', 10.00, 'Estimación: cobertura general de Puebla');
        $this->guardarArista($db, 'tlaxcala-general', 'tlaxcala', 10.00, 'Estimación: cobertura general de Tlaxcala');
    }

    public function down()
    {
        // Las distancias previas eran demostrativas; no se restauran automáticamente.
    }

    private function claveAId($db, string $clave): ?int
    {
        $fila = $db->table('ruta_nodo')->select('id')->where('clave', $clave)->get()->getRowArray();
        return $fila ? (int) $fila['id'] : null;
    }

    private function desactivarArista($db, string $a, string $b): void
    {
        $idA = $this->claveAId($db, $a);
        $idB = $this->claveAId($db, $b);
        if ($idA === null || $idB === null) {
            return;
        }

        $db->table('ruta_arista')
            ->groupStart()
                ->where(['nodo_origen_id' => $idA, 'nodo_destino_id' => $idB])
            ->groupEnd()
            ->orGroupStart()
                ->where(['nodo_origen_id' => $idB, 'nodo_destino_id' => $idA])
            ->groupEnd()
            ->update(['activo' => 0]);
    }

    private function guardarArista($db, string $a, string $b, float $km, string $nota): void
    {
        $idA = $this->claveAId($db, $a);
        $idB = $this->claveAId($db, $b);
        if ($idA === null || $idB === null) {
            return;
        }

        $existente = $db->table('ruta_arista')
            ->groupStart()
                ->where(['nodo_origen_id' => $idA, 'nodo_destino_id' => $idB])
            ->groupEnd()
            ->orGroupStart()
                ->where(['nodo_origen_id' => $idB, 'nodo_destino_id' => $idA])
            ->groupEnd()
            ->get()->getRowArray();

        $datos = [
            'distancia_km'  => $km,
            'bidireccional' => 1,
            'activo'        => 1,
            'es_demo'       => 1,
        ];

        if ($existente) {
            $db->table('ruta_arista')->where('id', $existente['id'])->update($datos);
        } else {
            $db->table('ruta_arista')->insert($datos + [
                'nodo_origen_id'  => $idA,
                'nodo_destino_id' => $idB,
            ]);
        }
    }
}
