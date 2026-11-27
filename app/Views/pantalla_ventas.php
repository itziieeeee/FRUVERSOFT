<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>FRUVER · Control de Pedidos</title>

     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/ventasestilo.css') ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
</head>
<style>
    
        :root {
            --primary-green: #1d4a27;
            --primary-orange: #f16b1a;
            --light-green: #e8f3e6;
            --dark-green: #0f3317;
            --gray-light: #f8f9fa;
            --gray-border: #e0e0e0;
            --text-dark: #333;
            --text-light: #666;
            --white: #ffffff;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --radius-md: 12px;
            --radius-sm: 8px;
        }
/* ===== NAVEGACIÓN CENTRADA ===== */
        .menu-navegacion {
            background: white;
            padding: 0.5rem 1.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 1px solid var(--gray-border);
            gap: 0.5rem;
            flex-wrap: wrap;
            box-shadow: var(--shadow-sm);
        }

        .nav-link {
            padding: 0.9rem 1.2rem;
            color: var(--text-dark);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
            border-radius: 40px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            color: var(--primary-orange);
            background: var(--light-green);
        }

        .nav-link.activo {
            color: var(--primary-orange);
            background: rgba(241, 107, 26, 0.08);
            border-bottom: 3px solid var(--primary-orange);
        }

        /* Estilos adicionales para la sección unificada */
        .seccion-unificada {
            background: var(--white);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            padding: 1.8rem;
            margin-bottom: 2rem;
        }

        .grid-2-columnas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .divider-vertical {
            border-left: 2px solid var(--gray-border);
            padding-left: 2rem;
        }

        @media (max-width: 768px) {
            .grid-2-columnas {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            .divider-vertical {
                border-left: none;
                padding-left: 0;
                border-top: 2px solid var(--gray-border);
                padding-top: 1.5rem;
                margin-top: 0.5rem;
            }
        }

        .fila-flex {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-top: 0.5rem;
        }

        .fila-flex .form-group {
            flex: 1;
            min-width: 180px;
        }

        /* ===== Repartidor y ruta de entrega ===== */
        .asignacion-ruta { margin-top: 1.2rem; }
        .asignacion-grid {
            display: grid;
            grid-template-columns: minmax(280px, 370px) 1fr;
            gap: 1.2rem;
            align-items: start;
        }
        .asignacion-lateral { display: flex; flex-direction: column; gap: 10px; min-width: 0; }
        .acciones-repartidor { display: flex; gap: 8px; flex-wrap: wrap; }
        .acciones-repartidor .btn-primary { margin: 0; flex: 1; justify-content: center; padding: .55rem .8rem; font-size: .85rem; }
        #listaAlternativasRepartidor { max-height: 190px; overflow-y: auto; }
        #mapaRuta { height: 430px; border-radius: 12px; border: 1px solid var(--gray-border); z-index: 0; }
        .mapa-vacio {
            position: absolute; inset: 0; z-index: 1000;
            display: flex; align-items: center; justify-content: center; text-align: center;
            padding: 1rem; border-radius: 12px; background: rgba(248, 249, 250, .94);
            color: #666; font-size: .92rem;
        }
        .mapa-controles { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-top: 10px; }
        .mapa-controles .btn-primary { margin: 0; padding: .55rem .9rem; font-size: .85rem; text-decoration: none; }
        .itinerario { list-style: none; margin: 0; padding: 0; max-height: 230px; overflow-y: auto; }
        .itinerario li {
            display: flex; align-items: center; gap: 8px; padding: 7px 8px; border-radius: 8px;
            border: 1px solid transparent; font-size: .85rem; line-height: 1.25;
        }
        .itinerario li[data-orden] { cursor: pointer; }
        .itinerario li[data-orden]:hover, .itinerario li.activo { background: #e8f3e6; border-color: #cfe3ca; }
        .itinerario .num {
            width: 26px; height: 26px; border-radius: 50%; background: #2d8a3e; color: #fff; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .78rem;
        }
        .itinerario li.nuevo .num { background: #f16b1a; }
        .itinerario li.fijo .num { background: #1d4a27; }
        .itinerario .km { margin-left: auto; color: #666; white-space: nowrap; font-size: .8rem; }
        .marcador-resaltado > div { transform: scale(1.3); transition: transform .15s; }
        @media (max-width: 900px) { .asignacion-grid { grid-template-columns: 1fr; } }
        .btn-guardar-pedido {
            margin-top: 1.8rem;
            text-align: right;
            border-top: 1px solid var(--gray-border);
            padding-top: 1.5rem;
        }

        .subtitulo-seccion {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-green);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light-green);
        }
        /* Modal de éxito */
.modal-exito {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease;
}

.modal-exito .modal-contenido {
    background: white;
    border-radius: 20px;
    max-width: 450px;
    width: 90%;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    animation: slideUp 0.3s ease;
}

.modal-exito .icono-exito {
    font-size: 4rem;
    color: #1d4a27;
    margin-bottom: 1rem;
}

.modal-exito h3 {
    color: #1d4a27;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.modal-exito .detalle-pedido {
    background: #e8f3e6;
    border-radius: 12px;
    padding: 1rem;
    margin: 1rem 0;
    text-align: left;
    font-size: 0.9rem;
}

.modal-exito .detalle-pedido p {
    margin: 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-exito .btn-cerrar {
    background: linear-gradient(105deg, #f16b1a, #e05a0c);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 40px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    margin-top: 1rem;
    transition: transform 0.2s;
}

.modal-exito .btn-cerrar:hover {
    transform: scale(1.02);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

</style>
<body>

<div class="barra-superior">
    <div class="logo-area">
        <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo FRUVER" width="140">
    </div>
    <div class="user-actions">
        <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> <span>Admin</span></a>
        <a href="#" class="btn-user"><i class="fas fa-bell"></i> <span>Notificaciones</span></a>
        <a href="<?= base_url('menusolo') ?>" class="btn-user"><i class="fas fa-sign-out-alt"></i> <span>Regresar</span></a>
    </div>
</div>

<?= view('partials/nav_admin') ?>

<div class="container">
    <!-- SECCIÓN UNIFICADA: Nueva venta / pedido + Gestión de estado -->
    <div class="seccion-unificada">
        <h2><i class="fa-solid fa-cart-plus"></i> Nuevo Pedido</h2>
        
        <div class="form-group" style="margin-bottom: 24px;">
            <label> Cliente</label>
            <select id="selectCliente" name="id_cliente" required style="max-width: 320px;">
    <option value="">Seleccione...</option>

    <?php foreach($clientes as $cliente): ?>
        <option value="<?= $cliente['id_cliente']; ?>">
            <?= $cliente['nombre'] . ' ' . ($cliente['apellido_paterno'] ?? '') . ' ' . ($cliente['apellido_materno'] ?? ''); ?>
        </option>
    <?php endforeach; ?>
</select>
        </div>

        <div class="grid-2-columnas">
            <!-- Columna izquierda: Registrar Productos -->
            <div>
                <div class="subtitulo-seccion"><i class="fa-solid fa-apple-alt"></i> Registrar Productos</div>
                <form class="form-producto" id="formAgregarProducto">
                    <div class="form-group">
                        <label>Producto</label>
                        <select id="selectProducto" name="id_producto" required>
                            <option value="">Seleccione producto...</option>
                            <?php foreach($productos as $prod): ?>
                                <option value="<?= $prod['id']; ?>" data-precio="<?= $prod['precio_sugerido']; ?>" data-unidad="<?= $prod['unidad_venta'] ?? ''; ?>">
                                    <?= $prod['nombre']; ?>
                                </option> 
                            <?php endforeach; ?>
                        </select>
                        <div id="hintUnidad" class="hint-unidad"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Unidad de venta</label>
                        <select id="selectUnidad" name="unidad_venta" required>
                            <option value="">Seleccione unidad...</option>
                            <?php if(!empty($unidades)): ?>
                                <?php foreach($unidades as $unidad): ?>
                                    <option value="<?= $unidad; ?>"><?= ucfirst($unidad); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Cantidad</label>
                        <input type="number" step="0.01" min="0.01" name="cantidad" placeholder="Ej: 2.5" required id="inputCantidad">
                    </div>
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-circle-plus"></i> Agregar Producto</button>
                </form>
            </div>

            <!-- Columna derecha: Gestión del Pedido (Tipo venta + Entrega) -->
            <div class="divider-vertical">
                <div class="subtitulo-seccion"><i class="fa-solid fa-truck-fast"></i> Gestión del Pedido</div>
                <form id="formEstado">
                    <div class="fila-flex">
                        <div class="form-group">
                            <label>Tipo de venta:</label>
                            <select id="selectTipoVenta" name="tipo_venta" class="select-gestion">
                                <option value="contado">Contado</option>
                                <option value="credito">Crédito</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fa-solid fa-dolly" style="color:#1d4a27;"></i> Entrega:</label>
                            <select id="selectEntrega" name="tipo_entrega" class="select-gestion">
                                <option value="tienda">Tienda</option>
                                <option value="domicilio">Domicilio</option>
                            </select>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <!-- Repartidor y ruta de entrega (aparece solo con entrega a domicilio) -->
        <div id="grupoRepartidor" class="asignacion-ruta" style="display:none;">
            <div class="subtitulo-seccion"><i class="fa-solid fa-motorcycle"></i> Repartidor y ruta de entrega</div>

            <label for="chkUrgente" style="display:flex; align-items:center; gap:8px; margin:6px 0 10px; padding:8px 12px; border:2px solid #f16b1a; border-radius:10px; background:#fff7f1; cursor:pointer; font-weight:600; color:#c2410c;">
                <input type="checkbox" id="chkUrgente" style="width:18px; height:18px; accent-color:#f16b1a;">
                <i class="fa-solid fa-bolt"></i> Pedido urgente
                <small style="font-weight:400; color:#64748b;">(se entrega primero en la ruta)</small>
            </label>

            <select id="selectRepartidor" name="id_repartidor" style="display:none;">
                <option value="">Seleccione repartidor...</option>
                <?php foreach($repartidores as $repartidor): ?>
                    <option value="<?= $repartidor['id']; ?>"><?= $repartidor['nombre'] . ' ' . $repartidor['ap_p']; ?></option>
                <?php endforeach; ?>
            </select>

            <div class="asignacion-grid">
                <aside class="asignacion-lateral">
                    <div id="panelSugerenciaRepartidor" style="display:none; padding:10px; border-radius:8px; background:#f4f8f2; border:1px solid #cfe3ca; font-size:.9rem;"></div>
                    <div class="acciones-repartidor">
                        <button type="button" id="btnMostrarOtrosRepartidores" class="btn-primary" style="display:none; background:#5b6b5d;">
                            <i class="fa-solid fa-users"></i> Elegir otro repartidor
                        </button>
                        <button type="button" id="btnSugerirRepartidor" class="btn-primary" style="background:#2d572c;">
                            <i class="fa-solid fa-route"></i> Sugerir por ruta
                        </button>
                    </div>
                    <div id="listaAlternativasRepartidor" style="display:none; padding:6px; border-radius:8px; background:#fafafa; border:1px solid #e0e0e0; font-size:.88rem;"></div>
                    <div id="resumenRuta" style="font-size:.88rem;"></div>
                    <ol id="itinerarioRuta" class="itinerario"></ol>
                </aside>

                <div class="asignacion-mapa">
                    <div id="panelMapaRuta" style="position:relative;">
                        <div id="mapaRuta"></div>
                        <div id="mapaVacio" class="mapa-vacio">
                            <span><i class="fa-solid fa-map-location-dot" style="font-size:1.6rem; color:#2d8a3e;"></i><br>Elige un cliente y un repartidor para ver la ruta de entrega.</span>
                        </div>
                    </div>
                    <div class="mapa-controles">
                        <button type="button" id="btnVerTodaRuta" class="btn-primary" style="background:#5b6b5d;" disabled>
                            <i class="fa-solid fa-expand"></i> Ver toda la ruta
                        </button>
                        <button type="button" id="btnSimularRuta" class="btn-primary" style="background:#2563eb;" disabled>
                            <i class="fa-solid fa-play"></i> Simular recorrido
                        </button>
                        <a id="btnGoogleMaps" href="#" target="_blank" rel="noopener noreferrer" class="btn-primary" style="background:#1d4a27; display:none;">
                            <i class="fa-brands fa-google"></i> Abrir en Google Maps
                        </a>
                    </div>
                    <small id="leyendaMapa" style="display:block; margin-top:6px; color:#666;"></small>
                </div>
            </div>
        </div>

        <!-- Tabla de productos en este pedido -->
        <div class="tabla-container" style="margin-top: 2rem;">
            <h3><i class="fa-solid fa-list"></i> Productos en este pedido</h3>
            <table class="tabla-productos" id="tablaProductos">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Unidad</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyProductos">
                    <!-- filas dinámicas -->
                </tbody>
            </table>
        </div>
        
        <!-- Botones de acción del pedido y plantillas frecuentes -->
        <div class="btn-guardar-pedido" style="display: flex; justify-content: flex-end; gap: 10px; align-items: center;">
            <button type="button" onclick="abrirModalCargarConfig()" class="btn-primary" style="background: linear-gradient(105deg, #2d572c, #1e3c1e);">
                <i class="fa-solid fa-folder-open"></i> Cargar Configuración
            </button>
            <button type="button" onclick="abrirModalGuardarConfig()" class="btn-primary" style="background: linear-gradient(105deg, #1d4a27, #0f3317);">
                <i class="fa-solid fa-bookmark"></i> Guardar Configuración
            </button>
            <button type="button" onclick="enviarPedido()" class="btn-primary" style="background: linear-gradient(105deg, #f16b1a, #e05a0c);">
                <i class="fa-regular fa-floppy-disk"></i> Guardar Pedido
            </button>
        </div>
    </div>
</div>

<script>

// Mostrar/ocultar campo repartidor según tipo de entrega
const selectEntrega = document.getElementById('selectEntrega');
const selectCliente = document.getElementById('selectCliente');
const grupoRepartidor = document.getElementById('grupoRepartidor');
const selectRepartidor = document.getElementById('selectRepartidor');
const btnSugerirRepartidor = document.getElementById('btnSugerirRepartidor');
const btnMostrarOtrosRepartidores = document.getElementById('btnMostrarOtrosRepartidores');
const listaAlternativasRepartidor = document.getElementById('listaAlternativasRepartidor');
const panelSugerenciaRepartidor = document.getElementById('panelSugerenciaRepartidor');

selectEntrega.addEventListener('change', function() {
    if (this.value === 'domicilio') {
        grupoRepartidor.style.display = 'block';
        solicitarSugerenciaRepartidor();
    } else {
        grupoRepartidor.style.display = 'none';
        selectRepartidor.value = '';
        btnMostrarOtrosRepartidores.style.display = 'none';
        listaAlternativasRepartidor.style.display = 'none';
        listaAlternativasRepartidor.innerHTML = '';
        panelSugerenciaRepartidor.style.display = 'none';
        panelSugerenciaRepartidor.innerHTML = '';
        document.getElementById('chkUrgente').checked = false;
        ocultarMapaRuta();
    }
});
// Marcar o quitar "urgente" recalcula la ruta (el pedido urgente pasa primero)
document.getElementById('chkUrgente').addEventListener('change', cargarPlanSalida);
selectCliente.addEventListener('change', function() {
    if (selectEntrega.value === 'domicilio') {
        solicitarSugerenciaRepartidor();
    }
});
btnSugerirRepartidor.addEventListener('click', solicitarSugerenciaRepartidor);
btnMostrarOtrosRepartidores.addEventListener('click', function() {
    const visible = listaAlternativasRepartidor.style.display === 'block';
    listaAlternativasRepartidor.style.display = visible ? 'none' : 'block';
    this.innerHTML = visible
        ? '<i class="fa-solid fa-users"></i> Elegir otro repartidor'
        : '<i class="fa-solid fa-chevron-up"></i> Ocultar repartidores';
});

listaAlternativasRepartidor.addEventListener('click', function(evento) {
    const opcion = evento.target.closest('[data-id-repartidor]');
    if (!opcion) return;

    selectRepartidor.value = opcion.dataset.idRepartidor;
    listaAlternativasRepartidor.querySelectorAll('[data-id-repartidor]').forEach(elemento => {
        elemento.style.background = '#fff';
    });
    opcion.style.background = '#e8f3e6';
    cargarPlanSalida();
});

function escaparHtml(valor) {
    return String(valor ?? '').replace(/[&<>'"]/g, caracter => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
    })[caracter]);
}

// ===== Mapa de la ruta del repartidor =====
const panelMapaRuta = document.getElementById('panelMapaRuta');
const mapaVacio = document.getElementById('mapaVacio');
const mensajeMapaVacio = mapaVacio.innerHTML;
const resumenRuta = document.getElementById('resumenRuta');
const itinerarioRuta = document.getElementById('itinerarioRuta');
const btnGoogleMaps = document.getElementById('btnGoogleMaps');
const btnVerTodaRuta = document.getElementById('btnVerTodaRuta');
const btnSimularRuta = document.getElementById('btnSimularRuta');
const leyendaMapa = document.getElementById('leyendaMapa');
let mapaRuta = null;
let capaRuta = null;
let tokenPlan = 0;
let marcadoresParadas = {};
let limitesRuta = null;
let trazoActual = [];
let carroSimulado = null;
let temporizadorSimulacion = null;
const cachePlanes = new Map(); // "idRepartidor:idCliente" -> plan, para cambiar de repartidor al instante

function ocultarMapaRuta() {
    tokenPlan++; // descarta cualquier respuesta que siga en camino
    detenerSimulacion(false);
    if (capaRuta) { capaRuta.remove(); capaRuta = null; }
    marcadoresParadas = {};
    limitesRuta = null;
    trazoActual = [];
    mapaVacio.innerHTML = mensajeMapaVacio;
    mapaVacio.style.display = 'flex';
    resumenRuta.innerHTML = '';
    itinerarioRuta.innerHTML = '';
    leyendaMapa.textContent = '';
    btnVerTodaRuta.disabled = true;
    btnSimularRuta.disabled = true;
    btnGoogleMaps.style.display = 'none';
}

function iconoMapa(contenido, fondo, tamano, opacidad) {
    return L.divIcon({
        className: '',
        iconSize: [tamano, tamano],
        iconAnchor: [tamano / 2, tamano / 2],
        popupAnchor: [0, -tamano / 2],
        html: '<div style="width:' + tamano + 'px;height:' + tamano + 'px;border-radius:50%;background:' + fondo +
              ';color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:' + Math.round(tamano * 0.45) +
              'px;border:2px solid #fff;box-shadow:0 1px 5px rgba(0,0,0,.45);opacity:' + (opacidad ?? 1) + ';">' + contenido + '</div>'
    });
}

function textoHace(minutos) {
    if (minutos === null || minutos === undefined) return 'hora desconocida';
    if (minutos < 1) return 'hace menos de 1 minuto';
    if (minutos < 60) return 'hace ' + minutos + ' min';
    if (minutos < 1440) return 'hace ' + Math.round(minutos / 60) + ' h';
    return 'hace ' + Math.round(minutos / 1440) + ' día(s)';
}

async function cargarPlanSalida() {
    const idRepartidor = selectRepartidor.value;
    const idCliente = selectCliente.value;
    if (selectEntrega.value !== 'domicilio' || !idRepartidor || !idCliente) {
        ocultarMapaRuta();
        return;
    }

    const token = ++tokenPlan;
    const urgente = document.getElementById('chkUrgente').checked;
    const clave = idRepartidor + ':' + idCliente + ':' + (urgente ? 1 : 0);
    if (cachePlanes.has(clave)) {
        dibujarPlanRuta(cachePlanes.get(clave));
        return;
    }

    mapaVacio.innerHTML = '<span><i class="fa-solid fa-spinner fa-spin" style="font-size:1.4rem;"></i><br>Calculando la ruta...</span>';
    mapaVacio.style.display = 'flex';
    resumenRuta.innerHTML = '';
    itinerarioRuta.innerHTML = '';

    try {
        const respuesta = await fetch('<?= base_url("pedido/plan_salida") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ id_repartidor: idRepartidor, id_cliente: idCliente, prioritario: urgente })
        });
        const textoRespuesta = await respuesta.text();
        let resultado;
        try { resultado = JSON.parse(textoRespuesta); } catch (e) {
            throw new Error('El servidor respondió HTTP ' + respuesta.status + ' al calcular el plan.');
        }
        if (!respuesta.ok) {
            throw new Error(resultado.message || 'El servidor respondió HTTP ' + respuesta.status + '.');
        }
        if (token !== tokenPlan) return; // se eligió otro repartidor mientras tanto

        if (resultado.status !== 'success') {
            mapaVacio.innerHTML = '<span style="color:#8a5a00;"><i class="fa-solid fa-triangle-exclamation" style="font-size:1.4rem;"></i><br>' +
                escaparHtml(resultado.message) + '</span>';
            return;
        }
        cachePlanes.set(clave, resultado.data);
        dibujarPlanRuta(resultado.data);
    } catch (error) {
        if (token !== tokenPlan) return;
        mapaVacio.innerHTML = '<span style="color:#b00020;"><i class="fa-solid fa-circle-xmark" style="font-size:1.4rem;"></i><br>' + escaparHtml(error.message || 'No se pudo cargar el mapa de la ruta.') + '</span>';
    }
}

/* 2 h 35 min / 45 min */
function formatoMinutos(n) {
    n = Math.max(0, Math.round(Number(n) || 0));
    const h = Math.floor(n / 60), m = n % 60;
    return h > 0 ? h + ' h ' + m + ' min' : m + ' min';
}

function dibujarPlanRuta(plan) {
    detenerSimulacion(false);
    const fuentes = { osrm: 'km reales por carretera', mixta: 'km por carretera (algunos estimados)', estimada: 'km estimados' };
    const ultima = plan.repartidor.ultima_ubicacion;

    // --- Resumen (columna izquierda) ---
    let html = '<strong>' + escaparHtml(plan.repartidor.nombre) + '</strong><br>' + plan.paradas.length + ' entrega(s) · <strong>' +
        Number(plan.km_total).toFixed(1) + ' km</strong> ida y vuelta' +
        (plan.combustible ? '<br>⛽ ≈ <strong>' + Number(plan.combustible.litros).toFixed(1) + ' L</strong> de gasolina · ≈ $' +
            Number(plan.combustible.costo).toFixed(2) +
            ' <small style="color:#666;">(' + Number(plan.combustible.rendimiento_km_l) + ' km/L a $' + Number(plan.combustible.precio_litro).toFixed(2) + '/L)</small>' : '') +
        '<br><small style="color:#666;">' +
        (fuentes[plan.fuente_distancia] || 'km calculados') + ' · ' +
        (ultima ? 'última ubicación ' + textoHace(ultima.minutos) : 'sin ubicación enviada') + '</small>';
    // Tiempo estimado (con ajuste por horas pico) y comparación contra el orden de captura
    if (plan.tiempo) {
        const t = plan.tiempo;
        html += '<br><i class="fa-solid fa-clock"></i> Tiempo estimado <strong>' + formatoMinutos(t.total_min) + '</strong>' +
            ' <small style="color:#666;">(manejo ' + formatoMinutos(t.traslado_min) +
            (t.transito_min > 0 ? ', incluye +' + formatoMinutos(t.transito_min) + ' por hora pico' : '') +
            ' + entregas ' + formatoMinutos(t.paradas_min) + ' · regreso ~' + escaparHtml(t.regreso) + ')</small>';
    }
    const comp = plan.comparativo;
    if (comp && comp.ahorro && plan.paradas.length > 1) {
        const a = comp.ahorro;
        html += comp.hay_ahorro
            ? '<br><i class="fa-solid fa-route" style="color:#2d6a3b;"></i> Ruta optimizada: ahorra <strong>' + Number(a.km).toFixed(1) + ' km (' + Number(a.pct_km).toFixed(1) + '%)</strong>, ' +
              formatoMinutos(a.min) + ' (' + Number(a.pct_min).toFixed(1) + '%) y $' + Number(a.costo).toFixed(2) + ' de gasolina contra el orden de captura'
            : '<br><small style="color:#666;"><i class="fa-solid fa-route"></i> ' + escaparHtml(comp.nota || 'Sin diferencia contra el orden de captura.') + '</small>';
    }
    (plan.advertencias || []).forEach(aviso => {
        html += '<br><span style="color:#8a5a00;"><i class="fa-solid fa-triangle-exclamation"></i> ' + escaparHtml(aviso) + '</span>';
    });
    if ((plan.sin_ubicacion || []).length) {
        html += '<br><small style="color:#8a5a00;">' + plan.sin_ubicacion.length +
            ' pedido(s) del repartidor no tienen ubicación y no aparecen en el mapa ni en el cálculo.</small>';
    }
    resumenRuta.innerHTML = html;

    // --- Itinerario clicable ---
    let items = '<li class="fijo"><span class="num"><i class="fa-solid fa-house"></i></span><span>Salida: Fruver</span></li>';
    plan.paradas.forEach(p => {
        const lugar = String(p.direccion || '').split(',').slice(-3).join(',').trim();
        items += '<li data-orden="' + p.orden + '" class="' + (p.es_nuevo ? 'nuevo' : '') + '" title="Clic para ver en el mapa">' +
            '<span class="num">' + p.orden + '</span>' +
            '<span><strong>' + (p.es_nuevo ? 'Esta venta' : 'Pedido #' + p.id_pedido) + '</strong>' +
            (p.prioritario ? ' <span style="background:#dc2626;color:#fff;font-size:.65rem;font-weight:700;padding:1px 7px;border-radius:50px;">URGENTE</span>' : '') +
            '<br><small>' + escaparHtml(lugar) + '</small></span>' +
            '<span class="km">+' + Number(p.km_desde_anterior).toFixed(1) + ' km' + (p.llegada ? ' · ~' + escaparHtml(p.llegada) : '') + '</span></li>';
    });
    if (plan.paradas.length) {
        items += '<li class="fijo"><span class="num"><i class="fa-solid fa-flag-checkered"></i></span><span>Regreso al local</span>' +
            '<span class="km">+' + Number(plan.regreso_km).toFixed(1) + ' km</span></li>';
    }
    itinerarioRuta.innerHTML = items;

    // --- Mapa ---
    mapaVacio.style.display = 'none';
    if (!mapaRuta) {
        mapaRuta = L.map('mapaRuta', { scrollWheelZoom: false }); // la rueda mueve la página hasta hacer clic en el mapa
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mapaRuta);
        mapaRuta.on('click', () => mapaRuta.scrollWheelZoom.enable());
        mapaRuta.on('mouseout', () => mapaRuta.scrollWheelZoom.disable());
    }
    mapaRuta.invalidateSize();
    if (capaRuta) capaRuta.remove();
    capaRuta = L.layerGroup().addTo(mapaRuta);
    marcadoresParadas = {};

    const local = [plan.local.lat, plan.local.lng];
    const limites = [local];
    const recorrido = [local];

    L.marker(local, { icon: iconoMapa('<i class="fa-solid fa-house"></i>', '#1d4a27', 36) })
        .bindPopup('<strong>Fruver</strong><br>Punto de salida y regreso').addTo(capaRuta);

    plan.paradas.forEach(parada => {
        const punto = [parada.lat, parada.lng];
        limites.push(punto);
        recorrido.push(punto);

        const titulo = parada.es_nuevo ? '<strong>Esta venta</strong>' : '<strong>Pedido #' + parada.id_pedido + '</strong>';
        const marcador = L.marker(punto, { icon: iconoMapa(parada.orden, parada.es_nuevo ? '#f16b1a' : '#2d8a3e', 32) })
            .bindPopup(titulo + '<br>' + escaparHtml(parada.direccion) +
                (parada.aproximada ? '<br><em>Ubicación aproximada (centro de su zona)</em>' : '') +
                '<br>' + Number(parada.km_desde_anterior).toFixed(1) + ' km desde la parada anterior')
            .addTo(capaRuta);
        marcador.on('click', () => marcarItinerario(parada.orden));
        marcadoresParadas[parada.orden] = marcador;
    });
    recorrido.push(local);

    // Trazo por carretera si OSRM lo dio; si no, línea recta punteada entre paradas.
    const hayTrazo = Array.isArray(plan.geometria) && plan.geometria.length > 1;
    trazoActual = hayTrazo ? plan.geometria : recorrido;
    L.polyline(trazoActual, {
        color: '#2563eb', weight: 4, opacity: 0.8, dashArray: hayTrazo ? null : '8 8'
    }).addTo(capaRuta);

    let leyenda = 'Casa: salida y regreso · Verde: entregas en orden · Naranja: esta venta';
    if (ultima) {
        const punto = [ultima.lat, ultima.lng];
        limites.push(punto);
        const antigua = ultima.minutos === null || ultima.minutos > 60;
        L.marker(punto, { icon: iconoMapa('<i class="fa-solid fa-car-side"></i>', '#2563eb', 38, antigua ? 0.55 : 1) })
            .bindPopup('<strong>' + escaparHtml(plan.repartidor.nombre) + '</strong><br>Última ubicación: ' + textoHace(ultima.minutos) +
                (antigua ? '<br><em>Puede no ser su posición actual.</em>' : ''))
            .addTo(capaRuta);
        leyenda += ' · Carro azul: última ubicación del repartidor' + (antigua ? ' (antigua)' : '');
    }
    leyendaMapa.textContent = leyenda;

    limitesRuta = L.latLngBounds(limites);
    mapaRuta.fitBounds(limitesRuta, { padding: [30, 30] });

    btnVerTodaRuta.disabled = false;
    btnSimularRuta.disabled = trazoActual.length < 2;

    const url = plan.google_maps_url || '';
    if (url.startsWith('https://www.google.com/maps/')) {
        btnGoogleMaps.href = url;
        btnGoogleMaps.style.display = 'inline-flex';
    } else {
        btnGoogleMaps.style.display = 'none';
    }
}

// --- Interactividad: itinerario <-> mapa ---
function marcarItinerario(orden) {
    itinerarioRuta.querySelectorAll('li').forEach(li => li.classList.remove('activo'));
    const item = itinerarioRuta.querySelector('li[data-orden="' + orden + '"]');
    if (item) {
        item.classList.add('activo');
        item.scrollIntoView({ block: 'nearest' });
    }
}

function resaltarMarcador(orden, activo) {
    const marcador = marcadoresParadas[orden];
    const elemento = marcador && marcador.getElement();
    if (elemento) elemento.classList.toggle('marcador-resaltado', activo);
}

function enfocarParada(orden) {
    const marcador = marcadoresParadas[orden];
    if (!marcador || !mapaRuta) return;
    mapaRuta.flyTo(marcador.getLatLng(), Math.max(mapaRuta.getZoom(), 12), { duration: 0.7 });
    marcador.openPopup();
    marcarItinerario(orden);
}

itinerarioRuta.addEventListener('click', evento => {
    const item = evento.target.closest('li[data-orden]');
    if (item) enfocarParada(item.dataset.orden);
});
itinerarioRuta.addEventListener('mouseover', evento => {
    const item = evento.target.closest('li[data-orden]');
    if (item) resaltarMarcador(item.dataset.orden, true);
});
itinerarioRuta.addEventListener('mouseout', evento => {
    const item = evento.target.closest('li[data-orden]');
    if (item) resaltarMarcador(item.dataset.orden, false);
});

btnVerTodaRuta.addEventListener('click', () => {
    if (!mapaRuta || !limitesRuta) return;
    mapaRuta.closePopup();
    mapaRuta.fitBounds(limitesRuta, { padding: [30, 30] });
});

// --- Simulación del recorrido: un carro recorre el trazo del local a las entregas y de regreso ---
function detenerSimulacion(terminoSolo) {
    if (temporizadorSimulacion) {
        clearInterval(temporizadorSimulacion);
        temporizadorSimulacion = null;
    }
    if (carroSimulado && !terminoSolo) {
        carroSimulado.remove();
        carroSimulado = null;
    }
    btnSimularRuta.innerHTML = '<i class="fa-solid fa-play"></i> Simular recorrido';
}

// Si el trazo tiene pocos puntos (línea recta de respaldo), se agregan puntos intermedios para que el carro avance suave.
function densificar(trazo) {
    if (trazo.length >= 150) return trazo;
    const salida = [];
    for (let i = 0; i < trazo.length - 1; i++) {
        for (let k = 0; k < 40; k++) {
            const t = k / 40;
            salida.push([
                trazo[i][0] + (trazo[i + 1][0] - trazo[i][0]) * t,
                trazo[i][1] + (trazo[i + 1][1] - trazo[i][1]) * t
            ]);
        }
    }
    salida.push(trazo[trazo.length - 1]);
    return salida;
}

function simularRecorrido() {
    if (temporizadorSimulacion) {
        detenerSimulacion(false);
        return;
    }
    if (!mapaRuta || trazoActual.length < 2) return;

    if (carroSimulado) { carroSimulado.remove(); carroSimulado = null; }
    mapaRuta.closePopup();
    mapaRuta.fitBounds(limitesRuta, { padding: [30, 30] });

    const puntos = densificar(trazoActual);
    carroSimulado = L.marker(puntos[0], {
        icon: iconoMapa('<i class="fa-solid fa-car-side"></i>', '#e11d48', 38),
        zIndexOffset: 1000
    }).addTo(mapaRuta);

    const ultimo = puntos.length - 1;
    const pasos = Math.min(ultimo, 300);
    let paso = 0;
    btnSimularRuta.innerHTML = '<i class="fa-solid fa-stop"></i> Detener';
    temporizadorSimulacion = setInterval(() => {
        paso++;
        carroSimulado.setLatLng(puntos[Math.round(paso * ultimo / pasos)]);
        if (paso >= pasos) detenerSimulacion(true);
    }, 25);
}
btnSimularRuta.addEventListener('click', simularRecorrido);
// Explica de dónde salieron los km: carretera real (OSRM), estimados o por zonas.
function describirCalculo(candidato, datos) {
    if (candidato.metodo !== 'coordenadas') {
        return 'calculado por zonas';
    }
    const fuentes = {
        osrm: 'km reales por carretera',
        mixta: 'km por carretera (algunos estimados)',
        estimada: 'km estimados'
    };
    const texto = fuentes[datos.configuracion?.fuente_distancia] || 'km calculados';
    return texto + (candidato.aproximado ? ' · ubicación aproximada de algún pedido' : '');
}

function mostrarAlternativasRepartidor(candidatos) {
    listaAlternativasRepartidor.innerHTML = (candidatos || []).map(candidato => {
        const distancia = candidato.distancia_extra === null
            ? 'Sin ruta'
            : '+' + Number(candidato.distancia_extra).toFixed(2) + ' km';
        const estado = candidato.disponible
            ? distancia + ' · ' + candidato.pedidos_activos + ' por salir' +
              (candidato.en_transito > 0 ? ' · ' + candidato.en_transito + ' ya en camino' : '')
            : (candidato.motivo || 'No disponible');
        const color = candidato.disponible ? '#1d4a27' : '#8a5a00';

        return '<button type="button" data-id-repartidor="' + candidato.id + '" ' +
            'style="display:block; width:100%; text-align:left; border:0; border-bottom:1px solid #ededed; background:#fff; padding:8px 6px; cursor:pointer;">' +
            '<strong>' + escaparHtml(candidato.nombre) + '</strong>' +
            '<br><small style="color:' + color + ';">' + escaparHtml(estado) + '</small>' +
            '</button>';
    }).join('');

    btnMostrarOtrosRepartidores.style.display = candidatos && candidatos.length ? 'inline-flex' : 'none';
}

async function solicitarSugerenciaRepartidor() {
    if (selectEntrega.value !== 'domicilio') return;
    cachePlanes.clear(); // una sugerencia nueva vuelve a calcular las rutas con datos frescos

    const idCliente = selectCliente.value;
    if (!idCliente) {
        panelSugerenciaRepartidor.style.display = 'block';
        panelSugerenciaRepartidor.innerHTML = '<i class="fa-solid fa-circle-info"></i> Selecciona un cliente para calcular su zona.';
        return;
    }

    btnSugerirRepartidor.disabled = true;
    btnSugerirRepartidor.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Calculando ruta...';

    try {
        const respuesta = await fetch('<?= base_url("pedido/sugerir_repartidor") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ id_cliente: idCliente })
        });
        const textoRespuesta = await respuesta.text();
        let resultado;
        try { resultado = JSON.parse(textoRespuesta); } catch (e) {
            throw new Error('El servidor respondió HTTP ' + respuesta.status + ' en la sugerencia. Revisa que tu sesión de administrador siga activa.');
        }
        if (!respuesta.ok) {
            throw new Error(resultado.message || 'El servidor respondió HTTP ' + respuesta.status + '.');
        }

        panelSugerenciaRepartidor.style.display = 'block';
        if (resultado.status !== 'success') {
            btnMostrarOtrosRepartidores.style.display = 'none';
            listaAlternativasRepartidor.style.display = 'none';
            ocultarMapaRuta();
            panelSugerenciaRepartidor.style.background = '#fff8e6';
            panelSugerenciaRepartidor.style.borderColor = '#f1d28a';
            panelSugerenciaRepartidor.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> ' + escaparHtml(resultado.message);
            return;
        }

        const datos = resultado.data;
        const recomendado = datos.recomendado;
        mostrarAlternativasRepartidor(datos.candidatos || []);
        listaAlternativasRepartidor.style.display = 'none';
        btnMostrarOtrosRepartidores.innerHTML = '<i class="fa-solid fa-users"></i> Elegir otro repartidor';

        if (recomendado) {
            selectRepartidor.value = String(recomendado.id);
            cargarPlanSalida();
            panelSugerenciaRepartidor.style.background = '#f4f8f2';
            panelSugerenciaRepartidor.style.borderColor = '#cfe3ca';
            panelSugerenciaRepartidor.innerHTML =
                '<strong><i class="fa-solid fa-check-circle" style="color:#1d4a27;"></i> Repartidor sugerido:</strong> ' +
                escaparHtml(recomendado.nombre) + ' (+' + Number(recomendado.distancia_extra).toFixed(2) + ' km)' +
                '<br><small>Zona: ' + escaparHtml(datos.destino.zona) + ' · ' + describirCalculo(recomendado, datos) +
                (recomendado.zona_base
                    ? '<br>Zona del repartidor: ' + escaparHtml(recomendado.zona_base) +
                      (recomendado.criterio === 'zona' ? ' (se prefirió por su zona)' : '')
                    : '') +
                '</small>';
        } else {
            ocultarMapaRuta();
            panelSugerenciaRepartidor.style.background = '#fff8e6';
            panelSugerenciaRepartidor.style.borderColor = '#f1d28a';
            panelSugerenciaRepartidor.innerHTML =
                '<strong><i class="fa-solid fa-triangle-exclamation"></i> Sin ruta cercana.</strong> Se agenda para la siguiente salida.' +
                '<br><small>Zona: ' + escaparHtml(datos.destino.zona) + '.</small>';
        }
    } catch (error) {
        panelSugerenciaRepartidor.style.display = 'block';
        panelSugerenciaRepartidor.style.background = '#fff0f0';
        panelSugerenciaRepartidor.style.borderColor = '#e7b8b8';
        panelSugerenciaRepartidor.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> ' + escaparHtml(error.message || 'No se pudo calcular la sugerencia.');
    } finally {
        btnSugerirRepartidor.disabled = false;
        btnSugerirRepartidor.innerHTML = '<i class="fa-solid fa-route"></i> Sugerir por ruta';
    }
}

// AGREGAR PRODUCTO
document.getElementById('formAgregarProducto').addEventListener('submit', function(e) {
    e.preventDefault();

    const selectProd = document.getElementById('selectProducto');
    const selectedOption = selectProd.options[selectProd.selectedIndex];
    const productoNombre = selectedOption.text;
    const productoId = selectProd.value;
    const precio = parseFloat(selectedOption.dataset.precio) || 0;
    const unidad = document.getElementById('selectUnidad').value;
    const cantidad = parseFloat(document.getElementById('inputCantidad').value);

    if (!productoId || !unidad || !cantidad || isNaN(cantidad) || cantidad <= 0) {
        alert("Completa todos los campos (producto, unidad y cantidad válida).");
        return;
    }

    const tbody = document.getElementById('tbodyProductos');
    const total = cantidad * precio;

    const nuevaFila = document.createElement('tr');
    nuevaFila.setAttribute('data-id', productoId);

    nuevaFila.innerHTML = `
        <td style="font-weight:500;">${productoNombre}</td>
        <td>${unidad}</td>
        <td class="cantidad-valor">${cantidad}</td>
        <td>
            <input type="number" step="0.01" value="${precio.toFixed(2)}" class="input-precio-editable precio-unitario">
        </td>
        <td>
            <input type="number" step="0.01" value="${total.toFixed(2)}" class="input-subtotal-editable subtotal-editable">
        </td>
        <td class="total-texto total-fila">$${total.toFixed(2)}</td>
        <td class="accion-botones">
            <button type="button" class="btn-danger-icon" onclick="eliminarFila(this)"><i class="fas fa-trash-alt"></i></button>
        </td>
    `;

    tbody.appendChild(nuevaFila);
    document.getElementById('formAgregarProducto').reset();
    document.getElementById('hintUnidad').innerText = '';
    recalcularTotalesFilas();
});

function recalcularTotalesFilas() {
    document.querySelectorAll('#tbodyProductos tr').forEach(fila => {
        const cantidad = parseFloat(fila.querySelector('.cantidad-valor')?.innerText) || 0;
        const precioInput = fila.querySelector('.precio-unitario');
        const subtotalInput = fila.querySelector('.subtotal-editable');
        const totalCelda = fila.querySelector('.total-fila');

        if (precioInput && subtotalInput && totalCelda) {
            const precio = parseFloat(precioInput.value) || 0;
            let totalCalculado = cantidad * precio;
            subtotalInput.value = totalCalculado.toFixed(2);
            totalCelda.innerText = `$${totalCalculado.toFixed(2)}`;
        }
    });
}

document.addEventListener('input', function(e) {
    if (e.target.classList.contains('precio-unitario') || e.target.classList.contains('subtotal-editable')) {
        const fila = e.target.closest('tr');
        const cantidad = parseFloat(fila.querySelector('.cantidad-valor')?.innerText) || 0;
        const precio = parseFloat(fila.querySelector('.precio-unitario')?.value) || 0;
        let total = cantidad * precio;
        fila.querySelector('.subtotal-editable').value = total.toFixed(2);
        fila.querySelector('.total-fila').innerText = `$${total.toFixed(2)}`;
    }
});

function eliminarFila(boton) {
    boton.closest('tr').remove();
}

// hint unidad al seleccionar producto
document.getElementById('selectProducto').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const unidadDefault = selected.dataset.unidad;
    const hintDiv = document.getElementById('hintUnidad');
    if (unidadDefault && unidadDefault.trim() !== "") {
        hintDiv.innerText = `Unidad sugerida: ${unidadDefault}`;
    } else {
        hintDiv.innerText = "";
    }
});

