<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
<link rel="stylesheet" href="<?= base_url('css/existencias.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<style>
       .btn-eliminar  {
        background: #ffe8e8; color: #dc2626; text-decoration: none !important;
        border: none; font-family: inherit; font-size: 0.7rem; font-weight: 600;
        flex: 1; padding: 0.5rem 0.2rem; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        gap: 6px; cursor: pointer; transition: all 0.2s ease;
    }
    .btn-eliminar:hover { background: #ffd4d4; transform: translateY(-2px); text-decoration: none; }
    .btn-eliminar {
    background: #ffe8e8;
    color: #dc2626;
}
.btn-eliminar:hover {
    background: #ffd4d4;
    transform: translateY(-2px);
}
</style>
<header>
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="FRUVER">
        </div>
        <div class="buscador">
            <form method="GET" id="formBusqueda">
                <input type="text" name="q" placeholder="Buscar productos..."
                    value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                <input type="hidden" name="orden" id="ordenHidden"
                    value="<?= isset($_GET['orden']) ? htmlspecialchars($_GET['orden']) : '' ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <a href="<?= base_url('alta_producto') ?>" class="btn-nuevo-producto">
            <i class="fas fa-plus-circle"></i> Nuevo Producto
        </a>
        <div class="filtros-group">
            <select name="orden" class="select-orden" id="ordenSelect" onchange="aplicarOrden()">
                <option value="">Ordenar por defecto</option>
                <option value="stock_mayor" <?= (isset($_GET['orden']) && $_GET['orden'] == 'stock_mayor') ? 'selected' : '' ?>>
                    Mayor existencias
                </option>
            </select>
        </div>
        <div class="user-actions">
            <a href="<?= base_url('admin') ?>" class="btn-user"><i class="fas fa-user-shield"></i> Admin</a>
            <a href="menusolo" class="btn-user"><i class="fas fa-sign-out-alt"></i> Regresar</a>
        </div>
    </div>
</header>

<nav class="menu-navegacion">
    <div class="nav-links">
        <a href="<?= base_url('pantalla_ventas') ?>"      class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="<?= base_url('pantalla_pedidos') ?>"     class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="<?= base_url('pantalla_inventario') ?>"  class="nav-link activo"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="<?= base_url('pantalla_clientes') ?>"    class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="<?= base_url('pantalla_repartidores') ?>" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="<?= base_url('pantalla_productos') ?>"   class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </div>
</nav>

<div class="contenido-principal">
    <div class="titulo-seccion">
        <div class="titulo-texto">
            <i class="fas fa-boxes"></i>
            <span>Gestión de Inventario</span>
        </div>
    </div>

    <div class="productos-grid" id="productosGrid">
        <?php foreach($productos as $p):
            $existencias_totales   = $p['existencias_totales']   ?? 0;
            $existencias_bloqueadas = $p['existencias_bloqueadas'] ?? 0;
            $existencias_venta     = $existencias_totales - $existencias_bloqueadas;
        ?>
        <div class="producto-card"
             data-id="<?= $p['id_producto'] ?>"
             data-total="<?= $existencias_totales ?>"
             data-bloqueadas="<?= $existencias_bloqueadas ?>">

            <div class="producto-nombre"><?= htmlspecialchars($p['nombre']) ?></div>
            <div class="producto-descripcion"><?= htmlspecialchars($p['descripcion']) ?></div>

            <div class="precio-unidad">
                <p>
                    <i class="fas fa-ruler-combined"></i> Unidad de medida:
                    <span class="unidad-medida">
                        <i class="fas fa-ruler"></i> <?= htmlspecialchars($p['unidad_medida'] ?? 'Unidad') ?>
                    </span>
                </p>
            </div>

            <div class="inventario-panel">
                <div class="inventario-item">
                    <span class="inventario-label"><i class="fas fa-warehouse"></i> Existencias totales:</span>
                    <span class="inventario-valor total" id="total-<?= $p['id_producto'] ?>"><?= $existencias_totales ?></span>
                </div>
                <div class="inventario-item">
                    <span class="inventario-label"><i class="fas fa-lock"></i> Bloqueadas:</span>
                    <span class="inventario-valor bloqueado" id="bloqueadas-<?= $p['id_producto'] ?>"><?= $existencias_bloqueadas ?></span>
                </div>
                <div class="inventario-item">
                    <span class="inventario-label"><i class="fas fa-check-circle"></i> Disponibles para venta:</span>
                    <span class="inventario-valor disponible" id="venta-<?= $p['id_producto'] ?>"><?= $existencias_venta ?></span>
                </div>
            </div>

            <div class="producto-acciones">
                <button class="btn-accion btn-editar" onclick="editarProducto(<?= $p['id_producto'] ?>)">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button class="btn-eliminar" onclick="confirmarEliminar(<?= $p['id_producto'] ?>, '<?= addslashes(htmlspecialchars($p['nombre'])) ?>')">
                    <i class="fas fa-trash-alt"></i> Eliminar
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?= $pager->links('default', 'mi_paginacion') ?>
</div>

<!-- MODAL EDITAR -->
<div id="modalEditar" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Editar Producto</h3>
            <button class="modal-close-btn" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="edit_id">
            <div class="modal-field">
                <label><i class="fas fa-tag"></i> Nombre del producto</label>
               <input type="text" id="edit_nombre" placeholder="Nombre del producto">
            </div>
            <div class="modal-field">
                <label><i class="fas fa-align-left"></i> Descripción</label>
                <textarea id="edit_descripcion" rows="3" placeholder="Descripción detallada..."></textarea>
            </div>
            <div class="modal-field"
                <label><i class="fas fa-ruler"></i> Unidad de medida</label>
                <input type="text" id="edit_unidad" placeholder="Ej: Kg, Litro, Unidad">
            </div>
            <div class="modal-row">
                <div class="modal-field">
                    <label><i class="fas fa-boxes"></i> Existencias totales</label>
                    <input type="number" id="edit_total" min="0" value="0">
                </div>
                <div class="modal-field">
                    <label><i class="fas fa-lock"></i> Existencias bloqueadas</label>
                    <input type="number" id="edit_bloqueadas" min="0" value="0">
                </div>
            </div>
            <div id="edit_error" class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <span></span>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="cerrarModal()"><i class="fas fa-times"></i> Cancelar</button>
                <button class="btn-save" onclick="guardarCambios()"><i class="fas fa-save"></i> Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR ELIMINAR -->
<div id="modalConfirmar" class="modal-overlay">
    <div class="modal-container" style="max-width:400px;">
        <div class="modal-header" style="background:#dc2626;">
            <h3><i class="fas fa-trash-alt"></i> Confirmar eliminación</h3>
            <button class="modal-close-btn" onclick="cerrarModalConfirmar()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="text-align:center; padding:2rem;">
            <i class="fas fa-exclamation-triangle" style="font-size:3rem; color:#f16b1a; margin-bottom:1rem; display:block;"></i>
            <p style="font-size:1rem; color:#333; margin-bottom:0.5rem;">¿Eliminar el producto?</p>
            <p style="font-size:1.1rem; font-weight:700; color:#dc2626;" id="confirm-nombre"></p>
            <p style="font-size:0.8rem; color:#666; margin-top:0.5rem;">Esta acción no se puede deshacer.</p>
            <div style="display:flex; gap:1rem; justify-content:center; margin-top:1.5rem;">
                <button class="btn-cancel" onclick="cerrarModalConfirmar()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button id="btnConfirmarEliminar"
                    style="padding:0.7rem 2rem; border-radius:40px; background:#dc2626;
                           border:none; color:white; font-weight:700; cursor:pointer;">
                    <i class="fas fa-trash-alt"></i> Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- TOAST -->
<div id="toast" style="
    position: fixed; bottom: 2rem; right: 2rem;
    padding: 1rem 1.5rem; border-radius: 12px;
    font-size: 0.9rem; font-weight: 600; color: white;
    z-index: 99999; display: none; align-items: center; gap: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    animation: slideInToast 0.3s ease; min-width: 260px;">
    <i id="toast-icon" class="fas fa-check-circle"></i>
    <span id="toast-msg"></span>
</div>

<script>
const BASE = '<?= base_url() ?>';

function showToast(mensaje, tipo = 'success') {
    const toast = document.getElementById('toast');
    const msg   = document.getElementById('toast-msg');
    const icon  = document.getElementById('toast-icon');
    msg.textContent = mensaje;
    if (tipo === 'success') {
        toast.style.background = '#1d4a27';
        icon.className = 'fas fa-check-circle';
    } else if (tipo === 'error') {
        toast.style.background = '#dc2626';
        icon.className = 'fas fa-times-circle';
    } else if (tipo === 'warning') {
        toast.style.background = '#f16b1a';
        icon.className = 'fas fa-exclamation-circle';
    }
    toast.style.display = 'flex';
    setTimeout(() => { toast.style.display = 'none'; }, 3500);
}

function confirmarEliminar(id, nombre) {
    document.getElementById('confirm-nombre').textContent = nombre;
    document.getElementById('modalConfirmar').style.display = 'flex';
    document.getElementById('btnConfirmarEliminar').onclick = function() {
        cerrarModalConfirmar();
        fetch(BASE + "existencias/eliminar/" + id, { method: "POST" })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Producto eliminado correctamente', 'success');
                setTimeout(() => location.reload(), 1800);
            } else {
                showToast('Error: ' + (data.message || 'intente de nuevo'), 'error');
            }
        })
        .catch(() => showToast('Error de conexión al eliminar', 'error'));
    };
}

