<?php
/**
 * app/Views/seguimiento_repartidor.php
 * Mapa en vivo: repartidor en movimiento, pines de clientes y alerta "ya está cerca".
 * Los datos llegan por JSON desde seguimiento/datos (se actualiza solo).
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FRUVER · Seguimiento repartidor</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    :root { --verde: #1d4a27; --naranja: #f16e1f; --claro: #e8f3e6; --borde: #e0e0e0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', 'Segoe UI', sans-serif; background: #f5f7fa; color: #1e293b; padding-bottom: 40px; }

    .barra-superior { display: flex; align-items: center; justify-content: space-between; padding: .6rem 2rem; background: linear-gradient(90deg, var(--verde) 0%, #2a5e35 100%); gap: 1.5rem; flex-wrap: wrap; }
    .barra-superior img { height: 70px; object-fit: contain; }
    .btn-user { color: #fff; text-decoration: none; font-size: .85rem; padding: .5rem 1.2rem; background: rgba(255,255,255,.15); border-radius: 50px; display: inline-flex; align-items: center; gap: 8px; }
    .menu-navegacion { background: #fff; padding: .8rem 2rem; box-shadow: 0 2px 8px rgba(0,0,0,.05); border-bottom: 1px solid var(--borde); }
    .nav-links { display: flex; flex-wrap: wrap; gap: .4rem; justify-content: center; }
    .nav-link { padding: .6rem 1.1rem; color: #333; text-decoration: none; font-weight: 600; border-radius: 40px; font-size: .9rem; }
    .nav-link:hover { background: #fef3ed; }
    .nav-link.activo { color: var(--naranja); background: rgba(241,110,31,.1); }

    .contenedor { max-width: 1250px; margin: 1.4rem auto; padding: 0 1rem; }
    .titulo { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .6rem; margin-bottom: .8rem; }
    .titulo h2 { color: var(--verde); font-size: 1.4rem; }
    .estado-act { color: #64748b; font-size: .82rem; }
    .estado-act .punto { display: inline-block; width: 9px; height: 9px; border-radius: 50%; background: #22c55e; margin-right: 5px; }
    .estado-act .punto.error { background: #ef4444; }

    .disposicion { display: grid; grid-template-columns: 1fr 340px; gap: 1rem; }
    @media (max-width: 900px) { .disposicion { grid-template-columns: 1fr; } }
    .tarjeta { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: 1rem; margin-bottom: 1rem; }
    .tarjeta h3 { color: var(--verde); font-size: 1rem; margin-bottom: .7rem; display: flex; align-items: center; gap: 8px; }
    #mapa { height: 560px; border-radius: 12px; z-index: 0; }
    .vacio { text-align: center; color: #94a3b8; padding: 1.4rem; font-size: .9rem; }

    .alerta { border: 2px solid var(--naranja); background: #fff7f1; border-radius: 12px; padding: .75rem .9rem; margin-bottom: .7rem; }
    .alerta strong { color: #c2410c; }
    .alerta .det { font-size: .82rem; color: #64748b; margin: 3px 0 8px; }
    .btn { border: 0; background: #16a34a; color: #fff; padding: 8px 14px; border-radius: 10px; font-weight: 700; cursor: pointer; font-family: inherit; font-size: .85rem; display: inline-flex; align-items: center; gap: 7px; }
    .btn:disabled { background: #cbd5e1; cursor: not-allowed; }
    .btn.sec { background: #e2e8f0; color: #334155; }

    .salida { border: 1px solid var(--borde); border-radius: 12px; padding: .7rem .8rem; margin-bottom: .7rem; }
    .salida .rep { font-weight: 700; color: var(--verde); }
    .salida .sub { font-size: .78rem; color: #64748b; margin-bottom: .4rem; }
    .parada { display: flex; align-items: center; gap: 8px; padding: 5px 0; border-top: 1px solid #eef2f6; font-size: .83rem; }
    .parada .n { flex: 0 0 22px; height: 22px; border-radius: 50%; background: var(--verde); color: #fff; font-size: .72rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .parada .n.ok { background: #94a3b8; }
    .parada .info { flex: 1; min-width: 0; }
    .parada .info small { color: #64748b; display: block; }
    .tag { font-size: .68rem; font-weight: 700; padding: 2px 8px; border-radius: 50px; background: #dcfce7; color: #15803d; white-space: nowrap; }
    .tag.cerca { background: #ffedd5; color: #c2410c; }
    .tag.urgente { background: #dc2626; color: #fff; }

    .pin-repartidor { background: var(--naranja); border: 3px solid #fff; border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,.4); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 15px; }
    .pin-cliente { background: var(--verde); border: 2px solid #fff; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,.35); color: #fff; font-weight: 700; font-size: 12px; display: flex; align-items: center; justify-content: center; }
    .pin-cliente.ok { background: #94a3b8; }
    .pin-local { background: #1e293b; border: 2px solid #fff; border-radius: 6px; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; }
    #toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: var(--verde); color: #fff; padding: 11px 20px; border-radius: 10px; font-weight: 600; display: none; z-index: 2000; }
</style>
</head>
<body>

<header class="barra-superior">
    <img src="<?= base_url('img/LOGO1.png') ?>" alt="FRUVER">
    <a href="<?= site_url('menusolo') ?>" class="btn-user"><i class="fas fa-arrow-left"></i> Regresar</a>
</header>

<nav class="menu-navegacion">
    <div class="nav-links">
        <a href="<?= base_url('pantalla_ventas') ?>" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="<?= base_url('pantalla_pedidos') ?>" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="<?= base_url('salidas') ?>" class="nav-link"><i class="fas fa-route"></i> Salidas</a>
        <a href="<?= base_url('seguimiento') ?>" class="nav-link activo"><i class="fas fa-location-dot"></i> Seguimiento</a>
        <a href="<?= base_url('pantalla_repartidores') ?>" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
    </div>
</nav>

<div class="contenedor">
    <div class="titulo">
        <h2><i class="fas fa-location-dot" style="color:var(--naranja)"></i> Seguimiento del repartidor</h2>
        <div class="estado-act"><span class="punto" id="punto"></span><span id="txtAct">Conectando…</span></div>
    </div>

    <div class="disposicion">
        <div class="tarjeta" style="padding:.6rem;">
            <div id="mapa"></div>
        </div>

        <div>
            <div class="tarjeta" id="cajaAlertas" style="display:none;">
                <h3><i class="fas fa-bell" style="color:var(--naranja)"></i> Ya está cerca</h3>
                <div id="listaAlertas"></div>
            </div>
            <div class="tarjeta">
                <h3><i class="fas fa-route"></i> Salidas en curso</h3>
                <div id="listaSalidas"><div class="vacio">Cargando…</div></div>
            </div>
        </div>
    </div>
</div>

<div id="toast"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const URL_DATOS   = '<?= base_url('seguimiento/datos') ?>';
const URL_AVISADO = '<?= base_url('seguimiento/avisado') ?>';
const URL_DETALLE = '<?= base_url('salidas/detalle') ?>';
const CADA_MS     = 10000;

const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
function aviso(t) { const e = document.getElementById('toast'); e.textContent = t; e.style.display = 'block'; setTimeout(() => e.style.display = 'none', 2600); }

const mapa = L.map('mapa').setView([19.0414, -98.2063], 11);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, attribution: '&copy; OpenStreetMap'
}).addTo(mapa);

const icono = (clase, html, tam) => L.divIcon({ className: '', html: `<div class="${clase}" style="width:${tam}px;height:${tam}px">${html}</div>`, iconSize: [tam, tam], iconAnchor: [tam / 2, tam / 2] });

const capa = L.layerGroup().addTo(mapa);
const marcasRepartidor = {};   // id salida -> marcador (se mueve sin recrearse)
const trazos = {};             // id salida -> polilínea
let primeraVez = true;

async function cargarTrazo(idSalida) {
    if (trazos[idSalida] !== undefined) return;
    trazos[idSalida] = null;
    try {
        const d = await (await fetch(URL_DETALLE + '/' + idSalida)).json();
        const g = d.success && d.data ? d.data.geometria : null;
        if (g && g.length) trazos[idSalida] = L.polyline(g, { color: '#1d4a27', weight: 4, opacity: .55 }).addTo(mapa);
    } catch (e) { /* sin trazo, no pasa nada */ }
}