// =====================
// ENVIAR PEDIDO (fetch)
// =====================
async function enviarPedido() {
    const filas = document.querySelectorAll('#tbodyProductos tr');
    if (filas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Carrito vacío',
            text: 'Agrega al menos un producto antes de guardar el pedido.',
            confirmButtonColor: '#f16b1a'
        });
        return;
    }

    const idCliente = document.getElementById('selectCliente').value;
    if (!idCliente) {
        Swal.fire({
            icon: 'warning',
            title: 'Cliente no seleccionado',
            text: 'Selecciona un cliente para continuar.',
            confirmButtonColor: '#f16b1a'
        });
        return;
    }

    const tipoVenta = document.getElementById('selectTipoVenta').value;
    const tipoEntrega = document.getElementById('selectEntrega').value;
    let idRepartidor = null;
    
    if (tipoEntrega === 'domicilio') {
        idRepartidor = document.getElementById('selectRepartidor').value;
        if (!idRepartidor) {
            Swal.fire({
                icon: 'warning',
                title: 'Repartidor requerido',
                text: 'Para envío a domicilio, selecciona un repartidor.',
                confirmButtonColor: '#f16b1a'
            });
            return;
        }
    }

    const productos = [];
    let hayError = false;
    filas.forEach(fila => {
        const id_producto = fila.getAttribute('data-id');
        const unidad = fila.cells[1]?.innerText || "";
        const cantidad = parseFloat(fila.querySelector('.cantidad-valor')?.innerText) || 0;
        const precio = parseFloat(fila.querySelector('.precio-unitario')?.value) || 0;
        if (!id_producto || !unidad || cantidad <= 0) hayError = true;
        productos.push({
            id_producto: id_producto,
            unidad: unidad,
            cantidad: cantidad,
            precio_venta: precio
        });
    });

    if (hayError) {
        Swal.fire({
            icon: 'error',
            title: 'Error en productos',
            text: 'Algunos productos tienen datos incompletos.',
            confirmButtonColor: '#f16b1a'
        });
        return;
    }

    // Mostrar loading
    Swal.fire({
        title: 'Guardando pedido...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const datosEnvio = {
        id_cliente: idCliente,
        tipo_venta: tipoVenta,
        tipo_entrega: tipoEntrega,
        id_repartidor: idRepartidor,
        prioritario: tipoEntrega === 'domicilio' && document.getElementById('chkUrgente').checked,
        productos: productos
    };

    try {
        const respuesta = await fetch('<?= base_url("pedido/guardar_productos_pedido") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosEnvio)
        });
        const resultado = await respuesta.json();
        
        if (resultado.status === 'success') {
    const d = resultado.data;

    // Línea de stock: confirmado o sin stock
    const lineaStock = d.auto_confirmado
        ? `<p><i class="fas fa-check-circle" style="color:#1d4a27;"></i> <strong>Stock:</strong> <span style="color:#1d4a27; font-weight:700;">Confirmado automáticamente </span></p>`
        : `<p><i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i> <strong>Stock:</strong> <span style="color:#f59e0b;">Inventario insuficiente — pedido en espera </span></p>`;

    Swal.fire({
        icon: 'success',
        title: '¡Pedido guardado!',
        html: `
            <div style="text-align:left; background:#e8f3e6; border-radius:12px; padding:1rem; margin-top:1rem;">
                <p><i class="fas fa-receipt" style="color:#1d4a27;"></i> <strong>Folio:</strong> ${d.folio}</p>
                <p><i class="fas fa-user" style="color:#1d4a27;"></i> <strong>Cliente:</strong> ${d.cliente}</p>
                <p><i class="fas fa-tag" style="color:#1d4a27;"></i> <strong>Tipo de venta:</strong> ${d.tipo_venta === 'credito' ? 'Crédito' : 'Contado'}</p>
                <p><i class="fas fa-truck" style="color:#1d4a27;"></i> <strong>Entrega:</strong> ${d.tipo_entrega === 'domicilio' ? 'Domicilio' : 'En tienda'}</p>
                ${d.repartidor ? `<p><i class="fas fa-motorcycle" style="color:#1d4a27;"></i> <strong>Repartidor:</strong> ${d.repartidor}</p>` : ''}
                <p><i class="fas fa-dollar-sign" style="color:#1d4a27;"></i> <strong>Total:</strong> $${parseFloat(d.total).toFixed(2)}</p>
                ${lineaStock}

<div style="margin-top:16px; text-align:center;">
    ${d.whatsapp_url ? `
    <a href="${d.whatsapp_url}" target="_blank" rel="noopener noreferrer"
       style="display:inline-block; background:#25D366; color:white; padding:11px 16px; border-radius:8px; text-decoration:none; font-weight:bold; margin:4px;">
        <i class="fab fa-whatsapp"></i> Enviar texto
    </a>` : ''}

    <button type="button" onclick="imprimirTicketTermico(${d.id_pedido})"
       style="background:#111827; color:white; padding:11px 16px; border:0; border-radius:8px; font-weight:bold; margin:4px; cursor:pointer;">
        <i class="fas fa-print"></i> Imprimir ticket
    </button>

    <a href="<?= base_url('ticket/pdf') ?>/${d.id_pedido}" target="_blank"
       style="display:inline-block; background:#f16b1a; color:white; padding:11px 16px; border-radius:8px; text-decoration:none; font-weight:bold; margin:4px;">
        <i class="fas fa-file-pdf"></i> Ticket PDF
    </a>

    <button type="button" onclick="compartirTicketPDF(${d.id_pedido})"
       style="background:#1d4a27; color:white; padding:11px 16px; border:0; border-radius:8px; font-weight:bold; margin:4px; cursor:pointer;">
        <i class="fas fa-share-alt"></i> Compartir
    </button>
</div>
</div>
        `,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#f16b1a',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) location.reload();
    });

    // Al levantar la venta se imprime el ticket térmico (el botón "Imprimir ticket" sirve para reimprimir)
    imprimirTicketTermico(d.id_pedido);
}
    else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resultado.message,
                confirmButtonColor: '#f16b1a'
            });
        }
    } catch(error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo guardar el pedido. Revisa tu conexión.',
            confirmButtonColor: '#f16b1a'
        });
    }
}
// Búsqueda en tabla de pedidos (si existe en la página)
document.getElementById('btnBuscarPedido')?.addEventListener('click', function() {
    const busqueda = document.getElementById('inputBuscarPedido').value.toLowerCase();
    const filas = document.querySelectorAll('#tablaPedidosBody tr');
    filas.forEach(fila => {
        const texto = fila.innerText.toLowerCase();
        if (texto.includes(busqueda)) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
});
document.getElementById('inputBuscarPedido')?.addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('btnBuscarPedido')?.click();
    }
});

