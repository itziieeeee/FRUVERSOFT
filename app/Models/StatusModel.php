<?php
namespace App\Models;
use App\Services\AuditoriaService;
use App\Services\PagoService;
use CodeIgniter\Model;

class StatusModel extends Model
{
    protected $table      = 'pedido';
    protected $primaryKey = 'id';
    protected $allowedFields = ['estado_actual', 'monto_pagado', 'tipo_pago'];

    /** Motivo del último fallo (por ejemplo "Stock insuficiente") */
    public string $ultimoError = '';

    /**
     * REGLA ÚNICA DE STOCK
     * El stock de un pedido solo debe estar descontado cuando el pedido está
     * confirmado: es decir, en cualquier estado menos "Pedido" y "Pedido cancelado".
     * La columna pedido.stock_descontado (0/1) recuerda si ya se descontó,
     * así nunca se descuenta ni se devuelve dos veces.
     */
    private function debeTenerStockDescontado(string $estado): bool
    {
        return !in_array($estado, ['Pedido', 'Pedido cancelado'], true);
    }

    private function sincronizarStock($id, string $estadoNuevo): bool
    {
        $fila = $this->db->table('pedido')
            ->select('stock_descontado')->where('id', $id)
            ->get()->getRowArray();

        $yaDescontado = (int) ($fila['stock_descontado'] ?? 0) === 1;
        $debeEstarlo  = $this->debeTenerStockDescontado($estadoNuevo);

        if ($yaDescontado === $debeEstarlo) {
            return true;                              // el stock ya está como debe
        }

        $productos = $this->db->table('producto_pedido')
            ->select('id_producto, cantidad, unidad_venta')
            ->where('id_pedido', $id)
            ->get()->getResultArray();

        if (!empty($productos)) {
            $existencias = new \App\Models\ExistenciasModel();

            if ($debeEstarlo) {                       // se confirma: descontar
                if (!empty($existencias->validarStockPedido($productos))) {
                    $this->ultimoError = 'Stock insuficiente para confirmar el pedido';
                    return false;
                }
                $existencias->descontarStock($productos);
            } else {                                  // se cancela: devolver
                $existencias->devolverStock($productos);
            }
        }

        $this->db->table('pedido')->where('id', $id)
            ->update(['stock_descontado' => $debeEstarlo ? 1 : 0]);

        return true;
    }

    public function actualizarEstado($id, $estado)
    {
        $this->ultimoError = '';

        $antes = $this->find($id);
        if (!$antes) {
            $this->ultimoError = 'Pedido no encontrado';
            return false;
        }

        if (!$this->sincronizarStock($id, $estado)) {
            return false;
        }

        $this->update($id, ['estado_actual' => $estado]);

        if ($antes['estado_actual'] !== $estado) {
            AuditoriaService::registrar(
                'pedido.estado', 'pedido', $id,
                AuditoriaService::folio($id) . ': ' . $antes['estado_actual'] . ' → ' . $estado,
                ['antes' => $antes['estado_actual'], 'despues' => $estado]
            );
        }

        $this->db->table('status')
            ->where('id_pedido', $id)
            ->update([
                'estado' => $estado,
                'fecha'  => date('Y-m-d H:i:s')
            ]);

        return true;
    }

