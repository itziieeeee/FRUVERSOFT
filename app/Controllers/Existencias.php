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
                'descripcion'            => $this->request->getPost('descripcion'),
                'unidad_medida'          => $this->request->getPost('unidad_medida'),
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

            // Eliminar en tabla existencias
            $db->table('existencias')->where('id_producto', $id)->delete();

            // Eliminar en tabla entrada
            $db->table('entrada')->where('id_producto', $id)->delete();

            echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        die();
    }
}