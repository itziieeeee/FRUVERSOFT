<?php
/**
 * Barra de navegación del administrador con submódulos desplegables.
 * Se usa en todas las pantallas del administrador: <?= view('partials/nav_admin') ?>
 *
 * Al hacer clic en un módulo con submódulos se abre su menú (el primer renglón lleva al módulo).
 * Sin JavaScript, el clic abre el módulo directamente.
 * El módulo activo se calcula con la ruta actual, así también se marca al estar en un submódulo.
 */
$ruta = trim((string) uri_string(), '/');

$modulos = [
    'ventas' => [
        'titulo' => 'Ventas', 'icono' => 'fas fa-tag', 'url' => 'pantalla_ventas',
        'rutas'  => ['pantalla_ventas', 'corte', 'reportes', 'costos'],
        'sub'    => [
            ['url' => 'pantalla_ventas', 'icono' => 'fa-cart-plus',     'titulo' => 'Registrar venta'],
            ['url' => 'corte',           'icono' => 'fa-cash-register', 'titulo' => 'Corte de caja', 'id' => 'snavCorte'],
            ['url' => 'reportes',        'icono' => 'fa-chart-column',  'titulo' => 'Reporte de ventas'],
            ['url' => 'costos',          'icono' => 'fa-coins',         'titulo' => 'Costos y ganancias'],
        ],
    ],
    'pedidos' => [
        'titulo' => 'Pedidos', 'icono' => 'fas fa-truck', 'url' => 'pantalla_pedidos',
        'rutas'  => ['pantalla_pedidos', 'seguimiento', 'salidas', 'devoluciones', 'cobranza'],
        'sub'    => [
            ['url' => 'pantalla_pedidos', 'icono' => 'fa-boxes-stacked',       'titulo' => 'Seguimiento de pedidos'],
            ['url' => 'seguimiento',      'icono' => 'fa-map-location-dot',    'titulo' => 'Seguimiento de repartos'],
            ['url' => 'salidas',          'icono' => 'fa-route',               'titulo' => 'Monitor de salidas'],
            ['url' => 'devoluciones',     'icono' => 'fa-rotate-left',         'titulo' => 'Devoluciones'],
            ['url' => 'cobranza',         'icono' => 'fa-hand-holding-dollar', 'titulo' => 'Cobranza de créditos'],
        ],
    ],
    'inventario' => [
        'titulo' => 'Inventario', 'icono' => 'fas fa-boxes', 'url' => 'pantalla_inventario',
        'rutas'  => ['pantalla_inventario', 'inventario', 'existencias', 'merma', 'mermas', 'alertas', 'pantalla_alertas', 'auditoria'],
        'sub'    => [
            ['url' => 'pantalla_inventario', 'icono' => 'fa-boxes',            'titulo' => 'Inventario'],
            ['url' => 'alertas',             'icono' => 'fa-chart-line',       'titulo' => 'Estado de inventario'],
            ['url' => 'auditoria',           'icono' => 'fa-clock-rotate-left', 'titulo' => 'Historial de cambios'],
            ['icono' => 'fa-file-export', 'titulo' => 'Exportar inventario', 'opciones' => [
                ['url' => 'exportar/inventario/pdf',  'icono' => 'fa-file-pdf',   'titulo' => 'PDF',   'nueva' => true],
                ['url' => 'exportar/inventario/xlsx', 'icono' => 'fa-file-excel', 'titulo' => 'Excel'],
            ]],
        ],
    ],
    'clientes' => [
        'titulo' => 'Clientes', 'icono' => 'fa-solid fa-users', 'url' => 'pantalla_clientes',
        'rutas'  => ['pantalla_clientes'],
    ],
    'repartidores' => [
        'titulo' => 'Repartidores', 'icono' => 'fa-solid fa-dolly', 'url' => 'pantalla_repartidores',
        'rutas'  => ['pantalla_repartidores'],
    ],
    'productos' => [
        'titulo' => 'Productos', 'icono' => 'fa-solid fa-apple-whole', 'url' => 'pantalla_productos',
        'rutas'  => ['pantalla_productos', 'producto', 'productos', 'alta_producto'],
    ],
];

