<?php
namespace App\Controllers;

use App\Models\ExistenciasModel;
use App\Models\StatusModel;
use App\Models\PedidoModel;

class PanelRepartidorController extends BaseController
{
    public function index()
    {
        return view('Repartidor/panel_inicio');
    }

    public function inventario()
    {
        $existenciasModel = new ExistenciasModel();
        $data['productos'] = $existenciasModel->getInventario(15); 
        $data['pager']     = $existenciasModel->pager;

        return view('Repartidor/vista_inventario_readonly', $data);
    }

    public function mis_pedidos()
    {
        $db = \Config\Database::connect();
        $id_repartidor = session()->get('id_repartidor');

        // Traemos solo los pedidos asignados a ESTE repartidor que no estén cancelados ni entregados
        $query = $db->query("
            SELECT p.*, c.nombre, c.apellido_paterno, d.calle, d.numero, d.colonia 
            FROM pedido p
            JOIN clientes c ON p.id_cliente = c.id_cliente
            LEFT JOIN direccion d ON c.id_cliente = d.id_cliente
            WHERE p.id_repartidor = ? AND p.estado_actual NOT IN ('Venta confirmada', 'Pedido cancelado')
        ", [$id_repartidor]);

        $data['pedidos'] = $query->getResultArray();

        return view('Repartidor/vista_mis_pedidos', $data);
    }
    
    
    public function cambiar_estado()
    {
        $id_pedido = $this->request->getPost('id_pedido');
        $estado    = $this->request->getPost('estado');
        
        $statusModel = new \App\Models\StatusModel();
        
        // Actualizamos el pedido con el nuevo estado que el repartidor seleccionó
        $statusModel->actualizarEstado($id_pedido, $estado);
        
        return redirect()->back()->with('mensaje', 'Estado del pedido #'.$id_pedido.' actualizado a: ' . $estado);
    }
}