<?php 
namespace App\Models;

use CodeIgniter\Model;

class RepartidorModel extends Model
{
    protected $table      = 'repartidor';
    protected $primaryKey = 'id';

    protected $allowedFields = ['nombre', 'ap_p', 'ap_m', 'tel', 'direccion', 'notas', 'foto'];

    // Pedidos asignados a repartidores (con datos de cliente y dirección)
    public function getPedidosPorRepartidor()
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                p.id_repartidor,
                p.id,
                p.estado_actual,
                p.total,
                p.fecha,
                c.nombre AS cliente_nombre,
                c.apellido_paterno AS cliente_ap,
                d.calle,
                d.numero,
                d.colonia,
                d.municipio,
                d.estado AS cliente_estado
            FROM pedido p
            LEFT JOIN clientes c  ON c.id_cliente = p.id_cliente
            LEFT JOIN direccion d ON d.id_cliente = p.id_cliente
            WHERE p.id_repartidor IS NOT NULL
            ORDER BY p.id_repartidor, p.id DESC
        ")->getResultArray();
    }

    // Quita al repartidor de sus pedidos antes de eliminarlo
    public function desasignarPedidos($id)
    {
        return $this->db->table('pedido')
            ->where('id_repartidor', $id)
            ->update(['id_repartidor' => null]);
    }
}