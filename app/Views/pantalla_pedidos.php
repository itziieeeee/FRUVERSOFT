<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>FRUVER · Seguimiento de Pedidos con Progreso</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="<?= base_url('css/pedidos.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ===== ESTILO  PARA LA VENTANA MODAL ===== */
    
        .modal-credito {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.2s ease-out;
        }

        .modal-credito.active {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Contenedor principal del modal */
        .modal-contenido {
            background: #ffffff;
            border-radius: 28px;
            max-width: 620px;
            width: 92%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
        }

        /* HEADER moderno con gradiente */
        .modal-header {
            background: linear-gradient(135deg, #1e6f3f 0%, #2a8e4e 100%);
            padding: 1.2rem 1.8rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            color: white;
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-header h3 i {
            font-size: 1.3rem;
            filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1));
        }

        .close-modal {
            background: rgba(255,255,255,0.15);
            border: none;
            font-size: 1.8rem;
            line-height: 1;
            color: white;
            cursor: pointer;
            width: 36px;
            height: 36px;
            border-radius: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .close-modal:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.05);
        }

        /* Scroll interno */
        .modal-scroll {
            max-height: 70vh;
            overflow-y: auto;
            padding: 1.5rem 1.8rem;
            scrollbar-width: thin;
        }

        /* Tarjeta de info general */
        .info-general-card {
            background: #f9fafb;
            border-radius: 20px;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
            border: 1px solid #eef2f0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .grid-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.85rem 1.2rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1f2a3a;
            word-break: break-word;
        }

        .badge-estado-pago, .badge-entrega {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Tabla de productos mejorada */
        .productos-titulo {
            font-weight: 600;
            color: #1e6f3f;
            margin-bottom: 0.8rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
            border-left: 3px solid #fbbf24;
            padding-left: 12px;
        }

        .tabla-productos-modal {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }

        .tabla-productos-modal th {
            background: #eef2ef;
            color: #1e4620;
            font-weight: 600;
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid #dce4dc;
        }

        .tabla-productos-modal td {
            padding: 10px 8px;
            border-bottom: 1px solid #edf2ed;
            color: #2c3e2f;
        }

        .tabla-productos-modal tr:last-child td {
            border-bottom: none;
        }

        /* Sección de pago (crédito) */
        .pago-section {
            background: #fffbeb;
            border-radius: 20px;
            padding: 1rem 1.2rem;
            margin-top: 1.2rem;
            border: 1px solid #fde68a;
        }

        .pago-titulo {
            font-weight: 700;
            color: #b45309;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .campo-pago {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .campo-pago label {
            font-weight: 600;
            color: #2d3e2b;
            font-size: 0.85rem;
        }

        .campo-pago input {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 60px;
            font-weight: 500;
            background: white;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .campo-pago input:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245,158,11,0.1);
        }

        .restante {
            background: #fef2e0;
            padding: 10px 14px;
            border-radius: 60px;
            text-align: center;
            font-weight: 700;
            font-size: 0.9rem;
            margin: 12px 0;
            color: #92400e;
        }

        .btn-guardar-pago {
            background: linear-gradient(95deg, #1e6f3f, #2b8c4a);
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 60px;
            font-weight: 700;
            font-size: 0.9rem;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-guardar-pago:hover {
            background: linear-gradient(95deg, #155a34, #1f6e3e);
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(30,111,63,0.25);
        }

        /* Personalización scroll modal */
        .modal-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .modal-scroll::-webkit-scrollbar-track {
            background: #ecfdf3;
            border-radius: 10px;
        }
        .modal-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }

        /* Badges dinámicos */
        .badge-credito {
            background: #fef3c7;
            color: #b45309;
        }
        .badge-pagado {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-domicilio {
            background: #e0e7ff;
            color: #3730a3;
        }
        .badge-tienda {
            background: #f3f4f6;
            color: #374151;
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
            <span>Seguimiento de Pedidos</span>
        </div>
    </div>

    <div class="controles-row">
        <input type="text" id="inputBuscarPedido" placeholder=" Buscar por cliente o folio...">
        <button class="btn-orden az" onclick="ordenarAZ()"><i class="fas fa-sort-alpha-down"></i> A–Z</button>
        <button class="btn-orden za" onclick="ordenarZA()"><i class="fas fa-sort-alpha-up"></i> Z–A</button>
    </div>

    <div class="tabla-container" style="overflow-x:auto;">
        <table class="tabla-pedidos-exitentes">
            <thead>
                <tr><th style="width:30px"></th><th>#</th><th>Fecha</th><th>Cliente</th><th>Total</th><th>Status</th><th>Pago</th><th>Acciones</th></tr>
            </thead>
            <tbody id="tablaPedidosBody">
            <?php if(isset($sp) && !empty($sp)): ?>
                <?php foreach($sp as $pedido): 
                    $totalPedido = $pedido['total'];
                    $tipoPago    = $pedido['tipo_pago']    ?? 'contado';
                    $montoPagado = $pedido['monto_pago'] ?? 0;
                    $estadoPago = ($tipoPago === 'credito') ? 'credito' : 'pagado';
                ?>
                <tr id="fila-<?= $pedido['id'] ?>" data-id="<?= $pedido['id'] ?>" data-total="<?= $totalPedido ?>" data-pagado="<?= $montoPagado ?>" data-tipo-pago="<?= $tipoPago ?>">
                    <td><button class="expand-icon" onclick="toggleProgressRow(<?= $pedido['id'] ?>)"><i class="fas fa-chevron-down"></i></button></td>
                    <td><strong>#<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                    <td><?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></td>
                    <td class="clase-nombre"><?= htmlspecialchars($pedido['nombre_cliente'] ?? 'Sin nombre') ?></td>
                    <td><strong style="color:var(--primary-orange);">$<?= number_format($totalPedido, 2) ?></strong></td>
                    <td>
                        <select class="estado-select" id="estado-<?= $pedido['id'] ?>">
                            <?php
                            $estadosMap = [
                                'Pedido' => 'Pedido',
                                'Pedido confirmado' => 'Confirmado',
                                'Pedido en tránsito' => 'En tránsito',
                                'Venta confirmada' => 'Entregado',
                                'Pedido cancelado' => 'Cancelado',
                            ];
                            foreach($estadosMap as $valor => $etiqueta): ?>
                                <option value="<?= $valor ?>" <?= $pedido['estado_actual'] === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <div class="pago-badge <?= $estadoPago ?>" onclick="abrirModalCredito(<?= $pedido['id'] ?>, <?= $totalPedido ?>, <?= $montoPagado ?>, '<?= $tipoPago ?>')">
                            <?php if($estadoPago == 'pagado'): ?>
                                🟢 Pagado
                            <?php else: ?>
                                🟡 Crédito ($<?= number_format($montoPagado, 2) ?> pagados)
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="acciones-fila">
                            <button class="btn-actualizar" onclick="cambiarEstado(<?= $pedido['id'] ?>)"><i class="fas fa-sync-alt"></i> Actualizar</button>
                            <button class="btn-eliminar" onclick="eliminarPedido(<?= $pedido['id'] ?>)"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </td>
                </tr>
                <tr id="progress-row-<?= $pedido['id'] ?>" class="progress-row-detail" style="display: none;">
                    <td colspan="8">
                        <div id="progress-container-<?= $pedido['id'] ?>" class="progress-container"></div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr class="fila-vacia"><td colspan="8">No hay pedidos registrados</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL MEJORADO - SOLO ESTILOS DE LA VENTANA MEJORADOS -->
<div id="modalCredito" class="modal-credito">
    <div class="modal-contenido">
        <div class="modal-header">
            <h3><i class="fas fa-receipt"></i> Detalle del Pedido</h3>
            <button class="close-modal" onclick="cerrarModal()">&times;</button>
        </div>
        
        <div class="modal-scroll">
            <!-- Info general -->
            <div id="modalInfoGeneral" class="info-general-card"></div>

            <!-- Productos -->
            <div class="productos-titulo">
                <i class="fas fa-box-open"></i> Productos del pedido
            </div>
            <div style="overflow-x:auto; border-radius:16px;">
                <table class="tabla-productos-modal">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="text-align:center">Cant.</th>
                            <th style="text-align:center">Unidad</th>
                            <th style="text-align:right">P. Unit.</th>
                            <th style="text-align:right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="modalProductosBody"></tbody>
                </table>
            </div>

            <!-- SECCIÓN DE PAGO (solo crédito) mejorada visualmente -->
            <div id="seccionPago" style="display:none;">
                <div class="pago-section">
                    <div class="pago-titulo">
                        <i class="fas fa-credit-card"></i> Actualizar estado de pago
                    </div>
                    <div class="campo-pago">
                        <label><i class="fas fa-calculator"></i> Total pedido:</label>
                        <input type="text" id="modalTotal" readonly style="background:#f3f4f6; font-weight:700;">
                    </div>
                    <div class="campo-pago">
                        <label><i class="fas fa-dollar-sign"></i> Monto Pagado ($):</label>
                        <input type="number" id="modalPagado" step="0.01" min="0" class="editable-pagado" placeholder="0.00">
                    </div>
                    <div class="restante">
                        <i class="fas fa-chart-line"></i> Restante por pagar: <span id="modalRestante">$0.00</span>
                    </div>
                    <button class="btn-guardar-pago" onclick="guardarPago()">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const ESTADOS_FLUJO = [
    { key: 'Pedido', label: 'Pedido' },
    { key: 'Pedido confirmado', label: 'Confirmado' },
    { key: 'Pedido en tránsito', label: 'En tránsito' },
    { key: 'Venta confirmada', label: 'Entregado' }
];

function renderProgressForPedido(estadoActual) {
    let currentIndex = -1;
    for (let i = 0; i < ESTADOS_FLUJO.length; i++) {
        if (ESTADOS_FLUJO[i].key === estadoActual) { currentIndex = i; break; }
    }
    let allStepsCompleted = (estadoActual === 'Venta confirmada');
    if (estadoActual === 'Pedido cancelado') {
        let html = `<div class="progress-steps" style="position:relative;">`;
        html += `<div class="progress-line-fill" style="width:0%; background:#cbd5e1;"></div>`;
        for (let idx = 0; idx < ESTADOS_FLUJO.length; idx++) {
            const step = ESTADOS_FLUJO[idx];
            html += `<div class="step-item pending"><div class="step-circle">${idx+1}</div><div class="step-label">${step.label}</div></div>`;
        }
        html += `</div><div style="text-align:center; margin-top:8px; font-size:0.7rem; color:#ef4444;"><i class="fas fa-ban"></i> Pedido cancelado</div>`;
        return html;
    }
    let completedIndex = (currentIndex !== -1) ? currentIndex : (allStepsCompleted ? ESTADOS_FLUJO.length-1 : -1);
    let activeIndex = currentIndex;
    let widthPercent = (completedIndex >= 0 && ESTADOS_FLUJO.length > 1) ? (completedIndex / (ESTADOS_FLUJO.length - 1)) * 100 : (allStepsCompleted ? 100 : 0);
    let htmlSteps = `<div class="progress-steps" style="position:relative;"><div class="progress-line-fill" style="width:${widthPercent}%;"></div>`;
    for (let i = 0; i < ESTADOS_FLUJO.length; i++) {
        const step = ESTADOS_FLUJO[i];
        let stepClass = 'pending';
        let iconContent = (i+1).toString();
        if (i < completedIndex || (i === completedIndex && completedIndex !== -1 && !allStepsCompleted && i !== activeIndex)) {
            stepClass = 'completed';
            iconContent = '<i class="fas fa-check" style="font-size:0.8rem;"></i>';
        } else if (i === activeIndex && !allStepsCompleted && activeIndex !== -1) {
            stepClass = 'active';
            iconContent = (i+1).toString();
        } else if (allStepsCompleted && i <= completedIndex) {
            stepClass = 'completed';
            iconContent = '<i class="fas fa-check" style="font-size:0.8rem;"></i>';
        }
        htmlSteps += `<div class="step-item ${stepClass}"><div class="step-circle">${iconContent}</div><div class="step-label">${step.label}</div></div>`;
    }
    htmlSteps += `</div>`;
    return htmlSteps;
}

function refreshProgressForPedido(idPedido, newEstado = null) {
    const targetDiv = document.getElementById(`progress-container-${idPedido}`);
    if (!targetDiv) return;
    let estadoActual = newEstado;
    if (!estadoActual) {
        const selectElement = document.getElementById(`estado-${idPedido}`);
        if (selectElement) estadoActual = selectElement.value;
        else {
            const filaPrincipal = document.getElementById(`fila-${idPedido}`);
            if (filaPrincipal) estadoActual = filaPrincipal.getAttribute('data-estado-actual');
        }
    }
    if (!estadoActual) estadoActual = 'Pedido';
    targetDiv.innerHTML = renderProgressForPedido(estadoActual);
    const fila = document.getElementById(`fila-${idPedido}`);
    if (fila) fila.setAttribute('data-estado-actual', estadoActual);
}

window.toggleProgressRow = function(idPedido) {
    const row = document.getElementById(`progress-row-${idPedido}`);
    if (row.style.display === 'none') {
        refreshProgressForPedido(idPedido);
        row.style.display = 'table-row';
    } else {
        row.style.display = 'none';
    }
}

window.cambiarEstado = function(idPedido) {
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
            refreshProgressForPedido(idPedido, nuevoEstado);
        } else { toast('Error al actualizar el estado', 'error'); }
    })
    .catch(() => toast('Error de conexión', 'error'));
}

window.eliminarPedido = function(idPedido) {
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
        fetch("<?= base_url('pedido/eliminar') ?>/" + idPedido, { method: 'DELETE', headers: { 'Content-Type': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('fila-' + idPedido)?.remove();
                document.getElementById('progress-row-' + idPedido)?.remove();
                toast('Pedido eliminado', 'success');
            } else { toast(data.message || 'Error', 'error'); }
        })
        .catch(() => toast('Error de conexión', 'error'));
    });
}