function dibujar(datos) {
    // Pines de clientes y local: se redibujan; el repartidor se mueve
    capa.clearLayers();
    const puntos = [];
    const vivas = new Set();

    datos.salidas.forEach(s => {
        vivas.add(String(s.id));
        cargarTrazo(s.id);

        if (s.local_lat && s.local_lng) {
            L.marker([+s.local_lat, +s.local_lng], { icon: icono('pin-local', '<i class="fas fa-store"></i>', 26) })
                .bindTooltip('Local').addTo(capa);
        }

        s.paradas.forEach(p => {
            if (p.lat === null || p.lng === null) return;
            const ok = p.estado === 'entregado';
            const m = L.marker([+p.lat, +p.lng], { icon: icono('pin-cliente' + (ok ? ' ok' : ''), ok ? '<i class="fas fa-check"></i>' : esc(p.orden), 28) })
                .bindTooltip(esc(p.cliente || 'Cliente') + (ok ? ' (entregado)' : ''))
                .addTo(capa);
            if (!ok) puntos.push([+p.lat, +p.lng]);
        });

        if (s.ultima_lat !== null && s.ultima_lng !== null) {
            const pos = [+s.ultima_lat, +s.ultima_lng];
            puntos.push(pos);
            let mk = marcasRepartidor[s.id];
            if (!mk) {
                mk = L.marker(pos, { icon: icono('pin-repartidor', '<i class="fas fa-motorcycle"></i>', 34), zIndexOffset: 1000 }).addTo(mapa);
                marcasRepartidor[s.id] = mk;
            } else {
                mk.setLatLng(pos);
            }
            mk.bindTooltip(esc(s.repartidor) + (s.ultima_minutos !== null ? ' · hace ' + s.ultima_minutos + ' min' : ''));
        }
    });

    // Quita marcadores de salidas que ya terminaron
    Object.keys(marcasRepartidor).forEach(id => {
        if (!vivas.has(id)) { mapa.removeLayer(marcasRepartidor[id]); delete marcasRepartidor[id]; }
    });

    if (primeraVez && puntos.length) {
        mapa.fitBounds(L.latLngBounds(puntos).pad(0.2), { maxZoom: 16 });
        primeraVez = false;
    }
}