// ==========================================
// FUNCIONES DE CONFIGURACIONES FRECUENTES
// ==========================================

// 1. Guardar la configuración actual como plantilla
async function abrirModalGuardarConfig() {
    const filas = document.querySelectorAll('#tbodyProductos tr');
    if (filas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin productos',
            text: 'Agrega productos a la tabla antes de guardar la configuración.',
            confirmButtonColor: '#f16b1a'
        });
        return;
    }

    const idCliente = document.getElementById('selectCliente').value;
    if (!idCliente) {
        Swal.fire({
            icon: 'warning',
            title: 'Cliente requerido',
            text: 'Selecciona un cliente para asociar esta configuración.',
            confirmButtonColor: '#f16b1a'
        });
        return;
    }

    // Pedir el nombre de la configuración con SweetAlert2
    const { value: nombreConfig } = await Swal.fire({
        title: 'Guardar configuración frecuente',
        input: 'text',
        inputLabel: 'Nombre de la plantilla (ej.Cliente - Fruteria Don Pepe)',
        inputPlaceholder: 'Escribe un nombre...',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#f16b1a',
        inputValidator: (value) => {
            if (!value) {
                return '¡Debes escribir un nombre para la configuración!';
            }
        }
    });

    if (nombreConfig) {
        const tipoVenta = document.getElementById('selectTipoVenta').value;
        const tipoEntrega = document.getElementById('selectEntrega').value;
        const idRepartidor = document.getElementById('selectRepartidor').value || null;

        const productos = [];
        filas.forEach(fila => {
            productos.push({
                id_producto: fila.getAttribute('data-id'),
                unidad: fila.cells[1]?.innerText || "",
                cantidad: parseFloat(fila.querySelector('.cantidad-valor')?.innerText) || 0,
                precio_venta: parseFloat(fila.querySelector('.precio-unitario')?.value) || 0
            });
        });

        const datosConfig = {
            nombre_configuracion: nombreConfig,
            id_cliente: idCliente,
            tipo_venta: tipoVenta,
            tipo_entrega: tipoEntrega,
            id_repartidor: idRepartidor,
            productos: productos
        };

        try {
            const respuesta = await fetch('<?= base_url("pedido/guardar_configuracion") ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datosConfig)
            });
            const resultado = await respuesta.json();

            if (resultado.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: resultado.message,
                    confirmButtonColor: '#f16b1a'
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: resultado.message, confirmButtonColor: '#f16b1a' });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo guardar la configuración.', confirmButtonColor: '#f16b1a' });
        }
    }
}

