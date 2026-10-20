<?php
$paginaActual = trim(uri_string(), '/');

$modulos = [
    ['ruta' => 'cliente/menu', 'texto' => 'Inicio'],
    ['ruta' => 'pedidos', 'texto' => 'Mis pedidos'],
    ['ruta' => 'cliente/historial', 'texto' => 'Historial'],
    ['ruta' => 'cliente/creditos', 'texto' => 'Créditos y pagos'],
    ['ruta' => 'cliente/estado', 'texto' => 'Estado'],
    ['ruta' => 'catalogo', 'texto' => 'Catálogo'],
];
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-fruver">
    <div class="container-fluid px-lg-5">

        <a class="navbar-brand" href="<?= base_url('cliente/menu') ?>">
            🥬 FRUVERSOFT
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal"
                aria-expanded="false"
                aria-label="Abrir navegación">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav mx-auto">
                <?php foreach ($modulos as $modulo): ?>
                    <?php $activo = $paginaActual === $modulo['ruta']; ?>

                    <li class="nav-item">
                        <a class="nav-link <?= $activo ? 'active' : '' ?>"
                           href="<?= base_url($modulo['ruta']) ?>"
                           <?= $activo ? 'aria-current="page"' : '' ?>>
                            <?= esc($modulo['texto']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Botón de Cerrar Sesión a la derecha -->
            <div class="d-flex align-items-center mt-3 mt-lg-0">
                <a href="<?= base_url('logout') ?>" class="btn btn-outline-light btn-sm px-3 rounded-pill">
                    <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
                </a>
            </div>
        </div>

    </div>
</nav>
