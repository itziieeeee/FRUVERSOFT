<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fruversoft - Inventario y Stock</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('CSS/Vendedor/vendedor.css') ?>?v=3">
</head>

<body>

<header class="barra-superior">
    <div class="logo-area">
        <img src="<?= base_url('IMG/LOGO1.png') ?>" alt="FRUVERSOFT">
    </div>

    <div class="user-actions">
        <a href="#" class="btn-user">
            <i class="fas fa-user"></i> Vendedor
        </a>

        <a href="#" class="btn-user">
            <i class="fas fa-bell"></i> Notificaciones
        </a>

        <a href="<?= base_url('logout') ?>" class="btn-user btn-cerrar-sesion">
            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </a>
    </div>
</header>

<nav class="menu-navegacion">
    <a href="<?= base_url('vendedor/panel') ?>" class="nav-link activo">
        <i class="fas fa-boxes"></i>
        Inventario y Stock
    </a>

    <a href="<?= base_url('vendedor/pedidos') ?>" class="nav-link">
        <i class="fas fa-shopping-cart"></i>
        Pedidos
    </a>
</nav>

<main class="contenido-principal">

    <section class="titulo-seccion">
        <div class="titulo-texto">
            <h1>Inventario y Stock</h1>
            <p>Consulta los productos y sus existencias disponibles.</p>
        </div>
    </section>

    <section class="buscador">
        <i class="fas fa-search"></i>
        <input
            type="text"
            id="buscador"
            placeholder="Buscar producto por nombre o código..."
            onkeyup="filtrarProductos()"
        >
    </section>

    <section class="productos-grid" id="listaProductos">

        <?php if (!empty($productos)): ?>

            <?php foreach ($productos as $producto): ?>

                <?php
                    $total = (float) ($producto['existencias_totales'] ?? 0);
                    $bloqueado = (float) ($producto['existencias_bloqueadas'] ?? 0);
                    $disponible = (float) ($producto['stock_disponible'] ?? ($total - $bloqueado));
                    $precio = (float) ($producto['precio_sugerido'] ?? $producto['precio_venta'] ?? 0);
                    $imagen = trim($producto['imagen'] ?? '');
                ?>

                <article class="producto-card">

                    <div class="producto-imagen">
                        <?php if (!empty($imagen)): ?>
                            <img
                                src="<?= base_url('uploads/productos/' . $imagen) ?>"
                                alt="<?= esc($producto['nombre'] ?? 'Producto') ?>"
                                loading="lazy"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                            >
                            <i class="fas fa-box-open imagen-fallback" style="display:none;"></i>
                        <?php else: ?>
                            <i class="fas fa-box-open imagen-fallback"></i>
                        <?php endif; ?>
                    </div>

                    <div class="producto-codigo">
                        <?= esc($producto['codigo'] ?? '') ?>
                    </div>

                    <h3 class="producto-nombre">
                        <?= esc($producto['nombre'] ?? '') ?>
                    </h3>

                    <p class="producto-descripcion">
                        <?= esc($producto['descripcion'] ?? 'Sin descripción') ?>
                    </p>

                    <div class="precio-unidad">
                        <span class="precio-venta">
                            $<?= number_format($precio, 2) ?>
                        </span>

                        <span class="unidad-medida">
                            / <?= esc($producto['unidad_venta'] ?? 'unidad') ?>
                        </span>
                    </div>

                    <div class="inventario-panel">

                        <div class="inventario-item">
                            <span>Existencias totales</span>
                            <strong class="total">
                                <?= number_format($total, 2) ?>
                            </strong>
                        </div>

                        <div class="inventario-item">
                            <span>Existencias bloqueadas</span>
                            <strong>
                                <?= number_format($bloqueado, 2) ?>
                            </strong>
                        </div>

                        <div class="inventario-item">
                            <span>Stock disponible</span>
                            <strong class="<?= $disponible > 0 ? 'disponible' : 'sin-stock' ?>">
                                <?= number_format($disponible, 2) ?>
                            </strong>
                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="sin-resultados">
                <i class="fas fa-box-open"></i>
                <p>No hay productos disponibles en el inventario.</p>
            </div>

        <?php endif; ?>

    </section>

    <div class="sin-resultados" id="sinResultados" style="display:none;">
        <i class="fas fa-search"></i>
        <p>No se encontraron productos.</p>
    </div>

</main>

<script>
function filtrarProductos() {
    const busqueda = document.getElementById('buscador').value.toLowerCase().trim();
    const productos = document.querySelectorAll('.producto-card');
    const sinResultados = document.getElementById('sinResultados');

    let encontrados = 0;

    productos.forEach(producto => {
        const coincide = producto.textContent.toLowerCase().includes(busqueda);

        producto.style.display = coincide ? '' : 'none';

        if (coincide) encontrados++;
    });

    sinResultados.style.display = encontrados === 0 ? 'block' : 'none';
}
</script>

</body>
</html>
