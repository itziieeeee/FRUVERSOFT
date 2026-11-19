<?php

/**
 * Teléfono listo para wa.me: solo dígitos y con lada de México (52) si vienen 10 dígitos.
 */
if (!function_exists('formato_telefono_wa')) {
    function formato_telefono_wa($telefono): string
    {
        $n = preg_replace('/[^0-9]/', '', (string) $telefono);
        if (strlen($n) === 10) {
            $n = '52' . $n;
        }
        return $n;
    }
}

/**
 * Cantidad sin ceros de más: 2.00 -> 2, 1.50 -> 1.5
 */
if (!function_exists('cantidad_ticket')) {
    function cantidad_ticket($n): string
    {
        return rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    }
}

/**
 * Texto del ticket para WhatsApp.
 *
 * IMPORTANTE: sin emojis a propósito. La app de WhatsApp para Windows
 * los destruye (salen como "?") cuando el texto viene de un link wa.me.
 * Se usa solo texto, negritas (*) y cursivas (_), que sí viajan bien.
 *
 * $pedido: fila de pedido (id, fecha, total, tipo_entrega, tipo_pago, monto_pagado)
 * $items : nombre_producto, cantidad, unidad, precio_unitario y (opcional) subtotal
 */
if (!function_exists('armar_mensaje_ticket')) {
    function armar_mensaje_ticket(array $pedido, array $items, string $cliente = ''): string
    {
        $folio   = 'PED-' . str_pad($pedido['id'], 5, '0', STR_PAD_LEFT);
        $fecha   = !empty($pedido['fecha']) ? date('d/m/Y h:i A', strtotime($pedido['fecha'])) : date('d/m/Y h:i A');
        $total   = (float) ($pedido['total'] ?? 0);
        $pagado  = (float) ($pedido['monto_pagado'] ?? 0);
        $entrega = (($pedido['tipo_entrega'] ?? '') === 'domicilio') ? 'A domicilio' : 'En tienda';
        $linea   = '------------------------------';

        $m  = "*F R U V E R*\n";
        $m .= "_Frutas y verdura · Ticket de compra_\n";
        $m .= $linea . "\n";
        $m .= "*Folio:* {$folio}\n";
        $m .= "*Fecha:* {$fecha}\n";
        if ($cliente !== '') {
            $m .= "*Cliente:* {$cliente}\n";
        }
        $m .= "*Entrega:* {$entrega}\n";
        $m .= $linea . "\n";

        // Bloque monoespaciado: cada producto en dos líneas para que no se desacomode
        $m .= '```' . "\n";
        foreach ($items as $it) {
            $cant   = (float) $it['cantidad'];
            $precio = (float) ($it['precio_unitario'] ?? 0);
            $sub    = (isset($it['subtotal']) && $it['subtotal'] !== '' && $it['subtotal'] !== null)
                        ? (float) $it['subtotal'] : $cant * $precio;
            $unidad = trim((string) ($it['unidad'] ?? ''));

            $m .= $it['nombre_producto'] . "\n";
            $m .= '  ' . cantidad_ticket($cant) . ($unidad !== '' ? " {$unidad}" : '')
                . ' x $' . number_format($precio, 2)
                . ' = $' . number_format($sub, 2) . "\n";
        }
        $m .= '```' . "\n";
        $m .= $linea . "\n";
        $m .= "*TOTAL:  $" . number_format($total, 2) . "*\n";

        if ($total > 0 && $pagado >= $total) {
            $m .= "*PAGADO*\n";
        } elseif ($pagado > 0) {
            $m .= "Abonado: $" . number_format($pagado, 2) . "\n";
            $m .= "*Saldo pendiente: $" . number_format($total - $pagado, 2) . "*\n";
        } elseif (($pedido['tipo_pago'] ?? '') === 'credito') {
            $m .= "*A CREDITO* - Saldo pendiente: $" . number_format($total, 2) . "\n";
        }

        $m .= $linea . "\n";
        $m .= "_¡Gracias por tu compra, vuelve pronto!_";

        return $m;
    }
}

/**
 * Link wa.me con el ticket ya escrito (se usa al registrar la venta).
 */
if (!function_exists('generar_link_ticket_wa')) {
    function generar_link_ticket_wa($telefono, array $pedido, array $items, string $cliente = ''): string
    {
        return 'https://wa.me/' . formato_telefono_wa($telefono)
             . '?text=' . rawurlencode(armar_mensaje_ticket($pedido, $items, $cliente));
    }
    }
    if (!function_exists('generar_link_pdf_wa')) {
    function generar_link_pdf_wa($telefono, int $idPedido): string
    {
        $telefono = formato_telefono_wa($telefono);

        // Debe ser una dirección pública accesible desde el celular del cliente.
        $basePublica = rtrim((string) env('FRUVER_PUBLIC_URL', ''), '/');

        if ($basePublica === '') {
            return '';
        }

        $urlPdf = $basePublica . '/ticket/pdf/' . $idPedido;

        $folio = 'PED-' . str_pad($idPedido, 5, '0', STR_PAD_LEFT);

        $mensaje = "Hola, te compartimos tu ticket de compra de FRUVER.\n"
            . "Folio: {$folio}\n"
            . "Puedes consultar y descargar tu ticket PDF aquí:\n"
            . $urlPdf;

        return 'https://wa.me/' . $telefono . '?text=' . rawurlencode($mensaje);
    }
}