// ¿La ruta actual es esta o está debajo de ella? (corte, corte/guardar, existencias/editar/3...)
$coincide = static function (string $ruta, string $base): bool {
    return $ruta === $base || str_starts_with($ruta, $base . '/');
};
?>
<style>
    .menu-navegacion.snav { overflow: visible !important; position: relative; z-index: 900; }
    .snav .nav-links {
        display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 4px;
        /* Sin esto, en celular la barra no se encoge y hace la página más ancha que la pantalla */
        min-width: 0; max-width: 100%; flex: 1 1 auto;
    }
    @media (max-width: 640px) {
        .menu-navegacion.snav { padding-left: .5rem !important; padding-right: .5rem !important; }
        .snav .nav-link { padding: .5rem .7rem !important; font-size: .85rem; }
    }
    .snav-item { position: relative; }
    .snav .snav-flecha { font-size: .65rem; margin-left: 2px; opacity: .7; transition: transform .15s ease; }
    .snav-item.abierto .snav-flecha { transform: rotate(180deg); }

    .snav-menu {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        min-width: 250px;
        padding: 8px;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 14px;
        box-shadow: 0 14px 32px rgba(0, 0, 0, .14);
        z-index: 1000;
        text-align: left;
        font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
    }
    .snav-item.abierto .snav-menu { display: block; animation: snavAbrir .14s ease; }
    /* Puente invisible entre el botón y el menú: el mouse no "se cae" en el hueco y el menú no se cierra */
    .snav-menu::before { content: ''; position: absolute; left: 0; right: 0; top: -12px; height: 12px; }

    /* Con mouse: el menú se abre solo al pasar el cursor y el clic en el módulo abre su pantalla */
    @media (hover: hover) and (pointer: fine) {
        .snav-item:hover .snav-menu,
        .snav-item:focus-within .snav-menu { display: block; animation: snavAbrir .14s ease; }
        .snav-item:hover .snav-flecha { transform: rotate(180deg); }
        .snav-grupo:hover .snav-opciones { display: block; }
        .snav-grupo:hover .snav-mas { transform: rotate(90deg); }
    }
    @keyframes snavAbrir { from { opacity: 0; transform: translate(-50%, -4px); } to { opacity: 1; transform: translate(-50%, 0); } }

    .snav-op {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 9px 12px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #1d4a27 !important;
        font: 600 .88rem 'Inter', 'Segoe UI', Arial, sans-serif;
        text-decoration: none !important;
        text-align: left;
        cursor: pointer;
        box-sizing: border-box;
    }
    .snav-op:hover, .snav-op:focus-visible { background: #e8f3e6; outline: none; }
    .snav-op.actual { background: #fff1e6; color: #c2410c !important; }
    .snav-op > i:first-child { width: 18px; text-align: center; color: #f16b1a; }
    .snav-op .snav-mas { margin-left: auto; font-size: .7rem; opacity: .6; transition: transform .15s ease; }
    .snav-grupo.abierto .snav-mas { transform: rotate(90deg); }
    .snav-chip {
        margin-left: auto;
        padding: 2px 8px;
        border-radius: 40px;
        font-size: .68rem;
        font-weight: 700;
        background: #f16b1a;
        color: #fff;
    }
    .snav-chip.listo { background: #e8f3e6; color: #1d4a27; }
    .snav-sep { height: 1px; margin: 6px 4px; background: #eef0f2; }

    .snav-opciones { display: none; padding: 2px 0 4px 28px; }
    .snav-grupo.abierto .snav-opciones { display: block; }
    .snav-opciones .snav-op { font-weight: 500; padding: 7px 12px; }

    @media (max-width: 640px) {
        .snav-menu { position: fixed; left: 12px; right: 12px; top: auto; transform: none; min-width: 0; }
        .snav-item.abierto .snav-menu { animation: none; }
    }
</style>

<nav class="menu-navegacion snav" aria-label="Módulos">
    <div class="nav-links">
        <?php foreach ($modulos as $clave => $m): ?>
            <?php
            $activo = false;
            foreach ($m['rutas'] as $r) {
                if ($coincide($ruta, $r)) { $activo = true; break; }
            }
            $tieneSub = !empty($m['sub']);
            ?>
            <div class="snav-item" data-modulo="<?= esc($clave, 'attr') ?>">
                <a href="<?= base_url($m['url']) ?>"
                   class="nav-link<?= $activo ? ' activo' : '' ?><?= $tieneSub ? ' snav-toggle' : '' ?>"
                   <?php if ($tieneSub): ?>aria-haspopup="true" aria-expanded="false"<?php endif; ?>>
                    <i class="<?= esc($m['icono'], 'attr') ?>"></i> <?= esc($m['titulo']) ?>
                    <?php if ($tieneSub): ?><i class="fas fa-chevron-down snav-flecha"></i><?php endif; ?>
                </a>

                <?php if ($tieneSub): ?>
                    <div class="snav-menu" role="menu">
                        <?php foreach ($m['sub'] as $i => $s): ?>
                            <?php if ($i === 1): ?><div class="snav-sep"></div><?php endif; ?>

                            <?php if (!empty($s['opciones'])): ?>
                                <div class="snav-grupo">
                                    <button type="button" class="snav-op snav-abrir-grupo" aria-expanded="false">
                                        <i class="fas <?= esc($s['icono'], 'attr') ?>"></i> <?= esc($s['titulo']) ?>
                                        <i class="fas fa-chevron-right snav-mas"></i>
                                    </button>
                                    <div class="snav-opciones">
                                        <?php foreach ($s['opciones'] as $o): ?>
                                            <a class="snav-op" role="menuitem" href="<?= base_url($o['url']) ?>"
                                               <?= !empty($o['nueva']) ? 'target="_blank" rel="noopener"' : '' ?>>
                                                <i class="fas <?= esc($o['icono'], 'attr') ?>"></i> <?= esc($o['titulo']) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <a class="snav-op<?= $ruta === $s['url'] ? ' actual' : '' ?>" role="menuitem"
                                   href="<?= base_url($s['url']) ?>"
                                   <?= !empty($s['id']) ? 'id="' . esc($s['id'], 'attr') . '"' : '' ?>>
                                    <i class="fas <?= esc($s['icono'], 'attr') ?>"></i> <?= esc($s['titulo']) ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</nav>

<script>
(function () {
    const nav = document.currentScript.previousElementSibling;
    if (!nav || !nav.classList.contains('snav')) return;

    function cerrarTodo(excepto) {
        nav.querySelectorAll('.snav-item.abierto').forEach(function (item) {
            if (item === excepto) return;
            item.classList.remove('abierto');
            item.querySelector('.snav-toggle').setAttribute('aria-expanded', 'false');
        });
    }

    // Estado del corte de hoy, solo la primera vez que se abre Ventas
    let corteRevisado = false;
    function revisarCorte() {
        if (corteRevisado) return;
        corteRevisado = true;
        const enlace = document.getElementById('snavCorte');
        if (!enlace) return;
        fetch('<?= base_url('dashboard/datos') ?>', { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : null)
            .then(j => {
                const c = j && j.data && j.data.corte;
                if (!c) return;
                const chip = document.createElement('span');
                chip.className = 'snav-chip' + (c.hoy_guardado ? ' listo' : '');
                chip.textContent = c.hoy_guardado ? 'Hecho hoy' : 'Pendiente hoy';
                enlace.appendChild(chip);
            })
            .catch(() => {});
    }

    // Con mouse el menú se abre al pasar el cursor (CSS) y el clic lleva al módulo.
    // En celular o tableta no hay "pasar el cursor": ahí el primer toque abre el menú.
    const conMouse = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    nav.querySelectorAll('.snav-item').forEach(function (item) {
        if (item.dataset.modulo === 'ventas') item.addEventListener('mouseenter', revisarCorte);
    });

    nav.querySelectorAll('.snav-toggle').forEach(function (boton) {
        boton.addEventListener('click', function (e) {
            if (conMouse) return; // navega a la pantalla del módulo
            e.preventDefault();
            const item = boton.closest('.snav-item');
            const abrir = !item.classList.contains('abierto');
            cerrarTodo(item);
            item.classList.toggle('abierto', abrir);
            boton.setAttribute('aria-expanded', abrir ? 'true' : 'false');
            if (abrir && item.dataset.modulo === 'ventas') revisarCorte();
        });
    });

    // "Exportar inventario" despliega PDF y Excel dentro del mismo menú
    nav.querySelectorAll('.snav-abrir-grupo').forEach(function (boton) {
        boton.addEventListener('click', function (e) {
            e.stopPropagation();
            const grupo = boton.closest('.snav-grupo');
            const abrir = !grupo.classList.contains('abierto');
            grupo.classList.toggle('abierto', abrir);
            boton.setAttribute('aria-expanded', abrir ? 'true' : 'false');
        });
    });

    document.addEventListener('click', function (e) { if (!nav.contains(e.target)) cerrarTodo(null); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrarTodo(null); });
})();
</script>
