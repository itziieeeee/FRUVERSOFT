<!DOCTYPE html>
<html lang="en">
<head>
    <!-- FORMULARIO PARA ALTA DE PRODUCTOS -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Nuevo Producto</title>
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

        /* --- CABECERO SUPERIOR (COMPACTO) --- */
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

        /* Buscador compacto */
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

        /* Acciones de usuario */
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

        /* --- MENÚ DE NAVEGACIÓN (6 BOTONES) --- */
        .menu-navegacion {
            background-color: #f5faf4;
            padding: 12px 24px;
            border-bottom: 1px solid #cde0ca;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            flex-shrink: 0;
        }

        .nav-links {
            display: flex;
            align-items: center;
            justify-content: space-around;
            gap: 12px;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }

        .nav-link {
            background: white;
            border-radius: 40px;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: 600;
            color: #1e3a2f;
            font-size: 0.95rem;
            border: 1.5px solid transparent;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(0,40,0,0.05);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link i {
            color: #f16b1a;
            font-size: 1rem;
        }

        .nav-link:hover {
            transform: translateY(-2px);
            border-color: #f16b1a;
            box-shadow: 0 6px 12px rgba(0,0,0,0.08);
            color: #1d4a27;
        }

        /* Estilo para el link activo */
        .nav-link.activo {
            background: #1d4a27;
            color: white;
            border-color: #1d4a27;
        }
        .nav-link.activo i {
            color: white;
        }

        /* Título de la sección */
        .titulo-seccion {
            padding: 16px 24px 8px 24px;
            font-size: 1.8rem;
            font-weight: 600;
            color: #1d4a27;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .titulo-seccion i {
            color: #f16b1a;
            background: #fff1e0;
            padding: 8px;
            border-radius: 16px;
            font-size: 1.6rem;
        }

        /* Contenedor principal del formulario */
        .contenedor-form {
            padding: 20px 24px;
            height: calc(100vh - 140px);
            overflow-y: auto;
        }

        /* Tarjeta del formulario */
        .card-form {
            background: white;
            border-radius: 28px;
            padding: 28px 32px;
            box-shadow: 0 8px 22px rgba(40, 70, 40, 0.15);
            border: 1px solid rgba(120, 160, 120, 0.2);
            max-width: 800px;
            margin: 0 auto;
        }

        .cabezacard {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
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

        /* Grid del formulario */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px 32px;
        }

        .campo-full {
            grid-column: 1 / -1;
        }

        .campo {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .campo label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #2d6e3b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .campo label i {
            color: #f16b1a;
            font-size: 1rem;
        }

        .campo input,
        .campo select,
        .campo textarea {
            padding: 12px 16px;
            border: 2px solid #e2eedf;
            border-radius: 20px;
            font-size: 0.95rem;
            font-family: inherit;
            background: #fafdf9;
            transition: all 0.2s ease;
        }

        .campo input:focus,
        .campo select:focus,
        .campo textarea:focus {
            outline: none;
            border-color: #f16b1a;
            background: white;
            box-shadow: 0 0 0 3px rgba(241, 107, 26, 0.1);
        }

        .campo textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Unidades de compra/venta (estilo similar a los botones de tipo cliente) */
        .unidades-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .unidad-btn {
            background: #eef5ec;
            border: 1.5px solid #bad2b4;
            border-radius: 30px;
            padding: 10px 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #1a4a27;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .unidad-btn i {
            color: #f16b1a;
            font-size: 0.9rem;
        }

        .unidad-btn.activo {
            background: #1d4a27;
            border-color: #1d4a27;
            color: white;
        }
        .unidad-btn.activo i {
            color: white;
        }

        /* Campo de contenido (con estilo de información) */
        .campo-contenido {
            background: #f5faf4;
            border-radius: 20px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .campo-contenido input {
            flex: 1;
            min-width: 150px;
            border: 2px solid #bad2b4;
            border-radius: 30px;
            padding: 10px 16px;
            font-size: 0.95rem;
        }

        .badge-contenido {
            background: #fff1e0;
            color: #b84500;
            padding: 8px 16px;
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .badge-contenido i {
            color: #f16b1a;
        }

        /* Acciones del formulario */
        .form-actions {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            margin-top: 16px;
            border-top: 2px solid #e2eedf;
            padding-top: 24px;
        }

        .btn-primario,
        .btn-secundario {
            padding: 12px 28px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-primario {
            background: #1d4a27;
            color: white;
            box-shadow: 0 4px 10px rgba(29, 74, 39, 0.3);
        }

        .btn-primario:hover {
            background: #14632a;
            transform: translateY(-2px);
        }

        .btn-secundario {
            background: white;
            color: #1d4a27;
            border: 2px solid #cde0ca;
        }

        .btn-secundario:hover {
            background: #f5faf4;
            border-color: #f16b1a;
        }

        /* Scrollbar personalizada */
        .contenedor-form::-webkit-scrollbar {
            width: 6px;
        }
        .contenedor-form::-webkit-scrollbar-thumb {
            background: #b8d4b0;
            border-radius: 10px;
        }

        @media (max-width: 700px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .card-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Barra superior con logo, buscador y acciones de usuario -->
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo" width="140">
        </div>

        <div class="buscador">
            <input type="text" placeholder="Buscar producto, código...">
            <button><i class="fas fa-search"></i></button>
        </div>

        <div class="user-actions">
            <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> <span class="hide-mobile">Admin</span></a>
            <a href="#" class="btn-user"><i class="fas fa-bell"></i> <span class="hide-mobile">Notificaciones</span></a>
            <a href="#" class="btn-user"><i class="fas fa-sign-out-alt"></i> <span class="hide-mobile">Salir</span></a>
        </div>
    </div>

    <!-- Menú de navegación principal (6 botones) -->
    <nav class="menu-navegacion">
        <div class="nav-links">
            <a href="#" class="nav-link"><i class="fas fa-tag"></i> Precios</a>
            <a href="#" class="nav-link"><i class="fas fa-credit-card"></i> Pagos</a>
            <a href="#" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
            <a href="#" class="nav-link activo"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="#" class="nav-link"><i class="fas fa-file-invoice"></i> Facturación</a>
            <a href="#" class="nav-link"><i class="fas fa-chart-bar"></i> Reportes</a>
        </div>
    </nav>

    <!-- Título de la sección -->
    <div class="titulo-seccion">
        <i class="fas fa-cube"></i> Nuevo Producto
    </div>

    <!-- Contenedor del formulario -->
    <div class="contenedor-form">
        <div class="card-form">
            <div class="cabezacard">
                <i class="fas fa-box-open"></i>
                <h2>Alta de producto</h2>
            </div>

            <form action="<?= base_url('producto/guardar') ?>" method="POST">
                <div class="form-grid">
                    <!-- Campo: Nombre -->
                    <div class="campo campo-full">
                        <label for="nombre"><i class="fas fa-tag"></i> Nombre del producto *</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej. Tomate Saladet, Fresa, Sandía..." required>
                    </div>

                    <!-- Campo: Descripción -->
                    <div class="campo campo-full">
                        <label for="descripcion"><i class="fas fa-align-left"></i> Descripción</label>
                        <textarea id="descripcion" name="descripcion" placeholder="Características adicionales, variedad, presentación..."></textarea>
                    </div>

                    <!-- Unidad de compra -->
                    <div class="campo">
                        <label><i class="fas fa-truck-loading"></i> Unidad de compra</label>
                        <div class="unidades-grid">
                            <button type="button" class="unidad-btn activo" onclick="seleccionarUnidad(this, 'compra')">
                                <i class="fas fa-box"></i> Caja
                            </button>
                            <button type="button" class="unidad-btn" onclick="seleccionarUnidad(this, 'compra')">
                                <i class="fas fa-weight"></i> Kilo
                            </button>
                            <button type="button" class="unidad-btn" onclick="seleccionarUnidad(this, 'compra')">
                                <i class="fas fa-cubes"></i> Tonelada
                            </button>
                        </div>
                        <input type="hidden" name="unidad_compra" id="unidad_compra" value="caja">
                    </div>

                    <!-- Unidad de venta -->
                    <div class="campo">
                        <label><i class="fas fa-shopping-cart"></i> Unidad de venta</label>
                        <div class="unidades-grid">
                            <button type="button" class="unidad-btn" onclick="seleccionarUnidad(this, 'venta')">
                                <i class="fas fa-box"></i> Caja
                            </button>
                            <button type="button" class="unidad-btn activo" onclick="seleccionarUnidad(this, 'venta')">
                                <i class="fas fa-weight"></i> Kilo
                            </button>
                            <button type="button" class="unidad-btn" onclick="seleccionarUnidad(this, 'venta')">
                                <i class="fas fa-balance-scale"></i> Pieza
                            </button>
                            <button type="button" class="unidad-btn" onclick="seleccionarUnidad(this, 'venta')">
                                <i class="fas fa-balance-scale"></i> Charola
                            </button>
                        </div>
                        <input type="hidden" name="unidad_venta" id="unidad_venta" value="kilo">
                    </div>
                    <div class="campo">
<label>Stock inicial</label>
<input type="number" name="stock" step="0.01">
</div>
<div class="campo">
<label>Precio compra</label>
<input type="number" name="precio_compra" step="0.01">
</div>
<div class="campo">
<label>Precio venta</label>
<input type="number" name="precio_venta" step="0.01">
</div>

                    <!-- Contenido (MAS O MENOS QUE LLEVE ESTE DISEÑO) 
                    <div class="campo campo-full">
                        <label><i class="fas fa-weight-hanging"></i> Contenido por unidad</label>
                        <div class="campo-contenido">
                            <input type="number" id="contenido" name="contenido" placeholder="Cantidad" step="0.001" min="0" value="1.000">
                            <span class="badge-contenido">
                                <i class="fas fa-arrows-alt-v"></i> Más o menos
                            </span>
                        </div>
                        <small style="color: #4d6b53; margin-top: 4px; display: block;">
                            <i class="fas fa-info-circle"></i> Ej. 20 kg por caja, 500 g por pieza, etc.
                        </small>
                    </div> -->

                    <!-- Campos adicionales que pueden ser útiles (siguiendo el diseño de la pantalla de clientes) -->
                    <div class="campo">
                        <label for="categoria"><i class="fas fa-folder"></i> Categoría</label>
                        <select id="categoria" name="categoria">
                            <option value="">Seleccionar...</option>
                            <option value="frutas">Frutas</option>
                            <option value="verduras">Verduras</option>
                           <!--  <option value="hortalizas">Hortalizas</option>
                            <option value="legumbres">Legumbres</option>-->
                        </select>
                    </div>
 <!-- 
                    <div class="campo">
                        <label for="impuesto"><i class="fas fa-percent"></i> Impuesto</label>
                        <select id="impuesto" name="impuesto">
                            <option value="0">Sin impuesto</option>
                            <option value="16">IVA 16%</option>
                            <option value="8">IVA 8%</option>
                            <option value="0">Exento</option>
                        </select>
                    </div>
-->
                    <!-- Acciones del formulario -->
                    <div class="form-actions">
                        <button type="reset" class="btn-secundario">
                            <i class="fas fa-undo-alt"></i> Limpiar
                        </button>
                        <button type="submit" class="btn-primario">
                            <i class="fas fa-save"></i> Guardar producto
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Script para manejar la selección de unidades -->
    <script>
        function seleccionarUnidad(btn, tipo) {
            // Encontrar el contenedor de unidades
            const contenedor = btn.parentElement;
            const botones = contenedor.querySelectorAll('.unidad-btn');
            
            // Quitar clase activo de todos
            botones.forEach(b => b.classList.remove('activo'));
            
            // Activar el botón clickeado
            btn.classList.add('activo');
            
            // Actualizar el campo oculto correspondiente
            const textoUnidad = btn.textContent.trim().replace(/[^a-zA-Záéíóúñ ]/g, '');
            const unidad = textoUnidad.toLowerCase().trim();
            
            if (tipo === 'compra') {
                document.getElementById('unidad_compra').value = unidad;
            } else if (tipo === 'venta') {
                document.getElementById('unidad_venta').value = unidad;
            }
        }
    </script>
</body>
</html>