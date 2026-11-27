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

    <style>
        /* ===== Buscador Header ===== */
        .buscador-header-wrap {
            position: relative;
            flex: 1;
            max-width: 520px;
            margin: 0 1.5rem;
        }
        .buscador-header-wrap .buscador-icono {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa39c;
            pointer-events: none;
            font-size: .95rem;
        }
        .buscador-header-wrap input {
            width: 100%;
            padding: 12px 40px 12px 42px;
            border: 1.5px solid #e2e6e0;
            border-radius: 50px;
            background: #fff;
            font-size: .95rem;
            color: #1d4a27;
            transition: border-color .2s, box-shadow .2s;
        }
        .buscador-header-wrap input:focus {
            outline: none;
            border-color: #1d4a27;
            box-shadow: 0 0 0 3px rgba(29,74,39,.12);
        }
        .buscador-limpiar {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: #eef1ec;
            color: #1d4a27;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
        }
        .buscador-limpiar:hover { background: #d8ded6; }

        .sugerencias {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #e2e6e0;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(0,0,0,.10);
            max-height: 320px;
            overflow-y: auto;
            display: none;
            z-index: 1200;
            padding: 6px;
        }
        .sugerencias.visible { display: block; }
        .sugerencia-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            cursor: pointer;
            color: #1d4a27;
            font-size: .92rem;
            transition: background .15s;
        }
        .sugerencia-item:hover,
        .sugerencia-item.activo { background: #eef5ec; }
        .sugerencia-item i { color: #f16b1a; }
        .sugerencia-item mark {
            background: rgba(241,107,26,.18);
            color: inherit;
            padding: 0 2px;
            border-radius: 3px;
        }

        .buscador-contador {
            position: absolute;
            right: 14px;
            bottom: -22px;
            font-size: .78rem;
            color: #6b7a6e;
            background: #fff;
            padding: 2px 10px;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }

        /* ===== Grid y tarjetas ===== */
        .grid-clientes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.2rem;
            padding: 1.2rem 0;
        }
        .cliente-card {
            background: #fff;
            border-radius: 18px;
            padding: 1.2rem 1.2rem 1rem;
            box-shadow: 0 6px 18px rgba(0,0,0,.06);
            border: 1px solid #eef1ec;
            position: relative;
            transition: transform .2s, box-shadow .2s;
            animation: cardIn .35s ease both;
        }
        .cliente-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 30px rgba(29,74,39,.12);
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .estado-vacio {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7a6e;
        }
        .estado-vacio i { font-size: 2.4rem; color: #d8ded6; margin-bottom: .6rem; display: block; }
        .estado-vacio strong { color: #1d4a27; }

        .card-btns {
            display: flex;
            gap: 6px;
            align-items: center;
            margin-top: .9rem;
            padding-top: .9rem;
            border-top: 1px dashed #e6ebe4;
            flex-wrap: wrap;
        }

        /* ===== REDISEÑO DEL HISTORIAL ===== */
        
        /* Barra de búsqueda y filtros */
        .hist-filtros {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 12px;
        }
        .hist-busca {
            position: relative;
            flex: 1;
            min-width: 200px;
        }
        .hist-busca i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa39c;
            font-size: 0.9rem;
            pointer-events: none;
        }
        .hist-busca input {
            width: 100%;
            padding: 10px 14px 10px 38px;
            border: 1.5px solid #e2e6e0;
            border-radius: 50px;
            font-size: 0.9rem;
            color: #1d4a27;
            background: #fff;
            transition: border-color .2s;
        }
        .hist-busca input:focus {
            outline: none;
            border-color: #1d4a27;
        }
        .hist-filtros select.select-modal,
        .hist-filtros input.fecha-modal {
            width: auto;
            padding: 10px 14px;
            border: 1.5px solid #e2e6e0;
            border-radius: 50px;
            font-size: 0.9rem;
            color: #1d4a27;
            background: #fff;
            outline: none;
        }
        .hist-filtros select.select-modal:focus,
        .hist-filtros input.fecha-modal:focus {
            border-color: #1d4a27;
        }

        /* Chips */
        .hist-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 16px;
        }
        .hist-chips .chip {
            background: #f8faf8;
            border: 1px solid #e2e6e0;
            color: #1d4a27;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
        }
        .hist-chips .chip:hover {
            background: #eef5ec;
            border-color: #c2d1c4;
        }
        .hist-chips .chip.activo {
            background: #1d4a27;
            color: #fff;
            border-color: #1d4a27;
        }

        /* Métricas (Resumen) */
        .hist-resumen {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .hist-metrica {
            flex: 1;
            min-width: 120px;
            background: #f8faf8;
            border: 1px solid #eef1ec;
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            flex-direction: column;
        }
        .hist-metrica small {
            color: #6b7a6e;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .hist-metrica strong {
            font-size: 1.15rem;
            color: #1d4a27;
        }
        .hist-metrica.alerta strong { color: #b45309; }
        .hist-metrica.vacia { display: none; }

        /* Tabla */
        .hist-tabla-wrap {
            border-radius: 12px;
            border: 1px solid #eef1ec;
            overflow: auto;
            background: #fff;
        }
        .hist-tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }
        .hist-tabla th {
            background: #f8faf8;
            color: #4a5c4e;
            font-weight: 700;
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #eef1ec;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .hist-tabla td {
            padding: 14px 16px;
            border-bottom: 1px solid #eef1ec;
            color: #1d4a27;
        }
        .hist-tabla tr:last-child td { border-bottom: none; }
        .hist-tabla tr:hover td { background: #fcfdfc; }
        .hist-tabla td.r { text-align: right; }
        .hist-tabla td.prods {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #4a5c4e;
        }
        
        /* Botones en la tabla */
        .hist-tabla td.acc {
            display: flex;
            gap: 8px;
        }
        .hist-tabla td.acc a {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            background: #f0f4f0;
            color: #1d4a27;
            transition: background .2s;
        }
        .hist-tabla td.acc a:hover { background: #d8e6da; }
        .hist-tabla .tag-urg {
            background: #c13b1b;
            color: #fff;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
            margin-left: 6px;
            vertical-align: middle;
        }
        .hist-vacio {
            text-align: center;
            padding: 30px !important;
            color: #9aa39c;
            font-style: italic;
        }

        /* Accesibilidad */
        button:focus-visible,
        a:focus-visible,
        input:focus-visible,
        select:focus-visible {
            outline: 2px solid #f16b1a;
            outline-offset: 2px;
        }

        /* ===== Buscador: la lupa queda encima de la caja de texto =====
           clienteestilo.css pone backdrop-filter al input (lo pinta encima del icono) y vuelve
           blancos y absolutos todos los <i> del buscador; aquí se corrige cada uno. */
        .buscador-header-wrap input { position: relative; z-index: 1; }
        .buscador-header-wrap .buscador-icono {
            left: 16px; right: auto; z-index: 2;
            color: #6b7a6e; font-size: 1rem;
        }
        .buscador-limpiar { z-index: 3; }
        .buscador-limpiar[hidden] { display: none; }
        .buscador-header-wrap .buscador-limpiar i {
            position: static; transform: none; color: #1d4a27; font-size: .85rem;
        }
        .buscador-header-wrap .sugerencias { z-index: 20; }
        .buscador-header-wrap .sugerencias i {
            position: static; transform: none; color: #f16b1a; font-size: 1rem;
        }

        /* ===== Tarjetas: ancho que cabe en cualquier pantalla ===== */
        .grid-clientes { grid-template-columns: repeat(auto-fill, minmax(min(300px, 100%), 1fr)); }
        .cliente-card .card-info h3 { padding-right: 96px; overflow-wrap: anywhere; }
        .cliente-card .card-info p { overflow-wrap: anywhere; }

        /* Botones: "Historial de compras" a lo ancho y debajo Editar / Eliminar */
        .card-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .card-btns .btn {
            flex: none;
            width: 100%;
            min-height: 42px;
            padding: .6rem .9rem;
            margin: 0;
            white-space: nowrap;
            font-size: .84rem;
        }
        .card-btns .btn-historial {
            grid-column: 1 / -1;
            background: #1d4a27;
            color: #fff;
        }
        .card-btns .btn-historial:hover { background: #2a5e35; color: #fff; }

        /* ===== Celular ===== */
        @media (max-width: 780px) {
            .barra-superior { gap: .8rem; padding: .8rem 1rem; }
            .buscador-header-wrap { margin: 0; width: 100%; max-width: 100%; }
            .user-actions-wrap { display: flex; justify-content: center; gap: .6rem; flex-wrap: wrap; }
            .main-container { padding: 1rem 1rem 96px; }   /* espacio para el botón flotante "Nuevo Cliente" */
            .header-acciones h2 { font-size: 1.3rem; }
        }
        @media (max-width: 400px) {
            .cliente-card { padding: 1rem .9rem .9rem; }
            .cliente-card .card-info h3 { font-size: 1.1rem; padding-right: 84px; }
            .card-btns .btn { font-size: .8rem; padding: .55rem .6rem; }
        }
    </style>
</head>
<body>

<header class="barra-superior">
    <div class="logo-area">
        <img src="<?= base_url('img/LOGO1.png') ?>" alt="Fruver Logo">
    </div>

    <div class="buscador-header-wrap" role="search">
        <i class="fas fa-search buscador-icono"></i>
        <input type="text" id="busc"
               placeholder="Buscar cliente por nombre, teléfono o RFC..."
               autocomplete="off"
               aria-label="Buscar cliente"
               aria-controls="sugerencias"
               aria-expanded="false">
        <button type="button" id="buscLimpiar" class="buscador-limpiar" aria-label="Limpiar búsqueda" hidden>
            <i class="fas fa-times"></i>
        </button>
        <div class="sugerencias" id="sugerencias" role="listbox"></div>
        <div class="buscador-contador" id="buscContador" hidden></div>
    </div>

    <div class="user-actions-wrap">
        <a href="#" class="btn-user-header"><i class="fas fa-user-shield"></i> Admin</a>
        <a href="<?= site_url('menusolo') ?>" class="btn-user-header"><i class="fas fa-sign-out-alt"></i> Regresar</a>
    </div>
</header>

 <?= view('partials/nav_admin') ?>

<main class="main-container">
    <div class="header-acciones">
        <h2><i class="fas fa-users" style="color:#f16b1a;"></i> Directorio de Clientes</h2>
    </div>
    <div class="grid-clientes" id="grid">
        <?php foreach($lista_clientes as $c): ?>
            <div class="cliente-card"
                 data-nombre="<?= strtolower(htmlspecialchars($c['nombre'] . ' ' . ($c['apellido_paterno'] ?? '') . ' ' . ($c['apellido_materno'] ?? ''))) ?>"
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
                <div class="card-btns">
                    <button type="button" class="btn btn-historial" onclick="abrirHistorial(<?= $c['id_cliente'] ?>)">
                        <i class="fas fa-receipt"></i> Historial de compras
                    </button>
                    <button type="button" class="btn btn-edit" onclick="abrirEditar(<?= $c['id_cliente'] ?>)">
                        <i class="fas fa-pen"></i> Editar
                    </button>
                    <button type="button" class="btn btn-del" onclick="confirmarEliminar(<?= $c['id_cliente'] ?>, '<?= addslashes(htmlspecialchars($c['nombre'])) ?>')">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- MODAL EDITAR CLIENTE -->
<div id="modalEdit" class="modal-overlay">
    <div class="modal-box modal-ancho">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Editar Cliente</h3>
            <button type="button" class="modal-close" onclick="cerrarEditar()"><i class="fas fa-times"></i></button>
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
            <button type="button" class="btn-cancel-modal" onclick="cerrarEditar()"><i class="fas fa-times"></i> Cancelar</button>
            <button type="button" class="btn-save-modal" id="btnGuardarCliente" onclick="guardarCambios()"><i class="fas fa-save"></i> Guardar cambios</button>
        </div>
    </div>
</div>

<!-- MODAL HISTORIAL DE COMPRAS (REDISEÑADO) -->
<div id="modalHistorial" class="modal-overlay">
    <div class="modal-box modal-ancho" style="width:min(950px, 95vw);">
        <div class="modal-header">
            <h3><i class="fas fa-receipt"></i> <span id="hist-titulo">Historial de compras</span></h3>
            <button type="button" class="modal-close" onclick="cerrarHistorial()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <!-- Buscador y Filtros -->
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
            
            <!-- Chips de rango -->
            <div class="hist-chips">
                <button type="button" class="chip" data-rango="todo">Todo</button>
                <button type="button" class="chip" data-rango="hoy">Hoy</button>
                <button type="button" class="chip" data-rango="semana">Últimos 7 días</button>
                <button type="button" class="chip" data-rango="mes">Este mes</button>
                <button type="button" class="chip" data-rango="mes_pasado">Mes pasado</button>
            </div>

            <!-- Resumen en Tarjetas -->
            <div class="hist-resumen" id="hist-resumen">
                <!-- Se llena dinámicamente con JS -->
            </div>

            <!-- Tabla -->
            <div class="hist-tabla-wrap">
                <table class="hist-tabla">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Productos</th>
                            <th class="r">Total</th>
                            <th>Estado</th>
                            <th>Ticket</th>
                        </tr>
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
            <button type="button" class="modal-close" onclick="cerrarConfirmar()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-confirm-body">
            <div class="icono-alerta"><i class="fas fa-exclamation-triangle"></i></div>
            <p>¿Estás seguro de eliminar a</p>
            <p class="nombre-cliente" id="confirm-nombre"></p>
            <small>Esta acción es irreversible.</small>
            <div class="confirm-btns" style="display:flex; gap:12px; justify-content:center; margin-top:1.5rem;">
                <button type="button" class="btn-confirm-del" id="btnConfirmarEliminar" style="background:#dc2626; border:none; padding:10px 25px; border-radius:50px; color:white; font-weight:bold;"><i class="fas fa-trash-alt"></i> Sí, eliminar</button>
                <button type="button" class="btn-cancel-modal" onclick="cerrarConfirmar()">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<div id="toast"><i id="toast-icon" class="fas fa-check-circle"></i> <span id="toast-msg"></span></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const BASE = '<?= base_url() ?>';

/* ===== Utilidades ===== */
const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
const normalizar = t => String(t || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

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

/* ===== BUSCADOR Y SUGERENCIAS ===== */
const buscInput = document.getElementById('busc');
const sugerenciasDiv = document.getElementById('sugerencias');
const buscLimpiar = document.getElementById('buscLimpiar');
const buscContador = document.getElementById('buscContador');
let sugerenciaActiva = -1;

function resaltar(texto, q) {
    if (!q) return texto;
    const idx = normalizar(texto).indexOf(q);
    if (idx === -1) return texto;
    return texto.slice(0, idx) + '<mark>' + texto.slice(idx, idx + q.length) + '</mark>' + texto.slice(idx + q.length);
}

function filtrarYSugerir() {
    const q = normalizar(buscInput.value.trim());
    const cards = [...document.querySelectorAll('.cliente-card')];
    sugerenciasDiv.innerHTML = '';
    sugerenciaActiva = -1;

    buscLimpiar.hidden = !q;
    buscInput.setAttribute('aria-expanded', q ? 'true' : 'false');

    if (!q) {
        cards.forEach(c => { c.style.display = ''; });
        sugerenciasDiv.classList.remove('visible');
        buscContador.hidden = true;
        document.querySelector('.estado-vacio')?.remove();
        return;
    }

    let visibles = 0;
    const coincidencias = [];

    cards.forEach(card => {
        const nombreData = normalizar(card.dataset.nombre || '');
        const telData = normalizar(card.querySelector('p i.fa-phone-alt')?.parentElement?.innerText || '');
        const rfcData = normalizar(card.querySelector('p i.fa-id-card')?.parentElement?.innerText || '');
        const match = nombreData.includes(q) || telData.includes(q) || rfcData.includes(q);
        card.style.display = match ? '' : 'none';
        if (match) {
            visibles++;
            if (coincidencias.length < 6) coincidencias.push(card);
        }
    });

    buscContador.hidden = false;
    buscContador.textContent = visibles === 0
        ? 'Sin resultados'
        : `${visibles} resultado${visibles === 1 ? '' : 's'}`;

    document.querySelector('.estado-vacio')?.remove();
    if (visibles === 0) {
        const vacio = document.createElement('div');
        vacio.className = 'estado-vacio';
        vacio.innerHTML = '<i class="fas fa-user-slash"></i>No se encontraron clientes para <strong>' +
            esc(buscInput.value.trim()) + '</strong>';
        document.getElementById('grid').appendChild(vacio);
    }

    coincidencias.forEach(card => {
        const nombreMostrar = card.querySelector('h3').innerText.trim();
        const item = document.createElement('div');
        item.className = 'sugerencia-item';
        item.setAttribute('role', 'option');
        item.innerHTML = `<i class="fas fa-user-circle"></i> ${resaltar(nombreMostrar, q)}`;
        item.onclick = () => seleccionarSugerencia(card, nombreMostrar);
        sugerenciasDiv.appendChild(item);
    });

    sugerenciasDiv.classList.toggle('visible', coincidencias.length > 0);
}

function seleccionarSugerencia(card, nombre) {
    buscInput.value = nombre;
    sugerenciasDiv.classList.remove('visible');
    buscContador.hidden = true;
    document.querySelectorAll('.cliente-card').forEach(c => { c.style.display = 'none'; });
    card.style.display = '';
    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

buscInput.addEventListener('input', filtrarYSugerir);
buscInput.addEventListener('keydown', (e) => {
    const items = [...sugerenciasDiv.querySelectorAll('.sugerencia-item')];
    if (e.key === 'Escape') {
        buscInput.value = '';
        filtrarYSugerir();
        buscInput.blur();
        return;
    }
    if (!items.length) return;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        sugerenciaActiva = (sugerenciaActiva + 1) % items.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        sugerenciaActiva = (sugerenciaActiva - 1 + items.length) % items.length;
    } else if (e.key === 'Enter' && sugerenciaActiva >= 0) {
        e.preventDefault();
        items[sugerenciaActiva].click();
        return;
    } else {
        return;
    }
    items.forEach((it, i) => it.classList.toggle('activo', i === sugerenciaActiva));
});

buscLimpiar.addEventListener('click', () => {
    buscInput.value = '';
    filtrarYSugerir();
    buscInput.focus();
});

document.addEventListener('click', (e) => {
    if (!e.target.closest('.buscador-header-wrap')) {
        sugerenciasDiv.classList.remove('visible');
        buscInput.setAttribute('aria-expanded', 'false');
    }
});

/* ===== MODALES: abrir/cerrar ===== */
function abrirModal(id) {
    document.getElementById(id).style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
    if (![...document.querySelectorAll('.modal-overlay')].some(m => m.style.display === 'flex')) {
        document.body.style.overflow = '';
    }
}

/* ===== MAPA Y EDICIÓN ===== */
const CENTRO_INICIAL = [19.0414, -98.2063];
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

function prepararMapa(lat, lng) {
    if (!mapa) {
        mapa = L.map('mapaEditar').setView(CENTRO_INICIAL, 10);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mapa);
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
            if (probados.size > 1) await new Promise(r => setTimeout(r, 1100));
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
            abrirModal('modalEdit');
            setTimeout(() => prepararMapa(c.lat, c.lng), 60);
        } else showToast('No se encontraron datos', 'error');
    }).catch(() => showToast('Error al cargar detalles', 'error'));
}
function cerrarEditar() { cerrarModal('modalEdit'); }
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

/* ===== HISTORIAL DE COMPRAS (REDISEÑADO) ===== */
let histPedidos = [];
let histRango = 'todo';
const histEl = {
    busc: document.getElementById('hist-busc'), estado: document.getElementById('hist-estado'),
    desde: document.getElementById('hist-desde'), hasta: document.getElementById('hist-hasta'),
    cuerpo: document.getElementById('hist-cuerpo'), resumen: document.getElementById('hist-resumen'),
};
const dinero = n => '$' + Number(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const folio = id => 'PED-' + String(id).padStart(5, '0');
const textoNormal = t => normalizar(t);
const aISO = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

function clasePedido(estado) {
    if (estado === 'Pedido cancelado') return 'cancelado';
    if (estado === 'Venta confirmada' || estado === 'Pedido pagado') return 'ok';
    if (estado === 'Pedido a crédito') return 'credito';
    return 'proceso';
}

function aplicarRango(rango) {
    histRango = rango;
    document.querySelectorAll('.hist-chips .chip').forEach(c => {
        c.classList.toggle('activo', c.dataset.rango === rango);
    });
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

    const lista = histPedidos.filter(p => {
        const dia = String(p.fecha).substring(0, 10);
        if (desde && dia < desde) return false;
        if (hasta && dia > hasta) return false;
        if (estado && p.estado_actual !== estado) return false;
        if (q && !(textoNormal(folio(p.id)).includes(q) || textoNormal(p.id).includes(q) || textoNormal(p.productos).includes(q))) return false;
        return true;
    });

    // Llenar las métricas (Tarjetas de resumen)
    let total = 0, saldo = 0;
    lista.forEach(p => {
        if (p.estado_actual === 'Pedido cancelado') return;
        total += Number(p.total);
        if (p.tipo_pago === 'credito') saldo += Math.max(0, Number(p.total) - Number(p.monto_pagado));
    });

    if (lista.length) {
        histEl.resumen.innerHTML = `
            <div class="hist-metrica">
                <small>Compras</small>
                <strong>${lista.length}</strong>
            </div>
            <div class="hist-metrica">
                <small>Total comprado</small>
                <strong>${dinero(total)}</strong>
            </div>
            <div class="hist-metrica ${saldo > 0 ? 'alerta' : ''}">
                <small>Saldo pendiente</small>
                <strong>${dinero(saldo)}</strong>
            </div>
        `;
    } else {
        histEl.resumen.innerHTML = '';
    }

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

        const estados = [...new Set(histPedidos.map(p => p.estado_actual))];
        histEl.estado.innerHTML = '<option value="">Todos los estados</option>' +
            estados.map(e => `<option value="${esc(e)}">${esc(e)}</option>`).join('');
        histEl.busc.value = '';
        aplicarRango('todo');
        abrirModal('modalHistorial');
        histEl.busc.focus();
    })
    .catch(() => showToast('Error al cargar el historial', 'error'));
}
function cerrarHistorial() { cerrarModal('modalHistorial'); }

histEl.busc.addEventListener('input', filtrarHistorial);
histEl.estado.addEventListener('change', filtrarHistorial);
[histEl.desde, histEl.hasta].forEach(el => el.addEventListener('change', () => { histRango = ''; filtrarHistorial(); }));
document.querySelectorAll('.hist-chips .chip').forEach(c => c.addEventListener('click', () => aplicarRango(c.dataset.rango)));

/* ===== ELIMINAR CONFIRMACIÓN ===== */
let pendingDeleteId = null;
function confirmarEliminar(id, nombre) {
    pendingDeleteId = id;
    document.getElementById('confirm-nombre').innerText = nombre;
    abrirModal('modalConfirmar');
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
function cerrarConfirmar() { cerrarModal('modalConfirmar'); }

/* ===== Cierre con overlay y Esc ===== */
window.addEventListener('click', (e) => {
    if (e.target === document.getElementById('modalEdit')) cerrarEditar();
    if (e.target === document.getElementById('modalConfirmar')) cerrarConfirmar();
    if (e.target === document.getElementById('modalHistorial')) cerrarHistorial();
});

document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    if (document.getElementById('modalEdit').style.display === 'flex') cerrarEditar();
    else if (document.getElementById('modalHistorial').style.display === 'flex') cerrarHistorial();
    else if (document.getElementById('modalConfirmar').style.display === 'flex') cerrarConfirmar();
});

/* ===== Flash messages ===== */
window.addEventListener('DOMContentLoaded', () => {
    <?php if (session()->getFlashdata('flash_success')): ?>
        showToast('<?= session()->getFlashdata('flash_success') ?>', 'success');
    <?php endif; ?>
    <?php if (session()->getFlashdata('flash_error')): ?>
        showToast('<?= session()->getFlashdata('flash_error') ?>', 'error');
    <?php endif; ?>
});

</script>

<a href="<?= base_url('pantalla_rcliente') ?>" class="btn-flotante-clientes" aria-label="Nuevo cliente">
    <i class="fas fa-plus-circle"></i>
    <span>Nuevo Cliente</span>
</a>

</body>
</html>