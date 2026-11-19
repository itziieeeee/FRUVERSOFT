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

        // Busca esta parte dentro de tu public function guardar()
        $nombreRaw        = trim($this->request->getPost('nombre'));
        $nombreNormalizado = ucfirst(strtolower($nombreRaw));

        $existe = $model->existeNombre($nombreRaw);
        if ($existe) {
            return redirect()->back()
                     ->with('error', "El producto \"$nombreNormalizado\" ya está registrado.")
                     ->withInput();
}

// Código de barras del empaque (opcional): solo para productos que ya lo traen de fábrica
        $codigoBarras = $this->limpiarCodigoBarras($this->request->getPost('codigo_barras'));
        if ($codigoBarras === false) {
            return redirect()->back()
                ->with('error', 'El código de barras solo puede tener letras, números y guiones (de 4 a 32 caracteres).')
                ->withInput();
        }
        if ($codigoBarras !== null && $model->codigoEnUso($codigoBarras)) {
            return redirect()->back()
                ->with('error', 'Ese código de barras ya pertenece a otro producto.')
                ->withInput();
        }

// 1. GENERAR CÓDIGO ÚNICO (Ejemplo: PRD-A1B2C3). Es el identificador propio de TODO producto
//    (también de los que no traen código de barras) y se imprime como etiqueta.
            do {
                $codigoUnico = 'PRD-' . strtoupper(bin2hex(random_bytes(3)));
            } while ($model->codigoEnUso($codigoUnico));

// 2. AGREGARLO AL ARREGLO DE DATOS
            $data = [
            'codigo'        => $codigoUnico,
            'codigo_barras' => $codigoBarras,
            'nombre'      => $nombreNormalizado,
            'descripcion' => $this->request->getPost('descripcion'),
            'imagen'      => $nombreImagen
];

        $model->insert($data);

        \App\Services\AuditoriaService::registrar(
            'producto.crear', 'producto', $model->getInsertID(),
            'Producto nuevo: ' . $nombreNormalizado . ' (' . $codigoUnico . ')' . ($codigoBarras ? ', código de barras ' . $codigoBarras : '')
        );

        return redirect()->to(base_url('pantalla_productos'))
                         ->with('mensaje', 'Producto guardado correctamente.');
    }

    /**
     * Normaliza el código de barras escrito o escaneado.
     * Devuelve null si viene vacío, false si no es válido, o el código limpio.
     */
    private function limpiarCodigoBarras($valor)
    {
        $valor = strtoupper(preg_replace('/\s+/', '', (string) $valor));
        if ($valor === '') {
            return null;
        }
        return preg_match('/^[A-Z0-9\-]{4,32}$/', $valor) ? $valor : false;
    }

    /** Hoja imprimible con la etiqueta (Code128) de cada producto: sirve para lo que no trae código de fábrica */
    public function etiquetas()
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $this->request->getGet('ids'))));

        $model = new ProductoModel();
        $model->select('id, codigo, codigo_barras, nombre')->orderBy('nombre', 'ASC');
        if ($ids) {
            $model->whereIn('id', $ids);
        }

        return view('etiquetas_productos', ['productos' => $model->findAll()]);
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

        $cambios = [
            'nombre'      => $nombre,
            'descripcion' => $descripcion,
        ];

        // Código de barras del empaque: solo se toca si el formulario lo trae
        if ($this->request->getPost('codigo_barras') !== null) {
            $codigoBarras = $this->limpiarCodigoBarras($this->request->getPost('codigo_barras'));
            if ($codigoBarras === false) {
                return redirect()->to(base_url('pantalla_productos'))
                                 ->with('error', 'El código de barras solo puede tener letras, números y guiones (de 4 a 32 caracteres).');
            }
            if ($codigoBarras !== null && $model->codigoEnUso($codigoBarras, (int) $id)) {
                return redirect()->to(base_url('pantalla_productos'))
                                 ->with('error', 'Ese código de barras ya pertenece a otro producto.');
            }
            $cambios['codigo_barras'] = $codigoBarras;
        }

        $model->update($id, $cambios);

        \App\Services\AuditoriaService::registrar(
            'producto.editar', 'producto', $id,
            'Producto editado: ' . $producto['nombre'] . ($producto['nombre'] !== $nombre ? ' → ' . $nombre : ''),
            [
                'antes'   => ['nombre' => $producto['nombre'], 'descripcion' => $producto['descripcion'], 'codigo_barras' => $producto['codigo_barras'] ?? null],
                'despues' => $cambios,
            ]
        );

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

        \App\Services\AuditoriaService::registrar(
            'producto.eliminar', 'producto', $id,
            'Producto eliminado: ' . $producto['nombre'] . ' (' . ($producto['codigo'] ?? '') . ')'
        );

        return redirect()->to(base_url('pantalla_productos'))
                         ->with('mensaje', "Producto \"" . $producto['nombre'] . "\" eliminado correctamente.");
    }
}