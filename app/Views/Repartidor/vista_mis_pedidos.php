<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVER · Mis Rutas y Pedidos</title>

    <?= view('Repartidor/partials/tema') ?>

    <link rel="stylesheet" href="<?= base_url('css/estiloparapedidos.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-page);
            color: var(--texto);
            margin: 0;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* Barra superior */
        .barra-superior {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.6rem 2rem; background: var(--bg-topbar);
            transition: background 0.3s ease;
        }
        .barra-superior img { height: 60px; }

        .user-actions { display: flex; gap: 10px; align-items: center; }
        .btn-user {
            color: white; text-decoration: none;
            padding: 0.5rem 1.2rem; background: rgba(255,255,255,0.15);
            border-radius: 50px; font-weight: 500; font-size: 0.85rem;
            display: inline-flex; align-items: center; gap: 8px;
            transition: background 0.2s ease;
            border: 1px solid rgba(255,255,255,0.15);
            cursor: pointer; font-family: inherit;
        }
        .btn-user:hover { background: rgba(255,255,255,0.25); }

        /* Nav */
        .menu-navegacion {
            background: var(--bg-nav);
            padding: 0.8rem;
            display: flex; justify-content: center; gap: 1rem;
            border-bottom: 1px solid var(--borde);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: background 0.3s ease, border-color 0.3s ease;
            flex-wrap: wrap;
        }
        .nav-link {
            text-decoration: none; color: var(--texto);
            font-weight: 600; padding: 0.6rem 1.2rem;
            border-radius: 40px;
            transition: color 0.2s ease, background 0.2s ease;
            font-size: 0.9rem;
        }
        .nav-link:hover { background: var(--bg-suave); }
        .nav-link.activo {
            color: var(--naranja);
            background: rgba(241,107,26,0.1);
        }

        /* Contenedor */
        .pedido-header-card {
            margin: 2rem auto; max-width: 1200px; padding: 2rem;
            background: var(--bg-card); border-radius: 12px;
            box-shadow: var(--sombra);
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }
        .pedido-header-card h2 {
            color: var(--verde);
            margin-bottom: 1.5rem;
            transition: color 0.3s ease;
        }
        html[data-tema="oscuro"] .pedido-header-card h2 { color: #4ade80; }

        /* Tabla */
        .tabla-container { overflow-x: auto; }
        .tabla-pedidos-exitentes {
            width: 100%; border-collapse: collapse;
            color: var(--texto);
        }
        .tabla-pedidos-exitentes th {
            background: var(--verde); color: white; padding: 12px; text-align: left;
        }
        html[data-tema="oscuro"] .tabla-pedidos-exitentes th { background: #153a1e; }

        .tabla-pedidos-exitentes tr { border-bottom: 1px solid var(--borde); }
        .tabla-pedidos-exitentes td { padding: 12px; }

        .direccion-text {
            font-size: 0.85rem; color: var(--texto-suave);
            background: var(--bg-suave); padding: 6px; border-radius: 6px;
            margin-top: 4px; transition: background 0.3s ease, color 0.3s ease;
        }

        .chip {
            display: inline-flex; align-items: center; gap: 6px;
            margin: 6px 6px 0 0; padding: 6px 12px; border-radius: 50px;
            background: var(--bg-suave); color: var(--verde);
            font-size: 0.8rem; font-weight: 600; text-decoration: none;
            border: 1px solid var(--borde);
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        html[data-tema="oscuro"] .chip { color: #4ade80; }
        .chip:hover { background: var(--borde); }
        .chip:active { transform: scale(0.97); }

        .badge-estado {
            display: inline-block; padding: 4px 10px; border-radius: 6px;
            font-weight: 700; font-size: 0.8rem;
        }
        .badge-estado.listo  { background: var(--poco-bg); color: var(--poco-tx); }
        .badge-estado.camino { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .badge-estado.otro   { background: var(--bg-suave); color: var(--texto-suave); }

        .rep-btn {
            border: 0; border-radius: 10px; padding: 12px 14px;
            font: 700 0.95rem 'Inter', sans-serif; color: #fff;
            cursor: pointer; transition: 0.2s;
            font-family: inherit;
        }
        .rep-btn:hover { transform: scale(1.03); }
        .rep-btn.salir    { background: #f16b1a; }
        .rep-btn.salir:hover { background: #d95c12; }
        .rep-btn.entregar { background: #1d4a27; }
        .rep-btn.entregar:hover { background: #153a1e; }
        .rep-btn.ayuda {
            background: var(--bg-card); color: #dc2626;
            border: 1px solid #fca5a5;
            font-weight: 600; padding: 8px 12px; font-size: 0.85rem;
        }
        .rep-btn.ayuda:hover { background: #fef2f2; }
        html[data-tema="oscuro"] .rep-btn.ayuda:hover { background: rgba(220,38,38,0.15); }

        .acciones-col { display: flex; flex-direction: column; gap: 8px; min-width: 190px; }

        .sync-info {
            font-size: 0.75rem; font-weight: 500;
            color: var(--texto-tenue); margin-left: 10px;
        }

        /* Pagos */
        .pago-pagado  { background: var(--ok-bg);   color: var(--ok-tx); }
        .pago-credito { background: var(--poco-bg); color: var(--poco-tx); }
        .pago-cobrar  { background: var(--agot-bg); color: var(--agot-tx); }
        .pago-tag {
            padding: 4px 8px; border-radius: 4px; font-weight: bold;
            display: inline-block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .barra-superior { padding: 0.6rem 1rem; }
            .barra-superior img { height: 48px; }
            .btn-user { padding: 0.45rem 0.9rem; font-size: 0.78rem; }
            .menu-navegacion { padding: 0.6rem; gap: 0.4rem; }
            .nav-link { padding: 0.5rem 0.9rem; font-size: 0.82rem; }
            .pedido-header-card { margin: 1rem; padding: 1rem; }
            .acciones-col { min-width: 0; }
            .tabla-pedidos-exitentes th, .tabla-pedidos-exitentes td { padding: 10px 8px; font-size: 0.85rem; }
        }
    </style>
</head>
<body>

<div class="barra-superior">
    <div class="logo-area">
        <img src="<?= base_url('img/LOGO1.png') ?>" alt="FRUVER">
    </div>
    <div class="user-actions">
        <button type="button" class="btn-user" id="btnTema" aria-label="Cambiar tema">
            <i class="fas fa-moon"></i> <span class="tema-texto">Oscuro</span>
        </button>
        <a href="<?= base_url('repartidor/panel') ?>" class="btn-user">
            <i class="fas fa-arrow-left"></i> Volver al Panel
        </a>
    </div>
</div>

<nav class="menu-navegacion">
    <a href="<?= base_url('repartidor/panel') ?>" class="nav-link"><i class="fas fa-home"></i> Inicio</a>
    <a href="<?= base_url('repartidor/pedidos') ?>" class="nav-link activo"><i class="fas fa-truck"></i> Mis Pedidos</a>
    <a href="<?= base_url('repartidor/inventario') ?>" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
</nav>

<div class="pedido-header-card">
    <h2>
        <i class="fas fa-route"></i> Mi Ruta de Entregas
        <span id="sync" class="sync-info"></span>
    </h2>

    <div class="tabla-container">
        <table class="tabla-pedidos-exitentes">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Cliente y Dirección</th>
                    <th>Total a Cobrar</th>
                    <th>Estado de Pago</th>
                    <th>Estado y Acción</th>
                </tr>
            </thead>
            <tbody id="tbodyPedidos">
                <tr><td colspan="5" style="text-align:center; padding:2rem; color:var(--texto-suave);">Cargando pedidos...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?= view('Repartidor/partials/ayuda_gps') ?>

<script>
/* ============ TEMA ============ */
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
    aplicar(html.getAttribute('data-tema') || 'claro');

    btn.addEventListener('click', () => {
        const actual = html.getAttribute('data-tema');
        const nuevo  = actual === 'oscuro' ? 'claro' : 'oscuro';
        aplicar(nuevo);
        localStorage.setItem('fruver-tema', nuevo);
    });
})();

/* ============ PEDIDOS ============ */
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const dinero = n => Number(n).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});

function badgePago(porCobrar, tipoPago) {
    if (porCobrar <= 0) {
        return '<span class="pago-tag pago-pagado"><i class="fas fa-check"></i> Pagado</span>';
    }
    if (tipoPago === 'credito') {
        return '<span class="pago-tag pago-credito"><i class="fas fa-clock"></i> Crédito</span>';
    }
    return '<span class="pago-tag pago-cobrar"><i class="fas fa-hand-holding-dollar"></i> Cobrar al entregar</span>';
}

function filaPedido(p) {
    const dir = p.calle ? `Calle ${p.calle} #${p.numero}, ${p.colonia}` : 'Sin dirección registrada';
    const dirMapa = p.calle ? `${p.calle} ${p.numero}, ${p.colonia}, ${p.municipio || ''} ${p.estado_dir || ''}` : '';
    const porCobrar = Math.max(0, Number(p.total) - Number(p.monto_pagado));
    const enCamino = p.estado_actual === 'Pedido en tránsito';

    let badge;
    if (enCamino) badge = '<span class="badge-estado camino"><i class="fas fa-truck"></i> En camino</span>';
    else if (p.estado_actual === 'Pedido confirmado') badge = '<span class="badge-estado listo"><i class="fas fa-box"></i> Listo para salir</span>';
    else badge = `<span class="badge-estado otro">${esc(p.estado_actual)}</span>`;

    const boton = enCamino
        ? `<button class="rep-btn entregar" onclick="cambiar(${p.id}, 'Venta confirmada')"><i class="fas fa-check-double"></i> Marcar entregado</button>`
        : `<button class="rep-btn salir" onclick="cambiar(${p.id}, 'Pedido en tránsito')"><i class="fas fa-truck"></i> Salir a entregar</button>`;

    return `
    <tr>
        <td style="font-weight:bold; font-size:1.1rem;">#${String(p.id).padStart(5, '0')}</td>
        <td>
            <strong style="color:var(--verde);"><i class="fas fa-user"></i> ${esc(p.cliente)}</strong>
            <div class="direccion-text"><i class="fas fa-map-marker-alt" style="color:#f16b1a;"></i> ${esc(dir)}</div>
            ${p.tel ? `<a class="chip" href="tel:${esc(p.tel)}"><i class="fas fa-phone"></i> Llamar</a>` : ''}
            ${dirMapa ? `<a class="chip" target="_blank" href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(dirMapa)}"><i class="fas fa-map"></i> Ver mapa</a>` : ''}
        </td>
        <td style="font-weight:bold; color:#f16b1a; font-size:1.1rem;">$${dinero(porCobrar)}</td>
        <td>${badgePago(porCobrar, p.tipo_pago)}</td>
        <td>
            <div class="acciones-col">
                <div>${badge}</div>
                ${boton}
                <button class="rep-btn ayuda" onclick="abrirAyuda(${p.id})"><i class="fas fa-life-ring"></i> No pude entregar / Ayuda</button>
            </div>
        </td>
    </tr>`;
}

async function cargar() {
    try {
        const r = await fetch(REP_API + '/pedidos');
        if (r.redirected) { location.reload(); return; }
        const d = await r.json();
        const lista = d.data || [];
        document.getElementById('tbodyPedidos').innerHTML = lista.length
            ? lista.map(filaPedido).join('')
            : '<tr><td colspan="5" style="text-align:center; padding:2rem; color:var(--texto-suave);">No tienes pedidos pendientes en este momento.</td></tr>';
        document.getElementById('sync').textContent = 'Actualizado ' + new Date().toLocaleTimeString('es-MX');
    } catch (e) {
        document.getElementById('sync').textContent = 'Sin conexión, reintentando...';
    }
}

async function cambiar(id, estado) {
    if (estado === 'Venta confirmada' && !confirm('¿Confirmas que ya entregaste el pedido #' + id + '?')) return;
    try {
        const r = await repPost(REP_API + '/estado', {id: id, estado: estado});
        repAviso(r.success ? (estado === 'Venta confirmada' ? 'Pedido entregado' : 'Pedido en camino') : 'No se pudo actualizar');
    } catch (e) {
        repAviso('Error de conexión');
    }
    cargar();
}

cargar();
setInterval(cargar, 8000);
</script>
</body>
</html>