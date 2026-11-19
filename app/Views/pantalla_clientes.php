<?php helper('whatsapp'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    
    <link rel="stylesheet" href="<?= base_url('css/clienteestilo.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <title>FRUVER · Gestión de Clientes</title>
</head>
<body>

<header class="barra-superior">
    <div class="logo-area">
        <img src="<?= base_url('img/LOGO1.png') ?>" alt="Fruver Logo">
    </div>
    <div class="buscador-header-wrap">
            <input type="text" id="busc"placeholder="Buscar cliente por nombre..." autocomplete="off">
        </div>
    
        <div class="sugerencias" id="sugerencias"></div>
    </div>
    <div class="user-actions-wrap">
        <a href="#" class="btn-user-header"><i class="fas fa-user-shield"></i> Admin</a>
        <a href="<?= site_url('menusolo') ?>" class="btn-user-header"><i class="fas fa-sign-out-alt"></i> Regresar</a>
    </div>
</header>

 <nav class="menu-navegacion">
        <a href="pantalla_ventas" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="pantalla_pedidos" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="inventario" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="pantalla_clientes" class="nav-link activo"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="pantalla_repartidores" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="pantalla_productos" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </nav>

<main class="main-container">
    <div class="header-acciones">
        <h2><i class="fas fa-users" style="color:#f16b1a;"></i> Directorio de Clientes</h2>
        <!-- espacio visual -->
    </div>
    <div class="grid-clientes" id="grid">
        <?php foreach($lista_clientes as $c): ?>
            <div class="cliente-card"
                 data-nombre="<?= strtolower($c['nombre'] . ' ' . ($c['apellido_paterno'] ?? '') . ' ' . ($c['apellido_materno'] ?? '')) ?>"
                 data-id="<?= $c['id_cliente'] ?>">
                <span class="badge-tipo <?= $c['tipo_cliente'] ?>"><?= $c['tipo_cliente'] ?></span>
                <div class="card-info">
    <h3 style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
        <?= htmlspecialchars($c['nombre']) ?> <?= htmlspecialchars($c['apellido_paterno'] ?? '') ?>
        
        <?php if(isset($c['total_pedidos']) && $c['total_pedidos'] > 5): ?>
            <span class="badge-preferencial" title="<?= $c['total_pedidos'] ?> pedidos realizados">
                <i class="fas fa-star"></i>
            </span>
        <?php endif; ?>
    </h3>
    <p><i class="fas fa-phone-alt"></i> <?= $c['tel'] ?? '—' ?></p>
    <p><i class="fas fa-id-card"></i> <?= $c['rfc'] ?? 'S/N RFC' ?></p>
    <?php
        $lugar = implode(', ', array_filter([$c['colonia'] ?? '', $c['municipio'] ?? '', $c['estado'] ?? '']));
        $tienePin = !empty($c['lat']) && !empty($c['lng']);
    ?>
    <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($lugar !== '' ? $lugar : 'No registrado') ?></p>
    <p style="font-size:.78rem; color:<?= $tienePin ? '#1d4a27' : '#b45309' ?>;">
        <i class="fas fa-map-pin"></i> <?= $tienePin ? 'Pin en el mapa' : 'Sin pin en el mapa' ?>
    </p>
</div>
                <div class="card-btns" style="display: flex; gap: 6px; align-items: center;">

    <button class="btn" style="background:#1d4a27; color:#fff;" onclick="abrirHistorial(<?= $c['id_cliente'] ?>)">
        <i class="fas fa-receipt"></i> Historial de compras
    </button>
    <button class="btn btn-edit" onclick="abrirEditar(<?= $c['id_cliente'] ?>)">
        <i class="fas fa-pen"></i> Editar
    </button>
    <button class="btn btn-del" onclick="confirmarEliminar(<?= $c['id_cliente'] ?>, '<?= addslashes(htmlspecialchars($c['nombre'])) ?>')">
        <i class="fas fa-trash"></i> Eliminar
    </button>
</div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- MODAL EDITAR CLIENTE (Premium) -->
<div id="modalEdit" class="modal-overlay">
    <div class="modal-box modal-ancho">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Editar Cliente</h3>
            <button class="modal-close" onclick="cerrarEditar()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="form-cliente" onsubmit="return false;">
                <input type="hidden" name="id_cliente" id="det-id">
                <input type="hidden" name="lat" id="inp-lat">
                <input type="hidden" name="lng" id="inp-lng">

                <div class="form-grid">
                    <div class="form-group"><label>Nombre</label><input type="text" name="nombre" id="inp-nom" placeholder="Nombre"></div>
                    <div class="form-group"><label>Apellido Paterno</label><input type="text" name="ap" id="inp-ap" placeholder="Apellido Paterno"></div>
                    <div class="form-group"><label>Apellido Materno</label><input type="text" name="am" id="inp-am" placeholder="Apellido Materno"></div>
                    <div class="form-group"><label>Teléfono</label><input type="text" name="tel" id="inp-tel" maxlength="12" placeholder="Teléfono"></div>
                    <div class="form-group"><label>RFC</label><input type="text" name="rfc" id="inp-rfc" maxlength="13" placeholder="RFC"></div>
                    <div class="form-group">
                        <label>Tipo de cliente</label>
                        <select name="tipo_cliente" id="inp-tipo" class="select-modal">
                            <option value="menudeo">Menudeo</option>
                            <option value="mayoreo">Mayoreo</option>
                        </select>
                    </div>
                </div>

                <div class="seccion-modal"><i class="fas fa-map-marker-alt"></i> Dirección</div>
                <div class="form-grid">
                    <div class="form-group"><label>Calle</label><input type="text" name="calle" id="inp-calle" placeholder="Calle"></div>
                    <div class="form-group"><label>Número</label><input type="text" name="numero" id="inp-num" maxlength="10" placeholder="Número"></div>
                    <div class="form-group"><label>Colonia</label><input type="text" name="colonia" id="inp-col" placeholder="Colonia"></div>
                    <div class="form-group"><label>Municipio</label><input type="text" name="municipio" id="inp-mun" placeholder="Municipio"></div>
                    <div class="form-group"><label>Estado</label><input type="text" name="estado" id="inp-est" placeholder="Estado"></div>
                </div>

                <div class="seccion-modal"><i class="fas fa-map-pin"></i> Ubicación en el mapa</div>
                <div class="acciones-mapa">
                    <button type="button" id="btnUbicar" class="btn-mapa"><i class="fas fa-location-crosshairs"></i> Ubicar dirección</button>
                    <button type="button" id="btnQuitarPin" class="btn-mapa sec"><i class="fas fa-eraser"></i> Quitar pin</button>
                </div>
                <div id="mensajeMapa" class="mensaje-mapa">Arrastra el pin o toca el mapa para fijar la ubicación exacta.</div>
                <div id="mapaEditar"></div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="cerrarEditar()"><i class="fas fa-times"></i> Cancelar</button>
            <button class="btn-save-modal" id="btnGuardarCliente" onclick="guardarCambios()"><i class="fas fa-save"></i> Guardar cambios</button>
        </div>
    </div>
</div>

<!-- MODAL HISTORIAL DE COMPRAS -->
<div id="modalHistorial" class="modal-overlay">
    <div class="modal-box modal-ancho" style="width:min(900px,94vw);">
        <div class="modal-header">
            <h3><i class="fas fa-receipt"></i> <span id="hist-titulo">Historial de compras</span></h3>
            <button class="modal-close" onclick="cerrarHistorial()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="hist-filtros">
                <div class="hist-busca">
                    <i class="fas fa-search"></i>
                    <input type="text" id="hist-busc" placeholder="Buscar por folio o producto..." autocomplete="off">
                </div>
                <select id="hist-estado" class="select-modal" aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                </select>
                <input type="date" id="hist-desde" class="fecha-modal" aria-label="Desde">
                <input type="date" id="hist-hasta" class="fecha-modal" aria-label="Hasta">
            </div>
            <div class="hist-chips">
                <button type="button" class="chip" data-rango="todo">Todo</button>
                <button type="button" class="chip" data-rango="hoy">Hoy</button>
                <button type="button" class="chip" data-rango="semana">Últimos 7 días</button>
                <button type="button" class="chip" data-rango="mes">Este mes</button>
                <button type="button" class="chip" data-rango="mes_pasado">Mes pasado</button>
            </div>
            <div class="hist-resumen" id="hist-resumen"></div>
            <div class="hist-tabla-wrap">
                <table class="hist-tabla">
                    <thead>
                        <tr><th>Folio</th><th>Fecha</th><th>Productos</th><th class="r">Total</th><th>Estado</th><th>Ticket</th></tr>
                    </thead>
                    <tbody id="hist-cuerpo"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR ELIMINACIÓN -->
<div id="modalConfirmar" class="modal-overlay">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <h3><i class="fas fa-trash-alt"></i> Eliminar cliente</h3>
            <button class="modal-close" onclick="cerrarConfirmar()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-confirm-body">
            <div class="icono-alerta"><i class="fas fa-exclamation-triangle"></i></div>
            <p>¿Estás seguro de eliminar a</p>
            <p class="nombre-cliente" id="confirm-nombre"></p>
            <small>Esta acción es irreversible.</small>
            <div class="confirm-btns" style="display:flex; gap:12px; justify-content:center; margin-top:1.5rem;">
                <button class="btn-confirm-del" id="btnConfirmarEliminar" style="background:#dc2626; border:none; padding:10px 25px; border-radius:50px; color:white; font-weight:bold;"><i class="fas fa-trash-alt"></i> Sí, eliminar</button>
            <button class="btn-cancel-modal" onclick="cerrarConfirmar()">Cancelar</button>
                
            </div>
        </div>
    </div>
</div>

<div id="toast"><i id="toast-icon" class="fas fa-check-circle"></i> <span id="toast-msg"></span></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const BASE = '<?= base_url() ?>';

function showToast(msg, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toast-icon');
    const span = document.getElementById('toast-msg');
    span.innerText = msg;
    if (type === 'success') {
        toast.style.background = '#1d4a27';
        icon.className = 'fas fa-check-circle';
    } else if (type === 'error') {
        toast.style.background = '#c13b1b';
        icon.className = 'fas fa-times-circle';
    } else {
        toast.style.background = '#f16b1a';
        icon.className = 'fas fa-exclamation-circle';
    }
    toast.style.display = 'flex';
    setTimeout(() => { toast.style.display = 'none'; }, 3300);
}

// BUSCADOR Y SUGERENCIAS
function filtrarYSugerir() {
    const texto = document.getElementById('busc').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.cliente-card');
    const sugerenciasDiv = document.getElementById('sugerencias');
    sugerenciasDiv.innerHTML = '';
    if (texto === '') {
        cards.forEach(c => c.style.display = '');
        sugerenciasDiv.style.display = 'none';
        return;
    }
    let coincide = false;
    let count = 0;
    cards.forEach(card => {
        const nombreData = card.dataset.nombre;
        const matches = nombreData.includes(texto);
        card.style.display = matches ? '' : 'none';
        if (matches && count < 6) {
            coincide = true;
            const nombreMostrar = card.querySelector('h3').innerText;
            const item = document.createElement('div');
            item.className = 'sugerencia-item';
            item.innerHTML = `<i class="fas fa-user-circle"></i> ${nombreMostrar}`;
            item.onclick = (e) => {
                document.getElementById('busc').value = nombreMostrar;
                sugerenciasDiv.style.display = 'none';
                cards.forEach(c => c.style.display = 'none');
                card.style.display = '';
            };
            sugerenciasDiv.appendChild(item);
            count++;
        }
    });
    sugerenciasDiv.style.display = coincide ? 'block' : 'none';
}
document.getElementById('busc').addEventListener('keyup', filtrarYSugerir);
document.addEventListener('click', (e) => {
    if (!e.target.closest('.buscador-header-wrap')) {
        document.getElementById('sugerencias').style.display = 'none';
    }
});

const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

// ===== EDITAR CLIENTE (datos, dirección y pin del mapa) =====
const CENTRO_INICIAL = [19.0414, -98.2063];   // Puebla: solo para que el mapa abra en la región correcta
const inLat = document.getElementById('inp-lat');
const inLng = document.getElementById('inp-lng');
const mensajeMapa = document.getElementById('mensajeMapa');
let mapa = null;
let pin = null;

function mostrarMapa(texto, clase) {
    mensajeMapa.textContent = texto;
    mensajeMapa.className = 'mensaje-mapa' + (clase ? ' ' + clase : '');
}

function ponerPin(lat, lng, zoom) {
    if (pin) {
        pin.setLatLng([lat, lng]);
    } else {
        pin = L.marker([lat, lng], { draggable: true }).addTo(mapa);
        pin.on('dragend', () => {
            const p = pin.getLatLng();
            guardarCoordenadas(p.lat, p.lng);
            mostrarMapa('Pin movido: ' + p.lat.toFixed(5) + ', ' + p.lng.toFixed(5) + '. Se guarda al pulsar "Guardar cambios".', 'ok');
        });
    }
    guardarCoordenadas(lat, lng);
    mapa.setView([lat, lng], zoom || mapa.getZoom());
}

function guardarCoordenadas(lat, lng) {
    inLat.value = lat.toFixed(7);
    inLng.value = lng.toFixed(7);
}

function quitarPin() {
    if (pin) { mapa.removeLayer(pin); pin = null; }
    inLat.value = '';
    inLng.value = '';
    mostrarMapa('Pin quitado. Toca el mapa o pulsa "Ubicar dirección" para poner uno nuevo.', 'aviso');
}

// El mapa solo calcula bien su tamaño cuando el modal ya está visible
function prepararMapa(lat, lng) {
    if (!mapa) {
        mapa = L.map('mapaEditar').setView(CENTRO_INICIAL, 10);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mapa);
        // Tocar el mapa pone o mueve el pin
        mapa.on('click', e => {
            ponerPin(e.latlng.lat, e.latlng.lng);
            mostrarMapa('Pin colocado: ' + e.latlng.lat.toFixed(5) + ', ' + e.latlng.lng.toFixed(5) + '. Se guarda al pulsar "Guardar cambios".', 'ok');
        });
    }
    mapa.invalidateSize();
    if (pin) { mapa.removeLayer(pin); pin = null; }

    const la = parseFloat(lat), ln = parseFloat(lng);
    if (!isNaN(la) && !isNaN(ln)) {
        ponerPin(la, ln, 17);
        mostrarMapa('Este es el pin actual. Arrástralo o toca el mapa para moverlo.', '');
    } else {
        inLat.value = '';
        inLng.value = '';
        mapa.setView(CENTRO_INICIAL, 10);
        mostrarMapa('Este cliente no tiene pin. Pulsa "Ubicar dirección" o toca el mapa.', 'aviso');
    }
}

