<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>FRUVER · Panel Repartidor</title>

    <?= view('Repartidor/partials/tema') ?>

    <link rel="stylesheet" href="<?= base_url('css/menusolo.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Variables específicas de esta vista (el resto vienen de partials/tema.php) */
        :root {
            --bg-topbar: linear-gradient(135deg, #1e5631 0%, #2d6a3b 100%);
            --badge-bg: rgba(255,255,255,0.98);
        }
        html[data-tema="oscuro"] {
            --bg-topbar: linear-gradient(135deg, #0f3d24 0%, #1d4a27 100%);
            --badge-bg: #0f172a;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        [hidden] { display: none !important; }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-page);
            min-height: 100vh;
            color: var(--texto);
            transition: background 0.35s ease, color 0.35s ease;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px 32px;
            padding-bottom: 100px;
        }

        /* ============ TOP BAR ============ */
        .top-bar {
            background: var(--bg-topbar);
            border-radius: 28px;
            padding: 12px 24px;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
            transition: background 0.35s ease;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            min-width: 0;
            flex: 1 1 auto;
        }

        .logo-img {
            height: 68px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .hora-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
        }
        .hora-chip i { font-size: 0.75rem; color: #fbbf24; }

        .admin-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--badge-bg);
            padding: 8px 18px;
            border-radius: 60px;
            transition: background 0.35s ease;
            min-width: 0;
        }
        .admin-badge i { font-size: 1.3rem; color: var(--naranja); flex-shrink: 0; }
        .admin-badge span {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--naranja);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            background: rgba(255,255,255,0.12);
            border-radius: 60px;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 500;
            color: white;
            border: 1px solid rgba(255,255,255,0.25);
            transition: background 0.25s ease, transform 0.2s ease;
            cursor: pointer;
            font-family: inherit;
            white-space: nowrap;
        }
        .action-btn:hover { background: var(--rojo); transform: translateY(-2px); }
        .action-btn.tema-btn:hover { background: var(--naranja); }

        /* ============ BIENVENIDA ============ */
        .welcome-section { text-align: center; margin-bottom: 40px; }
        .welcome-section h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--verde);
            margin-bottom: 8px;
            transition: color 0.35s ease;
        }
        html[data-tema="oscuro"] .welcome-section h1 { color: #4ade80; }

        .welcome-section p { color: var(--texto-suave); transition: color 0.35s ease; }

        /* ============ GRID MÓDULOS ============ */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 28px;
            max-width: 800px;
            margin: 0 auto;
        }

        .module-card {
            background: var(--bg-card);
            border-radius: 28px;
            padding: 28px 20px;
            text-align: center;
            text-decoration: none;
            box-shadow: var(--sombra);
            transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.35s ease;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 4px;
            background: linear-gradient(90deg, var(--verde), var(--naranja));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.35s ease;
        }

        .module-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--sombra-hover);
            border-color: rgba(241,107,26,0.3);
        }
        .module-card:hover::before { transform: scaleX(1); }
        .module-card:active { transform: translateY(-3px) scale(0.99); }

        .card-icon-wrapper {
            width: 100px;
            height: 100px;
            margin: 0 auto 16px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(29,74,39,0.08), rgba(241,107,26,0.08));
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.35s ease;
            position: relative;
        }
        .module-card:hover .card-icon-wrapper { transform: scale(1.08) rotate(-3deg); }

        .card-icon-wrapper > i.fas { font-size: 3.2rem; color: var(--naranja); }

        /* Badge contador (entregas pendientes / pedidos listos) */
        .pedidos-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 28px;
            height: 28px;
            padding: 0 8px;
            background: var(--naranja);
            color: #fff;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 800;
            box-shadow: 0 4px 10px rgba(241,107,26,0.45);
            border: 2px solid var(--bg-card);
            transition: transform 0.25s ease, border-color 0.35s ease;
        }
        .module-card:hover .pedidos-badge { transform: scale(1.1); }
        .pedidos-badge.oculto { display: none; }

        .module-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--texto);
            display: block;
            margin-bottom: 6px;
            transition: color 0.35s ease;
        }

        .module-sub {
            font-size: 0.78rem;
            color: var(--texto-suave);
            font-weight: 500;
        }

        .module-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--naranja);
            color: white;
            margin-top: 14px;
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }
        .module-card:hover .module-arrow { transform: translateX(4px); }

        /* ============ VENTANAS QUE ABREN LAS CARDS ============ */
        .ventana {
            position: fixed; inset: 0; z-index: 950;
            background: rgba(0,0,0,.55);
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
            animation: ventanaFondo 0.2s ease;
        }
        .ventana-caja {
            background: var(--bg-page);
            color: var(--texto);
            width: 100%; max-width: 1150px;
            max-height: 100%;
            border-radius: 28px;
            box-shadow: 0 24px 60px rgba(0,0,0,.35);
            display: flex; flex-direction: column;
            overflow: hidden;
            animation: ventanaCaja 0.25s ease;
        }
        .ventana-cab {
            background: var(--bg-topbar);
            color: #fff;
            padding: 16px 24px;
            display: flex; align-items: center; gap: 12px;
        }
        .ventana-cab h2 { font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; }
        .ventana-cab h2 i { color: #fbbf24; }
        .ventana-cab .contador { font-size: 0.78rem; font-weight: 500; opacity: .85; margin-left: 4px; }
        .ventana-cerrar {
            width: 38px; height: 38px; border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.12); color: #fff;
            cursor: pointer; font-size: 1rem; flex-shrink: 0;
            transition: background 0.25s ease, transform 0.2s ease;
        }
        .ventana-cerrar:hover { background: var(--rojo); transform: rotate(90deg); }
        .ventana-cuerpo { padding: 24px; overflow-y: auto; }
        @keyframes ventanaFondo { from { opacity: 0; } to { opacity: 1; } }
        @keyframes ventanaCaja { from { opacity: 0; transform: translateY(18px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .seccion-titulo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--texto);
            margin: 0 4px 18px;
        }
        .seccion-titulo i { color: var(--naranja); }
        .seccion-titulo .contador { margin-left: auto; font-size: 0.78rem; font-weight: 600; color: var(--texto-suave); }

        .rec-card {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 22px 24px;
            margin-bottom: 22px;
            box-shadow: var(--sombra);
            border-left: 6px solid var(--verde);
            transition: background 0.35s ease;
        }
        .rec-card.acento-naranja { border-left-color: var(--naranja); }
        .rec-card.acento-rojo    { border-left-color: var(--rojo); }
        .rec-card.vacio          { border-left-color: var(--texto-tenue); }

        .rec-head {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            font-size: 1.02rem;
            font-weight: 700;
        }
        .rec-head > i { color: var(--naranja); font-size: 1.15rem; }

        .rec-linea { margin-top: 8px; font-size: 0.88rem; color: var(--texto-suave); display: flex; gap: 8px; align-items: baseline; }
        .rec-linea i { color: var(--naranja); width: 16px; text-align: center; flex-shrink: 0; }
        .rec-linea b { color: var(--texto); }

        .rec-tag {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 50px;
            font-size: 0.72rem; font-weight: 600; white-space: nowrap;
            background: var(--bg-suave); color: var(--texto-suave);
        }
        .rec-tag.ok    { background: var(--ok-bg);   color: var(--ok-tx); }
        .rec-tag.poco  { background: var(--poco-bg); color: var(--poco-tx); }
        .rec-tag.agot  { background: var(--agot-bg); color: var(--agot-tx); }
        .rec-urgente {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 50px;
            font-size: 0.72rem; font-weight: 700; letter-spacing: .3px;
            background: var(--rojo); color: #fff;
        }

        .tabla-compara { overflow-x: auto; margin-top: 14px; }
        .rec-compara { width: 100%; min-width: 520px; border-collapse: collapse; font-size: 0.8rem; }
        .rec-compara th, .rec-compara td { padding: 8px 10px; border-bottom: 1px solid var(--borde-suave); text-align: left; white-space: nowrap; }
        .rec-compara thead th { background: var(--bg-suave); color: var(--texto-suave); font-weight: 600; font-size: 0.72rem; }
        .rec-compara tbody th { font-weight: 600; color: var(--texto-suave); }
        .rec-compara td.ahorro { color: var(--ok-tx); font-weight: 700; }
        .rec-compara td.ahorro small { font-weight: 600; }
        .nota-compara { margin-top: 8px; font-size: 0.72rem; color: var(--texto-tenue); line-height: 1.5; }

        .rec-aviso {
            margin-top: 12px; padding: 10px 14px; border-radius: 14px;
            background: rgba(241,107,26,0.12); color: var(--naranja);
            font-size: 0.82rem; font-weight: 500;
            display: flex; gap: 8px; align-items: flex-start;
        }
        .rec-aviso i { margin-top: 2px; }

        .rec-row { display: flex; gap: 10px; margin-top: 16px; flex-wrap: wrap; }
        .rec-btn {
            flex: 1 1 160px;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 12px 18px;
            border: 0; border-radius: 60px;
            font: 600 0.88rem 'Poppins', sans-serif;
            color: #fff; text-decoration: none; cursor: pointer;
            transition: transform 0.2s ease, filter 0.2s ease;
        }
        .rec-btn:hover { transform: translateY(-2px); filter: brightness(1.08); }
        .rec-btn:active { transform: scale(0.98); }
        .rec-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; filter: none; }
        .btn-verde   { background: var(--verde); }
        .btn-naranja { background: var(--naranja); }
        .btn-gris    { background: #64748b; }
        .btn-azul    { background: #2563eb; }
        html[data-tema="oscuro"] .btn-verde { background: #16a34a; }

        /* ============ TABLA DE PEDIDOS ASIGNADOS ============ */
        .tabla-scroll {
            background: var(--bg-card);
            border-radius: 24px;
            box-shadow: var(--sombra);
            overflow-x: auto;
        }
        .tabla-pedidos { width: 100%; min-width: 980px; border-collapse: collapse; font-size: 0.85rem; }
        .tabla-pedidos thead th {
            background: var(--verde);
            color: #fff;
            font-weight: 600;
            font-size: 0.78rem;
            text-align: left;
            padding: 14px 14px;
            white-space: nowrap;
        }
        html[data-tema="oscuro"] .tabla-pedidos thead th { background: #153a1e; }
        .tabla-pedidos tbody td {
            padding: 14px;
            border-bottom: 1px solid var(--borde-suave);
            vertical-align: middle;
        }
        .tabla-pedidos tbody tr:last-child td { border-bottom: 0; }
        .tabla-pedidos tbody tr:hover { background: var(--bg-suave); }
        .tabla-pedidos tr.entregado td { opacity: .7; }
        .tabla-pedidos .num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .tabla-pedidos .centro { text-align: center; }
        .tabla-pedidos .folio { font-weight: 700; white-space: nowrap; }
        .tabla-pedidos .cliente { font-weight: 600; }
        .tabla-pedidos .por-cobrar { font-weight: 700; color: var(--naranja); }
        .tabla-pedidos a { color: var(--naranja); text-decoration: none; font-weight: 500; }
        .tabla-pedidos a:hover { text-decoration: underline; }
        .tabla-pedidos .dir { min-width: 220px; }
        .tabla-pedidos .dir small { display: block; margin-top: 4px; }
        .tabla-pedidos .orden-num {
            display: inline-flex; align-items: center; justify-content: center;
            width: 28px; height: 28px; border-radius: 50%;
            background: rgba(241,107,26,0.14); color: var(--naranja); font-weight: 700;
        }
        .tabla-vacia { padding: 34px 20px; text-align: center; color: var(--texto-suave); }
        .tabla-vacia i { display: block; font-size: 1.8rem; margin-bottom: 10px; color: var(--texto-tenue); }

        .btn-evidencia {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border: 0; border-radius: 60px;
            background: var(--verde); color: #fff;
            font: 600 0.78rem 'Poppins', sans-serif; cursor: pointer; white-space: nowrap;
            transition: transform 0.2s ease, filter 0.2s ease;
        }
        html[data-tema="oscuro"] .btn-evidencia { background: #16a34a; }
        .btn-evidencia:hover { transform: translateY(-2px); filter: brightness(1.08); }
        .btn-evidencia:disabled { background: #94a3b8; cursor: not-allowed; transform: none; filter: none; }

        .acciones-fila { display: flex; flex-direction: column; gap: 6px; align-items: stretch; }
        .btn-fallo {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 6px 12px; border-radius: 60px;
            background: transparent; color: var(--rojo);
            border: 1.5px solid var(--rojo);
            font: 600 0.74rem 'Poppins', sans-serif; cursor: pointer; white-space: nowrap;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .btn-fallo:hover { background: var(--rojo); color: #fff; }

        .btn-wa {
            display: inline-flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: 50%;
            background: #25d366; color: #fff !important; text-decoration: none !important;
            font-size: 1rem; margin-left: 6px; vertical-align: middle;
            transition: transform 0.2s ease;
        }
        .btn-wa:hover { transform: scale(1.12); }

        .miniatura {
            display: inline-block; padding: 0; border: 2px solid var(--borde);
            border-radius: 12px; overflow: hidden; background: none; cursor: zoom-in;
            width: 56px; height: 56px; transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .miniatura:hover { transform: scale(1.08); border-color: var(--naranja); }
        .miniatura img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .nota-fallo { display: block; margin-top: 4px; font-size: 0.7rem; color: var(--texto-suave); max-width: 180px; margin-inline: auto; }
        .tabla-pedidos tr.fallido td { background: rgba(220,38,38,0.05); }

        /* ============ RESUMEN DEL DÍA ============ */
        .resumen-dia { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 22px; }
        .resumen-item {
            background: var(--bg-card); border-radius: 20px; padding: 16px 18px;
            box-shadow: var(--sombra); display: flex; align-items: center; gap: 14px;
        }
        .resumen-item > i {
            width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, rgba(29,74,39,0.1), rgba(241,107,26,0.12));
            color: var(--naranja); font-size: 1.15rem;
            display: flex; align-items: center; justify-content: center;
        }
        .resumen-item div { display: flex; flex-direction: column; min-width: 0; }
        .resumen-item .rv { font-size: 1.3rem; font-weight: 700; line-height: 1.2; }
        .resumen-item .rl { font-size: 0.74rem; color: var(--texto-suave); font-weight: 500; }
        .resumen-item .rs { font-size: 0.7rem; color: var(--naranja); font-weight: 500; margin-top: 2px; }

        /* ============ NO PUDE ENTREGAR / VISOR DE FOTO ============ */
        #sheetFallo, #visorFoto {
            position: fixed; inset: 0; background: rgba(0,0,0,.6);
            display: none; align-items: center; justify-content: center; z-index: 1040; padding: 16px;
        }
        #sheetFallo .caja {
            background: var(--bg-card); color: var(--texto);
            width: 100%; max-width: 440px; max-height: 94vh; overflow-y: auto;
            padding: 22px; border-radius: 28px;
        }
        #sheetFallo h3 { display: flex; align-items: center; gap: 10px; color: var(--rojo); font-size: 1.1rem; }
        .motivo {
            display: flex; align-items: center; gap: 12px;
            width: 100%; margin-top: 10px; padding: 13px 16px;
            border: 1.5px solid var(--borde); background: var(--bg-suave); color: var(--texto);
            border-radius: 16px; font: 600 0.9rem 'Poppins', sans-serif; text-align: left; cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease;
        }
        .motivo i { color: var(--naranja); width: 20px; text-align: center; }
        .motivo:hover { border-color: var(--naranja); }
        .motivo.sel { border-color: var(--rojo); background: rgba(220,38,38,0.1); }
        .motivo.sel i { color: var(--rojo); }
        #fNota {
            width: 100%; margin-top: 12px; padding: 12px 14px;
            border: 1.5px solid var(--borde); border-radius: 14px;
            font: 0.9rem 'Poppins', sans-serif; background: var(--bg-suave); color: var(--texto);
        }
        #visorFoto { z-index: 1050; background: rgba(0,0,0,.85); cursor: zoom-out; }
        #visorFoto img { max-width: 100%; max-height: 92vh; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.5); }
        #visorFoto .cerrar {
            position: absolute; top: 16px; right: 16px; width: 42px; height: 42px; border-radius: 50%;
            border: 0; background: rgba(255,255,255,0.18); color: #fff; font-size: 1.1rem; cursor: pointer;
        }

        /* ============ CATÁLOGO DE PRODUCTOS ============ */
        .buscador {
            display: flex; align-items: center; gap: 10px;
            background: var(--bg-card);
            border: 1.5px solid var(--borde);
            border-radius: 60px;
            padding: 4px 18px;
            margin-bottom: 20px;
            box-shadow: var(--sombra);
        }
        .buscador i { color: var(--naranja); }
        .buscador input {
            flex: 1; border: 0; outline: 0; background: transparent;
            padding: 11px 0; font: 0.92rem 'Poppins', sans-serif; color: var(--texto);
        }

        .catalogo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 20px;
        }
        .prod-card {
            background: var(--bg-card);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--sombra);
            display: flex; flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .prod-card:hover { transform: translateY(-5px); box-shadow: var(--sombra-hover); }
        .prod-img {
            height: 130px;
            background: linear-gradient(135deg, rgba(29,74,39,0.08), rgba(241,107,26,0.08));
            display: flex; align-items: center; justify-content: center;
        }
        .prod-img img { width: 100%; height: 100%; object-fit: cover; }
        .prod-img i { font-size: 2.8rem; color: var(--naranja); }
        .prod-body { padding: 16px 18px 18px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
        .prod-nombre { font-size: 1rem; font-weight: 700; }
        .prod-codigo { font-size: 0.72rem; color: var(--texto-tenue); }
        .prod-desc { font-size: 0.78rem; color: var(--texto-suave); }
        .prod-stock { margin-top: auto; padding-top: 10px; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .prod-stock b { font-size: 1.05rem; }
        .prod-stock.ok b   { color: var(--ok-tx); }
        .prod-stock.poco b { color: var(--poco-tx); }
        .prod-stock.agot b { color: var(--agot-tx); }
        .prod-precio { font-size: 0.82rem; color: var(--texto-suave); }
        .prod-precio b { color: var(--naranja); font-size: 0.95rem; }

        /* ============ HOJA DE EVIDENCIA ============ */
        #sheetEntrega {
            position: fixed; inset: 0; background: rgba(0,0,0,.55);
            display: none; align-items: flex-end; justify-content: center; z-index: 1000;
        }
        #sheetEntrega .caja {
            background: var(--bg-card); color: var(--texto);
            width: 100%; max-width: 560px; max-height: 94vh; overflow-y: auto;
            padding: 22px; border-radius: 28px 28px 0 0;
        }
        #sheetEntrega h3 { display: flex; align-items: center; gap: 10px; color: var(--verde); font-size: 1.1rem; }
        html[data-tema="oscuro"] #sheetEntrega h3 { color: #4ade80; }
        #sheetEntrega label.campo { display: block; font-size: 0.8rem; font-weight: 600; color: var(--texto-suave); margin: 14px 0 6px; }
        #sheetEntrega label.campo i { color: var(--naranja); width: 18px; }
        #sheetEntrega input[type=number], #sheetEntrega select, #sheetEntrega textarea {
            width: 100%; padding: 12px 14px;
            border: 1.5px solid var(--borde); border-radius: 14px;
            font: 0.95rem 'Poppins', sans-serif;
            background: var(--bg-suave); color: var(--texto);
        }
        #entFirma { width: 100%; height: 150px; border: 2px dashed var(--texto-tenue); border-radius: 14px; background: #fff; touch-action: none; display: block; }
        #entPrev { max-width: 100%; max-height: 240px; border-radius: 14px; margin-top: 10px; display: none; }
        .chk { display: flex; gap: 8px; align-items: center; font-size: 0.85rem; margin-top: 14px; color: var(--texto-suave); }

        #msg {
            position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
            width: calc(100% - 32px); max-width: 480px; z-index: 1100;
            background: var(--verde); color: #fff; padding: 12px 18px; border-radius: 14px;
            display: none; text-align: center; font-size: 0.88rem; font-weight: 600;
            box-shadow: 0 8px 20px rgba(0,0,0,.25);
        }
        html[data-tema="oscuro"] #msg { background: #4ade80; color: #0f172a; }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 1024px) {
            .top-bar { padding: 12px 20px; }
            .logo-img { height: 58px; }
            .admin-badge span { max-width: 160px; }
        }

        @media (max-width: 900px) {
            .dashboard-container { padding: 18px 20px; padding-bottom: 100px; }

            .top-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
                padding: 14px 18px;
            }
            .top-bar-left { justify-content: center; gap: 12px; }
            .action-buttons {
                justify-content: center;
                width: 100%;
                padding-top: 12px;
                border-top: 1px solid rgba(255,255,255,0.15);
            }
        }

        @media (max-width: 600px) {
            .dashboard-container { padding: 14px; padding-bottom: 100px; }
            .top-bar { border-radius: 20px; padding: 12px 14px; }

            .top-bar-left { flex-direction: column; gap: 10px; }
            .logo-img { height: 50px; }
            .admin-badge { width: 100%; justify-content: center; padding: 7px 14px; }
            .admin-badge span { max-width: none; }
            .hora-chip { font-size: 0.75rem; padding: 5px 12px; }

            .welcome-section h1 { font-size: 1.5rem; }
            .welcome-section { margin-bottom: 28px; }

            .modules-grid { grid-template-columns: 1fr; gap: 18px; }
            .action-btn { padding: 7px 14px; font-size: 0.78rem; }
            .rec-card { padding: 18px; }
            .ventana { padding: 0; align-items: stretch; }
            .ventana-caja { border-radius: 0; max-width: none; }
            .ventana-cab { padding: 14px 16px; }
            .ventana-cuerpo { padding: 16px; }
            .catalogo-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
            .prod-img { height: 100px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
            html { scroll-behavior: auto; }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <div class="top-bar">
        <div class="top-bar-left">
            <img src="<?= base_url('img/LOGO1.png') ?>" class="logo-img" alt="Logo FRUVER">

            <span class="hora-chip" id="horaChip" title="Hora actual">
                <i class="fas fa-clock"></i>
                <span id="horaTexto">--:--</span>
            </span>

            <div class="admin-badge">
                <i class="fa-solid fa-motorcycle"></i>
                <span>Repartidor: <?= esc(session()->get('nombre')) ?></span>
            </div>
        </div>

        <div class="action-buttons">
            <button type="button" class="action-btn tema-btn" id="btnTema" aria-label="Cambiar tema">
                <i class="fas fa-moon"></i> <span class="tema-texto">Oscuro</span>
            </button>
            <a href="<?= base_url('logout') ?>" class="action-btn">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>
    </div>

    <div class="welcome-section">
        <h1>Bienvenido de vuelta, <?= esc(session()->get('nombre')) ?></h1>
        <p>Selecciona una opción para comenzar tu ruta.</p>
    </div>

    <div class="modules-grid">
        <a href="#" class="module-card" id="cardRecorrido" onclick="abrirVentana('recorrido'); return false;">
            <div class="card-icon-wrapper">
                <i class="fas fa-route"></i>
                <span class="pedidos-badge oculto" id="recorridoBadge">0</span>
            </div>
            <span class="module-title">Mi Recorrido</span>
            <span class="module-sub">Pedidos asignados, recorrido y evidencia</span>
            <span class="module-arrow"><i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="#" class="module-card" id="cardCatalogo" onclick="abrirVentana('catalogo'); return false;">
            <div class="card-icon-wrapper">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <span class="module-title">Consultar Stock</span>
            <span class="module-sub">Catálogo de productos y su disponibilidad</span>
            <span class="module-arrow"><i class="fas fa-arrow-right"></i></span>
        </a>
    </div>
</div>

<!-- VENTANA: MI RECORRIDO (iniciar recorrido, mapa y tabla de pedidos) -->
<div class="ventana" id="ventanaRecorrido" hidden onclick="if (event.target === this) cerrarVentana()">
    <div class="ventana-caja" role="dialog" aria-modal="true" aria-labelledby="tituloRecorrido">
        <div class="ventana-cab">
            <h2 id="tituloRecorrido"><i class="fas fa-truck-fast"></i> Mi recorrido</h2>
            <button type="button" class="ventana-cerrar" onclick="cerrarVentana()" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="ventana-cuerpo">
            <div id="resumenDia"></div>
            <div id="recorridoTop"></div>

            <h2 class="seccion-titulo">
                <i class="fas fa-clipboard-list"></i> Pedidos asignados
                <span class="contador" id="pedidosContador"></span>
            </h2>
            <div class="tabla-scroll">
                <table class="tabla-pedidos">
                    <thead>
                        <tr>
                            <th class="centro">Orden</th>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Dirección</th>
                            <th class="centro">Llegada est.</th>
                            <th class="num">Total</th>
                            <th class="num">Por cobrar</th>
                            <th>Pago</th>
                            <th>Estado</th>
                            <th class="centro">Evidencia</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPedidos"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- VENTANA: CONSULTAR STOCK (catálogo de productos) -->
<div class="ventana" id="ventanaCatalogo" hidden onclick="if (event.target === this) cerrarVentana()">
    <div class="ventana-caja" role="dialog" aria-modal="true" aria-labelledby="tituloCatalogo">
        <div class="ventana-cab">
            <h2 id="tituloCatalogo"><i class="fas fa-boxes-stacked"></i> Catálogo de productos <span class="contador" id="catalogoContador"></span></h2>
            <button type="button" class="ventana-cerrar" onclick="cerrarVentana()" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="ventana-cuerpo">
            <div class="buscador">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" id="buscadorCatalogo" placeholder="Busca un producto (ej. fresa)..." autocomplete="off">
            </div>
            <div class="catalogo-grid" id="catalogoGrid"></div>
        </div>
    </div>
</div>

<!-- Evidencia de entrega: foto de prueba, cobro, firma y nota -->
<div id="sheetEntrega" onclick="if (event.target === this) cerrarEntrega()">
    <div class="caja">
        <h3><i class="fas fa-camera"></i> Evidencia de entrega</h3>
        <div style="margin-top:8px"><b id="entCliente"></b></div>
        <div class="rec-linea" id="entDinero" style="margin-top:4px"></div>

        <label class="campo"><i class="fas fa-camera"></i> Foto de prueba de que se entregó</label>
        <label class="rec-btn btn-azul" for="entFoto" style="cursor:pointer"><i class="fas fa-camera"></i> Tomar foto</label>
        <input type="file" id="entFoto" accept="image/*" capture="environment" style="display:none">
        <img id="entPrev" alt="Vista previa de la foto">

        <div id="entCobroBox">
            <label class="campo" for="entCobro"><i class="fas fa-hand-holding-dollar"></i> ¿Cuánto cobraste?</label>
            <input type="number" id="entCobro" min="0" step="0.01" inputmode="decimal" placeholder="0.00">
            <label class="campo" for="entMetodo"><i class="fas fa-credit-card"></i> Forma de pago</label>
            <select id="entMetodo">
                <option value="efectivo">Efectivo</option>
                <option value="transferencia">Transferencia</option>
                <option value="tarjeta">Tarjeta</option>
            </select>
        </div>

        <label class="campo"><i class="fas fa-signature"></i> Firma del cliente (opcional)</label>
        <canvas id="entFirma"></canvas>
        <div class="rec-row" style="margin-top:8px">
            <button type="button" class="rec-btn btn-gris" onclick="borrarFirma()"><i class="fas fa-eraser"></i> Borrar firma</button>
        </div>

        <label class="campo" for="entNota"><i class="fas fa-note-sticky"></i> Nota (opcional)</label>
        <textarea id="entNota" rows="2" placeholder="Ej. lo recibió el portero"></textarea>
        <label class="chk"><input type="checkbox" id="entSinEvidencia" style="width:auto"> Sin evidencia (explica el motivo en la nota)</label>

        <div class="rec-row" style="margin-top:18px">
            <button type="button" class="rec-btn btn-gris" onclick="cerrarEntrega()"><i class="fas fa-xmark"></i> Cancelar</button>
            <button type="button" class="rec-btn btn-verde" id="entBtn" onclick="confirmarEntrega()"><i class="fas fa-check"></i> Confirmar entrega</button>
        </div>
    </div>
</div>

<!-- No pude entregar: motivo y nota; se avisa al administrador con la ubicación -->
<div id="sheetFallo" onclick="if (event.target === this) cerrarFallo()">
    <div class="caja">
        <h3><i class="fas fa-ban"></i> No pude entregar</h3>
        <div style="margin-top:8px"><b id="fCliente"></b></div>
        <div class="rec-linea" style="margin-top:4px"><i class="fas fa-circle-info"></i><span>El pedido queda pendiente y el administrador recibe el aviso con tu ubicación.</span></div>

        <button type="button" class="motivo" data-motivo="cliente_ausente"><i class="fas fa-user-clock"></i> Cliente ausente</button>
        <button type="button" class="motivo" data-motivo="direccion_incorrecta"><i class="fas fa-map-location-dot"></i> Dirección incorrecta</button>
        <button type="button" class="motivo" data-motivo="cliente_rechazo"><i class="fas fa-hand"></i> El cliente rechazó el pedido</button>
        <button type="button" class="motivo" data-motivo="otro"><i class="fas fa-ellipsis"></i> Otro motivo</button>

        <textarea id="fNota" rows="2" placeholder="Nota (obligatoria si elegiste otro motivo)"></textarea>

        <div class="rec-row" style="margin-top:18px">
            <button type="button" class="rec-btn btn-gris" onclick="cerrarFallo()"><i class="fas fa-xmark"></i> Cancelar</button>
            <button type="button" class="rec-btn btn-naranja" id="fBtn" onclick="confirmarFallo()"><i class="fas fa-paper-plane"></i> Avisar y cerrar parada</button>
        </div>
    </div>
</div>

<!-- Visor de la foto de evidencia -->
<div id="visorFoto" onclick="cerrarFoto()">
    <button type="button" class="cerrar" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
    <img id="visorImg" alt="Foto de evidencia">
</div>

<div id="msg"></div>

<?= view('Repartidor/partials/ayuda_gps') ?>

<script>
const API = "<?= base_url('repartidor/api') ?>";
const URL_IMG = "<?= base_url('uploads/productos') ?>/";
const REPARTIDOR = <?= json_encode((string) session()->get('nombre'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

/* ============ TEMA CLARO / OSCURO ============ */
(function () {
    const html = document.documentElement;
    const btn  = document.getElementById('btnTema');
    const txt  = btn.querySelector('.tema-texto');
    const ico  = btn.querySelector('i');

    const aplicar = (tema) => {
        html.setAttribute('data-tema', tema);
        if (tema === 'oscuro') {
            ico.className = 'fas fa-sun';
            txt.textContent = 'Claro';
        } else {
            ico.className = 'fas fa-moon';
            txt.textContent = 'Oscuro';
        }
    };

    // El partial ya aplicó el tema al <html> antes de pintar; solo sincronizamos el botón
    aplicar(html.getAttribute('data-tema') || 'claro');

    btn.addEventListener('click', () => {
        const actual = html.getAttribute('data-tema');
        const nuevo  = actual === 'oscuro' ? 'claro' : 'oscuro';
        aplicar(nuevo);
        localStorage.setItem('fruver-tema', nuevo);
    });
})();

/* ============ HORA EN EL ENCABEZADO ============ */
(function () {
    const el = document.getElementById('horaTexto');
    const tick = () => {
        const now = new Date();
        el.textContent = now.toLocaleTimeString('es-MX', {hour: '2-digit', minute: '2-digit'});
    };
    tick();
    setInterval(tick, 30000);
})();

/* ============ UTILIDADES ============ */
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const post = (url, obj) => fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json'},
    body: new URLSearchParams(obj)
}).then(r => r.json());
const km = n => Number(n || 0).toFixed(1);
const dinero = n => '$' + Number(n || 0).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});
const folio = id => 'PED-' + String(id).padStart(5, '0');
const sinAcentos = s => String(s ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
const num = n => { const v = parseFloat(n); return Number.isFinite(v) ? v : 0; };

function aviso(t) {
    const m = document.getElementById('msg');
    m.textContent = t;
    m.style.display = 'block';
    clearTimeout(m._t);
    m._t = setTimeout(() => m.style.display = 'none', 3200);
}

function ponerBadge(n) {
    const b = document.getElementById('recorridoBadge');
    n = Number(n) || 0;
    b.textContent = n > 99 ? '99+' : n;
    b.classList.toggle('oculto', n <= 0);
}

/* ============ CARDS: ABRIR / CERRAR VENTANAS ============ */
const VENTANAS = { recorrido: 'ventanaRecorrido', catalogo: 'ventanaCatalogo' };

function abrirVentana(nombre) {
    cerrarVentana();
    document.getElementById(VENTANAS[nombre]).hidden = false;
    document.body.style.overflow = 'hidden';   // el panel de fondo no se desplaza mientras hay una ventana abierta

    if (nombre === 'catalogo') {
        cargarCatalogo();
        document.getElementById('buscadorCatalogo').focus({preventScroll: true});
    }
    if (nombre === 'recorrido') cargar(true);
}

function cerrarVentana() {
    Object.values(VENTANAS).forEach(id => { document.getElementById(id).hidden = true; });
    document.body.style.overflow = '';
}

// Escape cierra la ventana (si la hoja de evidencia está abierta, se cierra esa primero)
document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    // Se cierra lo que esté más arriba: foto, no pude entregar, evidencia o la ventana
    if (document.getElementById('visorFoto').style.display === 'flex') cerrarFoto();
    else if (document.getElementById('sheetFallo').style.display === 'flex') cerrarFallo();
    else if (document.getElementById('sheetEntrega').style.display === 'flex') cerrarEntrega();
    else cerrarVentana();
});