// 2. Abrir lista de configuraciones para cargar una
async function abrirModalCargarConfig() {
    try {
        const respuesta = await fetch('<?= base_url("pedido/listar_configuraciones") ?>');
        const resultado = await respuesta.json();

        if (resultado.status !== 'success' || resultado.data.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Sin configuraciones',
                text: 'No hay configuraciones o plantillas guardadas todavía.',
                confirmButtonColor: '#f16b1a'
            });
            return;
        }

        // Crear opciones para el selector de SweetAlert2
        let opcionesHtml = '<select id="swlSelectConfig" class="swal2-select" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #d9d9d9;">';
        resultado.data.forEach(c => {
            opcionesHtml += `<option value="${c.id}">${c.nombre_configuracion} (${c.cliente_nombre || 'Público'})</option>`;
        });
        opcionesHtml += '</select>';

        const { value: idConfigSeleccionada } = await Swal.fire({
            title: 'Cargar Configuración Frecuente',
            html: opcionesHtml,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Cargar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f16b1a',
            preConfirm: () => {
                return document.getElementById('swlSelectConfig').value;
            }
        });

        if (idConfigSeleccionada) {
            cargarDatosConfiguracion(idConfigSeleccionada);
        }

    } catch (error) {
        console.error(error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las plantillas.', confirmButtonColor: '#f16b1a' });
    }
}

