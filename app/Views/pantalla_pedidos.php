<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>FRUVER · Seguimiento de Pedidos</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-green: #1d4a27;
            --primary-orange: #f16b1a;
            --light-green: #e8f3e6;
            --gray-border: #e0e0e0;
            --text-dark: #333;
            --white: #ffffff;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --radius-md: 12px;
            --radius-sm: 8px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f0 100%);
            min-height: 100vh;
        }

        /* HEADER */
        .barra-superior {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 2rem;
            background: linear-gradient(90deg, var(--primary-green) 0%, #2a5e35 100%);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .logo-area img { height: 80px; object-fit: contain; }

        .user-actions { display: flex; gap: 0.8rem; align-items: center; flex-wrap: wrap; }

        .btn-user {
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            padding: 0.5rem 1.2rem;
            background: rgba(255,255,255,0.15);
            border-radius: 50px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .btn-user:hover { background: rgba(255,255,255,0.3); transform: translateY(-1px); }

        /* NAV */
        .menu-navegacion {
            background: white;
            padding: 0.5rem 1.5rem;
            display: flex;
            justify-content: center;
            border-bottom: 1px solid var(--gray-border);
            flex-wrap: wrap;
            box-shadow: var(--shadow-sm);
        }

        .nav-links { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.25rem; }

        .nav-link {
            padding: 0.9rem 1.2rem;
            color: var(--text-dark);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
            border-radius: 40px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover { color: var(--primary-orange); background: var(--light-green); }
        .nav-link.activo {
            color: var(--primary-orange);
            background: rgba(241, 107, 26, 0.08);
            border-bottom: 3px solid var(--primary-orange);
        }

        /* CONTENEDOR */
        .pedido-header-card {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 1.8rem;
            background: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
        }

        /* TÍTULO */
        .titulo-buscador-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.8rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light-green);
        }

        .titulo-pedido {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-green);
        }

        .titulo-pedido i { color: var(--primary-orange); font-size: 1.6rem; }

        /* BUSCADOR + ORDENAR */
        .controles-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            padding: 1rem 1.2rem;
            background: var(--light-green);
            border-radius: var(--radius-sm);
        }

        .controles-row input {
            padding: 9px 16px;
            border-radius: 50px;
            border: 1.5px solid var(--gray-border);
            font-family: 'Inter', sans-serif;
            font-size: 0.88rem;
            width: 250px;
            transition: all 0.2s;
            background: white;
        }

        .controles-row input:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(241,107,26,0.1);
        }

        .btn-orden {
            padding: 8px 16px;
            border-radius: 50px;
            border: 1.5px solid;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-orden.az { border-color: var(--primary-green); background: white; color: var(--primary-green); }
        .btn-orden.za { border-color: var(--primary-orange); background: white; color: var(--primary-orange); }
        .btn-orden:hover { transform: translateY(-1px); opacity: 0.85; }

        /* TABLA */
        .tabla-pedidos-exitentes {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            border-radius: var(--radius-sm);
            overflow: hidden;
        }

        .tabla-pedidos-exitentes thead tr {
            background: var(--primary-green);
            color: white;
        }

        .tabla-pedidos-exitentes th {
            padding: 1rem 0.8rem;
            text-align: left;
            font-weight: 600;
        }

        .tabla-pedidos-exitentes td {
            padding: 0.9rem 0.8rem;
            border-bottom: 1px solid var(--gray-border);
            vertical-align: middle;
        }

        .tabla-pedidos-exitentes tbody tr:hover {
            background: #fafff9;
            transition: 0.2s;
        }

        /* SELECT ESTADO */
        .estado-select {
            padding: 0.45rem 0.8rem;
            border: 1.5px solid var(--gray-border);
            border-radius: var(--radius-sm);
            font-family: 'Inter', sans-serif;
            font-size: 0.8rem;
            font-weight: 500;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
            min-width: 180px;
        }

        .estado-select:focus { outline: none; border-color: var(--primary-orange); }

        /* BOTONES ACCIÓN */
        .acciones-fila { display: flex; gap: 6px; align-items: center; }

        .btn-actualizar {
            background: linear-gradient(105deg, var(--primary-green), #2a5e35);
            color: white;
            border: none;
            padding: 7px 14px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-actualizar:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(29,74,39,0.3);
        }

        .btn-eliminar {
            background: linear-gradient(105deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 7px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }

        .btn-eliminar:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(239,68,68,0.3);
        }

        /* VACÍO */
        .fila-vacia td {
            text-align: center;
            padding: 2rem;
            color: #999;
            font-style: italic;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .pedido-header-card { margin: 1rem; padding: 1.2rem; }
            .controles-row input { width: 100%; }
            .tabla-pedidos-exitentes th,
            .tabla-pedidos-exitentes td { padding: 0.6rem 0.4rem; font-size: 0.75rem; }
            .estado-select { min-width: 140px; font-size: 0.72rem; }
            .acciones-fila { flex-direction: column; gap: 4px; }
            .barra-superior { flex-direction: column; text-align: center; }
        }

        /* Notificación */
        @keyframes slideInRight {
            from { transform: translateX(110%); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0);    opacity: 1; }
            to   { transform: translateX(110%); opacity: 0; }
        }
    </style>
</head>
<body>

<div class="barra-superior">
    <div class="logo-area">
        <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo FRUVER">
    </div>
    <div class="user-actions">
        <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> <span>Admin</span></a>
        <a href="#" class="btn-user"><i class="fas fa-bell"></i> <span>Notificaciones</span></a>
        <a href="<?= base_url('menusolo') ?>" class="btn-user"><i class="fas fa-sign-out-alt"></i> <span>Regresar</span></a>
    </div>
</div>

<nav class="menu-navegacion">
    <div class="nav-links">
        <a href="<?= base_url('pantalla_ventas') ?>"      class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="<?= base_url('pantalla_pedidos') ?>"     class="nav-link activo"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="<?= base_url('pantalla_inventario') ?>"  class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="<?= base_url('pantalla_clientes') ?>"    class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="<?= base_url('pantalla_repartidores') ?>" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="<?= base_url('pantalla_productos') ?>"   class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </div>
</nav>

<div class="pedido-header-card">

    <div class="titulo-buscador-row">
        <div class="titulo-pedido">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>Seguimiento de Pedidos</span>
        </div>
    </div>

    <!-- Buscador + ordenar -->
    <div class="controles-row">
        <input type="text" id="inputBuscarPedido" placeholder=" Buscar por cliente o folio...">
        <button class="btn-orden az" onclick="ordenarAZ()">
            <i class="fas fa-sort-alpha-down"></i> A–Z
        </button>
        <button class="btn-orden za" onclick="ordenarZA()">
            <i class="fas fa-sort-alpha-up"></i> Z–A
        </button>
    </div>

    <div class="tabla-container" style="overflow-x:auto;">
        <table class="tabla-pedidos-exitentes">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaPedidosBody">

            <?php if(isset($sp) && !empty($sp)): ?>
                <?php foreach($sp as $pedido): ?>
                <tr id="fila-<?= $pedido['id'] ?>">

                    <td><strong>#<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>

                    <td><?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></td>

                    <td class="clase-nombre"><?= htmlspecialchars($pedido['nombre_cliente'] ?? 'Sin nombre') ?></td>

                    <td><strong style="color:var(--primary-orange);">$<?= number_format($pedido['total'], 2) ?></strong></td>

                    <td>
                        <select class="estado-select" id="estado-<?= $pedido['id'] ?>">
                            <?php
                            $estados = [
                                'Pedido'            => 'Pedido',
                                'Pedido confirmado' => 'Confirmado',
                                'Pedido en transito'=> 'En tránsito',
                                'Venta confirmada'  => 'Entregado',
                                'Pedido a credito'  => 'A crédito',
                                'Pedido pagado'     => 'Pagado',
                                'Pedido cancelado'  => 'Cancelado',
                            ];
                            foreach($estados as $valor => $etiqueta): ?>
                                <option value="<?= $valor ?>"
                                    <?= $pedido['estado_actual'] === $valor ? 'selected' : '' ?>>
                                    <?= $etiqueta ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td>
                        <div class="acciones-fila">
                            <button class="btn-actualizar"
                                    onclick="cambiarEstado(<?= $pedido['id'] ?>)">
                                <i class="fas fa-sync-alt"></i> Actualizar
                            </button>
                            <button class="btn-eliminar"
                                    onclick="eliminarPedido(<?= $pedido['id'] ?>)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>

                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr class="fila-vacia">
                    <td colspan="6">No hay pedidos registrados</td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

<script>
// ── CAMBIAR ESTADO ──────────────────────────────────────────────
function cambiarEstado(idPedido) {
    const nuevoEstado = document.getElementById('estado-' + idPedido).value;

    fetch("<?= base_url('status/cambiar') ?>", {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${idPedido}&estado=${encodeURIComponent(nuevoEstado)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            toast('Pedido actualizado a: ' + nuevoEstado, 'success');
        } else {
            toast('Error al actualizar el estado', 'error');
        }
    })
    .catch(() => toast('Error de conexión', 'error'));
}

// ── ELIMINAR PEDIDO ─────────────────────────────────────────────
function eliminarPedido(idPedido) {
    Swal.fire({
        title: '¿Eliminar pedido #' + String(idPedido).padStart(5, '0') + '?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch("<?= base_url('pedido/eliminar') ?>/" + idPedido, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Eliminar fila de la tabla sin recargar
                const fila = document.getElementById('fila-' + idPedido);
                if (fila) {
                    fila.style.transition = 'opacity 0.3s';
                    fila.style.opacity = '0';
                    setTimeout(() => fila.remove(), 300);
                }
                toast('Pedido eliminado correctamente', 'success');
            } else {
                toast(data.message || 'Error al eliminar', 'error');
            }
        })
        .catch(() => toast('Error de conexión', 'error'));
    });
}

