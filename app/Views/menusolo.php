<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Menú Administrativo</title>
    <link rel="stylesheet" href="<?= base_url('css/menusolo.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <style>
        /* ===== RESET Y BASE ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', 'Segoe UI', system-ui, -apple-system, sans-serif;
    background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
    min-height: 100vh;
    color: #1e293b;
}

.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px 32px;
}

/* ===== BARRA SUPERIOR ===== */
.top-bar {
    background: linear-gradient(135deg, #1e5631 0%, #2d6a3b 100%);
    border-radius: 28px;
    padding: 12px 28px;
    margin-bottom: 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

.logo-img {
    height: 75px;
    width: auto;
    object-fit: contain;
}



.admin-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.98);
    padding: 8px 20px;
    border-radius: 60px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease;
}

.admin-badge:hover {
    transform: translateY(-2px);
}

.admin-badge i {
    font-size: 1.4rem;
    color: #1e5631;
}

.admin-badge span {
    font-weight: 600;
    font-size: 0.9rem;
    color: #1e5631;
}

/* Búsqueda */
.search-wrapper {
    flex: 1;
    max-width: 360px;
    position: relative;
}

.search-wrapper input {
    width: 100%;
    padding: 12px 50px 12px 20px;
    border: none;
    border-radius: 60px;
    font-size: 0.9rem;
    background: rgba(255, 255, 255, 0.98);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.search-wrapper input:focus {
    outline: none;
    box-shadow: 0 0 0 4px rgba(241, 107, 26, 0.3);
}

.search-wrapper button {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: #f16b1a;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-wrapper button:hover {
    background: #e05a0c;
    transform: translateY(-50%) scale(1.08);
}

/* Botones de acción */
.action-buttons {
    display: flex;
    gap: 12px;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 60px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: white;
}

.action-btn:hover {
    background: #f16b1a;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(241, 107, 26, 0.35);
}

.logout-btn:hover {
    background: #dc2626;
}

/* Sección de bienvenida */
.welcome-section {
    margin-bottom: 32px;
    padding-left: 8px;
}

h1 {
    font-size: 2.2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #1e5631 0%, #f16b1a 100%);
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
    margin-bottom: 8px;
}

.welcome-subtitle {
    font-size: 1rem;
    color: #64748b;
    font-weight: 500;
}

/* ===== MÓDULOS ===== */
.modules-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
    margin-top: 16px;
}

.module-card {
    background: white;
    border-radius: 28px;
    padding: 28px 20px 24px;
    text-align: center;
    text-decoration: none;
    transition: all 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(0, 0, 0, 0.04);
    position: relative;
    overflow: hidden;
}

.module-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #1e5631, #f16b1a);
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.module-card:hover::before {
    transform: scaleX(1);
}

.module-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 35px rgba(0, 0, 0, 0.12);
}

.module-img {
    width: 100%;
    max-width: 100px;
    height: auto;
    margin-bottom: 16px;
    transition: all 0.4s ease;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.08));
}

.module-card:hover .module-img {
    transform: scale(1.08);
}

.module-title {
    display: block;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 6px;
    transition: color 0.3s ease;
}



.module-card:hover .module-title {
    color: #f16b1a;
}


/* Animación */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.module-card {
    animation: fadeInUp 0.5s ease backwards;
}

.module-card:nth-child(1) { animation-delay: 0.05s; }
.module-card:nth-child(2) { animation-delay: 0.1s; }
.module-card:nth-child(3) { animation-delay: 0.15s; }
.module-card:nth-child(4) { animation-delay: 0.2s; }
.module-card:nth-child(5) { animation-delay: 0.25s; }
.module-card:nth-child(6) { animation-delay: 0.3s; }

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .dashboard-container { padding: 20px 24px; }
    .modules-grid { gap: 22px; }
    h1 { font-size: 1.9rem; }
}

@media (max-width: 900px) {
    .modules-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .dashboard-container { padding: 16px; }
    
    .top-bar {
        flex-direction: column;
        padding: 20px;
        gap: 14px;
    }
    
    .logo-area { justify-content: center; }
    .search-wrapper { max-width: 100%; order: 3; }
    .action-buttons { justify-content: center; }
    
    h1 { font-size: 1.7rem; text-align: center; }
    .welcome-subtitle { text-align: center; }
}

@media (max-width: 580px) {
    .modules-grid {
        grid-template-columns: 1fr;
        max-width: 320px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .module-card {
        display: flex;
        align-items: center;
        gap: 20px;
        text-align: left;
        padding: 16px 20px;
    }
    
    .module-img { max-width: 60px; margin-bottom: 0; }
    .module-title { margin-bottom: 4px; }
    .card-icon-wrapper { flex-shrink: 0; }
}

@media (max-width: 480px) {
    .action-buttons { flex-direction: column; width: 100%; }
    .action-btn { justify-content: center; width: 100%; }
    .logo-img { height: 120px; }
    h1 { font-size: 1.5rem; }
}

/* Scrollbar */
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 10px; }
::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #1e5631, #2d6a3b); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #f16b1a; }
    </style>

<div class="dashboard-container">
    <!-- BARRA SUPERIOR MODERNA -->
    <div class="top-bar">
        <img src="<?= base_url('img/LOGO1.png') ?>" class="logo-img" alt="Logo">
        
        <div class="logo-area">        
            <div class="admin-badge">
                <i class="fa-solid fa-circle-user"></i> 
                <span>Administrador</span>
            </div>
        </div>

        <div class="search-wrapper">
            <input type="text" placeholder="Buscar módulo, producto o cliente...">
            <button aria-label="Buscar"><i class="fas fa-search"></i></button>
        </div>

        <div class="action-buttons">
            <a href="#" class="action-btn">
                <i class="fas fa-bell"></i> Notificaciones
            </a>
            <a href="<?= base_url('pantalla_inicio') ?>" class="action-btn logout-btn">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>
    </div>

    <div class="welcome-section">
        <h2>¡Bienvenido de vuelta!</h2>
       
    </div>

    <!-- MÓDULOS PRINCIPALES -->
    <div class="modules-grid">
        <a href="<?= base_url('pantalla_inventario') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/INVENTARIO1.png') ?>" alt="Inventario" class="module-img">
            </div>
            <span class="module-title">Inventario</span>
        </a>

        <a href="pantalla_ventas" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/VENTA.png') ?>" alt="Ventas" class="module-img">
            </div>
            <span class="module-title">Ventas</span>
        </a>

        <a href="pantalla_pedidos" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/PEDIDOS.png') ?>" alt="Pedidos" class="module-img">
            </div>
            <span class="module-title">Pedidos</span>
        </a>

        <a href="<?= base_url('pantalla_clientes') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/CLIENTES.png') ?>" alt="Clientes" class="module-img">
            </div>
            <span class="module-title">Clientes</span>
        </a>

        <a href="pantalla_repartidores" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/REPARTIDORES.png') ?>" alt="Repartidores" class="module-img">
            </div>
            <span class="module-title">Repartidores</span>
        </a>

        <a href="pantalla_productos" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/PRODUCTOS.png') ?>" alt="Productos" class="module-img">
            </div>
            <span class="module-title">Productos</span>
            
        </a>
    </div>
</div>

</body>
</html>