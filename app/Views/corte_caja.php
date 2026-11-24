<?php
/**
 * app/Views/corte_caja.php
 * Recibe: $resumen, $guardado, $historial, $hoy, $fondoSugerido
 */
$m = fn($v) => ($v < 0 ? '-' : '') . '$' . number_format(abs((float) $v), 2);
$fecha = $resumen['fecha'];
$ayer  = date('Y-m-d', strtotime($fecha . ' -1 day'));
$manana = date('Y-m-d', strtotime($fecha . ' +1 day'));
$fechaTexto = date('d/m/Y', strtotime($fecha));
$nombresMetodo = ['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'tarjeta' => 'Tarjeta'];
$nombresOrigen = ['venta' => 'Pago en la venta', 'abono' => 'Abono / pago registrado', 'entrega' => 'Cobro del repartidor',
                  'devolucion' => 'Reembolso por devolución', 'ajuste' => 'Corrección', 'migracion' => 'Pagos anteriores al registro'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FRUVER · Corte de caja <?= esc($fechaTexto) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root { --verde: #1d4a27; --naranja: #f16e1f; --borde: #e0e0e0; }
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

    .contenedor { max-width: 1100px; margin: 1.4rem auto; padding: 0 1rem; }
    .titulo { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .6rem; margin-bottom: .4rem; }
    h2 { color: var(--verde); font-size: 1.4rem; }
    .sub { color: #64748b; font-size: .88rem; margin-bottom: 1rem; }
    .fechas { display: flex; gap: .4rem; align-items: center; flex-wrap: wrap; }
    .fechas input { padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 10px; font: inherit; font-size: .85rem; }
    .btn { border: 0; background: var(--verde); color: #fff; padding: 9px 16px; border-radius: 10px; font-weight: 700; cursor: pointer; font-family: inherit; font-size: .85rem; text-decoration: none; display: inline-flex; gap: 6px; align-items: center; }
    .btn.sec { background: #e2e8f0; color: #334155; }
    .btn.naranja { background: var(--naranja); }
    .btn:disabled { opacity: .6; cursor: wait; }

    .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .8rem; margin-bottom: 1rem; }
    .kpi { background: #fff; border-radius: 14px; padding: .9rem 1rem; box-shadow: 0 2px 8px rgba(0,0,0,.07); border-left: 5px solid var(--verde); }
    .kpi.rojo { border-left-color: #dc2626; } .kpi.ambar { border-left-color: #f59e0b; } .kpi.azul { border-left-color: #2563eb; }
    .kpi .t { font-size: .74rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; }
    .kpi .v { font-size: 1.5rem; font-weight: 700; margin-top: 3px; }
    .kpi .s { font-size: .76rem; color: #64748b; margin-top: 2px; }

    .dos { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
    @media (max-width: 800px) { .dos { grid-template-columns: 1fr; } }
    .tarjeta { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: 1rem; margin-bottom: 1rem; }
    .dos .tarjeta { margin-bottom: 0; }
    .tarjeta h3 { color: var(--verde); font-size: 1rem; margin-bottom: .7rem; display: flex; gap: 8px; align-items: center; }
    .scroll { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    th { background: var(--verde); color: #fff; text-align: left; padding: 8px 9px; font-size: .7rem; text-transform: uppercase; letter-spacing: .4px; }
    td { padding: 8px 9px; border-bottom: 1px solid #eef2f6; }
    td.r, th.r { text-align: right; }
    tr.total td { font-weight: 700; background: #f8fafc; border-top: 2px solid #cbd5e1; }
    .pos { color: #15803d; } .neg { color: #b91c1c; }
    .vacio { text-align: center; color: #94a3b8; padding: 1.4rem; }

    .caja label { display: block; font-size: .72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; margin: .7rem 0 .25rem; }
    .caja input[type=number], .caja textarea { width: 100%; padding: 10px; border: 1.5px solid #cbd5e1; border-radius: 10px; font: inherit; font-size: 1rem; }
    .caja .fila { display: flex; justify-content: space-between; padding: 6px 0; font-size: .92rem; }
    .caja .fila.grande { font-size: 1.05rem; font-weight: 700; border-top: 1px dashed #cbd5e1; margin-top: 6px; padding-top: 10px; }
    .dif { font-weight: 800; }
    .aviso { border-radius: 10px; padding: .6rem .8rem; font-size: .84rem; margin-bottom: .8rem; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
    #toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: var(--verde); color: #fff; padding: 11px 20px; border-radius: 10px; font-weight: 600; display: none; z-index: 2000; }

    @media print {
        .barra-superior, .menu-navegacion, .fechas, .no-imprimir, #toast, #fe-tema-btn { display: none !important; }
        body { background: #fff; }
        .tarjeta, .kpi { box-shadow: none; border: 1px solid #ddd; }
        .contenedor { max-width: none; margin: 0; }
    }
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
        <a href="<?= base_url('cobranza') ?>" class="nav-link"><i class="fas fa-hand-holding-dollar"></i> Cobranza</a>
        <a href="<?= base_url('corte') ?>" class="nav-link activo"><i class="fas fa-cash-register"></i> Corte de caja</a>
        <a href="<?= base_url('devoluciones') ?>" class="nav-link"><i class="fas fa-rotate-left"></i> Devoluciones</a>
        <a href="<?= base_url('auditoria') ?>" class="nav-link"><i class="fas fa-clock-rotate-left"></i> Historial de cambios</a>
    </div>
</nav>

<div class="contenedor">
    <div class="titulo">
        <h2><i class="fas fa-cash-register" style="color:var(--naranja)"></i> Corte de caja · <?= esc($fechaTexto) ?><?= $fecha === $hoy ? ' (hoy)' : '' ?></h2>
        <form class="fechas" method="get" action="<?= base_url('corte') ?>">
            <a class="btn sec" href="<?= base_url('corte') . '?fecha=' . $ayer ?>" title="Día anterior"><i class="fas fa-chevron-left"></i></a>
            <input type="date" name="fecha" value="<?= esc($fecha) ?>" max="<?= esc($hoy) ?>" onchange="this.form.submit()">
            <?php if ($fecha < $hoy): ?><a class="btn sec" href="<?= base_url('corte') . '?fecha=' . $manana ?>" title="Día siguiente"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
            <button type="button" class="btn sec" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
            <a class="btn sec" href="<?= base_url('exportar/corte/pdf') . '?fecha=' . esc($fecha) ?>" target="_blank" rel="noopener" title="Descargar el corte en PDF"><i class="fas fa-file-pdf"></i> PDF</a>
            <a class="btn sec" href="<?= base_url('exportar/corte/xlsx') . '?fecha=' . esc($fecha) ?>" title="Descargar el corte en Excel"><i class="fas fa-file-excel"></i> Excel</a>
        </form>
    </div>
    <p class="sub">Se arma con los cobros y reembolsos registrados ese día (hora de la Ciudad de México).</p>

    <div class="kpis">
        <div class="kpi"><div class="t">Ventas del día</div><div class="v"><?= $m($resumen['ventas']['total']) ?></div><div class="s"><?= $resumen['ventas']['n'] ?> pedido<?= $resumen['ventas']['n'] === 1 ? '' : 's' ?> · quedó por cobrar <?= $m($resumen['ventas_credito']) ?></div></div>
        <div class="kpi azul"><div class="t">Cobrado</div><div class="v"><?= $m($resumen['cobrado']) ?></div><div class="s">Ventas, abonos y cobros del repartidor</div></div>
        <div class="kpi rojo"><div class="t">Reembolsado</div><div class="v <?= $resumen['reembolsado'] > 0 ? 'neg' : '' ?>"><?= $m($resumen['reembolsado']) ?></div><div class="s"><?= $resumen['devoluciones']['n'] ?> devolución<?= $resumen['devoluciones']['n'] === 1 ? '' : 'es' ?> del día</div></div>
        <div class="kpi ambar"><div class="t">Neto del día</div><div class="v"><?= $m($resumen['neto']) ?></div><div class="s">Créditos por cobrar en total: <?= $m($resumen['por_cobrar']) ?></div></div>
    </div>

    <div class="dos">
        <div class="tarjeta">
            <h3><i class="fas fa-wallet"></i> Por método de pago</h3>
            <div class="scroll">
            <table>
                <thead><tr><th>Método</th><th class="r">Cobrado</th><th class="r">Reembolsado</th><th class="r">Neto</th></tr></thead>
                <tbody>
                <?php foreach ($resumen['metodos'] as $clave => $x): ?>
                    <tr><td><?= esc($nombresMetodo[$clave] ?? $clave) ?></td><td class="r"><?= $m($x['cobrado']) ?></td><td class="r <?= $x['reembolsado'] > 0 ? 'neg' : '' ?>"><?= $m($x['reembolsado']) ?></td><td class="r"><strong><?= $m($x['neto']) ?></strong></td></tr>
                <?php endforeach; ?>
                <tr class="total"><td>TOTAL</td><td class="r"><?= $m($resumen['cobrado']) ?></td><td class="r"><?= $m($resumen['reembolsado']) ?></td><td class="r"><?= $m($resumen['neto']) ?></td></tr>
                </tbody>
            </table>
            </div>
            <?php if ($resumen['origenes']): ?>
                <div style="margin-top:.7rem; font-size:.8rem; color:#64748b;">
                    <?php foreach ($resumen['origenes'] as $o => $monto): ?>
                        <div style="display:flex; justify-content:space-between;"><span><?= esc($nombresOrigen[$o] ?? $o) ?></span><span><?= $m($monto) ?></span></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="tarjeta caja no-imprimir-form">
            <h3><i class="fas fa-money-bill-wave"></i> Efectivo en caja</h3>
            <?php if ($guardado): ?>
                <div class="aviso"><i class="fas fa-circle-check"></i> Ya hay un corte guardado de este día (<?= esc((new DateTime($guardado['created_at'], new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('America/Mexico_City'))->format('d/m/Y H:i')) ?>, <?= esc($guardado['usuario']) ?>). Si guardas otro, queda como el más reciente.</div>
            <?php endif; ?>
            <label for="fondo">Fondo inicial (lo que había al abrir)</label>
            <input type="number" id="fondo" min="0" step="0.01" value="<?= $guardado ? esc($guardado['fondo_inicial']) : number_format($fondoSugerido, 2, '.', '') ?>">
            <div class="fila"><span>+ Efectivo cobrado y reembolsado del día</span><strong id="efNeto"><?= $m($resumen['efectivo_neto']) ?></strong></div>
            <div class="fila grande"><span>Efectivo que debería haber</span><span id="esperado">$0.00</span></div>
            <label for="contado">Efectivo contado</label>
            <input type="number" id="contado" min="0" step="0.01" placeholder="0.00" value="<?= $guardado ? esc($guardado['efectivo_contado']) : '' ?>">
            <div class="fila grande"><span>Diferencia</span><span class="dif" id="dif">—</span></div>
            <label for="notas">Notas</label>
            <textarea id="notas" rows="2" maxlength="300" placeholder="Opcional: retiros, gastos, explicación de la diferencia..."><?= $guardado ? esc($guardado['notas']) : '' ?></textarea>
            <div class="no-imprimir" style="margin-top:.8rem;">
                <button class="btn naranja" id="btnGuardar" onclick="guardarCorte()"><i class="fas fa-floppy-disk"></i> Guardar corte</button>
            </div>
        </div>
    </div>

    <div class="tarjeta">
        <h3><i class="fas fa-list"></i> Movimientos del día <small style="color:#64748b; font-weight:500;">(<?= count($resumen['movimientos']) ?>)</small></h3>
        <div class="scroll">
        <table>
            <thead><tr><th>Hora</th><th>Pedido</th><th>Cliente</th><th>Concepto</th><th>Método</th><th>Registró</th><th class="r">Monto</th></tr></thead>
            <tbody>
            <?php if (!$resumen['movimientos']): ?>
                <tr><td colspan="7" class="vacio">No hubo cobros ni reembolsos este día.</td></tr>
            <?php endif; ?>
            <?php foreach ($resumen['movimientos'] as $x): ?>
                <tr>
                    <td><?= esc($x['hora']) ?></td>
                    <td><strong><?= esc($x['folio']) ?></strong></td>
                    <td><?= esc($x['cliente'] ?: '—') ?></td>
                    <td><?= esc($nombresOrigen[$x['origen']] ?? $x['origen']) ?></td>
                    <td><?= esc($nombresMetodo[$x['metodo']] ?? $x['metodo']) ?></td>
                    <td><?= esc($x['usuario'] ?: '—') ?></td>
                    <td class="r <?= $x['monto'] < 0 ? 'neg' : 'pos' ?>"><strong><?= $m($x['monto']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="tarjeta no-imprimir">
        <h3><i class="fas fa-clock-rotate-left"></i> Últimos cortes guardados</h3>
        <div class="scroll">
        <table>
            <thead><tr><th>Día cortado</th><th>Guardado</th><th>Usuario</th><th class="r">Fondo</th><th class="r">Esperado</th><th class="r">Contado</th><th class="r">Diferencia</th><th>Notas</th></tr></thead>
            <tbody>
            <?php if (!$historial): ?><tr><td colspan="8" class="vacio">Todavía no hay cortes guardados.</td></tr><?php endif; ?>
            <?php foreach ($historial as $c): ?>
                <tr>
                    <td><a href="<?= base_url('corte') . '?fecha=' . esc($c['fecha_corte']) ?>"><?= esc(date('d/m/Y', strtotime($c['fecha_corte']))) ?></a></td>
                    <td><?= esc($c['creado_local']) ?></td>
                    <td><?= esc($c['usuario']) ?></td>
                    <td class="r"><?= $m($c['fondo_inicial']) ?></td>
                    <td class="r"><?= $m($c['efectivo_esperado']) ?></td>
                    <td class="r"><?= $m($c['efectivo_contado']) ?></td>
                    <td class="r <?= (float) $c['diferencia'] < 0 ? 'neg' : ((float) $c['diferencia'] > 0 ? 'pos' : '') ?>"><strong><?= (float) $c['diferencia'] > 0 ? '+' : '' ?><?= $m($c['diferencia']) ?></strong></td>
                    <td><?= esc($c['notas'] ?: '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<div id="toast"></div>

<script>
const FECHA = <?= json_encode($fecha) ?>;
const EFECTIVO_NETO = <?= json_encode((float) $resumen['efectivo_neto']) ?>;
const dinero = n => (n < 0 ? '-' : '') + '$' + Math.abs(n).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function aviso(t) { const e = document.getElementById('toast'); e.textContent = t; e.style.display = 'block'; setTimeout(() => e.style.display = 'none', 2800); }

function recalcular() {
    const fondo   = parseFloat(document.getElementById('fondo').value) || 0;
    const contado = document.getElementById('contado').value;
    const esperado = Math.round((fondo + EFECTIVO_NETO) * 100) / 100;
    document.getElementById('esperado').textContent = dinero(esperado);

    const dif = document.getElementById('dif');
    if (contado === '') { dif.textContent = '—'; dif.style.color = ''; return; }
    const d = Math.round((parseFloat(contado) - esperado) * 100) / 100;
    dif.textContent = (d > 0 ? '+' : '') + dinero(d) + (d === 0 ? ' · cuadra' : (d > 0 ? ' · sobra' : ' · falta'));
    dif.style.color = d === 0 ? '#15803d' : (d > 0 ? '#b45309' : '#b91c1c');
}
document.getElementById('fondo').addEventListener('input', recalcular);
document.getElementById('contado').addEventListener('input', recalcular);
recalcular();

async function guardarCorte() {
    const contado = document.getElementById('contado').value;
    if (contado === '') { aviso('Escribe el efectivo contado.'); return; }

    const boton = document.getElementById('btnGuardar');
    boton.disabled = true;
    try {
        const r = await fetch('<?= base_url('corte/guardar') ?>', {
            method: 'POST',
            body: new URLSearchParams({
                fecha: FECHA,
                fondo: document.getElementById('fondo').value || '0',
                contado: contado,
                notas: document.getElementById('notas').value
            })
        });
        const d = await r.json();
        aviso(d.message);
        if (d.success) setTimeout(() => location.reload(), 900); else boton.disabled = false;
    } catch (e) { boton.disabled = false; aviso('Error de conexión'); }
}
</script>
</body>
</html>
