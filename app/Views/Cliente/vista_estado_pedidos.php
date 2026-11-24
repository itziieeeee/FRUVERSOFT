<?php
// Etiqueta y color de cada estado del pedido para el cliente
$etiquetas = [
    'Pedido'             => ['Recibido',             'bg-secondary',        'fa-receipt'],
    'Pedido confirmado'  => ['Confirmado',           'bg-info text-dark',   'fa-box-open'],
    'Pedido a crédito'   => ['Confirmado (crédito)', 'bg-info text-dark',   'fa-box-open'],
    'Pedido pagado'      => ['Confirmado y pagado',  'bg-info text-dark',   'fa-box-open'],
    'Pedido en tránsito' => ['En camino',            'bg-warning text-dark', 'fa-truck-fast'],
    'Venta confirmada'   => ['Entregado',            'bg-success',          'fa-circle-check'],
    'Pedido cancelado'   => ['Cancelado',            'bg-danger',           'fa-ban'],
];
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
    <title>Estado de Pedidos | FRUVERSOFT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('CSS/Cliente/menu.css') ?>" rel="stylesheet">
</head>

<body>

    <?= $this->include('Cliente/partials/navbar') ?>

    <main class="container py-4 py-lg-5">

        <section class="fruver-encabezado">
            <h1><i class="fas fa-truck-fast"></i> Estatus de mis pedidos</h1>
            <p>Consulta el estado de tus pedidos y sigue en el mapa los que van en camino.</p>
        </section>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <section class="fruver-tabla-contenedor">
            <div class="p-3 p-md-4">
                <h2 class="h4 mb-1">Seguimiento de mis pedidos</h2>
                <p class="text-muted mb-0">
                    Aquí aparecen tus pedidos, del más reciente al más antiguo.
                </p>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th># Pedido</th>
                            <th>Fecha</th>
                            <th>Entrega</th>
                            <th>Estatus actual</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($pedidos)): ?>
                            <?php foreach ($pedidos as $pedido): ?>
                                <?php
                                    $e = $etiquetas[$pedido['estado_actual'] ?? ''] ?? [$pedido['estado_actual'] ?? 'En proceso', 'bg-secondary', 'fa-clock'];
                                    $enCamino = ($pedido['estado_actual'] ?? '') === 'Pedido en tránsito';
                                ?>
                                <tr>
                                    <td><strong>PED-<?= str_pad((string) ($pedido['id'] ?? 0), 5, '0', STR_PAD_LEFT) ?></strong></td>
                                    <td><?= esc($local($pedido['fecha'] ?? null)) ?></td>
                                    <td><?= ($pedido['tipo_entrega'] ?? '') === 'domicilio' ? 'A domicilio' : 'En tienda' ?></td>
                                    <td>
                                        <span class="badge <?= $e[1] ?>"><i class="fas <?= $e[2] ?>"></i> <?= esc($e[0]) ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('cliente/seguimiento/' . (int) $pedido['id']) ?>" class="btn <?= $enCamino ? 'btn-fruver-naranja' : 'btn-fruver' ?> btn-sm">
                                            <i class="fas <?= $enCamino ? 'fa-location-dot' : 'fa-eye' ?>"></i>
                                            <?= $enCamino ? 'Ver en el mapa' : 'Seguir pedido' ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="mb-2">
                                        <i class="fas fa-box-open fs-2"></i>
                                    </div>
                                    <strong>Todavía no tienes pedidos.</strong>
                                    <p class="mb-0">Arma tu pedido desde el <a href="<?= base_url('catalogo') ?>">catálogo</a> y aquí podrás seguirlo.</p>
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
