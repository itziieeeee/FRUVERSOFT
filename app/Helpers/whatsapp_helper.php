<?php

if (!function_exists('generar_link_ticket_wa')) {
    function generar_link_ticket_wa($telefono, $idPedido, $items, $total) {
        // Dejar solo números en el teléfono (ej: 522212345678)
        $phone = preg_replace('/[^0-9]/', '', $telefono);

        $mensaje  = "🛒 *TICKET DE COMPRA - FRUVERSOFT* 🛒\n";
        $mensaje .= "--------------------------------------\n";
        $mensaje .= "*Pedido #:* " . str_pad($idPedido, 5, "0", STR_PAD_LEFT) . "\n";
        $mensaje .= "*Fecha:* " . date('d/m/Y h:i A') . "\n\n";
        $mensaje .= "*Detalle del Pedido:*\n";

        foreach ($items as $item) {
            $subtotal = $item['cantidad'] * $item['precio_unitario'];
            $mensaje .= "• " . $item['cantidad'] . "x " . $item['nombre_producto'] . " - $" . number_format($subtotal, 2) . "\n";
        }

        $mensaje .= "--------------------------------------\n";
        $mensaje .= "*TOTAL A PAGAR:* $" . number_format($total, 2) . "\n";
        $mensaje .= "--------------------------------------\n";
        $mensaje .= "¡Gracias por tu preferencia!🍎";

        return "https://wa.me/" . $phone . "?text=" . urlencode($mensaje);
    }
}