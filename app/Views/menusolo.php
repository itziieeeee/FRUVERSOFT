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

        body{
            background:#f0f4f8;
            font-family:'Segoe UI', Roboto, system-ui, sans-serif;
        }

        .fondo{
            width:95%;
            margin:20px auto;
        }

        /* ===== BARRA SUPERIOR ===== */
        .menuarriba{
            background:#2d5a27;
            border-radius:30px;
            padding:15px 30px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            margin-bottom:50px;
            flex-wrap:wrap;
            gap:20px;
        }

        .opcionesdelabarra{
            display:flex;
            gap:15px;
            flex-wrap:wrap;
        }

        .boton{
            background:rgba(255,255,255,0.15);
            border:1px solid rgba(255,255,255,0.3);
            color:white;
            padding:12px 22px;
            border-radius:40px;
            font-weight:600;
            display:flex;
            align-items:center;
            gap:10px;
            transition:0.3s;
            text-decoration:none;
            font-size:15px;
        }

        .boton:hover{
            background:white;
            color:#2d5a27;
            transform:translateY(-3px);
        }

        .buscador{
            background:white;
            border-radius:50px;
            padding:5px 5px 5px 20px;
            display:flex;
            align-items:center;
            min-width:250px;
            box-shadow:0 5px 15px rgba(0,0,0,0.1);
        }

        .buscador input{
            border:none;
            outline:none;
            flex:1;
            padding:10px 0;
        }

        .buscador button{
            background:#2d5a27;
            border:none;
            border-radius:50px;
            width:40px;
            height:40px;
            color:white;
            cursor:pointer;
        }

        /* ===== BOTONES PRINCIPALES GRANDES ===== */
        .opciones{
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(220px,1fr));
            gap:30px;
        }

        .botonesopciones{
            background:white;
            border-radius:30px;
            padding:50px 20px;
            text-align:center;
            text-decoration:none;
            transition:0.4s;
            border:2px solid transparent;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:20px;
            min-height:240px;
            box-shadow:0 10px 25px rgba(0,0,0,0.08);
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

        <div class="opcionesdelabarra">
            <a href="#" class="boton"><i class="fas fa-user-shield"></i> Administrador</a>
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

        <a href="#" class="botonesopciones">
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