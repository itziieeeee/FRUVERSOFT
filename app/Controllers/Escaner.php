<?php

namespace App\Controllers;

use App\Models\ProductoModel;

/**
 * Búsqueda de un producto a partir de un código escaneado (cámara o lector USB).
 * La misma acción se registra en las rutas del administrador, el vendedor y el repartidor.
 */
class Escaner extends BaseController
{
    public function buscar()
    {
        // Los lectores pueden mandar espacios o saltos de línea al final
        $codigo = trim((string) $this->request->getGet('codigo'));

        if ($codigo === '' || strlen($codigo) > 64) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Código no válido.',
            ]);
        }

        $p = (new ProductoModel())->buscarPorCodigo($codigo);

        if (!$p) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'codigo'  => $codigo,
                'message' => 'No hay ningún producto con ese código.',
            ]);
        }

        return $this->response->setJSON([
            'success'  => true,
            'producto' => [
                'id'              => (int) $p['id'],
                'codigo'          => $p['codigo'],
                'codigo_barras'   => $p['codigo_barras'],
                'nombre'          => $p['nombre'],
                'descripcion'     => $p['descripcion'],
                'imagen'          => $p['imagen'] ? base_url('uploads/productos/' . $p['imagen']) : null,
                'unidad'          => $p['unidad_venta'],
                'precio'          => $p['precio_sugerido'] !== null ? (float) $p['precio_sugerido'] : null,
                'existencias'     => (float) $p['existencias_totales'],
                'bloqueadas'      => (float) $p['existencias_bloqueadas'],
                'disponible'      => (float) $p['stock_disponible'],
            ],
        ]);
    }
}