function cerrarModalConfirmar() {
    document.getElementById('modalConfirmar').style.display = 'none';
}

function editarProducto(id) {
    const errorDiv = document.getElementById('edit_error');
    errorDiv.style.display = 'none';
    fetch(`${BASE}/existencias/getProducto/${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { showToast('No se pudo cargar el producto', 'error'); return; }
            const d = res.data;
            document.getElementById('edit_id').value          = d.id_producto;
            document.getElementById('edit_nombre').value      = d.nombre;
            document.getElementById('edit_descripcion').value = d.descripcion ?? '';
            document.getElementById('edit_unidad').value      = d.unidad_medida ?? '';
            document.getElementById('edit_total').value       = d.existencias_totales ?? 0;
            document.getElementById('edit_bloqueadas').value  = d.existencias_bloqueadas ?? 0;
            document.getElementById('modalEditar').style.display = 'flex';
        })
        .catch(() => showToast('Error de conexión al obtener datos', 'error'));
}

function cerrarModal() {
    document.getElementById('modalEditar').style.display = 'none';
}

document.getElementById('modalEditar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

document.getElementById('modalConfirmar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalConfirmar();
});


    function guardarCambios() {
    const id         = document.getElementById('edit_id').value;
    const total      = parseInt(document.getElementById('edit_total').value);
    const bloqueadas = parseInt(document.getElementById('edit_bloqueadas').value);
    const errDiv     = document.getElementById('edit_error');
    const errSpan    = errDiv.querySelector('span');

    if (isNaN(total) || isNaN(bloqueadas)) {
        errSpan.textContent = 'Los valores deben ser números válidos';
        errDiv.style.display = 'flex'; return;
    }
    if (bloqueadas > total) {
        errSpan.textContent = 'Las existencias bloqueadas no pueden superar las totales';
        errDiv.style.display = 'flex'; return;
    }
    errDiv.style.display = 'none';

    const body = new URLSearchParams({
        nombre:                 document.getElementById('edit_nombre').value,
        descripcion:            document.getElementById('edit_descripcion').value,
        unidad_medida:          document.getElementById('edit_unidad').value,
        existencias_totales:    total,
        existencias_bloqueadas: bloqueadas,
    });

    fetch(`${BASE}/existencias/actualizar/${id}`, { method: 'POST', body })
        .then(r => r.json())
        .then(res => {
            if (!res.success) { showToast('Error: ' + res.message, 'error'); return; }
            const card = document.querySelector(`.producto-card[data-id="${id}"]`);
            if (card) {
                const disponibles    = total - bloqueadas;
                const nuevoNombre    = document.getElementById('edit_nombre').value;
                const nuevaDesc      = document.getElementById('edit_descripcion').value;
                const nuevaUnidad    = document.getElementById('edit_unidad').value;

                const nombreEl       = card.querySelector('.producto-nombre');
                const descEl         = card.querySelector('.producto-descripcion');
                const unidadEl       = card.querySelector('.unidad-medida');
                const totalSpan      = card.querySelector(`#total-${id}`);
                const bloqueadasSpan = card.querySelector(`#bloqueadas-${id}`);
                const ventaSpan      = card.querySelector(`#venta-${id}`);

                if (nombreEl)       nombreEl.textContent      = nuevoNombre;
                if (descEl)         descEl.textContent        = nuevaDesc;
                if (unidadEl)       unidadEl.innerHTML        = `<i class="fas fa-ruler"></i> ${nuevaUnidad}`;
                if (totalSpan)      totalSpan.textContent     = total;
                if (bloqueadasSpan) bloqueadasSpan.textContent = bloqueadas;
                if (ventaSpan)      ventaSpan.textContent     = disponibles;

                card.dataset.total      = total;
                card.dataset.bloqueadas = bloqueadas;
            }
            cerrarModal();
            showToast('Producto actualizado correctamente', 'success');
        })
        .catch(() => showToast('Error de conexión al guardar', 'error'));
}
function aplicarOrden() {
    const orden = document.getElementById('ordenSelect').value;
    document.getElementById('ordenHidden').value = orden;
    document.getElementById('formBusqueda').submit();
}
</script>
</body>
</html>