// Búsqueda de la dirección escrita (OpenStreetMap). Se prueba de lo más preciso a lo más general.
const limpiarTxt = t => (t || '').replace(/["'“”‘’]/g, ' ').replace(/\s+/g, ' ').trim();
const limpiarColonia = t => limpiarTxt(t).replace(/^(fracc\.?|fraccionamiento|col\.?|colonia|u\.?\s?hab\.?|unidad habitacional)\s+/i, '').trim();

async function buscarEnMapa(consulta) {
    const r = await fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=mx&q=' + encodeURIComponent(consulta),
        { headers: { 'Accept': 'application/json' } });
    if (!r.ok) throw new Error('Error ' + r.status);
    return r.json();
}

async function ubicarDireccion() {
    const v = id => document.getElementById(id).value.trim();
    const calle = v('inp-calle'), numero = v('inp-num'), colonia = v('inp-col'), municipio = v('inp-mun'), estado = v('inp-est');
    if (!calle && !colonia && !municipio && !estado) {
        mostrarMapa('Escribe primero la dirección del cliente.', 'aviso');
        return;
    }

    const boton = document.getElementById('btnUbicar');
    boton.disabled = true;
    mostrarMapa('Buscando la dirección...', '');

    try {
        const col = limpiarColonia(colonia);
        const muni = /^\d+$/.test(municipio) ? '' : limpiarTxt(municipio);
        const est = limpiarTxt(estado);
        const calleNum = limpiarTxt(calle + ' ' + numero);
        const intentos = [
            { texto: [calleNum, col, muni, est],       zoom: 17, nivel: 'exacta' },
            { texto: [calleNum, muni, est],            zoom: 16, nivel: 'calle' },
            { texto: [limpiarTxt(calle), muni, est],   zoom: 16, nivel: 'calle' },
            { texto: [col, muni, est],                 zoom: 15, nivel: 'colonia' },
            { texto: [muni, est],                      zoom: 13, nivel: 'municipio' },
            { texto: [est],                            zoom: 9,  nivel: 'estado' },
        ];

        let resultado = [], usado = null;
        const probados = new Set();
        for (const intento of intentos) {
            const consulta = intento.texto.filter(s => s && s.trim()).join(', ');
            if (!consulta || probados.has(consulta)) continue;
            probados.add(consulta);
            if (probados.size > 1) await new Promise(r => setTimeout(r, 1100));   // Nominatim pide 1 consulta por segundo
            resultado = await buscarEnMapa(consulta + ', México');
            if (resultado.length) { usado = intento; break; }
        }

        if (!resultado.length) {
            mostrarMapa('No se encontró la dirección. Toca el mapa para poner el pin a mano.', 'aviso');
            return;
        }

        ponerPin(parseFloat(resultado[0].lat), parseFloat(resultado[0].lon), usado.zoom);
        const textos = {
            exacta:    ['Dirección encontrada. Revisa el pin y arrástralo si hace falta.', 'ok'],
            calle:     ['Se encontró la calle, pero no la colonia. Revisa el pin y arrástralo si hace falta.', 'ok'],
            colonia:   ['Solo se encontró la colonia. Arrastra el pin al lugar correcto.', 'aviso'],
            municipio: ['Solo se encontró el municipio. Acerca el mapa y arrastra el pin a la casa del cliente.', 'aviso'],
            estado:    ['Solo se encontró el estado. Acerca el mapa y arrastra el pin a la casa del cliente.', 'aviso'],
        };
        mostrarMapa(textos[usado.nivel][0], textos[usado.nivel][1]);
    } catch (e) {
        mostrarMapa('No se pudo consultar el mapa. Toca el mapa para poner el pin a mano.', 'aviso');
    } finally {
        boton.disabled = false;
    }
}
document.getElementById('btnUbicar').addEventListener('click', ubicarDireccion);
document.getElementById('btnQuitarPin').addEventListener('click', quitarPin);

function abrirEditar(id) {
    fetch(`${BASE}clientes/detalle/${id}`)
    .then(r => r.json())
    .then(data => {
        if (data.cliente) {
            const c = data.cliente;
            document.getElementById('det-id').value = c.id_cliente;
            document.getElementById('inp-nom').value = c.nombre || '';
            document.getElementById('inp-ap').value = c.apellido_paterno || '';
            document.getElementById('inp-am').value = c.apellido_materno || '';
            document.getElementById('inp-tel').value = c.tel || '';
            document.getElementById('inp-rfc').value = c.rfc || '';
            document.getElementById('inp-tipo').value = c.tipo_cliente === 'mayoreo' ? 'mayoreo' : 'menudeo';
            document.getElementById('inp-calle').value = c.calle || '';
            document.getElementById('inp-num').value = c.numero || '';
            document.getElementById('inp-col').value = c.colonia || '';
            document.getElementById('inp-mun').value = c.municipio || '';
            document.getElementById('inp-est').value = c.estado || '';
            document.getElementById('modalEdit').style.display = 'flex';
            setTimeout(() => prepararMapa(c.lat, c.lng), 60);
        } else showToast('No se encontraron datos', 'error');
    }).catch(() => showToast('Error al cargar detalles', 'error'));
}
function cerrarEditar() { document.getElementById('modalEdit').style.display = 'none'; }
function guardarCambios() {
    const boton = document.getElementById('btnGuardarCliente');
    boton.disabled = true;
    const formData = new FormData(document.getElementById('form-cliente'));
    fetch(`${BASE}clientes/actualizar`, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            cerrarEditar();
            showToast('Cliente actualizado correctamente', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            boton.disabled = false;
            showToast(res.msg || 'No se pudo guardar', 'error');
        }
    }).catch(() => { boton.disabled = false; showToast('Error de conexión', 'error'); });
}

