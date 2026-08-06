<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EntradaModel;
use App\Models\ExistenciasModel;

class GestionEntradas extends BaseController
{
    public function guardar()
    {
        $entradaModel     = new EntradaModel();
        $existenciasModel = new ExistenciasModel();

        $ids_productos     = $this->request->getPost('id_producto');
        $precios_compra    = $this->request->getPost('precio_compra');
        $cantidades_compra = $this->request->getPost('cantidad_compra');
        $unidades_compra   = $this->request->getPost('unidad_compra');
        $precios_sugeridos = $this->request->getPost('precio_sugerido');
        $unidades_venta    = $this->request->getPost('unidad_venta');
        $cantidades_venta  = $this->request->getPost('cantidad_venta');
        $categorias        = $this->request->getPost('categoria');

        if (empty($ids_productos)) {
            return redirect()->back()
                ->with('error', 'La lista de productos está vacía.');
        }

        $fecha_compra = date('Y-m-d H:i:s');
        $caducidad    = date('Y-m-d', strtotime('+5 days'));

        foreach ($ids_productos as $index => $id_producto) {

            $entradaModel->insert([
                'id_producto'     => $id_producto,
                'precio_compra'   => $precios_compra[$index],
                'cantidad_compra' => $cantidades_compra[$index],
                'unidad_compra'   => $unidades_compra[$index],
                'precio_sugerido' => $precios_sugeridos[$index],
                'unidad_venta'    => $unidades_venta[$index],
                'cantidad_venta'  => $cantidades_venta[$index],
                'categoria'       => $categorias[$index],
                'fecha'           => $fecha_compra,
                'fecha_cad'       => $caducidad,
            ]);

            $existenciasModel->agregarOActualizarStock($id_producto, $cantidades_venta[$index]);
        }

        return redirect()->to(base_url('inventario'))
            ->with('mensaje', '¡Productos registrados correctamente!');
    }
}