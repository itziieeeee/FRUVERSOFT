<?php
/**
 * app/Views/auditoria.php
 * Historial de cambios. Recibe: $filas, $total, $paginas, $pagina, $filtros, $acciones, $usuarios
 */
$url = function (array $extra = []) use ($filtros) {
    return base_url('auditoria') . '?' . http_build_query(array_filter($extra + $filtros, static fn ($v) => $v !== '' && $v !== null));
};

// Color de la etiqueta según el tipo de acción
$color = static function (string $accion): string {
    if (str_contains($accion, 'eliminar')) return 'rojo';
    if (str_starts_with($accion, 'stock') || str_starts_with($accion, 'merma')) return 'ambar';
    if (str_starts_with($accion, 'pedido.pago') || str_starts_with($accion, 'corte') || str_starts_with($accion, 'devolucion')) return 'azul';
    return 'verde';
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FRUVER · Historial de cambios</title>
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
    .filtros { display: flex; gap: .6rem; flex-wrap: wrap; align-items: flex-end; background: #fff; padding: .9rem 1rem; border-radius: 14px; box-shadow: 0 2px 8px rgba(0,0,0,.07); margin-bottom: 1rem; }
    .filtros label { display: flex; flex-direction: column; gap: 3px; font-size: .7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; }
    .filtros input, .filtros select { padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 10px; font: inherit; font-size: .85rem; min-width: 140px; }
    .filtros input[type=text] { min-width: 200px; }
    .btn { border: 0; background: var(--verde); color: #fff; padding: 9px 16px; border-radius: 10px; font-weight: 700; cursor: pointer; font-family: inherit; font-size: .85rem; text-decoration: none; display: inline-flex; gap: 6px; align-items: center; }
    .btn.sec { background: #e2e8f0; color: #334155; }
    .tarjeta { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: .6rem; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .85rem; min-width: 760px; }
    th { background: var(--verde); color: #fff; text-align: left; padding: 9px 10px; font-size: .7rem; text-transform: uppercase; letter-spacing: .4px; }
    td { padding: 9px 10px; border-bottom: 1px solid #eef2f6; vertical-align: top; }
    td.fecha { white-space: nowrap; color: #475569; }
    .tag { font-size: .68rem; font-weight: 700; padding: 3px 9px; border-radius: 50px; white-space: nowrap; display: inline-block; }
    .tag.verde { background: #dcfce7; color: #15803d; } .tag.rojo { background: #fee2e2; color: #b91c1c; }
    .tag.ambar { background: #fef3c7; color: #b45309; } .tag.azul { background: #dbeafe; color: #1d4ed8; }
    .rol { color: #94a3b8; font-size: .72rem; }
    details summary { cursor: pointer; color: var(--verde); font-size: .75rem; font-weight: 600; margin-top: 3px; }
    pre { background: #f1f5f9; padding: 8px; border-radius: 8px; font-size: .72rem; white-space: pre-wrap; word-break: break-word; margin-top: 4px; }
    .vacio { text-align: center; color: #94a3b8; padding: 2rem; }
    .paginas { display: flex; gap: .4rem; justify-content: center; align-items: center; margin-top: 1rem; font-size: .85rem; }
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
        <a href="<?= base_url('devoluciones') ?>" class="nav-link"><i class="fas fa-rotate-left"></i> Devoluciones</a>
        <a href="<?= base_url('auditoria') ?>" class="nav-link activo"><i class="fas fa-clock-rotate-left"></i> Historial de cambios</a>
    </div>
</nav>

<div class="contenedor">
    <h2><i class="fas fa-clock-rotate-left" style="color:var(--naranja)"></i> Historial de cambios</h2>
    <p class="sub">Quién cambió qué y cuándo: precios, estados de pedidos, pagos, existencias, devoluciones y más. Las horas son de la Ciudad de México.</p>

    <form class="filtros" method="get" action="<?= base_url('auditoria') ?>">
        <label>Desde <input type="date" name="desde" value="<?= esc($filtros['desde']) ?>"></label>
        <label>Hasta <input type="date" name="hasta" value="<?= esc($filtros['hasta']) ?>"></label>
        <label>Acción
            <select name="accion">
                <option value="">Todas</option>
                <?php foreach ($acciones as $clave => $texto): ?>
                    <option value="<?= esc($clave) ?>" <?= $filtros['accion'] === $clave ? 'selected' : '' ?>><?= esc($texto) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Usuario
            <select name="usuario">
                <option value="">Todos</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= esc($u) ?>" <?= $filtros['usuario'] === $u ? 'selected' : '' ?>><?= esc($u) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Buscar <input type="text" name="q" value="<?= esc($filtros['q']) ?>" placeholder="Folio, producto, cliente..."></label>
        <button class="btn" type="submit"><i class="fas fa-filter"></i> Filtrar</button>
        <a class="btn sec" href="<?= base_url('auditoria') ?>">Limpiar</a>
    </form>

    <div class="tarjeta">
        <table>
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Detalle</th></tr></thead>
            <tbody>
            <?php if (!$filas): ?>
                <tr><td colspan="4" class="vacio">No hay cambios registrados con esos filtros.</td></tr>
            <?php endif; ?>
            <?php foreach ($filas as $f): ?>
                <tr>
                    <td class="fecha"><?= esc($f['fecha_local']) ?></td>
                    <td><?= esc($f['usuario'] ?? 'Sistema') ?><?php if (!empty($f['rol'])): ?><div class="rol"><?= esc($f['rol']) ?></div><?php endif; ?></td>
                    <td><span class="tag <?= $color($f['accion']) ?>"><?= esc($f['accion_texto']) ?></span></td>
                    <td>
                        <?= esc($f['resumen']) ?>
                        <?php if (!empty($f['detalle_arr'])): ?>
                            <details><summary>Ver datos</summary><pre><?= esc(json_encode($f['detalle_arr'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre></details>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="paginas">
        <?php if ($pagina > 1): ?><a class="btn sec" href="<?= esc($url(['pagina' => $pagina - 1])) ?>">&laquo; Anterior</a><?php endif; ?>
        <span><?= $total ?> cambio<?= $total === 1 ? '' : 's' ?> · página <?= $pagina ?> de <?= $paginas ?></span>
        <?php if ($pagina < $paginas): ?><a class="btn sec" href="<?= esc($url(['pagina' => $pagina + 1])) ?>">Siguiente &raquo;</a><?php endif; ?>
    </div>
</div>

</body>
</html>