// =============================================
// MODAL DETALLE MEJORADO (solo se mejora visual, lógica intacta)
// =============================================
let currentPedidoId = null;
let currentTotal    = 0;
let currentPagado   = 0;
let currentTipoPago = 'contado';

function abrirModalCredito(id, total, pagado, tipoPago) {
    currentPedidoId = id;
    currentTotal    = parseFloat(total);
    currentPagado   = parseFloat(pagado);
    currentTipoPago = tipoPago;

    document.getElementById('modalInfoGeneral').innerHTML   = '<div class="info-general-card"><p style="color:#999;">Cargando información...</p></div>';
    document.getElementById('modalProductosBody').innerHTML = '';
    document.getElementById('seccionPago').style.display    = 'none';

    document.getElementById('modalCredito').classList.add('active');

    fetch("<?= base_url('pedido/detalle') ?>", {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) { toast('Error al cargar detalle', 'error'); return; }

        const p = data.pedido;
        const esCred = p.tipo_pago === 'credito';
        const montoPagado = parseFloat(p.monto_pagado) || 0;
        const totalPedido = parseFloat(p.total);
        const restante = totalPedido - montoPagado;

        const badgePago = esCred
            ? `<span class="badge-estado-pago badge-credito">🟡 Crédito</span>`
            : `<span class="badge-estado-pago badge-pagado">🟢 Pagado</span>`;

        const badgeEntrega = p.tipo_entrega === 'domicilio'
            ? `<span class="badge-entrega badge-domicilio"><i class="fa-solid fa-house"></i> Domicilio</span>`
            : `<span class="badge-entrega badge-tienda"><i class="fa-solid fa-store"></i> Tienda</span>`;

        document.getElementById('modalInfoGeneral').innerHTML = `
            <div class="grid-info">
                <div class="info-item"><div class="info-label">Folio</div><div class="info-value">#${String(p.id).padStart(5,'0')}</div></div>
                <div class="info-item"><div class="info-label">Fecha</div><div class="info-value">${formatearFecha(p.fecha)}</div></div>
                <div class="info-item"><div class="info-label">Cliente</div><div class="info-value">${p.nombre_cliente ?? 'Público general'}</div></div>
                <div class="info-item"><div class="info-label">Entrega</div><div class="info-value">${badgeEntrega}</div></div>
                ${p.nombre_repartidor ? `<div class="info-item"><div class="info-label">Repartidor</div><div class="info-value">${p.nombre_repartidor}</div></div>` : ''}
                <div class="info-item"><div class="info-label">Estado</div><div class="info-value">${p.estado_actual}</div></div>
                <div class="info-item"><div class="info-label">Pago</div><div class="info-value">${badgePago}</div></div>
                <div class="info-item"><div class="info-label">Total</div><div class="info-value" style="color:#f16b1a; font-weight:800;">$${totalPedido.toFixed(2)}</div></div>
                ${esCred ? `
                <div class="info-item"><div class="info-label">Pagado</div><div class="info-value" style="color:#10b981;">$${montoPagado.toFixed(2)}</div></div>
                <div class="info-item"><div class="info-label">Restante</div><div class="info-value" style="color:#ef4444;">$${restante.toFixed(2)}</div></div>
                ` : ''}
            </div>
        `;

        const tbody = document.getElementById('modalProductosBody');
        tbody.innerHTML = '';
        data.productos.forEach((prod, i) => {
            const tr = document.createElement('tr');
            tr.style.background = i % 2 === 0 ? '#ffffff' : '#fcfef9';
            tr.innerHTML = `
                <td style="padding:10px 8px;">${prod.nombre}</td>
                <td style="padding:10px 8px; text-align:center;">${parseFloat(prod.cantidad)}</td>
                <td style="padding:10px 8px; text-align:center;">${prod.unidad_venta}</td>
                <td style="padding:10px 8px; text-align:right;">$${parseFloat(prod.precio_venta).toFixed(2)}</td>
                <td style="padding:10px 8px; text-align:right; font-weight:600;">$${parseFloat(prod.subtotal).toFixed(2)}</td>
            `;
            tbody.appendChild(tr);
        });

        if (esCred) {
            document.getElementById('modalTotal').value = `$${totalPedido.toFixed(2)}`;
            document.getElementById('modalPagado').value = montoPagado.toFixed(2);
            actualizarRestante();
            document.getElementById('seccionPago').style.display = 'block';
        }
    })
    .catch(() => toast('Error de conexión', 'error'));
}

