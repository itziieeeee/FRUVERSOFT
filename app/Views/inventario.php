<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>FRUVER - Inventario | Gestión de Entradas y Mermas</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/inventarioestilo.css') ?>">
    
    <style>
        /* ===== Entrada: catálogo visual ===== */
        /* ===== Modal de entrada: amplio y responsivo ===== */
        #productModal .modal-container-custom {
            width: 96vw;
            max-width: 1500px;
            max-height: 95vh;
            margin: 2.5vh auto;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            animation: modalPop .28s cubic-bezier(.2,.8,.3,1.2);
        }
        @keyframes modalPop {
            from { opacity: 0; transform: translateY(14px) scale(.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        #productModal .modal-body {
            overflow-y: auto;
            min-height: 0;
            padding: 24px !important;
            scroll-behavior: smooth;
        }
        #productModal .modal-header-custom { flex-shrink: 0; }
        #productModal .modal-body,
        #productModal .modal-body * { box-sizing: border-box; }

        /* ---- controles de búsqueda ---- */
        .cat-controles {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) minmax(180px, 260px);
            gap: 12px;
            margin-bottom: 14px;
            position: sticky;
            top: 0;
            z-index: 5;
            background: #fff;
            padding: 6px 2px 10px;
            border-bottom: 1px solid #edf0ed;
        }
        .cat-filtro {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #e0e0e0;
            border-radius: 10px;
            background: #fff;
            font-family: 'Inter', sans-serif;
            transition: border-color .2s, box-shadow .2s;
        }
        .cat-filtro:hover { border-color: #f9c9a8; }
        .cat-filtro:focus {
            outline: none;
            border-color: #f16e1f;
            box-shadow: 0 0 0 3px rgba(241,110,31,.12);
        }
        .cat-barra { position: relative; margin-bottom: 0; }
        .cat-barra i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            transition: color .2s;
        }
        .cat-barra input {
            width: 100%;
            padding: 10px 12px 10px 38px;
            border: 1.5px solid #e0e0e0;
            border-radius: 10px;
            outline: none;
            font-family: 'Inter', sans-serif;
            transition: border-color .2s, box-shadow .2s;
        }
        .cat-barra input:hover { border-color: #f9c9a8; }
        .cat-barra input:focus {
            border-color: #f16e1f;
            box-shadow: 0 0 0 3px rgba(241,110,31,.12);
        }
        .cat-barra:focus-within i { color: #f16e1f; }

        /* ---- grid ---- */
        .cat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(205px, 1fr));
            gap: 20px;
            padding: 8px;
        }
        @media (min-width: 768px) {
            #catalogo {
                max-height: 52vh;
                overflow-y: auto;
                overflow-x: hidden;
                padding: 4px 8px 8px 4px;
                scroll-behavior: smooth;
            }
        }
        .cat-seccion {
            margin-bottom: 24px;
            padding-bottom: 8px;
            border-bottom: 1px solid #edf0ed;
            animation: fadeUp .3s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .cat-seccion:last-child { border-bottom: 0; }
        .cat-titulo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            font-weight: 700;
            color: #1d4a27;
            margin: 8px 4px 12px;
            font-size: 1.08rem;
        }
        .cat-titulo .cat-badge {
            font-size: .72rem;
            font-weight: 700;
            color: #d35400;
            background: #fff0e5;
            padding: 4px 9px;
            border-radius: 20px;
        }
        .cat-subtitulo { color: #777; font-size: .82rem; margin: -5px 4px 12px; }
        .cat-titulo small { font-weight: 400; color: #888; margin-left: 6px; }
        .cat-card.frec { border-color: #f9c9a8; background: #fffaf5; }
        .cat-hot { margin-top: 8px; font-size: .78rem; font-weight: 700; color: #d35400; }

        /* ---- tarjetas del catálogo ---- */
        .cat-card {
            position: relative;
            background: #fff;
            border: 1.5px solid #e0e0e0;
            border-radius: 16px;
            padding: 14px;
            text-align: center;
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
            outline: none;
            min-width: 0;
            box-shadow: 0 2px 7px rgba(0,0,0,.035);
            will-change: transform;
        }
        .cat-card:hover,
        .cat-card:focus-visible {
            border-color: #f16e1f;
            transform: translateY(-3px);
            box-shadow: 0 10px 22px rgba(0,0,0,.10);
        }
        .cat-card:active { transform: translateY(-1px) scale(.985); }
        .cat-card.en-lista {
            border-color: #1d4a27;
            background: #f0faf3;
            box-shadow: 0 0 0 3px rgba(29,74,39,.10);
        }
        .cat-check {
            display: none;
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #1d4a27;
            color: #fff;
            font-size: .8rem;
            align-items: center;
            justify-content: center;
            animation: checkPop .32s cubic-bezier(.2,.9,.3,1.4);
        }
        @keyframes checkPop {
            0%   { transform: scale(0); opacity: 0; }
            60%  { transform: scale(1.15); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        .cat-card.en-lista .cat-check { display: flex; }
        .cat-img, .cat-ph {
            width: 100%;
            height: 165px;
            border-radius: 12px;
            margin-bottom: 12px;
            transition: transform .25s ease;
        }
        .cat-card:hover .cat-img,
        .cat-card:hover .cat-ph { transform: scale(1.04); }
        .cat-img { display: block; object-fit: contain; background: #f5f7fa; }
        .cat-ph {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.2rem;
            background: #f0f4ef;
        }
        .cat-nombre {
            font-weight: 700;
            font-size: 1.02rem;
            color: #1d4a27;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }
        .cat-meta { font-size: .82rem; color: #888; margin: 4px 0 8px; }
        .cat-stock {
            display: inline-block;
            font-size: .82rem;
            background: #e8f3e6;
            color: #1d4a27;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            transition: background .2s;
        }
        .cat-card.en-lista .cat-stock { background: #d6ecdb; }

        /* ---- tarjeta expandida ---- */
        .tp-card {
            background: #fff;
            border: 2px solid #f16e1f;
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 20px;
            box-shadow: 0 8px 24px rgba(241,110,31,.12);
            animation: tpIn .26s cubic-bezier(.2,.8,.3,1.1);
        }
        @keyframes tpIn {
            from { opacity: 0; transform: translateY(-8px) scale(.99); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .tp-head { display: flex; align-items: center; gap: 18px; margin-bottom: 14px; }
        .tp-img { width: 120px; height: 120px; flex-shrink: 0; }
        .tp-img img, .tp-img .cat-ph {
            width: 120px;
            height: 120px;
            border-radius: 14px;
            object-fit: contain;
            background: #f5f7fa;
            margin: 0;
            font-size: 3rem;
        }
        .tp-titulo { flex: 1; min-width: 0; }
        .tp-titulo h5 { color: #1d4a27; font-weight: 700; }
        .tp-modo {
            display: inline-block;
            margin-left: 8px;
            font-size: .75rem;
            background: #fff3e8;
            color: #d35400;
            padding: 2px 10px;
            border-radius: 20px;
            font-weight: 600;
            animation: fadeUp .25s ease;
        }
        .tp-cerrar {
            background: none;
            border: none;
            font-size: 1.8rem;
            line-height: 1;
            color: #999;
            cursor: pointer;
            transition: color .2s, transform .2s;
        }
        .tp-cerrar:hover { color: #d35400; transform: rotate(90deg); }
        .tp-ultima {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: .82rem;
            color: #555;
            margin-bottom: 14px;
            border-left: 3px solid #f16e1f;
            animation: fadeUp .28s ease;
        }

        .tp-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        @media (max-width: 900px) { .tp-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px) { .tp-grid { grid-template-columns: 1fr; } }

        .tp-campo { display: flex; flex-direction: column; gap: 4px; }
        .tp-campo label { font-weight: 600; font-size: .82rem; color: #1d4a27; margin: 0; }
        .tp-campo label small { font-weight: 400; color: #888; }
        .tp-campo input, .tp-campo select {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #e0e0e0;
            border-radius: 10px;
            outline: none;
            font-family: 'Inter', sans-serif;
            background: #fff;
            transition: border-color .18s, box-shadow .18s, background .18s;
        }
        .tp-campo input:hover, .tp-campo select:hover { border-color: #f9c9a8; }
        .tp-campo input:focus, .tp-campo select:focus {
            border-color: #f16e1f;
            box-shadow: 0 0 0 3px rgba(241,110,31,.12);
        }
        .tp-campo input[readonly] {
            background: #eef1f4;
            font-weight: 700;
            cursor: not-allowed;
        }
        .tp-campo input[readonly]:focus { box-shadow: none; border-color: #e0e0e0; }
        .tp-dinero { display: flex; align-items: center; }
        .tp-dinero span {
            padding: 9px 12px;
            background: #f0f4ef;
            border: 1.5px solid #e0e0e0;
            border-right: none;
            border-radius: 10px 0 0 10px;
            font-weight: 700;
            transition: border-color .18s, background .18s;
        }
        .tp-dinero:focus-within span {
            border-color: #f16e1f;
            background: #fff3e8;
        }
        .tp-dinero input { border-radius: 0 10px 10px 0; }
        .tp-hint { font-size: .78rem; min-height: 1em; transition: color .2s; }

        .tp-acciones {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 16px;
            flex-wrap: wrap;
        }
        .tp-btn-add {
            background: linear-gradient(135deg, #f16e1f, #d35400);
            color: #fff;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            padding: 9px 22px;
            transition: transform .15s ease, box-shadow .2s ease;
        }
        .tp-btn-add:hover {
            color: #fff;
            box-shadow: 0 6px 16px rgba(241,110,31,.35);
            transform: translateY(-1px);
        }
        .tp-btn-add:active { transform: translateY(0) scale(.98); }

        /* ---- lista de entradas ---- */
        .lista-wrap { max-height: 300px; overflow-y: auto; scroll-behavior: smooth; }
        .lista-wrap td { vertical-align: middle; font-size: .88rem; }
        .lista-wrap tbody tr {
            animation: fadeUp .28s ease both;
            transition: background .2s;
        }
        .lista-wrap tbody tr:hover { background: #fffaf5; }
        .lista-wrap .btnEditarFila,
        .lista-wrap .btnEliminarFila {
            transition: transform .15s ease, box-shadow .2s ease;
        }
        .lista-wrap .btnEditarFila:hover,
        .lista-wrap .btnEliminarFila:hover { transform: translateY(-1px); }

        /* ---- botón guardar ---- */
        #btnGuardarTodo { transition: transform .15s ease, box-shadow .2s ease; }
        #btnGuardarTodo:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(29,74,39,.25) !important;
        }

        /* ---- estado vacío del catálogo ---- */
        #catVacio { animation: fadeUp .3s ease; }

        /* ---- focus visible accesible ---- */
        .cat-card:focus-visible {
            box-shadow: 0 0 0 3px rgba(241,110,31,.35), 0 10px 22px rgba(0,0,0,.10);
        }

        /* ===== Ajustes para tabletas y celulares ===== */
        @media (max-width: 767px) {
            #productModal .modal-container-custom {
                width: 98vw;
                max-width: 98vw;
                max-height: 96vh;
                margin: 2vh auto;
                animation: none;
            }
            #productModal .modal-body { padding: 14px !important; }
            .cat-controles { grid-template-columns: 1fr; gap: 9px; position: static; }
            #catalogo { max-height: 42vh; overflow-y: auto; }
            .cat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .cat-img, .cat-ph { height: 125px; }
            .cat-card { padding: 10px; }
            .cat-card:hover { transform: none; }
            .cat-card:active { transform: scale(.97); }
            .tp-card { padding: 14px; }
            .tp-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        }
        @media (max-width: 480px) {
            #productModal .modal-header-custom { padding: 12px 14px; }
            #productModal .modal-header-custom h5 { font-size: 1rem; }
            #productModal .modal-body { padding: 10px !important; }
            .cat-controles { grid-template-columns: 1fr; }
            .cat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
            .cat-img, .cat-ph { height: 105px; }
            .cat-card { padding: 8px; border-radius: 12px; }
            .cat-nombre { font-size: .9rem; }
            .cat-meta, .cat-stock { font-size: .72rem; }
            .cat-hot { font-size: .68rem; }
            .tp-head { gap: 10px; align-items: flex-start; }
            .tp-img, .tp-img img, .tp-img .cat-ph { width: 84px; height: 84px; }
            .tp-titulo h5 { font-size: 1rem; }
            .tp-grid { grid-template-columns: 1fr; }
            .tp-acciones .btn, .tp-btn-add { width: 100%; }
            .lista-wrap { max-height: 240px; }
        }
    </style>
</head>
<body>
<div class="fondo">
    <header>
        <div class="barra-superior">
            <div class="logo-area">
                <img src="<?= base_url('img/LOGO1.png') ?>" alt="Fruver Logo">
            </div>
            <div class="buscador">
                <input type="text" id="buscadorTabla" placeholder="Buscar producto en inventario...">
                <button type="button"><i class="fas fa-search"></i></button>
            </div>
            <div class="user-actions">
                <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> Admin</a>
                <a href="menusolo" class="btn-user"><i class="fas fa-arrow-right-from-bracket"></i> Regresar</a>
            </div>
        </div>
        <nav class="menu-navegacion">
            <a href="pantalla_ventas" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
            <a href="pantalla_pedidos" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
            <a href="inventario" class="nav-link activo"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="pantalla_clientes" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
            <a href="pantalla_repartidores" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
            <a href="pantalla_productos" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
        </nav>
    </header>

    <!-- Mensajes Flash -->
    <?php if (session()->getFlashdata('mensaje')): ?>
        <div class="alert alert-success alert-custom shadow-sm d-flex align-items-center justify-content-between">
            <span><i class="fas fa-check-circle me-2"></i> <?= session()->getFlashdata('mensaje') ?></span>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-custom shadow-sm">
            <i class="fas fa-exclamation-triangle"></i> <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- TARJETAS DE ACCIONES PRINCIPALES -->
    <div class="opciones">
        <button class="btn-opcion-card" id="btnAbrirEntrada" style="border: none; background: white; cursor: pointer;">
            <img src="<?= base_url('img/Entrada.jpeg') ?>" alt="Entrada">
            <span><i class="fas fa-arrow-down me-1"></i> Entrada</span>
        </button>

        <button class="btn-opcion-card" id="btnAbrirMerma" style="border: none; background: white; cursor: pointer;">
            <img src="<?= base_url('img/Merma.jpeg') ?>" alt="Merma">
            <span><i class="fas fa-trash-alt"></i> Merma</span>
        </button>

        <button class="btn-opcion-card" id="btnAbrirExistencias" style="border: none; background: white; cursor: pointer;">
            <img src="<?= base_url('img/existencias.png') ?>" alt="Existencias">
            <span><i class="fas fa-clipboard-list"></i> Existencias</span>
        </button>
    </div>

    <!-- TABLA DE STOCK ACTUAL -->
    <div class="container-mordern">
        <div class="section-title">
            <span>Resumen de Inventario Actual</span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table-custom" id="tablaStock">
                <thead>
                    <tr>
                        <th> Producto</th>
                        <th> Total disponible</th>
                        <th> Merma acumulada</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    <?php if (!empty($existencias)): ?>
                        <?php foreach ($existencias as $e): ?>
                            <tr class="fila-producto">
                                <td style="font-weight: 600;"><?= esc($e['nombre']) ?></td>
                                <td><span class="badge-stock"><i class="fas fa-box-open"></i> <?= number_format($e['e_total'], 2) ?></span></td>
                                <td><span class="text-danger"><?= number_format($e['e_merma'], 2) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center py-4 text-muted"><i class="fas fa-database"></i> No hay datos en el inventario</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
    $catalogo = $catalogo ?? array_map(static fn($p) => [
        'id' => $p['id'], 'nombre' => $p['nombre'], 'imagen' => $p['imagen'] ?? null, 'stock' => 0,
    ], $productos ?? []);
    $catalogoPorId = array_column($catalogo, null, 'id');

    $unidadesCompra = ['caja', 'kilo', 'domos', 'mazo', 'arpilla', 'ramos'];
    $unidadesVenta  = ['caja', 'kilo', 'domos', 'mazo', 'arpilla', 'ramos', 'pieza'];
    $categoriasEnt  = ['Frutas', 'Verduras', 'Hierbas'];
?>

<!-- MODAL ENTRADA: CATÁLOGO VISUAL -->
<div id="productModal" class="modal-overlay-custom">
    <div class="modal-container-custom">
        <div class="modal-header-custom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-truck-loading me-2"></i> Registro de Entrada Múltiple</h5>
            <button type="button" id="closeModalBtn" class="close text-white" style="font-size: 1.8rem; opacity: 0.9;">&times;</button>
        </div>

        <div class="modal-body p-4">

            <!-- 1. TARJETA EXPANDIDA DEL PRODUCTO SELECCIONADO -->
            <form id="formTarjeta" class="tp-card" style="display:none;" novalidate>
                <input type="hidden" id="f_id">

                <div class="tp-head">
                    <div id="tpImg" class="tp-img"></div>
                    <div class="tp-titulo">
                        <h5 id="tpNombre" class="mb-0"></h5>
                        <small id="tpMeta" class="text-muted"></small>
                        <span id="tpModo" class="tp-modo" style="display:none;"><i class="fas fa-pen"></i> Editando entrada de la lista</span>
                    </div>
                    <button type="button" class="tp-cerrar" id="btnCancelarTarjeta" title="Cerrar">&times;</button>
                </div>

                <div id="tpUltima" class="tp-ultima"></div>

                <div class="tp-grid">
                    <div class="tp-campo">
                        <label for="f_cant">Cantidad que recibes</label>
                        <input type="number" id="f_cant" step="0.01" min="0.01" required>
                    </div>

                    <div class="tp-campo">
                        <label for="f_uc">Unidad de compra</label>
                        <select id="f_uc" required>
                            <option value="" disabled selected hidden>Selecciona...</option>
                            <?php foreach ($unidadesCompra as $u): ?>
                                <option value="<?= $u ?>"><?= ucfirst($u) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="tp-campo">
                        <label for="f_pc">Precio de compra <small>(por unidad de compra)</small></label>
                        <div class="tp-dinero"><span>$</span><input type="number" id="f_pc" step="0.01" min="0.01" required></div>
                        <small id="hintCosto" class="tp-hint"></small>
                    </div>

                    <div class="tp-campo">
                        <label for="f_uv">Unidad de venta</label>
                        <select id="f_uv" required>
                            <option value="" disabled selected hidden>Selecciona...</option>
                            <?php foreach ($unidadesVenta as $u): ?>
                                <option value="<?= $u ?>"><?= ucfirst($u) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="tp-campo">
                        <label for="f_conv" id="lblConv">Conversión</label>
                        <input type="number" id="f_conv" step="0.01" min="0.01" required>
                    </div>

                    <div class="tp-campo">
                        <label for="f_total">Entra a existencias</label>
                        <input type="number" id="f_total" readonly tabindex="-1">
                    </div>

                    <div class="tp-campo">
                        <label for="f_ps">Precio de venta sugerido <small>(por unidad de venta)</small></label>
                        <div class="tp-dinero"><span>$</span><input type="number" id="f_ps" step="0.01" min="0.01" required></div>
                        <small id="hintVenta" class="tp-hint"></small>
                    </div>

                    <div class="tp-campo">
                        <label for="f_cat">Categoría</label>
                        <select id="f_cat" required>
                            <?php foreach ($categoriasEnt as $c): ?>
                                <option value="<?= $c ?>"><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="tp-acciones">
                    <button type="button" class="btn btn-light border" id="btnCancelarTarjeta2">Cancelar</button>
                    <button type="submit" class="btn tp-btn-add"><i class="fas fa-plus"></i> Añadir a la lista</button>
                </div>
            </form>

            <!-- 2. CATÁLOGO DE PRODUCTOS AGRUPADO POR CATEGORÍA -->
            <div class="cat-controles">
                <div class="cat-barra">
                    <i class="fas fa-search"></i>
                    <input type="text" id="catBuscador" placeholder="Buscar producto por nombre o código..." autocomplete="off">
                </div>
                <select id="catFiltroCategoria" class="cat-filtro" aria-label="Filtrar por categoría">
                    <option value="">Todas las categorías</option>
                    <?php
                    $categoriasFiltro = [];
                    foreach ($catalogo as $itemCat) {
                        $nombreCat = trim((string)($itemCat['categoria'] ?? $itemCat['nombre_categoria'] ?? 'Sin categoría'));
                        if ($nombreCat === '') $nombreCat = 'Sin categoría';
                        $categoriasFiltro[$nombreCat] = $nombreCat;
                    }
                    natcasesort($categoriasFiltro);
                    foreach ($categoriasFiltro as $nombreCat): ?>
                        <option value="<?= esc(mb_strtolower($nombreCat, 'UTF-8')) ?>"><?= esc($nombreCat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php
                $gruposCatalogo = [];
                foreach ($catalogo as $itemCat) {
                    $nombreCat = trim((string)($itemCat['categoria'] ?? $itemCat['nombre_categoria'] ?? 'Sin categoría'));
                    if ($nombreCat === '') $nombreCat = 'Sin categoría';
                    $itemCat['_categoriaVista'] = $nombreCat;
                    $gruposCatalogo[$nombreCat][] = $itemCat;
                }
                uksort($gruposCatalogo, 'strnatcasecmp');
                foreach ($gruposCatalogo as &$itemsGrupo) {
                    usort($itemsGrupo, static function ($a, $b) {
                        $fa = !empty($a['frecuente']) ? 1 : 0;
                        $fb = !empty($b['frecuente']) ? 1 : 0;
                        if ($fa !== $fb) return $fb <=> $fa;
                        return ((int)($b['ventas_periodo'] ?? 0)) <=> ((int)($a['ventas_periodo'] ?? 0));
                    });
                }
                unset($itemsGrupo);

                $tarjeta = function (array $c) { ?>
                    <div class="cat-card <?= !empty($c['frecuente']) ? 'frec' : '' ?>"
                         data-id="<?= (int) $c['id'] ?>"
                         data-nombre="<?= esc($c['nombre']) ?>"
                         data-categoria="<?= esc(mb_strtolower($c['_categoriaVista'] ?? $c['categoria'] ?? $c['nombre_categoria'] ?? 'Sin categoría', 'UTF-8')) ?>"
                         tabindex="0" role="button">
                        <span class="cat-check"><i class="fas fa-check"></i></span>
                        <?php if (!empty($c['imagen'])): ?>
                            <img class="cat-img" src="<?= base_url('uploads/productos/' . $c['imagen']) ?>" alt="<?= esc($c['nombre']) ?>"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div class="cat-ph" style="display:none;"></div>
                        <?php else: ?>
                            <div class="cat-ph"></div>
                        <?php endif; ?>
                        <div class="cat-nombre"><?= esc($c['nombre']) ?></div>
                        <div class="cat-meta">#<?= (int) $c['id'] ?></div>
                        <span class="cat-stock"><i class="fas fa-box-open"></i> <?= number_format((float) ($c['stock'] ?? 0), 2) ?></span>
                        <?php if (!empty($c['frecuente'])): ?>
                            <div class="cat-hot"> Alta rotación · <?= (int) ($c['ventas_periodo'] ?? 0) ?> ventas</div>
                        <?php endif; ?>
                    </div>
                <?php };
            ?>

            <div id="catalogo">
                <?php foreach ($gruposCatalogo as $nombreCat => $itemsGrupo): ?>
                    <div class="cat-seccion" data-categoria-seccion="<?= esc(mb_strtolower($nombreCat, 'UTF-8')) ?>">
                        <div class="cat-titulo">
                            <span><i class="fas fa-layer-group mr-1"></i> <?= esc($nombreCat) ?></span>
                            <small><?= count($itemsGrupo) ?> productos</small>
                        </div>
                        <?php if (count(array_filter($itemsGrupo, static fn($x) => !empty($x['frecuente']))) > 0): ?>
                            <div class="cat-subtitulo"> Los productos de alta rotación aparecen primero en esta categoría.</div>
                        <?php endif; ?>
                        <div class="cat-grid">
                            <?php foreach ($itemsGrupo as $c) { $tarjeta($c); } ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="catVacio" class="text-center text-muted py-4" style="display:none;">
                <i class="fas fa-search fa-2x mb-2"></i>
                <p class="mb-0">No se encontraron productos con esos filtros.</p>
            </div>

            <!-- 3. LISTA DE ENTRADAS PENDIENTES -->
            <form id="productForm" method="POST" action="<?= base_url('confirmar-entrada') ?>" class="mt-4">
                <h6 class="font-weight-bold mb-2"><i class="fas fa-clipboard-list"></i> Entradas por guardar</h6>

                <div class="table-responsive lista-wrap">
                    <table class="table table-sm table-hover border mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>P. compra</th>
                                <th>Importe</th>
                                <th>Entra a existencias</th>
                                <th>P. venta</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaEntradas"></tbody>
                    </table>
                </div>

                <div id="vacioMensaje" class="text-center py-4 text-muted">
                    <i class="fas fa-hand-pointer fa-2x mb-2"></i>
                    <p class="mb-0">Toca un producto del catálogo para empezar.</p>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap mt-3" style="gap:10px;">
                    <div>
                        <span class="badge badge-info p-2" id="contadorProductos">Productos en lista: 0</span>
                        <span class="badge badge-light border p-2" id="totalCompra" style="display:none;"></span>
                    </div>
                    <button type="submit" id="btnGuardarTodo" class="btn btn-success px-5 rounded-pill shadow" style="display:none; background: var(--verde-fruta); border: none; font-weight: bold;">
                        <i class="fas fa-save me-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(function () {

    // ===== DATOS Y ESTADO =====
    const CATALOGO = <?= json_encode($catalogoPorId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const URL_IMG  = '<?= base_url('uploads/productos') ?>/';

    const modal   = $('#productModal');
    const tarjeta = $('#formTarjeta');
    let lista     = [];
    let editIdx   = null;

    // ===== UTILIDADES =====
    const num   = sel => parseFloat($(sel).val()) || 0;
    const money = n => '$' + (Number(n) || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const norm  = s => String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    const esc   = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    // ===== MODAL =====
    $('#btnAbrirEntrada').on('click', function (e) {
        e.preventDefault();
        modal.addClass('active');
        setTimeout(() => $('#catBuscador').trigger('focus'), 150);
    });
    $('#closeModalBtn').on('click', () => modal.removeClass('active'));
    $(window).on('click', function (e) {
        if ($(e.target).is(modal) && lista.length === 0) modal.removeClass('active');
    });

    // ===== CATÁLOGO: BÚSQUEDA + FILTRO POR CATEGORÍA =====
    $('.cat-card').each(function () {
        $(this).data('busq', norm($(this).data('nombre') + ' ' + $(this).data('id')));
    });

    function filtrarCatalogo() {
        const q = norm($('#catBuscador').val().trim());
        const categoria = norm($('#catFiltroCategoria').val());
        let visibles = 0;

        $('.cat-card').each(function () {
            const coincideTexto = q === '' || $(this).data('busq').indexOf(q) > -1;
            const coincideCategoria = categoria === '' || norm($(this).data('categoria')) === categoria;
            const mostrar = coincideTexto && coincideCategoria;
            $(this).toggle(mostrar);
            if (mostrar) visibles++;
        });

        $('.cat-seccion').each(function () {
            const hay = $(this).find('.cat-card').filter((_, el) => el.style.display !== 'none').length > 0;
            $(this).toggle(hay);
        });
        $('#catVacio').toggle(visibles === 0);
    }

    $('#catBuscador').on('input', filtrarCatalogo);
    $('#catFiltroCategoria').on('change', filtrarCatalogo);

    // ===== CATÁLOGO: SELECCIÓN =====
    $('#catalogo').on('click', '.cat-card', function () { abrirTarjeta($(this).data('id')); });
    $('#catalogo').on('keydown', '.cat-card', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); abrirTarjeta($(this).data('id')); }
    });

    // Feedback táctil en tarjetas del catálogo
    $('#catalogo').on('mousedown', '.cat-card', function () {
        $(this).css('transform', 'scale(.98)');
    }).on('mouseup mouseleave', '.cat-card', function () {
        $(this).css('transform', '');
    });

    function abrirTarjeta(id) {
        const p = CATALOGO[id];
        if (!p) return;

        const idx = lista.findIndex(i => String(i.id) === String(id));
        editIdx = idx > -1 ? idx : null;
        const previo = editIdx !== null ? lista[editIdx] : null;

        tarjeta[0].reset();
        $('#f_id').val(id);
        $('#tpNombre').text(p.nombre);
        $('#tpMeta').text('#' + p.id + ' · Existencias actuales: ' + (parseFloat(p.stock) || 0).toFixed(2));
        $('#tpModo').toggle(previo !== null);

        const ph = '<div class="cat-ph"></div>';
        $('#tpImg').html(p.imagen
            ? '<img src="' + URL_IMG + encodeURIComponent(p.imagen) + '" alt="" onerror="this.outerHTML=\'' + ph + '\'">'
            : ph);

        if (previo) {
            $('#f_cant').val(previo.cant);  $('#f_uc').val(previo.uc);
            $('#f_pc').val(previo.pc);      $('#f_uv').val(previo.uv);
            $('#f_conv').val(previo.conv);  $('#f_ps').val(previo.ps);
            $('#f_cat').val(previo.cat);
        } else {
            if (p.unidad_compra) $('#f_uc').val(p.unidad_compra);
            if (p.unidad_venta)  $('#f_uv').val(p.unidad_venta);
            if (p.categoria)     $('#f_cat').val(p.categoria);
            if (p.conversion)    $('#f_conv').val(p.conversion);
            if (p.precio_sugerido) $('#f_ps').val(parseFloat(p.precio_sugerido));
        }

        if (p.precio_compra) {
            const fecha = p.fecha_ultima ? ' (' + String(p.fecha_ultima).substring(0, 10) + ')' : '';
            $('#tpUltima').html('<i class="fas fa-history"></i> Última entrada' + fecha + ': compra <strong>' +
                money(p.precio_compra) + '</strong> por ' + esc(p.unidad_compra || 'unidad') +
                ' · venta sugerida <strong>' + money(p.precio_sugerido) + '</strong> por ' + esc(p.unidad_venta || 'unidad')).show();
        } else {
            $('#tpUltima').html('<i class="fas fa-info-circle"></i> Este producto aún no tiene entradas registradas.').show();
        }

        tarjeta.slideDown(150);
        recalcular();
        const modalBody = modal.find('.modal-body');
        modalBody.animate({ scrollTop: 0 }, 200);
        setTimeout(() => $('#f_cant').trigger('focus'), 220);
    }

    function cerrarTarjeta() {
        tarjeta.slideUp(120);
        editIdx = null;
    }
    $('#btnCancelarTarjeta, #btnCancelarTarjeta2').on('click', cerrarTarjeta);

    // ===== CÁLCULOS DE LA TARJETA =====
    tarjeta.on('input change', 'input, select', recalcular);

    function recalcular() {
        const cant = num('#f_cant'), conv = num('#f_conv'), pc = num('#f_pc'), ps = num('#f_ps');
        const uc = $('#f_uc').val(), uv = $('#f_uv').val();

        const total = cant * conv;
        $('#f_total').val(total > 0 ? total.toFixed(2) : '');

        $('#lblConv').html('Conversión <small>(1 ' + esc(uc || 'unidad de compra') + ' = ¿cuántas ' + esc(uv || 'unidades de venta') + '?)</small>');

        const p = CATALOGO[$('#f_id').val()];
        const ant = p && p.precio_compra ? parseFloat(p.precio_compra) : 0;
        let h = '';
        if (ant > 0 && pc > 0) {
            const d = (pc - ant) / ant * 100;
            if (Math.abs(d) < 0.05) h = '<span class="text-muted">Igual que la última compra (' + money(ant) + ')</span>';
            else h = '<span class="' + (d > 0 ? 'text-danger' : 'text-success') + '">' + (d > 0 ? '▲ +' : '▼ ') +
                     d.toFixed(1) + '%  última compra (' + money(ant) + ')</span>';
        } else if (ant > 0) {
            h = '<span class="text-muted">Última compra: ' + money(ant) + '</span>';
        }
        $('#hintCosto').html(h);

        let v = '';
        if (pc > 0 && conv > 0) {
            const costoU = pc / conv;
            v = 'Costo por ' + esc(uv || 'unidad de venta') + ': <strong>' + money(costoU) + '</strong>';
            if (ps > 0) {
                const margen = (ps - costoU) / ps * 100;
                v += margen < 0
                    ? ' · <span class="text-danger">¡Vendes por debajo del costo!</span>'
                    : ' · Margen: <strong>' + margen.toFixed(1) + '%</strong>';
            }
        }
        $('#hintVenta').html(v);
    }

    // ===== AÑADIR / ACTUALIZAR EN LA LISTA =====
    tarjeta.on('submit', function (e) {
        e.preventDefault();
        if (!this.checkValidity()) { this.reportValidity(); return; }

        const id = $('#f_id').val();
        const cant = num('#f_cant'), conv = num('#f_conv');
        const item = {
            id: id,
            nombre: CATALOGO[id].nombre,
            cant: cant,
            uc: $('#f_uc').val(),
            pc: num('#f_pc'),
            uv: $('#f_uv').val(),
            conv: conv,
            total: Math.round(cant * conv * 100) / 100,
            ps: num('#f_ps'),
            cat: $('#f_cat').val()
        };

        if (editIdx !== null) lista[editIdx] = item; else lista.push(item);

        renderLista();
        cerrarTarjeta();
        $('#catBuscador').val('').trigger('input').trigger('focus');
    });

    // ===== LISTA DE ENTRADAS PENDIENTES =====
    function renderLista() {
        const tb = $('#cuerpoTablaEntradas').empty();
        let totalCompra = 0;

        lista.forEach(function (it, i) {
            const importe = it.cant * it.pc;
            totalCompra += importe;

            tb.append(`
                <tr class="fila-producto">
                    <td>
                        <input type="hidden" name="id_producto[]"     value="${esc(it.id)}">
                        <input type="hidden" name="precio_compra[]"   value="${it.pc}">
                        <input type="hidden" name="cantidad_compra[]" value="${it.cant}">
                        <input type="hidden" name="unidad_compra[]"   value="${esc(it.uc)}">
                        <input type="hidden" name="precio_sugerido[]" value="${it.ps}">
                        <input type="hidden" name="unidad_venta[]"    value="${esc(it.uv)}">
                        <input type="hidden" name="valor_conversion[]" value="${it.conv}">
                        <input type="hidden" name="cantidad_venta[]"  value="${it.total.toFixed(2)}">
                        <input type="hidden" name="categoria[]"       value="${esc(it.cat)}">
                        <strong>${esc(it.nombre)}</strong><br><small class="text-muted">${esc(it.cat)}</small>
                    </td>
                    <td>${it.cant} ${esc(it.uc)}</td>
                    <td>${money(it.pc)}<br><small class="text-muted">por ${esc(it.uc)}</small></td>
                    <td><strong>${money(importe)}</strong></td>
                    <td>${it.total.toFixed(2)} ${esc(it.uv)}<br><small class="text-muted">(×${it.conv})</small></td>
                    <td>${money(it.ps)}<br><small class="text-muted">por ${esc(it.uv)}</small></td>
                    <td class="text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary btnEditarFila" data-i="${i}" title="Editar"><i class="fas fa-pen"></i></button>
                        <button type="button" class="btn btn-sm btn-danger btnEliminarFila" data-i="${i}" title="Quitar"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`);
        });

        const n = lista.length;
        $('#contadorProductos').text('Productos en lista: ' + n);
        $('#totalCompra').text('Total de la compra: ' + money(totalCompra)).toggle(n > 0);
        $('#vacioMensaje').toggle(n === 0);
        $('.lista-wrap').toggle(n > 0);
        $('#btnGuardarTodo').toggle(n > 0);

        $('.cat-card').removeClass('en-lista');
        lista.forEach(it => $('.cat-card[data-id="' + it.id + '"]').addClass('en-lista'));

        // Microinteracciones
        if (n > 0) {
            $('.lista-wrap').stop(true, true).hide().fadeIn(200);
            $('#btnGuardarTodo').css('transform', 'scale(1.03)');
            setTimeout(() => $('#btnGuardarTodo').css('transform', ''), 180);
        }
    }

    $('#cuerpoTablaEntradas').on('click', '.btnEditarFila', function () {
        abrirTarjeta(lista[$(this).data('i')].id);
    });
    $('#cuerpoTablaEntradas').on('click', '.btnEliminarFila', function () {
        const i = parseInt($(this).data('i'), 10);
        lista.splice(i, 1);
        if (editIdx !== null) {
            if (i === editIdx) cerrarTarjeta(); else if (i < editIdx) editIdx--;
        }
        renderLista();
    });

    // ===== GUARDAR =====
    $('#productForm').on('submit', function (e) {
        if (lista.length === 0) { e.preventDefault(); alert('Agrega al menos un producto a la lista.'); return; }
        $('#btnGuardarTodo').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Guardando...');
    });

    renderLista();

    // ===== OTROS BOTONES DE LA PANTALLA =====
    $('#buscadorTabla').on('keyup', function () {
        const value = $(this).val().toLowerCase();
        $('#tablaBody .fila-producto').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    $('#btnAbrirMerma').on('click', function (e) {
        e.preventDefault();
        window.location.href = '<?= base_url('mermas') ?>';
    });
    $('#btnAbrirExistencias').on('click', function (e) {
        e.preventDefault();
        window.location.href = '<?= base_url('existencias') ?>';
    });
});
</script>
</body>
</html>