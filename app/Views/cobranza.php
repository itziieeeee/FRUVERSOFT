<?php
/**
 * app/Views/cobranza.php
 * Créditos por cobrar y recordatorio de pago por WhatsApp.
 * Recibe: $clientes, $resumen, $config
 */
$m = fn($v) => '$' . number_format((float) $v, 2);
$etiquetas = ['vencido' => 'Vencido', 'hoy' => 'Vence hoy', 'por_vencer' => 'Por vencer', 'al_corriente' => 'Al corriente'];
$textoDias = static function (int $d): string {
    if ($d < 0)  { return 'Venció hace ' . abs($d) . ($d === -1 ? ' día' : ' días'); }
    if ($d === 0) { return 'Vence hoy'; }
    return 'Vence en ' . $d . ($d === 1 ? ' día' : ' días');
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FRUVER · Cobranza de créditos</title>
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

    .contenedor { max-width: 1150px; margin: 1.4rem auto; padding: 0 1rem; }
    .titulo { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .6rem; margin-bottom: .4rem; }
    .titulo h2 { color: var(--verde); font-size: 1.4rem; }
    .sub { color: #64748b; font-size: .88rem; margin-bottom: 1rem; }

    .config { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; font-size: .82rem; color: #475569; background: #fff; padding: 6px 12px; border-radius: 50px; box-shadow: 0 2px 6px rgba(0,0,0,.06); }
    .config input { width: 56px; padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; text-align: center; }
    .btn { border: 0; background: var(--verde); color: #fff; padding: 8px 14px; border-radius: 10px; font-weight: 700; cursor: pointer; font-family: inherit; font-size: .82rem; display: inline-flex; align-items: center; gap: 7px; text-decoration: none; }
    .btn.wa { background: #16a34a; }
    .btn.sec { background: #e2e8f0; color: #334155; }
    .btn.peq { padding: 5px 10px; font-size: .75rem; }
    .btn:disabled, .btn.off { background: #cbd5e1; color: #64748b; cursor: not-allowed; }

    .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .8rem; margin-bottom: 1rem; }
    .kpi { background: #fff; border-radius: 14px; padding: .9rem 1rem; box-shadow: 0 2px 8px rgba(0,0,0,.07); border-left: 5px solid var(--verde); }
    .kpi.rojo { border-left-color: #dc2626; } .kpi.ambar { border-left-color: #f59e0b; } .kpi.gris { border-left-color: #94a3b8; }
    .kpi .t { font-size: .74rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; }
    .kpi .v { font-size: 1.5rem; font-weight: 700; margin-top: 3px; }

    .filtros { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; margin-bottom: 1rem; }
    .pill { padding: 8px 16px; border-radius: 50px; background: #fff; border: 2px solid transparent; color: #334155; font-weight: 600; font-size: .85rem; box-shadow: 0 2px 6px rgba(0,0,0,.06); cursor: pointer; font-family: inherit; }
    .pill.sel { border-color: var(--verde); color: var(--verde); background: var(--claro); }
    .busca { flex: 1 1 220px; display: flex; align-items: center; gap: 8px; background: #fff; border-radius: 50px; padding: 0 14px; box-shadow: 0 2px 6px rgba(0,0,0,.06); }
    .busca input { flex: 1; border: 0; outline: none; padding: 10px 0; font: inherit; font-size: .9rem; background: transparent; }

    .cliente { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: 1rem; margin-bottom: 1rem; border-left: 6px solid #94a3b8; }
    .cliente.vencido { border-left-color: #dc2626; } .cliente.hoy, .cliente.por_vencer { border-left-color: #f59e0b; } .cliente.al_corriente { border-left-color: #16a34a; }
    .cab { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: .6rem; }
    .cab .nom { font-size: 1.05rem; font-weight: 700; color: var(--verde); }
    .cab .tel { font-size: .82rem; color: #64748b; }
    .cab .debe { text-align: right; }
    .cab .debe b { font-size: 1.3rem; display: block; }
    .cab .debe small { color: #64748b; font-size: .74rem; }
    .acciones { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }

    .scroll { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .85rem; min-width: 680px; }
    th { background: #f1f5f9; color: #475569; text-align: left; padding: 7px 9px; font-size: .7rem; text-transform: uppercase; letter-spacing: .4px; }
    td { padding: 8px 9px; border-bottom: 1px solid #eef2f6; vertical-align: middle; }
    td.r, th.r { text-align: right; }
    input.fecha { padding: 4px 6px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; font-size: .8rem; }
    .tag { font-size: .68rem; font-weight: 700; padding: 3px 9px; border-radius: 50px; white-space: nowrap; }
    .tag.vencido { background: #fee2e2; color: #b91c1c; } .tag.hoy, .tag.por_vencer { background: #fef3c7; color: #b45309; } .tag.al_corriente { background: #dcfce7; color: #15803d; }
    .recordado { font-size: .72rem; color: #64748b; display: block; }
    .vacio { text-align: center; color: #94a3b8; padding: 2.5rem 1rem; background: #fff; border-radius: 14px; }

    #modalAbono { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1000; align-items: center; justify-content: center; padding: 1rem; }
    #modalAbono .caja { background: #fff; border-radius: 16px; padding: 1.3rem; width: min(380px, 100%); }
    #modalAbono h3 { color: var(--verde); margin-bottom: .6rem; }
    #modalAbono input { width: 100%; padding: 10px; border: 1.5px solid #cbd5e1; border-radius: 10px; font: inherit; font-size: 1.1rem; margin: .6rem 0; }
    #modalAbono .fila { display: flex; gap: .5rem; justify-content: flex-end; margin-top: .4rem; }
    #toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: var(--verde); color: #fff; padding: 11px 20px; border-radius: 10px; font-weight: 600; display: none; z-index: 2000; }
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
        <a href="<?= base_url('pantalla_clientes') ?>" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="<?= base_url('cobranza') ?>" class="nav-link activo"><i class="fas fa-hand-holding-dollar"></i> Cobranza</a>
        <a href="<?= base_url('costos') ?>" class="nav-link"><i class="fas fa-chart-line"></i> Costos</a>
    </div>
</nav>

<div class="contenedor">
    <div class="titulo">
        <h2><i class="fas fa-hand-holding-dollar" style="color:var(--naranja)"></i> Cobranza de créditos</h2>
        <form class="config" id="formConfig" onsubmit="return false;">
            Plazo del crédito <input type="number" id="cfgPlazo" min="1" max="365" value="<?= (int) $config['plazo'] ?>"> días
            · Avisar <input type="number" id="cfgAviso" min="0" max="60" value="<?= (int) $config['aviso'] ?>"> días antes
            <button class="btn peq" type="submit" id="btnConfig">Guardar</button>
        </form>
    </div>
    <p class="sub">Pulsa "Recordar por WhatsApp" y se abre el chat con el mensaje ya escrito. Solo falta presionar enviar.</p>

    <div class="kpis">
        <div class="kpi"><div class="t">Por cobrar</div><div class="v"><?= $m($resumen['por_cobrar']) ?></div></div>
        <div class="kpi rojo"><div class="t">Vencido</div><div class="v" style="color:#b91c1c;"><?= $m($resumen['vencido']) ?></div></div>
        <div class="kpi gris"><div class="t">Clientes con deuda</div><div class="v"><?= (int) $resumen['clientes'] ?></div></div>
        <div class="kpi ambar"><div class="t">Vencen pronto</div><div class="v"><?= (int) $resumen['por_vencer'] ?></div></div>
    </div>

    <div class="filtros">
        <button class="pill sel" data-filtro="todos">Todos</button>
        <button class="pill" data-filtro="vencido">Vencidos</button>
        <button class="pill" data-filtro="pronto">Vencen pronto</button>
        <button class="pill" data-filtro="al_corriente">Al corriente</button>
        <div class="busca"><i class="fas fa-search" style="color:#94a3b8"></i><input type="text" id="busc" placeholder="Buscar cliente o folio..." autocomplete="off"></div>
    </div>

    <div id="lista">
    <?php if (!$clientes): ?>
        <div class="vacio"><i class="fas fa-circle-check" style="font-size:2rem; color:#16a34a;"></i><br>No hay créditos pendientes de cobro.</div>
    <?php endif; ?>

    <?php foreach ($clientes as $c): ?>
        <?php $folios = implode(' ', array_column($c['pedidos'], 'folio')); ?>
        <div class="cliente <?= esc($c['estado']) ?>"
             data-estado="<?= esc($c['estado']) ?>"
             data-texto="<?= esc(mb_strtolower($c['cliente'] . ' ' . $folios, 'UTF-8')) ?>">
            <div class="cab">
                <div>
                    <div class="nom"><?= esc($c['cliente']) ?></div>
                    <div class="tel"><i class="fas fa-phone-alt"></i> <?= esc($c['tel'] ?: 'Sin teléfono') ?></div>
                </div>
                <div class="debe"><b><?= $m($c['saldo']) ?></b><small>Saldo pendiente · <?= count($c['pedidos']) ?> pedido<?= count($c['pedidos']) === 1 ? '' : 's' ?></small></div>
                <div class="acciones">
                    <span class="tag <?= esc($c['estado']) ?>"><?= esc($etiquetas[$c['estado']]) ?></span>
                    <?php if ($c['whatsapp_url']): ?>
                        <button class="btn wa" onclick='recordar(<?= json_encode($c['whatsapp_url'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) ?>, <?= json_encode($c['ids']) ?>)'>
                            <i class="fab fa-whatsapp"></i> Recordar por WhatsApp
                        </button>
                    <?php else: ?>
                        <button class="btn off" disabled title="El cliente no tiene un teléfono válido"><i class="fab fa-whatsapp"></i> Sin teléfono</button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="scroll">
            <table>
                <thead><tr><th>Folio</th><th>Fecha</th><th class="r">Total</th><th class="r">Pagado</th><th class="r">Saldo</th><th>Fecha límite</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($c['pedidos'] as $p): ?>
                    <tr>
                        <td><strong><?= esc($p['folio']) ?></strong></td>
                        <td><?= esc($p['fecha']) ?></td>
                        <td class="r"><?= $m($p['total']) ?></td>
                        <td class="r"><?= $m($p['pagado']) ?></td>
                        <td class="r"><strong><?= $m($p['saldo']) ?></strong></td>
                        <td>
                            <input type="date" class="fecha" value="<?= esc($p['vence']) ?>" data-id="<?= (int) $p['id'] ?>" title="<?= $p['personalizado'] ? 'Fecha puesta a mano' : 'Calculada con el plazo general' ?>">
                        </td>
                        <td>
                            <span class="tag <?= esc($p['estado']) ?>"><?= esc($textoDias($p['dias'])) ?></span>
                            <?php if ($p['recordado_texto']): ?><span class="recordado">Recordado <?= esc($p['recordado_texto']) ?></span><?php endif; ?>
                        </td>
                        <td><button class="btn peq" onclick="abrirAbono(<?= (int) $p['id'] ?>, '<?= esc($p['folio']) ?>', <?= json_encode($p['saldo']) ?>, <?= json_encode($p['pagado']) ?>)"><i class="fas fa-coins"></i> Abonar</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <div class="vacio" id="sinResultados" style="display:none;">Ningún cliente coincide con el filtro.</div>
</div>

<!-- Abono rápido: usa pedido/actualizar_pago, igual que la pantalla de pedidos -->
<div id="modalAbono">
    <div class="caja">
        <h3 id="abonoTitulo">Registrar abono</h3>
        <div id="abonoInfo" style="font-size:.85rem; color:#64748b;"></div>
        <input type="number" id="abonoMonto" min="0.01" step="0.01" placeholder="Monto del abono">
        <select id="abonoMetodo" style="width:100%; padding:10px; border:1.5px solid #cbd5e1; border-radius:10px; font:inherit; margin-bottom:.4rem;">
            <option value="efectivo">Efectivo</option>
            <option value="transferencia">Transferencia</option>
            <option value="tarjeta">Tarjeta</option>
        </select>
        <div class="fila">
            <button class="btn sec" onclick="cerrarAbono()">Cancelar</button>
            <button class="btn" id="btnAbono" onclick="guardarAbono()"><i class="fas fa-check"></i> Registrar abono</button>
        </div>
    </div>
</div>
<div id="toast"></div>

<script>
const BASE = '<?= base_url() ?>';
const dinero = n => '$' + Number(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function aviso(texto) {
    const t = document.getElementById('toast');
    t.textContent = texto; t.style.display = 'block';
    setTimeout(() => t.style.display = 'none', 2800);
}

async function enviar(url, datos) {
    const r = await fetch(BASE + url, { method: 'POST', body: new URLSearchParams(datos) });
    return r.json();
}

/* ---------- filtros ---------- */
let filtro = 'todos';
function aplicarFiltros() {
    const q = document.getElementById('busc').value.toLowerCase().trim();
    let visibles = 0;
    document.querySelectorAll('.cliente').forEach(c => {
        const e = c.dataset.estado;
        const okFiltro = filtro === 'todos' || e === filtro || (filtro === 'pronto' && (e === 'hoy' || e === 'por_vencer'));
        const okTexto = q === '' || c.dataset.texto.includes(q);
        const ver = okFiltro && okTexto;
        c.style.display = ver ? '' : 'none';
        if (ver) visibles++;
    });
    document.getElementById('sinResultados').style.display = (visibles === 0 && document.querySelector('.cliente')) ? 'block' : 'none';
}
document.querySelectorAll('.pill').forEach(p => p.addEventListener('click', () => {
    document.querySelectorAll('.pill').forEach(x => x.classList.remove('sel'));
    p.classList.add('sel');
    filtro = p.dataset.filtro;
    aplicarFiltros();
}));
document.getElementById('busc').addEventListener('input', aplicarFiltros);

/* ---------- recordatorio por WhatsApp ---------- */
function recordar(url, ids) {
    window.open(url, '_blank', 'noopener');             // abre el chat con el mensaje ya escrito
    enviar('cobranza/recordado', { ids: ids.join(',') })   // queda anotado para no repetirlo por descuido
        .then(() => aviso('Se abrió WhatsApp. Solo falta presionar enviar.'))
        .catch(() => {});
}

/* ---------- fecha límite ---------- */
document.querySelectorAll('input.fecha').forEach(inp => inp.addEventListener('change', async () => {
    try {
        const r = await enviar('cobranza/vencimiento', { id_pedido: inp.dataset.id, fecha: inp.value });
        aviso(r.message);
        if (r.success) setTimeout(() => location.reload(), 700);
    } catch (e) { aviso('Error de conexión'); }
}));

/* ---------- configuración ---------- */
document.getElementById('formConfig').addEventListener('submit', async () => {
    try {
        const r = await enviar('cobranza/configuracion', {
            plazo: document.getElementById('cfgPlazo').value, aviso: document.getElementById('cfgAviso').value
        });
        aviso(r.message);
        if (r.success) setTimeout(() => location.reload(), 700);
    } catch (e) { aviso('Error de conexión'); }
});

/* ---------- abono ---------- */
let abonoPedido = null, abonoPagado = 0, abonoSaldo = 0;
function abrirAbono(id, folio, saldo, pagado) {
    abonoPedido = id; abonoPagado = Number(pagado); abonoSaldo = Number(saldo);
    document.getElementById('abonoTitulo').textContent = 'Abono a ' + folio;
    document.getElementById('abonoInfo').textContent = 'Saldo pendiente: ' + dinero(saldo);
    const campo = document.getElementById('abonoMonto');
    campo.value = ''; campo.max = saldo;
    document.getElementById('modalAbono').style.display = 'flex';
    campo.focus();
}
function cerrarAbono() { document.getElementById('modalAbono').style.display = 'none'; }
async function guardarAbono() {
    const abono = parseFloat(document.getElementById('abonoMonto').value);
    if (!(abono > 0)) { aviso('Escribe el monto del abono.'); return; }
    if (abono > abonoSaldo + 0.001) { aviso('El abono no puede ser mayor al saldo.'); return; }

    const boton = document.getElementById('btnAbono');
    boton.disabled = true;
    try {
        // actualizar_pago recibe el total pagado hasta ahora (lo ya pagado + este abono)
        const r = await enviar('pedido/actualizar_pago', {
            id: abonoPedido,
            monto_pagado: (abonoPagado + abono).toFixed(2),
            metodo: document.getElementById('abonoMetodo').value
        });
        if (r.success) { aviso('Abono registrado.'); setTimeout(() => location.reload(), 700); }
        else { boton.disabled = false; aviso(r.message || 'No se pudo registrar el abono.'); }
    } catch (e) { boton.disabled = false; aviso('Error de conexión'); }
}
document.getElementById('modalAbono').addEventListener('click', e => { if (e.target.id === 'modalAbono') cerrarAbono(); });
</script>
</body>
</html>
