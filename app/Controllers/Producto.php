<?php

namespace App\Controllers;

use App\Models\ProductoModel;

class Producto extends BaseController
{
    public function index()
    {
        // Carga la vista del formulario
        return view('alta_producto');
    }

    public function guardar()
    {
        $model = new ProductoModel();
    
        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
            'uni_medida' => $this->request->getPost('uni_medida'),
            'unidad_compra' => $this->request->getPost('unidad_compra'),
            'unidad_venta' => $this->request->getPost('unidad_venta'),
             'categoria' => $this->request->getPost('categoria'),
              'stock' => $this->request->getPost('stock'),
               'precio_compra' => $this->request->getPost('precio_compra'),
                'precio_venta' => $this->request->getPost('precio_venta'),
        ];

        if ($model->insert($data)) {
            return redirect()->to(base_url('inventario/merma'))->with('mensaje', 'Guardado con éxito');
        }

    }

    
}