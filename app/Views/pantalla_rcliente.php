<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Gestión de Clientes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f0f2f5;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            background:#225531;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        /* Grid principal */
        .main-grid {
    display: flex;
    justify-content: center;
}
        

        /* Tarjetas */
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f2f5;
        }

        .card-header h2 {
            color: #333;
            font-size: 18px;
        }

        .badge {
            background: #f78b3d;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        /* Formulario */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

       

        /* Tipos de cliente */
        .client-types {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }

        .client-type-card {
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid transparent;
        }

       
        .client-type-card.cash {
            background-color: #f78b3d;
            color: white;
        }

        .client-type-card.credit {
            background-color: #235531;
            color: white;
        }

        .client-type-card h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .client-type-card p {
            font-size: 12px;
            opacity: 0.9;
        }

        /* Límite de crédito */
        .credit-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .credit-progress {
            margin-top: 10px;
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            width: 35%;
            border-radius: 5px;
        }

        .credit-stats {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #666;
        }

        .credit-amount {
            font-weight: bold;
            color: #333;
        }

        /* Tabla de historial */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px;
            background: #f8f9fa;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        /* Botones */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-primary {
            background:  #f78b3d;
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
            
            .client-types {
                grid-template-columns: 1fr;
            }
        }

        /* Sección de contacto */
        .contact-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
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


        /* Patrones de compra */
        .purchase-patterns {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .pattern-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #ddd;
        }

        .pattern-item:last-child {
            border-bottom: none;
        }

        .pattern-product {
            font-weight: 500;
            color: #333;
        }

        .pattern-frequency {
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header">
            <h1>Gestión de Cliente</h1>
            <p>Registrar cliente</p>
        </div>

        <!-- Grid principal -->
        <div class="main-grid">
            <!-- Formulario de alta -->
            
                <div class="menuarriba">
            <div class="logo">
                <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo" width="140">
            </div>
            <div class="opcionesdelabarra">
                <a href="#" class="boton"><i class="fas fa-user-shield"></i> Administrador</a>
                <a href="#" class="boton"><i class="fas fa-bell"></i> Alta cliente </a>
                <a href="#" class="boton"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
            </div>
            <div class="buscador">
                <input type="text" placeholder="Buscar...">
                <button><i class="fas fa-search"></i></button>
            </div>
        </div>

                <form id="clientForm" action="<?= base_url('guardar_cliente') ?>" method="POST">
                    <!-- Tipos de cliente -->
                    <div class="form-group">
                        <label>Tipo de Cliente *</label>
                        <div class="client-types">
                            <div class="client-type-card cash selected" onclick="selectClientType('cash')">
                                <h3>Contado / Menudeo</h3>
                                <p>Pago inmediato al momento de la compra/entrega</p>
                            </div>
                            <div class="client-type-card credit" onclick="selectClientType('credit')">
                                <h3>Crédito / Mayoreo</h3>
                                <p>Límite de crédito con días para pagar</p>
                            </div>
                        </div>
                    </div>

                    <!-- Información detallada -->
                    <div class="form-group">
                        <label>Nombre*</label>
                        <input type="text" placeholder="Ej: Juan Jose" required>
                        <label>Apellido Paterno*</label>
                        <input type="text" placeholder="Ej: Pérez" required>
                        
                    </div>

                    <div class="form-group">
                        <label>Dirección de entrega principal *</label>
                        <input type="text" placeholder="Calle, número, colonia, ciudad" required>
                    </div>

                    <div class="contact-info">
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="tel" placeholder="55 1234 5678">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" placeholder="cliente@email.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>RFC (para facturación)</label>
                        <input type="text" placeholder="XAXX010101000">
                    </div>

                    <!-- Límite de crédito (visible solo para crédito) -->
                    <div id="creditSection" style="display: block;">
                        <div class="credit-info">
                            <h3 style="margin-bottom: 10px; color: #333;">Límite de Crédito</h3>
                            <div class="form-group">
                                <label>Monto máximo de crédito</label>
                                <input type="number" placeholder="$0.00" value="50000">
                            </div>
                            <div class="form-group">
                                <label>Días de crédito</label>
                                <select>
                                    <option>7 días</option>
                                    <option selected>15 días</option>
                                    <option>30 días</option>
                                    <option>45 días</option>
                                    <option>60 días</option>
                                </select>
                            </div>
                            <!---ímite de Crédito: Definición del monto máximo de crédito y un contador que muestre el crédito disponible 
y el crédito utilizado. --><!--<div class="credit-progress">
                                <div class="credit-stats">
                                    <span>Crédito disponible: <span class="credit-amount">$32,500</span></span>
                                    <span>Crédito utilizado: <span class="credit-amount">$17,500</span></span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 35%;"></div>
                                </div>
                                <small style="color: #666;">35% utilizado de $50,000</small>
                            </div>
                        </div>
                    </div>-->

                    <button type="submit" class="btn btn-primary">
                       Registrar Cliente</button>
                </form>
            </div>
                            

           

</body>
</html>