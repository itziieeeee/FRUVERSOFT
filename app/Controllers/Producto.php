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

        // IMAGEN
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

        $nombreRaw        = trim($this->request->getPost('nombre'));
        $nombreNormalizado = ucfirst(strtolower($nombreRaw));

        $existe = $model->existeNombre($nombreRaw);
        if ($existe) {
            return redirect()->back()
                             ->with('error', "El producto \"$nombreNormalizado\" ya está registrado.")
                             ->withInput();
        }

        $data = [
            'nombre'      => $nombreNormalizado,
            'descripcion' => $this->request->getPost('descripcion'),
            'imagen'      => $nombreImagen
        ];

        $model->insert($data);

        return redirect()->to(base_url('pantalla_productos'))
                         ->with('mensaje', 'Producto guardado correctamente.');
    }

    //  LISTADO CON PAGINACIÓN
    public function pantalla_productos()
    {
        $model = new ProductoModel();

        $q     = $this->request->getGet('q');
        $orden = $this->request->getGet('orden');

        $data = [
            'productos' => $model->getProductosFiltrados($q, $orden),
            'pager'     => $model->pager,
            'q'         => $q,
        ];

        return view('pantalla_productos', $data);
    }

    //  EDITAR NOMBRE Y DESCRIPCIÓN 
    public function editar($id = null)
    {
        if (!$id) {
            return redirect()->to(base_url('pantalla_productos'))
                             ->with('error', 'Producto no encontrado.');
        }

        $model   = new ProductoModel();
        $producto = $model->find($id);

        if (!$producto) {
            return redirect()->to(base_url('pantalla_productos'))
                             ->with('error', 'Producto no encontrado.');
        }

        $nombre      = ucfirst(strtolower(trim($this->request->getPost('nombre'))));
        $descripcion = trim($this->request->getPost('descripcion'));

        // Verificar nombre duplicado en OTRO producto
        $duplicado = $model->existeNombreEnOtro($nombre, $id);
        if ($duplicado) {
            return redirect()->to(base_url('pantalla_productos'))
                             ->with('error', "Ya existe otro producto con el nombre \"$nombre\".");
        }

        $model->update($id, [
            'nombre'      => $nombre,
            'descripcion' => $descripcion,
        ]);

        return redirect()->to(base_url('pantalla_productos'))
                         ->with('mensaje', "Producto \"$nombre\" actualizado correctamente.");
    }

    // ELIMINAR 
    public function eliminar($id = null)
    {
        if (!$id) {
            return redirect()->to(base_url('pantalla_productos'))
                             ->with('error', 'Producto no encontrado.');
        }

        $model   = new ProductoModel();
        $producto = $model->find($id);

        if (!$producto) {
            return redirect()->to(base_url('pantalla_productos'))
                             ->with('error', 'Producto no encontrado.');
        }

        // Verificar si el producto está dentro de algún pedido 
        $enPedido = $model->estaEnPedido($id);

        if ($enPedido > 0) {
            return redirect()->to(base_url('pantalla_productos'))
                             ->with('error', "No se puede eliminar \"" . $producto['nombre'] . "\" porque está dentro de un pedido activo.");
        }

        // Eliminar imagen física si existe
        if (!empty($producto['imagen'])) {
            $ruta = FCPATH . 'uploads/productos/' . $producto['imagen'];
            if (file_exists($ruta)) {
                unlink($ruta);
            }
        }

        $model->delete($id);

        return redirect()->to(base_url('pantalla_productos'))
                         ->with('mensaje', "Producto \"" . $producto['nombre'] . "\" eliminado correctamente.");
    }
}