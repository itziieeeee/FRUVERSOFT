<?php
/**
 * app/Views/pantalla_alertas.php
 * Recibe: $alertas = ['productos'=>[], 'resumen'=>[], 'config'=>[], 'generado'=>'']
 */
$productos = $alertas['productos'];
$resumen   = $alertas['resumen'];
$cfg       = $alertas['config'];

$num = fn($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.') ?: '0';

$etiquetas = [
    'critico'     => ['CRÍTICO',      'fa-triangle-exclamation', '#dc2626'],
    'atencion'    => ['EN ATENCIÓN',  'fa-circle-exclamation',   '#ea580c'],
    'ok'          => ['OK',           'fa-circle-check',         '#16a34a'],
    'sin_consumo' => ['SIN VENTAS',   'fa-circle-minus',         '#64748b'],
];

$criticos = array_values(array_filter($productos, fn($p) => $p['nivel'] === 'critico'));
$atencion = array_values(array_filter($productos, fn($p) => $p['nivel'] === 'atencion'));
$topCriticos = array_slice($criticos, 0, 8);

$textoAgota = function ($p) {
    if ($p['cobertura'] === null) { return '—'; }
    $d = (int) floor($p['cobertura']);
    $cuando = $d <= 0 ? 'Hoy' : ($d === 1 ? 'Mañana' : "En {$d} días");
    return $cuando . ' <small>(' . date('d/m', strtotime($p['agota'])) . ')</small>';
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FRUVER · Alertas de stock</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root { --verde: #1d4a27; --naranja: #f16e1f; --claro: #e8f3e6; --borde: #e0e0e0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', 'Segoe UI', sans-serif; background: #f5f7fa; color: #1e293b; padding-bottom: 60px; }

    .barra-superior { display: flex; align-items: center; justify-content: space-between; padding: .6rem 2rem; background: linear-gradient(90deg, var(--verde) 0%, #2a5e35 100%); gap: 1.5rem; flex-wrap: wrap; }
    .barra-superior img { height: 70px; object-fit: contain; }
    .user-actions { display: flex; align-items: center; gap: .8rem; flex-wrap: wrap; }
    .btn-user { color: #fff; text-decoration: none; font-size: .85rem; padding: .5rem 1.2rem; background: rgba(255,255,255,.15); border-radius: 50px; display: inline-flex; align-items: center; gap: 8px; transition: .2s ease; }
    .btn-user:hover { background: rgba(255,255,255,.28); color: #fff; transform: translateY(-1px); }
    .btn-user.activo { background: var(--naranja); color: #fff; box-shadow: 0 0 0 3px rgba(241,110,31,.25); }

    .contenedor { max-width: 1100px; margin: 1.4rem auto; padding: 0 1rem; }
    .titulo { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem; margin-bottom: .6rem; }
    .titulo h2 { color: var(--verde); font-size: 1.35rem; }
    .titulo small { color: #64748b; font-size: .78rem; }

    .aviso { border-radius: 12px; padding: .7rem 1rem; margin-bottom: 1rem; font-size: .9rem; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .aviso.alerta { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
    .aviso.bien { background: var(--claro); border: 1px solid #bbd9b3; color: var(--verde); }

    /* ===== KPI CARDS ===== */
    .resumen { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .7rem; margin-bottom: 1.2rem; }
    .chip { background: #fff; border: 2px solid transparent; border-radius: 14px; padding: .8rem .9rem; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,.06); text-align: left; font-family: inherit; transition: all .2s ease; position: relative; overflow: hidden; }
    .chip:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.1); }
    .chip::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; border-radius: 4px 0 0 4px; }
    .chip.todos::before { background: var(--verde); }
    .chip.critico::before { background: #dc2626; }
    .chip.atencion::before { background: #ea580c; }
    .chip.ok::before { background: #16a34a; }
    .chip.sin_consumo::before { background: #64748b; }
    .chip .n { font-size: 1.7rem; font-weight: 700; line-height: 1; }
    .chip .t { font-size: .75rem; font-weight: 600; margin-top: 4px; color: #475569; }
    .chip.todos .n { color: var(--verde); }
    .chip.critico .n { color: #dc2626; }
    .chip.atencion .n { color: #ea580c; }
    .chip.ok .n { color: #16a34a; }
    .chip.sin_consumo .n { color: #64748b; }
    .chip.sel { border-color: var(--verde); transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.12); }

    /* ===== TARJETAS / SECCIONES ===== */
    .tarjeta { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,.06); padding: 1rem 1.1rem; margin-bottom: 1rem; }
    .tarjeta-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: .7rem; flex-wrap: wrap; gap: .4rem; }
    .tarjeta-header h3 { color: var(--verde); font-size: .98rem; display: flex; align-items: center; gap: 7px; }
    .tarjeta-header .badge-count { background: #eef2f6; color: #475569; font-size: .72rem; padding: 3px 9px; border-radius: 50px; font-weight: 700; }

    .scroll { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .84rem; }
    th { background: var(--verde); color: #fff; text-align: left; padding: 8px 10px; font-size: .7rem; text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; }
    th:first-child { border-radius: 8px 0 0 0; }
    th:last-child { border-radius: 0 8px 0 0; }
    td { padding: 8px 10px; border-bottom: 1px solid #eef2f6; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr.f-critico { background: #fff5f5; }
    tr.f-atencion { background: #fffaf3; }
    tr.fila { transition: background .15s; }
    tr.fila:hover { background: #fef9f5; }
    td.r, th.r { text-align: right; }
    .cod { color: #94a3b8; font-size: .7rem; }

    /* ===== BADGES ===== */
    .badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 50px; font-size: .68rem; font-weight: 700; white-space: nowrap; }
    .badge.critico { background: #fee2e2; color: #b91c1c; }
    .badge.atencion { background: #ffedd5; color: #c2410c; }
    .badge.ok { background: #dcfce7; color: #15803d; }
    .badge.sin_consumo { background: #e2e8f0; color: #475569; }

    /* ===== BARRAS DE COBERTURA ===== */
    .barra { position: relative; height: 8px; background: #e2e8f0; border-radius: 6px; min-width: 90px; margin-top: 4px; }
    .barra i { display: block; height: 100%; border-radius: 6px; transition: width .4s ease; }
    .barra b { position: absolute; top: -3px; width: 2px; height: 14px; background: #1e293b; border-radius: 1px; }
    .barra.critico i { background: linear-gradient(90deg, #ef4444, #dc2626); }
    .barra.atencion i { background: linear-gradient(90deg, #f97316, #ea580c); }
    .barra.ok i { background: linear-gradient(90deg, #22c55e, #16a34a); }
    .cob { font-weight: 700; }
    .pedir { font-weight: 700; color: var(--naranja); white-space: nowrap; font-size: .8rem; }
    .merma-alta { color: #b91c1c; font-weight: 700; }

    /* ===== ACORDEÓN ===== */
    .acordeon { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,.06); margin-bottom: 1rem; overflow: hidden; }
    .acordeon summary { padding: 1rem 1.1rem; cursor: pointer; font-weight: 700; color: var(--verde); font-size: .92rem; display: flex; align-items: center; gap: 8px; list-style: none; transition: background .15s; }
    .acordeon summary::-webkit-details-marker { display: none; }
    .acordeon summary:hover { background: #f8faf9; }
    .acordeon summary .chevron { margin-left: auto; transition: transform .25s; color: #94a3b8; }
    .acordeon[open] summary .chevron { transform: rotate(180deg); }
    .acordeon .acordeon-body { padding: 0 1.1rem 1rem; }
    .acordeon .acordeon-body table { font-size: .82rem; }

    /* ===== PARÁMETROS ===== */
    .params { display: flex; flex-wrap: wrap; gap: .8rem; align-items: flex-end; }
    .params label { font-size: .75rem; font-weight: 600; color: #475569; display: block; }
    .params input { display: block; margin-top: 4px; width: 120px; padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; }
    .params input:focus { outline: 2px solid var(--naranja); border-color: transparent; }
    .btn { border: 0; background: var(--naranja); color: #fff; padding: 9px 18px; border-radius: 10px; font-weight: 700; cursor: pointer; font-family: inherit; transition: all .2s; }
    .btn:hover { background: #d35400; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(241,110,31,.3); }

    details.ayuda { font-size: .84rem; color: #475569; line-height: 1.6; }
    details.ayuda summary { cursor: pointer; font-weight: 700; color: var(--verde); }
    details.ayuda ul { margin: .5rem 0 0 1.2rem; }
    code { background: #f1f5f9; padding: 1px 6px; border-radius: 5px; font-size: .8rem; }

    #toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: var(--verde); color: #fff; padding: 11px 20px; border-radius: 10px; font-weight: 600; display: none; z-index: 50; box-shadow: 0 6px 20px rgba(0,0,0,.2); }
    .vacio { text-align: center; color: #94a3b8; padding: 2rem; }
    .sin-datos { text-align: center; color: #94a3b8; padding: 1.5rem; font-size: .88rem; }
</style>
</head>
<body>

<header class="barra-superior">
    <img src="<?= base_url('img/LOGO1.png') ?>" alt="FRUVER">
    <div class="user-actions">
        <a href="<?= base_url('alertas') ?>" class="btn-user activo"><i class="fas fa-chart-line"></i> Estado de inventario</a>
        <a href="<?= site_url('menusolo') ?>" class="btn-user"><i class="fas fa-arrow-left"></i> Regresar</a>
    </div>
</header>

<div class="contenedor">
    <div class="titulo">
        <h2><i class="fas fa-boxes-stacked" style="color:var(--naranja)"></i> Estado del inventario</h2>
        <small><i class="fas fa-sync-alt"></i> Se actualiza solo cada minuto</small>
    </div>

    <!-- AVISO PRINCIPAL -->
    <?php if ($criticos): ?>
        <div class="aviso alerta">
            <i class="fas fa-triangle-exclamation"></i>
            <span><strong><?= count($criticos) ?> producto<?= count($criticos) > 1 ? 's' : '' ?></strong> podría<?= count($criticos) > 1 ? 'n' : '' ?> agotarse antes de que llegue la nueva mercancía.</span>
        </div>
    <?php else: ?>
        <div class="aviso bien"><i class="fas fa-circle-check"></i> Ningún producto en nivel crítico por ahora.</div>
    <?php endif; ?>

    <!-- KPI CARDS INTERACTIVAS -->
    <div class="resumen" id="resumenChips">
        <button class="chip todos sel" data-f="todos">
            <div class="n"><?= count($productos) ?></div>
            <div class="t"><i class="fas fa-cubes"></i> Todos</div>
        </button>
        <button class="chip critico" data-f="critico">
            <div class="n"><?= $resumen['critico'] ?></div>
            <div class="t"><i class="fas fa-triangle-exclamation"></i> Comprar pronto</div>
        </button>
        <button class="chip ok" data-f="ok">
            <div class="n"><?= $resumen['ok'] ?></div>
            <div class="t"><i class="fas fa-circle-check"></i> Stock suficiente</div>
        </button>
        <button class="chip sin_consumo" data-f="sin_consumo">
            <div class="n"><?= $resumen['sin_consumo'] ?></div>
            <div class="t"><i class="fas fa-circle-minus"></i> Sin ventas</div>
        </button>
    </div>

    <!-- TOP CRÍTICOS (siempre visible) -->
    <?php if ($topCriticos): ?>
    <div class="tarjeta">
        <div class="tarjeta-header">
            <h3><i class="fas fa-fire" style="color:#dc2626"></i> Prioridad: productos críticos</h3>
            <span class="badge-count"><?= count($criticos) ?> en total</span>
        </div>
        <div class="scroll">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="r">Stock</th>
                        <th class="r">Consumo/día</th>
                        <th>Cobertura</th>
                        <th>Se acaba</th>
                        <th class="r">Comprar</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($topCriticos as $p):
                    $escala = max(0.1, ($p['dias_reposicion'] + $cfg['colchon_dias']) * 2.5);
                    $ancho  = $p['cobertura'] === null ? 0 : max(3, min(100, $p['cobertura'] / $escala * 100));
                    $marca  = min(100, $p['dias_reposicion'] / $escala * 100);
                ?>
                    <tr class="fila f-critico">
                        <td><strong><?= esc($p['nombre']) ?></strong><?php if (!empty($p['codigo'])): ?><div class="cod"><?= esc($p['codigo']) ?></div><?php endif; ?></td>
                        <td class="r"><strong><?= $num($p['stock']) ?></strong> <small><?= esc($p['unidad']) ?></small></td>
                        <td class="r"><?= $p['consumo_dia'] > 0 ? '<strong>' . $num($p['consumo_dia']) . '</strong>' : '—' ?></td>
                        <td>
                            <?php if ($p['cobertura'] !== null): ?>
                                <span class="cob"><?= $num($p['cobertura']) ?> días</span>
                                <div class="barra critico"><i style="width:<?= round($ancho) ?>%"></i><b style="left:<?= round($marca) ?>%"></b></div>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td><?= $textoAgota($p) ?></td>
                        <td class="r"><?= $p['pedir'] > 0 ? '<span class="pedir">' . $p['pedir'] . ' ' . esc($p['unidad']) . '</span>' : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (count($criticos) > 8): ?>
            <p style="text-align:center; font-size:.78rem; color:#94a3b8; margin-top:.6rem;">
                Mostrando 8 de <?= count($criticos) ?>. <a href="#todos" onclick="document.getElementById('accTodos').open = true" style="color:var(--naranja); font-weight:700;">Ver todos ↓</a>
            </p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ACORDEÓN: TODOS LOS PRODUCTOS -->
    <details class="acordeon" id="accTodos">
        <summary>
            <i class="fas fa-list"></i> Ver todos los productos (<?= count($productos) ?>)
            <i class="fas fa-chevron-down chevron"></i>
        </summary>
        <div class="acordeon-body">
            <div class="scroll">
            <table>
                <thead>
                    <tr>
                        <th>Estado</th>
                        <th>Producto</th>
                        <th class="r">Stock</th>
                        <th class="r">Consumo/día</th>
                        <th>Cobertura</th>
                        <th>Se acaba</th>
                        <th>Tiempo reponer</th>
                        <th class="r">Comprar</th>
                        <th>Merma</th>
                    </tr>
                </thead>
                <tbody id="tablaTodos">
                <?php foreach ($productos as $p):
                    [$etq, $ico] = $etiquetas[$p['nivel']];
                    $escala = max(0.1, ($p['dias_reposicion'] + $cfg['colchon_dias']) * 2.5);
                    $ancho  = $p['cobertura'] === null ? 0 : max(3, min(100, $p['cobertura'] / $escala * 100));
                    $marca  = min(100, $p['dias_reposicion'] / $escala * 100);
                ?>
                    <tr class="fila f-<?= $p['nivel'] ?>" data-nivel="<?= $p['nivel'] ?>">
                        <td><span class="badge <?= $p['nivel'] ?>"><i class="fas <?= $ico ?>"></i> <?= $etq ?></span></td>
                        <td><strong><?= esc($p['nombre']) ?></strong><?php if (!empty($p['codigo'])): ?><div class="cod"><?= esc($p['codigo']) ?></div><?php endif; ?></td>
                        <td class="r"><strong><?= $num($p['stock']) ?></strong> <small><?= esc($p['unidad']) ?></small></td>
                        <td class="r">
                            <?php if ($p['consumo_dia'] > 0): ?>
                                <strong><?= $num($p['consumo_dia']) ?></strong> <small><?= esc($p['unidad']) ?>/día</small>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['cobertura'] !== null): ?>
                                <span class="cob"><?= $num($p['cobertura']) ?> días</span>
                                <div class="barra <?= $p['nivel'] ?>"><i style="width:<?= round($ancho) ?>%"></i><b style="left:<?= round($marca) ?>%"></b></div>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td><?= $textoAgota($p) ?></td>
                        <td><input class="dias" type="number" min="0.5" max="90" step="0.5" value="<?= $num($p['dias_reposicion']) ?>"
                                   data-id="<?= $p['id'] ?>" data-orig="<?= $num($p['dias_reposicion']) ?>"
                                   style="width:60px; padding:5px 7px; border:1px solid #cbd5e1; border-radius:7px; font:inherit; font-size:.82rem; text-align:right;"> <small>días</small></td>
                        <td class="r"><?= $p['pedir'] > 0 ? '<span class="pedir">' . $p['pedir'] . ' ' . esc($p['unidad']) . '</span>' : '—' ?></td>
                        <td>
                            <?php if ($p['merma_pct'] > 0): ?>
                                <span class="<?= $p['merma_pct'] >= 15 ? 'merma-alta' : '' ?>"><?= $num($p['merma_pct']) ?>%</span>
                                <?php if ($p['motivo_top']): ?><div class="cod"><?= esc($p['motivo_top']) ?></div><?php endif; ?>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$productos): ?>
                    <tr><td colspan="9" class="vacio">Todavía no hay productos registrados.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </details>

</div>

<div id="toast"></div>

<script>
const URL_REPOSICION = '<?= base_url('alertas/reposicion') ?>';

function aviso(texto) {
    const t = document.getElementById('toast');
    t.textContent = texto; t.style.display = 'block';
    setTimeout(() => t.style.display = 'none', 2400);
}

async function enviar(url, datos) {
    const fd = new FormData();
    Object.keys(datos).forEach(k => fd.append(k, datos[k]));
    const r = await fetch(url, { method: 'POST', body: fd });
    return r.json();
}

// Filtro por nivel: al hacer clic en un chip, filtra la tabla de "todos"
// y abre el acordeón automáticamente.
document.querySelectorAll('.chip').forEach(chip => chip.addEventListener('click', () => {
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('sel'));
    chip.classList.add('sel');
    const f = chip.dataset.f;
    const acc = document.getElementById('accTodos');
    if (acc) acc.open = true;
    document.querySelectorAll('#tablaTodos tr.fila').forEach(tr => {
        tr.style.display = (f === 'todos' || tr.dataset.nivel === f) ? '' : 'none';
    });
    if (acc) acc.scrollIntoView({ behavior: 'smooth', block: 'start' });
}));

// Cambiar el tiempo de reposición de un producto
document.querySelectorAll('input.dias').forEach(inp => inp.addEventListener('change', async () => {
    try {
        const d = await enviar(URL_REPOSICION, { id_producto: inp.dataset.id, dias: inp.value });
        if (d.success) { aviso('Reposición actualizada'); setTimeout(() => location.reload(), 700); }
        else { aviso(d.message); inp.value = inp.dataset.orig; }
    } catch (e) { aviso('Error de conexión'); inp.value = inp.dataset.orig; }
}));

// Se actualiza sola, salvo que estés escribiendo
setInterval(() => {
    const a = document.activeElement;
    if (!a || (a.tagName !== 'INPUT' && a.tagName !== 'TEXTAREA')) location.reload();
}, 60000);
</script>
</body>
</html>