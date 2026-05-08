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

    <!-- Mensajes Flash c-->
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

<!-- MODAL ENTRADA DINÁMICO -->
<div id="productModal" class="modal-overlay-custom">
    <div class="modal-container-custom" style="max-width: 90%; width: 1000px;">
        <div class="modal-header-custom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-truck-loading me-2"></i> Registro de Entrada Múltiple</h5>
            <button type="button" id="closeModalBtn" class="close text-white" style="font-size: 1.8rem; opacity: 0.9;">&times;</button>
        </div>
        
        <div class="modal-body p-4">
            <!-- SECCIÓN DE CAPTURA RÁPIDA -->
            <div class="alert alert-light border d-flex align-items-end gap-3 mb-4 shadow-sm">
                <div class="flex-grow-1">
                    <label class="fw-bold mb-1"><i class="fas fa-search"></i> Buscar Producto (Captura Rápida)</label>
                    <select id="selectEntrada" class="form-control">
                        <option value="">Escribe el nombre del producto...</option>
                        <?php foreach ($productos as $p): ?>
                            <option value="<?= $p['id'] ?>" data-nombre="<?= esc($p['nombre']) ?>">
                                #<?= $p['id'] ?> - <?= esc($p['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" id="btnAgregarFila" class="btn btn-primary" style="height: 38px; background: #2c3e50; border: none;">
                    <i class="fas fa-plus"></i> Añadir
                </button>
            </div>

            <form id="productForm" method="POST" action="<?= base_url('confirmar-entrada') ?>">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover border">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th style="width: 25%;">Producto</th>
                                <th>P. Compra</th>
                                <th>Cant. Compra</th>
                                <th>Unidad (C/V)</th>
                                <th>P. Sugerido</th>
                                <th>Conversion</th>
                                <th>Venta Total</th>
                                <th>Categoría</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaEntradas">
                            <!-- Aquí se insertarán las filas dinámicamente -->
                        </tbody>
                    </table>
                </div>

                <div id="vacioMensaje" class="text-center py-4 text-muted">
                    <i class="fas fa-clipboard-list fa-3x mb-2"></i>
                    <p>No hay productos en la lista de entrada.</p>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <span class="badge badge-info p-2" id="contadorProductos">Productos en lista: 0</span>
                    <button type="submit" id="btnGuardarTodo" class="btn btn-success px-5 rounded-pill shadow" style="display:none; background: var(--verde-fruta); border: none; font-weight: bold;">
                        <i class="fas fa-save me-2"></i> Guardar Todo el Inventario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

    $(document).ready(function() {
    const modalEntrada = $('#productModal');
    const cuerpoTabla = $('#cuerpoTablaEntradas');
    const selectBusqueda = $('#selectEntrada');

    // Al abrir el modal, iniciamos Select2
    $('#btnAbrirEntrada').on('click', function(e) {
        e.preventDefault();
        modalEntrada.addClass('active');
        selectBusqueda.select2({
            dropdownParent: modalEntrada,
            width: '100%'
        });
    });

    // Agregar producto a la tabla
    $('#btnAgregarFila').on('click', function() {
        const id = selectBusqueda.val();
        const nombre = selectBusqueda.find(':selected').data('nombre');

        if (!id) return alert("Selecciona un producto primero.");

        const nuevaFila = `
        <tr class="fila-producto">
        <td>
            <input type="hidden" name="id_producto[]" value="${id}">
            <small class="fw-bold d-block text-truncate" style="max-width: 150px;">${nombre}</small>
        </td>
        <td><input type="number" step="0.01" min="0.01" name="precio_compra[]" class="form-control form-control-sm" required></td>
        <td><input type="number" step="0.1" min="0.1" name="cantidad_compra[]" class="form-control form-control-sm cant-compra" required></td>
        <td>
            <!-- UNIDAD DE COMPRA -->
            <select name="unidad_compra[]" class="form-control form-control-sm mb-1" required>
                <option value="" disabled selected hidden>Compra...</option>
                <option value="Caja">Caja</option>
                <option value="Kilo">Kilo</option>
                <option value="Domo">Domo</option>
                <option value="Mazo">Mazo</option>
                <option value="Arpilla">Arpilla</option>
                <option value="Ramo">Ramo</option>
            </select>
            
            <!-- UNIDAD DE VENTA -->
            <select name="unidad_venta[]" class="form-control form-control-sm" required>
                <option value="" disabled selected hidden>Venta...</option>
                <option value="Caja">Caja</option>
                <option value="Kilo">Kilo</option>
                <option value="Domo">Domo</option>
                <option value="Mazo">Mazo</option>
                <option value="Arpilla">Arpilla</option>
                <option value="Ramo">Ramo</option>
                <option value="Pieza">Pieza</option>
            </select>
        </td>
        <td><input type="number" step="0.01" min="0.01" name="precio_sugerido[]" class="form-control form-control-sm" required></td>
        <td><input type="number" step="0.1" min="0.1" name="valor_conversion[]" class="form-control form-control-sm valor-conv" required></td>
        <td><input type="number" name="cantidad_venta[]" class="form-control form-control-sm total-venta" readonly required></td>
        <td>
            <select name="categoria[]" class="form-control form-control-sm" required>
                <option value="Frutas">Frutas</option>
                <option value="Verduras">Verduras</option>
                <option value="Hierbas">Hierbas</option>
            </select>
        </td>
        <td><button type="button" class="btn btn-sm btn-danger btnEliminarFila"><i class="fas fa-trash"></i></button></td>
    </tr>`;

        cuerpoTabla.append(nuevaFila);
        actualizarInterfaz();
        selectBusqueda.val(null).trigger('change'); // Limpiar buscador
    });

    // Cálculos automáticos por fila
    cuerpoTabla.on('input', '.cant-compra, .valor-conv', function() {
        const fila = $(this).closest('tr');
        const cant = parseFloat(fila.find('.cant-compra').val()) || 0;
        const conv = parseFloat(fila.find('.valor-conv').val()) || 0;
        const total = (cant * conv).toFixed(2);
        fila.find('.total-venta').val(total > 0 ? total : "");
    });

    // Eliminar fila
    cuerpoTabla.on('click', '.btnEliminarFila', function() {
        $(this).closest('tr').remove();
        actualizarInterfaz();
    });

    function actualizarInterfaz() {
    // Ahora solo contamos las filas que están DENTRO del cuerpo de la tabla del modal
    const filas = $('#cuerpoTablaEntradas .fila-producto').length;
    
    $('#contadorProductos').text(`Productos en lista: ${filas}`);
    $('#vacioMensaje').toggle(filas === 0);
    $('#btnGuardarTodo').toggle(filas > 0);
}

    $('#closeModalBtn').on('click', () => modalEntrada.removeClass('active'));
    });


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



        //calculo automatico de entrada, osea la conversion
        
        document.addEventListener('input', function (event) {
        // Verificamos si lo que cambió fue el input de compra o el de conversión
        if (event.target.id === 'cantidad_compra' || event.target.id === 'valor_conversion') {
        
        const compra = document.getElementById('cantidad_compra');
        const conversion = document.getElementById('valor_conversion');
        const resultado = document.getElementById('total_venta');

        // Convertimos a números (si están vacíos, usamos 0)
        const v1 = parseFloat(compra.value) || 0;
        const v2 = parseFloat(conversion.value) || 0;

        // Realizamos la multiplicación
        const total = v1 * v2;

        // Si el resultado es mayor a 0, lo ponemos en el campo
        if (total > 0) {
            resultado.value = total.toFixed(2);
        } else {
            resultado.value = "";
        }
        }
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

