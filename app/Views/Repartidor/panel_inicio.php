<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>FRUVER · Panel Repartidor</title>
    
    <link rel="stylesheet" href="<?= base_url('css/menusolo.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%); min-height: 100vh; color: #1e293b; }
        .dashboard-container { max-width: 1400px; margin: 0 auto; padding: 24px 32px; }
        .top-bar { background: linear-gradient(135deg, #1e5631 0%, #2d6a3b 100%); border-radius: 28px; padding: 12px 28px; margin-bottom: 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12); }
        .logo-img { height: 75px; object-fit: contain; }
        .admin-badge { display: flex; align-items: center; gap: 10px; background: rgba(255, 255, 255, 0.98); padding: 8px 20px; border-radius: 60px; }
        .admin-badge i { font-size: 1.4rem; color: #f16b1a; }
        .admin-badge span { font-weight: 600; font-size: 0.9rem; color: #f16b1a; }
        .action-buttons { display: flex; gap: 12px; }
        .action-btn { display: inline-flex; align-items: center; gap: 8px; padding: 8px 20px; background: rgba(255, 255, 255, 0.12); border-radius: 60px; text-decoration: none; font-size: 0.85rem; font-weight: 500; color: white; border: 1px solid rgba(255, 255, 255, 0.25); }
        .action-btn:hover { background: #dc2626; }
        .modules-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 28px; max-width: 800px; margin: 0 auto; }
        .module-card { background: white; border-radius: 28px; padding: 28px 20px; text-align: center; text-decoration: none; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06); transition: all 0.3s; position: relative; overflow: hidden; }
        .module-card:hover { transform: translateY(-8px); box-shadow: 0 20px 35px rgba(0, 0, 0, 0.12); }
        .module-img { width: 100%; max-width: 100px; margin-bottom: 16px; }
        .module-title { font-size: 1.2rem; font-weight: 700; color: #1e293b; }
        h1 { font-size: 2.2rem; font-weight: 700; color: #1e5631; margin-bottom: 8px; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <div class="top-bar">
        <img src="<?= base_url('img/LOGO1.png') ?>" class="logo-img" alt="Logo">
        <div class="logo-area">        
            <div class="admin-badge">
                <i class="fa-solid fa-motorcycle"></i> 
                <span>Repartidor: <?= session()->get('nombre') ?></span>
            </div>
        </div>
        <div class="action-buttons">
            <a href="<?= base_url('logout') ?>" class="action-btn">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>
    </div>

    <div class="welcome-section" style="text-align: center; margin-bottom: 40px;">
        <h1>¡Bienvenido de vuelta, <?= session()->get('nombre') ?>!</h1>
        <p style="color: #64748b;">Selecciona una opción para comenzar tu ruta.</p>
    </div>

    <div class="modules-grid">
        <a href="<?= base_url('repartidor/pedidos') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/PEDIDOS.png') ?>" alt="Mis Pedidos" class="module-img">
            </div>
            <span class="module-title">Mis Pedidos Asignados</span>
        </a>

        <a href="<?= base_url('repartidor/inventario') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/INVENTARIO1.png') ?>" alt="Inventario" class="module-img">
            </div>
            <span class="module-title">Consultar Inventario</span>
        </a>
    </div>
</div>
</body>
</html>