<?php
/** app/Views/entrega_evidencia.php · Recibe: $e (salida_pedido + datos), $folio, $local, $metros */
$m = fn($v) => '$' . number_format((float) $v, 2);
$metodos = ['efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'tarjeta' => 'Tarjeta'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FRUVER · Entrega <?= esc($folio) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root { --verde: #1d4a27; --naranja: #f16e1f; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', 'Segoe UI', sans-serif; background: #f5f7fa; color: #1e293b; padding-bottom: 40px; }
    .barra { background: linear-gradient(90deg, var(--verde), #2a5e35); color: #fff; padding: .9rem 1.4rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .barra a { color: #fff; text-decoration: none; background: rgba(255,255,255,.15); padding: .45rem 1.1rem; border-radius: 50px; font-size: .85rem; }
    .cont { max-width: 860px; margin: 1.2rem auto; padding: 0 1rem; }
    h2 { color: var(--verde); margin-bottom: .2rem; }
    .tarjeta { background: #fff; border-radius: 14px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: 1rem 1.2rem; margin-bottom: 1rem; }
    .datos { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .8rem; }
    .dato .t { font-size: .7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .4px; }
    .dato .v { font-weight: 600; margin-top: 2px; }
    .imgs { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; }
    .imgs figure { background: #f8fafc; border-radius: 12px; padding: .6rem; text-align: center; }
    .imgs img { max-width: 100%; max-height: 420px; border-radius: 8px; background: #fff; }
    .imgs figcaption { font-size: .78rem; color: #64748b; margin-top: .4rem; font-weight: 600; }
    .aviso { background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; border-radius: 10px; padding: .7rem .9rem; font-size: .88rem; }
    a.link { color: var(--verde); font-weight: 600; }
</style>
</head>
<body>
<div class="barra">
    <strong><i class="fas fa-camera"></i> Evidencia de entrega · <?= esc($folio) ?></strong>
    <a href="<?= base_url('pantalla_pedidos') ?>"><i class="fas fa-arrow-left"></i> Pedidos</a>
</div>
<div class="cont">
    <div class="tarjeta">
        <div class="datos">
            <div class="dato"><div class="t">Cliente</div><div class="v"><?= esc($e['cliente'] ?: 'Público general') ?></div></div>
            <div class="dato"><div class="t">Repartidor</div><div class="v"><?= esc($e['repartidor'] ?: '—') ?></div></div>
            <div class="dato"><div class="t">Entregado</div><div class="v"><?= esc($local ?: '—') ?></div></div>
            <div class="dato"><div class="t">Dirección</div><div class="v"><?= esc($e['direccion'] ?: '—') ?></div></div>
            <div class="dato"><div class="t">Cobro en la entrega</div>
                <div class="v"><?= $e['monto_cobrado'] !== null ? $m($e['monto_cobrado']) . ' · ' . esc($metodos[$e['metodo_cobro']] ?? $e['metodo_cobro']) : 'No cobró nada' ?></div></div>
            <div class="dato"><div class="t">Pedido</div><div class="v"><?= $m($e['total']) ?> · pagado <?= $m($e['monto_pagado']) ?></div></div>
            <?php if ($e['entrega_lat'] !== null): ?>
                <div class="dato"><div class="t">Dónde se marcó</div>
                    <div class="v"><a class="link" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?= esc($e['entrega_lat']) ?>,<?= esc($e['entrega_lng']) ?>">Ver en el mapa</a>
                    <?php if ($metros !== null): ?><small style="color:#64748b;"> · a <?= $metros >= 1000 ? number_format($metros / 1000, 1) . ' km' : $metros . ' m' ?> del domicilio</small><?php endif; ?></div></div>
            <?php endif; ?>
        </div>
        <?php if (!empty($e['nota'])): ?><p style="margin-top:.8rem;"><strong>Nota:</strong> <?= esc($e['nota']) ?></p><?php endif; ?>
    </div>

    <?php if (!empty($e['foto_entrega']) || !empty($e['firma_entrega'])): ?>
        <div class="tarjeta">
            <div class="imgs">
                <?php if (!empty($e['foto_entrega'])): ?>
                    <figure><a target="_blank" href="<?= base_url('entrega/archivo/' . $e['foto_entrega']) ?>"><img src="<?= base_url('entrega/archivo/' . $e['foto_entrega']) ?>" alt="Foto de la entrega"></a><figcaption>Foto de la entrega</figcaption></figure>
                <?php endif; ?>
                <?php if (!empty($e['firma_entrega'])): ?>
                    <figure><a target="_blank" href="<?= base_url('entrega/archivo/' . $e['firma_entrega']) ?>"><img src="<?= base_url('entrega/archivo/' . $e['firma_entrega']) ?>" alt="Firma del cliente"></a><figcaption>Firma del cliente</figcaption></figure>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="aviso"><i class="fas fa-triangle-exclamation"></i> El repartidor entregó sin foto ni firma. Revisa la nota.</div>
    <?php endif; ?>
</div>
</body>
</html>
