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

        .module-img { width: 100%; max-width: 68px; object-fit: contain; }

        /* Badge contador de pedidos */
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
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
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
                <span>Repartidor: <?= session()->get('nombre') ?></span>
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
        <h1>Bienvenido de vuelta, <?= session()->get('nombre') ?></h1>
        <p>Selecciona una opción para comenzar tu ruta.</p>
    </div>

    <div class="modules-grid">
        <a href="<?= base_url('repartidor/pedidos') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/PEDIDOS.png') ?>" alt="Mis Pedidos" class="module-img">
                <span class="pedidos-badge oculto" id="pedidosBadge">0</span>
            </div>
            <span class="module-title">Mis Pedidos Asignados</span>
            <span class="module-sub">Consulta y actualiza tus entregas</span>
            <span class="module-arrow"><i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="<?= base_url('repartidor/inventario') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/INVENTARIO1.png') ?>" alt="Inventario" class="module-img">
            </div>
            <span class="module-title">Consultar Inventario</span>
            <span class="module-sub">Verifica disponibilidad de productos</span>
            <span class="module-arrow"><i class="fas fa-arrow-right"></i></span>
        </a>
    </div>
</div>

<?= view('Repartidor/partials/ayuda_gps') ?>

<script>
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

/* ============ CONTADOR DE PEDIDOS (badge) ============ */
(async function () {
    const badge = document.getElementById('pedidosBadge');
    try {
        const r = await fetch("<?= base_url('repartidor/api/pedidos') ?>", {credentials: 'same-origin'});
        if (r.redirected) return;
        const d = await r.json();
        const n = (d.data || []).length;
        if (n > 0) {
            badge.textContent = n > 99 ? '99+' : n;
            badge.classList.remove('oculto');
        }
    } catch (e) {
        /* silencioso */
    }
})();
</script>
</body>
</html>