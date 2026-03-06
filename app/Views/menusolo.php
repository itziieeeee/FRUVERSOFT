<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

         body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9edf5 100%);
            font-family: 'Inter', 'Segoe UI', Roboto, system-ui, sans-serif;
            min-height: 100vh;
        }
        .fondo{
            width:95%;
            margin:10px auto;
        }

        /* ===== BARRA SUPERIOR MEJORADA ===== */
        .menuarriba {
            background: white;
            border-radius: 16px;
            padding: 8px 20px 8px 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
            backdrop-filter: blur(10px);
        }

        /* Logo con fondo más elegante */
        .logo {
            background: linear-gradient(135deg, #2d5a27 0%, #1e8a3e 100%);
            padding: 10px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(45, 90, 39, 0.3);
            
    
        }

        

        /* Título Administrador más elegante */
        .admin-titulo {
            display: flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 8px 20px;
            border-radius: 40px;
            border: 1px solid rgba(45, 90, 39, 0.1);
            box-shadow: inset 0 2px 4px rgba(255,255,255,0.8), 0 2px 4px rgba(0,0,0,0.05);
        }

        .admin-titulo i {
            color: #2d5a27;
            font-size: 20px;
            background: white;
            padding: 8px;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(45, 90, 39, 0.2);
        }

        .admin-titulo span {
            color: #1e293b;
            font-weight: 600;
            font-size: 18px;
            letter-spacing: 0.3px;
        }

        .admin-titulo .badge {
            background: #2d5a27;
            color: white;
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 20px;
            margin-left: 5px;
            font-weight: 500;
        }

        /* Opciones de la barra mejoradas */
        .opcionesdelabarra {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-left: auto;
        }

        .boton {
            background: transparent;
            color: #4a5568;
            padding: 10px 18px;
            border-radius: 40px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            text-decoration: none;
            font-size: 14px;
            border: 1px solid transparent;
        }

        .boton i {
            color: #2d5a27;
            font-size: 16px;
            transition: 0.3s;
        }

        .boton:hover {
            background: #f0f9f0;
            border-color: #2d5a27;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 90, 39, 0.15);
        }

        .boton:hover i {
            transform: scale(1.1);
        }

        .boton.cerrar-sesion {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .boton.cerrar-sesion i {
            color: #dc2626;
        }

        .boton.cerrar-sesion:hover {
            background: #fecaca;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
        }

        /* Buscador mejorado */
        .buscador {
            background: #f8fafc;
            border-radius: 40px;
            padding: 4px 4px 4px 20px;
            display: flex;
            align-items: center;
            min-width: 250px;
            border: 1px solid #e2e8f0;
            transition: 0.3s;
        }

        .buscador:focus-within {
            border-color: #2d5a27;
            box-shadow: 0 0 0 3px rgba(45, 90, 39, 0.1);
            background: white;
        }

        .buscador input {
            border: none;
            outline: none;
            flex: 1;
            padding: 12px 0;
            background: transparent;
            font-size: 14px;
            color: #1e293b;
        }

        .buscador input::placeholder {
            color: #94a3b8;
        }

        .buscador button {
            background: linear-gradient(135deg, #2d5a27 0%, #1e8a3e 100%);
            border: none;
            border-radius: 40px;
            width: 42px;
            height: 42px;
            color: white;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .buscador button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(45, 90, 39, 0.3);
        }

        /* ===== BOTONES PRINCIPALES GRANDES ===== */
        .opciones{
    display:grid;
    grid-template-columns: repeat(4, 1fr);
    justify-content:center;
    gap:20px;
    margin-top:7px;
}
       .botonesopciones{
    background:white;
    border-radius:20px;
    padding:4px 4px;
    text-align:center;
    text-decoration:none;
    transition:0.3s;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:4px;
    min-height:100px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
    height:200px ;
}
        .botonesopciones:hover{
            transform:translateY(-12px);
            border-color:#f16b1a;
            box-shadow:0 15px 35px rgba(0,0,0,0.15);
        }

        .botonesopciones img{
            width:130px;
            height:130px;
            object-fit:contain;
            transition:0.3s;
        }

        .botonesopciones:hover img{
            transform:scale(1.1);
        }

        .botonesopciones span{
            font-weight:700;
            color:#1e3a2f;
            font-size:18px;
            background:#e8f5e9;
            padding:10px 25px;
            border-radius:40px;
        }

        /* ===== RESPONSIVE ===== */
        @media(max-width:768px){
            .menuarriba{
                flex-direction:column;
                align-items:flex-start;
            }
        }

    </style>
</head>
<body>

<div class="fondo">

    <!-- BARRA SUPERIOR -->
    <div class="menuarriba">
        <div class="logo">
            <img src="<?= base_url('img/LOGO1.png') ?>" width="140">
        </div>
<h1> Administrador
</h1>
        <div class="opcionesdelabarra">
            <a href="#" class="boton"><i class="fas fa-bell"></i> Notificaciones</a>
            <a href="#" class="boton"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
        </div>

        <div class="buscador">
            <input type="text" placeholder="Buscar...">
            <button><i class="fas fa-search"></i></button>
        </div>
    </div>

    <!-- BOTONES GRANDES -->
    <div class="opciones">
        <a href="#" class="botonesopciones">
            <img src="<?= base_url('img/PRECIOS.png') ?>">
            <span>Precios</span>
        </a>

        <a href="#" class="botonesopciones">
            <img src="<?= base_url('img/PAGOS.png') ?>">
            <span>Pagos</span>
        </a>

        <a href="#" class="botonesopciones">
            <img src="<?= base_url('img/PEDIDOS.png') ?>">
            <span>Pedidos</span>
        </a>

        <a href="<?= base_url('pantalla_clientes') ?>" class="botonesopciones">
            <img src="<?= base_url('img/CLIENTES.png') ?>">
            <span>Clientes</span>
        </a>

        <a href="<?= base_url('pantalla_inventario') ?>" class="botonesopciones">
    <img src="<?= base_url('img/INVENTARIO1.png') ?>">
    <span>Inventario</span>
</a>

        <a href="#" class="botonesopciones">
            <img src="<?= base_url('img/FAC.png') ?>">
            <span>Facturación</span>
        </a>

        <a href="#" class="botonesopciones">
            <img src="<?= base_url('img/REPORTES.png') ?>">
            <span>Reportes</span>
        </a>
    </div>

</div>

</body>
</html>