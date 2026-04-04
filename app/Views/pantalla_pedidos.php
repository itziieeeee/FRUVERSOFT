<!DOCTYPE html>
<html lang="es">
<head>
    <!--ESTA ES LA PANTALLA DE CLIENTES -->
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Clientes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #eef3e9;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #1a2e1f;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .barra-superior {
            background: #1d4a27;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            box-shadow: 0 4px 12px rgba(0,30,0,0.2);
            flex-shrink: 0;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo-area img {
            width: 110px;
            filter: brightness(1.1);
        }

        .buscador {
            background: white;
            border-radius: 40px;
            padding: 3px 3px 3px 18px;
            display: flex;
            align-items: center;
            flex: 0 1 300px;
            max-width: 350px;
        }
        .buscador input {
            border: none;
            padding: 8px 0;
            width: 100%;
            outline: none;
            font-size: 0.9rem;
        }
        .buscador button {
            background: #f16b1a;
            border: none;
            border-radius: 40px;
            width: 38px;
            height: 38px;
            color: white;
            cursor: pointer;
            flex-shrink: 0;
        }

        .user-actions {
            display: flex;
            gap: 8px;
        }
        .btn-user {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 40px;
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: 0.2s;
            white-space: nowrap;
        }
        .btn-user:hover {
            background: white;
            color: #1d4a27;
        }

        /* --- MENÚ DE NAVEGACIÓN --- */
        .menu-navegacion {
            background-color: #ffffff;
            padding: 0 24px;
            border-bottom: 1px solid #dde8d8;
            flex-shrink: 0;
        }

        .nav-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        .nav-link {
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            border-radius: 0;
            padding: 14px 24px;
            text-decoration: none;
            font-weight: 600;
            color: #3a3a3a;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s ease;
            box-shadow: none;
        }

        .nav-link i {
            color: #2d7a3a;
            font-size: 1rem;
        }

        .nav-link:hover {
            color: #1d4a27;
            border-bottom: 3px solid #f16b1a;
            transform: none;
            background: none;
            box-shadow: none;
        }

        .nav-link.activo {
            color: #1d4a27;
            background: none;
            border-bottom: 3px solid #f16b1a;
            font-weight: 700;
        }

        .nav-link.activo i {
            color: #2d7a3a;
        }

        .tarjeta {
            display: grid;
            grid-template-columns: 1.2fr 1.3fr 1.3fr;
            gap: 36px;
            padding: 0 14px 10px 20px;
            height: calc(90vh - 140px);
            overflow: hidden;
            margin-top: 20px;
        }

        .card {
            background: white;
            border-radius: 28px;
            padding: 18px 16px;
            box-shadow: 0 8px 22px rgba(40, 70, 40, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(120, 160, 120, 0.2);
        }

        .cabezacard {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            flex-shrink: 0;
        }
        .cabezacard i {
            font-size: 1.6rem;
            color: #f16b1a;
            background: #fff1e0;
            padding: 8px;
            border-radius: 16px;
        }
        .cabezacard h2 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1d4a27;
        }
        .cabezacard .fondo {
            background: #d1e6cf;
            margin-left: auto;
            padding: 5px 12px;
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #1a4a1a;
        }

        .scroll-area {
            overflow-y: auto;
            padding-right: 6px;
            flex: 1;
            min-height: 0;
        }

        .minit {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .minit th {
            text-align: left;
            padding: 8px 4px 4px 4px;
            color: #2d6e3b;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #cde0ca;
        }
        .minit td {
            padding: 10px 4px;
            border-bottom: 1px solid #e2eedf;
        }
        .minit tr:last-child td {
            border-bottom: none;
        }

        .fondototal {
            background: #f16b1a10;
            color: #b84500;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.8rem;
            border: 1px solid #f16b1a60;
            white-space: nowrap;
        }

        .tag-cliente {
            background: #1d4a27;
            color: white;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .info-cliente-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 12px;
            background: #f5faf4;
            padding: 12px;
            border-radius: 20px;
            margin-bottom: 16px;
            font-size: 0.85rem;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 0.7rem;
            color: #4d6b53;
            text-transform: uppercase;
        }
        .info-value {
            font-weight: 600;
            color: #1d3a24;
            word-break: break-word;
        }

        .tipoc {
            display: flex;
            gap: 8px;
            margin: 12px 0;
        }
        .tipoc button {
            flex: 1;
            background: #eef5ec;
            border: 1.5px solid #bad2b4;
            border-radius: 30px;
            padding: 8px 6px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #1a4a27;
            cursor: pointer;
        }
        .tipoc button.activo {
            background: #1d4a27;
            border-color: #1d4a27;
            color: white;
        }

        .historiallist {
            display: grid;
            grid-template-columns: 70px 60px 50px 1fr 70px 80px;
            gap: 4px;
            padding: 8px 0;
            border-bottom: 1px dashed #c5ddc0;
            font-size: 0.75rem;
            align-items: center;
        }
        .historial-header {
            font-weight: 700;
            color: #20612e;
            border-bottom: 2px solid #b1d2aa;
            padding-bottom: 6px;
            margin-bottom: 4px;
        }

        .status {
            padding: 3px 8px;
            border-radius: 40px;
            text-align: center;
            font-weight: 600;
            font-size: 0.7rem;
        }
        .status.entregado { background: #daf1da; color: #156b2c; }
        .status.enviado { background: #ffe5cc; color: #b65000; }
        .status.pendiente { background: #fff0c0; color: #866e1c; }

        .credito-mini {
            background: #eef6ec;
            border-radius: 20px;
            padding: 14px;
            margin-top: 12px;
        }
        .nlimite {
            font-weight: 800;
            font-size: 1.4rem;
            color: #1d632d;
        }
        .barra-credito {
            background: #cfdecb;
            border-radius: 30px;
            height: 24px;
            margin: 10px 0;
            display: flex;
            overflow: hidden;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .barrautilizada {
            background: #1f8b4c;
            color: white;
            display: flex;
            align-items: center;
            padding-left: 12px;
        }
        .barradisponible {
            background: #fed7b0;
            color: #633f00;
            display: flex;
            align-items: center;
            padding-left: 12px;
        }
        .li {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .botonclienten {
            background: #f16b1a;
            color: white;
            border: none;
            border-radius: 40px;
            padding: 8px 14px;
            font-weight: 600;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
            text-decoration: none;
        }

        .scroll-area::-webkit-scrollbar {
            width: 6px;
        }
        .scroll-area::-webkit-scrollbar-thumb {
            background: #b8d4b0;
            border-radius: 10px;
        }

        @media (max-width: 1100px) {
            .tarjeta {
                grid-template-columns: 1fr;
                height: auto;
                overflow: auto;
            }
            body { overflow: auto; height: auto; }
        }
    </style>
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
            <a href="pantalla_pedidos" class="nav-link activo"><i class="fas fa-truck"></i> Pedidos</a>
            <a href="<?=base_url('pantalla_inventario')?>" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="pantalla_clientes" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
            <a href="pantalla_repartidores" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
            <a href="pantalla_productos" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
        </div>
    </nav>

</body>
</html>