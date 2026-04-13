<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class GestionEntradas extends BaseController
{
    public function guardar()
    {
        $db = \Config\Database::connect();

        // 1. Recibir datos del formulario
        $id_producto = $this->request->getPost('id_producto');
        $cantidad    = $this->request->getPost('cantidad');
        $unidad      = $this->request->getPost('unidad_compra'); 

        if (!$id_producto || !$cantidad) {
            return redirect()->back()->with('error', 'Por favor complete los campos obligatorios.');
        }

        // --- LÓGICA DE CADUCIDAD
        $fecha_objeto = new \DateTime(); 
        $fecha_actual = $fecha_objeto->format('Y-m-d H:i:s'); 
        
    
        $fecha_caducidad = $fecha_objeto->modify('+5 days')->format('Y-m-d');
        

        // 2. Registrar la Entrada
        $db->table('entrada')->insert([
            'id_producto'   => $id_producto,
            'cantidad'      => $cantidad,
            'unidad_compra' => $unidad,
            'fecha'         => $fecha_actual,
            'fecha_cad'     => $fecha_caducidad 
        ]);

        // 3. Actualizar o Crear el registro en 'existencias'
        $existencia = $db->table('existencias')
                         ->where('id_producto', $id_producto)
                         ->get()
                         ->getRowArray();

        if ($existencia) {
            $db->table('existencias')
               ->where('id_producto', $id_producto)
               ->update([
                   'e_total' => $existencia['e_total'] + $cantidad
               ]);
        } else {
            $db->table('existencias')->insert([
                'id_producto' => $id_producto,
                'e_total'     => $cantidad,
                'e_bloqueo'   => 0,
                'e_merma'     => 0
            ]);
        }

        return redirect()->to(base_url('inventario'))->with('mensaje', '¡Entrada registrada con éxito! Caduca el: ' . $fecha_caducidad);
    }
}
