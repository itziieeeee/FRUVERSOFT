<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fruversoft - Seleccionar Rol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --verde-fruver: #2e7d32;
            --verde-claro: #e9f5ec;
            --fondo-gris: #f0f4f8;
        }

        body {
            background-color: var(--fondo-gris);
            font-family: 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        /* Contenedor Principal Blanco (Igual a tu imagen) */
        .main-panel {
            background: white;
            border-radius: 40px; /* Bordes super redondeados */
            padding: 50px;
            width: 90%;
            max-width: 900px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            position: relative;
        }

        .header-section {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
        }

        .header-section h1 {
            font-weight: 800;
            color: #333;
            margin: 0;
            font-size: 2.2rem;
        }

        /* Tarjetas de Rol */
        .role-card {
            background: #ffffff;
            border-radius: 30px;
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
        }

        .role-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            border-color: var(--verde-fruver);
        }

        /* Iconos con el estilo de tus botones */
        .icon-box {
            font-size: 5rem;
            margin-bottom: 20px;
        }

        .admin-color { color: #2e7d32; }
        .vendedor-color { color: #ff9800; } /* Naranja como tu botón de Precios */

        /* Etiquetas tipo "Pill" (Igual a tu imagen) */
        .role-pill {
            background-color: var(--verde-claro);
            color: var(--verde-fruver);
            padding: 8px 30px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.1rem;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        }

        .vendedor-pill {
            background-color: #fff3e0;
            color: #ef6c00;
        }

        .cerrar-sesion {
            position: absolute;
            top: 30px;
            right: 40px;
            color: #888;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cerrar-sesion:hover { color: #d32f2f; }
    </style>
</head>
<body>

    <div class="main-panel">
        <a href="<?= base_url('salir') ?>" class="cerrar-sesion">
            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </a>

        <div class="header-section">
           <img src="<?= base_url('IMG/LOGO1.png') ?>" alt="Logo" width="90" class="me-3">
            <h1>BIENVENIDO, SELECCIONA TU ROL</h1>
        </div>

        <div class="row g-4 justify-content-center">
            
            <div class="col-md-5">
                <a href="<?= base_url('menusolo') ?>" class="role-card shadow-sm">
                    <div class="icon-box admin-color">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="role-pill">Administrador</div>
                </a>
            </div>

            <div class="col-md-5">
                <a href="<?= base_url('vendedor') ?>" class="role-card shadow-sm">
                    <div class="icon-box vendedor-color">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <div class="role-pill vendedor-pill">Vendedor</div>
                </a>
            </div>

        </div>
    </div>

</body>
</html>