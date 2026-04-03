<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= base_url('css/producto.css') ?>">
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
            <a href="#" class="btn-user"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </div>
</header>

<nav class="menu-navegacion">
    <div class="nav-links">
        <a href="#" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="#" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="#" class="nav-link activo"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="#" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="#" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="#" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </div>
 
</nav>

<div class="contenido-principal">
    <div class="titulo-seccion">
        <div class="titulo-texto">
            <i class="fas fa-boxes"></i>
            <span>Gestión de Inventario</span>
        </div>
    </div>
    

        
      
    
    <!-- CARDS DE PRODUCTOS - GRID 3 COLUMNAS -->
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
                <span class="precio-venta">$<?= number_format($p['precio_venta'], 2) ?></span>
                <span class="unidad-medida"><i class="fas fa-ruler"></i> <?= htmlspecialchars($p['unidad_medida'] ?? 'Unidad') ?></span>
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
    
    <!-- PAGINACIÓN -->
    <?= $pager->links('default', 'mi_paginacion') ?>
</div>


<!-- MODAL PARA NUEVO PRODUCTO 
<div id="modalNuevo" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle"></i> Nuevo Producto</h3>
            <span class="close" onclick="cerrarModalNuevo()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="formNuevoProducto">
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Nombre del producto:</label>
                    <input type="text" name="nombre" id="nuevo_nombre" required placeholder="Ej: Manzana Roja">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Descripción:</label>
                    <input type="text" name="descripcion" id="nuevo_descripcion" required placeholder="Descripción del producto">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-dollar-sign"></i> Precio de venta:</label>
                    <input type="number" step="0.01" name="precio_venta" id="nuevo_precio" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-ruler"></i> Unidad de medida:</label>
                    <input type="text" name="unidad_medida" id="nuevo_unidad" required placeholder="kg, litro, unidad, etc.">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-boxes"></i> Existencias totales iniciales:</label>
                    <input type="number" name="existencias_totales" id="nuevo_totales" required min="0" placeholder="0">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Existencias bloqueadas:</label>
                    <input type="number" name="existencias_bloqueadas" id="nuevo_bloqueadas" min="0" placeholder="0">
                    <small style="color: #666;">No puede exceder las existencias totales</small>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar Producto</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModalNuevo()"><i class="fas fa-times"></i> Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
-->

<!-- MODAL PARA EDITAR PRODUCTO 
<div id="modalEditar" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Editar Producto</h3>
            <span class="close" onclick="cerrarModalEditar()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="formEditarProducto">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Descripción:</label>
                    <input type="text" name="descripcion" id="edit_descripcion" required placeholder="Descripción del producto">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-dollar-sign"></i> Precio de venta:</label>
                    <input type="number" step="0.01" name="precio_venta" id="edit_precio" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-ruler"></i> Unidad de medida:</label>
                    <input type="text" name="unidad_medida" id="edit_unidad" required placeholder="kg, litro, unidad, etc.">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Existencias bloqueadas:</label>
                    <input type="number" name="existencias_bloqueadas" id="edit_bloqueadas" required min="0" placeholder="0">
                    <small style="color: #666;">No puede exceder las existencias totales</small>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar Cambios</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModalEditar()"><i class="fas fa-times"></i> Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
-->
<!-- MODAL PARA REGISTRAR ENTRADA
<div id="modalEntrada" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-arrow-down"></i> Registrar Entrada de Inventario</h3>
            <span class="close" onclick="cerrarModalEntrada()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="formRegistrarEntrada">
                <input type="hidden" name="id" id="entrada_id">
                <div class="form-group">
                    <label><i class="fas fa-boxes"></i> Cantidad a agregar:</label>
                    <input type="number" name="cantidad" id="entrada_cantidad" required min="1" placeholder="Cantidad de unidades">
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn-guardar"><i class="fas fa-check"></i> Registrar Entrada</button>
                    <button type="button" class="btn-cancelar" onclick="cerrarModalEntrada()"><i class="fas fa-times"></i> Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
 -->
<!--
<script>