    public function getPedidoConDetalle($id)
    {
        return $this->db->table('pedido')
            ->select('
                pedido.id,
                pedido.fecha,
                pedido.total,
                pedido.estado_actual,
                pedido.tipo_pago,
                pedido.monto_pagado,
                pedido.tipo_entrega,
                TRIM(CONCAT_WS(" ", clientes.nombre, NULLIF(clientes.apellido_paterno, ""), NULLIF(clientes.apellido_materno, ""))) AS nombre_cliente,
                CONCAT(repartidor.nombre, " ", repartidor.ap_p) AS nombre_repartidor
            ')
            ->join('clientes', 'clientes.id_cliente = pedido.id_cliente', 'left')
            ->join('repartidor', 'repartidor.id = pedido.id_repartidor', 'left')
            ->where('pedido.id', $id)
            ->get()->getRowArray();
    }

    
    
public function getProductosDePedido($id)
{
    return $this->db->table('producto_pedido pp')
        ->select('
            pp.id AS id_producto_pedido,
            pp.id_producto,
            p.nombre,
            pp.cantidad,
            pp.unidad_venta,
            pp.precio_venta,
            pp.subtotal,
            pp.tipo_venta
        ')
        ->join('producto p', 'p.id = pp.id_producto')
        ->where('pp.id_pedido', $id)
        ->get()->getResultArray();
}

    

    /**
     * Fija cuánto lleva pagado un pedido. La diferencia contra lo que llevaba queda en la tabla `pago`
     * (entrada si subió, salida si bajó) para que el corte de caja cuadre.
     */
    public function actualizarPago($id, $montoPagado, $total, string $metodo = 'efectivo')
    {
        $tipoPago    = ($montoPagado >= $total) ? 'contado' : 'credito';
        $estadoNuevo = ($montoPagado >= $total) ? 'Pedido pagado' : 'Pedido a crédito';

        $this->ultimoError = '';
        if (!$this->sincronizarStock($id, $estadoNuevo)) {
            return false;
        }

        $antes = $this->db->table('pedido')->select('monto_pagado, estado_actual')->where('id', $id)->get()->getRowArray();
        $pagadoAntes = (float) ($antes['monto_pagado'] ?? 0);
        $diferencia  = round((float) $montoPagado - $pagadoAntes, 2);

        $this->update($id, [
            'monto_pagado'  => $montoPagado,
            'tipo_pago'     => $tipoPago,
            'estado_actual' => $estadoNuevo,
        ]);

        if (abs($diferencia) >= 0.005) {
            (new PagoService($this->db))->registrar((int) $id, $diferencia, $metodo, $diferencia > 0 ? 'abono' : 'ajuste');
            AuditoriaService::registrar(
                'pedido.pago', 'pedido', $id,
                AuditoriaService::folio($id) . ': pagado $' . number_format($pagadoAntes, 2) . ' → $' . number_format((float) $montoPagado, 2)
                    . ' (' . PagoService::metodoValido($metodo) . ')',
                ['antes' => $pagadoAntes, 'despues' => (float) $montoPagado, 'metodo' => PagoService::metodoValido($metodo)]
            );
        }

        $this->db->table('status')->insert([
            'id_pedido' => $id,
            'estado'    => $estadoNuevo,
            'fecha'     => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    public function validarYConfirmar($idPedido)
    {
        // Si el pedido ya descontó su stock, no se vuelve a descontar
        $fila = $this->db->table('pedido')->select('stock_descontado, estado_actual')
            ->where('id', $idPedido)->get()->getRowArray();
        if ($fila && (int) $fila['stock_descontado'] === 1) {
            return ['success' => true, 'estado' => $fila['estado_actual']];
        }

        $productos = $this->db->table('producto_pedido')
            ->select('id_producto, cantidad, unidad_venta')
            ->where('id_pedido', $idPedido)
            ->get()->getResultArray();

        if (empty($productos)) {
            return ['success' => false, 'message' => 'El pedido no tiene productos'];
        }

        $existenciasModel = new \App\Models\ExistenciasModel();
        $faltantes        = $existenciasModel->validarStockPedido($productos);

        if (!empty($faltantes)) {
            return [
                'success'   => false,
                'sin_stock' => true,
                'faltantes' => $faltantes,
            ];
        }

        $existenciasModel->descontarStock($productos);

        $this->db->table('pedido')->where('id', $idPedido)->update(['stock_descontado' => 1]);
        $this->update($idPedido, ['estado_actual' => 'Pedido confirmado']);

        $this->db->table('status')->insert([
            'id_pedido' => $idPedido,
            'estado'    => 'Pedido confirmado',
            'fecha'     => date('Y-m-d H:i:s'),
        ]);

        AuditoriaService::registrar(
            'pedido.estado', 'pedido', $idPedido,
            AuditoriaService::folio($idPedido) . ': ' . ($fila['estado_actual'] ?? 'Pedido') . ' → Pedido confirmado (se descontó el stock)',
            ['antes' => $fila['estado_actual'] ?? null, 'despues' => 'Pedido confirmado']
        );

        return ['success' => true, 'estado' => 'Pedido confirmado'];
    }

    public function obtenerPedidos()
    {
        $evidencia = $this->db->fieldExists('foto_entrega', 'salida_pedido')
            ? ", (SELECT COUNT(*) FROM salida_pedido sp WHERE sp.id_pedido = pedido.id AND (sp.foto_entrega IS NOT NULL OR sp.firma_entrega IS NOT NULL OR sp.monto_cobrado IS NOT NULL)) AS tiene_evidencia"
            : '';

        return $this->db->table('pedido')
            ->select('pedido.*, TRIM(CONCAT_WS(" ", clientes.nombre, NULLIF(clientes.apellido_paterno, ""), NULLIF(clientes.apellido_materno, ""))) AS nombre_cliente' . $evidencia, false)
            ->join('clientes', 'clientes.id_cliente = pedido.id_cliente', 'left')
            ->get()
            ->getResultArray();
    }
}