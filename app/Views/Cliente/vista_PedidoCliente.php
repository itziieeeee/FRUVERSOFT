
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos | FRUVERSOFT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('CSS/Cliente/menu.css') ?>" rel="stylesheet">
</head>

<body>

    <?= $this->include('Cliente/partials/navbar') ?>

    <main class="container py-4 py-lg-5">

        <section class="fruver-encabezado">
            <h1>📦 Mis pedidos e historial</h1>
            <p>Consulta tus pedidos, sus importes y el estado de sus pagos.</p>
        </section>

        <section class="fruver-tabla-contenedor">
            <div class="p-3 p-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="h4 mb-1">Mis pedidos registrados</h2>
                    <p class="text-muted mb-0">Revisa la información de tus pedidos.</p>
                </div>

                <a href="<?= base_url('catalogo') ?>" class="btn btn-fruver">
                    Ir al catálogo
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th># Pedido</th>
                            <th>Fecha</th>
                            <th>Tipo de pago</th>
                            <th>Total</th>
                            <th>Estado del pago</th>
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
                                        <span class="badge <?= esc($pedido['badge_clase'] ?? 'bg-secondary') ?>">
                                            <?= esc($pedido['estado_liquidado'] ?? 'Sin determinar') ?>
                                        </span>
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
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="mb-2">
                                        <span class="fs-2">📋</span>
                                    </div>
                                    <strong>Aún no has realizado ningún pedido.</strong>
                                    <p class="mb-0">Tus pedidos aparecerán aquí cuando estén registrados.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <footer class="fruver-footer">
            FRUVERSOFT · Gestión de pedidos
        </footer>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