/* ============ CONSULTAR STOCK: CATÁLOGO ============ */
const UMBRAL_POCO = 5;
let catalogo = [];

async function cargarCatalogo() {
    const grid = document.getElementById('catalogoGrid');
    if (!catalogo.length) grid.innerHTML = '<div class="tabla-vacia" style="grid-column:1/-1"><i class="fas fa-spinner fa-spin"></i> Cargando catálogo...</div>';
    try {
        const d = await (await fetch(API + '/catalogo')).json();
        catalogo = d.data || [];
        pintarCatalogo();
    } catch (e) {
        grid.innerHTML = '<div class="tabla-vacia" style="grid-column:1/-1"><i class="fas fa-wifi"></i> No se pudo cargar el catálogo. Revisa tu conexión.</div>';
    }
}

function pintarCatalogo() {
    const q = sinAcentos(document.getElementById('buscadorCatalogo').value.trim());
    const lista = catalogo.filter(p => !q || sinAcentos(p.nombre + ' ' + (p.codigo || '') + ' ' + (p.descripcion || '')).includes(q));
    document.getElementById('catalogoContador').textContent = lista.length + ' de ' + catalogo.length + ' productos';

    const grid = document.getElementById('catalogoGrid');
    if (!lista.length) {
        grid.innerHTML = '<div class="tabla-vacia" style="grid-column:1/-1"><i class="fas fa-magnifying-glass"></i> No hay productos con ese nombre.</div>';
        return;
    }

    grid.innerHTML = lista.map(p => {
        const disp   = num(p.disponible);
        const nivel  = disp <= 0 ? 'agot' : (disp <= UMBRAL_POCO ? 'poco' : 'ok');
        const texto  = disp <= 0 ? 'Agotado' : (disp <= UMBRAL_POCO ? 'Poco stock' : 'Disponible');
        const unidad = p.unidad ? ' ' + esc(p.unidad) : '';
        return `<div class="prod-card">
            <div class="prod-img">${p.imagen
                ? `<img src="${URL_IMG}${encodeURIComponent(p.imagen)}" alt="${esc(p.nombre)}" loading="lazy">`
                : '<i class="fas fa-apple-whole"></i>'}</div>
            <div class="prod-body">
                <span class="prod-nombre">${esc(p.nombre)}</span>
                ${p.codigo ? `<span class="prod-codigo">${esc(p.codigo)}</span>` : ''}
                ${p.descripcion ? `<span class="prod-desc">${esc(p.descripcion)}</span>` : ''}
                <div class="prod-stock ${nivel}">
                    <span class="rec-tag ${nivel}">${texto}</span>
                    <b>${disp}${unidad}</b>
                </div>
                <div class="prod-precio">${p.precio !== null ? `<b>${dinero(p.precio)}</b>${p.unidad ? ' / ' + esc(p.unidad) : ''}` : 'Sin precio'}</div>
            </div>
        </div>`;
    }).join('');
}
document.getElementById('buscadorCatalogo').addEventListener('input', pintarCatalogo);

