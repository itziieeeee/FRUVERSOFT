<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>FRUVER - Inventario | Gestión de Entradas y Mermas</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/inventarioestilo.css') ?>">
    
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

    <!-- TARJETAS DE ACCIONES PRINCIPALES - TODOS BOTONES SIN SUBRAYADO -->
    <div class="opciones">
        <button class="btn-opcion-card" id="btnAbrirEntrada" style="border: none; background: white; cursor: pointer;">
            <img src="<?= base_url('img/Entrada.jpeg') ?>" alt="Entrada">
            <span><i class="fas fa-arrow-down me-1"></i> Entrada</span>
        </button>

        <button class="btn-opcion-card" id="btnAbrirMerma" style="border: none; background: white; cursor: pointer;">
            <img src="<?= base_url('img/Merma.jpeg') ?>" alt="Merma">
            <span><i class="fas fa-trash-alt"></i> Merma</span>
        </button>

        <button class="btn-opcion-card" id="btnAbrirExistencias" style="border: none; background: white; cursor: pointer;">
            <img src="<?= base_url('img/existencias.png') ?>" alt="Existencias">
            <span><i class="fas fa-clipboard-list"></i> Existencias</span>
        </button>
    </div>

    <!-- TABLA DE STOCK ACTUAL-->
    <div class="container-mordern">
        <div class="section-title">
            <span>Resumen de Inventario Actual</span>
        </div>
        <div style="overflow-x: auto;">
            <table class="table-custom" id="tablaStock">
                <thead>
                    <tr>
                        <th> Producto</th>
                        <th> Total disponible</th>
                        <th> Merma acumulada</th>
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

<!-- MODAL ENTRADA -->
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
                            <input type="number" step="0.01" min="0.01" name="precio_compra" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Cantidad de compra</label>
                        <input type="number" step="1" min="1" name="cantidad_compra" class="form-control" placeholder="Ej: 50" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Unidad de compra</label>
                        <select name="unidad_compra" class="form-control" required>
                            <option value="" disabled selected hidden>Selecciona unidad...</option>
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
                            <input type="number" step="0.01" min="0.01" name="precio_sugerido" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold text-success">Unidad de venta</label>
                        <select name="unidad_venta" class="form-control" required>
                            <option value="" disabled selected hidden>Selecciona unidad...</option>
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
                        <input type="number" step="1" min="1" name="cantidad_venta" class="form-control" placeholder="Ej: 100" required>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // FILTRO EN TIEMPO REAL PARA LA TABLA
        $("#buscadorTabla").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#tablaBody .fila-producto").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // MODAL ENTRADA
        const modalEntrada = $('#productModal');
        $('#btnAbrirEntrada').on('click', function(e) {
            e.preventDefault();
            modalEntrada.addClass('active');
            $('#selectEntrada').select2({
                placeholder: "Escribe el nombre del producto...",
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

        // BOTÓN MERMA - REDIRIGIR A LA VISTA DE MERMAS
        $('#btnAbrirMerma').on('click', function(e) {
            e.preventDefault();
            window.location.href = '<?= base_url('mermas') ?>';
        });

        // BOTÓN EXISTENCIAS - REDIRIGIR A LA VISTA DE EXISTENCIAS
        $('#btnAbrirExistencias').on('click', function(e) {
            e.preventDefault();
            window.location.href = '<?= base_url('existencias') ?>';
        });
        
        // Validación extra
        $('form').on('submit', function(e) {
            let cantidadInput = $(this).find('input[name="cantidad"]');
            if(cantidadInput.length && parseFloat(cantidadInput.val()) <= 0) {
                alert('La cantidad debe ser mayor a cero.');
                e.preventDefault();
                return false;
            }
        });
    });
</script>
</body>
</html>
