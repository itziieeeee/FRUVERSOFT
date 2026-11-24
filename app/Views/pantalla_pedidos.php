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
                    $montoPagado = $pedido['monto_pagado'] ?? 0;
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
                    <td><strong>#<?= str_pad($pedido['id'], 5, '0', STR_PAD_LEFT) ?></strong>
                        <?php if (!empty($pedido['prioritario'])): ?>
                            <span title="Pedido urgente" style="display:inline-block; background:#dc2626; color:#fff; font-size:.62rem; font-weight:700; padding:1px 7px; border-radius:50px; vertical-align:middle;"><i class="fas fa-bolt"></i> URGENTE</span>
                        <?php endif; ?>
                    </td>
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
    <button
        type="button"
        class="btn-actualizar"
        onclick="cambiarEstado(<?= $pedido['id'] ?>)">
        <i class="fas fa-sync-alt"></i> Actualizar
    </button>

    <?php if (in_array($estadoActual, [
        'Pedido confirmado',
        'Pedido en tránsito',
        'Venta confirmada'
    ], true)): ?>
        <button
            type="button"
            class="btn-actualizar"
            onclick="abrirModalCredito(
                <?= (int) $pedido['id'] ?>,
                <?= (float) $totalPedido ?>,
                <?= (float) $montoPagado ?>,
                '<?= esc($tipoPago) ?>'
            )">
            <i class="fas fa-edit"></i> Editar precios
        </button>
    <?php endif; ?>

    <?php if (in_array($estadoActual, [
        'Pedido confirmado',
        'Pedido en tránsito',
        'Venta confirmada',
        'Pedido a crédito',
        'Pedido pagado'
    ], true)): ?>
        <button
            type="button"
            class="btn-actualizar"
            title="El cliente regresa producto"
            onclick="abrirDevolucion(<?= (int) $pedido['id'] ?>)">
            <i class="fas fa-rotate-left"></i> Devolución
        </button>
    <?php endif; ?>

    <?php if (!empty($pedido['tiene_evidencia'])): ?>
        <a class="btn-actualizar" style="text-decoration:none;" target="_blank" title="Foto, firma y cobro de la entrega"
           href="<?= base_url('entrega/' . (int) $pedido['id']) ?>">
            <i class="fas fa-camera"></i> Evidencia
        </a>
    <?php endif; ?>

    <button
        type="button"
        class="btn-eliminar"
        onclick="eliminarPedido(<?= $pedido['id'] ?>)">
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

<div style="text-align: right; margin: 12px 0;">
    <button
        type="button"
        class="btn-fruver"
        onclick="guardarPrecios()">
        Guardar precios
    </button>
</div>

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
                    <div class="campo-pago">
                        <label><i class="fas fa-wallet"></i> Método de pago:</label>
                        <select id="modalMetodo" class="editable-pagado">
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
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

<!-- MODAL DEVOLUCIÓN -->
<style>
    #modalDev { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 3000; align-items: center; justify-content: center; padding: 1rem; }
    #modalDev.activo { display: flex; }
    #modalDev .dev-caja { background: #fff; border-radius: 18px; width: min(780px, 100%); max-height: 92vh; overflow-y: auto; box-shadow: 0 20px 50px rgba(0,0,0,.35); }
    #modalDev .dev-cab { background: #1d4a27; color: #fff; padding: 1rem 1.3rem; display: flex; justify-content: space-between; align-items: center; border-radius: 18px 18px 0 0; }
    #modalDev .dev-cab h3 { font-size: 1.1rem; display: flex; gap: 8px; align-items: center; }
    #modalDev .dev-x { background: rgba(255,255,255,.2); border: 0; color: #fff; width: 32px; height: 32px; border-radius: 50%; font-size: 1.1rem; cursor: pointer; }
    #modalDev .dev-cuerpo { padding: 1.2rem 1.3rem; }
    #modalDev table { width: 100%; border-collapse: collapse; font-size: .86rem; }
    #modalDev th { text-align: left; font-size: .7rem; text-transform: uppercase; color: #64748b; padding: 6px 8px; border-bottom: 2px solid #e2e8f0; }
    #modalDev td { padding: 8px; border-bottom: 1px solid #eef2f6; vertical-align: middle; }
    #modalDev td.r, #modalDev th.r { text-align: right; }
    #modalDev input[type=number], #modalDev select, #modalDev input[type=text] { padding: 7px 9px; border: 1.5px solid #cbd5e1; border-radius: 9px; font: inherit; font-size: .88rem; }
    #modalDev input[type=number] { width: 84px; text-align: right; }
    #modalDev .dev-resumen { background: #f1f5f9; border-radius: 12px; padding: .8rem 1rem; margin-top: 1rem; font-size: .9rem; }
    #modalDev .dev-resumen div { display: flex; justify-content: space-between; padding: 3px 0; }
    #modalDev .dev-resumen .grande { font-weight: 700; font-size: 1rem; border-top: 1px dashed #cbd5e1; margin-top: 4px; padding-top: 8px; }
    #modalDev .dev-aviso { background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; border-radius: 10px; padding: .7rem .9rem; font-size: .85rem; margin-bottom: .8rem; }
    #modalDev .dev-pie { display: flex; justify-content: flex-end; gap: .6rem; margin-top: 1rem; }
    #modalDev .dev-btn { border: 0; border-radius: 10px; padding: 10px 18px; font-weight: 700; cursor: pointer; font-family: inherit; background: #e2e8f0; color: #334155; }
    #modalDev .dev-btn.ok { background: #f16b1a; color: #fff; }
    #modalDev .dev-btn:disabled { opacity: .6; cursor: wait; }
