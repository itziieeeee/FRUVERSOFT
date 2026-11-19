<?php
/**
 * app/Views/costos_pdf.php  (se renderiza con Dompdf: tablas, sin flex ni grid)
 * Recibe: $periodo, $reporte, $variaciones, $logo, $generado
 */
$t  = $reporte['totales'];
$m  = fn($v) => ($v < 0 ? '-' : '') . '$' . number_format(abs((float) $v), 2);
$n  = fn($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.') ?: '0';
$f  = fn($d) => date('d/m/Y', strtotime($d));
$pc = fn($v) => $v === null ? '—' : number_format((float) $v, 1) . '%';
$color = fn($v) => $v < 0 ? '#b91c1c' : '#15803d';

$diasConMovimiento = array_values(array_filter($reporte['dias'], fn($d) => $d['ventas'] > 0 || $d['merma'] > 0));
$maxDia = 0;
foreach ($diasConMovimiento as $d) { $maxDia = max($maxDia, $d['ventas']); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de costos y ganancias</title>
<style>
    @page { margin: 26px 30px; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; color: #1e293b; }
    table { width: 100%; border-collapse: collapse; }
    h1 { font-size: 17px; color: #1d4a27; margin: 0; }
    h2 { font-size: 11px; color: #1d4a27; margin: 16px 0 5px 0; border-bottom: 2px solid #f16e1f; padding-bottom: 3px; }
    .sub { color: #64748b; font-size: 9px; margin-top: 3px; }
    .cab td { vertical-align: middle; }
    .kpi { width: 33.33%; padding: 3px; }
    .kpi .caja { border: 1px solid #d9e2d6; border-radius: 6px; padding: 7px 9px; background: #f8fbf6; }
    .kpi .t { font-size: 8px; color: #64748b; text-transform: uppercase; letter-spacing: .4px; }
    .kpi .v { font-size: 15px; font-weight: bold; margin-top: 2px; }
    .kpi .s { font-size: 8px; color: #64748b; margin-top: 1px; }
    th { background: #1d4a27; color: #fff; text-align: left; padding: 5px 6px; font-size: 8px; text-transform: uppercase; }
    td { padding: 4px 6px; border-bottom: 1px solid #e5eaee; }
    tr { page-break-inside: avoid; }
    .r { text-align: right; }
    .barra { height: 6px; background: #22c55e; border-radius: 3px; }
    .nota { font-size: 8px; color: #64748b; margin-top: 6px; line-height: 1.5; }
    .aviso { font-size: 8.5px; color: #92400e; background: #fffbeb; border: 1px solid #fcd34d; padding: 5px 8px; margin-top: 8px; }
    .pie { text-align: center; color: #94a3b8; font-size: 7.5px; margin-top: 16px; }
</style>
</head>
<body>

<table class="cab">
    <tr>
        <td style="width:70px;"><?php if (!empty($logo)): ?><img src="<?= $logo ?>" style="width:62px;"><?php endif; ?></td>
        <td>
            <h1>Reporte de costos y ganancias</h1>
            <div class="sub"><?= esc(ucfirst($periodo['texto'])) ?></div>
            <div class="sub">FRUVER · Generado el <?= esc($generado) ?></div>
        </td>
    </tr>
</table>

<h2>Resumen</h2>
<table>
    <tr>
        <td class="kpi"><div class="caja"><div class="t">Ventas</div><div class="v"><?= $m($t['ventas']) ?></div><div class="s"><?= $t['pedidos'] ?> pedido<?= $t['pedidos'] == 1 ? '' : 's' ?> · ticket promedio <?= $m($t['ticket']) ?></div></div></td>
        <td class="kpi"><div class="caja"><div class="t">Costo de lo vendido</div><div class="v"><?= $m($t['costo']) ?></div><div class="s">Según el costo de compra vigente</div></div></td>
        <td class="kpi"><div class="caja"><div class="t">Utilidad bruta</div><div class="v" style="color:<?= $color($t['utilidad']) ?>"><?= $m($t['utilidad']) ?></div><div class="s">Margen <?= $pc($t['margen']) ?></div></div></td>
    </tr>
    <tr>
        <td class="kpi"><div class="caja"><div class="t">Merma (costo)</div><div class="v" style="color:#b45309;"><?= $m($t['merma']) ?></div><div class="s">Caducidad automática: <?= $m($t['merma_auto']) ?></div></div></td>
        <td class="kpi"><div class="caja"><div class="t">Utilidad después de merma</div><div class="v" style="color:<?= $color($t['utilidad_neta']) ?>"><?= $m($t['utilidad_neta']) ?></div><div class="s">Utilidad bruta − merma</div></div></td>
        <td class="kpi"><div class="caja"><div class="t">Ventas − costo − merma</div><div class="v"><?= $t['ventas'] > 0 ? $pc($t['utilidad_neta'] / $t['ventas'] * 100) : '—' ?></div><div class="s">Margen después de merma</div></div></td>
    </tr>
</table>

<h2>Ganancias por producto</h2>
<?php if ($reporte['productos']): ?>
<table>
    <thead><tr><th>Producto</th><th class="r">Vendido</th><th class="r">Ventas</th><th class="r">Costo</th><th class="r">Utilidad</th><th class="r">Margen</th><th class="r">Merma ($)</th></tr></thead>
    <tbody>
    <?php foreach ($reporte['productos'] as $p): ?>
        <tr>
            <td><strong><?= esc($p['nombre']) ?></strong><?= $p['unidad_distinta'] ? ' *' : '' ?></td>
            <td class="r"><?= $n($p['unidades']) ?> <?= esc($p['unidad']) ?></td>
            <td class="r"><?= $m($p['ventas']) ?></td>
            <td class="r"><?= $m($p['costo']) ?></td>
            <td class="r" style="color:<?= $color($p['utilidad']) ?>"><strong><?= $m($p['utilidad']) ?></strong></td>
            <td class="r"><?= $pc($p['margen']) ?></td>
            <td class="r"><?= $p['merma_costo'] > 0 ? $m($p['merma_costo']) : '—' ?></td>
        </tr>
    <?php endforeach; ?>
        <tr>
            <td><strong>TOTAL</strong></td><td></td>
            <td class="r"><strong><?= $m($t['ventas']) ?></strong></td>
            <td class="r"><strong><?= $m($t['costo']) ?></strong></td>
            <td class="r"><strong><?= $m($t['utilidad']) ?></strong></td>
            <td class="r"><strong><?= $pc($t['margen']) ?></strong></td>
            <td class="r"><strong><?= $m($t['merma']) ?></strong></td>
        </tr>
    </tbody>
</table>
<?php else: ?>
    <p class="nota">No hubo ventas ni mermas en este periodo.</p>
<?php endif; ?>

<?php if ($periodo['dias'] > 1 && $diasConMovimiento): ?>
<h2>Resumen por día <span style="font-weight:normal; color:#64748b;">(solo días con movimiento)</span></h2>
<table>
    <thead><tr><th>Día</th><th class="r">Ventas</th><th class="r">Costo</th><th class="r">Merma</th><th class="r">Utilidad</th><th style="width:22%;"></th></tr></thead>
    <tbody>
    <?php foreach ($diasConMovimiento as $d): ?>
        <tr>
            <td><?= $f($d['dia']) ?></td>
            <td class="r"><?= $m($d['ventas']) ?></td>
            <td class="r"><?= $m($d['costo']) ?></td>
            <td class="r"><?= $d['merma'] > 0 ? $m($d['merma']) : '—' ?></td>
            <td class="r" style="color:<?= $color($d['utilidad']) ?>"><strong><?= $m($d['utilidad']) ?></strong></td>
            <td><div class="barra" style="width:<?= $maxDia > 0 ? max(2, round($d['ventas'] / $maxDia * 100)) : 0 ?>%;"></div></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<h2>Variación de costos del periodo</h2>
<?php if ($variaciones): ?>
<table>
    <thead><tr><th>Producto</th><th>Fecha de compra</th><th class="r">Costo anterior</th><th class="r">Costo nuevo</th><th class="r">Cambio</th><th class="r">%</th></tr></thead>
    <tbody>
    <?php foreach ($variaciones as $v): ?>
        <tr>
            <td><strong><?= esc($v['nombre']) ?></strong></td>
            <td><?= $f($v['fecha']) ?></td>
            <td class="r"><?= $m($v['anterior']) ?></td>
            <td class="r"><?= $m($v['costo']) ?></td>
            <td class="r" style="color:<?= $v['var_abs'] > 0 ? '#b91c1c' : '#15803d' ?>"><?= ($v['var_abs'] > 0 ? '+' : '') . $m($v['var_abs']) ?></td>
            <td class="r" style="color:<?= $v['var_abs'] > 0 ? '#b91c1c' : '#15803d' ?>"><?= $v['var_pct'] === null ? '—' : ($v['var_pct'] > 0 ? '+' : '') . number_format($v['var_pct'], 1) . '%' ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="nota">Costo por unidad de venta (por ejemplo, por kilo). Una alza aparece en rojo y una baja en verde.</div>
<?php else: ?>
    <p class="nota">No hubo compras con un costo distinto al anterior en este periodo.</p>
<?php endif; ?>

<?php if ($reporte['avisos']['unidad_distinta'] || $reporte['avisos']['sin_costo']): ?>
    <div class="aviso">
        <?php if ($reporte['avisos']['unidad_distinta']): ?>
            * Se vendió en una unidad distinta a la de la compra: <?= esc(implode(', ', $reporte['avisos']['unidad_distinta'])) ?>. Revisa el costo de esos productos.<br>
        <?php endif; ?>
        <?php if ($reporte['avisos']['sin_costo']): ?>
            Sin costo registrado (se tomó como $0): <?= esc(implode(', ', $reporte['avisos']['sin_costo'])) ?>.
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="nota" style="margin-top:12px;">
    <strong>Cómo se calcula.</strong> Ventas: pedidos confirmados del periodo. Costo: cantidad vendida × costo por unidad de la compra más reciente anterior a la venta
    (precio de compra ÷ unidades de venta que trae la unidad de compra). Merma: cantidad × costo del lote del que salió.
    Utilidad bruta = ventas − costo; utilidad después de merma = utilidad bruta − merma. Incluye ventas a crédito, cobradas o no.
</div>

<div class="pie">FRUVER · Reporte generado automáticamente por el sistema</div>
</body>
</html>