<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class GestionEntradas extends BaseController
{
    public function guardar()
    {
        $db = \Config\Database::connect();

        // Datos de la ventana modal
        $id_producto     = $this->request->getPost('id_producto');
        $precio_compra   = $this->request->getPost('precio_compra');
        $cantidad_compra = $this->request->getPost('cantidad_compra');
        $unidad_compra   = $this->request->getPost('unidad_compra');
        $precio_sugerido = $this->request->getPost('precio_sugerido');
        $unidad_venta    = $this->request->getPost('unidad_venta');
        $cantidad_venta  = $this->request->getPost('cantidad_venta');
        $categoria       = $this->request->getPost('categoria');

        // Validación
        if (!$id_producto || !$cantidad_compra || !$cantidad_venta || !$unidad_compra || !$unidad_venta || !$categoria) {
        return redirect()->back()
            ->withInput() // Esto mantiene lo que el usuario ya escribió para que no se borre
            ->with('error', 'Faltan datos: Asegúrate de seleccionar Unidad de Compra, Venta y Categoría.');
    }

        // --- LÓGICA DE FECHAS AUTOMÁTICA ---
        $fecha_compra = date('Y-m-d H:i:s'); 
        $caducidad    = date('Y-m-d', strtotime('+5 days')); // Fecha actual + 5 días

        // Registrar en la tabla 'entrada'
        $db->table('entrada')->insert([
            'id_producto'     => $id_producto,
            'precio_compra'   => $precio_compra,
            'cantidad_compra' => $cantidad_compra,
            'unidad_compra'   => $unidad_compra,
            'precio_sugerido' => $precio_sugerido,
            'unidad_venta'    => $unidad_venta,
            'cantidad_venta'  => $cantidad_venta,
            'categoria'       => $categoria,
            'fecha'           => $fecha_compra, 
            'fecha_cad'       => $caducidad     
        ]);

       
        // Buscar si el producto ya tiene un registro de stock
        $existencia = $db->table('existencias')
                         ->where('id_producto', $id_producto)
                         ->get()
                         ->getRowArray();

        if ($existencia) {
            // Si ya existe, sumamos la nueva cantidad de venta al total actual
            $db->table('existencias')
               ->where('id_producto', $id_producto)
               ->update([
                   'e_total' => $existencia['e_total'] + $cantidad_venta
               ]);
        } else {
            // Si el producto es nuevo en el inventario, creamos su primer registro de stock
            $db->table('existencias')->insert([
                'id_producto' => $id_producto,
                'e_total'     => $cantidad_venta,
                'e_bloqueo'   => 0,
                'e_merma'     => 0
            ]);
        }

        // Redirección con mensaje de éxito
        return redirect()->to(base_url('inventario'))
                         ->with('mensaje', '¡Entrada registrada con éxito! El producto caduca el: ' . $caducidad);
    }
}