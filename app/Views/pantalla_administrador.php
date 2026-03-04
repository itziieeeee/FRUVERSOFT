<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Admin </title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f4f8;
            font-family: 'Segoe UI', Roboto, system-ui, sans-serif;
        }

        .fondo {
            max-width: 1600px;
            margin: 20px auto;
            padding: 0 25px;
        }

        .menuarriba {
            background: #2d5a27;
            border-radius: 30px;
            padding: 8px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .opcionesdelabarra {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .boton{
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 22px;
            border-radius: 40px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 15px;
        }

        .boton i { font-size: 18px; }
        /* hover al pasar el raton por el boton */
        .boton:hover {
            background: white;
            color: #2d5a27;
            transform: translateY(-3px);
            box-shadow: #0000;
        }

        .buscador {
            background: white;
            border-radius: 50px;
            padding: 5px 5px 5px 20px;
            display: flex;
            align-items: center;
            min-width: 280px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .buscador input {
            border: none;
            outline: none;
            flex: 1;
            padding: 12px 0;
            font-size: 15px;
        }

        .buscador button {
            background: #2d5a27;
            border: none;
            border-radius: 50px;
            width: 45px;
            height: 45px;
            color: white;
            cursor: pointer;
            transition: 0.3s;
        }
        .buscador button:hover { background:#f16b1a; transform: scale(1.05); }

        /* botones de opciones */
        .opciones {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 15px;
            margin-bottom: 40px;
        }

        .botonesopciones {
            background: white;
            border-radius: 25px;
            padding: 20px 12px;
            text-align: center;
            text-decoration: none;
            transition: all 0.4s;
            border: 2px solid transparent;
            box-shadow: #f16b1a;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .botonesopciones:hover {
            transform: translateY(-12px);
            border-color: #f16b1a;
            box-shadow: #f16b1a;
        }

        .botonesopciones img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            transition: 0.3s;
        }
        .botonesopciones:hover img { transform: scale(1.1); }

        .botonesopciones span {
            font-weight: 700;
            color: #1e3a2f;
            font-size: 16px;
            background: #e8f5e9;
            padding: 6px 15px;
            border-radius: 40px;
            width: fit-content;
        }

        /* contenedor de las tablas (3)*/
        .contenedortablas {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-top: 20px;
        }

        /* tablas */
        .tabla{
            background: white;
            border-radius: 30px;
            padding: 25px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            height: fit-content;
        }

        .tablatitle {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #2d5a27;
        }

        .tablatitle i {
            font-size: 24px;
            color: #2d5a27;
            background: #e8f5e9;
            padding: 10px;
            border-radius: 15px;
        }

        .tablatitle h3 {
            font-size: 20px;
            color: #1a2e1a;
            font-weight: 700;
        }

        /* estilo de la tabla */
        .tablaestilo {
            width: 100%;
            border-collapse: collapse;
        }
        .tablaestilo th {
            text-align: left;
            padding: 15px 12px;
            background: #f1f8e9;
            color: #1e3a2f;
            font-weight: 700;
            font-size: 15px;
            border-bottom: 3px solid #2d5a27;
        }
        .tablaestilo td {
            padding: 12px;
            border-bottom: 1px solid #f9f8f0;
            color: #2d3e2d;
        }
       /*fondo de los totales de clientes buenos*/
        .badge-total {
            background: #f16b1a;
            color: white;
            padding: 4px 12px;
            border-radius: 40px;
            font-weight: 600;
            display: inline-block;
            font-size: 13px;
        }

        /* productos */
        .productolista {
            list-style: none;
        }
        .item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed #ddd;
        }
        .item:last-child { border: none; }
        .productnombre {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }
        .productnombre i {
            color: #2d5a27;
            width: 20px;
        }
        .product-stock {
            background: #e8f5e9;
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 600;
            color: #1e3a2f;
        }


        /* ===== RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .contenedortablas { grid-template-columns: repeat(2, 1fr); }
            .opciones { grid-template-columns: repeat(4, 1fr); }
        }
        @media (max-width: 900px) {
            .contenedortablas { grid-template-columns: 1fr; }
            .opciones { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 700px) {
            .opciones { grid-template-columns: repeat(2, 1fr); }
            .menuarriba { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="fondo">

        <div class="menuarriba">
            <div class="logo">
                <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo" width="140">
            </div>
            <div class="opcionesdelabarra">
                <a href="#" class="boton"><i class="fas fa-user-shield"></i> Administrador</a>
                <a href="#" class="boton"><i class="fas fa-bell"></i> Notificaciones </a>
                <a href="<?= base_url('salir') ?>" class="boton"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
            </div>
            <div class="buscador">
                <input type="text" placeholder="Buscar...">
                <button><i class="fas fa-search"></i></button>
            </div>
        </div>

        <!-- OPCIONES-->
        <div class="opciones">
            <a href="#" class="botonesopciones"><img src="<?= base_url('img/PRECIOS.png') ?>"><span>Precios</span></a>
            <a href="#" class="botonesopciones"><img src="<?= base_url('img/PAGOS.png') ?>"><span>Pagos</span></a>
            <a href="#" class="botonesopciones"><img src="<?= base_url('img/PEDIDOS.png') ?>"><span>Pedidos</span></a>
           <a href="<?= base_url('pantalla_clientes') ?>" class="botonesopciones">
           <img src="<?= base_url('img/CLIENTES.png') ?>">
    <span>Clientes</span>
</a>
            
            <a href="#" class="botonesopciones"><img src="<?= base_url('img/INVENTARIO1.png') ?>"><span>Inventario</span></a>
            <a href="#" class="botonesopciones"><img src="<?= base_url('img/FAC.png') ?>"><span>Facturación</span></a>
            <a href="#" class="botonesopciones"><img src="<?= base_url('img/REPORTES.png') ?>"><span>Reportes</span></a>
        </div>

        <!-- TRES COLUMNAS CON LAS 3 TABLAS PRINCIPALES -->
        <div class="contenedortablas">
            
            <!-- COLUMNA 1: MEJORES CLIENTES -->
            <div class="tabla">
                <div class="tablatitle">
                    <!--<i class="fas fa-crown"></i>-->
                    <h3>Mejores Clientes</h3>
                </div>
                <table class="tablaestilo">
                    <thead>
                        <tr>
                            <th>Negocio</th>
                            <th>Cliente</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Distribuidora "Por Salud"</td>
                            <td>Juan Pérez</td>
                            <td><span class="badge-total">$7,890</span></td>
                        </tr>
                        <tr>
                            <td>Verdulería "Mi casita"</td>
                            <td>Karla Juárez</td>
                            <td><span class="badge-total">$5,488</span></td>
                        </tr>
                        <tr>
                            <td>Huerto Dorado</td>
                            <td>Kenia Flores</td>
                            <td><span class="badge-total">$5,400</span></td>
                        </tr>
                        <tr>
                            <td>Frutas "El Edén"</td>
                            <td>Luis Martínez</td>
                            <td><span class="badge-total">$4,920</span></td>
                        </tr>
                    </tbody>
                </table>
                <div class="view-more">
                    <!--<a href="#">Ver todos <i class="fas fa-arrow-right"></i></a>-->
                </div>
            </div>

            <!--  FACTURACIÓN Y COBRANZA -->
            <div class="tabla">
                <div class="tablatitle">
                   <!-- <i class="fas fa-file-invoice"></i>-->
                    <h3>Facturación y Cobranza</h3>
                </div>
                <table class="tablaestilo">
                    <thead>
                        <tr>
                            <th>Factura</th>
                            <th>Cliente</th>
                            <th>Estado</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>F-0012</td>
                            <td>Por Salud</td>
                            <td><span style="background:#f16b1a; color:white; padding:3px 10px; border-radius:40px; font-size:12px;">Pendiente</span></td>
                            <td>$2,500</td>
                        </tr>
                        <tr>
                            <td>F-0015</td>
                            <td>Mi casita</td>
                            <td><span style="background:#2d5a27; color:white; padding:3px 10px; border-radius:40px; font-size:12px;">Pagado</span></td>
                            <td>$1,850</td>
                        </tr>
                        <tr>
                            <td>F-0018</td>
                            <td>Huerto Dorado</td>
                            <td><span style="background:#f16b1a; color:white; padding:3px 10px; border-radius:40px; font-size:12px;">Pendiente</span></td>
                            <td>$3,200</td>
                        </tr>
                        <tr>
                            <td>F-0020</td>
                            <td>El Edén</td>
                            <td><span style="background:#f44336; color:white; padding:3px 10px; border-radius:40px; font-size:12px;">Vencido</span></td>
                            <td>$950</td>
                        </tr>
                    </tbody>
                </table>
                <div class="view-more">
                   <!-- <a href="#">Ver facturas <i class="fas fa-arrow-right"></i></a>-->
                </div>
            </div>

            <!-- INVENTARIO  -->
            <div class="tabla">
                <div class="tablatitle">
                    <!--<i class="fas fa-boxes"></i>-->
                    <h3>Inventario y más vendidos</h3>
                </div>
                
                <!-- Productos más vendidos -->
                <h4 style="color:#2d5a27; margin:10px 0 15px; font-size:16px;"><i class="fas fa-chart-line" style="margin-right:8px;"></i>Más vendidos</h4>
                <table class="tablaestilo" style="margin-bottom:25px;">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Ventas</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Lechuga</td>
                            <td>350 und</td>
                            <td><span class="product-stock">25</span></td>
                        </tr>
                        <tr>
                            <td>Aguacate</td>
                            <td>280 und</td>
                            <td><span class="product-stock">42</span></td>
                        </tr>
                        <tr>
                            <td>Jitomate</td>
                            <td>210 und</td>
                            <td><span class="product-stock">18</span></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Stock bajo -->
                <h4 style="color:#d32f2f; margin:20px 0 15px; font-size:16px;"><i class="fas fa-exclamation-triangle" style="margin-right:8px;"></i>Stock bajo</h4>
                <ul class="productolista">
                    <li class="item">
                        <span class="productnombre"><i class="fas fa-leaf"></i> Cilantro</span>
                        <span class="product-stock" style="background:#ffcdd2; color:#b71c1c;">3 und</span>
                    </li>
                    <li class="item">
                        <span class="productnombre"><i class="fas fa-leaf"></i> Limón</span>
                        <span class="product-stock" style="background:#ffcdd2; color:#b71c1c;">5 und</span>
                    </li>
                    <li class="item">
                        <span class="productnombre"><i class="fas fa-leaf"></i> Espinaca</span>
                        <span class="product-stock" style="background:#ffcdd2; color:#b71c1c;">2 und</span>
                    </li>
                </ul>
                
                <div class="view-more">
                   <!-- <a href="#">Gestionar inventario <i class="fas fa-arrow-right"></i></a>-->
                </div>
            </div>
        </div>
    </div>
</body>
</html>