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

<header>
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="FRUVER">
        </div>
        
        <div class="buscador">
            <form method="GET" id="formBusqueda">
                <input type="text" name="q" placeholder="Buscar productos..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                <input type="hidden" name="orden" id="ordenHidden" value="<?= isset($_GET['orden']) ? htmlspecialchars($_GET['orden']) : '' ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        
        <button class="btn-nuevo-producto" onclick="mostrarModalNuevo()">
            <i class="fas fa-plus-circle"></i> Nuevo Producto
        </button>

        <div class="filtros-group">
            <select name="orden" class="select-orden" id="ordenSelect" onchange="aplicarOrden()">
                <option value="">Ordenar por defecto</option>
                <option value="menor" <?= (isset($_GET['orden']) && $_GET['orden'] == 'menor') ? 'selected' : '' ?>>Precio menor</option>
                <option value="mayor" <?= (isset($_GET['orden']) && $_GET['orden'] == 'mayor') ? 'selected' : '' ?>>Precio mayor</option>
                <option value="stock_mayor" <?= (isset($_GET['orden']) && $_GET['orden'] == 'stock_mayor') ? 'selected' : '' ?>>Mayor existencias</option>
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
        <a href="<?= base_url('pantalla_ventas') ?>" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="<?= base_url('pantalla_pedidos') ?>" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="<?= base_url('pantalla_inventario') ?>" class="nav-link activo"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="<?= base_url('pantalla_clientes') ?>" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="<?= base_url('pantalla_repartidores') ?>" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="<?= base_url('pantalla_productos') ?>" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
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
            $existencias_totales = isset($p['existencias_totales']) ? $p['existencias_totales'] : 0;
            $existencias_bloqueadas = isset($p['existencias_bloqueadas']) ? $p['existencias_bloqueadas'] : 0;
            $existencias_venta = $existencias_totales - $existencias_bloqueadas;
        ?>
        <div class="producto-card" data-id="<?= $p['id_producto'] ?>" data-total="<?= $existencias_totales ?>" data-bloqueadas="<?= $existencias_bloqueadas ?>">
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
                <button class="btn-accion btn-entrada" onclick="registrarEntrada(<?= $p['id_producto'] ?>)">
                    <i class="fas fa-arrow-down"></i> Entrada
                </button>
                <button class="btn-accion btn-eliminar" onclick="eliminarProducto(<?= $p['id_producto'] ?>, '<?= htmlspecialchars($p['nombre']) ?>')">
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
            <h3>
                <i class="fas fa-edit"></i>
                Editar Producto
            </h3>
            <button class="modal-close-btn" onclick="cerrarModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <input type="hidden" id="edit_id">
            
            <div class="modal-field">
                <label><i class="fas fa-tag"></i> Nombre del producto</label>
                <input type="text" id="edit_nombre" disabled placeholder="Nombre del producto">
            </div>
            
            <div class="modal-field">
                <label><i class="fas fa-align-left"></i> Descripción</label>
                <textarea id="edit_descripcion" rows="3" placeholder="Descripción detallada del producto..."></textarea>
            </div>
            
            <div class="modal-field">
                <label><i class="fas fa-ruler"></i> Unidad de medida</label>
                <input type="text" id="edit_unidad" placeholder="Ej: Kg, Litro, Unidad, Paquete">
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
                <button class="btn-cancel" onclick="cerrarModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button class="btn-save" onclick="guardarCambios()">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE = '<?= base_url() ?>';

function editarProducto(id) {
    const errorDiv = document.getElementById('edit_error');
    errorDiv.style.display = 'none';
    
    fetch(`${BASE}/existencias/getProducto/${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { 
                alert(' No se pudo cargar el producto'); 
                return; 
            }
            const d = res.data;
            document.getElementById('edit_id').value = d.id_producto;
            document.getElementById('edit_nombre').value = d.nombre;
            document.getElementById('edit_descripcion').value = d.descripcion ?? '';
            document.getElementById('edit_unidad').value = d.unidad_medida ?? '';
            document.getElementById('edit_total').value = d.existencias_totales ?? 0;
            document.getElementById('edit_bloqueadas').value = d.existencias_bloqueadas ?? 0;
            document.getElementById('modalEditar').style.display = 'flex';
        })
        .catch(() => alert(' Error de conexión'));
}

function cerrarModal() {
    document.getElementById('modalEditar').style.display = 'none';
}

document.getElementById('modalEditar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

function guardarCambios() {
    const id = document.getElementById('edit_id').value;
    const total = parseInt(document.getElementById('edit_total').value);
    const bloqueadas = parseInt(document.getElementById('edit_bloqueadas').value);
    const errDiv = document.getElementById('edit_error');
    const errSpan = errDiv.querySelector('span');

    if (isNaN(total) || isNaN(bloqueadas)) {
        errSpan.textContent = 'Los valores deben ser números válidos';
        errDiv.style.display = 'flex';
        return;
    }

    if (bloqueadas > total) {
        errSpan.textContent = 'Las existencias bloqueadas no pueden superar las totales';
        errDiv.style.display = 'flex';
        return;
    }
    
    errDiv.style.display = 'none';

    const body = new URLSearchParams({
        descripcion: document.getElementById('edit_descripcion').value,
        unidad_medida: document.getElementById('edit_unidad').value,
        existencias_totales: total,
        existencias_bloqueadas: bloqueadas,
    });

    fetch(`${BASE}/existencias/actualizar/${id}`, { method: 'POST', body })
        .then(r => r.json())
        .then(res => {
            if (!res.success) { 
                alert(' Error: ' + res.message); 
                return; 
            }

            const card = document.querySelector(`.producto-card[data-id="${id}"]`);
            if (card) {
                const disponibles = total - bloqueadas;
                const totalSpan = card.querySelector(`#total-${id}`);
                const bloqueadasSpan = card.querySelector(`#bloqueadas-${id}`);
                const ventaSpan = card.querySelector(`#venta-${id}`);
                const descEl = card.querySelector('.producto-descripcion');
                
                if (totalSpan) totalSpan.textContent = total;
                if (bloqueadasSpan) bloqueadasSpan.textContent = bloqueadas;
                if (ventaSpan) ventaSpan.textContent = disponibles;
                if (descEl) descEl.textContent = document.getElementById('edit_descripcion').value;
                
                card.dataset.total = total;
                card.dataset.bloqueadas = bloqueadas;
            }

            cerrarModal();
        })
        .catch(() => alert(' Error de conexión'));
}



function aplicarOrden() {
    const orden = document.getElementById('ordenSelect').value;
    document.getElementById('ordenHidden').value = orden;
    document.getElementById('formBusqueda').submit();
}
</script>
</body>
</html>