<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVER · Monitor de salidas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --verde:#1d5631; --naranja:#f16b1a; }
        * { box-sizing:border-box; }
        body { margin:0; background:#f5f7f5; color:#24352a; font-family:Inter,Segoe UI,sans-serif; }
        .wrap { max-width:1180px; margin:0 auto; padding:22px; }
        .head { background:linear-gradient(135deg,#1d5631,#2d7a43); color:#fff; border-radius:18px; padding:20px 24px; display:flex; justify-content:space-between; gap:15px; align-items:center; flex-wrap:wrap; }
        .head h1 { margin:0; font-size:1.55rem; } .head p { margin:5px 0 0; opacity:.85; font-size:.88rem; }
        .btn { border:0; border-radius:9px; padding:9px 13px; cursor:pointer; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:7px; font-size:.85rem; }
        .btn-white { background:#fff; color:var(--verde); } .btn-green { background:var(--verde); color:#fff; } .btn-gris { background:#eef2ef; color:#35503c; }
        .card { background:#fff; border-radius:15px; box-shadow:0 5px 18px rgba(15,23,42,.07); padding:18px; margin-top:16px; }
        .card h2 { margin:0 0 4px; color:var(--verde); font-size:1.1rem; display:flex; align-items:center; gap:8px; }
        .sub { color:#64748b; font-size:.82rem; margin:0 0 10px; }
        .pend { display:flex; gap:10px; flex-wrap:wrap; }
        .pend-item { background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:9px 13px; font-size:.86rem; }
        .pend-item strong { color:#9a3412; }
        .salida { border:1px solid #e1e8e2; border-radius:12px; padding:14px; margin-top:10px; }
        .salida.en-ruta { border-left:5px solid #2563eb; } .salida.fin { border-left:5px solid #16a34a; opacity:.92; }
        .top { display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
        .top h3 { margin:0; color:var(--verde); font-size:1rem; }
        .badge { border-radius:30px; padding:4px 10px; font-size:.72rem; font-weight:800; }
        .badge.iniciada { color:#075985; background:#e0f2fe; } .badge.finalizada { color:#166534; background:#dcfce7; }
        .meta { color:#64748b; font-size:.8rem; margin:8px 0; display:flex; gap:14px; flex-wrap:wrap; }
        .barra { height:8px; background:#e5ebe6; border-radius:8px; overflow:hidden; margin:8px 0; } .barra i { display:block; height:100%; background:#16a34a; }
        .acciones { display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }
        .paradas { margin:10px 0 0; padding:0; list-style:none; display:none; }
        .paradas.abierto { display:block; }
        .paradas li { display:flex; gap:10px; align-items:center; padding:8px 4px; border-top:1px solid #eef2ef; font-size:.84rem; }
        .num { width:26px; height:26px; border-radius:50%; background:#2d8a3e; color:#fff; font-weight:700; font-size:.78rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .num.hecho { background:#16a34a; } .num.pend { background:#94a3b8; }
        .vacio { color:#64748b; text-align:center; padding:22px; }
        .error { background:#fff7ed; color:#9a3412; border-radius:10px; padding:10px 13px; margin-top:14px; font-size:.86rem; }
        @media(max-width:650px){ .wrap{padding:12px;} }
    </style>
</head>
<body>
<div class="wrap">
    <header class="head">
        <div>
            <h1><i class="fas fa-route"></i> Monitor de salidas</h1>
            <p>Las salidas las inicia cada repartidor desde su app. Aquí ves quién ya salió y cómo va. <span id="actualizado"></span></p>
        </div>
        <a href="<?= base_url('menusolo') ?>" class="btn btn-white"><i class="fas fa-arrow-left"></i> Regresar</a>
    </header>

    <?php if (!empty($error)): ?><div class="error"><?= esc($error) ?></div><?php endif; ?>
    <div id="errorRed" class="error" style="display:none;"></div>

    <section class="card">
        <h2><i class="fas fa-hourglass-half" style="color:var(--naranja)"></i> Pendientes de salir</h2>
        <p class="sub">Repartidores con pedidos listos que todavía no inician su recorrido.</p>
        <div id="pendientes" class="pend"></div>
    </section>

    <section class="card">
        <h2><i class="fas fa-truck-fast" style="color:#2563eb"></i> Recorridos</h2>
        <p class="sub">En ruta primero; después los terminados recientemente.</p>
        <div id="salidas"></div>
    </section>
</div>

<script>
const URL_LISTA = '<?= base_url('salidas/lista') ?>';
let datos = <?= json_encode($datos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const abiertas = new Set(); // salidas con las paradas desplegadas

const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const km = n => Number(n || 0).toFixed(1);
const hora = f => f ? String(f).substring(11, 16) : '—';

function textoHace(min) {
    if (min === null || min === undefined) return 'sin ubicación enviada';
    if (min < 1) return 'hace menos de 1 min';
    if (min < 60) return 'hace ' + min + ' min';
    if (min < 1440) return 'hace ' + Math.round(min / 60) + ' h';
    return 'hace ' + Math.round(min / 1440) + ' día(s)';
}

function pintarPendientes() {
    const cont = document.getElementById('pendientes');
    const lista = datos.pendientes || [];
    cont.innerHTML = lista.length
        ? lista.map(p => '<div class="pend-item"><strong>' + esc(p.repartidor) + '</strong> · ' + p.listos + ' pedido(s) listo(s)</div>').join('')
        : '<div class="vacio" style="padding:6px 0;">Ningún repartidor tiene pedidos esperando salir.</div>';
}

function pintarSalidas() {
    const cont = document.getElementById('salidas');
    const lista = datos.salidas || [];
    if (!lista.length) {
        cont.innerHTML = '<div class="vacio">Todavía no hay recorridos. Aparecerán cuando un repartidor pulse "Iniciar recorrido".</div>';
        return;
    }
    cont.innerHTML = lista.map(s => {
        const enRuta = s.estado === 'iniciada';
        const pct = s.total_paradas ? Math.round(s.entregadas / s.total_paradas * 100) : 0;
        const ubic = (s.ultima_lat && s.ultima_lng)
            ? '<a class="btn btn-gris" target="_blank" rel="noopener" href="https://www.google.com/maps?q=' + encodeURIComponent(s.ultima_lat + ',' + s.ultima_lng) + '"><i class="fas fa-location-dot"></i> Última ubicación · ' + textoHace(s.ultima_minutos) + '</a>'
            : '';
        const ruta = (s.google_maps_url && String(s.google_maps_url).startsWith('https://www.google.com/maps/'))
            ? '<a class="btn btn-gris" target="_blank" rel="noopener" href="' + esc(s.google_maps_url) + '"><i class="fa-brands fa-google"></i> Ver ruta planeada</a>' : '';
        const abierto = abiertas.has(String(s.id));
        const paradas = (s.paradas || []).map(p => {
            const hecho = p.estado === 'entregado';
            const fallo = p.estado === 'fallido';   // el repartidor no pudo entregar (el motivo llega como alerta)
            return '<li><span class="num ' + (hecho ? 'hecho' : 'pend') + '"' + (fallo ? ' style="background:#dc2626;color:#fff;"' : '') + '>' + (hecho ? '<i class="fas fa-check"></i>' : (fallo ? '<i class="fas fa-xmark"></i>' : p.orden)) + '</span>' +
                '<span><strong>' + esc(p.cliente || 'Cliente') + '</strong> · Pedido #' + p.id_pedido + '<br><small style="color:#64748b;">' + esc(p.direccion || '') + '</small></span>' +
                '<span style="margin-left:auto;color:#64748b;white-space:nowrap;">' + (hecho ? 'Entregado ' + hora(p.entregado_at) : (fallo ? 'No entregado ' + hora(p.entregado_at) : '+' + km(p.km_desde_anterior) + ' km')) + '</span></li>';
        }).join('');
        return '<article class="salida ' + (enRuta ? 'en-ruta' : 'fin') + '">' +
            '<div class="top"><h3>Salida #' + s.id + ' · ' + esc(s.repartidor || 'Repartidor') + '</h3>' +
            '<span class="badge ' + esc(s.estado) + '">' + (enRuta ? 'En ruta' : 'Finalizada') + '</span></div>' +
            '<div class="meta"><span><i class="fas fa-play"></i> Salió ' + hora(s.iniciada_at) + '</span>' +
            (enRuta ? '' : '<span><i class="fas fa-flag-checkered"></i> Terminó ' + hora(s.finalizada_at) + '</span>') +
            '<span><i class="fas fa-road"></i> ' + km(s.km_total) + ' km planeados</span>' +
            '<span><i class="fas fa-box"></i> ' + s.entregadas + ' de ' + s.total_paradas + ' entregados</span></div>' +
            '<div class="barra"><i style="width:' + pct + '%"></i></div>' +
            '<div class="acciones"><button type="button" class="btn btn-green" data-toggle="' + s.id + '"><i class="fas fa-list"></i> ' + (abierto ? 'Ocultar paradas' : 'Ver paradas') + '</button>' + ubic + ruta + '</div>' +
            '<ul class="paradas ' + (abierto ? 'abierto' : '') + '">' + paradas + '</ul></article>';
    }).join('');
}

document.getElementById('salidas').addEventListener('click', e => {
    const btn = e.target.closest('[data-toggle]');
    if (!btn) return;
    const id = btn.dataset.toggle;
    abiertas.has(id) ? abiertas.delete(id) : abiertas.add(id);
    pintarSalidas();
});

function pintar() {
    pintarPendientes();
    pintarSalidas();
    document.getElementById('actualizado').textContent = 'Actualizado ' + (datos.generado || '');
}

async function refrescar() {
    const aviso = document.getElementById('errorRed');
    try {
        const r = await fetch(URL_LISTA, { headers: { 'Accept': 'application/json' } });
        const d = await r.json();
        if (!r.ok || !d.success) throw new Error(d.message || 'Error ' + r.status);
        datos = d;
        aviso.style.display = 'none';
        pintar();
    } catch (err) {
        aviso.textContent = 'No se pudo actualizar el monitor: ' + err.message + '. Se mostrará lo último que se cargó.';
        aviso.style.display = 'block';
    }
}

pintar();
setInterval(refrescar, 15000);
</script>
</body>
</html>
