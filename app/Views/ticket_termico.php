<?php
/**
 * Ticket para impresora térmica de 58 mm (ESC/POS, 203 dpi).
 * Ancho imprimible por defecto: 48 mm (384 puntos). Se puede cambiar con ?ancho=NN (en mm).
 */
$pedido = $pedido ?? [];
$items  = $items ?? [];

$idPedido = $pedido['id'] ?? 0;
$folio    = 'PED-' . str_pad((string) $idPedido, 5, '0', STR_PAD_LEFT);
$fecha    = !empty($pedido['fecha']) ? date('d/m/Y h:i A', strtotime($pedido['fecha'])) : date('d/m/Y h:i A');

$total  = (float) ($pedido['total'] ?? 0);
$pagado = (float) ($pedido['monto_pagado'] ?? 0);
$saldo  = max(0, $total - $pagado);

$tipoPago    = $pedido['tipo_pago'] ?? 'contado';
$tipoEntrega = $pedido['tipo_entrega'] ?? 'tienda';
$cliente     = trim(($pedido['nombre'] ?? '') . ' ' . ($pedido['apellido_paterno'] ?? ''));
$ancho       = (int) ($ancho ?? 48);
$imprimir    = !empty($imprimir);

$dinero = static fn (float $v): string => '$' . number_format($v, 2);
$cant   = static fn (float $v): string => rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ticket <?= esc($folio) ?></title>
<style>
    /* Hoja de 58 mm de ancho y alto según el contenido; sin márgenes (los pone el ancho del ticket) */
    @page { size: 58mm auto; margin: 0; }

    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body {
        background: #e5e7eb;
        font-family: 'Courier New', Courier, monospace;
        color: #000;
    }

    .vista { padding: 14px 0 28px; }
    .barra {
        width: 320px; max-width: 94%; margin: 0 auto 12px;
        display: flex; gap: 8px; font-family: Arial, sans-serif;
    }
    .barra button, .barra a {
        flex: 1; padding: 11px 8px; border: 0; border-radius: 8px; cursor: pointer;
        font-size: 14px; font-weight: bold; text-align: center; text-decoration: none; color: #fff;
    }
    .b-imp { background: #1d4a27; }
    .b-vol { background: #6b7280; }
    .aviso {
        width: 320px; max-width: 94%; margin: 0 auto 10px; font: 12px Arial, sans-serif; color: #4b5563;
    }

    .tk {
        width: <?= $ancho ?>mm;
        margin: 0 auto;
        padding: 2mm 0 6mm;
        background: #fff;
        font-size: 11px;
        line-height: 1.25;
    }
    .centro { text-align: center; }
    .logo {
        display: block; width: 20mm; height: auto; margin: 0 auto 1mm;
        filter: grayscale(1) contrast(1.6);
    }
    .marca { font-size: 17px; font-weight: bold; letter-spacing: 3px; }
    .sub   { font-size: 10px; }
    .folio { font-size: 14px; font-weight: bold; margin: 1.5mm 0; }
    .raya  { border: 0; border-top: 1px dashed #000; margin: 1.5mm 0; }
    .doble { border-top: 2px solid #000; }

    .fila { display: flex; justify-content: space-between; gap: 2mm; }
    .fila span:last-child { text-align: right; }
    .dato { word-break: break-word; }

    .prod { margin-bottom: 1.5mm; }
    .prod .nom { font-weight: bold; word-break: break-word; }

    .total { font-size: 16px; font-weight: bold; margin: 1mm 0; }
    .grande { font-weight: bold; }
    .gracias { font-weight: bold; margin-top: 2mm; }
    .nota { font-size: 9px; margin-top: 1mm; }

    @media print {
        body { background: #fff; }
        /* Solo el ticket: fuera la barra de depuración de CodeIgniter y cualquier otro agregado */
        body > *:not(.vista) { display: none !important; }
        .vista { padding: 0; }
        .barra, .aviso { display: none !important; }
        .tk { margin: 0 auto; padding-top: 0; }
    }
</style>
</head>
<body>
<div class="vista">

    <div class="barra">
        <button type="button" class="b-imp" onclick="window.print()">Imprimir ticket</button>
        <a class="b-vol" href="<?= esc(base_url('ticket/ver/' . (int) $idPedido), 'attr') ?>">Volver</a>
    </div>
    <div class="aviso">Formato de 58 mm. En el diálogo de impresión elige la impresora POS58, sin márgenes y escala 100%.</div>

    <div class="tk">
        <div class="centro">
            <?php if (!empty($logo)): ?>
                <img class="logo" src="<?= esc($logo, 'attr') ?>" alt="">
            <?php endif; ?>
            <div class="marca">FRUVER</div>
            <div class="sub">Frutas y verduras</div>
            <div class="folio"><?= esc($folio) ?></div>
        </div>

        <hr class="raya">

        <div class="fila"><span>Fecha</span><span><?= esc($fecha) ?></span></div>
        <div class="fila"><span>Cliente</span><span class="dato"><?= esc($cliente ?: 'Cliente general') ?></span></div>
        <div class="fila"><span>Entrega</span><span><?= $tipoEntrega === 'domicilio' ? 'A domicilio' : 'En tienda' ?></span></div>

        <hr class="raya">

        <?php if (!empty($items)): ?>
            <?php foreach ($items as $item): ?>
                <?php
                $cantidad = (float) ($item['cantidad'] ?? 0);
                $precio   = (float) ($item['precio_unitario'] ?? 0);
                $importe  = (float) ($item['subtotal'] ?? ($cantidad * $precio));
                ?>
                <div class="prod">
                    <div class="nom"><?= esc($item['nombre_producto'] ?? 'Producto') ?></div>
                    <div class="fila">
                        <span><?= esc($cant($cantidad) . ' ' . ($item['unidad'] ?? 'pza') . ' x ' . $dinero($precio)) ?></span>
                        <span><?= esc($dinero($importe)) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div>Sin productos registrados.</div>
        <?php endif; ?>

        <hr class="raya doble">

        <div class="fila total"><span>TOTAL</span><span><?= esc($dinero($total)) ?></span></div>

        <div class="fila"><span>Pago</span><span><?= $tipoPago === 'credito' ? 'CREDITO' : 'CONTADO' ?></span></div>
        <div class="fila"><span>Pagado</span><span><?= esc($dinero($pagado)) ?></span></div>
        <?php if ($saldo > 0): ?>
            <div class="fila grande"><span>Saldo pendiente</span><span><?= esc($dinero($saldo)) ?></span></div>
        <?php endif; ?>
        <div class="fila grande">
            <span>Estado</span>
            <span><?= $tipoPago === 'credito' ? ($saldo > 0 ? 'CREDITO PENDIENTE' : 'CREDITO PAGADO') : ($saldo > 0 ? 'PAGO PARCIAL' : 'PAGADO') ?></span>
        </div>

        <hr class="raya">

        <div class="centro">
            <div class="gracias">Gracias por tu compra</div>
            <div class="sub">Frescura todos los dias</div>
            <div class="nota">Comprobante de compra. No es factura fiscal.</div>
        </div>
    </div>
</div>

<?php if ($imprimir): ?>
<script>
// Se imprime cuando ya cargó todo (incluido el logo). El largo del papel lo controla el driver de la impresora.
window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });
</script>
<?php endif; ?>


</body>
</html>
