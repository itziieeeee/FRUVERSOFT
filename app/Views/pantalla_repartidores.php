<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVER · Gestión de Repartidores</title>
    <link rel="stylesheet" href="<?= base_url('css/repartidorestilo.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>

<header>
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Fruver Logo">
        </div>
        <div class="buscador-header">
            <input type="text" id="searchRepartidor" placeholder="Buscar repartidor por nombre o ubicación...">
        </div>
        <div class="user-actions">
            <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> Admin</a>
            <a href="menusolo" class="btn-user"><i class="fas fa-arrow-right-from-bracket"></i> Regresar</a>
        </div>
    </div>
    <nav class="menu-navegacion">
        <a href="pantalla_ventas" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="pantalla_pedidos" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="inventario" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="pantalla_clientes" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="pantalla_repartidores" class="nav-link activo"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="pantalla_productos" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </nav>
</header>

<div class="main-container">
    <div class="titulo-seccion-principal">
        <i class="fa-solid fa-dolly"></i>
        <span>Gestión de Repartidores</span>
    </div>

    <!-- Filtros y ordenamiento -->
    <div class="filtros-bar">
        <div class="filtros-group">
            <button class="filtro-btn active" data-filter="todos">Todos</button>
            <button class="filtro-btn" data-filter="libre">Libres</button>
            <button class="filtro-btn" data-filter="asignado">Asignados</button>
        </div>
        <div class="orden-group">
            <label><i class="fas fa-sort"></i> Ordenar:</label>
            <select id="ordenarSelect">
                <option value="nombre">Nombre A-Z</option>
                <option value="pedidos">Más pedidos</option>
                <option value="libre">Menos pedidos</option>
            </select>
        </div>
    </div>

    <!-- Grid de cards -->
    <div class="repartidores-grid" id="repartidoresGrid">
        <?php if(!empty($repartidores)): ?>
            <?php foreach($repartidores as $r): ?>
            <?php
            $pedidos    = $pedidosPorRepartidor[$r['id']] ?? [];
            $entregados = $entregadosPorRepartidor[$r['id']] ?? [];
            $asignado   = count($pedidos) > 0;
            ?>
            <div class="repartidor-card" data-id="<?= $r['id'] ?>" data-nombre="<?= strtolower($r['nombre'] . ' ' . $r['ap_p'] . ' ' . $r['ap_m']) ?>" data-ubicacion="<?= strtolower($r['direccion'] ?? '') ?>" data-estado="<?= $asignado ? 'asignado' : 'libre' ?>" data-pedidos="<?= count($pedidos) ?>" data-entregados="<?= count($entregados) ?>">
                <div class="card-header">
                    <div class="card-foto">
                        <?php if(!empty($r['foto'])): ?>
                            <img src="<?= base_url('uploads/repartidores/' . $r['foto']) ?>" alt="foto">
                        <?php else: ?>
                            <div class="foto-placeholder">
                                <?= strtoupper(substr($r['nombre'],0,1) . substr($r['ap_p'],0,1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-info">
                        <h3><?= $r['nombre'] . ' ' . $r['ap_p'] . ' ' . $r['ap_m'] ?></h3>
                        <p><i class="fas fa-phone"></i> <?= $r['tel'] ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?= $r['direccion'] ?? 'Sin dirección' ?></p>
                    </div>
                    <div class="card-estado">
                        <?php if($asignado): ?>
                            <span class="badge estado-asignado"><i class="fas fa-truck"></i> Asignado</span>
                        <?php else: ?>
                            <span class="badge estado-libre"><i class="fas fa-check-circle"></i> Libre</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-stats">
                    <div class="stat">
                        <span class="stat-value"><?= count($pedidos) ?></span>
                        <span class="stat-label">Pedidos activos</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value"><?= count($entregados) ?></span>
                        <span class="stat-label">Entregas completadas</span>
                    </div>
                </div>
                <?php if(!empty($r['notas'])): ?>
                    <div class="card-notas">
                        <i class="fas fa-sticky-note"></i> <?= $r['notas'] ?>
                    </div>
                <?php endif; ?>
                <div class="card-actions">
                    <button class="btn-ver-pedidos" onclick="verPedidos(<?= $r['id'] ?>)">
                        <i class="fas fa-box"></i> Ver pedidos (<?= count($pedidos) ?>)
                    </button>
                    <button class="btn-editar" onclick="abrirEditar(
                        <?= $r['id'] ?>,
                        '<?= addslashes($r['nombre']) ?>',
                        '<?= addslashes($r['ap_p']) ?>',
                        '<?= addslashes($r['ap_m']) ?>',
                        '<?= addslashes($r['tel']) ?>',
                        '<?= addslashes($r['direccion'] ?? '') ?>',
                        '<?= addslashes($r['notas'] ?? '') ?>'
                    )">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="btn-eliminar" onclick="confirmarEliminar(<?= $r['id'] ?>, '<?= addslashes($r['nombre'] . ' ' . $r['ap_p']) ?>')">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">No hay repartidores registrados</div>
        <?php endif; ?>
    </div>


    <button class="btn-flotante" onclick="abrirModal()">
        <i class="fas fa-plus"></i>
        <span>Agregar Repartidor</span>
    </button>

    <div id="alertas-container"></div>

    <!-- Modal Nuevo Repartidor -->
    <div id="modalRepartidor" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-user-plus"></i> Registrar Repartidor</h3>
            <form id="formRepartidor" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group"><label>Nombre</label><input type="text" name="nombre" required></div>
                    <div class="form-group"><label>Apellido Paterno</label><input type="text" name="ap_p" required></div>
                    <div class="form-group"><label>Apellido Materno</label><input type="text" name="ap_m"></div>
                    <div class="form-group"><label>Teléfono</label><input type="text" name="tel" required></div>
                    <div class="form-group full"><label>Dirección</label><input type="text" name="direccion"></div>
                    <div class="form-group full"><label>Notas</label><input type="text" name="notas"></div>
                    <div class="form-group full"><label>Foto</label><input type="file" name="foto" accept="image/*" onchange="previsualizarFoto(this, 'preview-nuevo')">
                    <img id="preview-nuevo" src="" alt="" class="preview-img"></div>
                </div>
                <div class="modal-buttons"><button type="submit" class="btn-guardar">Guardar</button><button type="button" onclick="cerrarModal()" class="btn-cancelar">Cancelar</button></div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Repartidor -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-user-edit"></i> Editar Repartidor</h3>
            <form id="formEditar" enctype="multipart/form-data">
                <input type="hidden" id="edit-id">
                <div class="form-grid">
                    <div class="form-group"><label>Nombre</label><input type="text" id="edit-nombre" required></div>
                    <div class="form-group"><label>Apellido Paterno</label><input type="text" id="edit-ap_p" required></div>
                    <div class="form-group"><label>Apellido Materno</label><input type="text" id="edit-ap_m"></div>
                    <div class="form-group"><label>Teléfono</label><input type="text" id="edit-tel" required></div>
                    <div class="form-group full"><label>Dirección</label><input type="text" id="edit-direccion"></div>
                    <div class="form-group full"><label>Notas</label><input type="text" id="edit-notas"></div>
                    <div class="form-group full"><label>Foto (dejar vacío para no cambiar)</label><input type="file" id="edit-foto" accept="image/*" onchange="previsualizarFoto(this, 'preview-editar')">
                    <img id="preview-editar" src="" alt="" class="preview-img"></div>
                </div>
                <div class="modal-buttons"><button type="submit" class="btn-guardar">Guardar cambios</button><button type="button" onclick="cerrarEditar()" class="btn-cancelar">Cancelar</button></div>
            </form>
        </div>
    </div>

  <!-- Modal Confirmación Eliminar -->
<div id="modalConfirmarEliminar" class="modal">
    <div class="modal-content modal-confirm">
        <div class="icono-alerta">
            <i class="fas fa-trash-alt"></i>
        </div>
        <h3>¿Eliminar repartidor?</h3>
        <p id="confirmarMensaje">¿Estás seguro de que deseas eliminar a este repartidor?<br>Esta acción no se puede deshacer.</p>
        <div class="modal-buttons-confirm">
            <button id="btnConfirmarSi" class="btn-confirm-yes">
                <i class="fas fa-trash"></i> Sí, eliminar
            </button>
            <button id="btnConfirmarNo" class="btn-confirm-no">
                <i class="fas fa-times"></i> Cancelar
            </button>
        </div>
    </div>
</div>

    <!-- Modal Pedidos del Repartidor -->
    <div id="modalPedidos" class="modal">
        <div class="modal-content modal-pedidos">
            <h3><i class="fas fa-box"></i> Pedidos del Repartidor</h3>
            <div id="listaPedidos"></div>
            <button onclick="cerrarModalPedidos()" class="btn-cerrar">Cerrar</button>
        </div>
    </div>
</div>

<script>
const pedidosPorRepartidor    = <?= json_encode($pedidosPorRepartidor) ?>;
const entregadosPorRepartidor = <?= json_encode($entregadosPorRepartidor) ?>;
let pendingDeleteId = null;
let pendingDeleteName = null;

function mostrarAlerta(mensaje, tipo) {
    const container = document.getElementById('alertas-container');
    const alertDiv  = document.createElement('div');
    alertDiv.className = `alert-msg alert-${tipo}`;
    alertDiv.innerHTML = `<i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${mensaje}`;
    container.appendChild(alertDiv);
    setTimeout(() => {
        alertDiv.style.opacity   = '0';
        alertDiv.style.transform = 'translateX(100%)';
        setTimeout(() => alertDiv.remove(), 300);
    }, 4000);
}

function confirmarEliminar(id, nombre) {
    const pedidosActivos = pedidosPorRepartidor[id] || [];
    if (pedidosActivos.length > 0) {
        mostrarAlerta(`No puedes eliminar a ${nombre}: tiene ${pedidosActivos.length} pedido(s) activo(s)`, 'error');
        return;
    }
    pendingDeleteId = id;
    pendingDeleteName = nombre;
    document.getElementById('confirmarMensaje').innerHTML = `¿Estás seguro de que deseas eliminar a <strong>${nombre}</strong>?<br>Esta acción no se puede deshacer.`;
    document.getElementById('modalConfirmarEliminar').style.display = 'flex';
}

document.getElementById('btnConfirmarSi')?.addEventListener('click', function() {
    if (pendingDeleteId) {
        ejecutarEliminacion(pendingDeleteId, pendingDeleteName);
    }
    document.getElementById('modalConfirmarEliminar').style.display = 'none';
    pendingDeleteId = null;
    pendingDeleteName = null;
});

document.getElementById('btnConfirmarNo')?.addEventListener('click', function() {
    document.getElementById('modalConfirmarEliminar').style.display = 'none';
    pendingDeleteId = null;
    pendingDeleteName = null;
});

function ejecutarEliminacion(id, nombre) {
    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    fetch(`<?= base_url('FRUVER/eliminarrepartidor') ?>/${id}`, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) { mostrarAlerta(`Repartidor ${nombre} eliminado`, 'success'); setTimeout(() => location.reload(), 1500); }
        else mostrarAlerta('Error al eliminar', 'error');
    })
    .catch(() => mostrarAlerta('Error de conexión', 'error'));
}

