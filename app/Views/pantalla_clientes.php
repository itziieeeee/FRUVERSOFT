<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/clientes.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Clientes</title>
    
</head>
<body>

<header>
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo">
        </div>
        <div class="buscador">
            <form>
                <input type="text" placeholder="Buscar cliente, pedido...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="user-actions">
            <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> Admin</a>
            <a href="#" class="btn-user"><i class="fas fa-bell"></i> Notificaciones</a>
            <a href="<?= base_url('menusolo') ?>" class="btn-user"><i class="fas fa-sign-out-alt"></i> Regresar</a>
        </div>
    </div>
</header>

<nav class="menu-navegacion">
    <div class="nav-links">
        <a href="pantalla_ventas" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="pantalla_pedidos" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="<?= base_url('pantalla_inventario') ?>" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="pantalla_clientes" class="nav-link activo"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="pantalla_repartidores" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="pantalla_productos" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </div>
</nav>

<div class="tarjeta">

    <div class="card">
        <div class="cabezacard">
            <i class="fas fa-users"></i>
            <h2>Clientes</h2>
            <a href="<?= base_url('pantalla_rcliente') ?>" class="botonclienten">
                <i class="fas fa-plus-circle"></i> Nuevo
            </a>
        </div>
        <div class="scroll-area">
            <div style="margin-bottom: 12px;">
                <h3 style="font-size:0.9rem; color:#22662c; margin-bottom:4px;">Mayoreo</h3>
                <table class="minit">
                    <thead>
                        <tr><th>Cliente</th><th>Total</th></tr></thead>
                    <tbody>
                        <tr><td>Juan Pérez</td><td><span class="fondototal">$7,890</span></td></tr>
                        <tr><td>Karla Juárez</td><td><span class="fondototal">$5,488</span></td></tr>
                    </tbody>
                </table>
            </div>
            <div>
                <h3 style="font-size:0.9rem; color:#22662c; margin:8px 0 4px;">Menudeo</h3>
                <table class="minit">
                    <thead><tr><th>Cliente</th><th>Total</th></tr></thead>
                    <tbody>
                        <tr><td>Luis Martínez</td><td><span class="fondototal">$2,920</span></td></tr>
                        <tr><td>Karla Juárez</td><td><span class="fondototal">$3,488</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="cabezacard">
            <i class="fas fa-id-card"></i>
            <h2>Datos del cliente</h2>
        </div>
        <div class="scroll-area">
            <div class="info-cliente-grid">
                <div class="info-item"><span class="info-label">Nombre</span><span class="info-value">Juan Pérez</span></div><br>
                <div class="info-item"><span class="info-label">RFC</span><span class="info-value">JPR9ZUAN8ERZ1</span></div><br>
                <div class="info-item"><span class="info-label">Dirección</span><span class="info-value">C Principal 123, Veracruz</span></div><br>
                <div class="info-item"><span class="info-label">Contacto</span><span class="info-value">untaljuan@gmail.com</span></div>
            </div>

            

            <div class="tipoc">
                <div class="info-item"><span class="info-label">Tipo de cliente</span></div>
                <button class="activo"><i class="fas fa-check-circle"></i> Contado/Menudeo</button>
                <button><i class="fas fa-credit-card"></i> Crédito/Mayoreo</button>
            </div>

           
        </div>
    </div>

    <div class="card">
        <div class="cabezacard">
            <i class="fas fa-history"></i>
            <h2>Historial de compras</h2>
            <span class="fondo">4 movimientos</span>
        </div>
        <div class="scroll-area">
            <div class="historiallist historial-header">
                <div>Fecha</div><div>Pedido</div><div>Cant</div><div>Desc</div><div>Monto</div><div>Estatus</div>
            </div>
            <div class="historiallist">
                <div>23/01/26</div><div>1 Caja</div><div>Fresa</div><div>Fresa</div><div>$8,500</div><div><span class="status entregado">Entregado</span></div>
            </div>
            <div class="historiallist">
                <div>23/01/26</div><div>1 Ton</div><div>Jitomate</div><div>Saladet</div><div>$11,500</div><div><span class="status entregado">Entregado</span></div>
            </div>
            <div class="historiallist">
                <div>23/01/26</div><div>1 Caja</div><div>Sandía</div><div>Sin semilla</div><div>$9,780</div><div><span class="status enviado">Enviado</span></div>
            </div>
            <div class="historiallist">
                <div>23/01/26</div><div>3 Cajas</div><div>Tomate</div><div>Bola</div><div>$8,500</div><div><span class="status pendiente">Pendiente</span></div>
            </div>
            <div style="margin-top:16px; background:#fcf9f0; border-radius:16px; padding:10px; font-size:0.8rem;">
                <i class="fas fa-info-circle" style="color:#f16b1a;"></i> Última compra: 23/01/26 · $11,500
            </div>
        </div>
    </div>

</div>

</body>
</html>