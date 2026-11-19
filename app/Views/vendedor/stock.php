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

    <!-- Escáner: lector USB (escribe el código y da Enter) o cámara del celular -->
    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; padding:10px 12px; border:2px dashed #1d4a27; border-radius:12px; background:#f4f8f2; margin-bottom:14px;">
        <strong style="color:#1d4a27;">Escanear producto</strong>
        <input type="text" id="campoEscaner" class="form-control" style="flex:1; min-width:180px;"
               placeholder="Escanea con el lector o escribe el código y presiona Enter"
               autocomplete="off" autocapitalize="characters" inputmode="text">
        <button type="button" class="btn btn-success" id="btnCamara">Cámara</button>
    </div>
    <div id="resultadoEscaner" style="display:none; margin-bottom:14px;"></div>

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
                id="producto-<?= esc($p['id']) ?>"
                data-nombre="<?= esc(strtolower($p['nombre'])) ?>"
                data-stock="<?= esc($p['stock_disponible']) ?>">

                <div class="producto-cabecera">
                    <span class="codigo">
                        <?= esc($p['codigo']) ?>
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

<script src="<?= base_url('js/vendor/html5-qrcode.min.js') ?>"></script>
<script src="<?= base_url('js/escaner.js') ?>?v=2"></script>
<script>
const URL_ESCANER = "<?= site_url('vendedor/escaner') ?>";

const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[c]);

/* Muestra la existencia del producto escaneado y lo resalta en la lista */
async function consultarCodigo(codigo) {
    const caja = document.getElementById('resultadoEscaner');
    try {
        const { producto: p } = await FruverEscaner.buscar(URL_ESCANER, codigo);
        const sinStock = p.disponible <= 0;
        caja.style.display = 'block';
        caja.innerHTML =
            '<div class="alert ' + (sinStock ? 'alert-warning' : 'alert-success') + ' mb-0">' +
            '<strong>' + esc(p.nombre) + '</strong> <small>(' + esc(p.codigo) + ')</small><br>' +
            'Disponible: <strong>' + p.disponible + ' ' + esc(p.unidad || '') + '</strong>' +
            (p.bloqueadas > 0 ? ' · Bloqueadas: ' + p.bloqueadas : '') +
            (p.precio !== null ? ' · Precio: $' + Number(p.precio).toFixed(2) : '') +
            (sinStock ? '<br>Sin existencias disponibles.' : '') + '</div>';

        const tarjeta = document.getElementById('producto-' + p.id);
        if (tarjeta) {
            document.querySelectorAll('.producto.resaltado').forEach(t => t.classList.remove('resaltado'));
            tarjeta.classList.add('resaltado');
            tarjeta.style.outline = '3px solid #f16e1f';
            setTimeout(() => { tarjeta.style.outline = ''; }, 4000);
            tarjeta.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return p.nombre + ': ' + p.disponible + ' ' + (p.unidad || '') + ' disponibles';
    } catch (e) {
        caja.style.display = 'block';
        caja.innerHTML = '<div class="alert alert-danger mb-0">' + esc(e.message) + '</div>';
        throw e;
    }
}

FruverEscaner.vincularCampo(document.getElementById('campoEscaner'), consultarCodigo);
document.getElementById('btnCamara').addEventListener('click', () => {
    FruverEscaner.abrirCamara({ titulo: 'Consultar stock', onCodigo: consultarCodigo });
});

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