function formatearFecha(fechaStr) {
    const d = new Date(fechaStr);
    return d.toLocaleDateString('es-MX', { day:'2-digit', month:'2-digit', year:'numeric' })
        + ' ' + d.toLocaleTimeString('es-MX', { hour:'2-digit', minute:'2-digit' });
}

function actualizarRestante() {
    let pagado = parseFloat(document.getElementById('modalPagado').value) || 0;
    if (pagado > currentTotal) pagado = currentTotal;
    document.getElementById('modalRestante').innerHTML = `$${(currentTotal - pagado).toFixed(2)}`;
}

document.getElementById('modalPagado')?.addEventListener('input', actualizarRestante);

function cerrarModal() {
    document.getElementById('modalCredito').classList.remove('active');
}

function guardarPago() {
    let nuevoPagado = parseFloat(document.getElementById('modalPagado').value) || 0;
    if (nuevoPagado > currentTotal) nuevoPagado = currentTotal;
    if (nuevoPagado < 0) nuevoPagado = 0;

    fetch("<?= base_url('pedido/actualizar_pago') ?>", {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${currentPedidoId}&monto_pagado=${nuevoPagado}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const badgeDiv = document.querySelector(`#fila-${currentPedidoId} td .pago-badge`);
            const esPagoCompleto = nuevoPagado >= currentTotal;
            const nuevoEstadoPago = (!esPagoCompleto && currentTipoPago === 'credito') ? 'credito' : 'pagado';

            if (nuevoEstadoPago === 'pagado') {
                badgeDiv.innerHTML = '🟢 Pagado';
                badgeDiv.className = 'pago-badge pagado';
            } else {
                badgeDiv.innerHTML = `🟡 Crédito ($${nuevoPagado.toFixed(2)} pagados)`;
                badgeDiv.className = 'pago-badge credito';
            }

            const fila = document.getElementById(`fila-${currentPedidoId}`);
            fila.setAttribute('data-pagado', nuevoPagado);
            fila.setAttribute('data-tipo-pago', nuevoEstadoPago === 'pagado' ? 'contado' : 'credito');

            toast('Pago actualizado correctamente', 'success');
            cerrarModal();
        } else {
            toast('Error al guardar el pago', 'error');
        }
    })
    .catch(() => toast('Error de conexión', 'error'));
}