/* ============ MI RECORRIDO ============ */
let salidaActual = null;   // recorrido en curso (null si no ha salido)
let previa = null;         // vista previa antes de salir
let previaError = '';
let asignados = [];        // pedidos asignados al repartidor
let iniciando = false;
let ultimaFirma = '';
let hoy = { entregas: 0, fallidas: 0, cobrado: { total: 0, efectivo: 0, transferencia: 0, tarjeta: 0 }, km: 0, hechas: [] };

async function pedirJson(ruta) { return (await fetch(API + ruta)).json(); }

async function cargar(forzar) {
    try {
        const [activa, lista, resumen] = await Promise.all([pedirJson('/salida-activa'), pedirJson('/pedidos'), pedirJson('/resumen')]);
        salidaActual = activa.data || null;
        asignados = lista.data || [];
        if (resumen.success) hoy = resumen.data;

        previa = null; previaError = '';
        if (!salidaActual) {
            const pr = await pedirJson('/recorrido/previa');
            if (pr.success) previa = pr.data; else previaError = pr.message || 'Intenta de nuevo en unos segundos.';
        }

        // Solo se vuelve a dibujar si algo cambió (evita parpadeos cada 8 s)
        const firma = JSON.stringify([salidaActual, previa, previaError, asignados, hoy]);
        if (forzar || firma !== ultimaFirma) {
            ultimaFirma = firma;
            pintarRecorrido();
        }
    } catch (e) { /* sin conexión: se reintenta en el siguiente ciclo */ }
}

