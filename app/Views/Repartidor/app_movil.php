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
  .urgente{display:inline-block;padding:3px 9px;border-radius:6px;font-size:.8rem;font-weight:700;background:#dc2626;color:#fff;letter-spacing:.3px}
  .card.es-urgente{border:2px solid #dc2626}
  .cobro{font-size:1.2rem;font-weight:700;color:#f16b1a}
  .titulo{margin:16px 14px 0;font-weight:700;color:#475569;font-size:.9rem}
  .nota{color:#64748b;font-size:.85rem}
  .aviso-card{background:#fff7ed;color:#9a3412;border-radius:8px;padding:8px;font-size:.85rem;margin-top:8px}
  ol.orden{margin:8px 0 0;padding-left:20px;font-size:.9rem}
  ol.orden li{margin:4px 0}
  #sheet{position:fixed;inset:0;background:#0007;display:none;align-items:flex-end}
  #sheet .box{background:#fff;width:100%;padding:16px;border-radius:18px 18px 0 0}
  #sheet .btn{display:block;width:100%;margin-top:8px}
  #sheetEntrega{position:fixed;inset:0;background:#0007;display:none;align-items:flex-end;z-index:50}
  #sheetEntrega .box{background:#fff;width:100%;max-height:94vh;overflow:auto;padding:16px;border-radius:18px 18px 0 0}
  #sheetEntrega label{display:block;font-size:.82rem;font-weight:700;color:#475569;margin:12px 0 4px}
  #sheetEntrega input[type=number],#sheetEntrega select,#sheetEntrega textarea{width:100%;padding:12px;border:1.5px solid #cbd5e1;border-radius:10px;font-size:1rem;font-family:inherit}
  #entFirma{width:100%;height:150px;border:2px dashed #94a3b8;border-radius:10px;background:#fff;touch-action:none;display:block}
  #entPrev{max-width:100%;max-height:220px;border-radius:10px;margin-top:8px;display:none}
  #msg{position:fixed;bottom:16px;left:16px;right:16px;z-index:60;background:#1e293b;color:#fff;padding:12px;border-radius:10px;display:none;text-align:center}
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
<div class="card"><button class="btn go" style="width:100%" onclick="escanearStock()">📷 Consultar stock (escanear)</button></div>
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
<div id="sheetEntrega"><div class="box">
  <b style="font-size:1.1rem">✅ Confirmar entrega</b>
  <div style="margin-top:6px"><b id="entCliente"></b></div>
  <div class="nota" id="entDinero" style="margin:4px 0 8px"></div>

  <div id="entCobroBox">
    <label for="entCobro">💵 ¿Cuánto cobraste?</label>
    <input type="number" id="entCobro" min="0" step="0.01" inputmode="decimal" placeholder="0.00">
    <label for="entMetodo">Forma de pago</label>
    <select id="entMetodo"><option value="efectivo">Efectivo</option><option value="transferencia">Transferencia</option><option value="tarjeta">Tarjeta</option></select>
  </div>

  <label>📷 Foto de la entrega</label>
  <label class="btn sec" for="entFoto" style="display:block;cursor:pointer;text-align:center">Tomar foto</label>
  <input type="file" id="entFoto" accept="image/*" capture="environment" style="display:none">
  <img id="entPrev" alt="Vista previa de la foto">

  <label>✍️ Firma del cliente</label>
  <canvas id="entFirma"></canvas>
  <button type="button" class="btn sec" style="width:100%;margin-top:6px;padding:10px" onclick="borrarFirma()">Borrar firma</button>

  <label for="entNota">Nota (opcional)</label>
  <textarea id="entNota" rows="2" placeholder="Ej. lo recibió el portero"></textarea>
  <label style="display:flex;gap:8px;align-items:center;font-weight:500"><input type="checkbox" id="entSinEvidencia" style="width:auto"> Sin evidencia (explica el motivo en la nota)</label>

  <div class="row" style="margin-top:12px">
    <button type="button" class="btn sec" onclick="cerrarEntrega()">Cancelar</button>
    <button type="button" class="btn ok" id="entBtn" onclick="confirmarEntrega()">Confirmar entrega</button>
  </div>
</div></div>
<div id="msg"></div>

<script src="<?= base_url('js/vendor/html5-qrcode.min.js') ?>"></script>
<script src="<?= base_url('js/escaner.js') ?>?v=2"></script>
<script>
const API = "<?= base_url('repartidor/api') ?>";

/* ---------- Escáner: consulta de stock con la cámara ---------- */
function escanearStock(){
  FruverEscaner.abrirCamara({
    titulo: 'Consultar stock',
    onCodigo: async codigo => {
      const { producto: p } = await FruverEscaner.buscar(API + '/escaner', codigo);
      const unidad = p.unidad ? ' ' + p.unidad : '';
      return p.nombre + ': ' + p.disponible + unidad + ' disponibles'
        + (p.precio !== null ? ' · $' + Number(p.precio).toFixed(2) : '');
    }
  });
}
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
    const urgente = Number(p.prioritario) === 1 && !entregado;
    return `<div class="card ${urgente ? 'es-urgente' : ''}" style="margin:8px 12px;border-left:5px solid ${entregado ? '#16a34a' : (urgente ? '#dc2626' : '#2563eb')};">
      <b>${p.orden}. ${esc(p.cliente || 'Cliente')}</b> <span class="tag">${entregado ? 'Entregado' : 'Pendiente'}</span> ${urgente ? '<span class="urgente">⚡ URGENTE</span>' : ''}
      <div style="margin:5px 0">📍 ${esc(p.direccion || 'Sin dirección')}</div>
      <div class="row">
        ${p.tel ? `<a class="btn sec" href="tel:${esc(p.tel)}">📞 Llamar</a>` : ''}
        ${p.lat && p.lng ? `<a class="btn sec" target="_blank" href="https://www.google.com/maps/search/?api=1&query=${p.lat},${p.lng}">🗺️ Destino</a>` : ''}
      </div>
      ${entregado ? '' : (Number(p.pedido_total) - Number(p.pedido_pagado) > 0.009 ? `<div class="cobro" style="margin:4px 0">Por cobrar: $${(Number(p.pedido_total) - Number(p.pedido_pagado)).toFixed(2)}</div>` : '')}
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
    `<li>${x.prioritario ? '<span class="urgente">⚡ URGENTE</span> ' : ''}${esc(lugarCorto(x.direccion))} <span class="nota">· +${km(x.km_desde_anterior)} km</span></li>`).join('');
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

/* ---------- Entrega con evidencia (foto o firma) y cobro ---------- */
let entrega = { id: null, saldo: 0, foto: null, firmaDibujada: false, enviando: false };

const dinero = n => '$' + Number(n || 0).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});

function entregarSalida(id){
  const p = (salidaActual?.paradas || []).find(x => Number(x.id_pedido) === Number(id));
  if (!p) { aviso('No se encontró el pedido en tu recorrido.'); return; }

  const total  = Number(p.pedido_total || 0), pagado = Number(p.pedido_pagado || 0);
  const saldo  = Math.max(0, Math.round((total - pagado) * 100) / 100);
  entrega = { id, saldo, foto: null, firmaDibujada: false, enviando: false };

  document.getElementById('entCliente').textContent = p.cliente || 'Cliente';
  document.getElementById('entDinero').innerHTML =
    `Total ${dinero(total)} · pagado ${dinero(pagado)} · <b>por cobrar ${dinero(saldo)}</b>` +
    (p.tipo_pago === 'credito' ? ' <span class="tag">crédito</span>' : '');
  // Si es de contado se sugiere cobrar todo; si es crédito, normalmente se cobra después
  const campo = document.getElementById('entCobro');
  campo.value = (p.tipo_pago === 'contado' && saldo > 0) ? saldo.toFixed(2) : '';
  campo.max = saldo;
  document.getElementById('entCobroBox').style.display = saldo > 0 ? 'block' : 'none';
  document.getElementById('entMetodo').value = 'efectivo';
  document.getElementById('entNota').value = '';
  document.getElementById('entSinEvidencia').checked = false;
  document.getElementById('entFoto').value = '';
  document.getElementById('entPrev').style.display = 'none';
  document.getElementById('entBtn').disabled = false;
  document.getElementById('sheetEntrega').style.display = 'flex';
  prepararFirma();
}

function cerrarEntrega(){ document.getElementById('sheetEntrega').style.display = 'none'; }

/* Foto: se reduce antes de subirla (las fotos del celular pesan varios MB) */
document.getElementById('entFoto').addEventListener('change', e => {
  const archivo = e.target.files[0];
  if (!archivo) { entrega.foto = null; document.getElementById('entPrev').style.display = 'none'; return; }
  const img = new Image();
  img.onload = () => {
    const max = 1280, escala = Math.min(1, max / Math.max(img.width, img.height));
    const c = document.createElement('canvas');
    c.width = Math.round(img.width * escala); c.height = Math.round(img.height * escala);
    c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
    c.toBlob(b => {
      entrega.foto = b;
      const prev = document.getElementById('entPrev');
      prev.src = URL.createObjectURL(b); prev.style.display = 'block';
    }, 'image/jpeg', 0.72);
    URL.revokeObjectURL(img.src);
  };
  img.onerror = () => aviso('No se pudo leer la foto. Intenta de nuevo.');
  img.src = URL.createObjectURL(archivo);
});

/* Firma con el dedo */
function prepararFirma(){
  const c = document.getElementById('entFirma');
  const ratio = window.devicePixelRatio || 1;
  const ancho = c.clientWidth || 300;
  c.width = ancho * ratio; c.height = 150 * ratio;
  const g = c.getContext('2d');
  g.setTransform(ratio, 0, 0, ratio, 0, 0);
  g.fillStyle = '#fff'; g.fillRect(0, 0, ancho, 150);
  g.lineWidth = 2.4; g.lineCap = 'round'; g.lineJoin = 'round'; g.strokeStyle = '#111';
  entrega.firmaDibujada = false;

  let dibujando = false;
  const punto = ev => { const r = c.getBoundingClientRect(); return [ev.clientX - r.left, ev.clientY - r.top]; };
  c.onpointerdown = ev => { dibujando = true; c.setPointerCapture(ev.pointerId); const [x, y] = punto(ev); g.beginPath(); g.moveTo(x, y); g.lineTo(x + 0.1, y + 0.1); g.stroke(); entrega.firmaDibujada = true; ev.preventDefault(); };
  c.onpointermove = ev => { if (!dibujando) return; const [x, y] = punto(ev); g.lineTo(x, y); g.stroke(); ev.preventDefault(); };
  c.onpointerup = c.onpointercancel = () => { dibujando = false; };
}
function borrarFirma(){ prepararFirma(); }

async function confirmarEntrega(){
  if (entrega.enviando) return;
  const nota  = document.getElementById('entNota').value.trim();
  const sinEv = document.getElementById('entSinEvidencia').checked;

  if (!entrega.foto && !entrega.firmaDibujada && !(sinEv && nota.length >= 5)) {
    aviso('Toma una foto o pide la firma del cliente. Si no se puede, marca "Sin evidencia" y explica por qué.');
    return;
  }
  const cobro = parseFloat(document.getElementById('entCobro').value) || 0;
  if (cobro < 0 || cobro > entrega.saldo + 0.001) { aviso('El cobro no puede ser mayor a lo que se debe (' + dinero(entrega.saldo) + ').'); return; }

  entrega.enviando = true;
  document.getElementById('entBtn').disabled = true;

  const ubicacion = await new Promise(res => {
    if (!navigator.geolocation) return res(null);
    navigator.geolocation.getCurrentPosition(p => res(p.coords), () => res(null), {timeout: 5000});
  });

  const fd = new FormData();
  fd.append('lat', ubicacion ? ubicacion.latitude : '');
  fd.append('lng', ubicacion ? ubicacion.longitude : '');
  fd.append('nota', nota);
  fd.append('cobro', cobro > 0 ? cobro.toFixed(2) : '0');
  fd.append('metodo', document.getElementById('entMetodo').value);
  fd.append('sin_evidencia', sinEv ? '1' : '0');
  if (entrega.foto) fd.append('foto', entrega.foto, 'foto.jpg');
  if (entrega.firmaDibujada) {
    const blob = await new Promise(res => document.getElementById('entFirma').toBlob(res, 'image/png'));
    if (blob) fd.append('firma', blob, 'firma.png');
  }

  try {
    const r = await (await fetch(API + '/salida/pedido/entregar/' + entrega.id, { method: 'POST', body: fd, headers: {'Accept': 'application/json'} })).json();
    aviso(r.success ? 'Entrega registrada' : (r.message || 'No se pudo registrar'));
    if (r.success) { cerrarEntrega(); refrescar(); }
    else { entrega.enviando = false; document.getElementById('entBtn').disabled = false; }
  } catch (e) {
    aviso('No se pudo registrar la entrega. Revisa tu conexión.');
    entrega.enviando = false; document.getElementById('entBtn').disabled = false;
  }
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
      const urgente = Number(p.prioritario) === 1;
      return `<div class="card ${urgente ? 'es-urgente' : ''}">
        <b>Pedido #${String(p.id).padStart(5,'0')}</b> <span class="tag">${esc(p.estado_actual)}</span> ${urgente ? '<span class="urgente">⚡ URGENTE</span>' : ''}<br>
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
