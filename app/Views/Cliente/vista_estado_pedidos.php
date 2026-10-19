
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Pedidos | FRUVERSOFT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('CSS/Cliente/menu.css') ?>" rel="stylesheet">
</head>

<body>

    <?= $this->include('Cliente/partials/navbar') ?>

    <main class="container py-4 py-lg-5">

        <section class="fruver-encabezado">
            <h1>🚚 Estatus de pedidos activos</h1>
            <p>Consulta el estado actual de tus pedidos y revisa su seguimiento.</p>
        </section>

        <section class="fruver-tabla-contenedor">
            <div class="p-3 p-md-4">
                <h2 class="h4 mb-1">Seguimiento de mis pedidos</h2>
                <p class="text-muted mb-0">
                    Aquí puedes consultar los pedidos registrados y su estatus actual.
                </p>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th># Pedido</th>
                            <th>Fecha</th>
                            <th>Estatus actual</th>
                            <th>Acción</th>
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
                                        <span class="badge bg-info text-dark">
                                            <?= esc($pedido['status_nombre'] ?? 'En proceso') ?>
                                        </span>
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-fruver btn-sm">
                                            Rastrear / Detalle
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <div class="mb-2">
                                        <span class="fs-2">📦</span>
                                    </div>
                                    <strong>No tienes pedidos activos en seguimiento.</strong>
                                    <p class="mb-0">Tus pedidos aparecerán aquí cuando estén registrados.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <footer class="fruver-footer">
            FRUVERSOFT · Seguimiento de pedidos
        </footer>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