/*
 * Filas de la tabla: las paradas del recorrido en curso, o los pedidos asignados si todavía no sale.
 * Al final se agregan las paradas que ya se cerraron hoy (para ver su resultado y su foto).
 * cierre: null (abierta), 'entregado' o 'fallido'.
 */
function filasTabla() {
    let filas;

    if (salidaActual) {
        filas = (salidaActual.paradas || []).map(p => ({
            orden: p.orden, id: p.id_pedido,
            cliente: [p.cliente, p.apellido_paterno].filter(Boolean).join(' ') || 'Cliente',
            tel: p.tel, direccion: p.direccion || 'Sin dirección', lat: p.lat, lng: p.lng,
            total: num(p.pedido_total), pagado: num(p.pedido_pagado), tipo_pago: p.tipo_pago,
            estado: p.estado === 'entregado' ? 'Entregado' : (p.estado === 'fallido' ? 'No entregado' : (p.estado === 'en_ruta' ? 'En ruta' : 'Pendiente')),
            cierre: p.estado === 'entregado' ? 'entregado' : (p.estado === 'fallido' ? 'fallido' : null),
            tieneFoto: !!p.foto_entrega, nota: p.nota || '', llegada: p.llegada_est || null,
            prioritario: Number(p.prioritario) === 1,
            puedeEvidencia: p.estado === 'pendiente' || p.estado === 'en_ruta'
        }));
    } else {
        const orden = new Map(((previa && previa.paradas) || []).map(x => [Number(x.id_pedido), Number(x.orden)]));
        const llegadas = new Map(((previa && previa.paradas) || []).map(x => [Number(x.id_pedido), x.llegada]));
        filas = asignados.map(p => ({
            orden: orden.has(Number(p.id)) ? orden.get(Number(p.id)) : null, id: p.id,
            cliente: p.cliente || 'Cliente', tel: p.tel,
            direccion: p.calle ? `${p.calle} #${p.numero}, ${p.colonia}, ${p.municipio || ''} ${p.estado_dir || ''}`.trim() : 'Sin dirección registrada',
            lat: p.lat, lng: p.lng,
            total: num(p.total), pagado: num(p.monto_pagado), tipo_pago: p.tipo_pago,
            estado: p.estado_actual, cierre: null, tieneFoto: false, nota: '', llegada: llegadas.get(Number(p.id)) || null,
            prioritario: Number(p.prioritario) === 1, puedeEvidencia: false
        })).sort((a, b) => (a.orden ?? 999) - (b.orden ?? 999) || Number(b.prioritario) - Number(a.prioritario) || a.id - b.id);
    }

    // Lo cerrado hoy en otros recorridos (sin repetir los pedidos que ya están en la lista)
    const ids = new Set(filas.map(f => Number(f.id)));
    (hoy.hechas || []).forEach(h => {
        if (ids.has(Number(h.id_pedido))) return;
        ids.add(Number(h.id_pedido));
        filas.push({
            orden: null, deHoy: true, id: h.id_pedido,
            cliente: [h.cliente, h.apellido_paterno].filter(Boolean).join(' ') || 'Cliente',
            tel: h.tel, direccion: h.direccion || 'Sin dirección', lat: h.lat, lng: h.lng,
            total: num(h.pedido_total), pagado: num(h.pedido_pagado), tipo_pago: h.tipo_pago,
            estado: h.estado === 'entregado' ? 'Entregado' : 'No entregado', cierre: h.estado,
            tieneFoto: !!h.tiene_foto, nota: h.nota || '',
            prioritario: Number(h.prioritario) === 1, puedeEvidencia: false
        });
    });

    return filas;
}

