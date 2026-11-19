<?php
/** app/Views/devoluciones.php · Recibe: $devoluciones */
$m = fn($v) => '$' . number_format((float) $v, 2);
$totalDev = array_sum(array_column($devoluciones, 'total'));
$totalReem = array_sum(array_column($devoluciones, 'reembolso'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FRUVER · Devoluciones</title>
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
    .contenedor { max-width: 1200px; margin: 1.4rem auto; padding: 0 1rem; }
    h2 { color: var(--verde); font-size: 1.4rem; margin-bottom: .3rem; }
    .sub { color: #64748b; font-size: .88rem; margin-bottom: 1rem; }
    .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .8rem; margin-bottom: 1rem; }
    .kpi { background: #fff; border-radius: 14px; padding: .9rem 1rem; box-shadow: 0 2px 8px rgba(0,0,0,.07); border-left: 5px solid var(--verde); }
    .kpi.rojo { border-left-color: #dc2626; }
    .kpi .t { font-size: .74rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; }
    .kpi .v { font-size: 1.5rem; font-weight: 700; margin-top: 3px; }
    .busca { display: flex; align-items: center; gap: 8px; background: #fff; border-radius: 50px; padding: 0 14px; box-shadow: 0 2px 6px rgba(0,0,0,.06); margin-bottom: 1rem; max-width: 420px; }
    .busca input { flex: 1; border: 0; outline: none; padding: 10px 0; font: inherit; font-size: .9rem; background: transparent; }
    .tarjeta { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: .6rem; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .85rem; min-width: 820px; }
    th { background: var(--verde); color: #fff; text-align: left; padding: 9px 10px; font-size: .7rem; text-transform: uppercase; letter-spacing: .4px; }
    td { padding: 9px 10px; border-bottom: 1px solid #eef2f6; vertical-align: top; }
    td.r, th.r { text-align: right; }
    .vacio { text-align: center; color: #94a3b8; padding: 2rem; }
    .tag { font-size: .68rem; font-weight: 700; padding: 3px 9px; border-radius: 50px; background: #e0f2fe; color: #0369a1; white-space: nowrap; }
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
        <a href="<?= base_url('corte') ?>" class="nav-link"><i class="fas fa-cash-register"></i> Corte de caja</a>
        <a href="<?= base_url('devoluciones') ?>" class="nav-link activo"><i class="fas fa-rotate-left"></i> Devoluciones</a>
        <a href="<?= base_url('auditoria') ?>" class="nav-link"><i class="fas fa-clock-rotate-left"></i> Historial de cambios</a>
    </div>
</nav>

<div class="contenedor">
    <h2><i class="fas fa-rotate-left" style="color:var(--naranja)"></i> Devoluciones de clientes</h2>
    <p class="sub">Para registrar una devolución abre el pedido en <a href="<?= base_url('pantalla_pedidos') ?>">Pedidos</a> y pulsa "Devolución".</p>

    <div class="kpis">
        <div class="kpi"><div class="t">Devoluciones registradas</div><div class="v"><?= count($devoluciones) ?></div></div>
        <div class="kpi rojo"><div class="t">Valor devuelto</div><div class="v"><?= $m($totalDev) ?></div></div>
        <div class="kpi"><div class="t">Reembolsado en dinero</div><div class="v"><?= $m($totalReem) ?></div></div>
    </div>

    <div class="busca"><i class="fas fa-search" style="color:#94a3b8"></i><input type="text" id="busc" placeholder="Buscar folio, cliente o producto..." autocomplete="off"></div>

    <div class="tarjeta">
        <table>
            <thead><tr><th>Fecha</th><th>Pedido</th><th>Cliente</th><th>Productos devueltos</th><th>Motivo</th><th class="r">Valor</th><th class="r">Reembolso</th><th>Registró</th></tr></thead>
            <tbody id="cuerpo">
            <?php if (!$devoluciones): ?><tr><td colspan="8" class="vacio">Todavía no hay devoluciones.</td></tr><?php endif; ?>
            <?php foreach ($devoluciones as $d): ?>
                <tr data-texto="<?= esc(mb_strtolower($d['folio'] . ' ' . $d['cliente'] . ' ' . $d['productos'] . ' ' . $d['motivo'], 'UTF-8')) ?>">
                    <td style="white-space:nowrap;"><?= esc($d['fecha_local']) ?></td>
                    <td><strong><?= esc($d['folio']) ?></strong></td>
                    <td><?= esc($d['cliente'] ?: 'Público general') ?></td>
                    <td><?= esc($d['productos']) ?></td>
                    <td><?= esc($d['motivo'] ?: '—') ?></td>
                    <td class="r"><strong><?= $m($d['total']) ?></strong></td>
                    <td class="r"><?php if ((float) $d['reembolso'] > 0): ?><?= $m($d['reembolso']) ?> <span class="tag"><?= esc($d['metodo_reembolso']) ?></span><?php else: ?><small style="color:#64748b;">Bajó el saldo</small><?php endif; ?></td>
                    <td><?= esc($d['usuario'] ?: '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
document.getElementById('busc').addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('#cuerpo tr[data-texto]').forEach(tr => { tr.style.display = tr.dataset.texto.includes(q) ? '' : 'none'; });
});
</script>
</body>
</html>
