<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Menú </title>
    <link rel="stylesheet" href="<?= base_url('css/menusolo.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>

<div class="dashboard-container">
    <!-- BARRA SUPERIOR MODERNA -->
    <div class="top-bar">
        <div class="logo-area">        
            <div class="admin-badge">
                 <img src="<?= base_url('img/LOGO2.png') ?>" alt="Logo Corporativo" class="logo-img" width="140">
                
                <span>Administrador </span>
            </div>
        </div>

        <div class="action-buttons">
            <a href="#" class="action-btn">
                <i class="fas fa-bell"></i> Notificaciones
            </a>
            <a href="<?= base_url('pantalla_inicio') ?>" class="action-btn logout-btn">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>

        <div class="search-wrapper">
            <input type="text" placeholder="Buscar módulo, producto o cliente...">
            <button aria-label="Buscar"><i class="fas fa-search"></i></button>
        </div>
    </div>

    <!-- MÓDULOS PRINCIPALES (TARJETAS MEJORADAS) -->
    <div class="modules-grid">
        <a href="<?= base_url('pantalla_inventario') ?>" class="module-card">
            <img src="<?= base_url('img/INVENTARIO1.png') ?>" alt="Inventario" class="module-img">
            <span class="module-title">Inventario</span>
        </a>

        <a href="pantalla_ventas" class="module-card">
            <img src="<?= base_url('img/VENTA.png') ?>" alt="Ventas" class="module-img">
            <span class="module-title">Ventas</span>
        </a>

        <a href="pantalla_pedidos" class="module-card">
            <img src="<?= base_url('img/PEDIDOS.png') ?>" alt="Pedidos" class="module-img">
            <span class="module-title">Pedidos</span>
        </a>

        <a href="<?= base_url('pantalla_clientes') ?>" class="module-card">
            <img src="<?= base_url('img/CLIENTES.png') ?>" alt="Clientes" class="module-img">
            <span class="module-title">Clientes</span>
        </a>

        <a href="pantalla_repartidores" class="module-card">
            <img src="<?= base_url('img/REPARTIDORES.png') ?>" alt="Repartidores" class="module-img">
            <span class="module-title">Repartidores</span>
        </a>

        <a href="pantalla_productos" class="module-card">
            <img src="<?= base_url('img/PRODUCTOS.png') ?>" alt="Productos" class="module-img">
            <span class="module-title">Productos</span>
        </a>
    </div>
</div>

</body>
</html>