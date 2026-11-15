<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>FRUVER · Repartidor</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;font-family:system-ui,sans-serif;background:#f1f5f9;color:#1e293b}
  header{background:#1e5631;color:#fff;padding:14px 16px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:10;gap:10px}
  header nav{display:flex;gap:14px}
  header a{color:#fff;font-size:.85rem}
  .card{background:#fff;margin:12px;padding:14px;border-radius:14px;box-shadow:0 1px 4px #0002}
  .row{display:flex;gap:8px;margin-top:8px}
  .btn{flex:1;padding:14px;border:0;border-radius:10px;font-size:1rem;font-weight:600;color:#fff;text-align:center;text-decoration:none}
  .btn:disabled{opacity:.55}
  .go{background:#2563eb}.ok{background:#16a34a}.help{background:#dc2626}.sec{background:#64748b}.start{background:#f16b1a}
  .tag{display:inline-block;padding:3px 8px;border-radius:6px;font-size:.8rem;font-weight:600;background:#e2e8f0}
  .cobro{font-size:1.2rem;font-weight:700;color:#f16b1a}
  .titulo{margin:16px 14px 0;font-weight:700;color:#475569;font-size:.9rem}
  .nota{color:#64748b;font-size:.85rem}
  .aviso-card{background:#fff7ed;color:#9a3412;border-radius:8px;padding:8px;font-size:.85rem;margin-top:8px}
  ol.orden{margin:8px 0 0;padding-left:20px;font-size:.9rem}
  ol.orden li{margin:4px 0}
  #sheet{position:fixed;inset:0;background:#0007;display:none;align-items:flex-end}
  #sheet .box{background:#fff;width:100%;padding:16px;border-radius:18px 18px 0 0}
  #sheet .btn{display:block;width:100%;margin-top:8px}
  #msg{position:fixed;bottom:16px;left:16px;right:16px;background:#1e293b;color:#fff;padding:12px;border-radius:10px;display:none;text-align:center}
</style>
</head>
<body>
<header>
  <b>🚚 <?= esc(session()->get('nombre')) ?></b>
  <nav>
    <a href="<?= base_url('repartidor/inventario') ?>">Inventario</a>
    <a href="<?= base_url('repartidor/panel') ?>">Panel</a>
    <a href="<?= base_url('salir') ?>">Salir</a>
  </nav>
</header>

<div id="salidaActiva"></div>
<div id="respuestas"></div>
<div id="lista"></div>
<div class="card"><button class="btn help" style="width:100%" onclick="abrirAyuda(null)">🆘 Necesito ayuda</button></div>

<div id="sheet"><div class="box">
  <b>¿Qué pasó?</b>
  <button class="btn sec" onclick="enviarAyuda('cliente_no_responde')">Cliente no responde</button>
  <button class="btn sec" onclick="enviarAyuda('direccion_incorrecta')">Dirección incorrecta</button>
  <button class="btn sec" onclick="enviarAyuda('cliente_no_paga')">Cliente no paga</button>
  <button class="btn sec" onclick="enviarAyuda('producto_danado')">Producto dañado</button>
  <button class="btn sec" onclick="enviarAyuda('problema_vehiculo')">Problema con el vehículo</button>
  <button class="btn" style="background:#cbd5e1;color:#000" onclick="cerrarAyuda()">Cancelar</button>
</div></div>
<div id="msg"></div>

<script>
const API = "<?= base_url('repartidor/api') ?>";
let pedidoAyuda = null, ultimoEnvio = 0;
let salidaActual = null;
let iniciando = false;

const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const post = (url, obj) => fetch(url, {
  method: 'POST',
  headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json'},
  body: new URLSearchParams(obj)
}).then(r => r.json());
const km = n => Number(n || 0).toFixed(1);
// Deja solo colonia/municipio/estado para listas compactas.
const lugarCorto = d => String(d || 'Sin dirección').split(',').slice(-3).join(',').trim();

function aviso(t){ const m = document.getElementById('msg'); m.textContent = t; m.style.display = 'block'; setTimeout(() => m.style.display = 'none', 2800); }

/* ---------- Recorrido en curso ---------- */
function renderSalida(salida){
  const box = document.getElementById('salidaActiva');
  const paradas = (salida.paradas || []).map(p => {
    const entregado = p.estado === 'entregado';
    return `<div class="card" style="margin:8px 12px;border-left:5px solid ${entregado ? '#16a34a' : '#2563eb'};">
      <b>${p.orden}. ${esc(p.cliente || 'Cliente')}</b> <span class="tag">${entregado ? 'Entregado' : 'Pendiente'}</span>
      <div style="margin:5px 0">📍 ${esc(p.direccion || 'Sin dirección')}</div>
      <div class="row">
        ${p.tel ? `<a class="btn sec" href="tel:${esc(p.tel)}">📞 Llamar</a>` : ''}
        ${p.lat && p.lng ? `<a class="btn sec" target="_blank" href="https://www.google.com/maps/search/?api=1&query=${p.lat},${p.lng}">🗺️ Destino</a>` : ''}
      </div>
      ${entregado ? '' : `<div class="row"><button class="btn ok" onclick="entregarSalida(${p.id_pedido})">✅ Marcar entrega</button></div>`}
      ${entregado ? '' : `<div class="row"><button class="btn help" onclick="abrirAyuda(${p.id_pedido})">No pude entregar / Ayuda</button></div>`}
    </div>`;
  }).join('');
  const hechas = (salida.paradas || []).filter(p => p.estado === 'entregado').length;
  box.innerHTML = `<div class="card" style="border-top:5px solid #1e5631">
    <b>🚚 Recorrido en curso</b>
    <div class="nota" style="margin:6px 0">${hechas} de ${salida.paradas.length} entregas · ${km(salida.km_total)} km con regreso al local</div>
    ${salida.google_maps_url ? `<a class="btn go" style="display:block" target="_blank" href="${esc(salida.google_maps_url)}">🗺️ Abrir recorrido completo</a>` : ''}
  </div>${paradas}`;
}

/* ---------- Vista previa antes de salir ---------- */
function renderPrevia(p){
  const box = document.getElementById('salidaActiva');
  if (!p || !p.listos) { box.innerHTML = ''; return; }
  const bloqueado = (p.sin_ubicacion || []).length > 0;
  const orden = (p.paradas || []).map(x =>
    `<li>${esc(lugarCorto(x.direccion))} <span class="nota">· +${km(x.km_desde_anterior)} km</span></li>`).join('');
  let extra = '';
  if (p.para_despues > 0) extra += `<div class="aviso-card">Tienes ${p.listos} pedidos listos. En este recorrido salen ${p.en_este_recorrido} (máximo 6); los demás irán en el siguiente.</div>`;
  if (bloqueado) extra += `<div class="aviso-card">${p.sin_ubicacion.length} pedido(s) no tienen ubicación en el mapa. Avisa al administrador para poder salir.</div>`;
  (p.advertencias || []).forEach(a => extra += `<div class="aviso-card">${esc(a)}</div>`);
  box.innerHTML = `<div class="card" style="border-top:5px solid #f16b1a">
    <b>🚚 Listo para salir</b>
    <div class="nota" style="margin:6px 0">${p.en_este_recorrido} entrega(s) · ${km(p.km_total)} km ida y vuelta (incluye el regreso al local)</div>
    ${p.combustible ? `<div class="nota" style="margin:6px 0">⛽ Gasolina aprox.: ${Number(p.combustible.litros).toFixed(1)} L · $${Number(p.combustible.costo).toFixed(2)}</div>` : ''}
    <ol class="orden">${orden}</ol>
    ${extra}
    <div class="row"><button id="btnIniciar" class="btn start" ${bloqueado ? 'disabled' : ''} onclick="iniciarRecorrido()">▶ Iniciar recorrido</button></div>
  </div>`;
}

async function cargarSalida(){
  try {
    const d = await (await fetch(API + '/salida-activa')).json();
    salidaActual = d.data || null;
    if (salidaActual) { renderSalida(salidaActual); return; }
    const previa = await (await fetch(API + '/recorrido/previa')).json();
    if (previa.success) renderPrevia(previa.data);
    else document.getElementById('salidaActiva').innerHTML = '';
  } catch(e) { }
}

async function iniciarRecorrido(){
  if (iniciando) return;
  if (!confirm('¿Iniciar el recorrido? Tus pedidos pasarán a "En tránsito".')) return;
  iniciando = true;
  const btn = document.getElementById('btnIniciar');
  if (btn) btn.disabled = true;
  try {
    const r = await post(API + '/recorrido/iniciar', {});
    aviso(r.success ? 'Recorrido iniciado' : (r.message || 'No se pudo iniciar el recorrido'));
  } catch(e) {
    aviso('No se pudo iniciar el recorrido. Revisa tu conexión.');
  } finally {
    iniciando = false;
    refrescar();
  }
}

async function entregarSalida(id){
  if (!confirm('¿Confirmas que entregaste este pedido?')) return;
  const enviar = (lat, lng) => post(API + '/salida/pedido/entregar/' + id, {lat: lat || '', lng: lng || ''})
    .then(r => { aviso(r.success ? 'Entrega registrada' : (r.message || 'No se pudo registrar')); refrescar(); })
    .catch(() => aviso('No se pudo registrar la entrega. Revisa tu conexión.'));
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(p => enviar(p.coords.latitude, p.coords.longitude), () => enviar('', ''), {timeout:5000});
  } else enviar('', '');
}

/* ---------- Pedidos asignados ---------- */
async function cargar(){
  try {
    const d = await (await fetch(API + '/pedidos')).json();
    // Los que ya van en el recorrido en curso se muestran arriba, no aquí.
    const enSalida = new Set((salidaActual?.paradas || []).map(p => Number(p.id_pedido)));
    const pedidos = (d.data || []).filter(p => !enSalida.has(Number(p.id)));
    const lista = document.getElementById('lista');

    if (!pedidos.length) {
      lista.innerHTML = salidaActual ? '' : '<div class="card">Sin pedidos asignados 🎉</div>';
      return;
    }

    const titulo = salidaActual
      ? '<div class="titulo">Para tu siguiente recorrido</div>'
      : '<div class="titulo">Tus pedidos asignados</div>';

    lista.innerHTML = titulo + pedidos.map(p => {
      const dir = p.calle ? `${p.calle} #${p.numero}, ${p.colonia}, ${p.municipio || ''} ${p.estado_dir || ''}` : 'Sin dirección registrada';
      const porCobrar = Math.max(0, p.total - p.monto_pagado).toFixed(2);
      const enRuta = p.estado_actual === 'Pedido en tránsito';
      return `<div class="card">
        <b>Pedido #${String(p.id).padStart(5,'0')}</b> <span class="tag">${esc(p.estado_actual)}</span><br>
        <div style="margin-top:6px">👤 ${esc(p.cliente)}</div>
        <div style="margin:4px 0">📍 ${esc(dir)}</div>
        <div class="cobro">Por cobrar: $${porCobrar}</div>
        <div class="row">
          ${p.tel ? `<a class="btn sec" href="tel:${esc(p.tel)}">📞 Llamar</a>` : ''}
          <a class="btn sec" target="_blank" href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(dir)}">🗺️ Mapa</a>
        </div>
        ${enRuta
          ? `<div class="row"><button class="btn ok" onclick="cambiar(${p.id},'Venta confirmada')">✅ Entregado</button></div>`
          : `<div class="nota" style="margin-top:8px">Saldrá cuando inicies tu recorrido.</div>`}
        <div class="row"><button class="btn help" onclick="abrirAyuda(${p.id})">No pude entregar / Ayuda</button></div>
      </div>`;
    }).join('');
  } catch(e) { aviso('Sin conexión, reintentando...'); }
}

// Pedido que ya iba en tránsito antes de existir los recorridos: solo se puede cerrar como entregado.
async function cambiar(id, estado){
  if (!confirm('¿Confirmas que ya entregaste el pedido?')) return;
  try {
    const r = await post(API + '/estado', {id, estado});
    aviso(r.success ? 'Estado actualizado' : 'No se pudo actualizar');
  } catch(e) { aviso('No se pudo actualizar. Revisa tu conexión.'); }
  refrescar();
}

// Primero la salida (define qué pedidos se muestran abajo) y luego la lista.
async function refrescar(){
  await cargarSalida();
  await cargar();
}

/* ---------- Ayuda ---------- */
function abrirAyuda(id){ pedidoAyuda = id; document.getElementById('sheet').style.display = 'flex'; }
function cerrarAyuda(){ document.getElementById('sheet').style.display = 'none'; }

function enviarAyuda(tipo){
  cerrarAyuda();
  const enviar = (lat, lng) => post(API + '/incidencia', {id_pedido: pedidoAyuda ?? '', tipo, lat: lat ?? '', lng: lng ?? ''})
      .then(() => aviso('Ayuda enviada al administrador'));
  if (!navigator.geolocation) return enviar();
  navigator.geolocation.getCurrentPosition(p => enviar(p.coords.latitude, p.coords.longitude), () => enviar(), {timeout: 5000});
}

// Ubicación en segundo plano (máximo 1 envío cada 10 s). Requiere https o localhost.
if (navigator.geolocation) {
  navigator.geolocation.watchPosition(p => {
    if (Date.now() - ultimoEnvio < 10000) return;
    ultimoEnvio = Date.now();
    post(API + '/ubicacion', {lat: p.coords.latitude, lng: p.coords.longitude});
  }, () => {}, {enableHighAccuracy: true, maximumAge: 10000});
}

/* ---------- Respuestas del administrador ---------- */
const respuestasMostradas = new Set();

function mostrarRespuesta(r){
  const card = document.createElement('div');
  card.className = 'card';
  card.style.borderLeft = '6px solid #1e5631';
  card.innerHTML = `<b>📩 Respuesta del administrador</b>${r.id_pedido ? ' · Pedido #' + esc(r.id_pedido) : ''}
    <div style="margin:6px 0;white-space:pre-wrap">${esc(r.respuesta)}</div>
    <button class="btn ok" style="width:100%">Entendido</button>`;
  card.querySelector('button').onclick = () => {
    post(API + '/respuestas/leer', {}).catch(() => {});
    card.remove();
  };
  document.getElementById('respuestas').appendChild(card);
}

async function cargarRespuestas(){
  try {
    const d = await (await fetch(API + '/respuestas')).json();
    (d.data || []).forEach(r => {
      if (respuestasMostradas.has(r.id)) return;
      respuestasMostradas.add(r.id);
      mostrarRespuesta(r);
    });
  } catch(e) {}
}

refrescar();
cargarRespuestas();
setInterval(refrescar, 8000);
setInterval(cargarRespuestas, 8000);
</script>
</body>
</html>
