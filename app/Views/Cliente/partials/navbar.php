<?php
    // Detecta la página actual para marcar el enlace activo
    $rutaActual = trim(uri_string(), '/');

    $enlaces = [
        ['ruta' => 'cliente/menu',      'icono' => 'fas fa-house',            'texto' => 'Inicio'],
        ['ruta' => 'pedidos',           'icono' => 'fas fa-box',              'texto' => 'Mis pedidos'],
        ['ruta' => 'cliente/historial', 'icono' => 'fas fa-receipt',          'texto' => 'Historial'],
        ['ruta' => 'cliente/creditos',  'icono' => 'fas fa-credit-card',      'texto' => 'Créditos y pagos'],
        ['ruta' => 'cliente/estado',    'icono' => 'fas fa-truck',            'texto' => 'Estado'],
        ['ruta' => 'catalogo',          'icono' => 'fa-solid fa-apple-whole', 'texto' => 'Catálogo'],
    ];
?>
<!-- Font Awesome (íconos de la barra y el menú) -->
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<header>
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo FRUVERSOFT">
        </div>
        <div class="user-actions">
            <a href="#" class="btn-user"><i class="fas fa-user"></i> Cliente</a>
            <a href="<?= base_url('logout') ?>" class="btn-user"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </div>
</header>

<nav class="menu-navegacion">
    <div class="nav-links">
        <?php foreach ($enlaces as $e): ?>
            <a href="<?= base_url($e['ruta']) ?>"
               class="nav-link <?= ($rutaActual === $e['ruta']) ? 'activo' : '' ?>">
                <i class="<?= $e['icono'] ?>"></i> <?= $e['texto'] ?>
            </a>
        <?php endforeach; ?>
    </div>
</nav>