</style>
<div id="modalDev">
    <div class="dev-caja">
        <div class="dev-cab">
            <h3><i class="fas fa-rotate-left"></i> <span id="devTitulo">Devolución</span></h3>
            <button type="button" class="dev-x" onclick="cerrarDevolucion()">&times;</button>
        </div>
        <div class="dev-cuerpo" id="devCuerpo"></div>
    </div>
</div>

<script>
// ============================================================
//  DEVOLUCIONES DE CLIENTES
// ============================================================
let devPedido = null;
const devDinero = n => '$' + Number(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const devEsc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

function abrirDevolucion(id) {
    const cuerpo = document.getElementById('devCuerpo');
    cuerpo.innerHTML = '<p style="color:#64748b;">Cargando...</p>';
    document.getElementById('devTitulo').textContent = 'Devolución';
    document.getElementById('modalDev').classList.add('activo');

    fetch("<?= base_url('devoluciones/pedido') ?>/" + id)
        .then(r => r.json())
        .then(d => {
            if (!d.success) { cuerpo.innerHTML = '<p>' + devEsc(d.message) + '</p>'; return; }
            devPedido = d.pedido;
            dibujarDevolucion();
        })
        .catch(() => { cuerpo.innerHTML = '<p>No se pudo cargar el pedido.</p>'; });
}

function dibujarDevolucion() {
    const p = devPedido;
    document.getElementById('devTitulo').textContent = 'Devolución · ' + p.folio + ' · ' + p.cliente;
    const cuerpo = document.getElementById('devCuerpo');

    if (p.bloqueado) {
        cuerpo.innerHTML = '<div class="dev-aviso">' + devEsc(p.bloqueado) + '</div><div class="dev-pie"><button class="dev-btn" onclick="cerrarDevolucion()">Cerrar</button></div>';
        return;
    }

    const filas = p.lineas.map((l, i) => `
        <tr>
            <td><strong>${devEsc(l.nombre)}</strong></td>
            <td>${l.cantidad} ${devEsc(l.unidad)} × ${devDinero(l.precio_venta)}</td>
            <td><input type="number" min="0" max="${l.cantidad}" step="0.01" value="0" data-i="${i}" class="dev-cant"></td>
            <td>
                <select data-i="${i}" class="dev-destino">
                    <option value="inventario">Regresa al inventario</option>
                    <option value="merma">A merma (ya no sirve)</option>
                </select>
            </td>
            <td class="r" id="devSub${i}">$0.00</td>
        </tr>`).join('');

    cuerpo.innerHTML = `
        <div style="font-size:.85rem; color:#64748b; margin-bottom:.7rem;">
            Total del pedido <strong>${devDinero(p.total)}</strong> · pagado <strong>${devDinero(p.pagado)}</strong>
            ${p.saldo > 0.009 ? ' · debe <strong style="color:#b45309;">' + devDinero(p.saldo) + '</strong>' : ''}
        </div>
        <div style="overflow-x:auto;">
        <table>
            <thead><tr><th>Producto</th><th>Vendido</th><th>Cantidad a devolver</th><th>¿A dónde va?</th><th class="r">Valor</th></tr></thead>
            <tbody>${filas}</tbody>
        </table>
        </div>
        <div style="margin-top:.9rem;">
            <input type="text" id="devMotivo" placeholder="Motivo (ej. llegó golpeado, se pasó de maduro...)" maxlength="300" style="width:100%;">
        </div>
        <div class="dev-resumen" id="devResumen"></div>
        <div class="dev-pie">
            <button class="dev-btn" onclick="cerrarDevolucion()">Cancelar</button>
            <button class="dev-btn ok" id="devGuardar" onclick="guardarDevolucion()"><i class="fas fa-check"></i> Registrar devolución</button>
        </div>`;

    cuerpo.querySelectorAll('.dev-cant').forEach(el => el.addEventListener('input', calcularDevolucion));
    calcularDevolucion();
}

function leerDevolucion() {
    return devPedido.lineas.map((l, i) => {
        const cant = Math.min(l.cantidad, Math.max(0, parseFloat(document.querySelector(`.dev-cant[data-i="${i}"]`).value) || 0));
        return { l, i, cant, destino: document.querySelector(`.dev-destino[data-i="${i}"]`).value };
    });
}

function calcularDevolucion() {
    const p = devPedido;
    let total = 0;
    leerDevolucion().forEach(x => {
        const sub = Math.round(x.cant * x.l.precio_venta * 100) / 100;
        total += sub;
        document.getElementById('devSub' + x.i).textContent = devDinero(sub);
    });
    total = Math.round(total * 100) / 100;
    const nuevoTotal = Math.round((p.total - total) * 100) / 100;
    const exceso = Math.max(0, Math.round((p.pagado - nuevoTotal) * 100) / 100);

    let html = `<div><span>Valor que se devuelve</span><strong>${devDinero(total)}</strong></div>
                <div><span>Nuevo total del pedido</span><strong>${devDinero(nuevoTotal)}</strong></div>`;
    if (total <= 0) {
        html += '<div class="grande"><span>Escribe la cantidad de lo que regresa el cliente</span><span></span></div>';
    } else if (exceso > 0.004) {
        html += `<div class="grande"><span>Reembolso al cliente</span><span style="color:#b91c1c;">${devDinero(exceso)}</span></div>
                 <div><span>¿Con qué se le regresa?</span>
                     <select id="devMetodo"><option value="efectivo">Efectivo (sale de caja)</option><option value="transferencia">Transferencia</option><option value="tarjeta">Tarjeta</option></select></div>`;
    } else {
        const saldoNuevo = Math.max(0, Math.round((nuevoTotal - p.pagado) * 100) / 100);
        html += `<div class="grande"><span>No hay reembolso: el saldo que debe baja a</span><span>${devDinero(saldoNuevo)}</span></div>`;
    }
    if (nuevoTotal <= 0.004 && total > 0) html += '<div style="color:#b45309; font-size:.82rem;">Se devuelve todo: el pedido quedará cancelado.</div>';
    document.getElementById('devResumen').innerHTML = html;
}

function cerrarDevolucion() { document.getElementById('modalDev').classList.remove('activo'); }

function guardarDevolucion() {
    const items = leerDevolucion().filter(x => x.cant > 0).map(x => ({ id_linea: x.l.id_linea, cantidad: x.cant, destino: x.destino }));
    if (!items.length) { toast('Escribe la cantidad de al menos un producto.', 'error'); return; }

    const boton = document.getElementById('devGuardar');
    boton.disabled = true;
    fetch("<?= base_url('devoluciones/guardar') ?>", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_pedido: devPedido.id,
            items: items,
            motivo: document.getElementById('devMotivo').value,
            metodo: (document.getElementById('devMetodo') || {}).value || 'efectivo'
        })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            toast('Devolución registrada', 'success');
            cerrarDevolucion();
            setTimeout(() => location.reload(), 900);
        } else {
            boton.disabled = false;
            toast(d.message || 'No se pudo registrar la devolución', 'error');
        }
    })
    .catch(() => { boton.disabled = false; toast('Error de conexión', 'error'); });
}
</script>

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
            toast(data.message || 'Error al actualizar el estado', 'error');
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

        data.productos.forEach((prod) => {
            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td style="padding:10px 8px;">${prod.nombre}</td>

                <td style="padding:10px 8px; text-align:center;">
                    ${parseFloat(prod.cantidad)}
                </td>

                <td style="padding:10px 8px; text-align:center;">
                    ${prod.unidad_venta || '-'}
                </td>

                <td style="padding:10px 8px; text-align:right;">
                    <input
                        type="number"
                        class="precio-editable"
                        data-id="${prod.id_producto_pedido}"
                        value="${parseFloat(prod.precio_venta).toFixed(2)}"
                        min="0"
                        step="0.01"
                        style="width:100px; padding:6px; text-align:right;"
                    >
                </td>

                <td
                    class="subtotal-editable"
                    style="padding:10px 8px; text-align:right; font-weight:600;"
                >
                    $${parseFloat(prod.subtotal).toFixed(2)}
                </td>
            `;

            tbody.appendChild(tr);
        });

    })
    .catch(() => toast('Error de conexión', 'error'));
}


function formatearFecha(fechaStr) {
    const d = new Date(fechaStr);
    return d.toLocaleDateString('es-MX', { day:'2-digit', month:'2-digit', year:'numeric' })
        + ' ' + d.toLocaleTimeString('es-MX', { hour:'2-digit', minute:'2-digit' });
}
function guardarPrecios() {
    const inputs = document.querySelectorAll('.precio-editable');

    if (inputs.length === 0) {
        toast('No hay precios para actualizar', 'error');
        return;
    }

    const precios = {};

    for (const input of inputs) {
        const precio = Number(input.value);
        const idDetalle = input.dataset.id;

        if (
            !idDetalle ||
            input.value.trim() === '' ||
            !Number.isFinite(precio) ||
            precio < 0
        ) {
            toast('Revisa los precios ingresados', 'error');
            input.focus();
            return;
        }

        precios[idDetalle] = precio;
    }

    const idPedido = currentPedidoId;
    if (!idPedido) {
        toast('No se identificó el pedido', 'error');
        return;
    }

    fetch('<?= base_url('pedido/actualizar_precios') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
    id: idPedido,
    precios: JSON.stringify(precios)
})
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            toast(data.message || 'No se pudieron guardar los precios', 'error');
            return;
        }

        toast('Precios actualizados correctamente', 'success');

        // Actualizar el total mostrado en el modal.
        const total = Number(data.total);
        const modalTotal = document.getElementById('modalTotal');

        if (modalTotal) {
            modalTotal.value = total.toFixed(2);
        }

        const totalInfo = document.querySelector(
            '#modalInfoGeneral .info-label'
        );

        if (totalInfo) {
            const items = document.querySelectorAll(
                '#modalInfoGeneral .info-item'
            );

            items.forEach(item => {
                const label = item.querySelector('.info-label');

                if (label && label.textContent.trim() === 'Total') {
                    item.querySelector('.info-value').textContent =
                        '$' + total.toFixed(2);
                }

                if (label && label.textContent.trim() === 'Restante') {
                    item.querySelector('.info-value').textContent =
                        '$' + Number(data.saldo_pendiente).toFixed(2);
                }
            });
        }

        // Recalcular los subtotales visibles.
        document.querySelectorAll('.precio-editable').forEach(input => {
            const fila = input.closest('tr');
            const cantidad = Number(
                fila.children[1].textContent.trim()
            );
            const precio = Number(input.value);
            const subtotal = cantidad * precio;

            fila.querySelector('.subtotal-editable').textContent =
                '$' + subtotal.toFixed(2);
        });

    })
    .catch(() => {
        toast('Error de conexión al guardar los precios', 'error');
    });
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
        body: `id=${currentPedidoId}&monto_pagado=${nuevoPagado}&metodo=${encodeURIComponent(document.getElementById('modalMetodo').value)}`
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
<script>
(function(){
  const URL_CAMBIOS    = "<?= base_url('admin_api/cambios') ?>";
  const URL_HISTORIAL  = "<?= base_url('admin_api/incidencias/historial') ?>";
  const URL_RESOLVER   = "<?= base_url('admin_api/incidencia/resolver') ?>";
  const URL_RESPONDER  = "<?= base_url('admin_api/incidencia/responder') ?>";
  const URL_DETALLE    = "<?= base_url('pedido/detalle') ?>";
  const META = {
    'Pedido confirmado':  ['confirmado','fa-check-circle','Confirmado'],
    'Pedido en tránsito': ['transito','fa-truck','En tránsito'],
    'Venta confirmada':   ['entregado','fa-check-double','Entregado'],
    'Pedido cancelado':   ['cancelado','fa-ban','Cancelado'],
    'Pedido':             ['pedido','fa-clock','Pedido']
  };
  const TIPOS = {
    cliente_no_responde:'Cliente no responde', direccion_incorrecta:'Dirección incorrecta',
    cliente_no_paga:'Cliente no paga', producto_danado:'Producto dañado', problema_vehiculo:'Problema con el vehículo',
    cliente_rechazo:'El cliente rechazó el pedido', entrega_fallida:'Entrega no realizada', otro:'Otro'
  };
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const folioDe = id => String(id).padStart(6, '0');
  const dinero = n => (Number(n) || 0).toLocaleString('es-MX', {style:'currency', currency:'MXN'});

  let desde = null, conocidas = new Set(), primera = true;
  let activas = [], pestana = 'activas', historialCargado = false;

  /* ---------------- Estilos ---------------- */
  const estilos = document.createElement('style');
  estilos.textContent = `
    .ia-bell{position:relative;border:0;cursor:pointer;font-family:inherit}
    .ia-bell.suena i{animation:iaRing 1s ease 2}
    @keyframes iaRing{0%,100%{transform:rotate(0)}20%{transform:rotate(-16deg)}40%{transform:rotate(14deg)}60%{transform:rotate(-9deg)}80%{transform:rotate(5deg)}}
    .ia-badge{position:absolute;top:-6px;right:-6px;min-width:20px;height:20px;padding:0 6px;border-radius:10px;background:#dc2626;color:#fff;font:700 .7rem 'Inter',sans-serif;display:none;align-items:center;justify-content:center;border:2px solid #fff;box-sizing:content-box}
    .ia-badge.on{display:inline-flex}

    .ia-toasts{position:fixed;top:14px;right:14px;z-index:10001;display:flex;flex-direction:column;gap:10px;width:340px;max-width:calc(100vw - 28px);font-family:'Inter',system-ui,sans-serif}
    .ia-toast{background:#fff;border-radius:14px;padding:12px 14px;box-shadow:0 12px 30px rgba(15,23,42,.25);border-left:5px solid #dc2626;display:flex;gap:12px;align-items:center;cursor:pointer;animation:iaEntrar .3s ease}
    .ia-toast:hover{background:#fff7f7}
    .ia-toast .ia-icono{width:38px;height:38px;border-radius:11px;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .ia-toast b{display:block;font-size:.9rem;color:#0f172a}
    .ia-toast small{color:#64748b;font-size:.78rem}
    @keyframes iaEntrar{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:none}}

    .ia-fondo{position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:10000;display:none}
    .ia-fondo.on{display:block;animation:iaFade .2s ease}
    @keyframes iaFade{from{opacity:0}to{opacity:1}}
    .ia-drawer{position:fixed;top:0;right:0;bottom:0;width:430px;max-width:100vw;background:#f8fafc;z-index:10002;box-shadow:-12px 0 36px rgba(15,23,42,.25);display:flex;flex-direction:column;transform:translateX(105%);transition:transform .28s ease;font-family:'Inter',system-ui,sans-serif;color:#1e293b}
    .ia-drawer.on{transform:none}
    .ia-d-head{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;background:#1e5631;color:#fff}
    .ia-d-head h3{margin:0;font-size:1.02rem;display:flex;gap:10px;align-items:center}
    .ia-x{background:rgba(255,255,255,.15);border:0;color:#fff;width:34px;height:34px;border-radius:10px;cursor:pointer;font-size:1rem}
    .ia-x:hover{background:rgba(255,255,255,.3)}
    .ia-tabs{display:flex;background:#fff;border-bottom:1px solid #e2e8f0}
    .ia-tab{flex:1;padding:12px;border:0;background:transparent;font:600 .86rem 'Inter',sans-serif;color:#64748b;cursor:pointer;border-bottom:3px solid transparent}
    .ia-tab.on{color:#1e5631;border-bottom-color:#1e5631}
    .ia-tab .n{display:inline-block;margin-left:6px;background:#dc2626;color:#fff;border-radius:9px;padding:0 7px;font-size:.7rem;line-height:18px}
    .ia-lista{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:12px}
    .ia-vacio{text-align:center;color:#94a3b8;padding:40px 10px;font-size:.9rem}
    .ia-vacio i{display:block;font-size:2rem;margin-bottom:10px}

    .ia-card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 10px rgba(15,23,42,.08);border-top:4px solid #dc2626}
    .ia-card.hecha{border-top-color:#16a34a}
    .ia-head{display:flex;align-items:center;gap:12px;margin-bottom:10px}
    .ia-icono{width:40px;height:40px;border-radius:12px;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
    .ia-card.hecha .ia-icono{background:#dcfce7;color:#16a34a}
    .ia-titulo b{display:block;font-size:.98rem;color:#0f172a}
    .ia-titulo small{color:#64748b;font-size:.75rem}
    .ia-datos{display:flex;flex-direction:column;gap:6px;font-size:.86rem;color:#334155}
    .ia-datos i{color:#f16b1a;width:16px;text-align:center;margin-right:4px}
    .ia-folio{display:inline-flex;align-items:center;gap:6px;background:#1e5631;color:#fff;border:0;border-radius:7px;padding:2px 10px;font:700 .85rem 'Inter',sans-serif;letter-spacing:.04em;cursor:pointer}
    .ia-folio:hover{background:#16452a;text-decoration:underline}
    .ia-ubicacion{margin-top:12px;padding:10px 12px;border-radius:12px;background:#f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;font-size:.84rem}
    .ia-mapa{display:inline-flex;align-items:center;gap:6px;background:#2563eb;color:#fff;text-decoration:none;padding:7px 12px;border-radius:9px;font-weight:600}
    .ia-mapa:hover{background:#1d4ed8}
    .ia-coords{color:#475569;font-family:monospace;font-size:.78rem}
    .ia-sin-gps{color:#b45309;font-weight:600}
    .ia-label{display:block;margin-top:12px;font-size:.78rem;font-weight:700;color:#334155}
    .ia-texto{width:100%;margin-top:4px;padding:9px 10px;border:1px solid #cbd5e1;border-radius:10px;font:inherit;font-size:.88rem;resize:vertical;box-sizing:border-box;color:#0f172a}
    .ia-texto:focus{outline:2px solid #1e5631;border-color:transparent}
    .ia-acciones{display:flex;gap:8px;margin-top:10px}
    .ia-btn{border:0;border-radius:10px;padding:9px 12px;font:600 .85rem 'Inter',system-ui,sans-serif;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px}
    .ia-btn:disabled{opacity:.55;cursor:not-allowed}
    .ia-btn.primario{flex:1;background:#1e5631;color:#fff}
    .ia-btn.primario:hover:not(:disabled){background:#16452a}
    .ia-btn.secundario{background:#f1f5f9;color:#475569}
    .ia-btn.secundario:hover:not(:disabled){background:#e2e8f0}
    .ia-error{margin-top:6px;font-size:.8rem;font-weight:600;color:#dc2626}
    .ia-error:empty{display:none}
    .ia-resp{margin-top:10px;padding:10px 12px;border-radius:10px;background:#f0fdf4;border:1px solid #bbf7d0;font-size:.86rem;white-space:pre-wrap}
    .ia-resp.sin{background:#f1f5f9;border-color:#e2e8f0;color:#64748b}
    .ia-resp small{display:block;color:#64748b;margin-top:4px}

    .ia-modal-fondo{position:fixed;inset:0;background:rgba(15,23,42,.6);z-index:10010;display:none;align-items:center;justify-content:center;padding:16px}
    .ia-modal-fondo.on{display:flex;animation:iaFade .2s ease}
    .ia-modal{background:#fff;border-radius:18px;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:0 24px 60px rgba(0,0,0,.4);font-family:'Inter',system-ui,sans-serif;color:#1e293b}
    .ia-m-head{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#1e5631;color:#fff;border-radius:18px 18px 0 0}
    .ia-m-head h3{margin:0;font-size:1.05rem}
    .ia-m-body{padding:18px 20px}
    .ia-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
    .ia-dato{background:#f8fafc;border-radius:10px;padding:10px 12px}
    .ia-dato span{display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b}
    .ia-dato b{font-size:.92rem}
    .ia-tabla{width:100%;border-collapse:collapse;font-size:.85rem}
    .ia-tabla th{text-align:left;font-size:.7rem;text-transform:uppercase;color:#64748b;padding:6px 4px;border-bottom:2px solid #e2e8f0}
    .ia-tabla td{padding:8px 4px;border-bottom:1px solid #eef2f6}
    .ia-tabla td.r,.ia-tabla th.r{text-align:right}
    .ia-total{display:flex;justify-content:space-between;margin-top:12px;font-weight:700}
    @media(max-width:520px){.ia-grid{grid-template-columns:1fr}}
  `;
  document.head.appendChild(estilos);

  /* ---------------- Campana en la barra superior ---------------- */
  const bell = document.createElement('button');
  bell.type = 'button';
  bell.className = 'btn-user ia-bell';
  bell.setAttribute('aria-label', 'Alertas de repartidores');
  bell.innerHTML = '<i class="fas fa-bell"></i> <span>Alertas</span><span class="ia-badge" id="iaBadge">0</span>';
  const acciones = document.querySelector('.barra-superior .user-actions');
  if (acciones) acciones.insertBefore(bell, acciones.firstChild); else document.body.appendChild(bell);
  const badge = bell.querySelector('#iaBadge');

  /* ---------------- Contenedores ---------------- */
  const toasts = document.createElement('div'); toasts.className = 'ia-toasts';
  const fondo  = document.createElement('div'); fondo.className = 'ia-fondo';
  const drawer = document.createElement('aside'); drawer.className = 'ia-drawer';
  drawer.innerHTML = `
    <div class="ia-d-head">
      <h3><i class="fas fa-bell"></i> Alertas de repartidores</h3>
      <button type="button" class="ia-x" aria-label="Cerrar"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="ia-tabs">
      <button type="button" class="ia-tab on" data-tab="activas"><i class="fas fa-circle-exclamation"></i> Activas<span class="n" id="iaN" style="display:none">0</span></button>
      <button type="button" class="ia-tab" data-tab="historial"><i class="fas fa-clock-rotate-left"></i> Historial</button>
    </div>
    <div class="ia-lista" id="iaListaActivas"></div>
    <div class="ia-lista" id="iaListaHistorial" style="display:none"></div>`;
  const modalFondo = document.createElement('div'); modalFondo.className = 'ia-modal-fondo';
  document.body.append(toasts, fondo, drawer, modalFondo);

  const listaActivas   = drawer.querySelector('#iaListaActivas');
  const listaHistorial = drawer.querySelector('#iaListaHistorial');
  const contadorTab    = drawer.querySelector('#iaN');

  function abrirDrawer(){ drawer.classList.add('on'); fondo.classList.add('on'); if (pestana === 'historial') cargarHistorial(); }
  function cerrarDrawer(){ drawer.classList.remove('on'); fondo.classList.remove('on'); }
  bell.onclick = () => drawer.classList.contains('on') ? cerrarDrawer() : abrirDrawer();
  fondo.onclick = cerrarDrawer;
  drawer.querySelector('.ia-x').onclick = cerrarDrawer;
  document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    if (modalFondo.classList.contains('on')) modalFondo.classList.remove('on'); else cerrarDrawer();
  });

  drawer.querySelectorAll('.ia-tab').forEach(t => t.onclick = () => {
    pestana = t.dataset.tab;
    drawer.querySelectorAll('.ia-tab').forEach(x => x.classList.toggle('on', x === t));
    listaActivas.style.display   = pestana === 'activas' ? '' : 'none';
    listaHistorial.style.display = pestana === 'historial' ? '' : 'none';
    if (pestana === 'historial') cargarHistorial();
  });

  /* ---------------- Utilidades de tarjeta ---------------- */
  const htmlMapa = i => (i.lat && i.lng)
    ? `<a class="ia-mapa" target="_blank" rel="noopener" href="https://www.google.com/maps?q=${encodeURIComponent(i.lat)},${encodeURIComponent(i.lng)}"><i class="fas fa-location-dot"></i> Ver en el mapa</a>`
    : `<span class="ia-sin-gps"><i class="fas fa-location-crosshairs"></i> Sin ubicación GPS</span>`;

  const htmlDatos = i => `
    <div class="ia-datos">
      <div><i class="fas fa-motorcycle"></i> ${esc(i.repartidor)}</div>
      ${i.id_pedido ? `<div><i class="fas fa-box"></i> Pedido <button type="button" class="ia-folio" data-pedido="${esc(i.id_pedido)}" title="Ver detalle del pedido">${folioDe(i.id_pedido)} <i class="fas fa-arrow-up-right-from-square" style="font-size:.7rem"></i></button></div>` : ''}
      ${i.cliente && i.cliente.trim() ? `<div><i class="fas fa-user"></i> ${esc(i.cliente)}</div>` : ''}
      ${i.direccion ? `<div><i class="fas fa-map-location-dot"></i> ${esc(i.direccion)}</div>` : ''}
    </div>`;

  // Un solo manejador para todos los folios clicables (activas, historial y avisos).
  drawer.addEventListener('click', e => {
    const b = e.target.closest('.ia-folio');
    if (b) abrirDetalle(b.dataset.pedido);
  });

  /* ---------------- Detalle del pedido ---------------- */
  function cerrarModal(){ modalFondo.classList.remove('on'); }
  modalFondo.onclick = e => { if (e.target === modalFondo) cerrarModal(); };

  async function abrirDetalle(id){
    modalFondo.innerHTML = `<div class="ia-modal"><div class="ia-m-head"><h3>Pedido ${folioDe(id)}</h3><button type="button" class="ia-x" aria-label="Cerrar"><i class="fas fa-xmark"></i></button></div><div class="ia-m-body"><div class="ia-vacio"><i class="fas fa-spinner fa-spin"></i>Cargando detalle...</div></div></div>`;
    modalFondo.classList.add('on');
    modalFondo.querySelector('.ia-x').onclick = cerrarModal;
    const cuerpo = modalFondo.querySelector('.ia-m-body');
    try {
      const r = await fetch(URL_DETALLE, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({id})});
      const d = await r.json();
      if (!d.success) throw new Error(d.message || 'No se pudo cargar');
      const p = d.pedido, total = Number(p.total) || 0, pagado = Number(p.monto_pagado) || 0;
      const filas = (d.productos || []).map(x => `
        <tr><td>${esc(x.nombre)}</td><td class="r">${esc(x.cantidad)} ${esc(x.unidad_venta || '')}</td><td class="r">${dinero(x.precio_venta)}</td><td class="r">${dinero(x.subtotal)}</td></tr>`).join('');
      cuerpo.innerHTML = `
        <div class="ia-grid">
          <div class="ia-dato"><span>Estado</span><b>${esc(p.estado_actual)}</b></div>
          <div class="ia-dato"><span>Fecha</span><b>${esc(p.fecha)}</b></div>
          <div class="ia-dato"><span>Cliente</span><b>${esc(p.nombre_cliente || 'Sin cliente')}</b></div>
          <div class="ia-dato"><span>Repartidor</span><b>${esc(p.nombre_repartidor || 'Sin asignar')}</b></div>
          <div class="ia-dato"><span>Entrega</span><b>${esc(p.tipo_entrega || '-')}</b></div>
          <div class="ia-dato"><span>Tipo de pago</span><b>${esc(p.tipo_pago || '-')}</b></div>
        </div>
        <table class="ia-tabla">
          <thead><tr><th>Producto</th><th class="r">Cantidad</th><th class="r">Precio</th><th class="r">Subtotal</th></tr></thead>
          <tbody>${filas || '<tr><td colspan="4" class="ia-vacio">Sin productos</td></tr>'}</tbody>
        </table>
        <div class="ia-total"><span>Total</span><span>${dinero(total)}</span></div>
        <div class="ia-total" style="font-weight:600;color:#475569"><span>Pagado</span><span>${dinero(pagado)}</span></div>
        <div class="ia-total" style="color:#f16b1a"><span>Por cobrar</span><span>${dinero(Math.max(0, total - pagado))}</span></div>`;
    } catch (e) {
      cuerpo.innerHTML = `<div class="ia-vacio"><i class="fas fa-circle-exclamation"></i>No se pudo cargar el detalle: ${esc(e.message)}</div>`;
    }
  }

  /* ---------------- Alertas activas ---------------- */
  const tarjetas = new Map(); // se crean una vez para no perder el texto que se escribe

  function crearTarjeta(i){
    const card = document.createElement('article');
    card.className = 'ia-card';
    card.innerHTML = `
      <div class="ia-head">
        <span class="ia-icono"><i class="fas fa-triangle-exclamation"></i></span>
        <div class="ia-titulo"><b>${esc(TIPOS[i.tipo] || i.tipo)}</b><small>${esc(i.fecha)}</small></div>
      </div>
      ${htmlDatos(i)}
      <div class="ia-ubicacion">${htmlMapa(i)}</div>
      <label class="ia-label">Respuesta para el repartidor</label>
      <textarea class="ia-texto" rows="3" maxlength="1000" placeholder="Ej. Llama al cliente, la dirección correcta es..."></textarea>
      <div class="ia-acciones">
        <button type="button" class="ia-btn primario btn-enviar"><i class="fas fa-paper-plane"></i> Enviar respuesta</button>
        <button type="button" class="ia-btn secundario btn-cerrar"><i class="fas fa-xmark"></i> Cerrar</button>
      </div>
      <div class="ia-error"></div>`;

    const txt = card.querySelector('textarea');
    const err = card.querySelector('.ia-error');
    const botones = card.querySelectorAll('.ia-btn');
    const bloquear = on => botones.forEach(b => b.disabled = on);

    async function enviar(url, opciones, falla){
      err.textContent = ''; bloquear(true);
      try {
        const r = await fetch(url, opciones);
        const d = await r.json().catch(() => ({}));
        if (!r.ok || !d.success) throw new Error(d.message || ('Error ' + r.status));
        tarjetas.delete(i.id); card.remove();
        historialCargado = false;
        tick();
      } catch (e) {
        err.textContent = falla + ': ' + e.message; bloquear(false);
      }
    }

    card.querySelector('.btn-enviar').onclick = () => {
      const texto = txt.value.trim();
      if (!texto) { err.textContent = 'Escribe una respuesta antes de enviar.'; return; }
      enviar(URL_RESPONDER + '/' + i.id, {
        method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams({respuesta:texto})
      }, 'No se envió');
    };
    card.querySelector('.btn-cerrar').onclick = () => enviar(URL_RESOLVER + '/' + i.id, {method:'POST'}, 'No se cerró');
    return card;
  }

  function actualizarContadores(){
    const n = activas.length;
    badge.textContent = n > 99 ? '99+' : n;
    badge.classList.toggle('on', n > 0);
    contadorTab.textContent = n;
    contadorTab.style.display = n > 0 ? '' : 'none';
    if (!n && !listaActivas.querySelector('.ia-card')) {
      listaActivas.innerHTML = '<div class="ia-vacio"><i class="fas fa-circle-check"></i>No hay alertas activas.</div>';
    }
  }

  function avisoNuevo(i){
    const t = document.createElement('div');
    t.className = 'ia-toast';
    t.innerHTML = `<span class="ia-icono"><i class="fas fa-triangle-exclamation"></i></span>
      <div><b>${esc(TIPOS[i.tipo] || i.tipo)}</b><small>${esc(i.repartidor)}${i.id_pedido ? ' · Pedido ' + folioDe(i.id_pedido) : ''}</small></div>`;
    t.onclick = () => { t.remove(); abrirDrawer(); };
    toasts.appendChild(t);
    setTimeout(() => t.remove(), 9000);
  }

  function pitido(){
    try { const a = new AudioContext(), o = a.createOscillator(); o.connect(a.destination); o.frequency.value = 880; o.start(); o.stop(a.currentTime + .25); } catch(e){}
  }

  function pintarIncidencias(lista){
    const nuevas = lista.filter(i => !conocidas.has(i.id));
    nuevas.forEach(i => conocidas.add(i.id));
    activas = lista;

    if (nuevas.length && !primera) {
      pitido();
      nuevas.forEach(avisoNuevo);
      bell.classList.add('suena'); setTimeout(() => bell.classList.remove('suena'), 2200);
    }
    primera = false;

    const ids = new Set(lista.map(i => i.id));
    tarjetas.forEach((card, id) => { if (!ids.has(id)) { card.remove(); tarjetas.delete(id); } });
    if (lista.length) listaActivas.querySelector('.ia-vacio')?.remove();
    lista.forEach(i => {
      if (tarjetas.has(i.id)) return;
      const card = crearTarjeta(i);
      tarjetas.set(i.id, card);
      listaActivas.appendChild(card);
    });
    actualizarContadores();
  }

  /* ---------------- Historial ---------------- */
  async function cargarHistorial(){
    if (historialCargado) return;
    listaHistorial.innerHTML = '<div class="ia-vacio"><i class="fas fa-spinner fa-spin"></i>Cargando historial...</div>';
    try {
      const r = await fetch(URL_HISTORIAL);
      const d = await r.json();
      if (!r.ok || !d.success) throw new Error(d.message || ('Error ' + r.status));
      historialCargado = true;
      listaHistorial.innerHTML = d.data.length ? d.data.map(i => `
        <article class="ia-card hecha">
          <div class="ia-head">
            <span class="ia-icono"><i class="fas fa-circle-check"></i></span>
            <div class="ia-titulo"><b>${esc(TIPOS[i.tipo] || i.tipo)}</b><small>${esc(i.fecha)}</small></div>
          </div>
          ${htmlDatos(i)}
          <div class="ia-ubicacion">${htmlMapa(i)}</div>
          ${i.respuesta
            ? `<div class="ia-resp"><b>Tu respuesta</b><br>${esc(i.respuesta)}<small>${esc(i.respondido_at || '')}</small></div>`
            : `<div class="ia-resp sin">Cerrada sin respuesta</div>`}
        </article>`).join('')
        : '<div class="ia-vacio"><i class="fas fa-inbox"></i>Aún no hay alertas atendidas.</div>';
    } catch (e) {
      listaHistorial.innerHTML = `<div class="ia-vacio"><i class="fas fa-circle-exclamation"></i>No se pudo cargar: ${esc(e.message)}</div>`;
    }
  }

  /* ---------------- Cambios de pedidos ---------------- */
  function actualizarFila(p){
    const fila = document.getElementById('fila-' + p.id);
    if (!fila || fila.dataset.estado === p.estado_actual) return;
    const [clase, icono, label] = META[p.estado_actual] || META['Pedido'];
    fila.dataset.estado = p.estado_actual;
    const b = fila.querySelector('.estado-badge');
    if (b) {
      b.className = 'estado-badge ' + clase;
      b.innerHTML = `<i class="fas ${icono}"></i> ${label} <i class="fas fa-chevron-down" style="font-size:0.6rem;"></i>`;
    }
    if (typeof refreshProgressForPedido === 'function') refreshProgressForPedido(p.id, p.estado_actual);
    if (typeof actualizarEstadisticas === 'function') actualizarEstadisticas();
    if (typeof toast === 'function') toast(`Pedido #${p.id}: ${label}${p.repartidor ? ' (' + p.repartidor + ')' : ''}`, 'success');
  }

  async function tick(){
    try {
      const r = await fetch(URL_CAMBIOS + (desde ? '?desde=' + encodeURIComponent(desde) : ''));
      const d = await r.json();
      desde = d.ahora;
      d.pedidos.forEach(actualizarFila);
      pintarIncidencias(d.incidencias);
    } catch(e) {}
  }
  actualizarContadores();
  tick();
  setInterval(tick, 5000);
})();
</script>
</body>
</html>
