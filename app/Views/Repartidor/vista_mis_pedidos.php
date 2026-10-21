<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVER · Mis Rutas y Pedidos</title>
    <link rel="stylesheet" href="<?= base_url('css/estiloparapedidos.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .tabla-pedidos-exitentes th { background: #1d4a27; color: white; padding: 12px; }
        .btn-entregar { background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.3s; }
        .btn-entregar:hover { background: #059669; transform: scale(1.05); }
        .direccion-text { font-size: 0.85rem; color: #555; background: #f3f4f6; padding: 6px; border-radius: 6px; margin-top: 4px; }
    </style>
</head>
<body>

<div class="barra-superior">
    <div class="logo-area">
        <img src="<?= base_url('img/LOGO1.png') ?>" alt="FRUVER" style="height: 60px;">
    </div>
    <div class="user-actions">
        <a href="<?= base_url('repartidor/panel') ?>" class="btn-user"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
    </div>
</div>

<nav class="menu-navegacion" style="background: white; padding: 0.8rem; display: flex; justify-content: center; gap: 1rem; border-bottom: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
    <a href="<?= base_url('repartidor/panel') ?>" class="nav-link" style="text-decoration: none; color: #333; font-weight:600;"><i class="fas fa-home"></i> Inicio</a>
    <a href="<?= base_url('repartidor/pedidos') ?>" class="nav-link" style="text-decoration: none; color: #f16b1a; font-weight:600;"><i class="fas fa-truck"></i> Mis Pedidos</a>
    <a href="<?= base_url('repartidor/inventario') ?>" class="nav-link" style="text-decoration: none; color: #333; font-weight:600;"><i class="fas fa-boxes"></i> Inventario</a>
</nav>

<div class="pedido-header-card" style="margin: 2rem auto; max-width: 1200px; padding: 2rem; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
    
    <?php if(session()->getFlashdata('mensaje')): ?>
        <div style="background:#10b981; color:white; padding:12px; border-radius:8px; margin-bottom:1rem; text-align:center; font-weight:bold;">
            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('mensaje') ?>
        </div>
    <?php endif; ?>

    <h2 style="color:#1d4a27; margin-bottom:1.5rem;"><i class="fas fa-route"></i> Mi Ruta de Entregas</h2>

    <div class="tabla-container" style="overflow-x: auto;">
        <table class="tabla-pedidos-exitentes" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Cliente y Dirección</th>
                    <th>Total a Cobrar</th>
                    <th>Estado de Pago</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php if(isset($pedidos) && !empty($pedidos)): ?>
                <?php foreach($pedidos as $pedido): 
                    $dir = $pedido['calle'] ? "Calle {$pedido['calle']} #{$pedido['numero']}, {$pedido['colonia']}" : 'Sin dirección registrada';
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; font-weight:bold; font-size:1.1rem;">#<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td style="padding: 12px;">
                        <strong style="color:#1d4a27;"><i class="fas fa-user"></i> <?= htmlspecialchars($pedido['nombre'].' '.$pedido['apellido_paterno']) ?></strong>
                        <div class="direccion-text"><i class="fas fa-map-marker-alt" style="color:#f16b1a;"></i> <?= $dir ?></div>
                    </td>
                    <td style="padding: 12px; font-weight:bold; color:#f16b1a; font-size:1.1rem;">$<?= number_format($pedido['total'], 2) ?></td>
                    <td style="padding: 12px;">
                        <?php if($pedido['tipo_pago'] == 'credito'): ?>
                            <span style="background:#fef3c7; color:#d97706; padding:4px 8px; border-radius:4px; font-weight:bold;"><i class="fas fa-clock"></i> Crédito</span>
                        <?php else: ?>
                            <span style="background:#d1fae5; color:#059669; padding:4px 8px; border-radius:4px; font-weight:bold;"><i class="fas fa-check"></i> Pagado</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px;">
                        <form action="<?= base_url('repartidor/marcar_entregado') ?>" method="POST" onsubmit="return confirm('¿Confirmas que este pedido ya fue entregado al cliente?');">
                            <input type="hidden" name="id_pedido" value="<?= $pedido['id'] ?>">
                            <button type="submit" class="btn-entregar">
                                <i class="fas fa-check-double"></i> Entregado
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; padding:2rem; color:#666;">No tienes pedidos pendientes de entrega en este momento.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>