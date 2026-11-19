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
                'unidad_venta'           => $this->request->getPost('unidad_venta'),
                'existencias_totales'    => $this->request->getPost('existencias_totales'),
                'existencias_bloqueadas' => $this->request->getPost('existencias_bloqueadas'),
            ];

            // Las existencias nunca pueden ser negativas ni quedar menos que lo bloqueado
            foreach (['existencias_totales', 'existencias_bloqueadas'] as $campo) {
                if ($datos[$campo] !== null && $datos[$campo] !== '' && (!is_numeric($datos[$campo]) || (float) $datos[$campo] < 0)) {
                    echo json_encode(['success' => false, 'message' => 'Las existencias no pueden ser negativas.']);
                    die();
                }
            }
            if ($datos['existencias_totales'] !== null && $datos['existencias_totales'] !== ''
                && $datos['existencias_bloqueadas'] !== null && $datos['existencias_bloqueadas'] !== ''
                && (float) $datos['existencias_bloqueadas'] > (float) $datos['existencias_totales']) {
                echo json_encode(['success' => false, 'message' => 'Lo bloqueado no puede ser mayor que las existencias totales.']);
                die();
            }

            $antes = $model->getProductoById($id);
            $model->actualizarExistencia($id, $datos);

            if ($antes) {
                \App\Services\AuditoriaService::registrar(
                    'stock.ajuste', 'producto', $id,
                    ($antes['nombre'] ?? 'Producto #' . $id) . ': existencias ' . (float) $antes['existencias_totales'] . ' → ' . (float) ($datos['existencias_totales'] ?? $antes['existencias_totales'])
                        . ', bloqueadas ' . (float) $antes['existencias_bloqueadas'] . ' → ' . (float) ($datos['existencias_bloqueadas'] ?? $antes['existencias_bloqueadas']),
                    [
                        'antes'   => ['totales' => (float) $antes['existencias_totales'], 'bloqueadas' => (float) $antes['existencias_bloqueadas'], 'nombre' => $antes['nombre']],
                        'despues' => ['totales' => $datos['existencias_totales'], 'bloqueadas' => $datos['existencias_bloqueadas'], 'nombre' => $datos['nombre']],
                    ]
                );
            }
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
            $model = new ExistenciasModel();

            $tienePedido = $model->contarProductoEnPedidos($id);

            if ($tienePedido > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se puede eliminar porque el producto está en un pedido.'
                ]);
                die();
            }

            $antes = $model->getProductoById($id);
            $model->eliminarProductoCompleto($id);

            \App\Services\AuditoriaService::registrar(
                'stock.eliminar', 'producto', $id,
                'Se quitó del inventario: ' . ($antes['nombre'] ?? 'producto #' . $id) . ' (existencias ' . (float) ($antes['existencias_totales'] ?? 0) . ')',
                $antes ?: null
            );

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