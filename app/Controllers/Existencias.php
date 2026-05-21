<?php
namespace App\Controllers;
use App\Models\ExistenciasModel;

class Existencias extends BaseController
{
    public function index()
    {
        try {
            $model = new ExistenciasModel();
            $productos = $model->getInventario(6);
            $data = [
                'productos' => $productos,
                'pager'     => $model->pager, 
            ];
            return view('existencias', $data);
        } catch (\Exception $e) {
            echo "Error BD: " . $e->getMessage();
            die();
        }
    }

    public function getProducto($id)
    {
        header('Content-Type: application/json');
        try {
            $model = new ExistenciasModel();
            $producto = $model->getProductoById($id);
            if ($producto) {
                echo json_encode(['success' => true, 'data' => $producto]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        die();
    }

    public function actualizar($id)
{
    header('Content-Type: application/json');
    try {
        $model = new ExistenciasModel();
        $datos = [
            'nombre'                 => $this->request->getPost('nombre'),
            'descripcion'            => $this->request->getPost('descripcion'),
            'unidad_medida'          => $this->request->getPost('unidad_medida'),
            'unidad_venta'          => $this->request->getPost('unidad_venta'),
            'existencias_totales'    => $this->request->getPost('existencias_totales'),
            'existencias_bloqueadas' => $this->request->getPost('existencias_bloqueadas'),
        ];
        $model->actualizarExistencia($id, $datos);
        echo json_encode(['success' => true, 'message' => 'Actualizado correctamente']);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    die();
}

    
  public function eliminar($id)
{
    header('Content-Type: application/json');

    try {

        $db = \Config\Database::connect();

        // Verificar si el producto está en pedidos
        $pedido = $db->table('producto_pedido')
            ->where('id_producto', $id)
            ->countAllResults();

        // Si existe en pedidos
        if ($pedido > 0) {

            echo json_encode([
                'success' => false,
                'message' => 'No se puede eliminar porque el producto está en un pedido.'
            ]);

            die();
        }

        // Eliminar existencias
        $db->table('existencias')
            ->where('id_producto', $id)
            ->delete();

        // Eliminar entradas
        $db->table('entrada')
            ->where('id_producto', $id)
            ->delete();

        echo json_encode([
            'success' => true,
            'message' => 'Producto eliminado correctamente'
        ]);

    } catch (\Exception $e) {

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    die();
}
}