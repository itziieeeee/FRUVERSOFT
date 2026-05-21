<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>FRUVER · Seguimiento de Pedidos con Progreso</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?= base_url('css/estiloparapedidos.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="barra-superior">
    <div class="logo-area">
        <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo FRUVER">
    </div>
    <div class="buscador-header">
        <input type="text" id="inputBuscarPedidoHeader" placeholder="Buscar por cliente o folio...">
    </div>
    <div class="user-actions">
        <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> <span>Admin</span></a>
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

    <!-- ESTADÍSTICAS -->
    <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-value" id="totalPedidosCount">0</div>
            <div class="stat-label">Total Pedidos</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-truck-moving"></i></div>
            <div class="stat-value" id="transitoCount">0</div>
            <div class="stat-label">En Tránsito</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value" id="entregadosCount">0</div>
            <div class="stat-label">Entregados</div>
        </div>
    </div>

    <!-- TÍTULO -->
    <div class="titulo-buscador-row">
        <div class="titulo-pedido">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>Seguimiento de Pedidos</span>
        </div>
    </div>

    <!-- FILTROS CHIPS -->
    <div class="filtros-container">
        <div class="chip-filtro active" data-filter="todos">Todos</div>
        <div class="chip-filtro" data-filter="Pedido confirmado">Confirmado</div>
        <div class="chip-filtro" data-filter="Pedido en tránsito">En tránsito</div>
        <div class="chip-filtro" data-filter="Venta confirmada">Entregado</div>
        <div class="chip-filtro" data-filter="Pedido cancelado">Cancelado</div>
    </div>

    <div class="controles-row">
        <button class="btn-orden az" onclick="ordenarAZ()"><i class="fas fa-sort-alpha-down"></i> A–Z</button>
        <button class="btn-orden za" onclick="ordenarZA()"><i class="fas fa-sort-alpha-up"></i> Z–A</button>
    </div>

    <div class="tabla-container">
        <table class="tabla-pedidos-exitentes">
            <thead>
                <tr>
                    <th style="width:30px"></th>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Pago</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaPedidosBody">
            <?php if(isset($sp) && !empty($sp)): ?>
                <?php foreach($sp as $pedido):
                    $totalPedido = $pedido['total'];
                    $tipoPago    = $pedido['tipo_pago']  ?? 'contado';
                    $montoPagado = $pedido['monto_pago'] ?? 0;
                    $estadoPago  = ($tipoPago === 'credito') ? 'credito' : 'pagado';
                    $estadoActual = $pedido['estado_actual'];

                    $estadoClass = '';
                    $estadoLabel = '';
                    if ($estadoActual == 'Pedido' || $estadoActual == 'Pedido confirmado') {
                        $estadoClass = 'confirmado';
                        $estadoLabel = 'Confirmado';
                    } elseif ($estadoActual == 'Pedido en tránsito') {
                        $estadoClass = 'transito';
                        $estadoLabel = 'En tránsito';
                    } elseif ($estadoActual == 'Venta confirmada') {
                        $estadoClass = 'entregado';
                        $estadoLabel = 'Entregado';
                    } elseif ($estadoActual == 'Pedido cancelado') {
                        $estadoClass = 'cancelado';
                        $estadoLabel = 'Cancelado';
                    } else {
                        $estadoClass = 'pedido';
                        $estadoLabel = 'Pedido';
                    }

                    $iconoEstado = '';
                    if ($estadoClass == 'confirmado') $iconoEstado = 'fa-check-circle';
                    elseif ($estadoClass == 'transito')   $iconoEstado = 'fa-truck';
                    elseif ($estadoClass == 'entregado')  $iconoEstado = 'fa-check-double';
                    elseif ($estadoClass == 'cancelado')  $iconoEstado = 'fa-ban';
                    else                                   $iconoEstado = 'fa-clock';
                ?>
                <tr id="fila-<?= $pedido['id'] ?>"
                    data-id="<?= $pedido['id'] ?>"
                    data-total="<?= $totalPedido ?>"
                    data-pagado="<?= $montoPagado ?>"
                    data-tipo-pago="<?= $tipoPago ?>"
                    data-estado="<?= htmlspecialchars($estadoActual) ?>">

                    <td>
                        <button class="expand-icon" onclick="toggleProgressRow(<?= $pedido['id'] ?>)">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </td>
                    <td><strong>#<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                    <td><?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></td>
                    <td class="clase-nombre"><?= htmlspecialchars($pedido['nombre_cliente'] ?? 'Sin nombre') ?></td>
                    <td><strong style="color:var(--primary-orange);">$<?= number_format($totalPedido, 2) ?></strong></td>

                    <td class="estado-badge-container">
                        <div class="estado-badge <?= $estadoClass ?>" onclick="toggleEstadoDropdown(this)">
                            <i class="fas <?= $iconoEstado ?>"></i>
                            <?= $estadoLabel ?>
                            <i class="fas fa-chevron-down" style="font-size:0.6rem;"></i>
                        </div>
                        <div class="estado-dropdown">
                            <div class="estado-option" data-value="Pedido confirmado" data-class="confirmado" data-icon="fa-check-circle">
                                <i class="fas fa-check-circle"></i> Confirmado
                            </div>
                            <div class="estado-option" data-value="Pedido en tránsito" data-class="transito" data-icon="fa-truck">
                                <i class="fas fa-truck"></i> En tránsito
                            </div>
                            <div class="estado-option" data-value="Venta confirmada" data-class="entregado" data-icon="fa-check-double">
                                <i class="fas fa-check-double"></i> Entregado
                            </div>
                            <div class="estado-option" data-value="Pedido cancelado" data-class="cancelado" data-icon="fa-ban">
                                <i class="fas fa-ban"></i> Cancelado
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="pago-badge <?= $estadoPago ?>"
                             onclick="abrirModalCredito(<?= $pedido['id'] ?>, <?= $totalPedido ?>, <?= $montoPagado ?>, '<?= $tipoPago ?>')">
                            <?php if($estadoPago == 'pagado'): ?>
                                🟢 Pagado
                            <?php else: ?>
                                🟡 Crédito ($<?= number_format($montoPagado, 2) ?> pagados)
                            <?php endif; ?>
                        </div>
                    </td>

                    <td>
                        <div class="acciones-fila">
                            <button class="btn-actualizar" onclick="cambiarEstado(<?= $pedido['id'] ?>)">
                                <i class="fas fa-sync-alt"></i> Actualizar
                            </button>
                            <button class="btn-eliminar" onclick="eliminarPedido(<?= $pedido['id'] ?>)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>

                <tr id="progress-row-<?= $pedido['id'] ?>" class="progress-row-detail" style="display:none;">
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

<!-- MODAL DETALLE DEL PEDIDO -->
<div id="modalCredito" class="modal-credito">
    <div class="modal-contenido">
        <div class="modal-header">
            <h3><i class="fas fa-receipt"></i> Detalle del Pedido</h3>
            <button class="close-modal" onclick="cerrarModal()">&times;</button>
        </div>
        <div class="modal-scroll">
            <div id="modalInfoGeneral" class="info-general-card"></div>

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

//  ESTADOS DE FLUJO

const ESTADOS_FLUJO = [
    { key: 'Pedido',            label: 'Pedido'      },
    { key: 'Pedido confirmado', label: 'Confirmado'  },
    { key: 'Pedido en tránsito',label: 'En tránsito' },
    { key: 'Venta confirmada',  label: 'Entregado'   }
];
function renderProgressForPedido(estadoActual) {
    if (estadoActual === 'Pedido cancelado') {
        let html = `<div class="progress-steps" style="position:relative;">
                        <div class="progress-line-fill" style="width:0%; background:#cbd5e1;"></div>`;
        ESTADOS_FLUJO.forEach((step, idx) => {
            html += `<div class="step-item pending">
                        <div class="step-circle">${idx + 1}</div>
                        <div class="step-label">${step.label}</div>
                     </div>`;
        });
        html += `</div>
                 <div style="text-align:center; margin-top:8px; font-size:0.7rem; color:#ef4444;">
                     <i class="fas fa-ban"></i> Pedido cancelado
                 </div>`;
        return html;
    }

    let currentIndex = ESTADOS_FLUJO.findIndex(s => s.key === estadoActual);
    let allDone      = (estadoActual === 'Venta confirmada');
    let widthPercent = currentIndex >= 0
        ? (currentIndex / (ESTADOS_FLUJO.length - 1)) * 100
        : (allDone ? 100 : 0);

    let html = `<div class="progress-steps" style="position:relative;">
                    <div class="progress-line-fill" style="width:${widthPercent}%;"></div>`;

    ESTADOS_FLUJO.forEach((step, i) => {
        let stepClass   = 'pending';
        let iconContent = (i + 1).toString();

        if (allDone && i <= currentIndex) {
            stepClass   = 'completed';
            iconContent = '<i class="fas fa-check" style="font-size:0.8rem;"></i>';
        } else if (i < currentIndex) {
            stepClass   = 'completed';
            iconContent = '<i class="fas fa-check" style="font-size:0.8rem;"></i>';
        } else if (i === currentIndex) {
            stepClass = 'active';
        }

        html += `<div class="step-item ${stepClass}">
                    <div class="step-circle">${iconContent}</div>
                    <div class="step-label">${step.label}</div>
                 </div>`;
    });

    html += `</div>`;
    return html;
}

function refreshProgressForPedido(idPedido, newEstado = null) {
    const container = document.getElementById(`progress-container-${idPedido}`);
    if (!container) return;
    const estado = newEstado
        || document.getElementById(`fila-${idPedido}`)?.getAttribute('data-estado')
        || 'Pedido';
    container.innerHTML = renderProgressForPedido(estado);
}

// ============================================================
//  DROPDOWN DE ESTADO  —  solo abre/cierra
// ============================================================
function toggleEstadoDropdown(badgeEl) {
    document.querySelectorAll('.estado-dropdown.show').forEach(d => {
        if (d !== badgeEl.nextElementSibling) d.classList.remove('show');
    });
    badgeEl.nextElementSibling.classList.toggle('show');
}

// Cerrar al hacer click fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.estado-badge-container')) {
        document.querySelectorAll('.estado-dropdown.show').forEach(d => d.classList.remove('show'));
    }
});

