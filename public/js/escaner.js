/**
 * FRUVER · Escáner de códigos
 *
 * Funciona con:
 *  - la cámara del celular (Safari en iPhone, Chrome en Android) o de la laptop
 *  - un lector de código de barras USB o Bluetooth (se comporta como teclado: escribe el código y da Enter)
 *
 * Lee EAN-13, EAN-8, UPC, Code128 (las etiquetas PRD-XXXXXX de los productos sin código de fábrica) y QR.
 *
 * La cámara del navegador solo funciona en HTTPS (o en localhost). Con el túnel de Cloudflare ya cumple.
 *
 * Uso:
 *   FruverEscaner.abrirCamara({
 *       titulo: 'Escanear producto',
 *       onCodigo: async (codigo) => { ...; return 'Texto que se muestra sobre la cámara'; }
 *   });
 *
 *   FruverEscaner.vincularCampo(document.getElementById('campoEscaner'), async (codigo) => { ... });
 *
 *   const r = await FruverEscaner.buscar(URL_ESCANER, codigo);   // { success, producto }
 */
(function () {
    'use strict';

    const FORMATOS = ['EAN_13', 'EAN_8', 'UPC_A', 'UPC_E', 'CODE_128', 'CODE_39', 'QR_CODE'];
    const ANTIREBOTE_MS = 2000;   // la cámara no procesa dos veces el mismo código si lo sigue viendo

    let lector = null;
    let capa = null;
    let ultimo = { codigo: '', hora: 0 };
    let contexto = null;
    let temporizadorZoom = null;

    /* ---------- estilos (se inyectan una sola vez) ---------- */
    function estilos() {
        if (document.getElementById('fe-estilos')) return;
        const css = document.createElement('style');
        css.id = 'fe-estilos';
        css.textContent = `
        .fe-capa{position:fixed;inset:0;z-index:99999;background:#000d;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:12px;font-family:system-ui,-apple-system,'Segoe UI',sans-serif}
        .fe-caja{background:#fff;border-radius:16px;width:100%;max-width:460px;max-height:96vh;overflow:auto;padding:14px;box-shadow:0 10px 40px #0008}
        .fe-cab{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
        .fe-cab b{font-size:1.05rem;color:#1d4a27}
        .fe-x{border:0;background:#e2e8f0;border-radius:50%;width:34px;height:34px;font-size:1.1rem;cursor:pointer}
        #fe-lector{width:100%;min-height:200px;border-radius:12px;overflow:hidden;background:#111}
        #fe-lector video{width:100%!important;border-radius:12px}
        .fe-zoom{display:flex;align-items:center;gap:10px;margin-top:10px;font-size:.9rem;color:#334155}
        .fe-zoom input{flex:1}
        .fe-estado{margin-top:10px;padding:10px 12px;border-radius:10px;background:#f1f5f9;color:#334155;font-size:.92rem;min-height:44px}
        .fe-estado.ok{background:#dcfce7;color:#14532d}
        .fe-estado.err{background:#fee2e2;color:#991b1b}
        .fe-manual{display:flex;gap:8px;margin-top:10px}
        .fe-manual input{flex:1;padding:10px;border:1px solid #cbd5e1;border-radius:10px;font-size:1rem}
        .fe-manual button{border:0;background:#1d4a27;color:#fff;border-radius:10px;padding:0 16px;font-weight:600;cursor:pointer}
        .fe-ayuda{font-size:.78rem;color:#64748b;margin-top:8px}
        `;
        document.head.appendChild(css);
    }

    /* ---------- sonido y vibración al leer ---------- */
    function aviso(ok) {
        try {
            if (navigator.vibrate) navigator.vibrate(ok ? 60 : [80, 60, 80]);
            const AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return;
            const ac = aviso.ac || (aviso.ac = new AC());
            const o = ac.createOscillator();
            const g = ac.createGain();
            o.type = 'sine';
            o.frequency.value = ok ? 1100 : 300;
            g.gain.value = 0.08;
            o.connect(g); g.connect(ac.destination);
            o.start();
            o.stop(ac.currentTime + (ok ? 0.12 : 0.25));
        } catch (e) { /* sin sonido, no pasa nada */ }
    }

    function estado(texto, tipo) {
        const el = document.getElementById('fe-estado');
        if (!el) return;
        el.className = 'fe-estado' + (tipo ? ' ' + tipo : '');
        el.textContent = texto;
    }

    /* ---------- procesar un código leído (cámara, campo o escritura manual) ---------- */
    async function procesar(codigo, alUsar, conAntirrebote) {
        codigo = String(codigo || '').trim();
        if (!codigo) return;

        // Solo la cámara necesita esto: sigue viendo el mismo código en cada fotograma
        const ahora = Date.now();
        if (conAntirrebote && codigo === ultimo.codigo && ahora - ultimo.hora < ANTIREBOTE_MS) return;
        ultimo = { codigo, hora: ahora };

        try {
            const texto = await alUsar(codigo);
            aviso(true);
            if (document.getElementById('fe-estado')) estado(texto || ('Leído: ' + codigo), 'ok');
        } catch (e) {
            aviso(false);
            if (document.getElementById('fe-estado')) estado(e && e.message ? e.message : 'No se pudo procesar el código.', 'err');
        }
    }

    /* ---------- cámara ---------- */
    function mensajeErrorCamara(e) {
        const nombre = e && (e.name || e.message || String(e));
        if (!window.isSecureContext) {
            return 'La cámara solo se puede usar en una conexión segura (HTTPS). Abre el sistema con el enlace de Cloudflare.';
        }
        if (/NotAllowed|Permission/i.test(nombre)) {
            return 'Permiso de cámara denegado. Actívalo en los ajustes del navegador para este sitio y vuelve a intentar.';
        }
        if (/NotFound|Devices/i.test(nombre)) return 'No se encontró una cámara en este dispositivo.';
        if (/NotReadable|TrackStart/i.test(nombre)) return 'La cámara está en uso por otra aplicación.';
        return 'No se pudo abrir la cámara: ' + nombre;
    }

    /* Zoom: real si la cámara lo permite (Android), digital propio en iPhone */
    function prepararZoom() {
        try {
            const video = document.querySelector('#fe-lector video');
            const pista = video && video.srcObject && video.srcObject.getVideoTracks()[0];
            if (!video || !pista) return;
            const cap = pista.getCapabilities ? pista.getCapabilities() : null;

            const caja = document.createElement('div');
            caja.className = 'fe-zoom';
            caja.innerHTML = '<span>Zoom</span><input type="range" aria-label="Zoom de la cámara">';
            const rango = caja.querySelector('input');
            document.getElementById('fe-lector').insertAdjacentElement('afterend', caja);

            if (cap && cap.zoom) {
                const min = cap.zoom.min, max = cap.zoom.max;
                rango.min = min; rango.max = max; rango.step = cap.zoom.step || 0.1;
                rango.value = Math.min(max, Math.max(min, min + (max - min) * 0.25));
                const aplicar = v => pista.applyConstraints({ advanced: [{ zoom: parseFloat(v) }] }).catch(() => {});
                rango.addEventListener('input', () => aplicar(rango.value));
                aplicar(rango.value);
            } else {
                zoomDigital(video, rango);
            }
        } catch (e) { /* sin zoom disponible: se usa la cámara normal */ }
    }

    /* Zoom digital: amplía el centro de la imagen y lo decodifica con un lector auxiliar */
    function zoomDigital(video, rango) {
        // Empieza en 2×: se lee desde donde el iPhone aún enfoca (unos 20 cm)
        rango.min = 1; rango.max = 4; rango.step = 0.1; rango.value = 2;
        let zoom = 2, ocupado = false;
        video.style.transform = 'scale(2)';
        video.style.transformOrigin = 'center center';

        const aux = document.createElement('div');
        aux.id = 'fe-aux';
        aux.style.display = 'none';
        document.getElementById('fe-lector').insertAdjacentElement('afterend', aux);
        const lectorAux = new Html5Qrcode('fe-aux', {
            formatsToSupport: FORMATOS.map(f => Html5QrcodeSupportedFormats[f]).filter(f => f !== undefined),
            verbose: false
        });

        rango.addEventListener('input', () => {
            zoom = parseFloat(rango.value);
            video.style.transform = 'scale(' + zoom + ')';
            video.style.transformOrigin = 'center center';
        });

        const lienzo = document.createElement('canvas');
        const ctx = lienzo.getContext('2d');
        temporizadorZoom = setInterval(async () => {
            if (ocupado || zoom <= 1.05 || !video.videoWidth || !lector) return;
            ocupado = true;
            try {
                const w = video.videoWidth / zoom, h = video.videoHeight / zoom;
                const x = (video.videoWidth - w) / 2, y = (video.videoHeight - h) / 2;
                lienzo.width = Math.min(1280, Math.round(w * 2));
                lienzo.height = Math.round(lienzo.width * h / w);
                ctx.imageSmoothingQuality = 'high';
                ctx.drawImage(video, x, y, w, h, 0, 0, lienzo.width, lienzo.height);
                const blob = await new Promise(r => lienzo.toBlob(r, 'image/jpeg', 0.9));
                if (!blob) return;
                const texto = await lectorAux.scanFile(new File([blob], 'f.jpg', { type: 'image/jpeg' }), false);
                if (texto) procesar(texto, contexto && contexto.onCodigo, true);
            } catch (e) { /* sin código en este fotograma: es lo normal */ }
            finally { ocupado = false; }
        }, 300);
    }

    async function abrirCamara(opciones) {
        opciones = opciones || {};
        estilos();
        await cerrar();
        contexto = opciones;

        capa = document.createElement('div');
        capa.className = 'fe-capa';
        capa.innerHTML = `
            <div class="fe-caja">
                <div class="fe-cab"><b></b><button type="button" class="fe-x" aria-label="Cerrar">&times;</button></div>
                <div id="fe-lector"></div>
                <div class="fe-estado" id="fe-estado">Apunta la cámara al código de barras.</div>
                <form class="fe-manual" id="fe-manual" autocomplete="off">
                    <input type="text" id="fe-texto" inputmode="text" placeholder="O escribe el código" aria-label="Código">
                    <button type="submit">Buscar</button>
                </form>
                <div class="fe-ayuda">Coloca el código dentro del recuadro. Si está lejos, usa el zoom; evita los reflejos.</div>
            </div>`;
        capa.querySelector('b').textContent = opciones.titulo || 'Escanear código';
        document.body.appendChild(capa);

        capa.querySelector('.fe-x').addEventListener('click', cerrar);
        capa.addEventListener('click', e => { if (e.target === capa) cerrar(); });
        capa.querySelector('#fe-manual').addEventListener('submit', e => {
            e.preventDefault();
            const campo = document.getElementById('fe-texto');
            const valor = campo.value;
            campo.value = '';
            procesar(valor, opciones.onCodigo, false);
        });

        if (typeof Html5Qrcode === 'undefined') {
            estado('No se cargó el lector de la cámara. Recarga la página.', 'err');
            return;
        }
        if (!window.isSecureContext || !(navigator.mediaDevices && navigator.mediaDevices.getUserMedia)) {
            estado(mensajeErrorCamara(null), 'err');
            return;
        }

        try {
            const formatos = FORMATOS.map(f => Html5QrcodeSupportedFormats[f]).filter(f => f !== undefined);
            lector = new Html5Qrcode('fe-lector', { formatsToSupport: formatos, useBarCodeDetectorIfSupported: true, verbose: false });
            await lector.start(
                { facingMode: 'environment' },
                {
                    fps: 12,
                    // Recuadro ancho: los códigos de barras son horizontales
                    qrbox: (w, h) => ({ width: Math.floor(Math.min(w * 0.92, 380)), height: Math.floor(Math.min(h * 0.55, 170)) }),
                    // Más resolución: un código lejano ocupa más píxeles dentro del recuadro
                    videoConstraints: {
                        facingMode: 'environment',
                        width: { ideal: 1920 },
                        height: { ideal: 1080 },
                        advanced: [{ focusMode: 'continuous' }]
                    }
                },
                texto => procesar(texto, opciones.onCodigo, true),
                () => { /* fotograma sin código: es lo normal */ }
            );
            prepararZoom();
        } catch (e) {
            estado(mensajeErrorCamara(e), 'err');
        }
    }

    async function cerrar() {
        if (temporizadorZoom) { clearInterval(temporizadorZoom); temporizadorZoom = null; }
        const l = lector;
        lector = null;
        if (l) {
            try { await l.stop(); } catch (e) { /* ya estaba detenido */ }
            try { l.clear(); } catch (e) { /* nada que limpiar */ }
        }
        if (capa) { capa.remove(); capa = null; }
        const alCerrar = contexto && contexto.onCerrar;
        contexto = null;
        if (typeof alCerrar === 'function') alCerrar();
    }

    /* ---------- lector USB / Bluetooth: campo de texto + Enter ---------- */
    function vincularCampo(campo, alUsar) {
        if (!campo) return;
        campo.addEventListener('keydown', e => {
            if (e.key !== 'Enter') return;
            e.preventDefault();               // evita enviar formularios por accidente
            const valor = campo.value;
            campo.value = '';
            procesar(valor, alUsar, false);
            campo.focus();
        });
    }

    /* ---------- consulta al servidor ---------- */
    async function buscar(urlBase, codigo) {
        const r = await fetch(urlBase + '?codigo=' + encodeURIComponent(codigo), {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store',
            credentials: 'same-origin'
        });
        let datos = null;
        try { datos = await r.json(); } catch (e) { /* respuesta que no es JSON (por ejemplo, sesión vencida) */ }
        if (!datos) throw new Error('El servidor no respondió bien. Inicia sesión de nuevo.');
        if (!datos.success) throw new Error(datos.message || 'Producto no encontrado.');
        return datos;
    }

    window.FruverEscaner = { abrirCamara, cerrar, vincularCampo, buscar, procesar, aviso };
})();
