<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FRUVERSOFT | Menú del cliente</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- CSS propio de FRUVERSOFT -->
    <link href="<?= base_url('CSS/Cliente/menu.css') ?>"
          rel="stylesheet">
</head>

<body>

<?= $this->include('Cliente/partials/navbar') ?>

<main class="container py-4 py-lg-5">
    
    <?php if (!empty($avisos)): ?>
        <div class="mb-4">
            <?php foreach ($avisos as $aviso): ?>
                <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 mb-3 position-relative" style="background-color: #fff3cd; border-left: 5px solid #f16b1a !important;">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-warning text-dark me-2"><i class="fas fa-bullhorn"></i> Aviso Importante</span>
                        <small class="text-muted"><?= date('d/m/Y', strtotime($aviso['created_at'])) ?></small>
                    </div>
                    <h5 class="fw-bold text-dark mt-2 mb-1"><?= esc($aviso['titulo']) ?></h5>
                    <p class="mb-0 text-secondary"><?= nl2br(esc($aviso['mensaje'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
     
    <!-- Bienvenida principal -->
    <section class="fruver-encabezado p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge text-bg-success mb-3">
                    PORTAL DEL CLIENTE
                </span>

                <h1 class="display-5 fw-bold">
                    ¡Bienvenido a FRUVERSOFT!
                </h1>

                <p class="fs-5 mt-3">
                    Consulta tus pedidos, revisa tus pagos
                    y mantente al tanto de tus compras desde
                    un solo lugar.
                </p>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a href="<?= base_url('pedidos') ?>"
                       class="btn btn-fruver">
                        <i class="fas fa-box"></i>
                        Mis pedidos
                    </a>

                    <a href="<?= base_url('catalogo') ?>"
                       class="btn btn-fruver-naranja">
                        Explorar catálogo
                    </a>
                </div>
            </div>

            <div class="col-lg-4 text-center">
                <div style="font-size: 100px; line-height: 1.2;">
                    🥬
                </div>
                <h4 class="mt-2">Frescura y confianza</h4>
                <p class="text-muted mb-0">
                    Todo lo que necesitas para consultar tus compras.
                </p>
            </div>
        </div>
    </section>

    <!-- Accesos rápidos -->
    <div class="d-flex align-items-center justify-content-between
                flex-wrap gap-2 mt-5 mb-3">
        <div>
            <h2 class="h3 fw-bold mb-1">¿Qué deseas consultar?</h2>
            <p class="text-muted mb-0">
                Accede rápidamente a tus módulos.
            </p>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-md-6 col-lg-3">
            <div class="fruver-card">
                <div class="fs-1 mb-3">📦</div>
                <h4>Mis pedidos</h4>
                <p class="text-muted">
                    Consulta tus pedidos y sus detalles.
                </p>
                <a href="<?= base_url('pedidos') ?>"
                   class="btn btn-fruver w-100 mt-2">
                    Consultar pedidos
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="fruver-card">
                <div class="fs-1 mb-3">💳</div>
                <h4>Créditos y pagos</h4>
                <p class="text-muted">
                    Revisa tus pagos, saldos y liquidaciones.
                </p>
                <a href="<?= base_url('cliente/creditos') ?>"
                   class="btn btn-fruver w-100 mt-2">
                    Revisar pagos
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="fruver-card">
                <div class="fs-1 mb-3">🧾</div>
                <h4>Historial</h4>
                <p class="text-muted">
                    Consulta tus compras anteriores.
                </p>
                <a href="<?= base_url('cliente/historial') ?>"
                   class="btn btn-fruver w-100 mt-2">
                    Ver historial
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="fruver-card">
                <div class="fs-1 mb-3">🚚</div>
                <h4>Estado de pedidos</h4>
                <p class="text-muted">
                    Consulta el avance de tus pedidos.
                </p>
                <a href="<?= base_url('cliente/estado') ?>"
                   class="btn btn-fruver w-100 mt-2">
                    Ver estado
                </a>
            </div>
        </div>

    </div>

    <!-- Pie de página -->
    <footer class="fruver-footer">
        <strong>FRUVERSOFT</strong>
        <p class="mb-0">
            Tu información de compras, organizada en un solo lugar.
        </p>
    </footer>

</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>