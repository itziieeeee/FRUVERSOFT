<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FRUVER · Consulta de Inventario</title>

<?= view('Repartidor/partials/tema') ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Inter', sans-serif;
        background: var(--bg-page); color: var(--texto);
        margin: 0; padding: 0;
        transition: background 0.3s ease, color 0.3s ease;
    }

    /* Barra superior */
    .barra-superior {
        display: flex; align-items: center; justify-content: space-between;
        padding: 0.6rem 2rem; background: var(--bg-topbar);
        transition: background 0.3s ease;
    }
    .barra-superior img { height: 60px; }
    .btn-user {
        color: white; text-decoration: none; padding: 0.5rem 1.2rem;
        background: rgba(255,255,255,0.15); border-radius: 50px;
        font-size: 0.85rem; font-weight: 500;
        display: inline-flex; align-items: center; gap: 8px;
        transition: background 0.2s ease;
        border: 1px solid rgba(255,255,255,0.15);
        cursor: pointer; font-family: inherit;
    }
    .btn-user:hover { background: rgba(255,255,255,0.25); }

    /* Nav */
    .menu-navegacion {
        background: var(--bg-nav); padding: 0.8rem;
        display: flex; justify-content: center; gap: 1rem;
        border-bottom: 1px solid var(--borde);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: background 0.3s ease, border-color 0.3s ease;
        flex-wrap: wrap;
    }
    .nav-link {
        padding: 0.6rem 1.2rem; color: var(--texto);
        text-decoration: none; font-weight: 600;
        border-radius: 40px; font-size: 0.9rem;
        transition: color 0.2s ease, background 0.2s ease;
    }
    .nav-link:hover { background: var(--bg-suave); }
    .nav-link.activo { color: var(--naranja); background: rgba(241,107,26,0.1); }

    /* Contenido */
    .contenido-principal {
        margin: 2rem; background: var(--bg-card);
        border-radius: 1rem; padding: 1.5rem;
        box-shadow: var(--sombra);
        transition: background 0.3s ease, box-shadow 0.3s ease;
    }

    .encabezado {
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap; gap: 8px; margin-bottom: 1rem;
    }
    .encabezado h2 {
        color: var(--verde); margin: 0;
        transition: color 0.3s ease;
    }
    html[data-tema="oscuro"] .encabezado h2 { color: #4ade80; }

    .btn-refrescar {
        border: 1px solid var(--borde); background: var(--bg-card);
        color: var(--verde); border-radius: 50px;
        padding: 8px 16px; font: 600 0.85rem 'Inter', sans-serif;
        cursor: pointer; transition: background 0.2s ease;
    }
    html[data-tema="oscuro"] .btn-refrescar { color: #4ade80; }
    .btn-refrescar:hover { background: var(--bg-suave); }

    .buscador-rapido {
        width: 100%; padding: 12px;
        border: 2px solid var(--borde); border-radius: 10px;
        font-size: 1rem; margin-bottom: 1rem; outline: none;
        box-sizing: border-box;
        background: var(--bg-card); color: var(--texto);
        font-family: inherit;
        transition: border-color 0.2s ease, background 0.3s ease;
    }
    .buscador-rapido:focus { border-color: var(--naranja); }

    /* Grid de productos */
    .productos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem; margin-top: 1.5rem;
    }

    .producto-card {
        border: 1px solid var(--borde); border-radius: 1rem;
        padding: 1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        background: var(--bg-card);
        transition: background 0.3s ease, border-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .producto-card:hover { transform: translateY(-2px); box-shadow: var(--sombra-hover); }

    .producto-cabecera {
        display: flex; justify-content: space-between; align-items: flex-start;
        gap: 8px; margin-bottom: 0.5rem;
    }
    .producto-nombre {
        font-size: 1.1rem; font-weight: 700; color: var(--verde);
        transition: color 0.3s ease;
    }
    html[data-tema="oscuro"] .producto-nombre { color: #4ade80; }

    .producto-descripcion {
        font-size: 0.8rem; color: var(--texto-suave);
        background: var(--bg-suave); padding: 8px;
        border-radius: 8px; margin-bottom: 1rem;
        transition: background 0.3s ease, color 0.3s ease;
    }

    .inventario-panel {
        background: var(--bg-suave); border-radius: 0.8rem;
        padding: 0.8rem; border-left: 3px solid var(--naranja);
        transition: background 0.3s ease;
    }
    .inventario-item {
        display: flex; justify-content: space-between; align-items: center;
        font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;
    }
    .disponible { font-weight: 700; font-size: 1.05rem; }

    /* Semáforo */
    .nivel { padding: 3px 10px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; white-space: nowrap; }
    .nivel.ok      { background: var(--ok-bg);   color: var(--ok-tx); }
    .nivel.poco    { background: var(--poco-bg); color: var(--poco-tx); }
    .nivel.agotado { background: var(--agot-bg); color: var(--agot-tx); }

    .nivel-agotado .disponible { color: var(--agot-tx); }
    .nivel-poco .disponible    { color: var(--poco-tx); }
    .nivel-ok .disponible      { color: var(--ok-tx); }

    /* Consulta rápida */
    .consulta { margin-top: 12px; }
    .consulta label {
        display: block; font-size: 0.78rem; font-weight: 600;
        color: var(--texto-suave); margin-bottom: 6px;
        transition: color 0.3s ease;
    }
    .cant-pedida {
        width: 100%; padding: 10px 12px;
        border: 2px solid var(--borde); border-radius: 10px;
        font: 600 1rem 'Inter', sans-serif;
        box-sizing: border-box; outline: none;
        background: var(--bg-card); color: var(--texto);
        transition: border-color 0.2s ease, background 0.3s ease;
    }
    .cant-pedida:focus { border-color: var(--naranja); }

    .resultado {
        margin-top: 8px; padding: 8px 12px; border-radius: 8px;
        font-size: 0.85rem; font-weight: 600; display: none;
    }
    .resultado.si { display: block; background: var(--ok-bg);   color: var(--ok-tx); }
    .resultado.no { display: block; background: var(--agot-bg); color: var(--agot-tx); }

    .sin-resultados {
        display: none; text-align: center; color: var(--texto-suave);
        padding: 2rem 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .barra-superior { padding: 0.6rem 1rem; }
        .barra-superior img { height: 48px; }
        .btn-user { padding: 0.45rem 0.9rem; font-size: 0.78rem; }
        .menu-navegacion { padding: 0.6rem; gap: 0.4rem; }
        .nav-link { padding: 0.5rem 0.9rem; font-size: 0.82rem; }
        .contenido-principal { margin: 1rem; padding: 1rem; }
        .productos-grid { grid-template-columns: 1fr; gap: 1rem; }
    }
</style>
</head>
<body>

<?php
    $UMBRAL_POCO = 5;
?>

<header class="barra-superior">
    <div class="logo-area">
        <img src="<?= base_url('img/LOGO1.png') ?>" alt="FRUVER">
    </div>
    <div class="user-actions" style="display:flex; gap:10px;">
        <button type="button" class="btn-user" id="btnTema" aria-label="Cambiar tema">
            <i class="fas fa-moon"></i> <span class="tema-texto">Oscuro</span>
        </button>
        <a href="<?= base_url('repartidor/panel') ?>" class="btn-user"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
    </div>
</header>

<nav class="menu-navegacion">
    <a href="<?= base_url('repartidor/panel') ?>" class="nav-link"><i class="fas fa-home"></i> Inicio</a>
    <a href="<?= base_url('repartidor/pedidos') ?>" class="nav-link"><i class="fas fa-truck"></i> Mis Pedidos</a>
    <a href="<?= base_url('repartidor/inventario') ?>" class="nav-link activo"><i class="fas fa-boxes"></i> Inventario</a>
</nav>

<div class="contenido-principal">
    <div class="encabezado">
        <h2><i class="fas fa-boxes"></i> Consulta de Inventario</h2>
        <button class="btn-refrescar" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i> Actualizar <span id="hora" style="font-weight:400; color:var(--texto-tenue);"></span>
        </button>
    </div>

    <input type="text" id="buscador" class="buscador-rapido" placeholder="Busca un producto (ej. cebolla)...">

    <div class="productos-grid" id="gridProductos">
        <?php foreach($productos as $p):
            $existencias_totales    = $p['existencias_totales']    ?? 0;
            $existencias_bloqueadas = $p['existencias_bloqueadas'] ?? 0;
            $existencias_venta      = max(0, $existencias_totales - $existencias_bloqueadas);
            $unidad                 = $p['unidad_venta'] ?? 'unidad';

            if ($existencias_venta <= 0)                 { $nivel = 'agotado'; $textoNivel = 'Agotado'; }
            elseif ($existencias_venta <= $UMBRAL_POCO)  { $nivel = 'poco';    $textoNivel = 'Poco stock'; }
            else                                         { $nivel = 'ok';      $textoNivel = 'Disponible'; }
        ?>
        <div class="producto-card nivel-<?= $nivel ?>"
             data-busqueda="<?= htmlspecialchars(mb_strtolower(($p['nombre'] ?? '') . ' ' . ($p['codigo'] ?? ''))) ?>">
            <div class="producto-cabecera">
                <div class="producto-nombre"><?= htmlspecialchars($p['nombre']) ?></div>
                <span class="nivel <?= $nivel ?>"><?= $textoNivel ?></span>
            </div>
            <div class="producto-descripcion"><?= htmlspecialchars($p['descripcion'] ?? 'Sin descripción') ?></div>
            <div class="inventario-panel">
                <div class="inventario-item">
                    <span><i class="fas fa-check-circle" style="color: var(--verde);"></i> Disponibles para venta:</span>
                    <span class="disponible"><?= (float) $existencias_venta ?> <?= htmlspecialchars($unidad) ?></span>
                </div>
            </div>

            <div class="consulta">
                <label>¿Cuánto pide el cliente? (en <?= htmlspecialchars($unidad) ?>)</label>
                <input type="number" min="0" step="any" inputmode="decimal" class="cant-pedida"
                       data-disp="<?= (float) $existencias_venta ?>"
                       data-unidad="<?= htmlspecialchars($unidad) ?>"
                       placeholder="Ej. 20">
                <div class="resultado"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="sin-resultados" id="sinResultados"><i class="fas fa-search"></i> No hay productos con ese nombre.</div>
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

/* ============ RESTO ============ */
document.getElementById('hora').textContent = '· ' + new Date().toLocaleTimeString('es-MX', {hour: '2-digit', minute: '2-digit'});

document.getElementById('buscador').addEventListener('input', function () {
    const term = this.value.toLowerCase().trim();
    let visibles = 0;
    document.querySelectorAll('.producto-card').forEach(card => {
        const coincide = card.dataset.busqueda.includes(term);
        card.style.display = coincide ? 'block' : 'none';
        if (coincide) visibles++;
    });
    document.getElementById('sinResultados').style.display = visibles ? 'none' : 'block';
});

const num = n => (Math.round(n * 100) / 100).toString();
document.getElementById('gridProductos').addEventListener('input', function (e) {
    if (!e.target.classList.contains('cant-pedida')) return;
    const input = e.target;
    const caja  = input.parentElement.querySelector('.resultado');
    const pide  = parseFloat(input.value);
    const disp  = parseFloat(input.dataset.disp);
    const un    = input.dataset.unidad;

    caja.className = 'resultado';
    if (!pide || pide <= 0) return;

    if (pide <= disp) {
        caja.className = 'resultado si';
        caja.innerHTML = '<i class="fas fa-check-circle"></i> Sí alcanza. Quedarían ' + num(disp - pide) + ' ' + un + '.';
    } else {
        caja.className = 'resultado no';
        caja.innerHTML = disp > 0
            ? '<i class="fas fa-triangle-exclamation"></i> No alcanza: solo hay ' + num(disp) + ' ' + un + ' (faltan ' + num(pide - disp) + ').'
            : '<i class="fas fa-ban"></i> Sin stock de este producto.';
    }
});
</script>
</body>
</html>