<!DOCTYPE html>
<html lang="es">
<head>
    <!--ESTA ES LA PANTALLA DE CLIENTES -->
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
     <link rel="stylesheet" href="<?= base_url('css/productos.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Productos</title>
    
</head>
<body>

    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo" width="140">
        </div>
        <div class="buscador">
            <input type="text" placeholder="Buscar...">
            <button><i class="fas fa-search"></i></button>
        </div>
        <div class="user-actions">
            <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> <span>Admin</span></a>
            <a href="#" class="btn-user"><i class="fas fa-bell"></i> <span>Notificaciones</span></a>
            <a href="<?= base_url('menusolo') ?>" class="btn-user"><i class="fas fa-sign-out-alt"></i> <span>Regresar</span></a>
        </div>
    </div>

    <nav class="menu-navegacion">
        <div class="nav-links">
            <a href="pantalla_ventas" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
            <a href="pantalla_pedidos" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
            <a href="<?=base_url('pantalla_inventario')?>" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="pantalla_clientes" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
            <a href="pantalla_repartidores" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
            <a href="pantalla_productos" class="nav-link activo"><i class="fa-solid fa-apple-whole"></i> Productos</a>
        </div>
    </nav>

</body>
</html>