function listarSalidas(datos) {
    const cont = document.getElementById('listaSalidas');
    if (!datos.salidas.length) {
        cont.innerHTML = '<div class="vacio">No hay repartidores en ruta ahora.</div>';
        return;
    }
    cont.innerHTML = datos.salidas.map(s => {
        const ubic = s.ultima_minutos === null ? 'Sin ubicación todavía'
            : (s.posicion_vigente ? 'Ubicación hace ' + s.ultima_minutos + ' min' : 'Ubicación desactualizada (' + s.ultima_minutos + ' min)');
        const paradas = s.paradas.map(p => {
            const ok = p.estado === 'entregado';
            let etiqueta = '';
            if (ok) etiqueta = '<span class="tag">entregado</span>';
            else if (p.distancia_km !== null) {
                const cerca = p.distancia_km * 1000 <= datos.radio_m;
                etiqueta = '<span class="tag ' + (cerca ? 'cerca' : '') + '">' + (p.distancia_km < 1 ? Math.round(p.distancia_km * 1000) + ' m' : p.distancia_km.toFixed(1) + ' km') + '</span>';
            }
            const urg = (Number(p.prioritario) === 1 && !ok) ? ' <span class="tag urgente">URGENTE</span>' : '';
            return '<div class="parada"><div class="n ' + (ok ? 'ok' : '') + '">' + esc(p.orden) + '</div>'
                 + '<div class="info">' + esc(p.cliente || 'Cliente') + urg + '<small>' + esc(p.direccion || '') + '</small></div>' + etiqueta + '</div>';
        }).join('');
        return '<div class="salida"><div class="rep"><i class="fas fa-motorcycle"></i> ' + esc(s.repartidor) + '</div>'
             + '<div class="sub">' + esc(s.entregadas) + ' de ' + esc(s.total_paradas) + ' entregas · ' + esc(ubic) + '</div>' + paradas + '</div>';
    }).join('');
}

function listarAlertas(datos) {
    const caja = document.getElementById('cajaAlertas');
    const lista = document.getElementById('listaAlertas');
    caja.style.display = datos.alertas.length ? 'block' : 'none';
    lista.innerHTML = datos.alertas.map(a =>
        '<div class="alerta"><strong>' + esc(a.cliente || 'Cliente') + '</strong> · PED-' + String(a.id_pedido).padStart(5, '0')
        + '<div class="det">' + esc(a.repartidor) + ' está a ' + esc(a.distancia_m) + ' m</div>'
        + (a.whatsapp_url
            ? '<button class="btn" data-pedido="' + esc(a.id_pedido) + '" data-url="' + esc(a.whatsapp_url) + '"><i class="fab fa-whatsapp"></i> Avisar por WhatsApp</button> '
            : '<button class="btn" disabled title="El cliente no tiene teléfono"><i class="fab fa-whatsapp"></i> Sin teléfono</button> ')
        + '<button class="btn sec" data-descartar="' + esc(a.id_pedido) + '">Ya avisé / omitir</button></div>'
    ).join('');
}

async function marcarAvisado(idPedido) {
    try { await fetch(URL_AVISADO + '/' + idPedido, { method: 'POST' }); } catch (e) { /* se reintenta al volver a mostrar la alerta */ }
    actualizar();
}

document.getElementById('listaAlertas').addEventListener('click', e => {
    const bAviso = e.target.closest('button[data-url]');
    if (bAviso) {
        window.open(bAviso.dataset.url, '_blank', 'noopener');   // abre WhatsApp con el mensaje escrito
        marcarAvisado(bAviso.dataset.pedido);
        aviso('Se abrió WhatsApp. Solo falta presionar enviar.');
        return;
    }
    const bOmitir = e.target.closest('button[data-descartar]');
    if (bOmitir) marcarAvisado(bOmitir.dataset.descartar);
});

async function actualizar() {
    const punto = document.getElementById('punto'), txt = document.getElementById('txtAct');
    try {
        const r = await fetch(URL_DATOS, { cache: 'no-store' });
        const d = await r.json();
        if (!d.success) throw new Error(d.message || 'error');
        dibujar(d); listarSalidas(d); listarAlertas(d);
        punto.className = 'punto';
        txt.textContent = 'Actualizado ' + new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    } catch (e) {
        punto.className = 'punto error';
        txt.textContent = 'Sin conexión, reintentando…';
    }
}

actualizar();
setInterval(actualizar, CADA_MS);
</script>
</body>
</html>
