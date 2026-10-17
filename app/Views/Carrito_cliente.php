<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="<?= base_url('css/productos.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/catalogo_cliente.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Mi carrito</title>
</head>
<body>

<header>
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo FRUVER">
        </div>
        <div class="user-actions">
            <a href="<?= base_url('catalogo') ?>" class="btn-user"><i class="fas fa-arrow-left"></i> Seguir comprando</a>
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
    <h4>Mi pedido</h4>

    <?php if (empty($carrito)): ?>
        <div class="empty-state">
            <p>Tu carrito está vacío. <a href="<?= base_url('catalogo') ?>">Elige tus productos</a>.</p>
        </div>
    <?php else: ?>
        <div class="tabla-scroll">
            <table class="tabla-carrito">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($carrito as $id => $item): ?>
                    <tr>
                        <td class="celda-producto">
                            <img src="<?= $item['imagen']
                                ? base_url('uploads/productos/' . $item['imagen'])
                                : 'https://placehold.co/80x60/DFF0E6/2C6E49?text=🍎' ?>" alt="">
                            <strong><?= esc($item['nombre']) ?></strong>
                        </td>
                        <td>$<?= number_format($item['precio'], 2) ?></td>
                        <td>
                            <form action="<?= base_url('carrito/actualizar/' . $id) ?>" method="post" class="form-cant">
                                <?= csrf_field() ?>
                                <input type="number" name="cantidad" value="<?= $item['cantidad'] ?>" min="0" step="0.5">
                                <button type="submit" class="btn-card btn-edit" title="Actualizar cantidad"><i class="fas fa-rotate"></i></button>
                            </form>
                        </td>
                        <td>$<?= number_format($item['subtotal'], 2) ?></td>
                        <td>
                            <form action="<?= base_url('carrito/quitar/' . $id) ?>" method="post">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-card btn-delete"><i class="fas fa-trash"></i> Quitar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="texto-der">Total a pagar:</th>
                        <th colspan="2">$<?= number_format($total, 2) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="carrito-acciones">
            <form action="<?= base_url('carrito/vaciar') ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="btn-modal btn-cancel">Vaciar carrito</button>
            </form>
            <!-- Se conecta con PedidoController en el siguiente paso -->
            <form action="<?= base_url('carrito/confirmar') ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="btn-modal btn-save"><i class="fas fa-check"></i> Confirmar pedido</button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>