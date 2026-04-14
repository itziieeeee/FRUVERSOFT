<?php

namespace App\Controllers;

class Merma extends BaseController
{
    public function guardar()
    {
        $db = \Config\Database::connect();

        // 1. Recibimos id_producto (del nuevo select sin repetidos)
        $id_producto = $this->request->getPost('id_producto'); 
        $cantidad    = (float) $this->request->getPost('cantidad');
        $motivo      = $this->request->getPost('motivo');

        // VALIDACIÓN BÁSICA
        if (!$id_producto || $cantidad <= 0) {
            return redirect()->to(base_url('inventario'))->with('error', 'Seleccione un producto y una cantidad válida.');
        }

        // 2. BUSCAR ENTRADA AUTOMÁTICAMENTE
        $entrada = $db->table('entrada')
                      ->where('id_producto', $id_producto)
                      ->where('cantidad >', 0)
                      ->orderBy('fecha', 'ASC') 
                      ->get()
                      ->getRowArray();

        if (!$entrada) {
            return redirect()->to(base_url('inventario'))->with('error', 'No hay stock disponible en el sistema para este producto.');
        }

        // 3. VALIDAR SI LA CANTIDAD ALCANZA EN EL LOTE MÁS VIEJO
        if ($entrada['cantidad'] < $cantidad) {
            return redirect()->to(base_url('inventario'))->with('error', 'La cantidad a mermar supera el stock del lote más antiguo (Disponible en lote: ' . $entrada['cantidad'] . ')');
        }

        // 4. REGISTRAR EN TABLA MERMA
        $db->table('merma')->insert([
            'id_entrada' => $entrada['id'], 
            'cantidad'   => $cantidad,
            'motivo'     => $motivo,
            'fecha'      => date('Y-m-d')
        ]);

        // 5. ACTUALIZAR TABLA ENTRADA
        $db->table('entrada')
           ->where('id', $entrada['id'])
           ->update(['cantidad' => $entrada['cantidad'] - $cantidad]);

        // 6. ACTUALIZAR EXISTENCIAS TOTALES
        $existencia = $db->table('existencias')
                         ->where('id_producto', $id_producto)
                         ->get()
                         ->getRowArray();
                         
        if ($existencia) {
            $db->table('existencias')
               ->where('id_producto', $id_producto)
               ->update([
                   'e_total' => $existencia['e_total'] - $cantidad,
                   'e_merma' => $existencia['e_merma'] + $cantidad
               ]);
        }

        return redirect()->to(base_url('inventario'))->with('mensaje', '¡Merma registrada! Se descontó del lote más antiguo automáticamente.');
    }
}