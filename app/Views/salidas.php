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
        .opt-cab { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap; }
        .opt-kpis { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px; margin-top:6px; }
        .opt-kpi { border:1px solid #e1e8e2; border-left:5px solid #16a34a; border-radius:12px; padding:12px 14px; background:#fbfdfb; }
        .opt-kpi .t { font-size:.7rem; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:.4px; }
        .opt-kpi .v { font-size:1.5rem; font-weight:800; color:#15803d; margin-top:2px; }
        .opt-kpi .v small { font-size:.85rem; font-weight:700; }
        .opt-kpi .s { font-size:.76rem; color:#64748b; margin-top:3px; }
        .opt-nota { color:#64748b; font-size:.78rem; margin:10px 0 0; line-height:1.5; }
        .meta .opt { color:#15803d; font-weight:700; }
        .modal-fondo { position:fixed; inset:0; background:rgba(0,0,0,.5); display:none; align-items:center; justify-content:center; padding:16px; z-index:1000; }
        .modal-fondo.abierto { display:flex; }
        .modal-caja { background:#fff; border-radius:16px; padding:22px; width:min(560px,100%); max-height:94vh; overflow:auto; box-shadow:0 20px 50px rgba(0,0,0,.3); }
        .modal-caja h3 { margin:0 0 4px; color:var(--verde); display:flex; gap:8px; align-items:center; }
        .modal-caja h4 { margin:18px 0 6px; color:var(--verde); font-size:.95rem; }
        .campos { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:12px; }
        .campos label { display:flex; flex-direction:column; gap:4px; font-size:.78rem; font-weight:700; color:#475569; }
        .campos label small { font-weight:500; color:#64748b; }
        .campos input[type=number] { padding:9px 10px; border:1.5px solid #cbd5e1; border-radius:9px; font:inherit; font-size:.95rem; }
        .campos .ancho { grid-column:1 / -1; flex-direction:row; align-items:center; gap:10px; font-weight:600; }
        .horarios { width:100%; border-collapse:collapse; font-size:.82rem; }
        .horarios th, .horarios td { padding:7px 8px; border-bottom:1px solid #eef2ef; text-align:left; }
        .horarios th { color:#64748b; font-size:.7rem; text-transform:uppercase; }
        .modal-pie { display:flex; gap:10px; justify-content:flex-end; margin-top:18px; flex-wrap:wrap; }
        .msg-form { margin-top:10px; font-size:.84rem; min-height:1.2em; }
        .msg-form.error { color:#b91c1c; } .msg-form.ok { color:#15803d; }
        @media(max-width:650px){ .wrap{padding:12px;} .campos{grid-template-columns:1fr;} }
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

    <section class="card" id="cardOptimizacion">
        <div class="opt-cab">
            <div>
                <h2><i class="fas fa-leaf" style="color:#16a34a"></i> Ahorro por optimizar las rutas</h2>
                <p class="sub" style="margin-bottom:0;">Cada recorrido se compara contra el orden en que se registraron los pedidos. Últimos 30 días · <span id="optSalidas"></span></p>
            </div>
            <button type="button" class="btn btn-gris" id="btnParametros"><i class="fas fa-sliders"></i> Parámetros de ruta y gasolina</button>
        </div>
        <div id="optKpis" class="opt-kpis"></div>
        <p class="opt-nota" id="optNota"></p>
    </section>

    <section class="card">
        <h2><i class="fas fa-truck-fast" style="color:#2563eb"></i> Recorridos</h2>
        <p class="sub">En ruta primero; después los terminados recientemente.</p>
        <div id="salidas"></div>
    </section>
</div>

<!-- Parámetros con los que se calculan los tiempos, el tráfico y la gasolina -->
<div class="modal-fondo" id="modalParametros" role="dialog" aria-modal="true" aria-labelledby="tituloParametros">
    <div class="modal-caja">
        <h3 id="tituloParametros"><i class="fas fa-sliders" style="color:var(--naranja)"></i> Parámetros de ruta y gasolina</h3>
        <p class="sub" style="margin:0;">Se usan al armar cada recorrido. Los recorridos que ya se guardaron conservan los valores con los que se calcularon.</p>

        <form id="formParametros" novalidate>
            <div class="campos">
                <label>Rendimiento (km por litro)
                    <input type="number" name="rendimiento_km_l" min="1" max="60" step="0.1" required>
                    <small>Cuánto rinde la unidad de reparto.</small>
                </label>
                <label>Precio de la gasolina ($ por litro)
                    <input type="number" name="precio_litro" min="1" max="200" step="0.01" required>
                    <small>Actualízalo cuando cambie.</small>
                </label>
                <label>Velocidad promedio (km/h)
                    <input type="number" name="velocidad_promedio_kmh" min="10" max="120" step="1" required>
                    <small>Solo se usa si el mapa no da el tiempo.</small>
                </label>
                <label>Minutos por entrega
                    <input type="number" name="minutos_por_entrega" min="0" max="60" step="1" required>
                    <small>Descargar, cobrar y tomar la evidencia.</small>
                </label>
                <label class="ancho"><input type="checkbox" name="usar_transito" id="usarTransito"> Alargar el tiempo en los horarios de tráfico pesado</label>
            </div>
            <div class="msg-form" id="msgParametros"></div>

            <h4>Horarios de tráfico pesado</h4>
            <table class="horarios">
                <thead><tr><th>Horario</th><th>Días</th><th>De</th><th>A</th><th>Alarga el tiempo</th></tr></thead>
                <tbody id="tablaHorarios"></tbody>
            </table>
            <p class="opt-nota">Es un ajuste por hora del día basado en valores de referencia, no tráfico en vivo. Para cambiar los horarios, edita la tabla <code>ruta_transito</code>.</p>

            <div class="modal-pie">
                <button type="button" class="btn btn-gris" id="btnCerrarParametros">Cancelar</button>
                <button type="submit" class="btn btn-green" id="btnGuardarParametros"><i class="fas fa-check"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
const URL_LISTA = '<?= base_url('salidas/lista') ?>';
const URL_PARAMETROS = '<?= base_url('salidas/parametros') ?>';
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
            (s.tiempo ? '<span><i class="fas fa-clock"></i> ~' + fmtMin(s.tiempo.total_min) + ' estimados' + (s.tiempo.transito_min > 0 ? ' (+' + fmtMin(s.tiempo.transito_min) + ' por hora pico)' : '') + '</span>' : '') +
            (s.comparativo && s.comparativo.hay_ahorro ? '<span class="opt"><i class="fas fa-leaf"></i> Ahorra ' + km(s.comparativo.ahorro.km) + ' km (' + Number(s.comparativo.ahorro.pct_km).toFixed(1) + '%) contra el orden de captura</span>' : '') +
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

/* 2 h 35 min / 45 min */
function fmtMin(n) {
    n = Math.max(0, Math.round(Number(n) || 0));
    const h = Math.floor(n / 60), m = n % 60;
    return h > 0 ? h + ' h ' + m + ' min' : m + ' min';
}
const dinero = n => (n < 0 ? '-' : '') + '$' + Math.abs(Number(n || 0)).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

/* Ahorro acumulado de las rutas optimizadas contra el orden de captura */
function pintarOptimizacion() {
    const o = datos.optimizacion;
    const kpis = document.getElementById('optKpis');
    const nota = document.getElementById('optNota');

    if (!o || !o.salidas) {
        document.getElementById('optSalidas').textContent = 'sin recorridos todavía';
        kpis.innerHTML = '<div class="vacio" style="grid-column:1/-1;padding:10px 0;">Cuando los repartidores inicien recorridos, aquí verás cuánto se ahorra en distancia, tiempo y gasolina.</div>';
        nota.textContent = '';
        return;
    }

    document.getElementById('optSalidas').textContent = o.salidas + (o.salidas === 1 ? ' recorrido analizado' : ' recorridos analizados');
    const tarjeta = (titulo, ahorro, pct, detalle, signo) =>
        '<div class="opt-kpi" style="' + (ahorro < -0.005 ? 'border-left-color:#f59e0b;' : '') + '"><div class="t">' + titulo + '</div>' +
        '<div class="v">' + (ahorro < -0.005 ? '+' : '') + ahorro_txt(ahorro, signo) + ' <small>(' + Math.abs(Number(pct)).toFixed(1) + '%)</small></div>' +
        '<div class="s">' + detalle + '</div></div>';
    const ahorro_txt = (v, t) => t(Math.abs(v));

    kpis.innerHTML =
        tarjeta('Distancia ahorrada', o.km_ahorro, o.pct_km, km(o.km_original) + ' km → ' + km(o.km_optimizado) + ' km', v => km(v) + ' km') +
        tarjeta('Tiempo de manejo ahorrado', o.min_ahorro, o.pct_min, fmtMin(o.min_original) + ' → ' + fmtMin(o.min_optimizado), v => fmtMin(v)) +
        tarjeta('Gasolina ahorrada', o.litros_ahorro, o.pct_litros, Number(o.litros_original).toFixed(1) + ' L → ' + Number(o.litros_optimizado).toFixed(1) + ' L', v => Number(v).toFixed(1) + ' L') +
        tarjeta('Costo de gasolina ahorrado', o.costo_ahorro, o.pct_costo, dinero(o.costo_original) + ' → ' + dinero(o.costo_optimizado), v => dinero(v));
    nota.textContent = 'Recorrido original = el orden en que se registraron los pedidos. Optimizado = el orden con menos kilómetros (los pedidos urgentes van primero). El tiempo usa el tiempo de manejo del mapa, alargado en las horas pico configuradas (no es tráfico en vivo). Si un recorrido tuvo urgentes, puede salir más largo que el orden de captura.';
}

/* ----- Parámetros de ruta y gasolina ----- */
const modalParam = document.getElementById('modalParametros');
const formParam  = document.getElementById('formParametros');
const msgParam   = document.getElementById('msgParametros');
const dias = { 1: 'Lun', 2: 'Mar', 3: 'Mié', 4: 'Jue', 5: 'Vie', 6: 'Sáb', 7: 'Dom' };

function mensajeParam(texto, tipo) { msgParam.textContent = texto || ''; msgParam.className = 'msg-form ' + (tipo || ''); }

async function abrirParametros() {
    mensajeParam('Cargando...', '');
    modalParam.classList.add('abierto');
    try {
        const r = await fetch(URL_PARAMETROS, { headers: { 'Accept': 'application/json' } });
        const d = await r.json();
        if (!r.ok || !d.success) throw new Error(d.message || 'Error ' + r.status);
        Object.entries(d.parametros).forEach(([clave, valor]) => {
            const campo = formParam.elements[clave];
            if (!campo) return;
            if (campo.type === 'checkbox') campo.checked = Number(valor) === 1; else campo.value = valor;
        });
        document.getElementById('tablaHorarios').innerHTML = (d.horarios || []).length
            ? d.horarios.map(h => '<tr><td>' + esc(h.nombre) + (Number(h.activo) ? '' : ' <small>(apagado)</small>') + '</td><td>' + esc(String(h.dias).split(',').map(x => dias[x] || x).join(', ')) + '</td><td>' + esc(String(h.hora_inicio).substring(0, 5)) + '</td><td>' + esc(String(h.hora_fin).substring(0, 5)) + '</td><td>+' + Math.round((Number(h.factor) - 1) * 100) + ' %</td></tr>').join('')
            : '<tr><td colspan="5" class="vacio" style="padding:10px;">No hay horarios configurados.</td></tr>';
        mensajeParam('', '');
    } catch (err) {
        mensajeParam('No se pudieron cargar los parámetros: ' + err.message, 'error');
    }
}

document.getElementById('btnParametros').addEventListener('click', abrirParametros);
document.getElementById('btnCerrarParametros').addEventListener('click', () => modalParam.classList.remove('abierto'));
modalParam.addEventListener('click', e => { if (e.target === modalParam) modalParam.classList.remove('abierto'); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') modalParam.classList.remove('abierto'); });

formParam.addEventListener('submit', async e => {
    e.preventDefault();
    const cuerpo = new URLSearchParams();
    ['rendimiento_km_l', 'precio_litro', 'velocidad_promedio_kmh', 'minutos_por_entrega'].forEach(c => cuerpo.set(c, formParam.elements[c].value));
    cuerpo.set('usar_transito', formParam.elements['usar_transito'].checked ? '1' : '0');

    const boton = document.getElementById('btnGuardarParametros');
    boton.disabled = true;
    mensajeParam('Guardando...', '');
    try {
        const r = await fetch(URL_PARAMETROS, { method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' }, body: cuerpo });
        const d = await r.json();
        if (!r.ok || !d.success) throw new Error(d.message || 'Error ' + r.status);
        mensajeParam(d.message || 'Parámetros guardados.', 'ok');
        setTimeout(() => modalParam.classList.remove('abierto'), 900);
    } catch (err) {
        mensajeParam(err.message, 'error');
    } finally {
        boton.disabled = false;
    }
});

function pintar() {
    pintarPendientes();
    pintarOptimizacion();
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