function filtrarYOrdenar() {
    const searchTerm   = document.getElementById('searchRepartidor').value.toLowerCase();
    const filterEstado = document.querySelector('.filtro-btn.active')?.dataset.filter || 'todos';
    const orden        = document.getElementById('ordenarSelect').value;
    const cards        = Array.from(document.querySelectorAll('.repartidor-card'));
    cards.forEach(card => {
        const matchesSearch  = card.dataset.nombre.includes(searchTerm) || card.dataset.ubicacion.includes(searchTerm);
        const matchesFilter  = filterEstado === 'todos' || card.dataset.estado === filterEstado;
        card.style.display   = matchesSearch && matchesFilter ? 'block' : 'none';
    });
    const visibles = cards.filter(c => c.style.display !== 'none');
    visibles.sort((a, b) => {
        if (orden === 'nombre')  return a.dataset.nombre.localeCompare(b.dataset.nombre);
        if (orden === 'pedidos') return parseInt(b.dataset.pedidos) - parseInt(a.dataset.pedidos);
        if (orden === 'libre')   return parseInt(a.dataset.pedidos) - parseInt(b.dataset.pedidos);
        return 0;
    });
    const grid = document.getElementById('repartidoresGrid');
    visibles.forEach(card => grid.appendChild(card));
}