// BUSCADOR Y ORDENAMIENTO (funciones originales)
document.getElementById('inputBuscarPedido').addEventListener('keyup', function() {
    const busqueda = this.value.toLowerCase();
    document.querySelectorAll('#tablaPedidosBody tr').forEach(fila => {
        if (fila.querySelector('td[colspan]')) return;
        if (fila.classList?.contains('progress-row-detail')) return;
        if (fila.id?.includes('progress-row')) return;
        const mostrar = fila.innerText.toLowerCase().includes(busqueda);
        fila.style.display = mostrar ? '' : 'none';
        const detailId = fila.id ? fila.id.replace('fila-', 'progress-row-') : null;
        if (detailId && !mostrar) {
            const detail = document.getElementById(detailId);
            if (detail) detail.style.display = 'none';
        }
    });
});

function ordenarAZ() { ordenarTabla(1); }
function ordenarZA() { ordenarTabla(-1); }
function ordenarTabla(dir) {
    const tbody = document.getElementById('tablaPedidosBody');
    let trs = Array.from(tbody.querySelectorAll('tr')).filter(f => f.id && f.id.startsWith('fila-'));
    trs.sort((a,b) => {
        let na = (a.querySelector('.clase-nombre')?.innerText || '').trim().toLowerCase();
        let nb = (b.querySelector('.clase-nombre')?.innerText || '').trim().toLowerCase();
        return dir * na.localeCompare(nb, 'es');
    });
    trs.forEach(tr => {
        let detailRow = document.getElementById(tr.id.replace('fila-', 'progress-row-'));
        tbody.appendChild(tr);
        if (detailRow) tbody.appendChild(detailRow);
    });
}

function toast(msg, tipo) {
    const t = document.createElement('div');
    t.textContent = msg;
    Object.assign(t.style, {
        position:'fixed', bottom:'24px', right:'24px', padding:'12px 20px',
        borderRadius:'10px', fontSize:'0.85rem', fontWeight:'600', color:'white',
        zIndex:'9999', background: tipo === 'success' ? '#10b981' : '#ef4444',
        animation: 'slideInRight 0.3s ease'
    });
    document.body.appendChild(t);
    setTimeout(() => { t.style.animation = 'slideOutRight 0.3s ease'; setTimeout(() => t.remove(), 300); }, 3000);
}
</script>
</body>
</html>