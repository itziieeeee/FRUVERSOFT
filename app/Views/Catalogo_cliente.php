<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="<?= base_url('css/productos.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/catalogo_cliente.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Haz tu pedido</title>
</head>
<body>

<header>
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo FRUVER">
        </div>
        <div class="buscador">
            <form method="GET">
                <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="Buscar frutas y verduras...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="user-actions">
            <a href="<?= base_url('carrito') ?>" class="btn-user btn-carrito">
                <i class="fas fa-shopping-cart"></i> Carrito
                <span class="badge-carrito"><?= (int) $totalItems ?></span>
            </a>
            <a href="<?= esc($urlRegresar) ?>" class="btn-user"><i class="fas fa-sign-out-alt"></i> Regresar</a>
        </div>
    </div>
</header>

<div class="alert">
    <?php if (session()->getFlashdata('mensaje')): ?>
        <div class="alert-msg alert-success"><i class="fas fa-check-circle"></i> <?= esc(session()->getFlashdata('mensaje')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert-msg alert-error"><i class="fas fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
</div>

<div class="catalogo-container">
    <h4>¿Qué te llevas hoy?</h4>

    <div class="cards-grid">
        <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $prod): ?>
                <?php
                    $stock  = (float) $prod['disponible'];            // (e_total - e_bloqueo) - carrito
                    $agotado = (float) $prod['stock_disponible'] <= 0;
                    $unidad = $prod['unidad_venta'] ?? 'pieza';
                    $paso   = $unidad === 'kilo' ? '0.5' : '1';
                ?>
                <div class="product-card">
                    <div class="card-img">
                        <img src="<?= $prod['imagen']
                            ? base_url('uploads/productos/' . $prod['imagen'])
                            : 'https://placehold.co/400x300/DFF0E6/2C6E49?text=🍎+FRUVER' ?>"
                             alt="<?= esc($prod['nombre']) ?>" loading="lazy">
                    </div>
                    <div class="card-info">
                        <div class="product-name"><?= esc($prod['nombre']) ?></div>
                        <div class="product-desc"><?= esc($prod['descripcion']) ?></div>
                        <div class="product-price">$<?= number_format((float) ($prod['precio'] ?? 0), 2) ?> <small>/ <?= esc($unidad) ?></small></div>
                        <div class="product-stock <?= $agotado ? 'agotado' : '' ?>">
                            <?php if ($agotado): ?>
                                Agotado
                            <?php elseif ($stock <= 0): ?>
                                Ya tienes todo el stock en tu carrito
                            <?php else: ?>
                                Disponible: <?= $stock + 0 ?> <?= esc($unidad) ?>
                                <?php if ($prod['en_carrito'] > 0): ?>
                                    (<?= $prod['en_carrito'] + 0 ?> en tu carrito)
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <form class="card-actions form-agregar" action="<?= base_url('carrito/agregar') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $prod['id'] ?>">
                        <input type="number" name="cantidad" value="1" min="<?= $paso ?>" step="<?= $paso ?>"
                               max="<?= $stock ?>" <?= $stock <= 0 ? 'disabled' : '' ?>>
                        <button type="submit" class="btn-card btn-add" <?= $stock <= 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-cart-plus"></i> Agregar
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" style="grid-column: 1/-1;">
                <p>No encontramos productos con esa búsqueda.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="pagination-wrapper">
        <?php if (isset($pager)): ?>
            <?= $pager->links('default', 'mi_paginacion') ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>