document.getElementById('searchRepartidor')?.addEventListener('input', filtrarYOrdenar);
document.getElementById('ordenarSelect')?.addEventListener('change', filtrarYOrdenar);
document.querySelectorAll('.filtro-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        filtrarYOrdenar();
    });
});

function verPedidos(idRepartidor) {
    const pedidosActivos    = pedidosPorRepartidor[idRepartidor]    || [];
    const pedidosEntregados = entregadosPorRepartidor[idRepartidor] || [];
    const coloresEstado = { 'pedido en tránsito': '#e67e22', 'pedido confirmado': '#2980b9', 'pedido a crédito': '#8e44ad', 'pedido pagado': '#27ae60' };
    let htmlActivos = '';
    if (pedidosActivos.length === 0) htmlActivos = '<div class="no-pedidos">No hay pedidos activos</div>';
    else pedidosActivos.forEach(p => {
        const color = coloresEstado[p.estado_actual?.toLowerCase()] || '#555';
        const dir   = p.calle ? `${p.calle} #${p.numero}, ${p.colonia || ''}, ${p.municipio || ''}` : 'Sin dirección';
        htmlActivos += `<div class="pedido-item"><div class="pedido-header"><strong>#${p.id}</strong><span class="pedido-estado" style="background:${color}20; color:${color}">${p.estado_actual || 'Pendiente'}</span></div><div class="pedido-cliente"><i class="fas fa-user"></i> ${p.cliente_nombre || 'Sin cliente'} ${p.cliente_ap || ''}</div><div class="pedido-direccion"><i class="fas fa-location-dot"></i> ${dir}</div><div class="pedido-total"><i class="fas fa-dollar-sign"></i> Total: $${parseFloat(p.total).toFixed(2)}</div>${p.estado_actual === 'Pedido en tránsito' ? `<button class="btn-entregar" onclick="marcarEntregado(${p.id}, ${idRepartidor})"><i class="fas fa-check-circle"></i> Marcar como entregado</button>` : ''}</div>`;
    });
    let htmlEntregados = '';
    if (pedidosEntregados.length === 0) htmlEntregados = '<div class="no-pedidos">No hay pedidos entregados</div>';
    else pedidosEntregados.forEach(p => { htmlEntregados += `<div class="pedido-item entregado"><div class="pedido-header"><strong>#${p.id}</strong><span class="pedido-estado" style="background:#27ae6020; color:#27ae60"><i class="fas fa-check-circle"></i> ${p.estado_actual}</span></div><div class="pedido-cliente"><i class="fas fa-user"></i> ${p.cliente_nombre || 'Sin cliente'} ${p.cliente_ap || ''}</div><div class="pedido-total"><i class="fas fa-dollar-sign"></i> Total: $${parseFloat(p.total).toFixed(2)}</div></div>`; });
    document.getElementById('listaPedidos').innerHTML = `<div class="pedidos-tabs"><button class="tab-btn active" onclick="mostrarTab('activos', event)">Activos (${pedidosActivos.length})</button><button class="tab-btn" onclick="mostrarTab('entregados', event)">Entregados (${pedidosEntregados.length})</button></div><div id="tab-activos" class="tab-content active">${htmlActivos}</div><div id="tab-entregados" class="tab-content">${htmlEntregados}</div>`;
    document.getElementById('modalPedidos').style.display = 'flex';
}

