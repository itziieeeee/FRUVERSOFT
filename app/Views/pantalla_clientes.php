<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER·Clientes</title>
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
        .marriba {
            background: #1d4a27;
            padding: 8px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px 15px;
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
        .logo-area h1 {
            color: white;
            font-size: 1.5rem;
            font-weight: 500;
            border-left: 2px solid rgba(255,255,255,0.3);
            padding-left: 16px;
            margin: 0;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
        }
        .btnmenu {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            padding: 8px 18px;
            border-radius: 40px;
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: 0.2s;
        }
        .btnmenu:hover {
            background: white;
            color: #1d4a27;
        }

        .buscador {
            background: white;
            border-radius: 40px;
            padding: 3px 3px 3px 18px;
            display: flex;
            align-items: center;
        }
        .buscador input {
            border: none;
            padding: 8px 0;
            width: 180px;
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
        }

        /* Contenedor de las 3 tarjetas o tablas */
        .tarjeta {
            display: grid;
            grid-template-columns: 1.2fr 1.3fr 1.3fr; /* 3 columnas proporcionales */
            gap: 16px;
            padding: 18px 24px;
            height: calc(100vh - 80px); /* Ocupa todo menos header */
            overflow: hidden;
        }

        /* TARJETAS  */
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

        /* Área scrolleable dentro de cada card */
        .scroll-area {
            overflow-y: auto;
            padding-right: 6px;
            flex: 1;
            min-height: 0; /* importante para flex child */
        }

        /* Tablas  */
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

        /* datos cliente  */
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

        /* tipo cliente  */
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

        /* historial */
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

        /* barras de crédito */
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
        .barracredito {
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

        /* botón agregar cliente */
        .botonclienten{
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
            text-decoration:none; 
        }

        /* scroll personalizado */
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
        /* opciones del menu */
       .menu-opciones-extra{
        padding: 0;
        }

        .opciones-extra{
        display:flex;
        align-items:center;
        gap:12px;
        flex-wrap:nowrap;
        }

        .boton-extra{
        background:white;
        border-radius:25px;
        padding:25px 10px;
        text-align:center;
        text-decoration:none;
        font-weight:700;
        color:#1e3a2f;
        font-size:16px;
        border:2px solid transparent;
        transition:0.3s;
        box-shadow:0 6px 18px rgba(0,0,0,0.08);
       }

       .boton-extra:hover{
       transform:translateY(-6px);
       border-color:#f16b1a;
       box-shadow:0 10px 25px rgba(0,0,0,0.15);
       color:#f16b1a;
       }
    </style>
</head>
<body>
    <div class="marriba">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo" width="140">
            <h1>Clientes</h1>
        </div>
    
<div class="menu-opciones-extra">
    <div class="opciones-extra">

        <a href="#" class="boton-extra">Precios</a>

        <a href="#" class="boton-extra">Pagos</a>

        <a href="#" class="boton-extra">Pedidos</a>

        <a href="<?= base_url('pantalla_clientes') ?>" class="boton-extra">Clientes</a>

        <a href="#" class="boton-extra">Inventario</a>

        <a href="#" class="boton-extra">Facturación</a>

        <a href="#" class="boton-extra">Reportes</a>

    </div>
</div>
        <div class="action-buttons">
            <a href="#" class="btnmenu"><i class="fas fa-user-shield"></i> Admin</a>
            <a href="#" class="btnmenu"><i class="fas fa-bell"></i> Notificaciones</a>
            <a href="#" class="btnmenu"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
        <div class="buscador">
            <input type="text" placeholder="Buscar...">
            <button><i class="fas fa-search"></i></button>
        </div>
    </div>

    <!-- Contenedor de las 3 tarjetas o tablas  -->
    <div class="tarjeta">

        <!-- parte uno clientes de los 2 tipos -->
        <div class="card">
            <div class="cabezacard">
                <i class="fas fa-users"></i>
                <h2>Clientes</h2>
                <span class="fondo">n registros</span>
              <a href="<?= base_url('pantalla_rcliente') ?>" method="POST"  class="botonclienten"><i class="fas fa-plus-circle"></i> Nuevo</a>
            </div>
            <div class="scroll-area">
                <!-- Mayoreo  -->
                <div style="margin-bottom: 12px;">
                    <h3 style="font-size:0.9rem; color:#22662c; margin-bottom:4px;">Mayoreo</h3>
                    <table class="minit">
                        <thead><tr><th>Negocio</th>
                        <th>Cliente</th>
                        <th>Total</th></tr></thead>
                        <tbody>
                            <tr><td>Distribuidora "Por Salud"</td>
                            <td>Juan Pérez</td><td><span class="fondototal">$7,890</span></td></tr>
                            <tr><td>Verduleña "Mi casita"</td>
                            <td>Karla Juárez</td><td>
                            <span class="fondototal">$5,488</span></td></tr>
                            <tr><td>Huerto Dorado</td>
                            <td>Kenia Flores</td><td>
                            <span class="fondototal">$5,400</span></td></tr>
                            <tr><td>Frutas "El Edén"</td>
                            <td>Luis Martínez</td><td><span class="fondototal">$4,920</span></td></tr>
                        </tbody>
                    </table>
                </div>
                <!--  Menudeo  -->
                <div>
                    <h3 style="font-size:0.9rem; color:#22662c; margin:8px 0 4px;"> Menudeo</h3>
                    <table class="minit">
                        <thead>
                            <tr>
                                <th>Negocio</th>
                                <th>Cliente</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Frutas "Max"</td>
                            <td>Luis Martínez</td>
                            <td><span class="fondototal">$2,920</span></td></tr>
                            <tr><td>Verduleria "El periquito"</td>
                            <td>Karla Juárez</td>
                            <td><span class="fondototal">$3,488</span></td></tr>
                            <tr><td>Raices deliciosas</td>
                            <td>Kenia Flores</td><td>
                            <span class="fondototal">$3,400</span></td></tr>
                            <tr><td>Distribuidora "Sabor a campo"</td>
                            <td>Juan Pérez</td><td>
                            <span class="fondototal">$4,890</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- DATOS DEL CLIENTE -->
        <div class="card">
            <div class="cabezacard">
                <i class="fas fa-id-card"></i>
                <h2>Datos del cliente</h2>
            </div>
            <div class="scroll-area">
                <!-- Info principal en cuadrícula compacta -->
                <div class="info-cliente-grid">
                    <div class="info-item"><span class="info-label">Nombre</span><span class="info-value">Juan Pérez</span></div>
                    <div class="info-item"><span class="info-label">RFC</span><span class="info-value">JPR9ZUAN8ERZ1</span></div>
                    <div class="info-item"><span class="info-label">Dirección</span><span class="info-value">C Principal 123, Veracruz</span></div>
                    <div class="info-item"><span class="info-label">Contacto</span><span class="info-value">untaljuan@gmail.com</span></div>
                </div>
                
                <!-- Clasificación actual -->
                <div style="margin:8px 0 12px; display:flex; align-items:center; gap:10px;">
                    <span class="tag-cliente"><i class="fas fa-store"></i> Crédito / Mayoreo</span>
                    <span style="font-size:0.7rem; background:#f3f9f0; padding:4px 10px; border-radius:30px;">límite $50k</span>
                </div>

                <!-- Botones de tipo de cliente-->
                <div class="tipoc">
                    <button class="activo"><i class="fas fa-check-circle"></i> Contado/Menudeo</button>
                    <button><i class="fas fa-credit-card"></i> Crédito/Mayoreo</button>
                </div>

                <!-- Limite de crédito compacto (ya no en columna 3) -->
                <div class="credito-mini">
                    <div class="li">
                        <span>Límite autorizado</span>
                        <span class="nlimite">$50,000</span>
                    </div>
                    <div class="barra-credito">
                        <div class="barrautilizada" style="width:25%;">$12.5k</div>
                        <div class="barradisponible" style="width:75%;">$37.5k</div>
                    </div>
                    <div class="li">
                        <span><i class="fas fa-circle" style="color:#1f8b4c;"></i> Usado: $12,500</span>
                        <span><i class="fas fa-circle" style="color:#f5a35c;"></i> Disponible: $37,500</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- historial -->
        <div class="card">
            <div class="cabezacard">
                <i class="fas fa-history"></i>
                <h2>Historial de compras</h2>
                <span class="fondo">4 movimientos</span>
            </div>
            <div class="scroll-area">
                <!-- Cabecera del historial -->
                <div class="historiallist historial-header">
                    <div>Fecha</div><div>Pedido</div><div>Cant</div><div>Desc</div><div>Monto</div><div>Estatus</div>
                </div>

                <!-- Filas de historial -->
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

                <!-- Espacio extra -->
                <div style="margin-top:16px; background:#fcf9f0; border-radius:16px; padding:10px; font-size:0.8rem;">
                    <i class="fas fa-info-circle" style="color:#f16b1a;"></i> Última compra: 23/01/26 · $11,500
                </div>
            </div>
        </div>
    </div>

</body>
</html>