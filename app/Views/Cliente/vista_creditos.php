<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créditos y Liquidaciones | FRUVERSOFT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('CSS/Cliente/menu.css') ?>" rel="stylesheet">
</head>

<body>

    <?= $this->include('Cliente/partials/navbar') ?>

    <main class="container py-4 py-lg-5">

        <section class="fruver-encabezado">
            <h1>💳 Mis créditos y liquidaciones</h1>
            <p>
                Consulta el total de tus compras, los pagos realizados
                y los saldos que todavía tienes pendientes.
            </p>
        </section>

        <?php
            $totalCompras = 0;
            $totalPagado = 0;
            $totalPendiente = 0;

            foreach ($pedidos ?? [] as $pedido) {
                $totalCompras += (float) ($pedido['total'] ?? 0);
                $totalPagado += (float) ($pedido['monto_pagado'] ?? 0);
                $totalPendiente += (float) ($pedido['saldo_pendiente'] ?? 0);
            }
        ?>

        <div class="row g-3 mb-4">

            <div class="col-12 col-md-4">
                <div class="fruver-resumen">
                    <p class="titulo">Total de compras</p>
                    <div class="cantidad">
                        $<?= number_format($totalCompras, 2) ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="fruver-resumen">
                    <p class="titulo">Total pagado</p>
                    <div class="cantidad">
                        $<?= number_format($totalPagado, 2) ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="fruver-resumen naranja">
                    <p class="titulo">Saldo pendiente</p>
                    <div class="cantidad">
                        $<?= number_format($totalPendiente, 2) ?>
                    </div>
                </div>
            </div>

        </div>

        <section class="fruver-tabla-contenedor">

            <div class="p-3 p-md-4">
                <h2 class="h4 mb-1">Detalle de mis pedidos</h2>
                <p class="text-muted mb-0">
                    Consulta el estado de pago de cada pedido registrado.
                </p>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Fecha</th>
                            <th>Tipo de pago</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Saldo pendiente</th>
                            <th>Estado del pago</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($pedidos)): ?>

                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= esc($pedido['id'] ?? '') ?></strong>
                                    </td>

                                    <td>
                                        <?= esc($pedido['fecha'] ?? '') ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= esc(ucfirst($pedido['tipo_pago'] ?? 'contado')) ?>
                                        </span>
                                    </td>

                                    <td>
                                        $<?= number_format((float) ($pedido['total'] ?? 0), 2) ?>
                                    </td>

                                    <td>
                                        $<?= number_format((float) ($pedido['monto_pagado'] ?? 0), 2) ?>
                                    </td>

                                    <td>
                                        <strong>
                                            $<?= number_format((float) ($pedido['saldo_pendiente'] ?? 0), 2) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <span class="badge <?= esc($pedido['badge_clase'] ?? 'bg-secondary') ?>">
                                            <?= esc($pedido['estado_liquidado'] ?? 'Sin determinar') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <div class="mb-2">
                                        <span class="fs-2">📋</span>
                                    </div>
                                    <strong>No hay pedidos para mostrar.</strong>
                                    <p class="mb-0">
                                        Cuando tengas pedidos registrados, aparecerán aquí.
                                    </p>
                                </td>
                            </tr>

                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <footer class="fruver-footer">
            FRUVERSOFT · Consulta de créditos y liquidaciones
        </footer>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>