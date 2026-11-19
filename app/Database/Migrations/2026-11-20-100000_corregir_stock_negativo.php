<?php

namespace App\Database\Migrations;

use App\Services\AuditoriaService;
use CodeIgniter\Database\Migration;

/**
 * Corrige las existencias que quedaron en negativo (la caducidad automática mandaba a merma lotes
 * que ya se habían vendido). El stock negativo se deja en cero y cada corrección queda en el historial de cambios.
 */
class CorregirStockNegativo extends Migration
{
    public function up()
    {
        $filas = $this->db->query(
            'SELECT e.id, e.id_producto, e.e_total, p.nombre
               FROM existencias e LEFT JOIN producto p ON p.id = e.id_producto
              WHERE e.e_total < 0'
        )->getResultArray();

        foreach ($filas as $f) {
            $this->db->query('UPDATE existencias SET e_total = 0 WHERE id = ?', [$f['id']]);

            AuditoriaService::registrar(
                'stock.ajuste', 'producto', $f['id_producto'],
                ($f['nombre'] ?: 'Producto #' . $f['id_producto']) . ': existencias ' . (float) $f['e_total'] . ' → 0 (se corrigió el stock negativo)',
                ['antes' => (float) $f['e_total'], 'despues' => 0, 'motivo' => 'Corrección automática de stock negativo']
            );
        }
    }

    public function down()
    {
        // No se puede volver a un stock negativo: no tiene sentido.
    }
}
