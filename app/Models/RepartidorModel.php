<?php 
namespace App\Models;

use CodeIgniter\Model;

class RepartidorModel extends Model
{
    protected $table      = 'repartidor';
    protected $primaryKey = 'id';

    protected $allowedFields = ['nombre', 'ap_p', 'ap_m', 'tel', 'direccion', 'notas', 'foto', 'zona_base_id'];

    // Zonas que se pueden asignar a un repartidor (tienen coordenadas y no son el local).
    public function getZonasDisponibles(): array
    {
        return $this->db->table('ruta_nodo')
            ->select('id, nombre, estado')
            ->where('activo', 1)
            ->where('tipo', 'zona_demo')
            ->where('lat IS NOT NULL', null, false)
            ->orderBy('estado', 'ASC')
            ->orderBy('nombre', 'ASC')
            ->get()
            ->getResultArray();
    }

    // Convierte el valor recibido del formulario en un id de zona válido, o null si va vacío o no existe.
    public function zonaValidaOrNull($valor): ?int
    {
        if ($valor === null || $valor === '' || !ctype_digit((string) $valor)) {
            return null;
        }

        $existe = $this->db->table('ruta_nodo')
            ->where('id', (int) $valor)
            ->where('activo', 1)
            ->where('tipo', 'zona_demo')
            ->where('lat IS NOT NULL', null, false)
            ->countAllResults();

        return $existe > 0 ? (int) $valor : null;
    }

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