// 3. Obtener los detalles de la configuración seleccionada y rellenar el formulario
async function cargarDatosConfiguracion(idConfig) {
    try {
        const respuesta = await fetch(`<?= base_url("pedido/obtener_detalle_configuracion/") ?>/${idConfig}`);
        const resultado = await respuesta.json();

        if (resultado.status === 'success') {
            const config = resultado.config;
            const productos = resultado.productos;

            // Rellenar datos generales del pedido
            document.getElementById('selectCliente').value = config.id_cliente;
            document.getElementById('selectTipoVenta').value = config.tipo_venta;
            document.getElementById('selectEntrega').value = config.tipo_entrega;

            if (config.tipo_entrega === 'domicilio') {
                document.getElementById('grupoRepartidor').style.display = 'block';
                document.getElementById('selectRepartidor').value = config.id_repartidor || '';
                solicitarSugerenciaRepartidor();
            } else {
                document.getElementById('grupoRepartidor').style.display = 'none';
                document.getElementById('selectRepartidor').value = '';
                document.getElementById('panelSugerenciaRepartidor').style.display = 'none';
            }

            // Limpiar tabla actual e insertar los productos de la plantilla
            const tbody = document.getElementById('tbodyProductos');
            tbody.innerHTML = '';

            productos.forEach(prod => {
                const total = parseFloat(prod.cantidad) * parseFloat(prod.precio_unitario);

                const nuevaFila = document.createElement('tr');
                nuevaFila.setAttribute('data-id', prod.id_producto);

                nuevaFila.innerHTML = `
                    <td style="font-weight:500;">${prod.nombre_producto}</td>
                    <td>${prod.unidad}</td>
                    <td class="cantidad-valor">${prod.cantidad}</td>
                    <td>
                        <input type="number" step="0.01" value="${parseFloat(prod.precio_unitario).toFixed(2)}" class="input-precio-editable precio-unitario">
                    </td>
                    <td>
                        <input type="number" step="0.01" value="${total.toFixed(2)}" class="input-subtotal-editable subtotal-editable">
                    </td>
                    <td class="total-texto total-fila">$${total.toFixed(2)}</td>
                    <td class="accion-botones">
                        <button type="button" class="btn-danger-icon" onclick="eliminarFila(this)"><i class="fas fa-trash-alt"></i></button>
                    </td>
                `;
                tbody.appendChild(nuevaFila);
            });

            recalcularTotalesFilas();

            Swal.fire({
                icon: 'success',
                title: '¡Plantilla cargada!',
                text: `Se cargó la configuración "${config.nombre_configuracion}" correctamente.`,
                timer: 2000,
                showConfirmButton: false
            });

        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: resultado.message, confirmButtonColor: '#f16b1a' });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Error al procesar la configuración.', confirmButtonColor: '#f16b1a' });
    }
}


