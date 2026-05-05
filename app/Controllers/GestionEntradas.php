<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class GestionEntradas extends BaseController
{
    public function guardar()
{
    $db = \Config\Database::connect();
    
    // Ahora recibimos ARRAYS desde la vista
    $ids_productos    = $this->request->getPost('id_producto');
    $precios_compra   = $this->request->getPost('precio_compra');
    $cantidades_compra = $this->request->getPost('cantidad_compra');
    $unidades_compra  = $this->request->getPost('unidad_compra');
    $precios_sugeridos = $this->request->getPost('precio_sugerido');
    $unidades_venta   = $this->request->getPost('unidad_venta');
    $cantidades_venta  = $this->request->getPost('cantidad_venta');
    $categorias       = $this->request->getPost('categoria');

    if (empty($ids_productos)) {
        return redirect()->back()->with('error', 'La lista de productos está vacía.');
    }

    $fecha_compra = date('Y-m-d H:i:s');
    $caducidad    = date('Y-m-d', strtotime('+5 days'));

    // Procesamos cada producto de la tabla
    foreach ($ids_productos as $index => $id_producto) {
        $db->table('entrada')->insert([
            'id_producto'     => $id_producto,
            'precio_compra'   => $precios_compra[$index],
            'cantidad_compra' => $cantidades_compra[$index],
            'unidad_compra'   => $unidades_compra[$index],
            'precio_sugerido' => $precios_sugeridos[$index],
            'unidad_venta'    => $unidades_venta[$index],
            'cantidad_venta'  => $cantidades_venta[$index],
            'categoria'       => $categorias[$index],
            'fecha'           => $fecha_compra,
            'fecha_cad'       => $caducidad
        ]);

        // Actualizar stock en 'existencias'
        $existencia = $db->table('existencias')->where('id_producto', $id_producto)->get()->getRowArray();

        if ($existencia) {
            $db->table('existencias')->where('id_producto', $id_producto)->update([
                'e_total' => $existencia['e_total'] + $cantidades_venta[$index]
            ]);
        } else {
            $db->table('existencias')->insert([
                'id_producto' => $id_producto,
                'e_total'     => $cantidades_venta[$index],
                'e_bloqueo'   => 0,
                'e_merma'     => 0
            ]);
        }
    }

    return redirect()->to(base_url('inventario'))->with('mensaje', '¡Se han registrado ' . count($ids_productos) . ' productos correctamente!');
}
}