// ============================================================
//  DELEGACIÓN GLOBAL PARA OPCIONES DE ESTADO
//  *** REGISTRADO UNA SOLA VEZ — NUNCA DENTRO DE OTRA FUNCIÓN ***
// ============================================================
document.addEventListener('click', function(e) {
    const option = e.target.closest('.estado-option');
    if (!option) return;

    e.stopPropagation();

    const dropdown        = option.closest('.estado-dropdown');
    const badge           = dropdown?.previousElementSibling;
    const fila            = dropdown?.closest('tr');
    const pedidoId        = fila?.getAttribute('data-id');
    const nuevoEstadoValue = option.getAttribute('data-value');
    const nuevoEstadoClass = option.getAttribute('data-class');
    const nuevoIcono       = option.getAttribute('data-icon');

    // Actualizar badge visualmente
    if (badge) {
        badge.className = `estado-badge ${nuevoEstadoClass}`;
        badge.innerHTML = `<i class="fas ${nuevoIcono}"></i> ${option.textContent.trim()} <i class="fas fa-chevron-down" style="font-size:0.6rem;"></i>`;
    }

    // Cerrar dropdown
    dropdown?.classList.remove('show');

    if (!pedidoId) return;

    // Petición al servidor
    fetch("<?= base_url('status/cambiar') ?>", {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${pedidoId}&estado=${encodeURIComponent(nuevoEstadoValue)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            fila?.setAttribute('data-estado', nuevoEstadoValue);
            refreshProgressForPedido(pedidoId, nuevoEstadoValue);
            toast('Estado actualizado correctamente', 'success');
            actualizarEstadisticas();
            aplicarFiltroActual();
        } else {
            toast('Error al actualizar el estado', 'error');
        }
    })
    .catch(() => toast('Error de conexión', 'error'));
});