/* Teléfono para wa.me: solo dígitos y con la lada de México si vienen 10 */
function telefonoWa(t) {
    let n = String(t || '').replace(/\D/g, '');
    if (n.length === 10) n = '52' + n;
    return n;
}

/* Enlace de WhatsApp para avisar que voy en camino (sin emojis: WhatsApp para Windows los rompe en enlaces wa.me) */
function enlaceWa(f, porCobrar) {
    const n = telefonoWa(f.tel);
    if (n.length < 10) return null;
    const nombre = String(f.cliente).trim().split(/\s+/)[0] || '';
    const texto = `Hola ${nombre}, soy ${REPARTIDOR} de FRUVER. Voy en camino con tu pedido ${folio(f.id)}.`
        + (porCobrar > 0.009 ? ` Tienes un saldo por pagar de ${dinero(porCobrar)}.` : '')
        + ' Por favor estate atento. Gracias.';
    return 'https://wa.me/' + n + '?text=' + encodeURIComponent(texto);
}

function pintarRecorrido() {
    pintarResumen();
    pintarTope();
    pintarTabla();
}

/* Resumen del día: entregas, cobrado por método y km de ruta */
function pintarResumen() {
    const c = hoy.cobrado || {};
    const metodos = [['efectivo', 'Efectivo'], ['transferencia', 'Transferencia'], ['tarjeta', 'Tarjeta']]
        .filter(([k]) => num(c[k]) > 0)
        .map(([k, t]) => `${t} ${dinero(c[k])}`).join(' · ');

    document.getElementById('resumenDia').innerHTML = `<div class="resumen-dia">
        <div class="resumen-item"><i class="fas fa-circle-check"></i><div>
            <span class="rv">${num(hoy.entregas)}</span><span class="rl">Entregas hechas hoy</span>
            ${num(hoy.fallidas) ? `<span class="rs">${num(hoy.fallidas)} no entregada${num(hoy.fallidas) === 1 ? '' : 's'}</span>` : ''}
        </div></div>
        <div class="resumen-item"><i class="fas fa-hand-holding-dollar"></i><div>
            <span class="rv">${dinero(c.total)}</span><span class="rl">Cobrado hoy</span>
            <span class="rs">${metodos || 'Sin cobros todavía'}</span>
        </div></div>
        <div class="resumen-item"><i class="fas fa-road"></i><div>
            <span class="rv">${km(hoy.km)} km</span><span class="rl">Km de ruta hoy</span>
        </div></div>
    </div>`;
}

