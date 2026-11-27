<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="<?= base_url('css/productos.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/catalogo_cliente.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .aviso-sesion { margin: 14px 0 0; padding: 10px 14px; border-radius: 10px; background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; font-size: .9rem; }
        .aviso-sesion a { color: #c2410c; font-weight: 700; }

        .entrega-card { background: #fff; border: 1px solid #cfe3d6; border-radius: 14px; padding: 18px; margin-top: 16px; }
        .entrega-card h5 { color: #2C6E49; font-size: 1.05rem; margin: 0 0 14px; display: flex; gap: 10px; align-items: center; }
        .entrega-opciones { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .entrega-opcion { cursor: pointer; display: block; }
        .entrega-opcion input { position: absolute; opacity: 0; pointer-events: none; }
        .entrega-tarjeta { display: flex; flex-direction: column; gap: 3px; padding: 14px 16px; border: 2px solid #cfe3d6; border-radius: 12px; background: #f8fbf9; transition: border-color .2s, background .2s, box-shadow .2s; height: 100%; }
        .entrega-tarjeta i { color: #f16b1a; font-size: 1.4rem; margin-bottom: 4px; }
        .entrega-tarjeta strong { color: #1e293b; }
        .entrega-tarjeta small { color: #64748b; }
        .entrega-opcion:hover .entrega-tarjeta { border-color: #2C6E49; }
        .entrega-opcion input:focus-visible + .entrega-tarjeta { box-shadow: 0 0 0 3px rgba(44,110,73,.35); }
        .entrega-opcion input:checked + .entrega-tarjeta { border-color: #2C6E49; background: #e8f3e6; }

        #bloqueDomicilio { margin-top: 16px; padding-top: 16px; border-top: 1px dashed #cfe3d6; }
        .entrega-nota { color: #475569; font-size: .88rem; margin-bottom: 12px; }
        .entrega-nota i { color: #f16b1a; }
        .entrega-campos { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px 12px; }
        .entrega-campos .campo { display: flex; flex-direction: column; gap: 4px; font-size: .78rem; font-weight: 600; color: #475569; }
        .entrega-campos .ancho-2 { grid-column: span 2; }
        .entrega-campos input { padding: 9px 10px; border: 1px solid #cfe3d6; border-radius: 8px; font: inherit; font-size: .92rem; font-weight: 400; color: #1e293b; }
        .entrega-campos input:focus { outline: 2px solid rgba(44,110,73,.4); border-color: #2C6E49; }

        .mapa-barra { display: flex; gap: 10px; flex-wrap: wrap; margin: 14px 0 10px; }
        .btn-mapa { border: 0; background: #2C6E49; color: #fff; padding: 9px 14px; border-radius: 8px; font: inherit; font-size: .85rem; font-weight: 600; cursor: pointer; display: inline-flex; gap: 8px; align-items: center; }
        .btn-mapa:hover { background: #1d4a27; }
        .btn-mapa:disabled { opacity: .6; cursor: wait; }
        #mapaEntrega { height: 320px; border-radius: 12px; border: 1px solid #cfe3d6; z-index: 0; }
        .estado-pin { margin-top: 8px; font-size: .86rem; }
        .estado-pin.ok { color: #15803d; }
        .estado-pin.falta { color: #b45309; }

        @media (max-width: 640px) {
            .entrega-opciones { grid-template-columns: 1fr; }
            .entrega-campos { grid-template-columns: 1fr 1fr; }
            .entrega-campos .ancho-2 { grid-column: span 2; }
        }
    </style>
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

        <?php $e = $entrega ?? []; $domicilio = ($e['tipo'] ?? 'tienda') === 'domicilio'; ?>

        <?php if (empty($e['logueado'])): ?>
            <div class="aviso-sesion">
                <i class="fas fa-circle-info"></i>
                Para confirmar tu pedido necesitas <a href="<?= base_url('login') ?>">iniciar sesión</a>. Tu carrito se conserva.
            </div>
        <?php endif; ?>

        <!-- Cómo recibir el pedido: en tienda o a domicilio -->
        <form action="<?= base_url('carrito/confirmar') ?>" method="post" id="formConfirmar" novalidate>
            <?= csrf_field() ?>

            <section class="entrega-card">
                <h5><i class="fas fa-truck-fast"></i> ¿Cómo quieres recibir tu pedido?</h5>

                <div class="entrega-opciones">
                    <label class="entrega-opcion">
                        <input type="radio" name="tipo_entrega" value="tienda" <?= $domicilio ? '' : 'checked' ?>>
                        <span class="entrega-tarjeta">
                            <i class="fas fa-store"></i>
                            <strong>Recoger en tienda</strong>
                            <small>Pagas al recogerlo</small>
                        </span>
                    </label>
                    <label class="entrega-opcion">
                        <input type="radio" name="tipo_entrega" value="domicilio" <?= $domicilio ? 'checked' : '' ?>>
                        <span class="entrega-tarjeta">
                            <i class="fas fa-motorcycle"></i>
                            <strong>A domicilio</strong>
                            <small>Pagas al recibirlo (efectivo, transferencia o tarjeta)</small>
                        </span>
                    </label>
                </div>

                <div id="bloqueDomicilio" <?= $domicilio ? '' : 'hidden' ?>>
                    <p class="entrega-nota"><i class="fas fa-location-dot"></i> Dinos dónde te lo llevamos. Asignamos al repartidor con la mejor ruta y podrás seguirlo en el mapa.</p>

                    <div class="entrega-campos">
                        <label class="campo ancho-2">Calle
                            <input type="text" name="calle" maxlength="200" value="<?= esc($e['calle'] ?? '') ?>" autocomplete="address-line1">
                        </label>
                        <label class="campo">Número
                            <input type="text" name="numero" maxlength="10" value="<?= esc($e['numero'] ?? '') ?>" autocomplete="off">
                        </label>
                        <label class="campo ancho-2">Colonia
                            <input type="text" name="colonia" maxlength="100" value="<?= esc($e['colonia'] ?? '') ?>" autocomplete="off">
                        </label>
                        <label class="campo">Municipio
                            <input type="text" name="municipio" maxlength="100" value="<?= esc($e['municipio'] ?? '') ?>" autocomplete="address-level2">
                        </label>
                        <label class="campo">Estado
                            <input type="text" name="estado" maxlength="100" value="<?= esc($e['estado'] ?? '') ?>" autocomplete="address-level1">
                        </label>
                        <label class="campo">Teléfono (10 dígitos)
                            <input type="tel" name="tel" maxlength="14" inputmode="tel" value="<?= esc($e['tel'] ?? '') ?>" autocomplete="tel">
                        </label>
                    </div>

                    <div class="mapa-barra">
                        <button type="button" class="btn-mapa" id="btnUbicarDireccion"><i class="fas fa-magnifying-glass-location"></i> Ubicar mi dirección en el mapa</button>
                        <button type="button" class="btn-mapa" id="btnMiUbicacion"><i class="fas fa-crosshairs"></i> Usar mi ubicación actual</button>
                    </div>

                    <div id="mapaEntrega"></div>
                    <input type="hidden" name="lat" value="<?= esc($e['lat'] ?? '') ?>">
                    <input type="hidden" name="lng" value="<?= esc($e['lng'] ?? '') ?>">
                    <p id="estadoPin" class="estado-pin"></p>
                </div>
            </section>
        </form>

        <div class="carrito-acciones">
            <form action="<?= base_url('carrito/vaciar') ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="btn-modal btn-cancel">Vaciar carrito</button>
            </form>
            <button type="submit" form="formConfirmar" class="btn-modal btn-save"><i class="fas fa-check"></i> Confirmar pedido</button>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($carrito)): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    const form = document.getElementById('formConfirmar');
    if (!form) return;

    const CENTRO = <?= json_encode($e['centro'] ?? [19.0414, -98.2063]) ?>;
    const radios  = form.querySelectorAll('input[name="tipo_entrega"]');
    const bloque  = document.getElementById('bloqueDomicilio');
    const estado  = document.getElementById('estadoPin');
    const campos  = ['calle', 'numero', 'colonia', 'estado', 'tel'].map(n => form.elements[n]);
    const lat = form.elements['lat'], lng = form.elements['lng'];

    let mapa = null, pin = null;

    const esDomicilio = () => form.elements['tipo_entrega'].value === 'domicilio';

    function mensajePin(texto, ok) {
        estado.textContent = texto;
        estado.className = 'estado-pin ' + (ok ? 'ok' : 'falta');
    }

    function ponerPin(punto, centrar) {
        lat.value = Number(punto.lat).toFixed(7);
        lng.value = Number(punto.lng).toFixed(7);
        if (!pin) {
            pin = L.marker(punto, { draggable: true }).addTo(mapa);
            pin.on('dragend', () => ponerPin(pin.getLatLng(), false));
        } else {
            pin.setLatLng(punto);
        }
        if (centrar) mapa.setView(punto, 16);
        mensajePin('Punto marcado. Si no es exacto, arrastra el pin o toca otro lugar del mapa.', true);
    }

    function iniciarMapa() {
        if (mapa) return;
        if (!window.L) { mensajePin('No se pudo cargar el mapa. Revisa tu conexión e intenta de nuevo.', false); return; }

        const hayPin = lat.value !== '' && lng.value !== '' && !isNaN(+lat.value) && !isNaN(+lng.value);
        mapa = L.map('mapaEntrega').setView(hayPin ? [+lat.value, +lng.value] : CENTRO, hayPin ? 16 : 12);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(mapa);
        mapa.on('click', ev => ponerPin(ev.latlng, false));

        if (hayPin) ponerPin({ lat: +lat.value, lng: +lng.value }, false);
        else mensajePin('Toca el mapa para marcar dónde te entregamos, o usa uno de los botones de arriba.', false);
    }

    function actualizar() {
        const dom = esDomicilio();
        bloque.hidden = !dom;
        campos.forEach(c => { if (c) c.required = dom; });
        if (dom) {
            iniciarMapa();
            setTimeout(() => mapa && mapa.invalidateSize(), 60);   // el mapa estaba oculto: recalcula su tamaño
        }
    }

    radios.forEach(r => r.addEventListener('change', actualizar));

    // Busca la dirección escrita con el servicio de OpenStreetMap
    document.getElementById('btnUbicarDireccion').addEventListener('click', async function () {
        const v = n => (form.elements[n].value || '').trim();
        if (!v('calle') || !v('colonia')) { mensajePin('Escribe al menos la calle y la colonia para buscarla.', false); return; }
        if (!mapa) return;

        const boton = this;
        boton.disabled = true;
        mensajePin('Buscando tu dirección...', false);
        try {
            const consulta = [v('calle') + ' ' + v('numero'), v('colonia'), v('municipio'), v('estado'), 'México'].filter(Boolean).join(', ');
            const r = await fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=mx&q=' + encodeURIComponent(consulta));
            const datos = await r.json();
            if (datos && datos.length) ponerPin({ lat: +datos[0].lat, lng: +datos[0].lon }, true);
            else mensajePin('No encontramos esa dirección. Toca el mapa para marcar el punto a mano.', false);
        } catch (err) {
            mensajePin('No se pudo buscar la dirección. Toca el mapa para marcar el punto a mano.', false);
        } finally {
            boton.disabled = false;
        }
    });

    // Usa el GPS del dispositivo (solo funciona con https o en localhost)
    document.getElementById('btnMiUbicacion').addEventListener('click', function () {
        if (!mapa) return;
        if (!navigator.geolocation || !window.isSecureContext) { mensajePin('Tu navegador no permite usar la ubicación aquí. Toca el mapa para marcar el punto.', false); return; }
        mensajePin('Obteniendo tu ubicación...', false);
        navigator.geolocation.getCurrentPosition(
            p => ponerPin({ lat: p.coords.latitude, lng: p.coords.longitude }, true),
            () => mensajePin('No pudimos obtener tu ubicación. Toca el mapa para marcar el punto.', false),
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });

    // No se envía una entrega a domicilio sin los datos que necesita el repartidor
    form.addEventListener('submit', ev => {
        if (!esDomicilio()) return;
        const faltante = campos.find(c => c && !c.value.trim());
        if (faltante) { ev.preventDefault(); faltante.focus(); mensajePin('Completa tu dirección y tu teléfono.', false); return; }
        if (!lat.value || !lng.value) { ev.preventDefault(); mensajePin('Marca en el mapa el punto exacto de tu domicilio.', false); document.getElementById('mapaEntrega').scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    });

    actualizar();
})();
</script>
<?php endif; ?>

</body>
</html>