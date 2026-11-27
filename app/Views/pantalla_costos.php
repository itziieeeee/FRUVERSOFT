<?php
/**
 * app/Views/pantalla_costos.php
 * Recibe: $periodo, $reporte, $variaciones, $productos, $idProducto, $historial, $tieneConversion
 */
$t  = $reporte['totales'];
$m  = fn($v) => ($v < 0 ? '-' : '') . '$' . number_format(abs((float) $v), 2);
$n  = fn($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.') ?: '0';
$f  = fn($d) => date('d/m/Y', strtotime($d));
$pc = fn($v) => $v === null ? '—' : number_format((float) $v, 1) . '%';
$cls = fn($v) => $v < 0 ? 'neg' : 'pos';

// Parámetros que se conservan al navegar (periodo y rango)
$base = ['periodo' => $periodo['clave']];
if ($periodo['clave'] === 'rango') { $base['desde'] = $periodo['desde']; $base['hasta'] = $periodo['hasta']; }
$url    = fn(array $extra = []) => base_url('costos') . '?' . http_build_query($base + $extra);
$urlPdf = base_url('costos/pdf') . '?' . http_build_query($base);

$chips = ['hoy' => 'Hoy', 'ayer' => 'Ayer', 'semana' => 'Últimos 7 días', 'mes' => 'Este mes', 'mes_pasado' => 'Mes pasado'];

// ---------- gráfica de barras por día ----------
$serie = $reporte['dias'];
$nDias = count($serie);
$W = 720; $H = 200; $pad = 10; $padB = 24;
$maxV = 1.0;
foreach ($serie as $d) { $maxV = max($maxV, $d['ventas'], $d['costo'] + $d['merma']); }
$alto = $H - $padB - $pad;
$bw   = ($W - 2 * $pad) / max(1, $nDias);
$cadaN = max(1, (int) ceil($nDias / 8));

// ---------- línea de costo del producto elegido ----------
$puntos = array_reverse($historial);           // del más viejo al más nuevo
$lineaW = 640; $lineaH = 150;
$minC = $maxC = null;
foreach ($puntos as $p) { $minC = $minC === null ? $p['costo'] : min($minC, $p['costo']); $maxC = $maxC === null ? $p['costo'] : max($maxC, $p['costo']); }
$rango = ($maxC !== null && $maxC > $minC) ? ($maxC - $minC) : 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FRUVER · Costos y ganancias</title>
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
    .periodo-txt { color: #64748b; font-size: .9rem; margin-bottom: 1rem; }
    .btn { border: 0; background: var(--naranja); color: #fff; padding: 10px 18px; border-radius: 10px; font-weight: 700; cursor: pointer; font-family: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: .9rem; }
    .btn.sec { background: var(--verde); }

    .filtros { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; margin-bottom: 1rem; }
    .pill { padding: 8px 16px; border-radius: 50px; background: #fff; border: 2px solid transparent; color: #334155; text-decoration: none; font-weight: 600; font-size: .85rem; box-shadow: 0 2px 6px rgba(0,0,0,.06); }
    .pill.sel { border-color: var(--verde); color: var(--verde); background: var(--claro); }
    form.rango { display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; background: #fff; padding: 5px 10px; border-radius: 50px; box-shadow: 0 2px 6px rgba(0,0,0,.06); }
    form.rango input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 5px 8px; font: inherit; font-size: .82rem; }
    form.rango button { padding: 6px 14px; font-size: .82rem; }

    .aviso { border-radius: 12px; padding: .75rem 1rem; margin-bottom: .8rem; font-size: .88rem; }
    .aviso.error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
    .aviso.alerta { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }

    .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: .8rem; margin-bottom: 1rem; }
    .kpi { background: #fff; border-radius: 14px; padding: .9rem 1rem; box-shadow: 0 2px 8px rgba(0,0,0,.07); border-left: 5px solid var(--verde); }
    .kpi.c2 { border-left-color: #94a3b8; } .kpi.c3 { border-left-color: #16a34a; } .kpi.c4 { border-left-color: #f59e0b; } .kpi.c5 { border-left-color: var(--naranja); }
    .kpi .t { font-size: .74rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; }
    .kpi .v { font-size: 1.55rem; font-weight: 700; margin-top: 3px; }
    .kpi .s { font-size: .76rem; color: #64748b; margin-top: 2px; }
    .pos { color: #15803d; } .neg { color: #b91c1c; }

    .tarjeta { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: 1rem; margin-bottom: 1rem; }
    .tarjeta h3 { color: var(--verde); font-size: 1rem; margin-bottom: .7rem; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .tarjeta h3 small { color: #64748b; font-weight: 500; font-size: .78rem; }
    .scroll { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .86rem; min-width: 640px; }
    th { background: var(--verde); color: #fff; text-align: left; padding: 9px 10px; font-size: .72rem; text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; }
    td { padding: 9px 10px; border-bottom: 1px solid #eef2f6; }
    td.r, th.r { text-align: right; }
    tr.total td { font-weight: 700; background: #f8fafc; border-top: 2px solid #cbd5e1; }
    .vacio { text-align: center; color: #94a3b8; padding: 1.6rem; font-size: .9rem; }
    .tag { font-size: .68rem; font-weight: 700; padding: 2px 8px; border-radius: 50px; background: #fef3c7; color: #b45309; }
    .tag.alza { background: #fee2e2; color: #b91c1c; } .tag.baja { background: #dcfce7; color: #15803d; }
    svg { width: 100%; height: auto; display: block; }
    .leyenda { display: flex; gap: 1rem; font-size: .78rem; color: #475569; margin-top: .4rem; flex-wrap: wrap; }
    .leyenda i { display: inline-block; width: 11px; height: 11px; border-radius: 3px; margin-right: 5px; vertical-align: -1px; }
    select.prod { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 10px; font: inherit; font-size: .88rem; min-width: 220px; }
    input.conv { width: 84px; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; font-size: .84rem; text-align: right; }
    input.conv.vacio-conv { background: #fffbeb; border-color: #fcd34d; }
    details { font-size: .85rem; color: #475569; line-height: 1.6; }
    details summary { cursor: pointer; font-weight: 700; color: var(--verde); }
    details ul { margin: .5rem 0 0 1.2rem; }
    #toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: var(--verde); color: #fff; padding: 11px 20px; border-radius: 10px; font-weight: 600; display: none; z-index: 50; }
</style>
</head>
<body>

<header class="barra-superior">
    <img src="<?= base_url('img/LOGO1.png') ?>" alt="FRUVER">
    <a href="<?= site_url('pantalla_ventas') ?>" class="btn-user"><i class="fas fa-arrow-left"></i> Regresar a ventas</a>
</header>

<?= view('partials/nav_admin') ?>

<div class="contenedor">
    <div class="titulo">
        <h2><i class="fas fa-chart-line" style="color:var(--naranja)"></i> Costos y ganancias</h2>
        <a class="btn" href="<?= esc($urlPdf) ?>" target="_blank"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
    </div>
    <div class="periodo-txt"><strong><?= esc($periodo['etiqueta']) ?></strong> · <?= esc($periodo['texto']) ?></div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="aviso error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (!$tieneConversion): ?>
        <div class="aviso alerta"><i class="fas fa-triangle-exclamation"></i> Falta correr la migración que guarda cuántos kilos trae cada caja
            (<code>php spark migrate</code>). Mientras tanto se asume que 1 unidad de compra = 1 unidad de venta y los costos pueden salir inflados.</div>
    <?php endif; ?>

    <div class="filtros">
        <?php foreach ($chips as $clave => $texto): ?>
            <a class="pill <?= $periodo['clave'] === $clave ? 'sel' : '' ?>" href="<?= base_url('costos') . '?periodo=' . $clave ?>"><?= $texto ?></a>
        <?php endforeach; ?>
        <form class="rango" method="get" action="<?= base_url('costos') ?>">
            <input type="hidden" name="periodo" value="rango">
            <input type="date" name="desde" value="<?= esc($periodo['desde']) ?>" required>
            <span>a</span>
            <input type="date" name="hasta" value="<?= esc($periodo['hasta']) ?>" required>
            <button class="btn sec" type="submit">Ver</button>
        </form>
    </div>

    <div class="kpis">
        <div class="kpi"><div class="t">Ventas</div><div class="v"><?= $m($t['ventas']) ?></div><div class="s"><?= $t['pedidos'] ?> pedido<?= $t['pedidos'] == 1 ? '' : 's' ?> · ticket <?= $m($t['ticket']) ?></div></div>
        <div class="kpi c2"><div class="t">Costo de lo vendido</div><div class="v"><?= $m($t['costo']) ?></div><div class="s">Costo de compra vigente</div></div>
        <div class="kpi c3"><div class="t">Utilidad bruta</div><div class="v <?= $cls($t['utilidad']) ?>"><?= $m($t['utilidad']) ?></div><div class="s">Margen <?= $pc($t['margen']) ?></div></div>
        <div class="kpi c4"><div class="t">Merma (costo)</div><div class="v" style="color:#b45309;"><?= $m($t['merma']) ?></div><div class="s">Caducidad automática: <?= $m($t['merma_auto']) ?></div></div>
        <div class="kpi c5"><div class="t">Utilidad tras merma</div><div class="v <?= $cls($t['utilidad_neta']) ?>"><?= $m($t['utilidad_neta']) ?></div><div class="s">Bruta − merma</div></div>
    </div>

    <?php if ($reporte['avisos']['unidad_distinta'] || $reporte['avisos']['sin_costo']): ?>
        <div class="aviso alerta">
            <?php if ($reporte['avisos']['unidad_distinta']): ?>
                <i class="fas fa-triangle-exclamation"></i> Se vendió en una unidad distinta a la de la compra: <strong><?= esc(implode(', ', $reporte['avisos']['unidad_distinta'])) ?></strong>. Revisa su costo y su conversión.<br>
            <?php endif; ?>
            <?php if ($reporte['avisos']['sin_costo']): ?>
                <i class="fas fa-circle-info"></i> Sin costo registrado (se tomó como $0): <strong><?= esc(implode(', ', $reporte['avisos']['sin_costo'])) ?></strong>.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($nDias > 1): ?>
    <div class="tarjeta">
        <h3><i class="fas fa-chart-column"></i> Ventas contra costo por día <small>(el costo incluye la merma)</small></h3>
        <svg viewBox="0 0 <?= $W ?> <?= $H ?>" role="img" aria-label="Ventas y costo por día">
            <line x1="<?= $pad ?>" y1="<?= $H - $padB ?>" x2="<?= $W - $pad ?>" y2="<?= $H - $padB ?>" stroke="#cbd5e1" stroke-width="1"/>
            <?php foreach ($serie as $i => $d):
                $x   = $pad + $i * $bw;
                $h1  = $d['ventas'] / $maxV * $alto;
                $h2  = ($d['costo'] + $d['merma']) / $maxV * $alto;
                $anch = max(1, $bw * 0.4);
            ?>
                <rect x="<?= round($x + $bw * 0.08, 2) ?>" y="<?= round($H - $padB - $h1, 2) ?>" width="<?= round($anch, 2) ?>" height="<?= round($h1, 2) ?>" fill="#22c55e" rx="1.5"><title><?= $f($d['dia']) ?> · ventas <?= $m($d['ventas']) ?></title></rect>
                <rect x="<?= round($x + $bw * 0.08 + $anch + 1, 2) ?>" y="<?= round($H - $padB - $h2, 2) ?>" width="<?= round($anch, 2) ?>" height="<?= round($h2, 2) ?>" fill="#f97316" rx="1.5"><title><?= $f($d['dia']) ?> · costo + merma <?= $m($d['costo'] + $d['merma']) ?></title></rect>
                <?php if ($i % $cadaN === 0 || $i === $nDias - 1): ?>
                    <text x="<?= round($x + $bw / 2, 2) ?>" y="<?= $H - 7 ?>" font-size="10" text-anchor="middle" fill="#64748b"><?= date('d/m', strtotime($d['dia'])) ?></text>
                <?php endif; ?>
            <?php endforeach; ?>
        </svg>
        <div class="leyenda"><span><i style="background:#22c55e"></i>Ventas</span><span><i style="background:#f97316"></i>Costo + merma</span><span>Pasa el cursor sobre una barra para ver el monto.</span></div>
    </div>
    <?php endif; ?>

    <div class="tarjeta">
        <h3><i class="fas fa-apple-whole"></i> Ganancias por producto</h3>
        <div class="scroll">
        <table>
            <thead><tr><th>Producto</th><th class="r">Vendido</th><th class="r">Ventas</th><th class="r">Costo</th><th class="r">Utilidad</th><th class="r">Margen</th><th class="r">Merma ($)</th><th></th></tr></thead>
            <tbody>
            <?php if (!$reporte['productos']): ?>
                <tr><td colspan="8" class="vacio">No hubo ventas ni mermas en este periodo.</td></tr>
            <?php endif; ?>
            <?php foreach ($reporte['productos'] as $p): ?>
                <tr>
                    <td><strong><?= esc($p['nombre']) ?></strong></td>
                    <td class="r"><?= $n($p['unidades']) ?> <small><?= esc($p['unidad']) ?></small></td>
                    <td class="r"><?= $m($p['ventas']) ?></td>
                    <td class="r"><?= $m($p['costo']) ?></td>
                    <td class="r <?= $cls($p['utilidad']) ?>"><strong><?= $m($p['utilidad']) ?></strong></td>
                    <td class="r"><?= $pc($p['margen']) ?></td>
                    <td class="r"><?= $p['merma_costo'] > 0 ? $m($p['merma_costo']) : '—' ?></td>
                    <td>
                        <?php if ($p['unidad_distinta']): ?><span class="tag" title="Se vendió en una unidad distinta a la de la compra">revisar unidad</span><?php endif; ?>
                        <?php if ($p['sin_costo']): ?><span class="tag">sin costo</span><?php endif; ?>
                        <a href="<?= esc($url(['producto' => $p['id']])) ?>#historial" style="font-size:.78rem; color:var(--verde); font-weight:600;">ver costos</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($reporte['productos']): ?>
                <tr class="total"><td>TOTAL</td><td></td><td class="r"><?= $m($t['ventas']) ?></td><td class="r"><?= $m($t['costo']) ?></td><td class="r <?= $cls($t['utilidad']) ?>"><?= $m($t['utilidad']) ?></td><td class="r"><?= $pc($t['margen']) ?></td><td class="r"><?= $m($t['merma']) ?></td><td></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <?php if ($nDias > 1): ?>
    <div class="tarjeta">
        <details>
            <summary>Ver el detalle día por día (<?= $nDias ?> días)</summary>
            <div class="scroll" style="margin-top:.7rem;">
            <table>
                <thead><tr><th>Día</th><th class="r">Ventas</th><th class="r">Costo</th><th class="r">Merma</th><th class="r">Utilidad</th></tr></thead>
                <tbody>
                <?php foreach (array_reverse($serie) as $d): ?>
                    <tr>
                        <td><?= $f($d['dia']) ?></td>
                        <td class="r"><?= $d['ventas'] > 0 ? $m($d['ventas']) : '—' ?></td>
                        <td class="r"><?= $d['costo'] > 0 ? $m($d['costo']) : '—' ?></td>
                        <td class="r"><?= $d['merma'] > 0 ? $m($d['merma']) : '—' ?></td>
                        <td class="r <?= $cls($d['utilidad']) ?>"><?= ($d['ventas'] > 0 || $d['merma'] > 0) ? $m($d['utilidad']) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </details>
    </div>
    <?php endif; ?>

    <div class="tarjeta">
        <h3><i class="fas fa-arrow-trend-up"></i> Variación de costos <small>compras del periodo con un costo distinto al de la compra anterior</small></h3>
        <div class="scroll">
        <table>
            <thead><tr><th>Producto</th><th>Fecha</th><th class="r">Costo anterior</th><th class="r">Costo nuevo</th><th class="r">Cambio</th><th class="r">%</th><th>Precio sugerido</th></tr></thead>
            <tbody>
            <?php if (!$variaciones): ?>
                <tr><td colspan="7" class="vacio">No hubo cambios de costo en este periodo.</td></tr>
            <?php endif; ?>
            <?php foreach ($variaciones as $v): $alza = $v['var_abs'] > 0; ?>
                <tr>
                    <td><strong><?= esc($v['nombre']) ?></strong></td>
                    <td><?= $f($v['fecha']) ?></td>
                    <td class="r"><?= $m($v['anterior']) ?></td>
                    <td class="r"><?= $m($v['costo']) ?></td>
                    <td class="r <?= $alza ? 'neg' : 'pos' ?>"><?= ($alza ? '+' : '') . $m($v['var_abs']) ?></td>
                    <td class="r"><span class="tag <?= $alza ? 'alza' : 'baja' ?>"><?= $alza ? '▲' : '▼' ?> <?= number_format(abs((float) $v['var_pct']), 1) ?>%</span></td>
                    <td><?= $m($v['precio_sugerido']) ?> <small style="color:#64748b;">(margen <?= $pc($v['margen_sugerido']) ?>)</small></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="tarjeta" id="historial">
        <h3><i class="fas fa-clock-rotate-left"></i> Historial de costo por producto</h3>
        <form method="get" action="<?= base_url('costos') ?>" style="margin-bottom:.8rem; display:flex; gap:.5rem; flex-wrap:wrap; align-items:center;">
            <?php foreach ($base as $k => $v): ?><input type="hidden" name="<?= esc($k) ?>" value="<?= esc($v) ?>"><?php endforeach; ?>
            <select class="prod" name="producto" onchange="this.form.submit()">
                <option value="0">Elige un producto…</option>
                <?php foreach ($productos as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (int) $idProducto === (int) $p['id'] ? 'selected' : '' ?>><?= esc($p['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($idProducto && !$historial): ?>
            <div class="vacio">Este producto todavía no tiene compras registradas.</div>
        <?php elseif ($historial): ?>
            <?php if (count($puntos) > 1): ?>
            <svg viewBox="0 0 <?= $lineaW + 60 ?> <?= $lineaH + 40 ?>" role="img" aria-label="Costo por compra">
                <?php
                $coords = [];
                foreach ($puntos as $i => $p) {
                    $x = 40 + ($lineaW - 20) * ($i / max(1, count($puntos) - 1));
                    $y = 15 + ($lineaH - 20) * (1 - ($p['costo'] - $minC) / $rango);
                    $coords[] = [$x, $y, $p];
                }
                ?>
                <polyline fill="none" stroke="#1d4a27" stroke-width="2.5" points="<?= implode(' ', array_map(fn($c) => round($c[0], 1) . ',' . round($c[1], 1), $coords)) ?>"/>
                <?php foreach ($coords as [$x, $y, $p]): ?>
                    <circle cx="<?= round($x, 1) ?>" cy="<?= round($y, 1) ?>" r="4.5" fill="#f16e1f" stroke="#fff" stroke-width="1.5"><title><?= $f($p['fecha']) ?> · <?= $m($p['costo']) ?></title></circle>
                    <text x="<?= round($x, 1) ?>" y="<?= round($y - 9, 1) ?>" font-size="10.5" text-anchor="middle" font-weight="700" fill="#1e293b"><?= $m($p['costo']) ?></text>
                    <text x="<?= round($x, 1) ?>" y="<?= $lineaH + 28 ?>" font-size="9.5" text-anchor="middle" fill="#64748b"><?= date('d/m/y', strtotime($p['fecha'])) ?></text>
                <?php endforeach; ?>
            </svg>
            <?php endif; ?>

            <div class="scroll">
            <table>
                <thead><tr><th>Fecha</th><th class="r">Compra</th><th class="r">Precio por <?= esc($historial[0]['unidad_compra'] ?: 'unidad') ?></th>
                    <?php if ($tieneConversion): ?><th class="r" title="Cuántas unidades de venta trae 1 unidad de compra">Conversión</th><?php endif; ?>
                    <th class="r">Costo por <?= esc($historial[0]['unidad_venta']) ?></th><th class="r">Cambio</th><th class="r">Precio sugerido</th><th class="r">Margen</th></tr></thead>
                <tbody>
                <?php foreach ($historial as $h): ?>
                    <tr>
                        <td><?= $f($h['fecha']) ?></td>
                        <td class="r"><?= $n($h['cantidad_compra']) ?> <small><?= esc($h['unidad_compra'] ?: '') ?></small></td>
                        <td class="r"><?= $m($h['precio_compra']) ?></td>
                        <?php if ($tieneConversion): ?>
                        <td class="r"><input class="conv <?= $h['conversion'] === null ? 'vacio-conv' : '' ?>" type="number" min="0.01" step="0.01"
                                             data-id="<?= $h['id'] ?>" data-orig="<?= $h['conversion'] === null ? '' : $n($h['conversion']) ?>"
                                             value="<?= $h['conversion'] === null ? '' : $n($h['conversion']) ?>" placeholder="1 (sin dato)"
                                             title="¿Cuántos <?= esc($h['unidad_venta']) ?> trae 1 <?= esc($h['unidad_compra'] ?: 'unidad de compra') ?>?"></td>
                        <?php endif; ?>
                        <td class="r"><strong><?= $m($h['costo']) ?></strong></td>
                        <td class="r"><?php if ($h['var_pct'] !== null): $alza = $h['var_abs'] > 0; ?>
                            <span class="tag <?= $h['var_abs'] == 0 ? '' : ($alza ? 'alza' : 'baja') ?>"><?= $h['var_abs'] == 0 ? '=' : ($alza ? '▲' : '▼') . ' ' . number_format(abs($h['var_pct']), 1) . '%' ?></span>
                            <?php else: ?><small style="color:#94a3b8;">primera compra</small><?php endif; ?></td>
                        <td class="r"><?= $m($h['precio_sugerido']) ?></td>
                        <td class="r <?= ($h['margen_sugerido'] ?? 0) < 0 ? 'neg' : '' ?>"><?= $pc($h['margen_sugerido']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php if ($tieneConversion): ?>
                <div style="font-size:.78rem; color:#64748b; margin-top:.5rem;">La conversión es cuántas unidades de venta trae 1 unidad de compra (por ejemplo, 10 kilos por caja).
                    Las compras nuevas la guardan solas; en las anteriores puedes escribirla aquí y el costo se recalcula.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="vacio">Elige un producto para ver cómo ha cambiado su costo de compra.</div>
        <?php endif; ?>
    </div>

    <div class="tarjeta">
        <details>
            <summary>¿Cómo se calcula?</summary>
            <ul>
                <li><strong>Ventas</strong>: pedidos confirmados del periodo (incluye los de crédito, estén cobrados o no).</li>
                <li><strong>Costo por unidad</strong> = precio de compra ÷ unidades de venta que trae la unidad de compra (precio por caja ÷ kilos por caja).</li>
                <li><strong>Costo de lo vendido</strong> = cantidad vendida × costo de la compra más reciente anterior a esa venta.</li>
                <li><strong>Merma</strong> = cantidad × costo del lote del que salió, incluida la caducidad automática del sistema.</li>
                <li><strong>Utilidad bruta</strong> = ventas − costo. <strong>Utilidad tras merma</strong> = utilidad bruta − merma.</li>
            </ul>
        </details>
    </div>
</div>

<div id="toast"></div>
<script>
const URL_CONV = '<?= base_url('costos/conversion') ?>';
function aviso(t) { const e = document.getElementById('toast'); e.textContent = t; e.style.display = 'block'; setTimeout(() => e.style.display = 'none', 2400); }

document.querySelectorAll('input.conv').forEach(inp => inp.addEventListener('change', async () => {
    const fd = new FormData();
    fd.append('id_entrada', inp.dataset.id);
    fd.append('valor', inp.value);
    try {
        const d = await (await fetch(URL_CONV, { method: 'POST', body: fd })).json();
        aviso(d.message);
        if (d.success) setTimeout(() => location.reload(), 700); else inp.value = inp.dataset.orig;
    } catch (e) { aviso('Error de conexión'); inp.value = inp.dataset.orig; }
}));
</script>
</body>
</html>