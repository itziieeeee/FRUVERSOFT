<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock - Fruversoft</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('CSS/Vendedor/stock.css') ?>">
</head>
<body>

<header class="encabezado">
    <img src="<?= base_url('img/LOGO1.png') ?>" alt="FRUVER">
    <div>
        <h2>Consulta de stock</h2>
        <a href="<?= base_url('vendedor/pedidos') ?>">Regresar a pedidos</a>
    </div>
</header>

<main class="container py-4">

    <div class="filtros">
        <select id="filtroCategoria">
            <option value="todos">Todos</option>
            <option value="frutas">Frutas</option>
            <option value="verduras">Verduras</option>
            <option value="hierbas">Hierbas</option>
        </select>

        <select id="orden">
            <option value="az">A → Z</option>
            <option value="za">Z → A</option>
            <option value="mayor">Mayor stock</option>
            <option value="menor">Menor stock</option>
        </select>
    </div>

    <div class="productos" id="productos">

        <?php foreach ($productos as $p): ?>
            <article class="producto"
                data-nombre="<?= esc(strtolower($p['nombre'])) ?>"
                data-stock="<?= esc($p['stock_disponible']) ?>">

                <div class="producto-cabecera">
                    <span class="codigo">
                        PRD-<?= esc($p['codigo']) ?>
                    </span>
                </div>

                <h4><?= esc($p['nombre']) ?></h4>

                <p><?= esc($p['descripcion'] ?? '') ?></p>

                <div class="dato">
                    <span>Unidad de medida:</span>
                    <strong><?= esc($p['unidad_venta'] ?? '-') ?></strong>
                </div>

                <div class="dato">
                    <span>Existencias totales:</span>
                    <strong><?= esc($p['existencias_totales']) ?></strong>
                </div>

                <div class="dato">
                    <span>Bloqueadas:</span>
                    <strong><?= esc($p['existencias_bloqueadas']) ?></strong>
                </div>

                <div class="disponible">
                    <span>Disponibles para venta:</span>
                    <strong><?= esc($p['stock_disponible']) ?></strong>
                </div>

            </article>
        <?php endforeach; ?>

    </div>
</main>

<script>
const productos = document.getElementById('productos');
const orden = document.getElementById('orden');

function ordenarProductos() {
    const tarjetas = [...productos.children];

    tarjetas.sort((a, b) => {
        const nombreA = a.dataset.nombre;
        const nombreB = b.dataset.nombre;
        const stockA = Number(a.dataset.stock);
        const stockB = Number(b.dataset.stock);

        switch (orden.value) {
            case 'za': return nombreB.localeCompare(nombreA);
            case 'mayor': return stockB - stockA;
            case 'menor': return stockA - stockB;
            default: return nombreA.localeCompare(nombreB);
        }
    });

    tarjetas.forEach(t => productos.appendChild(t));
}

orden.addEventListener('change', ordenarProductos);
</script>

</body>
</html>