function mostrarTab(tab, event) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(`tab-${tab}`).classList.add('active');
    event.target.classList.add('active');
}

function marcarEntregado(pedidoId, repartidorId) {
    const formData = new FormData();
    formData.append('id', pedidoId);
    formData.append('estado', 'Venta confirmada');
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    fetch(`<?= base_url('Status/cambiar') ?>`, { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => { if (data.success) { mostrarAlerta('¡Pedido marcado como entregado!', 'success'); setTimeout(() => window.location.reload(), 1500); } else { mostrarAlerta('Error al actualizar el pedido', 'error'); } });
}

function cerrarModalPedidos() { document.getElementById('modalPedidos').style.display = 'none'; }
function abrirModal()  { document.getElementById('modalRepartidor').style.display = 'flex'; }
function cerrarModal() { document.getElementById('modalRepartidor').style.display = 'none'; }
function abrirEditar(id, nombre, ap_p, ap_m, tel, direccion, notas) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nombre').value = nombre;
    document.getElementById('edit-ap_p').value = ap_p;
    document.getElementById('edit-ap_m').value = ap_m;
    document.getElementById('edit-tel').value = tel;
    document.getElementById('edit-direccion').value = direccion;
    document.getElementById('edit-notas').value = notas;
    document.getElementById('modalEditar').style.display = 'flex';
}
function cerrarEditar() { document.getElementById('modalEditar').style.display = 'none'; }
function previsualizarFoto(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) { const reader = new FileReader(); reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; }; reader.readAsDataURL(input.files[0]); }
}
document.getElementById('formRepartidor')?.addEventListener('submit', function(e) { e.preventDefault(); fetch("<?= base_url('FRUVER/guardarrepartidor') ?>", { method: 'POST', body: new FormData(this) }).then(r => r.json()).then(data => { if (data.success) { cerrarModal(); mostrarAlerta('¡Repartidor guardado!', 'success'); setTimeout(() => location.reload(), 1500); } else mostrarAlerta('Error al guardar', 'error'); }); });
document.getElementById('formEditar')?.addEventListener('submit', function(e) { e.preventDefault(); const id = document.getElementById('edit-id').value; const formData = new FormData(); formData.append('nombre', document.getElementById('edit-nombre').value); formData.append('ap_p', document.getElementById('edit-ap_p').value); formData.append('ap_m', document.getElementById('edit-ap_m').value); formData.append('tel', document.getElementById('edit-tel').value); formData.append('direccion', document.getElementById('edit-direccion').value); formData.append('notas', document.getElementById('edit-notas').value); const fotoInput = document.getElementById('edit-foto'); if (fotoInput.files[0]) formData.append('foto', fotoInput.files[0]); fetch(`<?= base_url('FRUVER/editarrepartidor') ?>/${id}`, { method: 'POST', body: formData }).then(r => r.json()).then(data => { if (data.success) { cerrarEditar(); mostrarAlerta('¡Actualizado!', 'success'); setTimeout(() => location.reload(), 1500); } else mostrarAlerta('Error al actualizar', 'error'); }); });
</script>
</body>
</html>