/* Parte de arriba: INICIAR RECORRIDO y el mapa del recorrido completo */
function pintarTope() {
    const box = document.getElementById('recorridoTop');

    if (salidaActual) {
        const lista = salidaActual.paradas || [];
        const hechas = lista.filter(p => p.estado === 'entregado').length;
        const fallidas = lista.filter(p => p.estado === 'fallido').length;
        ponerBadge(lista.length - hechas - fallidas);
        box.innerHTML = `<div class="rec-card acento-naranja">
            <div class="rec-head"><i class="fas fa-truck-fast"></i> Recorrido en curso</div>
            <div class="rec-linea"><i class="fas fa-list-check"></i><span><b>${hechas} de ${lista.length}</b> entregas completadas${fallidas ? ` · ${fallidas} no entregada${fallidas === 1 ? '' : 's'}` : ''}</span></div>
            <div class="rec-linea"><i class="fas fa-road"></i><span>${km(salidaActual.km_total)} km con regreso al local</span></div>
            ${bloqueRuta(salidaActual.optimizacion && salidaActual.optimizacion.tiempo, salidaActual.optimizacion && salidaActual.optimizacion.comparativo)}
            ${salidaActual.google_maps_url ? `<div class="rec-row"><a class="rec-btn btn-azul" target="_blank" rel="noopener" href="${esc(salidaActual.google_maps_url)}"><i class="fas fa-map"></i> Mapa del recorrido completo</a></div>` : ''}
        </div>`;
        return;
    }

    if (previaError) {
        ponerBadge(0);
        box.innerHTML = `<div class="rec-card acento-rojo">
            <div class="rec-head"><i class="fas fa-triangle-exclamation"></i> No se pudo cargar tu recorrido</div>
            <div class="rec-linea"><span>${esc(previaError)}</span></div></div>`;
        return;
    }

    if (!previa || !previa.listos) {
        ponerBadge(0);
        box.innerHTML = `<div class="rec-card vacio">
            <div class="rec-head"><i class="fas fa-inbox"></i> Sin pedidos listos para salir</div>
            <div class="rec-linea"><span>Cuando el administrador te asigne pedidos confirmados, podrás iniciar tu recorrido desde aquí.</span></div>
        </div>`;
        return;
    }

    ponerBadge(previa.listos);
    const p = previa;
    const bloqueado = (p.sin_ubicacion || []).length > 0;

    let extra = '';
    if (p.para_despues > 0) extra += `<div class="rec-aviso"><i class="fas fa-circle-info"></i><span>Tienes ${p.listos} pedidos listos. En este recorrido salen ${p.en_este_recorrido} (máximo 6); los demás irán en el siguiente.</span></div>`;
    if (bloqueado) extra += `<div class="rec-aviso"><i class="fas fa-triangle-exclamation"></i><span>${p.sin_ubicacion.length} pedido(s) no tienen ubicación en el mapa. Avisa al administrador para poder salir.</span></div>`;
    (p.advertencias || []).forEach(a => extra += `<div class="rec-aviso"><i class="fas fa-triangle-exclamation"></i><span>${esc(a)}</span></div>`);

    box.innerHTML = `<div class="rec-card acento-naranja">
        <div class="rec-head"><i class="fas fa-truck-fast"></i> Listo para salir</div>
        <div class="rec-linea"><i class="fas fa-box"></i><span><b>${p.en_este_recorrido}</b> entrega(s) en este recorrido</span></div>
        <div class="rec-linea"><i class="fas fa-road"></i><span>${km(p.km_total)} km ida y vuelta (incluye el regreso al local)</span></div>
        ${p.combustible ? `<div class="rec-linea"><i class="fas fa-gas-pump"></i><span>Gasolina aprox.: ${Number(p.combustible.litros).toFixed(1)} L · ${dinero(p.combustible.costo)}</span></div>` : ''}
        ${bloqueRuta(p.tiempo, p.comparativo)}
        ${extra}
        <div class="rec-row">
            <button type="button" id="btnIniciar" class="rec-btn btn-naranja" ${(bloqueado || iniciando) ? 'disabled' : ''} onclick="iniciarRecorrido()"><i class="fas fa-play"></i> Iniciar recorrido</button>
            ${p.google_maps_url ? `<a class="rec-btn btn-azul" target="_blank" rel="noopener" href="${esc(p.google_maps_url)}"><i class="fas fa-map"></i> Mapa del recorrido completo</a>` : ''}
        </div>
    </div>`;
}

