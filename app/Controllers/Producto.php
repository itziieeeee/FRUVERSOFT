<?php

namespace App\Controllers;

use App\Models\ProductoModel;

class Producto extends BaseController
{
   
    public function altaproducto()
    {
        return view('alta_producto');
    }

   public function guardar()
{
    helper(['form']);
    $model = new ProductoModel();

    //  IMAGEN
    $file = $this->request->getFile('foto');
    $nombreImagen = null;

    if ($file && $file->isValid() && !$file->hasMoved()) {
        
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 2048; 

        if (!in_array($file->getMimeType(), $tiposPermitidos)) {
            return redirect()->back()->with('error', 'Solo se permiten imágenes JPG o PNG');
        }

        if ($file->getSize() > $maxSize * 1024) {
            return redirect()->back()->with('error', 'La imagen no debe superar 2MB');
        }

        $nombreImagen = $file->getRandomName();
        $file->move(FCPATH . 'uploads/productos/', $nombreImagen);
    }
    $data = [
    'nombre'      => $this->request->getPost('nombre'),
    'descripcion' => $this->request->getPost('descripcion'),
    'imagen'      => $nombreImagen
];

    $model->insert($data);

    return redirect()->to(base_url('pantalla_productos'))
                     ->with('mensaje', 'Producto guardado ');
}
    public function pantalla_productos()
{
    $model = new ProductoModel();

    $orden = $this->request->getGet('orden');

    // 
    if ($orden == 'stock_mayor') {
        $model->orderBy('e_total', 'DESC');
    }

    $data = [
        'productos' => $model->paginate(10),
        'pager'     => $model->pager
    ];

    return view('pantalla_productos', $data);
}
    public function eliminar($id)
{
    $modelo = new \App\Models\ProductoModel();
    $modelo->delete($id);

    return $this->response->setJSON([
        'status' => 'ok'
    ]);
}
}