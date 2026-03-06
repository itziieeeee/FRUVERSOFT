<?php

namespace App\Controllers;

use App\Models\ProductoModel;
use App\Models\EntradaModel;
use App\Models\MermaModel;

class Controlador extends BaseController
{
    public function inventario()
    {
        $productoModel = new ProductoModel();
        $entradaModel  = new EntradaModel();
        $mermaModel    = new MermaModel();

        $data['producto'] = $productoModel->findAll();

        // Nota: Asegúrate que en la DB las llaves foráneas se llamen id_producto
        $data['entrada'] = $entradaModel
            ->select('entradas.*, productos.nombre as producto_nombre')
            ->join('productos', 'productos.id = entradas.id_producto')
            ->findAll();

        $data['merma'] = $mermaModel
            ->select('mermas.*, productos.nombre as producto_nombre')
            ->join('productos', 'productos.id = mermas.id_producto')
            ->findAll();

        return view('inventario', $data);
    }

    public function guardarEntrada()
    {
        $entradaModel = new EntradaModel();
        $productoModel = new ProductoModel();

        $id_producto = $this->request->getPost('id_producto');
        $cantidad = $this->request->getPost('cantidad');

        $datos = [
            'id_producto' => $id_producto,
            'cantidad'    => $cantidad,
            'fecha'       => $this->request->getPost('fecha')
        ];

        if ($entradaModel->insert($datos)) {
            $producto = $productoModel->find($id_producto);
            $productoModel->update($id_producto, [
                'stock' => $producto['stock'] + $cantidad
            ]);
            return redirect()->to(base_url('pantalla_inventario'));
        }
        return "Error al registrar la entrada.";
    }

    public function guardarMerma()
    {
        $mermaModel = new MermaModel();
        $productoModel = new ProductoModel();

        $id_producto = $this->request->getPost('id_producto');
        $cantidad = $this->request->getPost('cantidad');

        $producto = $productoModel->find($id_producto);

        if (!$producto || $producto['stock'] < $cantidad) {
            return "No hay suficiente stock para registrar la merma.";
        }

        $datos = [
            'id_producto' => $id_producto,
            'cantidad'    => $cantidad,
            'motivo'      => $this->request->getPost('motivo'),
            'fecha'       => date('Y-m-d') 
        ];

        if ($mermaModel->insert($datos)) {
            $productoModel->update($id_producto, [
                'stock' => $producto['stock'] - $cantidad
            ]);
            return redirect()->to(base_url('pantalla_inventario'));
        }
        return "Error al registrar la merma.";
    }
}