// ==========================================
// COMPARTIR TICKET PDF
// ==========================================
// Imprime el ticket de 58 mm en la impresora térmica usando un marco oculto (no abre otra pestaña)
function imprimirTicketTermico(idPedido) {
    const anterior = document.getElementById('marcoTicketTermico');
    if (anterior) anterior.remove();

    const marco = document.createElement('iframe');
    marco.id = 'marcoTicketTermico';
    // Fuera de la pantalla pero con tamaño real: un marco de 0x0 o oculto puede imprimir en blanco
    marco.style.cssText = 'position:fixed; left:-9999px; top:0; width:320px; height:700px; border:0;';
    // Se imprime el contenido DEL MARCO (solo el ticket), nunca la pantalla de ventas
    marco.onload = function () {
        setTimeout(function () {
            marco.contentWindow.focus();
            marco.contentWindow.print();
        }, 300);
    };
    marco.src = '<?= base_url("ticket/termico") ?>/' + idPedido + '?marco=1';
    document.body.appendChild(marco);
}

async function compartirTicketPDF(idPedido) {
    const url = '<?= base_url("ticket/pdf") ?>/' + idPedido;

    try {
        const respuesta = await fetch(url);

        if (!respuesta.ok) {
            throw new Error('No se pudo obtener el ticket PDF.');
        }

        const blob = await respuesta.blob();

        if (blob.type.includes('application/pdf')) {
            const archivo = new File(
                [blob],
                'Ticket-PED-' + String(idPedido).padStart(5, '0') + '.pdf',
                { type: 'application/pdf' }
            );

            if (navigator.canShare && navigator.canShare({ files: [archivo] })) {
                await navigator.share({
                    files: [archivo],
                    text: 'Tu ticket de compra - FRUVER'
                });
                return;
            }
        }
    } catch (error) {
        if (error.name === 'AbortError') {
            return;
        }

        console.error('Error al compartir el ticket:', error);
    }

    // Respaldo: abrir el PDF para consultarlo o descargarlo.
    window.open(url, '_blank');
}

</script>
</body>
</html>