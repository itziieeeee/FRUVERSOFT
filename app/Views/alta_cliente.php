<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/usuario_estilo.css') ?>">
    <title>FRUVER - Registro de Cliente</title>
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
<body style="background: #eef3e9;"> <div style="max-width: 800px; margin: 30px auto; font-family: sans-serif;">
    <div style="background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden;">
        
        <div style="background: #1d4a27; color: white; padding: 20px; display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-user-plus" style="font-size: 1.5rem;"></i>
            <h2 style="margin: 0; font-size: 1.4rem;">Registro de Nuevo Cliente</h2>
        </div>

      <form action="<?= base_url('alta_clientes/guardar') ?>" method="post">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                
                <div style="grid-column: span 2;">
                    <label style="display: block; color: #4d6b53; font-weight: 600; margin-bottom: 8px;">Nombre(s)</label>
                    <input type="text" name="nombre" required style="width: 100%; padding: 12px; border: 1px solid #cde0ca; border-radius: 10px; outline: none;">
                </div>

                <div>
                    <label style="display: block; color: #4d6b53; font-weight: 600; margin-bottom: 8px;">Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" required style="width: 100%; padding: 12px; border: 1px solid #cde0ca; border-radius: 10px;">
                </div>
                <div>
                    <label style="display: block; color: #4d6b53; font-weight: 600; margin-bottom: 8px;">Apellido Materno</label>
                    <input type="text" name="apellido_materno" required style="width: 100%; padding: 12px; border: 1px solid #cde0ca; border-radius: 10px;">
                </div>

                <div>
                    <label style="display: block; color: #4d6b53; font-weight: 600; margin-bottom: 8px;">RFC</label>
                    <input type="text" name="rfc" maxlength="13" placeholder="ABCD123456XYZ" style="width: 100%; padding: 12px; border: 1px solid #cde0ca; border-radius: 10px; text-transform: uppercase;">
                </div>

                <div>
                    <label style="display: block; color: #4d6b53; font-weight: 600; margin-bottom: 8px;">Tipo de Cliente</label>
                    <select name="tipo_cliente" style="width: 100%; padding: 12px; border: 1px solid #cde0ca; border-radius: 10px; background: white;">
                        <option value="Mayoreo">Mayoreo (Crédito)</option>
                        <option value="Menudeo">Menudeo (Contado)</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end;">
                <a href="<?= base_url('pantalla_clientes') ?>" style="text-decoration: none; color: #666; padding: 12px 25px; font-weight: 600;">Cancelar</a>
                <button type="submit" style="background: #f16b1a; color: white; border: none; padding: 12px 35px; border-radius: 30px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 10px rgba(241,107,26,0.3);">
                    Registrar Cliente
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>