<?php
/**
 * app/Views/Cliente/seguimiento_pedido.php
 * Seguimiento de un pedido del cliente: etapas, mapa con el repartidor y detalle.
 * Recibe: $pedido, $productos, $pasos, $datos
 */
$m     = fn($v) => '$' . number_format((float) $v, 2);
$n     = fn($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.') ?: '0';
$local = static function (?string $fecha): string {
    if (!$fecha) { return ''; }
    return (new DateTime($fecha, new DateTimeZone(date_default_timezone_get())))
        ->setTimezone(new DateTimeZone('America/Mexico_City'))->format('d/m/Y h:i A');
};

$domicilio = $datos['domicilio'];
$terminal  = $datos['cancelado'] || $datos['entregado'];
$estadoPago = $datos['saldo'] <= 0.009
    ? ['Pagado', 'bg-success']
    : ($pedido['tipo_pago'] === 'credito' ? ['A crédito', 'bg-warning text-dark'] : ['Pago pendiente', 'bg-danger']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento <?= esc($datos['folio']) ?> | FRUVERSOFT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('CSS/Cliente/menu.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .seg-card { background: #fff; border-radius: var(--fruver-radio, 12px); box-shadow: var(--fruver-sombra, 0 4px 14px rgba(29,74,39,.08)); padding: 1.4rem; margin-bottom: 1.2rem; }
        .seg-card h2 { font-size: 1.1rem; color: var(--fruver-verde, #1d4a27); margin-bottom: 1rem; display: flex; align-items: center; gap: .6rem; }
        .seg-card h2 i { color: var(--fruver-naranja, #f16b1a); }

        .linea-tiempo { display: flex; margin: .4rem 0 .2rem; }
        .paso { flex: 1; text-align: center; position: relative; padding: 0 4px; }
        .paso::before { content: ''; position: absolute; top: 26px; left: -50%; width: 100%; height: 4px; background: #e2e8f0; }
        .paso:first-child::before { display: none; }
        .paso .bola { position: relative; z-index: 1; width: 54px; height: 54px; margin: 0 auto 10px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #e2e8f0; color: #94a3b8; font-size: 1.25rem; }
        .paso .txt { font-size: .82rem; font-weight: 600; color: #94a3b8; }
        .paso.hecho::before, .paso.actual::before { background: var(--fruver-verde, #1d4a27); }
        .paso.hecho .bola { background: var(--fruver-verde, #1d4a27); color: #fff; }
        .paso.actual .bola { background: var(--fruver-naranja, #f16b1a); color: #fff; animation: pulso 1.8s infinite; }
        .paso.hecho .txt, .paso.actual .txt { color: #26352a; }
        @keyframes pulso { 0% { box-shadow: 0 0 0 0 rgba(241,107,26,.45); } 70% { box-shadow: 0 0 0 12px rgba(241,107,26,0); } 100% { box-shadow: 0 0 0 0 rgba(241,107,26,0); } }
        @media (max-width: 576px) { .paso .bola { width: 44px; height: 44px; font-size: 1rem; } .paso::before { top: 22px; } .paso .txt { font-size: .7rem; } }
        @media (prefers-reduced-motion: reduce) { .paso.actual .bola { animation: none; } }

        .estado-texto { margin-top: 1rem; padding: .8rem 1rem; border-radius: 10px; background: #f4f6f4; color: #26352a; font-size: .92rem; display: flex; gap: .6rem; align-items: center; }
        .estado-texto.ok { background: #dcfce7; color: #14532d; }
        .estado-texto.mal { background: #fee2e2; color: #991b1b; }

        #mapa { height: 340px; border-radius: 12px; z-index: 0; }
        #mapaTexto { margin-top: .8rem; font-size: .92rem; color: #26352a; }
        .pin { border: 3px solid #fff; border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,.4); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .pin-casa { background: var(--fruver-verde, #1d4a27); }
        .pin-repartidor { background: var(--fruver-naranja, #f16b1a); }

        .totales { margin-left: auto; max-width: 320px; }
        .totales .fila { display: flex; justify-content: space-between; padding: .25rem 0; }
        .totales .fila.grande { font-weight: 700; font-size: 1.1rem; border-top: 1px solid #e2e8e3; margin-top: .3rem; padding-top: .6rem; }
    </style>
</head>

<body>

    <?= $this->include('Cliente/partials/navbar') ?>

    <main class="container py-4 py-lg-5">

        <section class="fruver-encabezado">
            <h1><i class="fas fa-route"></i> Seguimiento de tu pedido <?= esc($datos['folio']) ?></h1>
            <p>Hecho el <?= esc($local($pedido['fecha'])) ?> · <?= $domicilio ? 'Entrega a domicilio' : 'Para recoger en tienda' ?></p>
        </section>

        <?php if ($datos['cancelado']): ?>
            <div class="estado-texto mal mb-3" id="estadoGeneral"><i class="fas fa-ban"></i><span>Este pedido fue cancelado. Si tienes dudas, comunícate con la tienda.</span></div>
        <?php endif; ?>

        <!-- Etapas del pedido -->
        <section class="seg-card">
            <h2><i class="fas fa-list-check"></i> Estado del pedido</h2>

            <div class="linea-tiempo" id="lineaTiempo">
                <?php foreach ($pasos as $i => $p): ?>
                    <?php
                        if ($datos['cancelado'])        { $clase = ''; }
                        elseif ($i < $datos['paso'])    { $clase = 'hecho'; }
                        elseif ($i === $datos['paso'])  { $clase = $datos['entregado'] ? 'hecho' : 'actual'; }
                        else                            { $clase = ''; }
                    ?>
                    <div class="paso <?= $clase ?>">
                        <div class="bola"><i class="fas <?= esc($p['icono']) ?>"></i></div>
                        <div class="txt"><?= esc($p['texto']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($datos['entregado']): ?>
                <div class="estado-texto ok"><i class="fas fa-circle-check"></i><span>Tu pedido fue entregado<?= !empty($pedido['entregado_at']) ? ' el ' . esc($local($pedido['entregado_at'])) : '' ?>. ¡Gracias por tu compra!</span></div>
            <?php elseif (!$datos['cancelado']): ?>
                <div class="estado-texto"><i class="fas fa-circle-info"></i><span>
                    <?php
                        if ($datos['en_camino'])            { echo 'Tu pedido ya salió a reparto.'; }
                        elseif ($datos['paso'] === 0)       { echo 'Recibimos tu pedido. En un momento lo confirmamos.'; }
                        elseif ($domicilio)                 { echo 'Tu pedido está confirmado y lo estamos preparando para enviarlo.'; }
                        else                                { echo 'Tu pedido está listo. Pasa a recogerlo y a pagarlo en tienda.'; }
                    ?>
                </span></div>
            <?php endif; ?>
        </section>

        <!-- Mapa: solo para entregas a domicilio que siguen en camino -->
        <?php if ($domicilio && !$terminal): ?>
            <section class="seg-card" id="tarjetaMapa">
                <h2><i class="fas fa-location-dot"></i> Dónde va tu pedido</h2>
                <div id="mapa" <?= empty($datos['mapa']['destino']) ? 'style="display:none"' : '' ?>></div>
                <div id="mapaTexto"></div>
            </section>
        <?php endif; ?>

        <!-- Detalle -->
        <section class="seg-card">
            <h2><i class="fas fa-basket-shopping"></i> Detalle del pedido</h2>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Producto</th><th class="text-end">Cantidad</th><th class="text-end">Precio</th><th class="text-end">Importe</th></tr></thead>
                    <tbody>
                        <?php if (!$productos): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Este pedido no tiene productos.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($productos as $p): ?>
                            <tr>
                                <td><strong><?= esc($p['nombre']) ?></strong></td>
                                <td class="text-end"><?= $n($p['cantidad']) ?> <?= esc($p['unidad_venta']) ?></td>
                                <td class="text-end"><?= $m($p['precio_venta']) ?></td>
                                <td class="text-end"><?= $m($p['subtotal']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="totales">
                <div class="fila"><span>Pagado</span><span><?= $m($pedido['monto_pagado']) ?></span></div>
                <?php if ($datos['saldo'] > 0.009): ?>
                    <div class="fila"><span>Saldo pendiente</span><span><strong><?= $m($datos['saldo']) ?></strong></span></div>
                <?php endif; ?>
                <div class="fila grande"><span>Total</span><span><?= $m($pedido['total']) ?></span></div>
                <div class="fila"><span>Estado del pago</span><span class="badge <?= $estadoPago[1] ?>"><?= $estadoPago[0] ?></span></div>
            </div>
        </section>

        <div class="d-flex gap-2 flex-wrap mb-4">
            <a href="<?= base_url('cliente/estado') ?>" class="btn btn-fruver"><i class="fas fa-arrow-left"></i> Mis pedidos</a>
            <a href="<?= base_url('ticket/ver/' . (int) $pedido['id']) ?>" class="btn btn-outline-secondary"><i class="fas fa-receipt"></i> Ver ticket</a>
        </div>

        <footer class="fruver-footer">
            FRUVERSOFT · Seguimiento de pedidos
        </footer>

    </main>

    <?php if ($domicilio && !$terminal): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    (function () {
        let datos = <?= json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const URL_DATOS = "<?= base_url('cliente/seguimiento/datos/' . (int) $pedido['id']) ?>";
        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

        const caja = document.getElementById('mapa');
        const texto = document.getElementById('mapaTexto');
        if (!window.L) { caja.style.display = 'none'; texto.textContent = 'No se pudo cargar el mapa. Revisa tu conexión.'; return; }

        const mapa = L.map('mapa', { scrollWheelZoom: false }).setView([19.0414, -98.2063], 12);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(mapa);

        const icono = (clase, html) => L.divIcon({ className: '', html: `<div class="pin ${clase}" style="width:38px;height:38px">${html}</div>`, iconSize: [38, 38], iconAnchor: [19, 19] });
        let pinCasa = null, pinRep = null, repVisto = false, ajustado = false;

        function dibujar() {
            const m = datos.mapa || {};
            const puntos = [];

            if (m.destino) {
                const p = [m.destino.lat, m.destino.lng];
                if (!pinCasa) { pinCasa = L.marker(p, { icon: icono('pin-casa', '<i class="fas fa-house"></i>') }).addTo(mapa).bindTooltip('Tu domicilio'); }
                puntos.push(p);
            }
            if (m.repartidor) {
                const p = [m.repartidor.lat, m.repartidor.lng];
                if (!pinRep) { pinRep = L.marker(p, { icon: icono('pin-repartidor', '<i class="fas fa-motorcycle"></i>'), zIndexOffset: 1000 }).addTo(mapa); }
                else { pinRep.setLatLng(p); }
                pinRep.bindTooltip(esc(m.repartidor.nombre || 'Tu repartidor'));
                puntos.push(p);
            } else if (pinRep) {
                mapa.removeLayer(pinRep); pinRep = null;
            }

            if (puntos.length) {
                caja.style.display = '';
                mapa.invalidateSize();
                // Se acomoda la vista al principio y cuando aparece el repartidor; después se respeta el zoom del cliente
                if (!ajustado || (m.repartidor && !repVisto)) {
                    mapa.fitBounds(L.latLngBounds(puntos).pad(0.35), { maxZoom: 16 });
                    ajustado = true;
                }
                repVisto = repVisto || !!m.repartidor;
            }
        }

        function pintarTexto() {
            const m = datos.mapa || {};
            let t;
            if (!datos.en_camino) {
                t = 'Cuando tu pedido salga a reparto verás aquí a tu repartidor en el mapa.';
            } else if (!m.repartidor) {
                t = 'Tu pedido va en camino. Estamos esperando la ubicación actual del repartidor...';
            } else {
                t = `<strong>${esc(m.repartidor.nombre || 'Tu repartidor')}</strong> va en camino`;
                if (m.distancia_km !== null && m.distancia_km !== undefined) {
                    t += ` · está a unos <strong>${m.distancia_km} km</strong> de tu domicilio (en línea recta)`;
                }
                t += ` · ubicación de ${m.repartidor.minutos < 1 ? 'hace menos de 1 minuto' : 'hace ' + m.repartidor.minutos + ' min'}`;
            }
            texto.innerHTML = t;
        }

        async function actualizar() {
            if (document.hidden) return;
            try {
                const r = await fetch(URL_DATOS, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (!r.ok) return;
                const j = await r.json();
                if (!j.success) return;
                // Si el pedido cambió de etapa (salió a reparto, se entregó, se canceló), se recarga la pantalla completa
                if (j.data.estado !== datos.estado) { location.reload(); return; }
                datos = j.data;
                dibujar();
                pintarTexto();
            } catch (e) { /* sin conexión: se conserva lo último mostrado */ }
        }

        dibujar();
        pintarTexto();
        setInterval(actualizar, 10000);
    })();
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