/* 2 h 35 min / 45 min */
const fmtMin = n => {
    n = Math.max(0, Math.round(Number(n) || 0));
    const h = Math.floor(n / 60), m = n % 60;
    return h > 0 ? `${h} h ${m} min` : `${m} min`;
};

/* Tiempo estimado del recorrido y comparación contra el orden de captura (se usa en "listo para salir" y "en curso") */
function bloqueRuta(t, c) {
    let h = '';

    if (t) {
        const trafico = t.transito_min > 0
            ? ` (incluye <b>+${fmtMin(t.transito_min)}</b> por tráfico de hora pico${(t.ventanas || []).length ? ': ' + esc(t.ventanas.join(', ')) : ''})`
            : '';
        h += `<div class="rec-linea"><i class="fas fa-clock"></i><span>Tiempo estimado: <b>${fmtMin(t.total_min)}</b> · sales a las ${esc(t.salida)} y regresas al local ~${esc(t.regreso)}</span></div>`;
        h += `<div class="rec-linea"><i class="fas fa-car-side"></i><span>Manejo ${fmtMin(t.traslado_min)}${trafico} + entregas ${fmtMin(t.paradas_min)}</span></div>`;
    }

    if (c && c.original && c.optimizado && c.ahorro) {
        const a = c.ahorro;
        const dif = (v, texto, pct) => v > 0.049 ? `${texto} <small>(${Number(pct).toFixed(1)}%)</small>` : (v < -0.049 ? `+${texto.replace('-', '')}` : 'Sin diferencia');
        h += `<div class="tabla-compara"><table class="rec-compara">
            <thead><tr><th></th><th>Orden de captura</th><th>Ruta optimizada</th><th>Ahorro</th></tr></thead>
            <tbody>
                <tr><th>Distancia</th><td>${km(c.original.km)} km</td><td>${km(c.optimizado.km)} km</td><td class="ahorro">${dif(a.km, km(Math.abs(a.km)) + ' km', a.pct_km)}</td></tr>
                <tr><th>Tiempo de manejo</th><td>${fmtMin(c.original.min)}</td><td>${fmtMin(c.optimizado.min)}</td><td class="ahorro">${dif(a.min, fmtMin(Math.abs(a.min)), a.pct_min)}</td></tr>
                <tr><th>Gasolina</th><td>${Number(c.original.litros).toFixed(1)} L · ${dinero(c.original.costo)}</td><td>${Number(c.optimizado.litros).toFixed(1)} L · ${dinero(c.optimizado.costo)}</td><td class="ahorro">${dif(a.litros, Number(Math.abs(a.litros)).toFixed(1) + ' L · ' + dinero(Math.abs(a.costo)), a.pct_litros)}</td></tr>
            </tbody></table></div>`;
        h += `<p class="nota-compara">${c.nota ? esc(c.nota) + ' ' : ''}Orden de captura = el orden en que se registraron los pedidos. El tiempo usa el tiempo de manejo del mapa y lo alarga en horas pico; no es tráfico en vivo.</p>`;
    }
    return h;
}

/* Tabla de pedidos asignados */
function pintarTabla() {
    const filas = filasTabla();
    const cuerpo = document.getElementById('tablaPedidos');
    document.getElementById('pedidosContador').textContent = filas.length + (filas.length === 1 ? ' pedido' : ' pedidos');

    if (!filas.length) {
        cuerpo.innerHTML = `<tr><td colspan="11"><div class="tabla-vacia"><i class="fas fa-clipboard-check"></i>No tienes pedidos asignados por ahora.</div></td></tr>`;
        return;
    }

    cuerpo.innerHTML = filas.map(f => {
        const porCobrar = Math.max(0, f.total - f.pagado);
        const destino = (f.lat && f.lng)
            ? `https://www.google.com/maps/search/?api=1&query=${f.lat},${f.lng}`
            : `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(f.direccion)}`;

        // Columna Evidencia: miniatura de la foto, resultado de la parada o las acciones si sigue abierta
        let evidencia;
        if (f.cierre === 'entregado') {
            evidencia = f.tieneFoto
                ? `<button type="button" class="miniatura" onclick="verFoto(${f.id})" title="Ver la foto de evidencia"><img src="${API}/evidencia/${f.id}" alt="Evidencia ${folio(f.id)}" loading="lazy"></button>`
                : '<span class="rec-tag ok"><i class="fas fa-check"></i> Registrada</span>';
        } else if (f.cierre === 'fallido') {
            evidencia = `<span class="rec-tag agot"><i class="fas fa-ban"></i> No entregado</span>`
                + (f.nota ? `<small class="nota-fallo">${esc(f.nota)}</small>` : '');
        } else if (f.puedeEvidencia) {
            evidencia = `<div class="acciones-fila">
                <button type="button" class="btn-evidencia" onclick="entregarSalida(${f.id})"><i class="fas fa-camera"></i> Evidencia</button>
                <button type="button" class="btn-fallo" onclick="noPudeEntregar(${f.id})"><i class="fas fa-ban"></i> No pude entregar</button>
            </div>`;
        } else {
            evidencia = '<button type="button" class="btn-evidencia" disabled title="Inicia el recorrido para registrar la evidencia"><i class="fas fa-camera"></i> Evidencia</button>';
        }

        const wa = f.cierre ? null : enlaceWa(f, porCobrar);
        const claseFila = f.cierre === 'entregado' ? 'entregado' : (f.cierre === 'fallido' ? 'fallido' : '');
        const celdaOrden = f.deHoy ? '<span class="rec-tag">Hoy</span>'
            : (f.orden !== null && f.orden !== undefined ? `<span class="orden-num">${esc(f.orden)}</span>` : '<span class="rec-tag">Siguiente salida</span>');

        return `<tr class="${claseFila}">
            <td class="centro">${celdaOrden}</td>
            <td class="folio">${folio(f.id)}${f.prioritario && !f.cierre ? '<br><span class="rec-urgente"><i class="fas fa-bolt"></i> URGENTE</span>' : ''}</td>
            <td class="cliente">${esc(f.cliente)}</td>
            <td>${f.tel ? `<a href="tel:${esc(f.tel)}"><i class="fas fa-phone"></i> ${esc(f.tel)}</a>` : '<span class="rec-tag">Sin teléfono</span>'}${wa ? `<a class="btn-wa" target="_blank" rel="noopener" href="${wa}" title="Avisar por WhatsApp que voy en camino"><i class="fa-brands fa-whatsapp"></i></a>` : ''}</td>
            <td class="dir">${esc(f.direccion)}<small><a target="_blank" rel="noopener" href="${destino}"><i class="fas fa-location-arrow"></i> Ver destino</a></small></td>
            <td class="centro">${f.llegada && !f.cierre ? '~' + esc(f.llegada) : '—'}</td>
            <td class="num">${dinero(f.total)}</td>
            <td class="num ${porCobrar > 0.009 ? 'por-cobrar' : ''}">${dinero(porCobrar)}</td>
            <td><span class="rec-tag">${f.tipo_pago === 'credito' ? 'Crédito' : 'Contado'}</span></td>
            <td><span class="rec-tag ${f.cierre === 'entregado' ? 'ok' : (f.cierre === 'fallido' ? 'agot' : '')}">${esc(f.estado)}</span></td>
            <td class="centro">${evidencia}</td>
        </tr>`;
    }).join('');
}

