<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PedidoModel;

class ClientePedidoController extends BaseController
{

public function index()
{
    $pedidoModel = new PedidoModel();
    $pedidos = $pedidoModel->getPedidosConCliente();

    foreach ($pedidos as &$pedido) {
        $total = (float) ($pedido['total'] ?? 0);
        $pagado = (float) ($pedido['monto_pagado'] ?? 0);
        $tipo = $pedido['tipo_pago'] ?? 'contado';

        $saldo = max(0, $total - $pagado);
        $pedido['saldo_pendiente'] = $saldo;

        if ($tipo === 'credito') {
            if ($saldo <= 0) {
                $pedido['estado_liquidado'] = 'Liquidado';
                $pedido['badge_clase'] = 'bg-success';
            } elseif ($pagado > 0) {
                $pedido['estado_liquidado'] = 'Pago parcial';
                $pedido['badge_clase'] = 'bg-warning text-dark';
            } else {
                $pedido['estado_liquidado'] = 'Pendiente de pago';
                $pedido['badge_clase'] = 'bg-danger';
            }
        } else {
            if ($saldo <= 0) {
                $pedido['estado_liquidado'] = 'Pagado de contado';
                $pedido['badge_clase'] = 'bg-success';
            } else {
                $pedido['estado_liquidado'] = 'Contado pendiente';
                $pedido['badge_clase'] = 'bg-danger';
            }
        }
    }

    unset($pedido);

    return view('Cliente/vista_PedidoCliente', [
        'pedidos' => $pedidos
    ]);
}

    
    public function menu()
    {
        $avisoModel = new \App\Models\AvisoModel();
        $data['avisos'] = $avisoModel->getAvisosCliente();

        return view('Cliente/menu', $data);
    }

    public function historial()
    {
        $pedidoModel = new PedidoModel();
        $pedidos = $pedidoModel->getPedidosConCliente();

        return view('Cliente/vista_historial', ['pedidos' => $pedidos]);
    }

    
public function creditos()
{
    $pedidoModel = new PedidoModel();
    $pedidos = $pedidoModel->getPedidosConCliente();

    foreach ($pedidos as &$pedido) {
        $total = (float) ($pedido['total'] ?? 0);
        $pagado = (float) ($pedido['monto_pagado'] ?? 0);
        $tipo = $pedido['tipo_pago'] ?? 'contado';

        // Evitar saldos negativos si el monto pagado supera el total.
        $pedido['saldo_pendiente'] = max(0, $total - $pagado);

        if ($tipo === 'credito') {
            if ($pedido['saldo_pendiente'] <= 0) {
                $pedido['estado_liquidado'] = 'Liquidado';
                $pedido['badge_clase'] = 'bg-success';
            } elseif ($pagado > 0) {
                $pedido['estado_liquidado'] = 'Pago parcial';
                $pedido['badge_clase'] = 'bg-warning text-dark';
            } else {
                $pedido['estado_liquidado'] = 'Pendiente de pago';
                $pedido['badge_clase'] = 'bg-danger';
            }
        } else {
    if ($pedido['saldo_pendiente'] <= 0) {
        $pedido['estado_liquidado'] = 'Pagado de contado';
        $pedido['badge_clase'] = 'bg-success';
    } else {
        $pedido['estado_liquidado'] = 'Contado pendiente';
        $pedido['badge_clase'] = 'bg-danger';
    }
}
    }
    unset($pedido);

    return view('Cliente/vista_creditos', [
        'pedidos' => $pedidos,
    ]);
}

    public function estado()
    {
        $pedidoModel = new PedidoModel();
        $pedidos = $pedidoModel->getPedidosConCliente();

        return view('Cliente/vista_estado_pedidos', ['pedidos' => $pedidos]);
    }
}