// ── BUSCADOR EN TIEMPO REAL ─────────────────────────────────────
document.getElementById('inputBuscarPedido').addEventListener('keyup', function() {
    const busqueda = this.value.toLowerCase();
    document.querySelectorAll('#tablaPedidosBody tr').forEach(fila => {
        if (fila.querySelector('td[colspan]')) return;
        fila.style.display = fila.innerText.toLowerCase().includes(busqueda) ? '' : 'none';
    });
});

// ── ORDENAR ─────────────────────────────────────────────────────
function ordenarAZ() { ordenarTabla(1); }
function ordenarZA() { ordenarTabla(-1); }

function ordenarTabla(dir) {
    const tbody = document.getElementById('tablaPedidosBody');
    const filas = Array.from(tbody.querySelectorAll('tr')).filter(f => !f.querySelector('td[colspan]'));
    filas.sort((a, b) => {
        const na = (a.querySelector('.clase-nombre')?.innerText || '').trim().toLowerCase();
        const nb = (b.querySelector('.clase-nombre')?.innerText || '').trim().toLowerCase();
        return dir * na.localeCompare(nb, 'es');
    });
    filas.forEach(f => tbody.appendChild(f));
}

// ── TOAST ────────────────────────────────────────────────────────
function toast(msg, tipo) {
    const t = document.createElement('div');
    t.textContent = msg;
    Object.assign(t.style, {
        position: 'fixed', bottom: '24px', right: '24px',
        padding: '12px 20px', borderRadius: '10px',
        fontSize: '0.85rem', fontWeight: '600',
        color: 'white', zIndex: '9999',
        background: tipo === 'success' ? '#10b981' : '#ef4444',
        animation: 'slideInRight 0.3s ease'
    });
    document.body.appendChild(t);
    setTimeout(() => {
        t.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => t.remove(), 300);
    }, 3000);
}
</script>
</body>
</html>