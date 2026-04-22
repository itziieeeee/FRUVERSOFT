<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVER · Gestión de Mermas</title>
    <link rel="stylesheet" href="<?= base_url('css/repartidores.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .form-merma-inline {
            display: grid;
            grid-template-columns: 2fr 1fr 2fr 1fr;
            gap: 15px;
            align-items: end;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-weight: 600; color: #444; font-size: 0.9rem; }
        .form-group input, .form-group select { 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            outline: none;
        }
        .badge-cantidad {
            background: #fdeaea;
            color: #c0392b;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: bold;
        }
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
        }
        .chart-container h3 {
            text-align: center;
            color: #333;
            margin-bottom: 15px;
            font-size: 1rem;
        }
    </style>
</head>
<body>

<header>
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo">
        </div>
        <div class="user-actions">
            <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> Admin</a>
            <a href="#" class="btn-user"><i class="fas fa-bell"></i> Notificaciones</a>
            <a href="<?= base_url('menusolo') ?>" class="btn-user"><i class="fas fa-sign-out-alt"></i> Regresar</a>
        </div>
    </div>
</header>

<nav class="menu-navegacion">
    <div class="nav-links">
        <a href="<?= base_url('pantalla_ventas') ?>" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="<?= base_url('pantalla_pedidos') ?>" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="<?= base_url('inventario') ?>" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="<?= base_url('mermas') ?>" class="nav-link activo"><i class="fas fa-trash-can"></i> Mermas</a>
        <a href="<?= base_url('pantalla_clientes') ?>" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="<?= base_url('pantalla_repartidores') ?>" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="<?= base_url('pantalla_productos') ?>" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </div>
</nav>

<div class="main-container">
    <div class="header-acciones">
        <div class="titulo-seccion">
            <i class="fas fa-trash-can"></i>
            <span>Registro de Mermas</span>
        </div>
    </div>

    <form action="<?= base_url('merma/guardar') ?>" method="POST" class="form-merma-inline">
        <div class="form-group">
            <label>Seleccionar Producto</label>
            <select name="id_producto" id="select_producto" required>
                <option value="">Buscar producto...</option>
                <?php foreach ($productos_merma as $pm): ?>
                    <option value="<?= $pm['id_p'] ?>">
                       #<?= $pm['id_p'] ?> - <?= $pm['nombre'] ?> (Disponible: <?= $pm['e_total'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Cantidad</label>
            <input type="number" name="cantidad" min="1" step="1" placeholder="0" required>
        </div>

        <div class="form-group">
            <label>Motivo / Razón</label>
            <input type="text" name="motivo" placeholder="Ej. Producto golpeado" required>
        </div>

        <button type="submit" class="btn-registrar" style="height: 42px;">
            <i class="fas fa-save"></i> Registrar Merma
        </button>
    </form>

    
    <div class="titulo-seccion" style="margin-bottom: 15px; font-size: 1.1rem;">
        <i class="fas fa-history"></i>
        <span>Historial de Mermas Recientes</span>
    </div>

    <div class="tabla-card">
        <table>
            <thead>
                <tr>
                    <th>Fecha Registro</th>
                    <th>Producto</th>
                    <th>Cantidad Mermada</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($historial_mermas)): ?>
                    <?php foreach($historial_mermas as $hm): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($hm['fecha'])) ?></td>
                        <td><strong><?= $hm['nombre'] ?></strong></td>
                        <td><span class="badge-cantidad"><?= $hm['cantidad'] ?></span></td>
                        <td><?= $hm['motivo'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding:2rem;">
                            No hay mermas registradas el día de hoy.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <?php if (!empty($grafica_labels)): ?>
    <div class="chart-container">
        <h3>Grafico de control</h3>
        <canvas id="graficaEntradaMerma"></canvas>
    </div>
    <?php endif; ?>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        $('#select_producto').select2();
    });

    <?php if (!empty($grafica_labels)): ?>
    const labels   = <?= json_encode($grafica_labels) ?>;
    const entradas = <?= json_encode($grafica_entradas) ?>;
    const mermas   = <?= json_encode($grafica_mermas) ?>;

    const ctx = document.getElementById('graficaEntradaMerma').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Entradas',
                    data: entradas,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Merma',
                    data: mermas,
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: {
                    display: true,
                    text: 'Comparativo de entradas con merma'
                }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Cantidad' } },
                x: { title: { display: true, text: '' } }
            }
        }
    });
    <?php endif; ?>
</script>

</body>
</html>