<?php /* app/Views/Repartidor/partials/ayuda_gps.php
   FAB compacto (solo icono en móvil) + chip GPS como círculo de estado. */ ?>
<style>
    /* ============ FAB DE AYUDA ============ */
    .rep-fab {
        position: fixed; right: 16px; bottom: 16px; z-index: 900;
        background: #dc2626; color: #fff; border: 0;
        border-radius: 60px;
        height: 56px; min-width: 56px;
        padding: 0 20px;
        font: 700 0.9rem 'Inter','Poppins',sans-serif;
        box-shadow: 0 8px 20px rgba(220,38,38,.4);
        cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, padding 0.2s ease;
    }
    .rep-fab i { font-size: 1.15rem; }
    .rep-fab .rep-fab-texto { display: inline; }
    .rep-fab:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(220,38,38,.55); }
    .rep-fab:active { transform: scale(.95); }

    /* En móvil: solo icono, círculo perfecto */
    @media (max-width: 600px) {
        .rep-fab { padding: 0; width: 56px; }
        .rep-fab .rep-fab-texto { display: none; }
    }

    /* ============ CHIP GPS (círculo de estado) ============ */
    .rep-gps {
        position: fixed; left: 16px; bottom: 16px; z-index: 900;
        width: 56px; height: 56px;
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: var(--bg-card, #fff);
        border: 2px solid var(--borde, #e0e0e0);
        color: var(--texto-suave, #64748b);
        box-shadow: var(--sombra, 0 4px 12px rgba(0,0,0,.15));
        cursor: pointer;
        transition: background 0.3s ease, border-color 0.3s ease, color 0.3s ease, transform 0.2s ease;
        font-size: 1.2rem;
        overflow: visible;
    }
    .rep-gps:hover { transform: translateY(-2px); }
    .rep-gps:active { transform: scale(.95); }

    /* Estados del GPS */
    .rep-gps.esperando { border-color: #f59e0b; color: #f59e0b; }
    .rep-gps.activo    { border-color: #16a34a; color: #16a34a; background: #f0fdf4; }
    .rep-gps.error     { border-color: #dc2626; color: #dc2626; background: #fef2f2; }

    html[data-tema="oscuro"] .rep-gps.activo { background: rgba(22,163,74,0.15); }
    html[data-tema="oscuro"] .rep-gps.error  { background: rgba(220,38,38,0.15); }
    html[data-tema="oscuro"] .rep-gps.esperando { background: rgba(245,158,11,0.15); }

    /* Pulso cuando está activo */
    .rep-gps.activo::after {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: 50%;
        border: 2px solid #16a34a;
        animation: repPulse 2s infinite;
        pointer-events: none;
    }
    @keyframes repPulse {
        0%   { transform: scale(1);   opacity: 0.8; }
        100% { transform: scale(1.6); opacity: 0; }
    }

    /* Tooltip simple (texto al pasar el mouse en desktop) */
    .rep-gps .rep-gps-tip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-6px);
        background: var(--texto, #1e293b);
        color: var(--bg-card, #fff);
        font: 600 0.72rem 'Inter','Poppins',sans-serif;
        padding: 5px 10px;
        border-radius: 6px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }
    .rep-gps:hover .rep-gps-tip { opacity: 1; }

    /* ============ SHEET DE AYUDA ============ */
    #rep-sheet {
        position: fixed; inset: 0; background: rgba(0,0,0,.55);
        display: none; align-items: flex-end; justify-content: center; z-index: 1000;
    }
    #rep-sheet.activo { display: flex; animation: repFade 0.2s ease; }
    @keyframes repFade { from { opacity: 0; } to { opacity: 1; } }

    #rep-sheet .caja {
        background: var(--bg-card, #fff); width: 100%; max-width: 520px;
        padding: 18px; border-radius: 20px 20px 0 0;
        font-family: 'Inter','Poppins',sans-serif;
        color: var(--texto, #1e293b);
        animation: repSlide 0.25s ease;
        max-height: 85vh;
        overflow-y: auto;
    }
    @keyframes repSlide { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    #rep-sheet h3 { margin: 0 0 12px; color: var(--verde, #1d4a27); }
    html[data-tema="oscuro"] #rep-sheet h3 { color: #4ade80; }

    #rep-sheet .opcion {
        display: flex; align-items: center; gap: 10px;
        width: 100%; margin-top: 8px; padding: 14px;
        border: 1px solid var(--borde, #e0e0e0);
        background: var(--bg-suave, #f8f9fa);
        border-radius: 12px; font-size: 0.95rem; font-weight: 600;
        color: var(--texto, #1e293b); text-align: left; cursor: pointer;
        font-family: inherit; transition: 0.15s ease;
    }
    #rep-sheet .opcion i { color: var(--naranja, #f16b1a); font-size: 1.1rem; }
    #rep-sheet .opcion:hover { background: rgba(241,107,26,0.08); border-color: #f16b1a; }
    #rep-sheet .opcion:active { background: #fde8d8; border-color: #f16b1a; transform: scale(0.99); }
    #rep-sheet .cancelar {
        justify-content: center; background: transparent;
        color: var(--texto-suave, #64748b);
        border-color: var(--borde, #e0e0e0);
    }
    #rep-sheet .cancelar:hover { background: rgba(100,116,139,0.08); }

    /* ============ TOAST ============ */
    #rep-msg {
        position: fixed; left: 50%; bottom: 88px; transform: translateX(-50%);
        background: var(--verde, #1d4a27); color: #fff;
        padding: 12px 20px; border-radius: 12px;
        font: 600 0.88rem 'Inter','Poppins',sans-serif;
        display: none; z-index: 1100;
        box-shadow: 0 8px 20px rgba(0,0,0,.25);
        max-width: calc(100vw - 40px);
        text-align: center;
        animation: repMsg 0.25s ease;
    }
    #rep-msg.activo { display: block; }
    @keyframes repMsg { from { opacity: 0; transform: translate(-50%, 10px); } to { opacity: 1; transform: translate(-50%, 0); } }
    html[data-tema="oscuro"] #rep-msg { background: #4ade80; color: #0f172a; }

    /* En móvil, subimos un poco el toast para no chocar con FABs */
    @media (max-width: 600px) {
        #rep-msg { bottom: 84px; }
    }
</style>

<!-- FAB de ayuda: en desktop icono + texto, en móvil solo icono -->
<button class="rep-fab" onclick="abrirAyuda(null)" aria-label="Solicitar ayuda">
    <i class="fas fa-triangle-exclamation"></i>
    <span class="rep-fab-texto">Necesito ayuda</span>
</button>

<!-- Chip GPS: círculo con icono de mapa. Estado: esperando/activo/error -->
<button id="rep-gps" class="rep-gps esperando" onclick="repGpsClick()" aria-label="Estado de ubicación GPS" type="button">
    <i class="fas fa-map-location-dot"></i>
    <span class="rep-gps-tip" id="rep-gps-tip">Ubicación: iniciando...</span>
</button>

<!-- Sheet -->
<div id="rep-sheet" onclick="if(event.target===this)cerrarAyuda()">
    <div class="caja">
        <h3>¿Qué pasó?</h3>
        <button class="opcion" onclick="enviarAyuda('cliente_no_responde')">
            <i class="fas fa-user-clock"></i> El cliente no responde
        </button>
        <button class="opcion" onclick="enviarAyuda('direccion_incorrecta')">
            <i class="fas fa-map-location-dot"></i> Dirección incorrecta
        </button>
        <button class="opcion" onclick="enviarAyuda('cliente_no_paga')">
            <i class="fas fa-hand-holding-dollar"></i> El cliente no paga
        </button>
        <button class="opcion" onclick="enviarAyuda('producto_danado')">
            <i class="fas fa-box-open"></i> Producto dañado
        </button>
        <button class="opcion" onclick="enviarAyuda('problema_vehiculo')">
            <i class="fas fa-motorcycle"></i> Problema con el vehículo
        </button>
        <button class="opcion cancelar" onclick="cerrarAyuda()">Cancelar</button>
    </div>
</div>
<div id="rep-msg"></div>

<script>
const REP_API = "<?= base_url('repartidor/api') ?>";
let repPedidoAyuda = null;
let repUltimoEnvio = 0;
let repWatchId = null;

const repPost = (url, obj) => fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams(obj)
}).then(r => r.json());

function repAviso(texto) {
    const m = document.getElementById('rep-msg');
    m.textContent = texto;
    m.classList.add('activo');
    clearTimeout(m._t);
    m._t = setTimeout(() => m.classList.remove('activo'), 2600);
}

/* ---------- Helpers GPS (estado visual) ---------- */
function repGpsEstado(estado, tipText) {
    const el  = document.getElementById('rep-gps');
    const tip = document.getElementById('rep-gps-tip');
    el.classList.remove('esperando', 'activo', 'error');
    el.classList.add(estado);
    if (tipText) tip.textContent = tipText;
}

function repGpsClick() {
    // Reintentar / re-pedir permiso
    iniciarGPS(true);
}

/* ---------- Sheet ayuda ---------- */
function abrirAyuda(idPedido) {
    repPedidoAyuda = idPedido;
    document.getElementById('rep-sheet').classList.add('activo');
}
function cerrarAyuda() {
    document.getElementById('rep-sheet').classList.remove('activo');
}

function enviarAyuda(tipo) {
    cerrarAyuda();
    const enviar = (lat, lng) => repPost(REP_API + '/incidencia', {
        id_pedido: repPedidoAyuda ?? '', tipo: tipo, lat: lat ?? '', lng: lng ?? ''
    }).then(() => repAviso('Ayuda enviada al administrador'))
      .catch(() => repAviso('No se pudo enviar, intenta de nuevo'));

    if (!navigator.geolocation) return enviar();
    navigator.geolocation.getCurrentPosition(
        p => enviar(p.coords.latitude, p.coords.longitude),
        () => enviar(),
        {timeout: 5000}
    );
}

/* ---------- GPS ---------- */
function iniciarGPS(forzar = false) {
    if (!window.isSecureContext) {
        repGpsEstado('error', 'Ubicación requiere HTTPS');
        return;
    }
    if (!navigator.geolocation) {
        repGpsEstado('error', 'Este navegador no soporta ubicación');
        return;
    }

    if (repWatchId !== null && !forzar) return; // ya estaba corriendo
    if (repWatchId !== null) {
        navigator.geolocation.clearWatch(repWatchId);
        repWatchId = null;
    }

    repGpsEstado('esperando', 'Ubicación: esperando permiso...');

    repWatchId = navigator.geolocation.watchPosition(
        (p) => {
            const acc = Math.round(p.coords.accuracy);
            repGpsEstado('activo', 'Ubicación activa (±' + acc + ' m)');
            if (Date.now() - repUltimoEnvio < 10000) return;
            repUltimoEnvio = Date.now();
            repPost(REP_API + '/ubicacion', {
                lat: p.coords.latitude,
                lng: p.coords.longitude
            }).catch(() => {});
        },
        (e) => {
            const motivos = {
                1: 'Permiso denegado. Toca para reintentar.',
                2: 'Activa la ubicación del dispositivo.',
                3: 'Tiempo agotado. Toca para reintentar.'
            };
            repGpsEstado('error', motivos[e.code] || e.message);
        },
        {enableHighAccuracy: true, maximumAge: 10000, timeout: 20000}
    );
}
iniciarGPS();
</script>