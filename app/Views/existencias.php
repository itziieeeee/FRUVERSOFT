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
    :root {
        --primary-green: #1d4a27;
        --primary-orange: #f16b1a;
        --primary-orange-dark: #e05a0a;
        --light-green: #e8f3e6;
        --dark-green: #0f3317;
        --gray-light: #f8f9fa;
        --gray-border: #e0e0e0;
        --text-dark: #333;
        --text-light: #666;
        --white: #ffffff;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
        --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
        --shadow-lg: 0 10px 30px rgba(0,0,0,0.2);
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

    /* HEADER */
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

    .buscador {
        flex: 1;
        max-width: 450px;
        display: flex;
    }

    .buscador form {
        display: flex;
        width: 100%;
        height: 40px;
        background-color: white;
        border-radius: 50px;
        overflow: hidden;
    }

    .buscador input {
        flex: 1;
        padding: 0.5rem 1rem;
        border: none;
        font-size: 0.85rem;
        outline: none;
    }

    .buscador button {
        background: var(--primary-orange);
        border: none;
        padding: 0.5rem 1rem;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .buscador button:hover {
        background: var(--primary-orange-dark);
    }

    .filtros-group .select-orden {
        padding: 0.5rem;
        border-radius: 6px;
        border: none;
        font-size: 0.8rem;
        cursor: pointer;
        background: white;
    }

    /* === BOTON NUEVO PRODUCTO ESTILIZADO === */
    .btn-nuevo-producto {
        background: linear-gradient(135deg, var(--primary-orange), var(--primary-orange-dark));
        color: white;
        border: none;
        padding: 0.6rem 1.4rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        letter-spacing: 0.3px;
    }

    .btn-nuevo-producto i {
        font-size: 1rem;
    }

    .btn-nuevo-producto:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(241, 107, 26, 0.35);
        background: linear-gradient(135deg, var(--primary-orange-dark), #c94e08);
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

    /* NAVEGACIÓN */
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

    /* CONTENIDO PRINCIPAL */
    .contenido-principal {
        flex: 1;
        margin: 1rem;
        background: white;
        border-radius: 1rem;
        display: flex;
        flex-direction: column;
        min-height: 0;
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }

    .titulo-seccion {
        padding: 1rem 1.5rem 0.5rem 1.5rem;
        border-bottom: 2px solid var(--light-green);
        flex-shrink: 0;
    }

    .titulo-texto {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-green);
    }

    .titulo-texto i {
        font-size: 1.5rem;
        background: var(--light-green);
        padding: 8px;
        border-radius: 14px;
    }

    /* GRID 3 COLUMNAS */
    .productos-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        padding: 1.5rem;
        flex: 1;
        overflow-y: auto;
        align-content: flex-start;
    }

    .producto-card {
        background: white;
        border-radius: 1rem;
        padding: 1rem;
        border: 1px solid var(--gray-border);
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        position: relative;
        overflow: visible;
        height: auto;
        min-height: 280px;
    }

    .producto-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .producto-nombre {
        font-size: 1rem;
        font-weight: bold;
        color: var(--primary-green);
        padding-right: 6px;
    }

    .producto-descripcion {
        font-size: 0.75rem;
        color: var(--text-light);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        overflow: hidden;
        background: #fafbfd;
        padding: 6px 10px;
        border-radius: 10px;
        line-height: 1.4;
    }

    .precio-unidad {
        background: linear-gradient(120deg, #fff7ed, #fff2e6);
        padding: 0.5rem 0.8rem;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .precio-unidad p {
        margin: 0;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .precio-unidad p i {
        color: var(--primary-green);
        font-size: 0.7rem;
    }

    .unidad-medida {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(241, 107, 26, 0.12);
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--primary-orange);
    }

    .unidad-medida i {
        font-size: 0.65rem;
        color: var(--primary-orange);
    }

    .inventario-panel {
        background: var(--gray-light);
        border-radius: 0.8rem;
        padding: 0.6rem 0.8rem;
        border-left: 3px solid var(--primary-orange);
    }

    .inventario-item {
        display: flex;
        justify-content: space-between;
        font-size: 0.73rem;
        font-weight: 500;
        padding: 0.2rem 0;
    }

    .inventario-label i {
        width: 22px;
        color: var(--primary-green);
    }

    .total { color: #2c6e3c; font-weight: 700; }
    .bloqueado { color: #b45353; font-weight: 700; }
    .disponible { color: #1e7e34; font-weight: 700; }

    .producto-acciones {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.2rem;
        flex-wrap: wrap;
    }

    .btn-accion {
        flex: 1;
        padding: 0.5rem 0.2rem;
        border-radius: 8px;
        font-size: 0.7rem;
        border: none;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-editar {
        background: var(--light-green);
        color: var(--primary-green);
    }
    .btn-editar:hover {
        background: #cbe5c4;
        transform: translateY(-2px);
    }

    /* BOTON ELIMINAR - SIN SUBRAYADO, ESTILO BOTÓN */
    .btn-eliminar {
        background: #ffe8e8;
        color: #dc2626;
        text-decoration: none !important;
        border: none;
        font-family: inherit;
        font-size: 0.7rem;
        font-weight: 600;
        flex: 1;
        padding: 0.5rem 0.2rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-eliminar:hover {
        background: #ffd4d4;
        transform: translateY(-2px);
        text-decoration: none;
    }

    /* MODAL */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-container {
        background: var(--gray-light);
        border-radius: 20px;
        width: min(550px, 92vw);
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
        box-shadow: var(--shadow-lg);
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .modal-header {
        background: var(--primary-green);
        padding: 1.2rem 1.5rem;
        border-radius: 20px 20px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-header h3 i {
        font-size: 1.4rem;
        color: var(--primary-orange);
    }

    .modal-close-btn {
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .modal-close-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    .modal-body {
        padding: 1.8rem 1.8rem 1.5rem;
    }

    .modal-field {
        margin-bottom: 1.2rem;
    }

    .modal-field label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--primary-green);
        margin-bottom: 0.5rem;
    }

    .modal-field label i {
        width: 24px;
        color: var(--primary-orange);
    }

    .modal-field input,
    .modal-field textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1.5px solid #e0e0e0;
        border-radius: 12px;
        background: white;
        color: var(--text-dark);
        font-size: 0.9rem;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .modal-field input:focus,
    .modal-field textarea:focus {
        outline: none;
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 3px rgba(241, 107, 26, 0.1);
    }

    .modal-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.2rem;
    }

    .error-message {
        background: #fee2e2;
        border-left: 3px solid #dc2626;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        margin-bottom: 1.2rem;
        display: none;
        color: #dc2626;
        font-size: 0.85rem;
        align-items: center;
        gap: 8px;
    }

    .modal-footer {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 0.5rem;
    }

    .btn-cancel {
        padding: 0.7rem 1.8rem;
        border-radius: 40px;
        background: white;
        border: 1.5px solid var(--primary-green);
        color: var(--primary-green);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-save {
        padding: 0.7rem 2rem;
        border-radius: 40px;
        background: var(--primary-orange);
        border: none;
        color: white;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-save:hover {
        background: var(--primary-orange-dark);
        transform: translateY(-2px);
    }

    @media (max-width: 900px) {
        .productos-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .productos-grid { grid-template-columns: 1fr; }
        .barra-superior { flex-direction: column; text-align: center; }
        .modal-row { grid-template-columns: 1fr; }
    }

    .custom-pagination {
        display: flex;
        justify-content: center;
        list-style: none;
        padding: 1rem;
        border-top: 1px solid var(--gray-border);
        gap: 0.5rem;
        background: white;
    }
</style>
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
        <a href="<?= base_url('alta_producto') ?>" class="btn-nuevo-producto">
            <i class="fas fa-plus-circle"></i> Nuevo Producto
        </a>
        <div class="filtros-group">
            <select name="orden" class="select-orden" id="ordenSelect" onchange="aplicarOrden()">
                <option value="">Ordenar por defecto</option>

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
                <!-- Eliminar como BOTON sin subrayado -->
                <a href="#" class="btn-eliminar" onclick="confirmarEliminar(<?= $p['id_producto'] ?>, '<?= addslashes(htmlspecialchars($p['nombre'])) ?>'); return false;">
                    <i class="fas fa-trash-alt"></i> Eliminar
                </a>
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
                <input type="text" id="edit_nombre" disabled placeholder="Nombre del producto">
            </div>
            <div class="modal-field">
                <label><i class="fas fa-align-left"></i> Descripción</label>
                <textarea id="edit_descripcion" rows="3" placeholder="Descripción detallada..."></textarea>
            </div>
            <div class="modal-field">
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
            <div id="edit_error" class="error-message"><i class="fas fa-exclamation-triangle"></i> <span></span></div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="cerrarModal()"><i class="fas fa-times"></i> Cancelar</button>
                <button class="btn-save" onclick="guardarCambios()"><i class="fas fa-save"></i> Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE = '<?= base_url() ?>';

function confirmarEliminar(id, nombre) {
    if (confirm(`¿Eliminar completamente el producto "${nombre}"?`)) {
        fetch(BASE + "/producto/eliminar/" + id, { method: "DELETE" })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                alert("Producto eliminado correctamente");
                location.reload();
            } else {
                alert("Error al eliminar: " + (data.message || "intente de nuevo"));
            }
        })
        .catch(err => {
            console.error(err);
            alert("Error de conexión al eliminar");
        });
    }
}

function editarProducto(id) {
    const errorDiv = document.getElementById('edit_error');
    errorDiv.style.display = 'none';
    
    fetch(`${BASE}/existencias/getProducto/${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { alert('No se pudo cargar el producto'); return; }
            const d = res.data;
            document.getElementById('edit_id').value = d.id_producto;
            document.getElementById('edit_nombre').value = d.nombre;
            document.getElementById('edit_descripcion').value = d.descripcion ?? '';
            document.getElementById('edit_unidad').value = d.unidad_medida ?? '';
            document.getElementById('edit_total').value = d.existencias_totales ?? 0;
            document.getElementById('edit_bloqueadas').value = d.existencias_bloqueadas ?? 0;
            document.getElementById('modalEditar').style.display = 'flex';
        })
        .catch(() => alert('Error de conexión al obtener datos'));
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
            if (!res.success) { alert('Error: ' + res.message); return; }

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
        .catch(() => alert('Error de conexión al guardar'));
}

function aplicarOrden() {
    const orden = document.getElementById('ordenSelect').value;
    document.getElementById('ordenHidden').value = orden;
    document.getElementById('formBusqueda').submit();
}
</script>
</body>
</html>