const productos=[
{nombre:"Tomate",descripcion:"Tomate rojo",emoji:"🍅",precio:22},
{nombre:"Fresa",descripcion:"Fresa fresca",emoji:"🍓",precio:65},
{nombre:"Sandía",descripcion:"Sandía dulce",emoji:"🍉",precio:15},
{nombre:"Sandía",descripcion:"Sandía dulce",emoji:"🍉",precio:15},
{nombre:"Sandía",descripcion:"Sandía dulce",emoji:"🍉",precio:15},
{nombre:"Sandía",descripcion:"Sandía dulce",emoji:"🍉",precio:15},
{nombre:"Sandía",descripcion:"Sandía dulce",emoji:"🍉",precio:15},
{nombre:"Sandía",descripcion:"Sandía dulce",emoji:"🍉",precio:15},
{nombre:"Sandía",descripcion:"Sandía dulce",emoji:"🍉",precio:15},
{nombre:"Sandía",descripcion:"Sandía dulce",emoji:"🍉",precio:15},
];

function renderProductos(lista){

const grid=document.getElementById("productosGrid");

grid.innerHTML=lista.map(p=>`

<div class="producto-card">

<div class="producto-img">${p.emoji}</div>

<div class="producto-nombre">${p.nombre}</div>

<div class="producto-descripcion">${p.descripcion}</div>

<div class="precio-venta">$${p.precio}</div>

<div class="producto-acciones">

<button class="btn-accion btn-editar">Editar</button>

<button class="btn-accion btn-comprar">Entrada</button>

</div>

</div>

`).join("");

// Función para mostrar notificaciones
function mostrarNotificacion(mensaje, tipo = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.style.background = tipo === 'success' ? '#1d4a27' : '#dc3545';
    toast.innerHTML = `<i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${mensaje}`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Calcular y actualizar estadísticas globales
function actualizarEstadisticasGlobales() {
    const cards = document.querySelectorAll('.producto-card');
    let totalExistencias = 0;
    let totalBloqueadas = 0;
    
    cards.forEach(card => {
        const total = parseInt(card.dataset.total) || 0;
        const bloqueadas = parseInt(card.dataset.bloqueadas) || 0;
        totalExistencias += total;
        totalBloqueadas += bloqueadas;
    });
    
    const totalDisponibles = totalExistencias - totalBloqueadas;
    
    const totalExisElem = document.getElementById('totalExistencias');
    const totalBloqElem = document.getElementById('totalBloqueadas');
    const totalDispElem = document.getElementById('totalDisponibles');
    
    if (totalExisElem) totalExisElem.textContent = totalExistencias;
    if (totalBloqElem) totalBloqElem.textContent = totalBloqueadas;
    if (totalDispElem) totalDispElem.textContent = totalDisponibles;
}

<<<<<<< HEAD

</script>
=======
// Aplicar ordenamiento
function aplicarOrden() {
    const orden = document.getElementById('ordenSelect').value;
    const ordenHidden = document.getElementById('ordenHidden');
    if (ordenHidden) ordenHidden.value = orden;
    document.getElementById('formBusqueda').submit();
}

// Mostrar modal nuevo producto
function mostrarModalNuevo() {
    document.getElementById('modalNuevo').style.display = 'block';
}

function cerrarModalNuevo() {
    document.getElementById('modalNuevo').style.display = 'none';
    document.getElementById('formNuevoProducto').reset();
}

// Editar producto
function editarProducto(id) {
    const card = document.querySelector(`.producto-card[data-id="${id}"]`);
    if (!card) return;
    
    const descripcion = card.querySelector('.producto-descripcion').textContent;
    const precioTexto = card.querySelector('.precio-venta').textContent;
    const precio = parseFloat(precioTexto.replace('$', ''));
    const unidad = card.querySelector('.unidad-medida').textContent.trim().replace(/^[^a-zA-ZáéíóúñÑ]+/, '');
    const bloqueadas = parseInt(document.getElementById(`bloqueadas-${id}`).textContent);
    const totales = parseInt(document.getElementById(`total-${id}`).textContent);
    
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_descripcion').value = descripcion;
    document.getElementById('edit_precio').value = precio;
    document.getElementById('edit_unidad').value = unidad;
    document.getElementById('edit_bloqueadas').value = bloqueadas;
    document.getElementById('edit_bloqueadas').max = totales;
    
    document.getElementById('modalEditar').style.display = 'block';
}

function cerrarModalEditar() {
    document.getElementById('modalEditar').style.display = 'none';
}

// Registrar entrada
function registrarEntrada(id) {
    document.getElementById('entrada_id').value = id;
    document.getElementById('modalEntrada').style.display = 'block';
}

function cerrarModalEntrada() {
    document.getElementById('modalEntrada').style.display = 'none';
    document.getElementById('entrada_cantidad').value = '';
}

// Eliminar producto
function eliminarProducto(id, nombre) {
    if (confirm(`¿Estás seguro de eliminar el producto "${nombre}"?\nEsta acción eliminará también su inventario asociado.`)) {
        const formData = new URLSearchParams();
        formData.append('accion', 'eliminar');
        formData.append('id', id);
        
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                mostrarNotificacion(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('Error al eliminar el producto', 'error');
        });
    }
}

// Cerrar modales al hacer clic fuera
window.onclick = function(event) {
    const modalNuevo = document.getElementById('modalNuevo');
    const modalEditar = document.getElementById('modalEditar');
    const modalEntrada = document.getElementById('modalEntrada');
    
    if (event.target === modalNuevo) cerrarModalNuevo();
    if (event.target === modalEditar) cerrarModalEditar();
    if (event.target === modalEntrada) cerrarModalEntrada();
}

// Procesar formulario de nuevo producto
document.getElementById('formNuevoProducto')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const nombre = document.getElementById('nuevo_nombre').value;
    const descripcion = document.getElementById('nuevo_descripcion').value;
    const precio_venta = document.getElementById('nuevo_precio').value;
    const unidad_medida = document.getElementById('nuevo_unidad').value;
    const existencias_totales = document.getElementById('nuevo_totales').value;
    const existencias_bloqueadas = document.getElementById('nuevo_bloqueadas').value || 0;
    
    if (parseInt(existencias_bloqueadas) > parseInt(existencias_totales)) {
        mostrarNotificacion('Las existencias bloqueadas no pueden exceder las existencias totales', 'error');
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append('accion', 'nuevo');
    formData.append('nombre', nombre);
    formData.append('descripcion', descripcion);
    formData.append('precio_venta', precio_venta);
    formData.append('unidad_medida', unidad_medida);
    formData.append('existencias_totales', existencias_totales);
    formData.append('existencias_bloqueadas', existencias_bloqueadas);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarNotificacion(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al crear el producto', 'error');
    });
});

