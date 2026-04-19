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
            <a href="menusolo" class="btn-user"><i class="fas fa-sign-out-alt"></i> Regresar</a>
        </div>
    </div>
</header>

<nav class="menu-navegacion">
    <div class="nav-links">
        <a href="#" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="#" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="#" class="nav-link activo"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="pantalla_clientes" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
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
 <?= $pager->links('default', 'mi_paginacion')?>
</div>
