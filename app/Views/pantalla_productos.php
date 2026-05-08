<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    
    <link rel="stylesheet" href="<?= base_url('css/productos.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Catálogo de Productos</title>
    

</head>
<body>

<!-- HEADER -->
<header>
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo FRUVER">
        </div>
        <div class="buscador">
            <form method="GET">
                <input type="text" name="q"
                    value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"
                    placeholder="Buscar productos...">
                <button type="submit"><i class="fas fa-search"></i> </button>
            </form>
        </div>
        <div class="user-actions">
            <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> Admin</a>
            <a href="#" class="btn-user"><i class="fas fa-bell"></i> Notificaciones</a>
            <a href="<?= base_url('menusolo') ?>" class="btn-user"><i class="fas fa-sign-out-alt"></i> Regresar</a>
        </div>
    </div>
</header>

<!-- NAVEGACIÓN -->
<nav class="menu-navegacion">
    <div class="nav-links">
        <a href="pantalla_ventas"      class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="pantalla_pedidos"     class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="<?= base_url('pantalla_inventario') ?>" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="pantalla_clientes"    class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="pantalla_repartidores" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="pantalla_productos"   class="nav-link activo"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </div>
</nav>

<!-- BOTÓN FLOTANTE -->
<a href="<?= base_url('alta_producto') ?>" class="btn-nuevo-producto">
    <i class="fas fa-plus-circle"></i> Nuevo Producto
</a>

<!-- ALERTAS -->
<div class="alert">
    <?php if(session()->getFlashdata('mensaje')): ?>
        <div class="alert-msg alert-success">
            <i class="fas fa-check-circle"></i>
            <?= session()->getFlashdata('mensaje') ?>
        </div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert-msg alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>
</div>

<!-- CATÁLOGO -->
<div class="catalogo-container">
    <h4>Nuestros Productos</h4>

    <div class="cards-grid">
        <?php if(!empty($productos)): ?>
            <?php foreach($productos as $prod): ?>
            <div class="product-card">
                <div class="card-img">
                    <img src="<?= $prod['imagen']
                        ? base_url('uploads/productos/'.$prod['imagen'])
                        : 'https://placehold.co/400x300/DFF0E6/2C6E49?text=🍎+FRUVER' ?>"
                        alt="<?= esc($prod['nombre']) ?>"
                        loading="lazy">
                </div>
                <div class="card-info">
                    <div class="product-name">
                        <?= esc($prod['nombre']) ?>
                        
                    </div>
                    <div class="product-desc">
                        <?= esc($prod['descripcion']) ?>
                    </div>
                </div>
                <div class="card-actions">
                    <!-- Botón Editar -->
                    <button class="btn-card btn-edit"
                        onclick="abrirEditar(<?= $prod['id'] ?>, '<?= esc($prod['nombre'], 'js') ?>', '<?= esc($prod['descripcion'], 'js') ?>')">
                        <i class="fas fa-pen"></i> Editar
                    </button>
                    <!-- Botón Eliminar -->
                    <button class="btn-card btn-delete"
                        onclick="abrirEliminar(<?= $prod['id'] ?>, '<?= esc($prod['nombre'], 'js') ?>')">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" style="grid-column: 1/-1;">
               
                <p>No hay productos disponibles</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- PAGINACIÓN con template personalizado -->
    <div class="pagination-wrapper">
        <?php if(isset($pager)): ?>
            <?= $pager->links('default', 'mi_paginacion') ?>
        <?php endif; ?>
    </div>
</div>

<!-- ===== MODAL EDITAR ===== -->
<div class="modal-overlay" id="modalEditar">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-pen" style="color:var(--primary-orange)"></i> Editar Producto</div>
        <form method="POST" id="formEditar">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Nombre del producto</label>
                <input type="text" name="nombre" id="edit_nombre" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" id="edit_descripcion"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="cerrarEditar()">Cancelar</button>
                <button type="submit" class="btn-modal btn-save"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL CONFIRMAR ELIMINAR ===== -->
<div class="confirm-overlay" id="modalEliminar">
    <div class="confirm-box">
        <div class="confirm-icon"><i class="fas fa-trash-alt"></i></div>
        <div class="confirm-title">¿Eliminar producto?</div>
        <div class="confirm-text" id="confirmText">Esta acción no se puede deshacer.</div>
        <div class="confirm-footer">
            <button class="btn-modal btn-cancel" onclick="cerrarEliminar()">Cancelar</button>
            <form method="POST" id="formEliminar" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn-confirm-delete"><i class="fas fa-trash"></i> Sí, eliminar</button>
            </form>
        </div>
    </div>
</div>

<script>
    const BASE = '<?= base_url() ?>';

    // ===== EDITAR =====
    function abrirEditar(id, nombre, descripcion) {
        document.getElementById('edit_nombre').value      = nombre;
        document.getElementById('edit_descripcion').value = descripcion;
        document.getElementById('formEditar').action      = BASE + 'producto/editar/' + id;
        document.getElementById('modalEditar').classList.add('open');
    }
    function cerrarEditar() {
        document.getElementById('modalEditar').classList.remove('open');
    }

    // ===== ELIMINAR =====
    function abrirEliminar(id, nombre) {
        document.getElementById('confirmText').textContent = '¿Seguro que deseas eliminar "' + nombre + '"? Esta acción no se puede deshacer.';
        document.getElementById('formEliminar').action     = BASE + 'producto/eliminar/' + id;
        document.getElementById('modalEliminar').classList.add('open');
    }
    function cerrarEliminar() {
        document.getElementById('modalEliminar').classList.remove('open');
    }

    // Cerrar modales al hacer clic fuera
    document.getElementById('modalEditar').addEventListener('click', function(e) {
        if(e.target === this) cerrarEditar();
    });
    document.getElementById('modalEliminar').addEventListener('click', function(e) {
        if(e.target === this) cerrarEliminar();
    });
</script>

</body>
</html>