<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>FRUVER · Repartidor</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;font-family:system-ui,sans-serif;background:#f1f5f9;color:#1e293b}
  header{background:#1e5631;color:#fff;padding:14px 16px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0}
  header a{color:#fff;font-size:.85rem}
  .card{background:#fff;margin:12px;padding:14px;border-radius:14px;box-shadow:0 1px 4px #0002}
  .row{display:flex;gap:8px;margin-top:8px}
  .btn{flex:1;padding:14px;border:0;border-radius:10px;font-size:1rem;font-weight:600;color:#fff;text-align:center;text-decoration:none}
  .go{background:#2563eb}.ok{background:#16a34a}.help{background:#dc2626}.sec{background:#64748b}
  .tag{display:inline-block;padding:3px 8px;border-radius:6px;font-size:.8rem;font-weight:600;background:#e2e8f0}
  .cobro{font-size:1.2rem;font-weight:700;color:#f16b1a}
  #sheet{position:fixed;inset:0;background:#0007;display:none;align-items:flex-end}
  #sheet .box{background:#fff;width:100%;padding:16px;border-radius:18px 18px 0 0}
  #sheet .btn{display:block;width:100%;margin-top:8px}
  #msg{position:fixed;bottom:16px;left:16px;right:16px;background:#1e293b;color:#fff;padding:12px;border-radius:10px;display:none;text-align:center}
</style>
</head>
<body>
<header>
  <b>🚚 <?= esc(session()->get('nombre')) ?></b>
  <a href="<?= base_url('salir') ?>">Salir</a>
</header>

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

const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const post = (url, obj) => fetch(url, {
  method: 'POST',
  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
  body: new URLSearchParams(obj)
}).then(r => r.json());

function aviso(t){ const m = document.getElementById('msg'); m.textContent = t; m.style.display = 'block'; setTimeout(() => m.style.display = 'none', 2500); }

async function cargar(){
  try {
    const d = await (await fetch(API + '/pedidos')).json();
    document.getElementById('lista').innerHTML = (d.data || []).map(p => {
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
          : `<div class="row"><button class="btn go" onclick="cambiar(${p.id},'Pedido en tránsito')">🚚 Salir a entregar</button></div>`}
        <div class="row"><button class="btn help" onclick="abrirAyuda(${p.id})">No pude entregar / Ayuda</button></div>
      </div>`;
    }).join('') || '<div class="card">Sin pedidos asignados 🎉</div>';
  } catch(e) { aviso('Sin conexión, reintentando...'); }
}

async function cambiar(id, estado){
  if (estado === 'Venta confirmada' && !confirm('¿Confirmas que ya entregaste el pedido?')) return;
  const r = await post(API + '/estado', {id, estado});
  aviso(r.success ? 'Estado actualizado' : 'No se pudo actualizar');
  cargar();
}

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

cargar();
setInterval(cargar, 8000);
</script>
</body>
</html>