
<?php
$esPdf = $esPdf ?? false;

$pedido = $pedido ?? [];
$items = $items ?? [];

$idPedido = $pedido['id'] ?? 0;
$folio = 'PED-' . str_pad((string) $idPedido, 5, '0', STR_PAD_LEFT);

$fechaOriginal = $pedido['fecha'] ?? '';

$fecha = $fechaOriginal
    ? date('d/m/Y h:i A', strtotime($fechaOriginal))
    : date('d/m/Y h:i A');

$total = (float) ($pedido['total'] ?? 0);
$pagado = (float) ($pedido['monto_pagado'] ?? 0);
$saldo = max(0, $total - $pagado);

$tipoPago = $pedido['tipo_pago'] ?? 'contado';
$tipoEntrega = $pedido['tipo_entrega'] ?? 'tienda';

$cliente = trim(
    ($pedido['nombre'] ?? '') . ' ' .
    ($pedido['apellido_paterno'] ?? '')
);

$telefono = $pedido['tel'] ?? '';

$logoSrc = $logo ?? '';

if (!empty($logoSrc) && strpos($logoSrc, 'data:image') !== 0) {
    $logoSrc = 'data:image/png;base64,' . $logoSrc;
}

$domicilio = $tipoEntrega === 'domicilio'
    ? 'Entrega a domicilio'
    : 'Recoger en tienda';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ticket <?= esc($folio) ?> | FRUVER</title>

    <?php if (!$esPdf): ?>
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <?php endif; ?>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 16px;
            background: #edf2f6;
            font-family: Arial, Helvetica, sans-serif;
            color: #183b28;
        }

        /* CONTENEDOR GENERAL */

        .contenedor {
            width: 100%;
            max-width: 430px;
            margin: 0 auto;
        }

        .ticket {
            width: 100%;
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            border-top: 8px solid #1d4a27;
            border-bottom: 8px solid #1d4a27;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .12);
        }

        .linea-naranja {
            height: 3px;
            background: #f16b1a;
        }

        .contenido {
            padding: 22px 20px;
        }

        /* ENCABEZADO */

        .encabezado {
            text-align: center;
            margin-bottom: 18px;
        }

        .logo {
            display: block;
            width: 62px;
            height: 62px;
            object-fit: contain;
            margin: 0 auto 7px;
        }

        .nombre-empresa {
            font-size: 25px;
            font-weight: 800;
            letter-spacing: 7px;
            color: #1d4a27;
            margin: 4px 0;
        }

        .subtitulo {
            color: #667b9a;
            font-size: 10px;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        .folio {
            display: inline-block;
            background: #e8f3e8;
            color: #1d4a27;
            padding: 7px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 12px;
        }

        /* DATOS DEL PEDIDO */

        .datos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .datos td {
            padding: 7px 2px;
            vertical-align: top;
            line-height: 1.4;
        }

        .datos td:first-child {
            color: #667b9a;
            width: 30%;
        }

        .datos td:last-child {
            text-align: right;
            font-weight: bold;
            color: #182b45;
            overflow-wrap: anywhere;
        }

        .separador {
            border-top: 1px dashed #aab6c5;
            margin: 15px 0;
        }

        /* PRODUCTOS */

        .seccion-titulo {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #1d4a27;
            margin: 13px 0 10px;
        }

        .tabla-productos {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            table-layout: fixed;
        }

        .tabla-productos thead {
            background: #1d4a27;
            color: white;
        }

        .tabla-productos th {
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
        }

        .tabla-productos th:first-child,
        .tabla-productos td:first-child {
            width: 68%;
        }

        .tabla-productos th:last-child,
        .tabla-productos td:last-child {
            width: 32%;
            text-align: right;
        }

        .tabla-productos td {
            padding: 11px 8px;
            border-bottom: 1px solid #e5eaf0;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        .tabla-productos td:last-child {
            font-weight: bold;
            white-space: nowrap;
        }

        .producto-nombre {
            font-weight: bold;
            color: #182b45;
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .producto-detalle {
            color: #667b9a;
            font-size: 10px;
            line-height: 1.5;
        }

        /* TOTAL */

        .total {
            margin-top: 15px;
            padding: 15px;
            border: 1px solid #ffbd98;
            background: #fff4ed;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            border-radius: 8px;
        }

        .total span {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .total strong {
            color: #f16b1a;
            font-size: 23px;
            white-space: nowrap;
        }

        /* INFORMACIÓN DE PAGO */

        .pago {
            margin-top: 13px;
            padding: 14px;
            background: #f3f7f3;
            border-radius: 9px;
            font-size: 12px;
        }

        .pago div {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            padding: 6px 0;
        }

        .pago div span {
            flex: 1;
        }

        .pago strong {
            color: #1d4a27;
            text-align: right;
        }

        .estado-pago {
            margin-top: 8px;
            padding-top: 9px !important;
            border-top: 1px dashed #c5d4c5;
            color: #1d4a27;
            font-weight: bold;
        }

        /* MENSAJE FINAL */

        .mensaje-final {
            text-align: center;
            margin-top: 20px;
        }

        .mensaje-final h3 {
            font-size: 15px;
            margin: 0 0 6px;
            color: #1d4a27;
        }

        .mensaje-final p {
            font-size: 10px;
            color: #667b9a;
            margin: 0;
        }

        .nota {
            text-align: center;
            color: #91a0b5;
            font-size: 9px;
            line-height: 1.5;
            margin-top: 13px;
        }

        /* BOTONES */

        .acciones {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 18px auto 0;
            width: 100%;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            min-height: 48px;
            padding: 12px 16px;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            color: white;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }

        .b-pdf {
            background: #f16b1a;
        }

        .b-print {
            background: #1d4a27;
        }

        .b-whatsapp {
            background: #168b4b;
        }

        .b-volver {
            background: #fff;
            color: #1d4a27;
            border: 1.5px solid #1d4a27;
        }

        .b-volver:hover {
            background: #e8f3e6;
        }

        /* =================================
           CELULARES PEQUEÑOS
           iPhone 13 mini y similares
        ================================= */

        @media screen and (max-width: 390px) {

            body {
                padding: 12px 9px;
            }

            .contenedor {
                max-width: 100%;
            }

            .contenido {
                padding: 19px 15px;
            }

            .nombre-empresa {
                font-size: 23px;
                letter-spacing: 6px;
            }

            .logo {
                width: 57px;
                height: 57px;
            }

            .datos {
                font-size: 11px;
            }

            .datos td {
                padding: 7px 2px;
            }

            .tabla-productos {
                font-size: 11px;
            }

            .tabla-productos th {
                padding: 9px 6px;
                font-size: 9px;
            }

            .tabla-productos td {
                padding: 10px 6px;
            }

            .producto-nombre {
                font-size: 12px;
            }

            .producto-detalle {
                font-size: 10px;
            }

            .total {
                padding: 13px 11px;
            }

            .total span {
                font-size: 13px;
            }

            .total strong {
                font-size: 21px;
            }

            .pago {
                padding: 12px;
                font-size: 11px;
            }
        }

        /* CELULARES MUY ESTRECHOS */

        @media screen and (max-width: 350px) {

            .contenido {
                padding: 15px 11px;
            }

            .nombre-empresa {
                font-size: 21px;
                letter-spacing: 5px;
            }

            .datos {
                font-size: 10px;
            }

            .tabla-productos th,
            .tabla-productos td {
                padding: 8px 4px;
            }

            .producto-nombre {
                font-size: 11px;
            }

            .producto-detalle {
                font-size: 9px;
            }

            .total strong {
                font-size: 19px;
            }
        }

        /* =================================
           TABLETS
        ================================= */

        @media screen and (min-width: 600px) {

            body {
                padding: 35px 20px;
            }

            .contenedor {
                max-width: 450px;
            }

            .contenido {
                padding: 25px 24px;
            }

            .datos {
                font-size: 13px;
            }

            .tabla-productos {
                font-size: 13px;
            }

            .producto-nombre {
                font-size: 13px;
            }

            .pago {
                font-size: 13px;
            }

            .acciones {
                max-width: 450px;
            }
        }

        /* =================================
           LAPTOP Y ESCRITORIO
        ================================= */

        @media screen and (min-width: 1024px) {

            body {
                padding: 40px 20px;
            }

            .contenedor {
                max-width: 430px;
            }

            .contenido {
                padding: 24px 23px;
            }

            .acciones {
                flex-direction: row;
                flex-wrap: wrap;
                max-width: 520px;
            }

            .acciones .btn {
                flex: 1 1 145px;
                width: auto;
                font-size: 12px;
                padding: 12px 10px;
            }
        }

        /* IMPRESIÓN Y PDF */

        @media print {

            body {
                padding: 0;
                background: white;
            }

            .contenedor {
                max-width: 100%;
            }

            .ticket {
                box-shadow: none;
                border-radius: 0;
            }

            .acciones {
                display: none !important;
            }
        }
        /* =====================================
   DISEÑO EXCLUSIVO PARA PDF TAMAÑO CARTA
===================================== */

@page {
    size: letter portrait;
    margin: 10mm;
}

.modo-pdf {
    background: #ffffff !important;
    margin: 0;
    padding: 0;
    font-size: 14px;
}

.modo-pdf .contenedor {
    width: 100% !important;
    max-width: none !important;
    margin: 0 auto !important;
}

.modo-pdf .ticket {
    width: 100% !important;
    max-width: none !important;
    margin: 0 auto !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    border-top: 7px solid #1d4a27;
    border-bottom: 7px solid #1d4a27;
}

.modo-pdf .contenido {
    padding: 25px 30px !important;
}

.modo-pdf .logo {
    width: 110px !important;
    height: auto !important;
}

.modo-pdf .nombre-empresa {
    font-size: 27px !important;
}

.modo-pdf .subtitulo {
    font-size: 14px !important;
}

.modo-pdf .folio {
    font-size: 17px !important;
    margin: 15px 0 !important;
}

.modo-pdf .datos {
    font-size: 14px !important;
    width: 100%;
}

.modo-pdf .seccion-titulo {
    font-size: 17px !important;
    margin: 20px 0 10px !important;
}

.modo-pdf .tabla-productos {
    width: 100% !important;
    font-size: 13px !important;
}

.modo-pdf .tabla-productos th,
.modo-pdf .tabla-productos td {
    padding: 11px 8px !important;
}

.modo-pdf .producto-nombre {
    font-size: 14px !important;
}

.modo-pdf .producto-detalle {
    font-size: 12px !important;
}

.modo-pdf .total {
    font-size: 23px !important;
    margin-top: 20px !important;
}

.modo-pdf .pago,
.modo-pdf .estado-pago {
    font-size: 14px !important;
}

.modo-pdf .mensaje-final {
    font-size: 16px !important;
    margin-top: 25px !important;
}

.modo-pdf .nota {
    font-size: 12px !important;
}

.modo-pdf .acciones {
    display: none !important;
}
.modo-pdf .ticket {
    background: #ffffff;
    border-top: 8px solid #1d4a27;
    border-bottom: 8px solid #1d4a27;
}

.modo-pdf .tabla-productos thead {
    background: #1d4a27 !important;
    color: #ffffff !important;
}

.modo-pdf .total {
    background: #fff4ed !important;
    border: 1px solid #f16b1a;
}

.modo-pdf .total strong {
    color: #f16b1a !important;
}

.modo-pdf .pago {
    background: #f3f7f3 !important;
}

.modo-pdf .linea-naranja {
    background: #f16b1a !important;
}

.modo-pdf .tabla-productos tr,
.modo-pdf .total,
.modo-pdf .pago {
    page-break-inside: avoid;
}

    </style>
</head>

<body class="<?= $esPdf ? 'modo-pdf' : 'modo-web' ?>">

<div class="contenedor">

    <div class="ticket">

        <div class="linea-naranja"></div>

        <div class="contenido">

            <div class="encabezado">

                <?php if (!empty($logoSrc)): ?>
                    <img
                        src="<?= esc($logoSrc, 'attr') ?>"
                        alt="Logo FRUVER"
                        class="logo"
                    >
                <?php endif; ?>

                <div class="nombre-empresa">FRUVER</div>

                <div class="subtitulo">
                    FRUTAS Y VERDURAS · TICKET DE COMPRA
                </div>

                <div class="folio">
                    <?= esc($folio) ?>
                </div>

            </div>

            <table class="datos">

                <tr>
                    <td>Fecha</td>
                    <td><?= esc($fecha) ?></td>
                </tr>

                <tr>
                    <td>Cliente</td>
                    <td><?= esc($cliente ?: 'Cliente general') ?></td>
                </tr>

                <tr>
                    <td>Teléfono</td>
                    <td><?= esc($telefono ?: 'No registrado') ?></td>
                </tr>

                <tr>
                    <td>Entrega</td>
                    <td><?= esc($domicilio) ?></td>
                </tr>

            </table>

            <div class="separador"></div>

            <div class="seccion-titulo">
                DETALLE DE PRODUCTOS
            </div>

            <table class="tabla-productos">

                <thead>
                    <tr>
                        <th>PRODUCTO</th>
                        <th>IMPORTE</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!empty($items)): ?>

                        <?php foreach ($items as $item): ?>

                            <?php
                            $nombre = $item['nombre_producto'] ?? 'Producto';

                            $cantidad = (float) ($item['cantidad'] ?? 0);

                            $unidad = $item['unidad'] ??
                                      $item['unidad_venta'] ??
                                      'pieza';

                            $precio = (float) (
                                $item['precio_unitario'] ??
                                $item['precio_venta'] ??
                                0
                            );

                            $importe = (float) (
                                $item['subtotal'] ??
                                $item['total'] ??
                                ($cantidad * $precio)
                            );
                            ?>

                            <tr>

                                <td>
                                    <div class="producto-nombre">
                                        <?= esc($nombre) ?>
                                    </div>

                                    <div class="producto-detalle">
                                        <?= rtrim(rtrim(number_format($cantidad, 2, '.', ''), '0'), '.') ?>
                                        <?= esc($unidad) ?>
                                        × $<?= number_format($precio, 2) ?>
                                    </div>
                                </td>

                                <td>
                                    $<?= number_format($importe, 2) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="2">
                                No se encontraron productos para este pedido.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

            <div class="total">
                <span>TOTAL</span>
                <strong>$<?= number_format($total, 2) ?></strong>
            </div>

            <div class="pago">

                <div>
                    <span>Tipo de pago</span>

                    <strong>
                        <?= $tipoPago === 'credito' ? 'VENTA A CRÉDITO' : 'CONTADO' ?>
                    </strong>
                </div>

                <div>
                    <span>Monto pagado</span>
                    <strong>$<?= number_format($pagado, 2) ?></strong>
                </div>

                <?php if ($tipoPago === 'credito'): ?>

                    <div>
                        <span>Saldo pendiente</span>
                        <strong>$<?= number_format($saldo, 2) ?></strong>
                    </div>

                    <div class="estado-pago">
                        <span>Estado</span>

                        <strong>
                            <?= $saldo > 0 ? 'CRÉDITO PENDIENTE' : 'CRÉDITO PAGADO' ?>
                        </strong>
                    </div>

                <?php else: ?>

                    <div class="estado-pago">
                        <span>Estado</span>

                        <strong>
                            <?= $saldo > 0 ? 'PAGO PARCIAL' : 'PAGADO' ?>
                        </strong>
                    </div>

                <?php endif; ?>

            </div>

            <div class="mensaje-final">
                <h3>¡Gracias por tu compra!</h3>
                <p>Frescura todos los días. Vuelve pronto.</p>
            </div>

            <div class="nota">
                Comprobante de compra. No es factura fiscal.
            </div>

        </div>

        <div class="linea-naranja"></div>

    </div>

    <?php if (!$esPdf): ?>

        <div class="acciones">

            <a
                href="<?= esc($urlPdf ?? '#', 'attr') ?>"
                class="btn b-pdf"
                target="_blank"
            >
                <i class="fa-solid fa-file-pdf"></i>
                Descargar PDF
            </a>

            <a
                href="<?= esc(base_url('ticket/termico/' . (int) $idPedido), 'attr') ?>"
                class="btn b-print"
            >
                <i class="fa-solid fa-receipt"></i>
                Ticket térmico 58 mm
            </a>

            <button
                type="button"
                class="btn b-print"
                onclick="window.print()"
            >
                <i class="fa-solid fa-print"></i>
                Imprimir / Guardar PDF
            </button>

            <?php if (!empty($urlWhatsApp)): ?>

                <a
                    href="<?= esc($urlWhatsApp, 'attr') ?>"
                    class="btn b-whatsapp"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="fa-brands fa-whatsapp"></i>
                    Enviar por WhatsApp
                </a>

            <?php endif; ?>

            <a
                href="<?= esc($urlVolver ?? base_url('/'), 'attr') ?>"
                class="btn b-volver"
                id="btnVolverTicket"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Regresar
            </a>

        </div>

        <script>
        // Regresar a la pantalla anterior. El ticket casi siempre se abre en una pestaña nueva
        // (por ejemplo desde Clientes > Historial de compras): en ese caso se cierra la pestaña
        // y queda a la vista la pantalla de donde se abrió. Si no se puede, va al enlace del botón.
        document.getElementById('btnVolverTicket').addEventListener('click', function (e) {
            var mismoSitio = document.referrer && document.referrer.indexOf(location.origin) === 0;

            if (history.length > 1 && mismoSitio) {
                e.preventDefault();
                history.back();
                return;
            }
            if (history.length <= 1) {
                e.preventDefault();
                var destino = this.href;
                window.close();
                // Si el navegador no dejó cerrar la pestaña, se navega al destino
                setTimeout(function () { location.href = destino; }, 250);
            }
        });
        </script>

    <?php endif; ?>

</div>

</body>
</html>