// Procesar formulario de edición
document.getElementById('formEditarProducto')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('edit_id').value;
    const descripcion = document.getElementById('edit_descripcion').value;
    const precio_venta = document.getElementById('edit_precio').value;
    const unidad_medida = document.getElementById('edit_unidad').value;
    const existencias_bloqueadas = document.getElementById('edit_bloqueadas').value;
    
    const card = document.querySelector(`.producto-card[data-id="${id}"]`);
    const totales = parseInt(card.dataset.total);
    
    if (parseInt(existencias_bloqueadas) > totales) {
        mostrarNotificacion('Las existencias bloqueadas no pueden exceder las existencias totales', 'error');
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append('accion', 'editar');
    formData.append('id', id);
    formData.append('descripcion', descripcion);
    formData.append('precio_venta', precio_venta);
    formData.append('unidad_medida', unidad_medida);
    formData.append('existencias_bloqueadas', existencias_bloqueadas);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarNotificacion(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al actualizar el producto', 'error');
    });
});

// Procesar formulario de entrada
document.getElementById('formRegistrarEntrada')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('entrada_id').value;
    const cantidad = document.getElementById('entrada_cantidad').value;
    
    if (cantidad <= 0) {
        mostrarNotificacion('La cantidad debe ser mayor a 0', 'error');
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append('accion', 'entrada');
    formData.append('id', id);
    formData.append('cantidad', cantidad);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarNotificacion(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al registrar la entrada', 'error');
    });
});

// Actualizar estadísticas al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    actualizarEstadisticasGlobales();
});
</script> -->
>>>>>>> c54e47ee85c4a0c73656fe7cd0457eaffca2f620

</body>
</html>