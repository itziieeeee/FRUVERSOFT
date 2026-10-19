
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras | FRUVERSOFT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('CSS/Cliente/menu.css') ?>" rel="stylesheet">
</head>

<body>

    <?= $this->include('Cliente/partials/navbar') ?>

    <main class="container py-4 py-lg-5">

        <section class="fruver-encabezado">
            <h1> Historial de mis compras</h1>
            <p>Consulta tus pedidos anteriores, las fechas y los importes de tus compras.</p>
        </section>

        <section class="fruver-tabla-contenedor">
            <div class="p-3 p-md-4">
                <h2 class="h4 mb-1">Mis compras registradas</h2>
                <p class="text-muted mb-0">
                 
                </p>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th># Pedido</th>
                            <th>Fecha</th>
                            <th>Tipo de pago</th>
                            <th>Total</th>
                            <th>Acciones</th>
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
                                        <span class="badge bg-secondary text-uppercase">
                                            <?= esc($pedido['tipo_pago'] ?? 'contado') ?>
                                        </span>
                                    </td>

                                    <td>
                                        <strong>
                                            $<?= number_format((float) ($pedido['total'] ?? 0), 2) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-fruver btn-sm">
                                            Ver detalle
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="mb-2">
                                        <span class="fs-2">🛒</span>
                                    </div>
                                    <strong>No tienes compras registradas.</strong>
                                    <p class="mb-0">
                                        Cuando realices una compra, aparecerá aquí.
                                    </p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <footer class="fruver-footer">
            FRUVERSOFT · Historial de compras
        </footer>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
