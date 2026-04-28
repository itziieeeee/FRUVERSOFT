<!DOCTYPE html>
<html lang="es">
<head>
    <!-- ESTA ES LA PANTALLA DE PRODUCTOS -->
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Productos</title>
    
    <style>
        /* ===== ESTILOS BASE ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: #2d3748;
        }

        /* ===== BARRA SUPERIOR ===== */
        .barra-superior {
            background: linear-gradient(135deg, #1a5f3a 0%, #0d3b22 100%);
            padding: 0.8rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .logo-area img {
            max-height: 55px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .buscador {
            display: flex;
            flex: 1;
            max-width: 400px;
            margin: 0 1rem;
        }

        .buscador input {
            flex: 1;
            padding: 0.7rem 1rem;
            border: none;
            border-radius: 30px 0 0 30px;
            font-size: 0.9rem;
            outline: none;
            background-color: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .buscador button {
            background-color: #f5a623;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 0 30px 30px 0;
            cursor: pointer;
            color: white;
            transition: background 0.3s;
        }

        .buscador button:hover {
            background-color: #e69500;
        }

        .user-actions {
            display: flex;
            gap: 0.8rem;
        }

        .btn-user {
            background-color: rgba(255,255,255,0.15);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            text-decoration: none;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .btn-user:hover {
            background-color: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        /* ===== MENÚ DE NAVEGACIÓN ===== */
        .menu-navegacion {
            background-color: white;
            border-bottom: 3px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-links {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            flex-wrap: wrap;
        }

        .nav-link {
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            color: #4a5568;
            font-weight: 500;
            border-radius: 30px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .nav-link i {
            font-size: 1.1rem;
        }

        .nav-link:hover {
            background-color: #f0fff4;
            color: #1a5f3a;
        }

        .nav-link.activo {
            background: linear-gradient(135deg, #1a5f3a, #0d3b22);
            color: white;
            box-shadow: 0 4px 10px rgba(26,95,58,0.3);
        }

        /* ===== CONTENEDOR CATÁLOGO ===== */
        .catalogo-container {
            max-width: 1300px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .catalogo-container h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: #1a5f3a;
            border-left: 5px solid #f5a623;
            padding-left: 1rem;
            font-weight: 600;
        }

        /* ===== GRID DE CARDS ===== */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.8rem;
            margin-bottom: 2rem;
        }

        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.12);
        }

        .card-img {
            background: #f7fafc;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-bottom: 2px solid #e2e8f0;
        }

        .card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s;
        }

        .product-card:hover .card-img img {
            transform: scale(1.05);
        }

        .card-info {
            padding: 1rem 1rem 1.2rem;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .product-desc {
            font-size: 0.85rem;
            color: #718096;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* ===== PAGINACIÓN ===== */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .pagination a, 
        .pagination strong {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 0.8rem;
            border-radius: 8px;
            background-color: white;
            color: #4a5568;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
        }

        .pagination a:hover {
            background-color: #f0fff4;
            border-color: #1a5f3a;
            color: #1a5f3a;
        }

        .pagination strong {
            background: linear-gradient(135deg, #1a5f3a, #0d3b22);
            color: white;
            border: none;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .barra-superior {
                flex-direction: column;
                text-align: center;
            }
            
            .buscador {
                max-width: 100%;
                width: 100%;
                margin: 0.5rem 0;
            }
            
            .user-actions {
                justify-content: center;
            }
            
            .nav-links {
                gap: 0.3rem;
            }
            
            .nav-link {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }
            
            .catalogo-container {
                padding: 0 1rem;
            }
            
            .cards-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
            }
            
            .card-img {
                height: 150px;
            }
        }

        @media (max-width: 480px) {
            .cards-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            }
            
            .product-name {
                font-size: 0.95rem;
            }
            
            .product-desc {
                font-size: 0.75rem;
            }
            
            .catalogo-container h2 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>

    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo" width="140">
        </div>

        <form action="<?= base_url('pantalla_productos') ?>" method="GET" class="buscador">
        <input type="text" name="q" placeholder="Buscar producto..." value="<?= isset($_GET['q']) ? esc($_GET['q']) : '' ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
        </form>


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

    <div class="catalogo-container">
        <h2>Catálogo de Productos</h2>

        <!-- CARDS DESDE BD -->
        <div class="cards-grid">
            <?php foreach($productos as $prod): ?>
                <div class="product-card">
                    <div class="card-img">
                        <img src="<?= $prod['imagen'] 
                            ? base_url('uploads/productos/'.$prod['imagen']) 
                            : 'https://placehold.co/200x150?text=FRUVER' ?>">
                    </div>
                    <div class="card-info">
                        <div class="product-name">
                            <?= esc($prod['nombre']) ?>
                        </div>
                        <div class="product-desc">
                            <?= esc($prod['descripcion']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:30px; text-align:center;">
            <?= $pager->links() ?>
        </div>
    </div>

</body>
</html>