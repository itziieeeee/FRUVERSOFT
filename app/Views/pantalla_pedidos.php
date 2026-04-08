<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>FRUVER · Seguimiento de Pedidos</title>

    <!-- Fuentes e iconos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-green: #1d4a27;
            --primary-orange: #f16b1a;
            --light-green: #e8f3e6;
            --dark-green: #0f3317;
            --gray-light: #f8f9fa;
            --gray-border: #e0e0e0;
            --text-dark: #333;
            --text-light: #666;
            --white: #ffffff;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --radius-md: 12px;
            --radius-sm: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f0 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ===== HEADER ===== */
        .barra-superior {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 2rem;
            background: linear-gradient(90deg, var(--primary-green) 0%, #2a5e35 100%);
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .logo-area img {
            height: 80px;
            object-fit: contain;
        }

        .user-actions {
            display: flex;
            gap: 0.8rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-user {
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            padding: 0.5rem 1.2rem;
            background: rgba(255,255,255,0.15);
            border-radius: 50px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .btn-user:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }

        /* ===== NAVEGACIÓN CENTRADA ===== */
        .menu-navegacion {
            background: white;
            padding: 0.5rem 1.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 1px solid var(--gray-border);
            gap: 0.5rem;
            flex-wrap: wrap;
            box-shadow: var(--shadow-sm);
        }

        .nav-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.25rem;
        }

        .nav-link {
            padding: 0.9rem 1.2rem;
            color: var(--text-dark);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
            border-radius: 40px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            color: var(--primary-orange);
            background: var(--light-green);
        }

        .nav-link.activo {
            color: var(--primary-orange);
            background: rgba(241, 107, 26, 0.08);
            border-bottom: 3px solid var(--primary-orange);
        }

        /* ===== CONTENEDOR PRINCIPAL ===== */
        .pedido-header-card {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 1.8rem;
            background: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid rgba(0,0,0,0.03);
        }

        .pedido-header-card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }

        /* ===== TÍTULO Y BUSCADOR ===== */
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
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-green);
        }

        .titulo-pedido i {
            color: var(--primary-orange);
            font-size: 1.8rem;
        }

        .buscador-pedido input {
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--gray-border);
            border-radius: 50px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            width: 260px;
            transition: all 0.2s;
            background: white;
        }

        .buscador-pedido input:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(241,107,26,0.1);
        }

        /* ===== TABLA DE PEDIDOS ===== */
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
            background: var(--light-green);
            transition: 0.2s;
        }

        /* ===== SELECT DE ESTADO ===== */
        .tabla-pedidos-exitentes select {
            padding: 0.5rem 0.8rem;
            border: 1.5px solid var(--gray-border);
            border-radius: var(--radius-sm);
            font-family: 'Inter', sans-serif;
            font-size: 0.8rem;
            font-weight: 500;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tabla-pedidos-exitentes select:focus {
            outline: none;
            border-color: var(--primary-orange);
        }

        /* Opciones con colores según estado */
        .tabla-pedidos-exitentes select option[value="Pedido"] { color: #f59e0b; }
        .tabla-pedidos-exitentes select option[value="Pedido confirmado"] { color: #3b82f6; }
        .tabla-pedidos-exitentes select option[value="Pedido en transito"] { color: #8b5cf6; }
        .tabla-pedidos-exitentes select option[value="Venta confirmada"] { color: #10b981; }
        .tabla-pedidos-exitentes select option[value="Pedido pagado"] { color: #059669; }
        .tabla-pedidos-exitentes select option[value="Pedido cancelado"] { color: #ef4444; }

        /* ===== BOTÓN VER MÁS ===== */
        .btn-ver-mas {
            background: linear-gradient(105deg, var(--primary-orange), #e05a0c);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: 500;
            font-size: 0.75rem;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-ver-mas:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(241,107,26,0.3);
            filter: brightness(1.02);
        }

        /* ===== BADGE DE ESTADO (opcional para mejorar visual) ===== */
        .estado-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .pedido-header-card {
                margin: 1rem;
                padding: 1.2rem;
            }
            
            .titulo-buscador-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .buscador-pedido input {
                width: 100%;
            }
            
            .tabla-pedidos-exitentes th,
            .tabla-pedidos-exitentes td {
                padding: 0.6rem 0.5rem;
                font-size: 0.75rem;
            }
            
            .tabla-pedidos-exitentes select {
                padding: 0.3rem 0.5rem;
                font-size: 0.7rem;
            }
            
            .btn-ver-mas {
                padding: 0.4rem 0.8rem;
                font-size: 0.7rem;
            }
            
            .nav-link {
                padding: 0.5rem 1rem;
                font-size: 0.75rem;
            }
            
            .barra-superior {
                flex-direction: column;
                text-align: center;
            }
            
            .user-actions {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .titulo-pedido {
                font-size: 1.2rem;
            }
            
            .titulo-pedido i {
                font-size: 1.4rem;
            }
            
            .tabla-pedidos-exitentes {
                font-size: 0.7rem;
            }
        }
        
        /* Utilidades */
        .text-center { text-align: center; }
        .mt-4 { margin-top: 1rem; }
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
        <a href="<?= base_url('pantalla_ventas') ?>" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="<?= base_url('pantalla_pedidos') ?>" class="nav-link activo"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="<?= base_url('pantalla_inventario') ?>" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="<?= base_url('pantalla_clientes') ?>" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="<?= base_url('pantalla_repartidores') ?>" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="<?= base_url('pantalla_productos') ?>" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </div>
</nav>

<div class="pedido-header-card">
    <div class="titulo-buscador-row">
        <div class="titulo-pedido">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>Visualizacion de status de pedidos totales</span>
        </div>

        <div class="buscador-pedido">
            <input type="text" id="inputBuscarPedido" placeholder=" Buscar pedido por ID, cliente o estado...">
        </div>
    </div>

    <div class="tabla-container">
        <table class="tabla-pedidos-exitentes">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="tablaPedidosBody">
                <?php if(isset($sp) && !empty($sp)): ?>
                    <?php foreach($sp as $status): ?>
                    <tr>
                        <td><strong>#<?= $status['id']; ?></strong></td>
                        <td><?= $status['fecha']; ?></td>
                        <td><?= $status['nombre_cliente'] ?? 'Sin cliente'; ?></td>
                        <td><strong style="color: var(--primary-orange);">$<?= number_format($status['total'], 2); ?></strong></td>
                        <td>
                            <select onchange="cambiarEstado(<?= $status['id']; ?>, this.value)" class="estado-select">
                                <option value="Pedido" <?= $status['estado_actual']=='Pedido'?'selected':'' ?>> Pedido</option>
                                <option value="Pedido confirmado" <?= $status['estado_actual']=='Pedido confirmado'?'selected':'' ?>> Confirmado</option>
                                <option value="Pedido en transito" <?= $status['estado_actual']=='Pedido en transito'?'selected':'' ?>>En tránsito</option>
                                <option value="Venta confirmada" <?= $status['estado_actual']=='Venta confirmada'?'selected':'' ?>>Entregado</option>
                                <option value="Pedido pagado" <?= $status['estado_actual']=='Pedido pagado'?'selected':'' ?>> Pagado</option>
                                <option value="Pedido cancelado" <?= $status['estado_actual']=='Pedido cancelado'?'selected':'' ?>> Cancelado</option>
                            </select>
                        </td>
                        <td>
                            <button class="btn-ver-mas" onclick="verDetallePedido(<?= $status['id']; ?>)">
                                <i class="fas fa-eye"></i> Ver más
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 3rem;">
                            <i class="fas fa-inbox" style="font-size: 2rem; color: var(--gray-border);"></i>
                            <p style="margin-top: 0.5rem; color: var(--text-light);">No hay pedidos registrados</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Función para cambiar estado del pedido
function cambiarEstado(idPedido, nuevoEstado) {
    fetch("<?= base_url('status/cambiar') ?>", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `id=${idPedido}&estado=${nuevoEstado}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            // Mostrar notificación sutil
            mostrarNotificacion(`Pedido #${idPedido} actualizado a: ${nuevoEstado}`, 'success');
        } else {
            mostrarNotificacion('Error al actualizar el estado', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error de conexión', 'error');
    });
}

// Función para ver detalles del pedido
function verDetallePedido(idPedido) {
    // Puedes redirigir a una página de detalles o abrir un modal
    window.location.href = "<?= base_url('pedido/detalle/') ?>" + idPedido;
}

// Función para mostrar notificaciones
function mostrarNotificacion(mensaje, tipo) {
    // Crear elemento de notificación
    const notif = document.createElement('div');
    notif.textContent = mensaje;
    notif.style.position = 'fixed';
    notif.style.bottom = '20px';
    notif.style.right = '20px';
    notif.style.padding = '12px 20px';
    notif.style.borderRadius = '8px';
    notif.style.fontSize = '0.85rem';
    notif.style.fontWeight = '500';
    notif.style.zIndex = '1000';
    notif.style.animation = 'slideIn 0.3s ease';
    
    if(tipo === 'success') {
        notif.style.backgroundColor = '#10b981';
        notif.style.color = 'white';
    } else {
        notif.style.backgroundColor = '#ef4444';
        notif.style.color = 'white';
    }
    
    document.body.appendChild(notif);
    
    setTimeout(() => {
        notif.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notif.remove(), 300);
    }, 3000);
}

// Función de búsqueda en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const inputBuscar = document.getElementById('inputBuscarPedido');
    if(inputBuscar) {
        inputBuscar.addEventListener('keyup', function() {
            const busqueda = this.value.toLowerCase();
            const filas = document.querySelectorAll('#tablaPedidosBody tr');
            
            filas.forEach(fila => {
                // Saltar fila de "no hay pedidos"
                if(fila.querySelector('td[colspan]')) return;
                
                const texto = fila.innerText.toLowerCase();
                if(texto.includes(busqueda)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
});

// Estilos para animaciones
const styleAnim = document.createElement('style');
styleAnim.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(styleAnim);
</script>

</body>
</html>