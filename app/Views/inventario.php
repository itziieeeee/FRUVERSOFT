<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>FRUVER - Inventario | Gestión de Entradas y Mermas</title>
    
    <!-- Font Awesome 6 (Iconos) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 4.6 + tema suave -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Select2 para búsqueda elegante -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Fuente Google: Inter (moderna y legible) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    
    <style>
        /* ========== ROOT: PALETA FRESCA ========== */
        :root {
            --verde-profundo: #0f3b1f;
            --verde-fruta: #2b7a3e;
            --verde-lima: #4caf50;
            --verde-menta: #e6f4ea;
            --naranja-fresh: #f97316;
            --naranja-hover: #ea580c;
            --blanco-suave: #fefefe;
            --gris-claro: #f1f5f9;
            --gris-borde: #e2e8f0;
            --texto-oscuro: #1e293b;
            --texto-suave: #475569;
            --sombra-suave: 0 12px 30px rgba(0, 0, 0, 0.08);
            --sombra-hover: 0 20px 35px -12px rgba(0, 0, 0, 0.15);
            --transition: all 0.25s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: linear-gradient(145deg, #f8fafc 0%, #eef2f0 100%);
            overflow-y: auto !important;
            min-height: 100vh;
            scroll-behavior: smooth;
        }

        .fondo {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: radial-gradient(circle at 10% 20%, rgba(76, 175, 80, 0.03) 0%, rgba(249, 115, 22, 0.02) 100%);
        }

        /* ========== HEADER MODERNO ========== */
        header {
            background: var(--blanco-suave);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid var(--gris-borde);
            position: sticky;
            top: 0;
            z-index: 1030;
            backdrop-filter: blur(2px);
        }

        .barra-superior {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.7rem 2.5rem;
            background: linear-gradient(105deg, var(--verde-profundo) 0%, #1f5430 100%);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .logo-area img {
            height: 70px;
            width: auto;
            filter: drop-shadow(0 2px 6px rgba(0,0,0,0.1));
            transition: var(--transition);
        }
        .logo-area img:hover { transform: scale(1.02); }

        .buscador {
            flex: 1;
            max-width: 480px;
            display: flex;
            background: white;
            border-radius: 60px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: var(--transition);
        }
        .buscador input {
            flex: 1;
            padding: 0.75rem 1.4rem;
            border: none;
            font-size: 0.9rem;
            outline: none;
            background: white;
        }
        .buscador button {
            background: var(--naranja-fresh);
            border: none;
            padding: 0 1.4rem;
            color: white;
            cursor: pointer;
            transition: var(--transition);
        }
        .buscador button:hover { background: var(--naranja-hover); }
        .buscador input:focus { background: #fff9f0; }

        .user-actions {
            display: flex;
            gap: 0.8rem;
        }
        .btn-user {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(4px);
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            color: white;
            font-weight: 500;
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-user:hover {
            background: var(--naranja-fresh);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }

       /* ===== NAVEGACIÓN CENTRADA ===== */
        .menu-navegacion {
            background: white;
            padding: 0.5rem 1.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 1px solid var(--gray-border);
            gap: 0.5rem;
            flex-wrap: wrap;
            box-shadow: var(--shadow-sm);
        }

        .nav-link {
            padding: 0.9rem 1.2rem;
            color: var(--text-dark);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
            border-radius: 40px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            color: var(--primary-orange);
            background: var(--light-green);
        }

        .nav-link.activo {
            color: var(--primary-orange);
            background: rgba(241, 107, 26, 0.08);
            border-bottom: 3px solid var(--primary-orange);
        }
         /* ===== BOTONES ===== */
        .btn-primary {
            background: linear-gradient(105deg, var(--primary-orange), #e05a0c);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(241,107,26,0.3);
            filter: brightness(1.02);
        }

        .btn-guardar-pedido {
            margin-top: 1.5rem;
            text-align: right;
        }

        .btn-danger-icon {
            background: #fee2e2;
            border: none;
            color: #dc2626;
            padding: 0.5rem 0.8rem;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-danger-icon:hover {
            background: #fecaca;
            transform: scale(1.05);
        } 
        :root {
            --primary-green: #1d4a27;
            --primary-orange: #f16b1a;
            --light-green: #e8f3e6;
            --dark-green: #0f3317;
            --gray-light: #f8f9fa;
            --gray-border: #e0e0e0;
            --text-dark: #333;
            --text-light: #666;
            --white: #ffffff;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --radius-md: 12px;
            --radius-sm: 8px;
        }

        /* Tarjetas de opciones (Entrada, Merma, Existencias) */
        .opciones {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            margin: 2rem 2rem 1rem 2rem;
            padding: 1rem 0.5rem;
        }
        .botonesopciones {
            background: white;
            border-radius: 2rem;
            padding: 1.2rem 1.8rem;
            text-align: center;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 6px 14px rgba(0,0,0,0.03);
            border: 1px solid var(--gris-borde);
            min-width: 170px;
            backdrop-filter: blur(2px);
        }
        .botonesopciones img {
            width: 90px !important;
            height: 90px !important;
            object-fit: contain;
            margin-bottom: 0.75rem;
            transition: transform 0.2s;
        }
        .botonesopciones span {
            display: block;
            font-weight: 700;
            color: var(--verde-profundo);
            font-size: 1.1rem;
            letter-spacing: -0.2px;
        }
        .botonesopciones:hover {
            transform: translateY(-8px);
            box-shadow: var(--sombra-hover);
            border-color: var(--naranja-fresh);
        }
        .botonesopciones:hover img { transform: scale(1.02); }

        /* Tabla estilo inventario fresco */
        .container-mordern {
            background: white;
            border-radius: 1.8rem;
            margin: 0.5rem 2rem 2rem 2rem;
            padding: 1.2rem 1.5rem 1.8rem 1.5rem;
            box-shadow: var(--sombra-suave);
            border: 1px solid rgba(0,0,0,0.02);
        }
        .section-title {
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--verde-profundo);
            border-left: 5px solid var(--naranja-fresh);
            padding-left: 1rem;
            margin-bottom: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 1.2rem;
            overflow: hidden;
        }
        .table-custom thead tr {
            background: var(--verde-menta);
        }
        .table-custom th {
            padding: 1rem 1.2rem;
            font-weight: 700;
            color: #1f5430;
            border-bottom: 2px solid var(--verde-lima);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .table-custom td {
            padding: 0.9rem 1.2rem;
            border-bottom: 1px solid var(--gris-borde);
            color: var(--texto-oscuro);
            font-weight: 500;
        }
        .table-custom tbody tr:hover {
            background: #fefaf5;
            transition: 0.1s;
        }
        .badge-stock {
            background: #e9f7eb;
            color: #2e7d32;
            font-weight: 600;
            padding: 0.3rem 0.8rem;
            border-radius: 40px;
            font-size: 0.85rem;
        }

        /* MODAL PERSONALIZADO - entrada con estilo premium */
        .modal-overlay-custom {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 59, 31, 0.8);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2100;
            visibility: hidden;
            opacity: 0;
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        .modal-overlay-custom.active {
            visibility: visible !important;
            opacity: 1 !important;
        }
        .modal-container-custom {
            background: white;
            border-radius: 2rem;
            width: 90%;
            max-width: 520px;
            box-shadow: 0 35px 50px rgba(0,0,0,0.3);
            overflow: hidden;
            transform: scale(0.97);
            transition: transform 0.2s;
        }
        .modal-overlay-custom.active .modal-container-custom {
            transform: scale(1);
        }
        .modal-header-custom {
            background: linear-gradient(95deg, var(--verde-profundo), var(--verde-fruta));
            padding: 1.2rem 1.5rem;
            color: white;
        }
        /* Select2 z-index fix */
        .select2-container--open {
            z-index: 9999 !important;
        }
        .select2-container {
            width: 100% !important;
        }

        /* Alertas más lindas */
        .alert-custom {
            margin: 0.5rem 2rem 0 2rem;
            border-radius: 1rem;
            border-left: 6px solid;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .barra-superior { flex-direction: column; align-items: stretch; padding: 1rem; }
            .buscador { max-width: 100%; }
            .menu-navegacion { padding: 0.5rem; gap: 0; }
            .nav-link { padding: 0.7rem 1rem; font-size: 0.8rem; }
            .opciones { gap: 1rem; margin: 1rem; }
            .botonesopciones { min-width: 130px; padding: 1rem; }
            .botonesopciones img { width: 65px !important; height: 65px !important; }
            .container-mordern { margin: 0.8rem; padding: 1rem; }
            .table-custom th, .table-custom td { padding: 0.7rem; font-size: 0.85rem; }
        }
    </style>
</head>
<body>
<div class="fondo">
    <header>
        <div class="barra-superior">
            <div class="logo-area">
                <img src="<?= base_url('img/LOGO1.png') ?>" alt="Fruver Logo">
            </div>
            <div class="buscador">
                <input type="text" id="buscadorTabla" placeholder="Buscar producto en inventario...">
                <button type="button"><i class="fas fa-search"></i></button>
            </div>
            <div class="user-actions">
                <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> Admin</a>
                <a href="menusolo" class="btn-user"><i class="fas fa-arrow-right-from-bracket"></i> Regresar</a>
            </div>
        </div>
        <nav class="menu-navegacion">
            <a href="pantalla_ventas" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
            <a href="pantalla_pedidos" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
            <a href="inventario" class="nav-link activo"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="pantalla_clientes" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
            <a href="pantalla_repartidores" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
            <a href="pantalla_productos" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
        </nav>
    </header>

    <!-- Mensajes Flash con estilo -->
    <?php if (session()->getFlashdata('mensaje')): ?>
        <div class="alert alert-success alert-custom shadow-sm d-flex align-items-center justify-content-between">
            <span><i class="fas fa-check-circle me-2"></i> <?= session()->getFlashdata('mensaje') ?></span>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-custom shadow-sm">
            <i class="fas fa-exclamation-triangle"></i> <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- TARJETAS DE ACCIONES PRINCIPALES -->
<div class="opciones">
    <a href="javascript:void(0);" class="botonesopciones" id="btnAbrirEntrada">
        <img src="<?= base_url('img/Entrada.jpeg') ?>" alt="Entrada">
        <span><i class="fas fa-arrow-down me-1"></i> Entrada</span>
    </a>

    <a href="<?= base_url('mermas') ?>" class="botonesopciones">
        <img src="<?= base_url('img/Merma.jpeg') ?>" alt="Merma">
        <span><i class="fas fa-trash-alt"></i> Merma</span>
    </a>

    <a href="<?= base_url('existencias') ?>" class="botonesopciones">
        <img src="<?= base_url('img/existencias.png') ?>" alt="Existencias">
        <span><i class="fas fa-clipboard-list"></i> Existencias</span>
    </a>
</div>

    <!-- TABLA DE STOCK ACTUAL-->
    <div class="container-mordern">
        <div class="section-title">
            <i class="fas fa-apple-alt fa-lg" style="color: var(--naranja-fresh);"></i> 
            <span>Resumen de Inventario Actual</span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table-custom" id="tablaStock">
                <thead>
                    <tr>
                        <th><i class="fas fa-carrot"></i> Producto</th>
                        <th><i class="fas fa-cubes"></i> Total disponible</th>
                        <th><i class="fas fa-skull-crossbones"></i> Merma acumulada</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    <?php if (!empty($existencias)): ?>
                        <?php foreach ($existencias as $e): ?>
                            <tr class="fila-producto">
                                <td style="font-weight: 600;"><?= esc($e['nombre']) ?></td>
                                <td><span class="badge-stock"><i class="fas fa-box-open"></i> <?= number_format($e['e_total'], 2) ?></span></td>
                                <td><span class="text-danger"><?= number_format($e['e_merma'], 2) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center py-4 text-muted"><i class="fas fa-database"></i> No hay datos en el inventario</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL ENTRADa -->
<div id="productModal" class="modal-overlay-custom">
    <div class="modal-container-custom">
        <div class="modal-header-custom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-truck-loading me-2"></i> Registrar Entrada de Producto</h5>
            <button type="button" id="closeModalBtn" class="close text-white" style="font-size: 1.8rem; opacity: 0.9;">&times;</button>
        </div>
        
        <div class="modal-body p-4">
            <form id="productForm" method="POST" action="<?= base_url('confirmar-entrada') ?>">
                
<div class="form-group mb-3">
    <label class="font-weight-bold"><i class="fas fa-apple-alt"></i> Producto</label>
    <select name="id_producto" id="selectEntrada" class="form-control" style="width: 100%;" required>
        <option value="">Escribe para buscar...</option>
        <?php foreach ($productos as $p): ?>
            <option value="<?= $p['id'] ?>"><?= esc($p['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Precio de compra</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                            <input type="number" step="0.01" min= "0.01" name="precio_compra" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Cantidad de compra</label>
                        <input type="number" step="1" min= "1" name="cantidad_compra" class="form-control" placeholder="Ej: 50" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Unidad de compra</label>
                        <select name="unidad_compra" class="form-control" required>
                            <option value="Caja">Caja</option>
                            <option value="Kilo">Kilo</option>
                            <option value="Domo">Domo</option>
                            <option value="Mazo">Mazo</option>
                            <option value="Arpilla">Arpilla</option>
                            <option value="Ramo">Ramo</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Precio sugerido</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                            <input type="number" step="0.01" min= "0.01" name="precio_sugerido" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold text-success">Unidad de venta</label>
                        <select name="unidad_venta" class="form-control" required>
                            <option value="Caja">Caja</option>
                            <option value="Kilo">Kilo</option>
                            <option value="Domo">Domo</option>
                            <option value="Mazo">Mazo</option>
                            <option value="Arpilla">Arpilla</option>
                            <option value="Ramo">Ramo</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold text-success">Cantidad de venta</label>
                        <input type="number" step="1" min= "1" name="cantidad_venta" class="form-control" placeholder="Ej: 100" required>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="font-weight-bold">Categoría</label>
                    <select name="categoria" class="form-control" required>
                        <option value="Frutas">Frutas</option>
                        <option value="Verduras">Verduras</option>
                        <option value="Abarrotes">Abarrotes</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success btn-block py-2 rounded-pill shadow" style="background: var(--verde-fruta); border: none; font-weight: bold;">
                    <i class="fas fa-save me-2"></i> Guardar Registro de Inventario
                </button>
            </form>
        </div>
    </div>
</div>


<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // ---------- FILTRO EN TIEMPO REAL PARA LA TABLA ----------
        $("#buscadorTabla").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#tablaBody .fila-producto").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // ---------- MODAL ENTRADA (custom) ----------
        const modalEntrada = $('#productModal');
        $('#btnAbrirEntrada').on('click', function(e) {
            e.preventDefault();
            modalEntrada.addClass('active');
            // Inicializar Select2 dentro del modal personalizado
            $('#selectEntrada').select2({
                placeholder: " Escribe el nombre del producto...",
                dropdownParent: modalEntrada,
                width: '100%'
            });
        });

        $('#closeModalBtn').on('click', function() {
            modalEntrada.removeClass('active');
            $('#selectEntrada').select2('destroy');
        });
        $(window).on('click', function(event) {
            if ($(event.target).is(modalEntrada)) {
                modalEntrada.removeClass('active');
                $('#selectEntrada').select2('destroy');
            }
        });

        // ---------- MODAL MERMA con select2 + bootstrap ----------
        $('#modalMerma').on('shown.bs.modal', function () {
            $('#selectMerma').select2({
                placeholder: "🔎 Buscar producto por nombre...",
                dropdownParent: $('#modalMerma'),
                width: '100%'
            });
        });
        $('#modalMerma').on('hidden.bs.modal', function () {
            $('#selectMerma').select2('destroy');
        });
        
        // Validación extra: evitar envío de cantidades negativas
        $('form').on('submit', function(e) {
            let cantidadInput = $(this).find('input[name="cantidad"]');
            if(cantidadInput.length && parseFloat(cantidadInput.val()) <= 0) {
                alert(' La cantidad debe ser mayor a cero.');
                e.preventDefault();
                return false;
            }
        });
    });
</script>
</body>
</html>