async function iniciarRecorrido() {
    if (iniciando) return;
    if (!confirm('¿Iniciar el recorrido? Tus pedidos pasarán a "En tránsito".')) return;
    iniciando = true;
    const btn = document.getElementById('btnIniciar');
    if (btn) btn.disabled = true;
    try {
        const r = await post(API + '/recorrido/iniciar', {});
        aviso(r.success ? 'Recorrido iniciado' : (r.message || 'No se pudo iniciar el recorrido'));
    } catch (e) {
        aviso('No se pudo iniciar el recorrido. Revisa tu conexión.');
    } finally {
        iniciando = false;
        cargar(true);
    }
}

/* ============ EVIDENCIA DE ENTREGA ============ */
let entrega = { id: null, saldo: 0, foto: null, firmaDibujada: false, enviando: false };

function entregarSalida(id) {
    const p = (salidaActual?.paradas || []).find(x => Number(x.id_pedido) === Number(id));
    if (!p) { aviso('Inicia el recorrido para registrar la evidencia.'); return; }

    const total  = Number(p.pedido_total || 0), pagado = Number(p.pedido_pagado || 0);
    const saldo  = Math.max(0, Math.round((total - pagado) * 100) / 100);
    entrega = { id, saldo, foto: null, firmaDibujada: false, enviando: false };

    document.getElementById('entCliente').textContent = folio(id) + ' · ' + ([p.cliente, p.apellido_paterno].filter(Boolean).join(' ') || 'Cliente');
    document.getElementById('entDinero').innerHTML =
        `<i class="fas fa-receipt"></i><span>Total ${dinero(total)} · pagado ${dinero(pagado)} · <b>por cobrar ${dinero(saldo)}</b></span>` +
        (p.tipo_pago === 'credito' ? ' <span class="rec-tag">Crédito</span>' : '');

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

function cerrarEntrega() { document.getElementById('sheetEntrega').style.display = 'none'; }

/* ============ NO PUDE ENTREGAR ============ */
let fallo = { id: null, motivo: null, enviando: false };

function noPudeEntregar(id) {
    const p = (salidaActual?.paradas || []).find(x => Number(x.id_pedido) === Number(id));
    if (!p) { aviso('Inicia el recorrido para poder avisar que no se entregó.'); return; }

    fallo = { id, motivo: null, enviando: false };
    document.getElementById('fCliente').textContent = folio(id) + ' · ' + ([p.cliente, p.apellido_paterno].filter(Boolean).join(' ') || 'Cliente');
    document.getElementById('fNota').value = '';
    document.getElementById('fBtn').disabled = false;
    document.querySelectorAll('#sheetFallo .motivo').forEach(b => b.classList.remove('sel'));
    document.getElementById('sheetFallo').style.display = 'flex';
}

function cerrarFallo() { document.getElementById('sheetFallo').style.display = 'none'; }

document.querySelectorAll('#sheetFallo .motivo').forEach(b => b.addEventListener('click', () => {
    document.querySelectorAll('#sheetFallo .motivo').forEach(x => x.classList.remove('sel'));
    b.classList.add('sel');
    fallo.motivo = b.dataset.motivo;
}));

async function confirmarFallo() {
    if (fallo.enviando) return;
    const nota = document.getElementById('fNota').value.trim();

    if (!fallo.motivo) { aviso('Elige el motivo por el que no se pudo entregar.'); return; }
    if (fallo.motivo === 'otro' && nota.length < 5) { aviso('Explica en la nota por qué no se pudo entregar.'); return; }

    fallo.enviando = true;
    document.getElementById('fBtn').disabled = true;

    // La ubicación viaja con el aviso para que el administrador vea dónde está el repartidor
    const ubicacion = await new Promise(res => {
        if (!navigator.geolocation) return res(null);
        navigator.geolocation.getCurrentPosition(p => res(p.coords), () => res(null), {timeout: 5000, maximumAge: 60000});
    });

    try {
        const r = await post(API + '/salida/pedido/fallar/' + fallo.id, {
            motivo: fallo.motivo, nota,
            lat: ubicacion ? ubicacion.latitude : '', lng: ubicacion ? ubicacion.longitude : ''
        });
        aviso(r.success ? 'Listo. El administrador ya recibió el aviso.' : (r.message || 'No se pudo registrar'));
        if (r.success) { cerrarFallo(); cargar(true); }
        else { fallo.enviando = false; document.getElementById('fBtn').disabled = false; }
    } catch (e) {
        aviso('No se pudo avisar. Revisa tu conexión.');
        fallo.enviando = false; document.getElementById('fBtn').disabled = false;
    }
}

/* ============ VISOR DE LA FOTO DE EVIDENCIA ============ */
function verFoto(id) {
    document.getElementById('visorImg').src = API + '/evidencia/' + id;
    document.getElementById('visorFoto').style.display = 'flex';
}
function cerrarFoto() {
    document.getElementById('visorFoto').style.display = 'none';
    document.getElementById('visorImg').removeAttribute('src');
}

/* La foto se reduce antes de subirla (las del celular pesan varios MB) */
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
function prepararFirma() {
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
function borrarFirma() { prepararFirma(); }

async function confirmarEntrega() {
    if (entrega.enviando) return;
    const nota  = document.getElementById('entNota').value.trim();
    const sinEv = document.getElementById('entSinEvidencia').checked;

    if (!entrega.foto && !entrega.firmaDibujada && !(sinEv && nota.length >= 5)) {
        aviso('Toma la foto de prueba de la entrega. Si no se puede, marca "Sin evidencia" y explica por qué.');
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
        aviso(r.success ? 'Evidencia registrada. Pedido entregado.' : (r.message || 'No se pudo registrar'));
        if (r.success) { cerrarEntrega(); cargar(true); }
        else { entrega.enviando = false; document.getElementById('entBtn').disabled = false; }
    } catch (e) {
        aviso('No se pudo registrar la evidencia. Revisa tu conexión.');
        entrega.enviando = false; document.getElementById('entBtn').disabled = false;
    }
}

/* El contador de la card se mantiene al día aunque la sección esté cerrada */
cargar(true);
setInterval(() => cargar(false), 8000);
</script>
</body>
</html>