// ============================================================
//  BOTÓN "ACTUALIZAR" DE LA FILA — abre el dropdown
// ============================================================
window.cambiarEstado = function(idPedido) {
    const fila  = document.getElementById(`fila-${idPedido}`);
    const badge = fila?.querySelector('.estado-badge');
    if (badge) toggleEstadoDropdown(badge);
};

// ============================================================
//  EXPAND / COLLAPSE PROGRESS ROW
// ============================================================
window.toggleProgressRow = function(idPedido) {
    const row = document.getElementById(`progress-row-${idPedido}`);
    if (!row) return;
    if (row.style.display === 'none') {
        refreshProgressForPedido(idPedido);
        row.style.display = 'table-row';
    } else {
        row.style.display = 'none';
    }
};

// ============================================================
//  ELIMINAR PEDIDO
// ============================================================
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
        fetch("<?= base_url('pedido/eliminar') ?>/" + idPedido, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('fila-' + idPedido)?.remove();
                document.getElementById('progress-row-' + idPedido)?.remove();
                toast('Pedido eliminado', 'success');
                actualizarEstadisticas();
            } else {
                toast(data.message || 'Error', 'error');
            }
        })
        .catch(() => toast('Error de conexión', 'error'));
    });
};

// ============================================================
//  MODAL — DETALLE + PAGO
// ============================================================
let currentPedidoId = null;
let currentTotal    = 0;

