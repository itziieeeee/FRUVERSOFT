<?php
/**
 * app/Views/reportes_ventas.php
 * Reporte de ventas por día y por producto, con gráficas.
 * Recibe: $periodo, $reporte, $metodos
 */
$t  = $reporte['totales'];
$m  = fn($v) => ($v < 0 ? '-' : '') . '$' . number_format(abs((float) $v), 2);
$n  = fn($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.') ?: '0';
$pc = fn($v) => $v === null ? '—' : number_format((float) $v, 1) . '%';

// Parámetros que se conservan al navegar y al exportar (periodo y rango)
$base = ['periodo' => $periodo['clave']];
if ($periodo['clave'] === 'rango') { $base['desde'] = $periodo['desde']; $base['hasta'] = $periodo['hasta']; }
$qs     = http_build_query($base);
$chips  = ['hoy' => 'Hoy', 'ayer' => 'Ayer', 'semana' => 'Últimos 7 días', 'mes' => 'Este mes', 'mes_pasado' => 'Mes pasado'];

// Datos de las gráficas
$dias = $reporte['dias'];
$productos = $reporte['productos'];
usort($productos, fn($a, $b) => [$b['ventas'], $a['nombre']] <=> [$a['ventas'], $b['nombre']]);
$top = array_slice(array_values(array_filter($productos, fn($p) => $p['ventas'] > 0)), 0, 8);

$nombresMetodo = ['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'tarjeta' => 'Tarjeta'];
$cobros = [];
foreach ($nombresMetodo as $clave => $nombre) {
    $cobros[] = ['metodo' => $nombre, 'cobrado' => (float) ($metodos[$clave]['cobrado'] ?? 0)];
}
$hayCobros = array_sum(array_column($cobros, 'cobrado')) > 0;
$hayVentas = $t['ventas'] > 0;

$datosGraficas = [
    'dias'  => array_map(fn($d) => [
        'etiqueta' => date('d/m', strtotime($d['dia'])), 'ventas' => $d['ventas'], 'utilidad' => $d['utilidad'],
    ], $dias),
    'top'   => array_map(fn($p) => ['nombre' => $p['nombre'], 'ventas' => $p['ventas']], $top),
    'cobros' => $cobros,
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FRUVER · Reporte de ventas</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root { --verde: #1d4a27; --naranja: #f16e1f; --claro: #e8f3e6; --borde: #e0e0e0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', 'Segoe UI', sans-serif; background: #f5f7fa; color: #1e293b; padding-bottom: 60px; }

    .barra-superior { display: flex; align-items: center; justify-content: space-between; padding: .6rem 2rem; background: linear-gradient(90deg, var(--verde) 0%, #2a5e35 100%); gap: 1.5rem; flex-wrap: wrap; }
    .barra-superior img { height: 70px; object-fit: contain; }
    .btn-user { color: #fff; text-decoration: none; font-size: .85rem; padding: .5rem 1.2rem; background: rgba(255,255,255,.15); border-radius: 50px; display: inline-flex; align-items: center; gap: 8px; }
    .menu-navegacion { background: #fff; padding: .8rem 2rem; box-shadow: 0 2px 8px rgba(0,0,0,.05); border-bottom: 1px solid var(--borde); }
    .nav-links { display: flex; flex-wrap: wrap; gap: .4rem; justify-content: center; }
    .nav-link { padding: .6rem 1.1rem; color: #333; text-decoration: none; font-weight: 600; border-radius: 40px; font-size: .9rem; }
    .nav-link:hover { background: #fef3ed; }
    .nav-link.activo { color: var(--naranja); background: rgba(241,110,31,.1); }

    .contenedor { max-width: 1250px; margin: 1.4rem auto; padding: 0 1rem; }
    .titulo { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .6rem; margin-bottom: .4rem; }
    .titulo h2 { color: var(--verde); font-size: 1.4rem; }
    .acciones { display: flex; gap: .5rem; flex-wrap: wrap; }
    .periodo-txt { color: #64748b; font-size: .9rem; margin-bottom: 1rem; }
    .btn { border: 0; background: var(--verde); color: #fff; padding: 9px 16px; border-radius: 10px; font-weight: 700; cursor: pointer; font-family: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: .85rem; }
    .btn.sec { background: #e2e8f0; color: #334155; }

    .filtros { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; margin-bottom: 1rem; }
    .pill { padding: 8px 16px; border-radius: 50px; background: #fff; border: 2px solid transparent; color: #334155; text-decoration: none; font-weight: 600; font-size: .85rem; box-shadow: 0 2px 6px rgba(0,0,0,.06); }
    .pill.sel { border-color: var(--verde); color: var(--verde); background: var(--claro); }
    form.rango { display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; background: #fff; padding: 5px 10px; border-radius: 50px; box-shadow: 0 2px 6px rgba(0,0,0,.06); }
    form.rango input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 5px 8px; font: inherit; font-size: .82rem; }
    form.rango button { border: 0; background: var(--verde); color: #fff; border-radius: 50px; padding: 6px 14px; font: inherit; font-size: .82rem; font-weight: 600; cursor: pointer; }

    .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .8rem; margin-bottom: 1rem; }
    .kpi { background: #fff; border-radius: 14px; padding: .9rem 1rem; box-shadow: 0 2px 8px rgba(0,0,0,.07); border-left: 5px solid var(--verde); }
    .kpi.naranja { border-left-color: var(--naranja); } .kpi.azul { border-left-color: #2563eb; } .kpi.rojo { border-left-color: #dc2626; } .kpi.ambar { border-left-color: #f59e0b; }
    .kpi .t { font-size: .74rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; }
    .kpi .v { font-size: 1.5rem; font-weight: 700; margin-top: 3px; }
    .kpi .s { font-size: .76rem; color: #64748b; margin-top: 2px; }

    .tarjeta { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: 1rem 1.1rem; margin-bottom: 1rem; }
    .tarjeta h3 { color: var(--verde); font-size: 1rem; margin-bottom: .7rem; display: flex; gap: 8px; align-items: center; }
    .tarjeta h3 i { color: var(--naranja); }
    .tarjeta h3 small { font-weight: 500; color: #64748b; font-size: .76rem; }
    .lienzo { position: relative; height: 280px; }
    .lienzo.bajo { height: 260px; }
    .dos { display: grid; grid-template-columns: 3fr 2fr; gap: 1rem; }
    .dos .tarjeta { margin-bottom: 1rem; }
    @media (max-width: 900px) { .dos { grid-template-columns: 1fr; } }

    .scroll { overflow: auto; max-height: 420px; }
    table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    th { background: var(--verde); color: #fff; text-align: left; padding: 8px 9px; font-size: .7rem; text-transform: uppercase; letter-spacing: .4px; position: sticky; top: 0; }
    td { padding: 8px 9px; border-bottom: 1px solid #eef2f6; }
    td.r, th.r { text-align: right; }
    tr.total td { font-weight: 700; background: #f8fafc; border-top: 2px solid #cbd5e1; }
    .pos { color: #15803d; } .neg { color: #b91c1c; }
    .vacio { text-align: center; color: #94a3b8; padding: 1.6rem 1rem; }
    .vacio i { display: block; font-size: 1.8rem; margin-bottom: .5rem; }
    .alerta { background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; border-radius: 10px; padding: .6rem .9rem; font-size: .85rem; margin-bottom: 1rem; }
</style>
</head>
<body>

<header class="barra-superior">
    <img src="<?= base_url('img/LOGO1.png') ?>" alt="FRUVER">
    <a href="<?= site_url('menusolo') ?>" class="btn-user"><i class="fas fa-arrow-left"></i> Regresar</a>
</header>

<nav class="menu-navegacion">
    <div class="nav-links">
        <a href="<?= base_url('pantalla_ventas') ?>" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="<?= base_url('pantalla_pedidos') ?>" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="<?= base_url('reportes') ?>" class="nav-link activo"><i class="fas fa-chart-column"></i> Reportes</a>
        <a href="<?= base_url('costos') ?>" class="nav-link"><i class="fas fa-chart-line"></i> Costos</a>
        <a href="<?= base_url('corte') ?>" class="nav-link"><i class="fas fa-cash-register"></i> Corte de caja</a>
        <a href="<?= base_url('cobranza') ?>" class="nav-link"><i class="fas fa-hand-holding-dollar"></i> Cobranza</a>
    </div>
</nav>

<div class="contenedor">
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alerta"><i class="fas fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="titulo">
        <h2><i class="fas fa-chart-column" style="color:var(--naranja)"></i> Reporte de ventas</h2>
        <div class="acciones">
            <a class="btn sec" href="<?= base_url('exportar/ventas/pdf') . '?' . $qs ?>" target="_blank" rel="noopener"><i class="fas fa-file-pdf"></i> PDF</a>
            <a class="btn sec" href="<?= base_url('exportar/ventas/xlsx') . '?' . $qs ?>"><i class="fas fa-file-excel"></i> Excel</a>
            <a class="btn" href="<?= base_url('costos') . '?' . $qs ?>"><i class="fas fa-chart-line"></i> Costos y ganancias</a>
        </div>
    </div>
    <div class="periodo-txt"><strong><?= esc($periodo['etiqueta']) ?></strong> · <?= esc($periodo['texto']) ?></div>

    <div class="filtros">
        <?php foreach ($chips as $clave => $texto): ?>
            <a class="pill <?= $periodo['clave'] === $clave ? 'sel' : '' ?>" href="<?= base_url('reportes') . '?periodo=' . $clave ?>"><?= $texto ?></a>
        <?php endforeach; ?>
        <form class="rango" method="get" action="<?= base_url('reportes') ?>">
            <input type="hidden" name="periodo" value="rango">
            <input type="date" name="desde" value="<?= esc($periodo['desde']) ?>" required>
            <span>a</span>
            <input type="date" name="hasta" value="<?= esc($periodo['hasta']) ?>" required>
            <button type="submit">Ver</button>
        </form>
    </div>

    <div class="kpis">
        <div class="kpi"><div class="t">Ventas</div><div class="v"><?= $m($t['ventas']) ?></div><div class="s"><?= (int) $t['pedidos'] ?> pedido<?= $t['pedidos'] === 1 ? '' : 's' ?></div></div>
        <div class="kpi azul"><div class="t">Ticket promedio</div><div class="v"><?= $m($t['ticket']) ?></div><div class="s">Por pedido</div></div>
        <div class="kpi naranja"><div class="t">Utilidad</div><div class="v <?= $t['utilidad'] < 0 ? 'neg' : 'pos' ?>"><?= $m($t['utilidad']) ?></div><div class="s">Margen <?= $pc($t['margen']) ?> · antes de merma</div></div>
        <div class="kpi rojo"><div class="t">Merma</div><div class="v"><?= $m($t['merma']) ?></div><div class="s">Utilidad neta <?= $m($t['utilidad_neta']) ?></div></div>
    </div>

    <?php if (!$hayVentas): ?>
        <div class="tarjeta"><div class="vacio"><i class="fas fa-chart-simple"></i>No hubo ventas en este periodo. Prueba con otro rango de fechas.</div></div>
    <?php else: ?>
        <div class="tarjeta">
            <h3><i class="fas fa-chart-column"></i> Ventas por día <small>barras: ventas · línea: utilidad después de costo y merma</small></h3>
            <div class="lienzo"><canvas id="gDias"></canvas></div>
        </div>

        <div class="dos">
            <div class="tarjeta">
                <h3><i class="fas fa-ranking-star"></i> Productos más vendidos <small>por importe</small></h3>
                <div class="lienzo bajo"><canvas id="gTop"></canvas></div>
            </div>
            <div class="tarjeta">
                <h3><i class="fas fa-wallet"></i> Cobros por método de pago</h3>
                <?php if ($hayCobros): ?>
                    <div class="lienzo bajo"><canvas id="gCobros"></canvas></div>
                <?php else: ?>
                    <div class="vacio"><i class="fas fa-wallet"></i>No hay cobros registrados en este periodo.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="dos" style="grid-template-columns: 2fr 3fr;">
        <div class="tarjeta">
            <h3><i class="fas fa-calendar-day"></i> Ventas por día</h3>
            <div class="scroll">
                <table>
                    <thead><tr><th>Día</th><th class="r">Ventas</th><th class="r">Costo</th><th class="r">Merma</th><th class="r">Utilidad</th></tr></thead>
                    <tbody>
                    <?php foreach (array_reverse($dias) as $d): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($d['dia'])) ?></td>
                            <td class="r"><?= $m($d['ventas']) ?></td>
                            <td class="r"><?= $m($d['costo']) ?></td>
                            <td class="r"><?= $m($d['merma']) ?></td>
                            <td class="r <?= $d['utilidad'] < 0 ? 'neg' : 'pos' ?>"><strong><?= $m($d['utilidad']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($dias): ?>
                        <tr class="total"><td>Total</td><td class="r"><?= $m($t['ventas']) ?></td><td class="r"><?= $m($t['costo']) ?></td><td class="r"><?= $m($t['merma']) ?></td><td class="r"><?= $m($t['utilidad_neta']) ?></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tarjeta">
            <h3><i class="fas fa-apple-whole"></i> Ventas por producto</h3>
            <div class="scroll">
                <table>
                    <thead><tr><th>Producto</th><th class="r">Cantidad</th><th class="r">Ventas</th><th class="r">Costo</th><th class="r">Utilidad</th><th class="r">Margen</th></tr></thead>
                    <tbody>
                    <?php if (!$productos): ?>
                        <tr><td colspan="6" class="vacio">No hubo ventas en este periodo.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($productos as $p): ?>
                        <tr>
                            <td><strong><?= esc($p['nombre']) ?></strong></td>
                            <td class="r"><?= $n($p['unidades']) ?> <?= esc($p['unidad']) ?></td>
                            <td class="r"><?= $m($p['ventas']) ?></td>
                            <td class="r"><?= $m($p['costo']) ?></td>
                            <td class="r <?= $p['utilidad'] < 0 ? 'neg' : 'pos' ?>"><?= $m($p['utilidad']) ?></td>
                            <td class="r"><?= $pc($p['margen']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($productos): ?>
                        <tr class="total"><td>Total</td><td></td><td class="r"><?= $m($t['ventas']) ?></td><td class="r"><?= $m($t['costo']) ?></td><td class="r"><?= $m($t['utilidad']) ?></td><td class="r"><?= $pc($t['margen']) ?></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if ($hayVentas): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const D = <?= json_encode($datosGraficas, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const dinero = n => (n < 0 ? '-' : '') + '$' + Math.abs(Number(n || 0)).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const eje = v => '$' + Number(v).toLocaleString('es-MX');

    if (!window.Chart) {
        document.querySelectorAll('.lienzo').forEach(l => { l.innerHTML = '<div class="vacio"><i class="fas fa-wifi"></i>No se pudieron cargar las gráficas (revisa tu conexión). Las tablas de abajo tienen los mismos datos.</div>'; l.style.height = 'auto'; });
        return;
    }

    // Ventas (barras) y utilidad (línea) por día
    new Chart(document.getElementById('gDias'), {
        data: {
            labels: D.dias.map(d => d.etiqueta),
            datasets: [
                { type: 'bar',  label: 'Ventas',   data: D.dias.map(d => d.ventas),   backgroundColor: '#2d6a3b', borderRadius: 6, maxBarThickness: 48, order: 2 },
                { type: 'line', label: 'Utilidad', data: D.dias.map(d => d.utilidad), borderColor: '#f16e1f', backgroundColor: '#f16e1f', tension: .3, pointRadius: 3, order: 1 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { tooltip: { callbacks: { label: c => ' ' + c.dataset.label + ': ' + dinero(c.parsed.y) } } },
            scales: { y: { ticks: { callback: eje } }, x: { grid: { display: false } } }
        }
    });

    // Productos más vendidos
    new Chart(document.getElementById('gTop'), {
        type: 'bar',
        data: { labels: D.top.map(p => p.nombre), datasets: [{ label: 'Ventas', data: D.top.map(p => p.ventas), backgroundColor: '#f16e1f', borderRadius: 6 }] },
        options: {
            indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' ' + dinero(c.parsed.x) } } },
            scales: { x: { ticks: { callback: eje } }, y: { grid: { display: false } } }
        }
    });

    // Cobros por método
    const lienzoCobros = document.getElementById('gCobros');
    if (lienzoCobros) {
        const metodos = D.cobros.filter(c => c.cobrado > 0);
        new Chart(lienzoCobros, {
            type: 'doughnut',
            data: { labels: metodos.map(c => c.metodo), datasets: [{ data: metodos.map(c => c.cobrado), backgroundColor: ['#1d4a27', '#f16e1f', '#2563eb'], borderWidth: 2 }] },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '58%',
                plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: c => ' ' + c.label + ': ' + dinero(c.parsed) } } }
            }
        });
    }
})();
</script>
<?php endif; ?>

</body>
</html>
