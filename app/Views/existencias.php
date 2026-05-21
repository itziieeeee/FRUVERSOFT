<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Inter', 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #eef2f0 100%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    .barra-superior {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.6rem 2rem;
        background: linear-gradient(90deg, var(--primary-green) 0%, #2a5e35 100%);
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    .logo-area img { height: 80px; object-fit: contain; }
    .buscador { flex: 1; max-width: 450px; position: relative; }
    .buscador form {
        display: flex; width: 100%; height: 40px;
        background-color: white; border-radius: 50px; overflow: hidden;
    }
    .buscador input { 
        flex: 1; padding: 0.5rem 1rem; border: none; font-size: 0.85rem; outline: none;
        background: white;
    }
    .buscador button {
        background: var(--primary-orange); border: none;
        padding: 0.5rem 1rem; color: white; cursor: pointer; transition: all 0.3s ease;
    }
    .buscador button:hover { background: var(--primary-orange-dark); }
    
    /* DROPDOWN BUSCADOR */
    .dropdown-sugerencias {
        display: none;
        position: absolute;
        top: 110%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid var(--gray-border);
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
        z-index: 9999;
        max-height: 260px;
        overflow-y: auto;
    }
    .sugerencia-item {
        padding: 10px 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #f5f5f5;
        font-size: 0.85rem;
        transition: background 0.15s;
        color: var(--text-dark);
    }
    .sugerencia-item:hover { background: #fff5ee; }
    .sugerencia-item:last-child { border-bottom: none; }
    .sugerencia-stock {
        background: #e8f3e6;
        color: #1d4a27;
        font-size: 0.72rem;
        padding: 2px 8px;
        border-radius: 20px;
        font-weight: 700;
    }
    
    /* BOTÓN FLOTANTE (FAB) ESTILIZADO */
    .btn-nuevo-producto-flotante {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background: linear-gradient(135deg, var(--primary-orange), var(--primary-orange-dark));
        color: white;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 4px 20px rgba(241, 107, 26, 0.4);
        transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        z-index: 1000;
        border: none;
        cursor: pointer;
    }
    .btn-nuevo-producto-flotante i {
        font-size: 1.5rem;
        transition: transform 0.2s ease;
    }
    .btn-nuevo-producto-flotante:hover {
        transform: scale(1.1) translateY(-3px);
        box-shadow: 0 8px 28px rgba(241, 107, 26, 0.5);
        background: linear-gradient(135deg, var(--primary-orange-dark), #c94e08);
    }
    .btn-nuevo-producto-flotante:hover i {
        transform: rotate(90deg);
    }
    
    /* Tooltip para el FAB */
    .btn-nuevo-producto-flotante::before {
        content: "Nuevo Producto";
        position: absolute;
        right: 70px;
        background: var(--primary-green);
        color: white;
        padding: 6px 14px;
        border-radius: 40px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
        pointer-events: none;
        box-shadow: var(--shadow-sm);
        letter-spacing: 0.3px;
    }
    .btn-nuevo-producto-flotante:hover::before {
        opacity: 1;
        visibility: visible;
        transform: translateX(-5px);
    }
    
    .user-actions { display: flex; gap: 0.8rem; align-items: center; flex-wrap: wrap; }
    .btn-user {
        color: white; text-decoration: none; font-size: 0.85rem;
        padding: 0.5rem 1.2rem; background: rgba(255,255,255,0.15);
        border-radius: 50px; transition: all 0.3s ease;
        display: inline-flex; align-items: center; gap: 8px; font-weight: 500;
    }
    .btn-user:hover { background: rgba(255,255,255,0.3); transform: translateY(-1px); }
    .menu-navegacion {
        background: white; padding: 0.5rem 1.5rem;
        display: flex; justify-content: center; align-items: center;
        border-bottom: 1px solid var(--gray-border);
        gap: 0.5rem; flex-wrap: wrap; box-shadow: var(--shadow-sm);
    }
    .nav-links { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.25rem; }
    .nav-link {
        padding: 0.9rem 1.2rem; color: var(--text-dark); text-decoration: none;
        font-size: 0.85rem; font-weight: 600; transition: all 0.2s ease;
        border-radius: 40px; display: inline-flex; align-items: center; gap: 8px;
    }
    .nav-link:hover { color: var(--primary-orange); background: var(--light-green); }
    .nav-link.activo {
        color: var(--primary-orange); background: rgba(241, 107, 26, 0.08);
        border-bottom: 3px solid var(--primary-orange);
    }
    .contenido-principal {
        flex: 1; margin: 1rem; background: white; border-radius: 1rem;
        display: flex; flex-direction: column; min-height: 0;
        box-shadow: var(--shadow-md); overflow: hidden;
    }
    
    .titulo-seccion {
        padding: 1rem 1.5rem 0.8rem 1.5rem;
        border-bottom: 2px solid var(--light-green);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
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
    
    .filtros-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .filtro-cat {
        padding: 6px 16px;
        border-radius: 40px;
        border: 1.5px solid var(--gray-border);
        background: white;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        color: #555;
    }
    .filtro-cat:hover { border-color: var(--primary-green); color: var(--primary-green); background: #f5faf5; }
    .filtro-cat.activo {
        background: var(--primary-green);
        color: white;
        border-color: var(--primary-green);
    }
    .select-orden {
        padding: 6px 12px;
        border-radius: 40px;
        border: 1.5px solid var(--gray-border);
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        background: white;
        color: #555;
        outline: none;
    }
    .select-orden:focus {
        border-color: var(--primary-orange);
    }
    .separador {
        color: #ccc;
        font-size: 0.8rem;
        margin: 0 2px;
    }
    
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
    .precio-unidad p i { color: var(--primary-green); font-size: 0.7rem; }
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
    .unidad-medida i { font-size: 0.65rem; color: var(--primary-orange); }
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
    .inventario-label i { width: 22px; color: var(--primary-green); }
    .total { color: #2c6e3c; font-weight: 700; }
    .bloqueado { color: #b45353; font-weight: 700; }
    .disponible { color: #1e7e34; font-weight: 700; }
    .producto-acciones { display: flex; gap: 0.5rem; margin-top: 0.2rem; flex-wrap: wrap; }
    .btn-accion {
        flex: 1; padding: 0.5rem 0.2rem; border-radius: 8px; font-size: 0.7rem;
        border: none; cursor: pointer; font-weight: 600; transition: all 0.2s ease;
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    }
    .btn-editar { background: var(--light-green); color: var(--primary-green); }
    .btn-editar:hover { background: #cbe5c4; transform: translateY(-2px); }
    .btn-eliminar {
        background: #ffe8e8; color: #dc2626; text-decoration: none !important;
        border: none; font-family: inherit; font-size: 0.7rem; font-weight: 600;
        flex: 1; padding: 0.5rem 0.2rem; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        gap: 6px; cursor: pointer; transition: all 0.2s ease;
    }
    .btn-eliminar:hover { background: #ffd4d4; transform: translateY(-2px); text-decoration: none; }
    
    /* MODALES */
    .modal-overlay {
        display: none; position: fixed; top: 0; left: 0;
        width: 100%; height: 100%; background: rgba(0,0,0,0.6);
        backdrop-filter: blur(4px); z-index: 9999;
        align-items: center; justify-content: center; animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .modal-container {
        background: var(--gray-light); border-radius: 20px;
        width: min(550px, 92vw); max-height: 90vh; overflow-y: auto;
        position: relative; box-shadow: var(--shadow-lg); animation: slideUp 0.3s ease;
    }
    @keyframes slideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .modal-header {
        background: var(--primary-green); padding: 1.2rem 1.5rem;
        border-radius: 20px 20px 0 0;
        display: flex; justify-content: space-between; align-items: center;
    }
    .modal-header h3 {
        margin: 0; font-size: 1.3rem; font-weight: 700; color: white;
        display: flex; align-items: center; gap: 10px;
    }
    .modal-header h3 i { font-size: 1.4rem; color: var(--primary-orange); }
    .modal-close-btn {
        background: rgba(255,255,255,0.15); border: none; color: white;
        font-size: 1.2rem; cursor: pointer; width: 34px; height: 34px;
        border-radius: 50%; display: flex; align-items: center;
        justify-content: center; transition: all 0.2s ease;
    }
    .modal-close-btn:hover { background: rgba(255,255,255,0.3); transform: rotate(90deg); }
    .modal-body { padding: 1.8rem 1.8rem 1.5rem; }
    .modal-field { margin-bottom: 1.2rem; }
    .modal-field label {
        display: block; font-size: 0.75rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.5px;
        color: var(--primary-green); margin-bottom: 0.5rem;
    }
    .modal-field label i { width: 24px; color: var(--primary-orange); }
    .modal-field input, .modal-field textarea {
        width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #e0e0e0;
        border-radius: 12px; background: white; color: var(--text-dark);
        font-size: 0.9rem; transition: all 0.2s ease; font-family: inherit;
    }
    .modal-field input:focus, .modal-field textarea:focus {
        outline: none; border-color: var(--primary-orange);
        box-shadow: 0 0 0 3px rgba(241, 107, 26, 0.1);
    }
    .modal-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem; }
    .error-message {
        background: #fee2e2; border-left: 3px solid #dc2626;
        padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1.2rem;
        display: none; color: #dc2626; font-size: 0.85rem; align-items: center; gap: 8px;
    }
    .modal-footer { display: flex; gap: 1rem; justify-content: flex-end; padding-top: 0.5rem; }
    .btn-cancel {
        padding: 0.7rem 1.8rem; border-radius: 40px; background: white;
        border: 1.5px solid var(--primary-green); color: var(--primary-green);
        font-weight: 600; cursor: pointer; transition: all 0.2s ease;
    }
    .btn-cancel:hover { background: var(--light-green); transform: translateY(-2px); }
    .btn-save {
        padding: 0.7rem 2rem; border-radius: 40px; background: var(--primary-orange);
        border: none; color: white; font-weight: 700; cursor: pointer;
        transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-save:hover { background: var(--primary-orange-dark); transform: translateY(-2px); }
    
    /* TOAST */
    #toast {
        position: fixed; bottom: 2rem; right: 2rem;
        padding: 1rem 1.5rem; border-radius: 12px;
        font-size: 0.9rem; font-weight: 600; color: white;
        z-index: 99999; display: none; align-items: center; gap: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2); min-width: 260px;
        animation: slideInToast 0.3s ease;
    }
    @keyframes slideInToast {
        from { transform: translateX(100px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .custom-pagination {
        display: flex; justify-content: center; list-style: none;
        padding: 1rem; border-top: 1px solid var(--gray-border);
        gap: 0.5rem; background: white; flex-shrink: 0;
    }
    .custom-pagination li a {
        padding: 0.4rem 0.9rem; font-size: 0.8rem; text-decoration: none;
        color: var(--text-dark); border-radius: 40px; background: #f1f3f5; font-weight: 500;
    }
    .custom-pagination li.active a { background: var(--primary-green); color: white; }
    
    @media (max-width: 900px) { .productos-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px) {
        .productos-grid { grid-template-columns: 1fr; }
        .barra-superior { flex-direction: column; text-align: center; }
        .titulo-seccion { flex-direction: column; align-items: flex-start; }
        .modal-row { grid-template-columns: 1fr; }
        .btn-nuevo-producto-flotante {
            width: 48px;
            height: 48px;
            bottom: 1.5rem;
            right: 1.5rem;
        }
        .btn-nuevo-producto-flotante i { font-size: 1.2rem; }
    }
</style>
</head>
<body>

<header>
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="FRUVER">
        </div>

        <!-- BUSCADOR -->
        <div class="buscador" style="position:relative;">
            <form onsubmit="return false;">
                <input type="text" id="inputBuscador" placeholder="Buscar productos..." autocomplete="off">
                <button type="button"><i class="fas fa-search"></i></button>
            </form>
            <div class="dropdown-sugerencias" id="dropdownSugerencias"></div>
        </div>

        <div class="user-actions">
            <a href="<?= base_url('admin') ?>" class="btn-user"><i class="fas fa-user-shield"></i> Admin</a>
            <a href="menusolo" class="btn-user"><i class="fas fa-sign-out-alt"></i> Regresar</a>
        </div>
    </div>
</header>

<nav class="menu-navegacion">
    <div class="nav-links">
        <a href="<?= base_url('pantalla_ventas') ?>"       class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="<?= base_url('pantalla_pedidos') ?>"      class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="<?= base_url('pantalla_inventario') ?>"   class="nav-link activo"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="<?= base_url('pantalla_clientes') ?>"     class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="<?= base_url('pantalla_repartidores') ?>" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="<?= base_url('pantalla_productos') ?>"    class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </div>
</nav>

<div class="contenido-principal">
    <div class="titulo-seccion">
        <div class="titulo-texto">
            <i class="fas fa-boxes"></i>
            <span>Gestión de Inventario</span>
        </div>
        
        <!-- FILTROS Y ORDEN -->
        <div class="filtros-actions">
            <button class="filtro-cat activo" onclick="filtrarCategoria('todos', this)">Todos</button>
            <button class="filtro-cat" onclick="filtrarCategoria('Frutas', this)">Frutas</button>
            <button class="filtro-cat" onclick="filtrarCategoria('Verduras', this)">Verduras</button>
            <button class="filtro-cat" onclick="filtrarCategoria('Hierbas', this)">Hierbas</button>
            
            <span class="separador">|</span>
            
            <select id="ordenSelect" onchange="aplicarOrdenLocal()" class="select-orden">
                <option value="">A → Z</option>
                <option value="z_a">Z → A</option>
                <option value="stock_mayor">Mayor stock</option>
                <option value="stock_menor">Menor stock</option>
            </select>
        </div>
    </div>

    <div class="productos-grid" id="productosGrid">
        <?php foreach($productos as $p):
            $existencias_totales    = $p['existencias_totales']    ?? 0;
            $existencias_bloqueadas = $p['existencias_bloqueadas'] ?? 0;
            $existencias_venta      = $existencias_totales - $existencias_bloqueadas;
        ?>
        <div class="producto-card"
             data-id="<?= $p['id_producto'] ?>"
             data-nombre="<?= strtolower(htmlspecialchars($p['nombre'])) ?>"
             data-categoria="<?= htmlspecialchars($p['categoria'] ?? '') ?>"
             data-total="<?= $existencias_totales ?>"
             data-bloqueadas="<?= $existencias_bloqueadas ?>">

            <div class="producto-nombre"><?= htmlspecialchars($p['nombre']) ?></div>
            <div class="producto-descripcion"><?= htmlspecialchars($p['descripcion']) ?></div>

            <div class="precio-unidad">
                <p>
                    <i class="fas fa-ruler-combined"></i> Unidad de medida:
                    <span class="unidad-medida">
                        <i class="fas fa-ruler"></i> <?= htmlspecialchars($p['unidad_venta'] ?? 'Unidad') ?>
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

<!-- BOTÓN FLOTANTE NUEVO PRODUCTO -->
<a href="<?= base_url('alta_producto') ?>" class="btn-nuevo-producto-flotante" id="btnNuevoProducto">
    <i class="fas fa-plus"></i>
</a>

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

<div id="toast">
    <i id="toast-icon" class="fas fa-check-circle"></i>
    <span id="toast-msg"></span>
</div>

<script>
const BASE = '<?= base_url() ?>';

// ============================================================
// BUSCADOR EN TIEMPO REAL
// ============================================================
const inputBuscador = document.getElementById('inputBuscador');
const dropdown      = document.getElementById('dropdownSugerencias');
let todasLasCards   = Array.from(document.querySelectorAll('.producto-card'));
let categoriaActual = 'todos';
let ordenActual     = '';

inputBuscador.addEventListener('input', function() {
    const texto = this.value.toLowerCase().trim();
    mostrarDropdown(texto);
    aplicarFiltros();
});

function mostrarDropdown(texto) {
    if (texto.length < 1) { dropdown.style.display = 'none'; return; }

    const coincidencias = todasLasCards.filter(card =>
        card.dataset.nombre.includes(texto)
    );

    if (coincidencias.length === 0) {
        dropdown.innerHTML = `<div style="padding:12px 16px; color:#999; font-size:0.85rem; text-align:center;">
            <i class="fas fa-search"></i> Sin resultados para "${texto}"
        </div>`;
    } else {
        dropdown.innerHTML = coincidencias.slice(0, 6).map(card => {
            const nombre    = card.querySelector('.producto-nombre')?.textContent.trim() || '';
            const stock     = card.dataset.total || 0;
            const resaltado = nombre.replace(
                new RegExp(`(${texto})`, 'gi'),
                '<strong style="color:#f16b1a">$1</strong>'
            );
            return `
                <div class="sugerencia-item" onclick="seleccionarSugerencia('${nombre.replace(/'/g, "\\'")}')">
                    <span>${resaltado}</span>
                    <span class="sugerencia-stock">Stock: ${stock}</span>
                </div>`;
        }).join('');
    }
    dropdown.style.display = 'block';
}

function seleccionarSugerencia(nombre) {
    inputBuscador.value = nombre;
    dropdown.style.display = 'none';
    aplicarFiltros();
}

document.addEventListener('click', function(e) {
    if (!inputBuscador.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

// ============================================================
// FILTROS CATEGORÍA
// ============================================================
function filtrarCategoria(cat, btn) {
    categoriaActual = cat;
    document.querySelectorAll('.filtro-cat').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    aplicarFiltros();
}

function aplicarOrdenLocal() {
    ordenActual = document.getElementById('ordenSelect').value;
    aplicarFiltros();
}

function aplicarFiltros() {
    const texto = inputBuscador.value.toLowerCase().trim();
    const grid  = document.getElementById('productosGrid');

    let visibles = todasLasCards.filter(card => {
        const nombreOk = !texto || card.dataset.nombre.includes(texto);
        const catOk    = categoriaActual === 'todos' || card.dataset.categoria === categoriaActual;
        return nombreOk && catOk;
    });

    if (ordenActual === 'z_a') {
        visibles.sort((a, b) => b.dataset.nombre.localeCompare(a.dataset.nombre));
    } else if (ordenActual === 'stock_mayor') {
        visibles.sort((a, b) => parseFloat(b.dataset.total) - parseFloat(a.dataset.total));
    } else if (ordenActual === 'stock_menor') {
        visibles.sort((a, b) => parseFloat(a.dataset.total) - parseFloat(b.dataset.total));
    } else {
        visibles.sort((a, b) => a.dataset.nombre.localeCompare(b.dataset.nombre));
    }

    todasLasCards.forEach(c => c.style.display = 'none');
    visibles.forEach(c => { c.style.display = ''; grid.appendChild(c); });

    let msgVacio = document.getElementById('msgSinResultados');
    if (!msgVacio) {
        msgVacio = document.createElement('div');
        msgVacio.id = 'msgSinResultados';
        msgVacio.style.cssText = 'text-align:center; padding:3rem; color:#999; width:100%;';
        msgVacio.innerHTML = '<i class="fas fa-search" style="font-size:2rem; margin-bottom:1rem; display:block;"></i> No se encontraron productos.';
        grid.appendChild(msgVacio);
    }
    msgVacio.style.display = visibles.length === 0 ? 'block' : 'none';
}

// TOAST
function showToast(mensaje, tipo = 'success') {
    const toast = document.getElementById('toast');
    const msg   = document.getElementById('toast-msg');
    const icon  = document.getElementById('toast-icon');
    msg.textContent = mensaje;
    if (tipo === 'success')      { toast.style.background = '#1d4a27'; icon.className = 'fas fa-check-circle'; }
    else if (tipo === 'error')   { toast.style.background = '#dc2626'; icon.className = 'fas fa-times-circle'; }
    else if (tipo === 'warning') { toast.style.background = '#f16b1a'; icon.className = 'fas fa-exclamation-circle'; }
    toast.style.display = 'flex';
    setTimeout(() => { toast.style.display = 'none'; }, 3500);
}

// ELIMINAR
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
function cerrarModalConfirmar() { document.getElementById('modalConfirmar').style.display = 'none'; }

// EDITAR
function editarProducto(id) {
    document.getElementById('edit_error').style.display = 'none';
    fetch(`${BASE}/existencias/getProducto/${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { showToast('No se pudo cargar el producto', 'error'); return; }
            const d = res.data;
            document.getElementById('edit_id').value          = d.id_producto;
            document.getElementById('edit_nombre').value      = d.nombre;
            document.getElementById('edit_descripcion').value = d.descripcion ?? '';
            document.getElementById('edit_unidad').value      = d.unidad_venta ?? '';
            document.getElementById('edit_total').value       = d.existencias_totales ?? 0;
            document.getElementById('edit_bloqueadas').value  = d.existencias_bloqueadas ?? 0;
            document.getElementById('modalEditar').style.display = 'flex';
        })
        .catch(() => showToast('Error de conexión al obtener datos', 'error'));
}
function cerrarModal() { document.getElementById('modalEditar').style.display = 'none'; }
document.getElementById('modalEditar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
document.getElementById('modalConfirmar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalConfirmar();
});

// GUARDAR
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
                const disponibles = total - bloqueadas;
                card.querySelector('.producto-nombre').textContent      = document.getElementById('edit_nombre').value;
                card.querySelector('.producto-descripcion').textContent = document.getElementById('edit_descripcion').value;
                card.querySelector('.unidad-medida').innerHTML          = `<i class="fas fa-ruler"></i> ${document.getElementById('edit_unidad').value}`;
                card.querySelector(`#total-${id}`).textContent          = total;
                card.querySelector(`#bloqueadas-${id}`).textContent     = bloqueadas;
                card.querySelector(`#venta-${id}`).textContent          = disponibles;
                card.dataset.total      = total;
                card.dataset.bloqueadas = bloqueadas;
                card.dataset.nombre     = document.getElementById('edit_nombre').value.toLowerCase();
                todasLasCards = Array.from(document.querySelectorAll('.producto-card'));
            }
            cerrarModal();
            showToast('Producto actualizado correctamente', 'success');
        })
        .catch(() => showToast('Error de conexión al guardar', 'error'));
}
</script>
</body>
</html>