function abrirModalCredito(id, total, pagado, tipoPago) {
    currentPedidoId = id;
    currentTotal    = parseFloat(total);

    document.getElementById('modalInfoGeneral').innerHTML =
        '<p style="color:#999; padding:1rem;">Cargando información...</p>';
    document.getElementById('modalProductosBody').innerHTML = '';
    document.getElementById('seccionPago').style.display   = 'none';
    document.getElementById('modalCredito').classList.add('active');

    fetch("<?= base_url('pedido/detalle') ?>", {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) { toast('Error al cargar detalle', 'error'); return; }

        const p           = data.pedido;
        const esCred      = p.tipo_pago === 'credito';
        const montoPagado = parseFloat(p.monto_pagado) || 0;
        const totalPedido = parseFloat(p.total);
        const restante    = totalPedido - montoPagado;

        const badgePago = esCred
            ? `<span class="badge-estado-pago badge-credito">🟡 Crédito</span>`
            : `<span class="badge-estado-pago badge-pagado">🟢 Pagado</span>`;

        const badgeEntrega = p.tipo_entrega === 'domicilio'
            ? `<span class="badge-entrega badge-domicilio"><i class="fa-solid fa-house"></i> Domicilio</span>`
            : `<span class="badge-entrega badge-tienda"><i class="fa-solid fa-store"></i> Tienda</span>`;

        document.getElementById('modalInfoGeneral').innerHTML = `
            <div class="grid-info">
                <div class="info-item">
                    <div class="info-label">Folio</div>
                    <div class="info-value">#${String(p.id).padStart(5,'0')}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fecha</div>
                    <div class="info-value">${formatearFecha(p.fecha)}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Cliente</div>
                    <div class="info-value">${p.nombre_cliente ?? 'Público general'}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Entrega</div>
                    <div class="info-value">${badgeEntrega}</div>
                </div>
                ${p.nombre_repartidor ? `
                <div class="info-item">
                    <div class="info-label">Repartidor</div>
                    <div class="info-value">${p.nombre_repartidor}</div>
                </div>` : ''}
                <div class="info-item">
                    <div class="info-label">Estado</div>
                    <div class="info-value">${p.estado_actual}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Pago</div>
                    <div class="info-value">${badgePago}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total</div>
                    <div class="info-value" style="color:#f16b1a; font-weight:800;">$${totalPedido.toFixed(2)}</div>
                </div>
                ${esCred ? `
                <div class="info-item">
                    <div class="info-label">Pagado</div>
                    <div class="info-value" style="color:#10b981;">$${montoPagado.toFixed(2)}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Restante</div>
                    <div class="info-value" style="color:#ef4444;">$${restante.toFixed(2)}</div>
                </div>` : ''}
            </div>`;

        const tbody = document.getElementById('modalProductosBody');
        tbody.innerHTML = '';
        if (data.productos && data.productos.length > 0) {
            data.productos.forEach((prod, i) => {
                const tr = document.createElement('tr');
                tr.style.background = i % 2 === 0 ? '#ffffff' : '#fcfef9';
                tr.innerHTML = `
                    <td style="padding:10px 8px;">${prod.nombre}</td>
                    <td style="padding:10px 8px; text-align:center;">${parseFloat(prod.cantidad)}</td>
                    <td style="padding:10px 8px; text-align:center;">${prod.unidad_venta || '-'}</td>
                    <td style="padding:10px 8px; text-align:right;">$${parseFloat(prod.precio_venta).toFixed(2)}</td>
                    <td style="padding:10px 8px; text-align:right; font-weight:600;">$${parseFloat(prod.subtotal).toFixed(2)}</td>`;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay productos registrados</td></tr>';
        }

        if (esCred) {
            document.getElementById('modalTotal').value  = `$${totalPedido.toFixed(2)}`;
            document.getElementById('modalPagado').value = montoPagado.toFixed(2);
            actualizarRestanteModal();
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

function actualizarRestanteModal() {
    let pagado = parseFloat(document.getElementById('modalPagado').value) || 0;
    if (pagado > currentTotal) pagado = currentTotal;
    document.getElementById('modalRestante').textContent = `$${(currentTotal - pagado).toFixed(2)}`;
}

document.getElementById('modalPagado').addEventListener('input', actualizarRestanteModal);

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
            const badgeDiv = document.querySelector(`#fila-${currentPedidoId} .pago-badge`);
            const esPagoCompleto = nuevoPagado >= currentTotal;
            if (esPagoCompleto) {
                badgeDiv.innerHTML   = '🟢 Pagado';
                badgeDiv.className   = 'pago-badge pagado';
            } else {
                badgeDiv.innerHTML   = `🟡 Crédito ($${nuevoPagado.toFixed(2)} pagados)`;
                badgeDiv.className   = 'pago-badge credito';
            }
            toast('Pago actualizado correctamente', 'success');
            cerrarModal();
        } else {
            toast('Error al guardar el pago', 'error');
        }
    })
    .catch(() => toast('Error de conexión', 'error'));
}

// ============================================================
//  ESTADÍSTICAS
// ============================================================
function actualizarEstadisticas() {
    const filas = document.querySelectorAll('#tablaPedidosBody tr[id^="fila-"]');
    let enTransito = 0;
    let entregados = 0;

    filas.forEach(fila => {
        const estado = fila.getAttribute('data-estado') || '';
        if (estado === 'Pedido en tránsito') enTransito++;
        if (estado === 'Venta confirmada')    entregados++;
    });

    document.getElementById('totalPedidosCount').textContent = filas.length;
    document.getElementById('transitoCount').textContent     = enTransito;
    document.getElementById('entregadosCount').textContent   = entregados;
}

// ============================================================
//  FILTROS
// ============================================================
let filtroActual = 'todos';

function aplicarFiltroActual() {
    document.querySelectorAll('#tablaPedidosBody tr[id^="fila-"]').forEach(fila => {
        const estado    = fila.getAttribute('data-estado') || '';
        const detailRow = document.getElementById(fila.id.replace('fila-', 'progress-row-'));
        const visible   = filtroActual === 'todos' || estado === filtroActual;

        fila.style.display = visible ? '' : 'none';
        if (detailRow) {
            if (!visible) {
                detailRow.style.display = 'none';
            }
            // Si está visible y el detalle estaba abierto, mantenerlo
        }
    });
}

document.querySelectorAll('.chip-filtro').forEach(chip => {
    chip.addEventListener('click', function() {
        document.querySelectorAll('.chip-filtro').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        filtroActual = this.getAttribute('data-filter');
        aplicarFiltroActual();
    });
});

// ============================================================
//  BUSCADOR
// ============================================================
document.getElementById('inputBuscarPedidoHeader').addEventListener('keyup', function() {
    const busqueda = this.value.toLowerCase();
    document.querySelectorAll('#tablaPedidosBody tr[id^="fila-"]').forEach(fila => {
        const folio   = fila.querySelector('td:nth-child(2)')?.innerText?.toLowerCase() || '';
        const cliente = fila.querySelector('.clase-nombre')?.innerText?.toLowerCase()   || '';
        const estado  = fila.getAttribute('data-estado') || '';
        const coincide = (folio.includes(busqueda) || cliente.includes(busqueda))
                      && (filtroActual === 'todos' || estado === filtroActual);

        const detailRow = document.getElementById(fila.id.replace('fila-', 'progress-row-'));
        fila.style.display = coincide ? '' : 'none';
        if (detailRow && !coincide) detailRow.style.display = 'none';
    });
});

// ============================================================
//  ORDENAMIENTO
// ============================================================
function ordenarAZ() { ordenarTabla(1);  }
function ordenarZA() { ordenarTabla(-1); }

function ordenarTabla(dir) {
    const tbody = document.getElementById('tablaPedidosBody');
    const filas = Array.from(tbody.querySelectorAll('tr[id^="fila-"]'));
    filas.sort((a, b) => {
        const na = (a.querySelector('.clase-nombre')?.innerText || '').trim().toLowerCase();
        const nb = (b.querySelector('.clase-nombre')?.innerText || '').trim().toLowerCase();
        return dir * na.localeCompare(nb, 'es');
    });
    filas.forEach(tr => {
        const detailRow = document.getElementById(tr.id.replace('fila-', 'progress-row-'));
        tbody.appendChild(tr);
        if (detailRow) tbody.appendChild(detailRow);
    });
}

// ============================================================
//  TOAST
// ============================================================
function toast(msg, tipo) {
    const t = document.createElement('div');
    t.textContent = msg;
    Object.assign(t.style, {
        position:     'fixed',
        bottom:       '24px',
        right:        '24px',
        padding:      '12px 20px',
        borderRadius: '10px',
        fontSize:     '0.85rem',
        fontWeight:   '600',
        color:        'white',
        zIndex:       '9999',
        background:   tipo === 'success' ? '#10b981' : '#ef4444',
        animation:    'slideInRight 0.3s ease'
    });
    document.body.appendChild(t);
    setTimeout(() => {
        t.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => t.remove(), 300);
    }, 3000);
}

// ============================================================
//  INIT
// ============================================================
document.addEventListener('DOMContentLoaded', actualizarEstadisticas);
</script>
</body>
</html>