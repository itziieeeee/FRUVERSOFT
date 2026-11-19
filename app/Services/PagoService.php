<?php
namespace App\Services;

/**
 * PAGOS
 * ------------------------------------------------------------
 * Cada cobro y cada reembolso queda en la tabla `pago`. El corte de caja se arma con ella.
 *  monto > 0  entra dinero (venta, abono, cobro del repartidor)
 *  monto < 0  sale dinero (reembolso por devolución o corrección)
 */
class PagoService
{
    public const METODOS = ['efectivo', 'transferencia', 'tarjeta'];

    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public static function metodoValido($metodo): string
    {
        $metodo = strtolower(trim((string) $metodo));
        return in_array($metodo, self::METODOS, true) ? $metodo : 'efectivo';
    }

    /** Solo escribe el movimiento. Quien lo llama es quien actualiza pedido.monto_pagado. */
    public function registrar(int $idPedido, float $monto, string $metodo, string $origen, ?string $nota = null): void
    {
        if (abs($monto) < 0.005 || !$this->db->tableExists('pago')) {
            return;
        }

        $idUsuario = null;
        $usuario   = 'Sistema';
        if (!is_cli()) {
            $s         = session();
            $idUsuario = $s->get('id_usuario') !== null ? (int) $s->get('id_usuario') : null;
            $usuario   = (string) ($s->get('nombre') ?: 'Sistema');
        }

        $this->db->table('pago')->insert([
            'id_pedido'  => $idPedido,
            'monto'      => round($monto, 2),
            'metodo'     => self::metodoValido($metodo),
            'origen'     => mb_substr($origen, 0, 20),
            'id_usuario' => $idUsuario,
            'usuario'    => mb_substr($usuario, 0, 120),
            'fecha'      => date('Y-m-d H:i:s'),
            'nota'       => $nota !== null ? mb_substr($nota, 0, 200) : null,
        ]);
    }

    /**
     * Cobra algo de un pedido: escribe el movimiento y sube pedido.monto_pagado.
     * Si con esto queda pagado por completo, el pedido deja de ser crédito.
     *
     * @return array{monto_pagado: float, saldo: float}
     * @throws \RuntimeException si el monto no es válido
     */
    public function cobrar(int $idPedido, float $monto, string $metodo, string $origen, ?string $nota = null): array
    {
        $pedido = $this->db->table('pedido')->select('total, monto_pagado, tipo_pago, estado_actual')->where('id', $idPedido)->get()->getRowArray();
        if (!$pedido) {
            throw new \RuntimeException('El pedido no existe.');
        }
        if ($pedido['estado_actual'] === 'Pedido cancelado') {
            throw new \RuntimeException('El pedido está cancelado.');
        }

        $monto = round($monto, 2);
        $saldo = round((float) $pedido['total'] - (float) $pedido['monto_pagado'], 2);
        if ($monto <= 0) {
            throw new \RuntimeException('El monto cobrado debe ser mayor a cero.');
        }
        if ($monto > $saldo + 0.001) {
            throw new \RuntimeException('El cobro ($' . number_format($monto, 2) . ') es mayor al saldo del pedido ($' . number_format($saldo, 2) . ').');
        }

        $nuevoPagado = round((float) $pedido['monto_pagado'] + $monto, 2);
        $cambios = ['monto_pagado' => $nuevoPagado];
        if ($nuevoPagado >= (float) $pedido['total'] - 0.001 && $pedido['tipo_pago'] === 'credito') {
            $cambios['tipo_pago'] = 'contado';
        }

        $this->db->table('pedido')->where('id', $idPedido)->update($cambios);
        $this->registrar($idPedido, $monto, $metodo, $origen, $nota);

        return ['monto_pagado' => $nuevoPagado, 'saldo' => round((float) $pedido['total'] - $nuevoPagado, 2)];
    }
}
