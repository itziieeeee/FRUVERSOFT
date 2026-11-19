<?php
namespace App\Models;

use CodeIgniter\Model;

class PedidoModel extends Model {
    // Estados que cuentan como venta real (se excluye 'Pedido' sin confirmar y 'Pedido cancelado')
    public const ESTADOS_VENTA = [
        'Pedido confirmado',
        'Pedido en tránsito',
        'Venta confirmada',
        'Pedido a crédito',
        'Pedido pagado',
    ];

    protected $table      = 'pedido';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'fecha',
        'id_cliente',
        'id_repartidor',
        'tipo_entrega',
        'total',
        'estado_actual',
        'monto_pagado',
        'tipo_pago',
        'prioritario',
    ];

    // ------- Consultas para pantalla_ventas() -------

    public function getPedidosConCliente()
    {
        return $this
            ->select("pedido.*, CONCAT(clientes.nombre, ' ', clientes.apellido_paterno) as nombre_cliente")
            ->join('clientes', 'clientes.id_cliente = pedido.id_cliente')
            ->findAll();
    }
    public function getPedidosVendedor()
{
    return $this->select("
            pedido.*,
            COALESCE(
                CONCAT(clientes.nombre, ' ', clientes.apellido_paterno),
                'Público general'
            ) AS nombre_cliente,
            COALESCE(
                CONCAT(repartidor.nombre, ' ', repartidor.ap_p),
                'Sin asignar'
            ) AS nombre_repartidor
        ")
        ->join(
            'clientes',
            'clientes.id_cliente = pedido.id_cliente',
            'left'
        )
        ->join(
            'repartidor',
            'repartidor.id = pedido.id_repartidor',
            'left'
        )
        ->orderBy('pedido.fecha', 'DESC')
        ->findAll();
}

    public function getClientes()
    {
        return $this->db->table('clientes')
            ->select('id_cliente, nombre, apellido_paterno, apellido_materno, tipo_cliente')
            ->groupStart()->where('tipo_cliente IS NULL', null, false)->orWhere('tipo_cliente !=', 'publico')->groupEnd()
            ->get()
            ->getResultArray();
    }
    public function getProductosConPrecio()
{
    return $this->db->table('producto p')
        ->select("
            p.id,
            p.codigo,
            p.nombre,
            p.descripcion,
            p.imagen,

            (
                SELECT ent.unidad_venta
                FROM entrada ent
                WHERE ent.id_producto = p.id
                ORDER BY ent.id DESC
                LIMIT 1
            ) AS unidad_venta,

            (
                SELECT ent.precio_sugerido
                FROM entrada ent
                WHERE ent.id_producto = p.id
                ORDER BY ent.id DESC
                LIMIT 1
            ) AS precio_sugerido,

            COALESCE(SUM(ex.e_total), 0) AS existencias_totales,
            COALESCE(SUM(ex.e_bloqueo), 0) AS existencias_bloqueadas,
            COALESCE(SUM(ex.e_total - ex.e_bloqueo), 0) AS stock_disponible
        ")
        ->join('existencias ex', 'ex.id_producto = p.id', 'left')
        ->groupBy('p.id, p.codigo, p.nombre, p.descripcion, p.imagen')
        ->orderBy('p.nombre', 'ASC')
        ->get()
        ->getResultArray();
}

    public function getUnidadesEnum()
    {
        $query = $this->db->query("SHOW COLUMNS FROM producto_pedido LIKE 'unidad_venta'");
        $row = $query->getRow();
        preg_match_all("/'([^']+)'/", $row->Type, $matches);
        return $matches[1];
    }

    public function getRepartidores()
    {
        return $this->db->table('repartidor')
            ->select('id, nombre, ap_p, ap_m')
            ->get()
            ->getResultArray();
    }

    // ------- Consultas para guardar_productos_pedido() -------

    public function getNombreCliente($idCliente)
    {
        $clienteRow = $this->db->table('clientes')
            ->select("CONCAT(nombre, ' ', apellido_paterno) as nombre_completo")
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        return $clienteRow['nombre_completo'] ?? 'Desconocido';
    }

    public function getNombreRepartidor($idRepartidor)
    {
        $rep = $this->db->table('repartidor')
            ->select("CONCAT(nombre, ' ', ap_p) as nombre_completo")
            ->where('id', $idRepartidor)
            ->get()->getRowArray();

        return $rep['nombre_completo'] ?? null;
    }

    public function insertarStatus($idPedido, $estado)
    {
        return $this->db->table('status')->insert([
            'id_pedido' => $idPedido,
            'estado'    => $estado,
            'fecha'     => date('Y-m-d H:i:s'),
        ]);
    }

    // Crea el pedido + sus productos + su status inicial dentro de una transacción
    public function crearPedidoCompleto($pedidoData, array $productos, $productoPedModel)
    {
        $this->db->transStart();
        $errorDb = null;

        if ($this->insert($pedidoData) === false) {
            $errorDb = $this->db->error();
        }
        $idPedido = $this->db->insertID();

        foreach ($productos as $prod) {
            $cantidad = (float) $prod['cantidad'];
            $precio   = (float) $prod['precio_venta'];
            $subtotal = $cantidad * $precio;

            $guardado = $productoPedModel->insert([
                'id_pedido'    => $idPedido,
                'id_producto'  => (int) $prod['id_producto'],
                'cantidad'     => $cantidad,
                'precio_venta' => $precio,
                'unidad_venta' => $prod['unidad'],
                'tipo_venta'   => $pedidoData['tipo_pago'],
                'subtotal'     => $subtotal,
                'total'        => $subtotal,
            ]);

            if ($guardado === false && $errorDb === null) {
                $errorDb = $this->db->error();
            }
        }

        $this->insertarStatus($idPedido, $pedidoData['estado_actual']);

        $this->db->transComplete();

        $ok = $this->db->transStatus() !== false;

        return [
            'ok'       => $ok,
            'idPedido' => $idPedido,
            // El motivo real del fallo (por ejemplo, una columna que falta o una llave foránea)
            'error'    => $ok ? null : ($errorDb ?: $this->db->error()),
        ];
    }

    /**
     * Cliente real que representa al "Público general" (ventas sin cliente registrado).
     * pedido.id_cliente tiene llave foránea, así que no se puede guardar el 0.
     * Se crea una sola vez y no aparece en las listas de clientes (tipo_cliente = 'publico').
     */
    public function idClientePublicoGeneral(): int
    {
        $fila = $this->db->table('clientes')->select('id_cliente')->where('tipo_cliente', 'publico')->get()->getRowArray();
        if ($fila) {
            return (int) $fila['id_cliente'];
        }

        $this->db->table('clientes')->insert([
            'nombre'           => 'Público general',
            'apellido_paterno' => null,
            'apellido_materno' => '',
            'rfc'              => null,
            'tipo_cliente'     => 'publico',
            'tel'              => null,
        ]);

        return (int) $this->db->insertID();
    }

    // ------- Consultas para eliminarPedido() -------

    public function eliminarPedidoCompleto($id)
    {
        $this->db->transStart();

        // Si el pedido ya había descontado stock, la mercancía regresa al inventario
        $fila = $this->db->table('pedido')->select('stock_descontado')->where('id', $id)->get()->getRowArray();
        if ($fila && (int) $fila['stock_descontado'] === 1) {
            $productos = $this->db->table('producto_pedido')
                ->select('id_producto, cantidad')->where('id_pedido', $id)
                ->get()->getResultArray();
            (new \App\Models\ExistenciasModel())->devolverStock($productos);
        }

        $this->db->table('producto_pedido')->where('id_pedido', $id)->delete();
        $this->db->table('status')->where('id_pedido', $id)->delete();
        $this->db->table('pedido')->where('id', $id)->delete();

        $this->db->transComplete();

        return $this->db->transStatus() !== false;
    }

    // ------- Consultas para cambiarEstado() -------

    public function cambiarEstadoPedido($id, $estado)
    {
        $this->db->table('pedido')->where('id', $id)->update(['estado_actual' => $estado]);
        $this->insertarStatus($id, $estado);
    }

    // ------- Rotación de productos (para el catálogo de entradas) -------

    /**
     * Ventas por producto en los últimos $dias días.
     * Devuelve [id_producto => ['ventas' => nº de pedidos, 'ingreso' => importe vendido]].
     * Se cuenta por pedidos y no por cantidad, porque las unidades cambian (kilo, domos, caja).
     */
    public function getRotacionPorProducto(int $dias = 30): array
    {
        $desde = date('Y-m-d H:i:s', strtotime('-' . max(1, $dias) . ' days'));

        $filas = $this->db->table('producto_pedido pp')
            ->select('pp.id_producto, COUNT(DISTINCT pp.id_pedido) AS ventas, SUM(pp.cantidad * pp.precio_venta) AS ingreso')
            ->join('pedido pd', 'pd.id = pp.id_pedido')
            ->where('pd.fecha >=', $desde)
            ->whereIn('pd.estado_actual', self::ESTADOS_VENTA)
            ->groupBy('pp.id_producto')
            ->get()
            ->getResultArray();

        $rotacion = [];
        foreach ($filas as $f) {
            $rotacion[(int) $f['id_producto']] = [
                'ventas'  => (int) $f['ventas'],
                'ingreso' => (float) $f['ingreso'],
            ];
        }
        return $rotacion;
    }
    public function stock_vendedor()
{
    $pedidoModel = new \App\Models\PedidoModel();

    $productos = $pedidoModel->getProductosConPrecio();

    return view('vendedor/stock', [
        'productos' => $productos
    ]);
}
  
}