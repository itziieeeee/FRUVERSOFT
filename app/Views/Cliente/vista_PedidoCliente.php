<?php
// Fecha en hora de la Ciudad de México
$local = static function (?string $fecha): string {
    if (!$fecha) { return ''; }
    return (new DateTime($fecha, new DateTimeZone(date_default_timezone_get())))
        ->setTimezone(new DateTimeZone('America/Mexico_City'))->format('d/m/Y h:i A');
};
?>
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
            <h1><i class="fas fa-box"></i> Mis pedidos e historial</h1>
            <p>Consulta todas tus compras, sus importes, el estado de sus pagos y el ticket de cada una.</p>
        </section>

        <section class="fruver-tabla-contenedor">
            <div class="p-3 p-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="h4 mb-1">Mis pedidos registrados</h2>
                    <p class="text-muted mb-0">Del más reciente al más antiguo.</p>
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
                                        <strong>PED-<?= str_pad((string) ($pedido['id'] ?? 0), 5, '0', STR_PAD_LEFT) ?></strong>
                                    </td>

                                    <td>
                                        <?= esc($local($pedido['fecha'] ?? null)) ?>
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
                                        <a href="<?= base_url('ticket/ver/' . (int) ($pedido['id'] ?? 0)) ?>"
                                           target="_blank" rel="noopener"
                                           class="btn btn-fruver btn-sm">
                                            <i class="fas fa-receipt"></i> Ver ticket
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="mb-2">
                                        <i class="fas fa-clipboard-list fs-2"></i>
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
