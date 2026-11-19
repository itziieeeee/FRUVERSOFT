<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedidos - Fruversoft</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('CSS/Vendedor/pedidos.css') ?>?v=4">
</head>

<body>

<div class="contenedor">

    <!-- ENCABEZADO Y BOTÓN REGRESAR -->
    <div class="encabezado-pedidos">
        <div>
            <span class="etiqueta-pedidos">FRUVERSOFT / VENDEDOR</span>
            <h1>Gestión de pedidos</h1>
            <p>Registra ventas, administra entregas y consulta tus pedidos.</p>
        </div>

        <a href="<?= base_url('vendedor/panel') ?>" class="btn-regresar">
            <i class="fas fa-arrow-left"></i>
            Regresar al panel
        </a>
    </div>

    <!-- REGISTRAR PEDIDO -->
    <div class="panel">
        <h3 class="mb-4">Registrar nuevo pedido</h3>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Cliente</label>
                <select id="cliente" class="form-select">
                    <option value="0">Público general</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= esc($cliente['id_cliente']) ?>">
                            <?= esc($cliente['nombre'] . ' ' . ($cliente['apellido_paterno'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tipo de venta</label>
                <select id="tipoVenta" class="form-select">
                    <option value="contado">Contado</option>
                    <option value="credito">Crédito</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Entrega</label>
                <select id="tipoEntrega" class="form-select">
                    <option value="tienda">En tienda</option>
                    <option value="domicilio">A domicilio</option>
                </select>
            </div>

            <div class="col-md-6" id="contenedorRepartidor" hidden>
                <label class="form-label">Repartidor</label>
                <select id="repartidor" class="form-select">
                    <option value="">Seleccionar repartidor</option>
                    <?php foreach ($repartidores as $repartidor): ?>
                        <option value="<?= esc($repartidor['id']) ?>">
                            <?= esc($repartidor['nombre'] . ' ' . ($repartidor['ap_p'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <hr class="my-4">

        <h5>Agregar productos</h5>

        <!-- Escáner: lector USB (escribe el código y da Enter) o cámara del celular -->
        <div class="barra-escaner mb-3" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; padding:10px 12px; border:2px dashed #1d4a27; border-radius:12px; background:#f4f8f2;">
            <i class="fas fa-barcode" style="font-size:1.4rem; color:#1d4a27;"></i>
            <input type="text" id="campoEscaner" class="form-control" style="flex:1; min-width:180px;"
                   placeholder="Escanea con el lector o escribe el código y presiona Enter"
                   autocomplete="off" autocapitalize="characters" inputmode="text">
            <button type="button" class="btn btn-verde" id="btnCamara">
                <i class="fas fa-camera"></i> Cámara
            </button>
        </div>
        <div id="resultadoEscaner" class="mb-3" style="display:none;"></div>

        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Producto</label>
                <select id="producto" class="form-select">
                    <option value="">Seleccionar producto</option>
                    <?php foreach ($productos as $p): ?>
                        <option
                            value="<?= esc($p['id']) ?>"
                            data-nombre="<?= esc($p['nombre']) ?>"
                            data-precio="<?= esc($p['precio_sugerido'] ?? 0) ?>"
                            data-unidad="<?= esc($p['unidad_venta'] ?? '') ?>"
                            data-stock="<?= esc($p['stock_disponible'] ?? 0) ?>">
                            <?= esc($p['nombre']) ?> (Stock: <?= esc($p['stock_disponible'] ?? 0) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Precio</label>
                <input type="text" id="precio" class="form-control" readonly>
            </div>

            <div class="col-md-2">
                <label class="form-label">Cantidad</label>
                <input type="number" id="cantidad" class="form-control" min="0.01" step="0.01" value="1">
            </div>

            <div class="col-md-3">
                <button type="button" class="btn btn-verde w-100" onclick="agregarProducto()">
                    <i class="fas fa-plus"></i> Agregar producto
                </button>
            </div>
        </div>

        <!-- TABLA DEL CARRITO -->
        <div class="table-responsive mt-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th>Unidad</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="listaProductos">
                    <tr>
                        <td colspan="6" class="text-center">Agrega productos al pedido</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <hr>

        <!-- RESUMEN DE PAGO -->
        <div class="resumen-pago">
            <div class="d-flex justify-content-between mb-3">
                <span class="fw-bold">Total del pedido:</span>
                <span class="total" id="total">$0.00</span>
            </div>

            <label class="form-label fw-bold">Método de pago</label>
            <select id="metodoPago" class="form-select mb-3">
                <option value="efectivo">Efectivo</option>
                <option value="transferencia">Transferencia</option>
                <option value="tarjeta">Tarjeta</option>
            </select>

            <label class="form-label fw-bold">¿Cuánto pagó el cliente?</label>
            <input
                type="number"
                id="montoPagado"
                class="form-control form-control-lg"
                min="0"
                step="0.01"
                value="0"
                oninput="calcularPago()"
            >

            <div class="d-flex justify-content-between mt-3">
                <span>Pago recibido:</span>
                <strong id="pagoRecibido">$0.00</strong>
            </div>

            <div class="d-flex justify-content-between mt-2">
                <span>Cambio:</span>
                <span class="cambio" id="cambio">$0.00</span>
            </div>

            <div class="d-flex justify-content-between mt-2">
                <span>Saldo pendiente:</span>
                <strong id="saldo">$0.00</strong>
            </div>
        </div>

        <button type="button" class="btn btn-verde btn-lg w-100 mt-4" onclick="enviarPedido()">
            <i class="fas fa-check-circle"></i> Registrar pedido
        </button>

        <div id="mensaje" class="mt-3"></div>
    </div>

    <!-- HISTORIAL -->
    <div class="panel">
        <h3 class="mb-4">Historial de pedidos</h3>

        <?php if (!empty($pedidos)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Repartidor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td>PED-<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td><?= esc($pedido['nombre_cliente']) ?></td>
                                <td><?= esc($pedido['fecha']) ?></td>
                                <td>$<?= number_format((float)$pedido['total'], 2) ?></td>
                                <td><?= esc($pedido['estado_actual']) ?></td>
                                <td><?= esc($pedido['nombre_repartidor']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No hay pedidos registrados.</p>
        <?php endif; ?>
    </div>

</div>

<script src="<?= base_url('js/vendor/html5-qrcode.min.js') ?>"></script>
<script src="<?= base_url('js/escaner.js') ?>?v=2"></script>
<script>
window.URL_GUARDAR_PEDIDO = "<?= site_url('vendedor/guardar-pedido') ?>";
const URL_ESCANER = "<?= site_url('vendedor/escaner') ?>";
const URL_TICKET_PDF = "<?= site_url('ticket/pdf') ?>";

/* Comparte el PDF del ticket como archivo (celular: WhatsApp, correo...). En PC abre el PDF. */
async function compartirTicketPDF(idPedido) {
    const url = URL_TICKET_PDF + '/' + idPedido;

    try {
        const respuesta = await fetch(url);
        if (!respuesta.ok) throw new Error('No se pudo obtener el ticket PDF.');

        const blob = await respuesta.blob();
        if (blob.type.includes('application/pdf')) {
            const archivo = new File(
                [blob],
                'Ticket-PED-' + String(idPedido).padStart(5, '0') + '.pdf',
                { type: 'application/pdf' }
            );

            if (navigator.canShare && navigator.canShare({ files: [archivo] })) {
                await navigator.share({ files: [archivo], text: 'Tu ticket de compra - FRUVER' });
                return;
            }
        }
    } catch (error) {
        if (error.name === 'AbortError') return;   // el usuario cerró el menú de compartir
        console.error('Error al compartir el ticket:', error);
    }

    window.open(url, '_blank');   // respaldo: abrir el PDF para verlo o descargarlo
}
let carrito = [];

/* ---------- Escáner: cada lectura agrega 1 unidad del producto al pedido ---------- */
async function agregarPorCodigo(codigo) {
    const { producto: p } = await FruverEscaner.buscar(URL_ESCANER, codigo);
    const op = [...document.getElementById('producto').options].find(o => Number(o.value) === p.id);

    if (!op) throw new Error(p.nombre + ' no está disponible para la venta.');

    const stock = Number(op.dataset.stock);
    const existente = carrito.find(x => x.id_producto === p.id);
    const nueva = (existente ? existente.cantidad : 0) + 1;

    if (nueva > stock) {
        throw new Error('No hay suficiente stock de ' + p.nombre + ' (disponible: ' + stock + ').');
    }

    if (existente) {
        existente.cantidad = nueva;
    } else {
        carrito.push({
            id_producto: p.id,
            nombre: op.dataset.nombre,
            unidad: op.dataset.unidad,
            cantidad: 1,
            precio_venta: Number(op.dataset.precio)
        });
    }
    dibujarProductos();

    const aviso = document.getElementById('resultadoEscaner');
    aviso.style.display = 'block';
    aviso.innerHTML = '<div class="alert alert-success py-2 mb-0">Agregado: <strong>' + escapar(p.nombre) +
        '</strong> (x' + nueva + ')</div>';

    return 'Agregado: ' + p.nombre + ' (x' + nueva + ')';
}

FruverEscaner.vincularCampo(document.getElementById('campoEscaner'), async codigo => {
    try {
        await agregarPorCodigo(codigo);
    } catch (e) {
        const aviso = document.getElementById('resultadoEscaner');
        aviso.style.display = 'block';
        aviso.innerHTML = '<div class="alert alert-danger py-2 mb-0">' + escapar(e.message) + '</div>';
        throw e;
    }
});

document.getElementById('btnCamara').addEventListener('click', () => {
    FruverEscaner.abrirCamara({ titulo: 'Escanear productos', onCodigo: agregarPorCodigo });
});

const formato = n => '$' + Number(n).toFixed(2);

document.getElementById('producto').addEventListener('change', function () {
    const op = this.selectedOptions[0];
    document.getElementById('precio').value =
        op.value ? formato(op.dataset.precio) : '';
});

document.getElementById('tipoEntrega').addEventListener('change', function () {
    const domicilio = this.value === 'domicilio';
    document.getElementById('contenedorRepartidor').hidden = !domicilio;
});

function agregarProducto() {
    const select = document.getElementById('producto');
    const op = select.selectedOptions[0];
    const cantidad = Number(document.getElementById('cantidad').value);

    if (!op.value || !Number.isFinite(cantidad) || cantidad <= 0) {
        alert('Selecciona un producto y una cantidad válida.');
        return;
    }

    const id = Number(op.value);
    const stock = Number(op.dataset.stock);
    const existente = carrito.find(p => p.id_producto === id);
    const nuevaCantidad = cantidad + (existente ? existente.cantidad : 0);

    if (nuevaCantidad > stock) {
        alert('No hay suficiente stock disponible.');
        return;
    }

    if (existente) {
        existente.cantidad = nuevaCantidad;
    } else {
        carrito.push({
            id_producto: id,
            nombre: op.dataset.nombre,
            unidad: op.dataset.unidad,
            cantidad: cantidad,
            precio_venta: Number(op.dataset.precio)
        });
    }

    dibujarProductos();
    select.value = '';
    document.getElementById('precio').value = '';
    document.getElementById('cantidad').value = 1;
}

function dibujarProductos() {
    const tbody = document.getElementById('listaProductos');

    if (!carrito.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Agrega productos al pedido</td></tr>';
        calcularPago();
        return;
    }

    tbody.innerHTML = carrito.map((p, i) => `
        <tr>
            <td>${escapar(p.nombre)}</td>
            <td>${escapar(p.unidad)}</td>
            <td>${formato(p.precio_venta)}</td>
            <td>${p.cantidad}</td>
            <td>${formato(p.cantidad * p.precio_venta)}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarProducto(${i})">
                    Quitar
                </button>
            </td>
        </tr>
    `).join('');

    calcularPago();
}

function escapar(texto) {
    return String(texto).replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[c]);
}

function eliminarProducto(index) {
    carrito.splice(index, 1);
    dibujarProductos();
}

function calcularPago() {
    const total = carrito.reduce(
        (s, p) => s + p.cantidad * p.precio_venta, 0
    );

    const pago = Math.max(0, Number(document.getElementById('montoPagado').value) || 0);
    const cambio = Math.max(0, pago - total);
    const saldo = Math.max(0, total - pago);

    document.getElementById('total').textContent = formato(total);
    document.getElementById('pagoRecibido').textContent = formato(pago);
    document.getElementById('cambio').textContent = formato(cambio);
    document.getElementById('saldo').textContent = formato(saldo);
}
async function enviarPedido() {
    const mensaje = document.getElementById('mensaje');
    const boton = document.querySelector('button[onclick="enviarPedido()"]');

    if (!carrito.length) {
        alert('Agrega productos al pedido.');
        return;
    }

    const tipoEntrega = document.getElementById('tipoEntrega').value;
    const repartidor = document.getElementById('repartidor').value;

    if (tipoEntrega === 'domicilio' && !repartidor) {
        alert('Selecciona un repartidor.');
        return;
    }

    const datos = {
        id_cliente: Number(document.getElementById('cliente').value),
        tipo_venta: document.getElementById('tipoVenta').value,
        tipo_entrega: tipoEntrega,
        id_repartidor: repartidor || null,
        monto_pagado: Number(document.getElementById('montoPagado').value) || 0,
        metodo_pago: document.getElementById('metodoPago').value,
        productos: carrito.map(p => ({
            id_producto: p.id_producto,
            unidad: p.unidad,
            cantidad: p.cantidad,
            precio_venta: p.precio_venta
        }))
    };

    boton.disabled = true;
    mensaje.textContent = 'Registrando pedido...';

    try {
        const respuesta = await fetch(window.URL_GUARDAR_PEDIDO, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(datos)
        });

        const texto = await respuesta.text();

        let resultado;

        try {
            resultado = JSON.parse(texto);
        } catch {
            throw new Error('El servidor no devolvió JSON. Respuesta: ' + texto.substring(0, 300));
        }

        if (!respuesta.ok || resultado.status !== 'success') {
            throw new Error(resultado.message || resultado.error || 'No se pudo guardar el pedido.');
        }

        const pedido = resultado.data;

        // El ticket es un PDF (igual que en la venta del administrador): se puede ver, compartir o mandar por WhatsApp
        const urlPdf = URL_TICKET_PDF + '/' + pedido.id_pedido;
        mensaje.innerHTML = `
            <div class="alert alert-success">
                <strong>¡Pedido registrado correctamente!</strong>
                <br>Folio: ${escapar(pedido.folio)}
                <br>Total: ${formato(pedido.total)}
                <br>Pago: ${formato(pedido.monto_pagado)}
                <br>Cambio: ${formato(pedido.cambio)}
                <br>Saldo: ${formato(pedido.saldo)}
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="${escapar(urlPdf)}" target="_blank" rel="noopener" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Ticket PDF
                    </a>
                    <button type="button" class="btn btn-verde" onclick="compartirTicketPDF(${Number(pedido.id_pedido)})">
                        <i class="fas fa-share-alt"></i> Compartir PDF
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                        <i class="fas fa-plus"></i> Nuevo pedido
                    </button>
                </div>
            </div>
        `;

        carrito = [];
        dibujarProductos();
        document.getElementById('montoPagado').value = 0;

    } catch (error) {
        mensaje.innerHTML = `
            <div class="alert alert-danger">
                Error al registrar: ${escapar(error.message)}
            </div>
        `;
    } finally {
        boton.disabled = false;
    }
}

dibujarProductos();
</script>

</body>
</html>