// ===== HISTORIAL DE COMPRAS =====
let histPedidos = [];
let histRango = 'todo';
const histEl = {
    busc: document.getElementById('hist-busc'), estado: document.getElementById('hist-estado'),
    desde: document.getElementById('hist-desde'), hasta: document.getElementById('hist-hasta'),
    cuerpo: document.getElementById('hist-cuerpo'), resumen: document.getElementById('hist-resumen'),
};
const dinero = n => '$' + Number(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const folio = id => 'PED-' + String(id).padStart(5, '0');
const textoNormal = t => String(t || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
const aISO = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

function clasePedido(estado) {
    if (estado === 'Pedido cancelado') return 'cancelado';
    if (estado === 'Venta confirmada' || estado === 'Pedido pagado') return 'ok';
    if (estado === 'Pedido a crédito') return 'credito';
    return 'proceso';
}

function aplicarRango(rango) {
    histRango = rango;
    const hoy = new Date();
    let d = '', h = '';
    if (rango === 'hoy') { d = h = aISO(hoy); }
    else if (rango === 'semana') { const x = new Date(hoy); x.setDate(x.getDate() - 6); d = aISO(x); h = aISO(hoy); }
    else if (rango === 'mes') { d = aISO(new Date(hoy.getFullYear(), hoy.getMonth(), 1)); h = aISO(hoy); }
    else if (rango === 'mes_pasado') { d = aISO(new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1)); h = aISO(new Date(hoy.getFullYear(), hoy.getMonth(), 0)); }
    histEl.desde.value = d;
    histEl.hasta.value = h;
    filtrarHistorial();
}

function filtrarHistorial() {
    const q = textoNormal(histEl.busc.value.trim());
    const estado = histEl.estado.value;
    const desde = histEl.desde.value, hasta = histEl.hasta.value;

    // Si las fechas se escriben a mano, ningún botón rápido queda marcado
    document.querySelectorAll('.hist-chips .chip').forEach(c => c.classList.toggle('activo', c.dataset.rango === histRango));

    const lista = histPedidos.filter(p => {
        const dia = String(p.fecha).substring(0, 10);
        if (desde && dia < desde) return false;
        if (hasta && dia > hasta) return false;
        if (estado && p.estado_actual !== estado) return false;
        if (q && !(textoNormal(folio(p.id)).includes(q) || textoNormal(p.id).includes(q) || textoNormal(p.productos).includes(q))) return false;
        return true;
    });

    let total = 0, saldo = 0;
    lista.forEach(p => {
        if (p.estado_actual === 'Pedido cancelado') return;
        total += Number(p.total);
        if (p.tipo_pago === 'credito') saldo += Math.max(0, Number(p.total) - Number(p.monto_pagado));
    });
    histEl.resumen.innerHTML = lista.length
        ? `<strong>${lista.length}</strong> compra${lista.length === 1 ? '' : 's'} · Total comprado: <strong>${dinero(total)}</strong>` +
          (saldo > 0 ? ` · Saldo pendiente: <strong style="color:#b45309;">${dinero(saldo)}</strong>` : '') +
          ' <small>(sin contar canceladas)</small>'
        : '';

    if (!lista.length) {
        histEl.cuerpo.innerHTML = '<tr><td colspan="6" class="hist-vacio">' +
            (histPedidos.length ? 'Ninguna compra coincide con los filtros.' : 'Este cliente todavía no tiene compras.') + '</td></tr>';
        return;
    }

    histEl.cuerpo.innerHTML = lista.map(p => {
        const f = new Date(String(p.fecha).replace(' ', 'T'));
        const fecha = isNaN(f) ? esc(p.fecha) : f.toLocaleDateString('es-MX') + ' ' + f.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
        return `<tr class="${p.estado_actual === 'Pedido cancelado' ? 'fila-cancelada' : ''}">
            <td><strong>${folio(p.id)}</strong>${Number(p.prioritario) === 1 ? ' <span class="tag-urg">URGENTE</span>' : ''}</td>
            <td>${fecha}</td>
            <td class="prods" title="${esc(p.productos)}">${esc(p.productos || '—')}</td>
            <td class="r"><strong>${dinero(p.total)}</strong></td>
            <td><span class="est ${clasePedido(p.estado_actual)}">${esc(p.estado_actual)}</span></td>
            <td class="acc">
                <a href="${BASE}ticket/ver/${p.id}" target="_blank" title="Ver ticket"><i class="fas fa-receipt"></i> Ver</a>
                <a href="${BASE}ticket/pdf/${p.id}" target="_blank" title="Abrir PDF"><i class="fas fa-file-pdf"></i> PDF</a>
            </td>
        </tr>`;
    }).join('');
}

function abrirHistorial(id) {
    fetch(`${BASE}clientes/historial/${id}`)
    .then(r => r.json())
    .then(d => {
        if (d.status !== 'success') { showToast(d.msg || 'No se pudo cargar el historial', 'error'); return; }
        histPedidos = d.pedidos || [];
        document.getElementById('hist-titulo').textContent = 'Historial de compras · ' + d.cliente;

        // El filtro de estado solo ofrece los estados que ese cliente sí tiene
        const estados = [...new Set(histPedidos.map(p => p.estado_actual))];
        histEl.estado.innerHTML = '<option value="">Todos los estados</option>' +
            estados.map(e => `<option value="${esc(e)}">${esc(e)}</option>`).join('');
        histEl.busc.value = '';
        aplicarRango('todo');
        document.getElementById('modalHistorial').style.display = 'flex';
        histEl.busc.focus();
    })
    .catch(() => showToast('Error al cargar el historial', 'error'));
}
function cerrarHistorial() { document.getElementById('modalHistorial').style.display = 'none'; }

histEl.busc.addEventListener('input', filtrarHistorial);
histEl.estado.addEventListener('change', filtrarHistorial);
[histEl.desde, histEl.hasta].forEach(el => el.addEventListener('change', () => { histRango = ''; filtrarHistorial(); }));
document.querySelectorAll('.hist-chips .chip').forEach(c => c.addEventListener('click', () => aplicarRango(c.dataset.rango)));

// ELIMINAR CONFIRMACIÓN
let pendingDeleteId = null;
function confirmarEliminar(id, nombre) {
    pendingDeleteId = id;
    document.getElementById('confirm-nombre').innerText = nombre;
    document.getElementById('modalConfirmar').style.display = 'flex';
    const btnConfirm = document.getElementById('btnConfirmarEliminar');
    btnConfirm.onclick = () => {
        cerrarConfirmar();
        fetch(`${BASE}clientes/eliminar/${pendingDeleteId}`, { method: 'POST' })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                showToast('Cliente eliminado', 'success');
                setTimeout(() => location.reload(), 1400);
            } else showToast('Error al eliminar', 'error');
        }).catch(() => showToast('Error de conexión', 'error'));
    };
}
function cerrarConfirmar() { document.getElementById('modalConfirmar').style.display = 'none'; }

// Cerrar modales con overlay click
window.onclick = (e) => {
    if (e.target === document.getElementById('modalEdit')) cerrarEditar();
    if (e.target === document.getElementById('modalConfirmar')) cerrarConfirmar();
    if (e.target === document.getElementById('modalHistorial')) cerrarHistorial();
};

// flash mensajes session
window.addEventListener('DOMContentLoaded', () => {
    <?php if (session()->getFlashdata('flash_success')): ?>
        showToast('<?= session()->getFlashdata('flash_success') ?>', 'success');
    <?php endif; ?>
    <?php if (session()->getFlashdata('flash_error')): ?>
        showToast('<?= session()->getFlashdata('flash_error') ?>', 'error');
    <?php endif; ?>
});

</script>

<a href="<?= base_url('pantalla_rcliente') ?>" class="btn-flotante-clientes">
    <i class="fas fa-plus-circle"></i>
    <span>Nuevo Cliente</span>
</a>

</body>
</html>