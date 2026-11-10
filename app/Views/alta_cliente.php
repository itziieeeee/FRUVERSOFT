<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <title>FRUVER · Registrar Cliente</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }

        body {
            background: rgba(30, 50, 30, 0.45);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
        }

        .modal-content {
            background: white;
            width: 600px;
            max-width: 100%;
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            background: linear-gradient(to right, #1a4324, #2d5a33);
            color: white;
            padding: 22px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s;
        }
        .close-modal:hover { transform: scale(1.1); }

        .modal-body {
            padding: 30px 35px;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        /* ── Alerta de errores ── */
        .alerta-errores {
            background: #fff0f0;
            border: 1.5px solid #f5c6cb;
            color: #c0392b;
            padding: 14px 18px;
            border-radius: 14px;
            font-size: 14px;
            line-height: 1.7;
        }

        /* ── Separador de sección ── */
        .seccion {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.3px;
            color: #2d5a33;
        }
        .seccion::after {
            content: '';
            flex: 1;
            height: 1.5px;
            background: #e4f0e6;
        }

        /* ── Grids ── */
        .modal-grid   { display: grid; grid-template-columns: 1fr 1fr;     gap: 18px; }
        .modal-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; }
        .col-2 { grid-column: span 2; }

        /* ── Campos ── */
        .campo label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 0.9rem;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 8px;
        }
        .campo label i { color: #2d5a33; font-size: 0.9rem; }

        .campo input,
        .campo select {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid #e0e0e0;
            border-radius: 12px;
            font-size: 0.95rem;
            outline: none;
            color: #333;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .campo input::placeholder { color: #b8b8b8; }
        .campo input:focus,
        .campo select:focus {
            border-color: #2d5a33;
            box-shadow: 0 0 0 3px rgba(45, 90, 51, 0.1);
        }

        /* ── Botón guardar ── */
        .btn-save-modal {
            background: #2d5a33;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
            box-shadow: 0 4px 14px rgba(45, 90, 51, 0.35);
            transition: background 0.2s;
            width: 100%;
        }
        .btn-save-modal:hover { background: #1a4324; }

        /* ── Mapa ── */
        #mapaCliente {
            width: 100%;
            height: 320px;
            border-radius: 14px;
            border: 1.5px solid #e0e0e0;
            margin-top: 10px;
            z-index: 0;
        }
        .btn-ubicar {
            background: #fff;
            color: #2d5a33;
            border: 1.5px solid #2d5a33;
            padding: 11px 18px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }
        .btn-ubicar:hover { background: #e8f3e6; }
        .btn-ubicar:disabled { opacity: 0.6; cursor: wait; }
        .mensaje-mapa {
            margin-top: 10px;
            font-size: 0.85rem;
            color: #666;
        }
        .mensaje-mapa.ok { color: #1d4a27; font-weight: 600; }
        .mensaje-mapa.aviso { color: #8a5a00; font-weight: 600; }

        @media (max-width: 560px) {
            .modal-grid, .modal-grid-3 { grid-template-columns: 1fr; }
            .col-2 { grid-column: span 1; }
        }
    </style>
</head>
<body>

<div class="modal-content">

    <!-- Header -->
    <div class="modal-header">
        <div class="header-title">
            <i class="fas fa-user-plus"></i>
            <span>Registrar Nuevo Cliente</span>
        </div>
        <a href="<?= base_url('pantalla_clientes') ?>" class="close-modal" title="Cancelar">
            <i class="fas fa-times"></i>
        </a>
    </div>

    <!-- Form -->
    <form action="<?= base_url('clientes/registrar') ?>" method="post" class="modal-body">
        <?= csrf_field() ?>

        <!-- Errores de validación del servidor -->
        <?php if (!empty($errores)): ?>
            <div class="alerta-errores">
                <?php foreach ($errores as $e): ?>
                    <div>• <?= esc($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ══ DATOS PERSONALES ══ -->
        <div class="seccion"><i class="fas fa-user"></i> Datos Personales</div>

        <div class="campo">
            <label><i class="fas fa-id-card"></i> Nombre(s) *</label>
            <input type="text" name="nombre"
                   placeholder="Escribe el nombre del cliente..."
                   value="<?= esc($old_input['nombre'] ?? '') ?>"
                   required>
        </div>

        <div class="modal-grid">
            <div class="campo">
                <label>Apellido Paterno *</label>
                <input type="text" name="apellido_paterno"
                       placeholder="Ej: Hernández"
                       value="<?= esc($old_input['apellido_paterno'] ?? '') ?>"
                       required>
            </div>
            <div class="campo">
                <label>Apellido Materno</label>
                <input type="text" name="apellido_materno"
                       placeholder="Ej: García"
                       value="<?= esc($old_input['apellido_materno'] ?? '') ?>">
            </div>
        </div>

        <div class="modal-grid">
            <div class="campo">
                <label><i class="fas fa-qrcode"></i> RFC</label>
                <input type="text" name="rfc" maxlength="13"
                       placeholder="ABCD123456XYZ"
                       value="<?= esc($old_input['rfc'] ?? '') ?>"
                       oninput="this.value = this.value.toUpperCase()">
            </div>
            <div class="campo">
                <label><i class="fas fa-phone"></i> Teléfono</label>
                <input type="text" name="tel" maxlength="12"
                       placeholder="Ej: 2223489010"
                       value="<?= esc($old_input['tel'] ?? '') ?>">
            </div>
        </div>

        <div class="campo" style="max-width:48%;">
            <label><i class="fas fa-users"></i> Tipo de Cliente *</label>
            <select name="tipo_cliente" required>
                <option value="">Seleccionar...</option>
                <option value="mayoreo" <?= (($old_input['tipo_cliente'] ?? '') === 'mayoreo') ? 'selected' : '' ?>>Mayoreo</option>
                <option value="menudeo" <?= (($old_input['tipo_cliente'] ?? '') === 'menudeo') ? 'selected' : '' ?>>Menudeo</option>
            </select>
        </div>

        <!-- ══ DIRECCIÓN ══ -->
        <div class="seccion"><i class="fas fa-map-marker-alt"></i> Dirección</div>

        <div class="modal-grid-3">
            <div class="campo col-2">
                <label><i class="fas fa-road"></i> Calle *</label>
                <input type="text" name="calle"
                       placeholder="Nombre de la calle"
                       value="<?= esc($old_input['calle'] ?? '') ?>"
                       required>
            </div>
            <div class="campo">
                <label>Número *</label>
                <input type="text" name="numero"
                       placeholder="Ej: 13"
                       value="<?= esc($old_input['numero'] ?? '') ?>"
                       required>
            </div>
        </div>

        <div class="modal-grid-3">
            <div class="campo">
                <label>Colonia *</label>
                <input type="text" name="colonia"
                       placeholder="Ej: Bravo"
                       value="<?= esc($old_input['colonia'] ?? '') ?>"
                       required>
            </div>
            <div class="campo">
                <label>Municipio</label>
                <input type="text" name="municipio"
                       placeholder="Ej: Veracruz"
                       value="<?= esc($old_input['municipio'] ?? '') ?>">
            </div>
            <div class="campo">
                <label>Estado *</label>
                <input type="text" name="estado"
                       placeholder="Ej: Veracruz"
                       value="<?= esc($old_input['estado'] ?? '') ?>"
                       required>
            </div>
        </div>

        <!-- ══ UBICACIÓN EN EL MAPA ══ -->
        <div class="seccion"><i class="fas fa-map-pin"></i> Ubicación en el mapa</div>

        <div class="campo">
            <button type="button" id="btnUbicarDireccion" class="btn-ubicar">
                <i class="fas fa-location-crosshairs"></i> Ubicar dirección en el mapa
            </button>
            <div id="mensajeMapa" class="mensaje-mapa">
                Escribe la dirección y pulsa el botón. Si el pin no cae bien, arrástralo al lugar correcto.
            </div>
            <div id="mapaCliente"></div>
            <input type="hidden" name="lat" id="inputLat" value="<?= esc($old_input['lat'] ?? '') ?>">
            <input type="hidden" name="lng" id="inputLng" value="<?= esc($old_input['lng'] ?? '') ?>">
        </div>

        <!-- Botón submit -->
        <button type="submit" class="btn-save-modal">
            <i class="fas fa-save"></i> Guardar Registro de Cliente
        </button>

    </form>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    // Centro inicial: Puebla. Solo sirve para que el mapa abra en la región correcta.
    const CENTRO_INICIAL = [19.0414, -98.2063];
    const inputLat = document.getElementById('inputLat');
    const inputLng = document.getElementById('inputLng');
    const mensaje  = document.getElementById('mensajeMapa');
    const boton    = document.getElementById('btnUbicarDireccion');
    const form     = document.querySelector('form.modal-body');

    const latGuardada = parseFloat(inputLat.value);
    const lngGuardada = parseFloat(inputLng.value);
    const hayPuntoPrevio = !isNaN(latGuardada) && !isNaN(lngGuardada);

    const mapa = L.map('mapaCliente').setView(
        hayPuntoPrevio ? [latGuardada, lngGuardada] : CENTRO_INICIAL,
        hayPuntoPrevio ? 16 : 10
    );

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapa);

    let pin = null;

    function mostrar(texto, clase) {
        mensaje.textContent = texto;
        mensaje.className = 'mensaje-mapa' + (clase ? ' ' + clase : '');
    }

    function colocarPin(lat, lng, zoom) {
        if (pin) {
            pin.setLatLng([lat, lng]);
        } else {
            pin = L.marker([lat, lng], { draggable: true }).addTo(mapa);
            pin.on('dragend', function () {
                const p = pin.getLatLng();
                guardarCoordenadas(p.lat, p.lng);
                mostrar('Pin movido. Ubicación guardada: ' + p.lat.toFixed(5) + ', ' + p.lng.toFixed(5), 'ok');
            });
        }
        guardarCoordenadas(lat, lng);
        mapa.setView([lat, lng], zoom || 16);
    }

    function guardarCoordenadas(lat, lng) {
        inputLat.value = lat.toFixed(7);
        inputLng.value = lng.toFixed(7);
    }

    // También se puede tocar el mapa para poner o mover el pin.
    mapa.on('click', function (e) {
        colocarPin(e.latlng.lat, e.latlng.lng, mapa.getZoom());
        mostrar('Pin colocado. Ubicación guardada: ' + e.latlng.lat.toFixed(5) + ', ' + e.latlng.lng.toFixed(5), 'ok');
    });

    if (hayPuntoPrevio) {
        colocarPin(latGuardada, lngGuardada, 16);
    }

    function valor(nombre) {
        return (form.elements[nombre]?.value || '').trim();
    }

    // Quita comillas y espacios repetidos que confunden al buscador.
    function limpiar(texto) {
        return (texto || '').replace(/["'“”‘’]/g, ' ').replace(/\s+/g, ' ').trim();
    }

    // Las colonias suelen escribirse con abreviaturas (FRACC, COL, U HAB) que OpenStreetMap no entiende.
    function limpiarColonia(texto) {
        return limpiar(texto)
            .replace(/^(fracc\.?|fraccionamiento|col\.?|colonia|u\.?\s?hab\.?|unidad habitacional)\s+/i, '')
            .trim();
    }

    async function buscar(consulta) {
        const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=mx&q='
            + encodeURIComponent(consulta);
        const respuesta = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!respuesta.ok) throw new Error('Error ' + respuesta.status);
        return respuesta.json();
    }

    boton.addEventListener('click', async function () {
        const calle = valor('calle'), numero = valor('numero'), colonia = valor('colonia');
        const municipio = valor('municipio'), estado = valor('estado');

        if (!calle && !colonia && !municipio && !estado) {
            mostrar('Escribe primero la dirección del cliente.', 'aviso');
            return;
        }

        boton.disabled = true;
        mostrar('Buscando la dirección...', '');

        try {
            // Se prueba de lo más preciso a lo más general hasta que algo aparezca.
            // Cada intento trae su nivel de precisión para avisar al vendedor.
            const colLimpia = limpiarColonia(colonia);
            const muniLimpio = /^\d+$/.test(municipio) ? '' : limpiar(municipio); // evita códigos numéricos
            const estLimpio = limpiar(estado);
            const calleNum = limpiar(calle + ' ' + numero);

            const intentos = [
                { texto: [calleNum, colLimpia, muniLimpio, estLimpio], zoom: 17, nivel: 'exacta' },
                { texto: [calleNum, muniLimpio, estLimpio],            zoom: 16, nivel: 'calle' },
                { texto: [limpiar(calle), muniLimpio, estLimpio],      zoom: 16, nivel: 'calle' },
                { texto: [colLimpia, muniLimpio, estLimpio],           zoom: 15, nivel: 'colonia' },
                { texto: [muniLimpio, estLimpio],                      zoom: 13, nivel: 'municipio' },
                { texto: [estLimpio],                                  zoom: 9,  nivel: 'estado' },
            ];

            let resultado = [];
            let usado = null;
            const probados = new Set();

            for (const intento of intentos) {
                const consulta = intento.texto.filter(s => s && s.trim()).join(', ');
                if (!consulta || probados.has(consulta)) continue;
                probados.add(consulta);

                if (probados.size > 1) {
                    await new Promise(r => setTimeout(r, 1100)); // Nominatim pide 1 consulta por segundo
                }
                resultado = await buscar(consulta + ', México');
                if (resultado.length) {
                    usado = intento;
                    break;
                }
            }

            if (!resultado.length) {
                mostrar('No se encontró la dirección. Toca el mapa para poner el pin a mano.', 'aviso');
                return;
            }

            colocarPin(parseFloat(resultado[0].lat), parseFloat(resultado[0].lon), usado.zoom);

            const mensajes = {
                exacta:    ['Dirección encontrada. Revisa el pin y arrástralo si hace falta.', 'ok'],
                calle:     ['Se encontró la calle, pero no la colonia. Revisa el pin y arrástralo si hace falta.', 'ok'],
                colonia:   ['Solo se encontró la colonia, no la calle. Arrastra el pin al lugar correcto.', 'aviso'],
                municipio: ['Solo se encontró el municipio. Acerca el mapa y arrastra el pin a la casa del cliente.', 'aviso'],
                estado:    ['Solo se encontró el estado. Acerca el mapa y arrastra el pin a la casa del cliente.', 'aviso'],
            };
            mostrar(mensajes[usado.nivel][0], mensajes[usado.nivel][1]);
        } catch (error) {
            mostrar('No se pudo consultar el mapa. Toca el mapa para poner el pin a mano.', 'aviso');
        } finally {
            boton.disabled = false;
        }
    });
})();
</script>

</body>
</html>