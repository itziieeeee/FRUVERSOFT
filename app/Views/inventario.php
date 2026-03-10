<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario - FruverSoft</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        /* Tus estilos originales */
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ background:#f0f4f8; font-family:'Segoe UI',Roboto,system-ui,sans-serif; }
        .fondo{ max-width:1700px; margin:20px auto; padding:0 25px; }
         /* === CABECERO SUPERIOR === */
        .barra-superior {
            background: #1d4a27;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            box-shadow: 0 4px 12px rgba(0,30,0,0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo-area img {
            height: 45px;
            width: auto;
            filter: brightness(1.1);
        }

        /* Buscador mejorado */
        .buscador {
            background: white;
            border-radius: 40px;
            padding: 3px 3px 3px 20px;
            display: flex;
            align-items: center;
            flex: 0 1 400px;
            transition: box-shadow 0.3s;
        }
        
        .buscador:focus-within {
            box-shadow: 0 0 0 3px rgba(241, 107, 26, 0.3);
        }

        .buscador input {
            border: none;
            padding: 10px 0;
            width: 100%;
            outline: none;
            font-size: 0.95rem;
        }

        .buscador button {
            background: #f16b1a;
            border: none;
            border-radius: 40px;
            width: 42px;
            height: 42px;
            color: white;
            cursor: pointer;
            transition: background 0.2s;
        }

        .buscador button:hover {
            background: #d55a0a;
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
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-user:hover {
            background: white;
            color: #1d4a27;
            border-color: white;
        }

        /* === MENÚ DE NAVEGACIÓN HORIZONTAL (CORREGIDO) === */
        .menu-navegacion {
            background: white;
            padding: 12px 24px;
            border-bottom: 2px solid #cde0ca;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .nav-link {
            background: #eef3e9;
            border-radius: 40px;
            padding: 12px 24px;
            text-decoration: none;
            font-weight: 600;
            color: #1d4a27;
            font-size: 0.95rem;
            border: 2px solid transparent;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link i {
            color: #f16b1a;
            font-size: 1rem;
            transition: color 0.2s;
        }

        .nav-link:hover {
            background: #e0e8db;
            transform: translateY(-2px);
            border-color: #f16b1a;
        }

        .nav-link.activo {
            background: #1d4a27;
            color: white;
        }

        .nav-link.activo i {
            color: white;
        }

        .marriba{
            background:#1d4a27; padding:10px 30px; display:flex; align-items:center; 
            justify-content:space-between; gap:20px; border-radius:12px;
            box-shadow:0 6px 18px rgba(0,0,0,0.15); margin-bottom:40px;
        }

        .logo-area{ display:flex; align-items:center; gap:15px; }
        .logo-area img{ width:110px; }
        .logo-area h1{ color:white; font-size:1.5rem; border-left:2px solid rgba(255,255,255,.3); padding-left:16px; }

        .buscador{
            background:#fff; border-radius:50px; padding:5px 5px 5px 20px;
            display:flex; align-items:center; min-width:280px; box-shadow:0 5px 15px rgba(0,0,0,.1);
        }
        .buscador input{ border:none; outline:none; flex:1; padding:12px 0; font-size:15px; }
        .buscador button{ background:#2d5a27; border:none; border-radius:50%; width:45px; height:45px; color:#fff; cursor:pointer; }

        /* Botones de Opciones */
        .opciones{ display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:30px; margin-top:40px; }
        .botonesopciones{
            background: linear-gradient(145deg, #ffffff, #f3f3f3); border-radius: 30px;
            padding: 40px 10px; text-decoration: none; display: flex; flex-direction: column;
            justify-content: center; align-items: center; text-align:center; gap: 30px;
            min-height: 280px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,.08); transition: .3s ease;
        }
        .botonesopciones:hover{ transform:translateY(-12px) scale(1.03); color: inherit; text-decoration: none; }
        .botonesopciones img{ width:240px; height:240px; object-fit:contain; }
        .botonesopciones span{ font-weight:700; color:#1e3a2f; font-size:26px; background:#e8f5e9; padding:15px 40px; border-radius:50px; }

        /* Tablas */
        .tablas{ display:grid; grid-template-columns:repeat(auto-fit, minmax(320px,1fr)); gap:30px; margin-top:40px; }
        .cardtabla{ background:white; border-radius:20px; padding:25px; box-shadow:0 10px 25px rgba(0,0,0,.08); margin-top: 40px;}
        .cardtabla h3{ margin-bottom:15px; color:#1e3a2f; border-bottom:3px solid #2d5a27; padding-bottom:8px; }
        table{ width:100%; border-collapse:collapse; }
        table th{ background:#e8f5e9; padding:10px; text-align:left; }
        table td{ padding:10px; border-bottom:1px solid #eee; }

        /* Estilo para que Select2 se vea bien en el modal */
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid #ced4da !important;
        }
    </style>
</head>

<body>
<div class="fondo">

    <header>
        <!-- Barra superior -->
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="FRUVER Logo">
        </div>

        <div class="buscador">
            <input type="text" id="buscarProducto" placeholder="Buscar producto, código o descripción...">
            <button type="button" aria-label="Buscar"><i class="fas fa-search"></i></button>
        </div>

        <div class="user-actions">
            <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> <span class="hide-mobile">Admin</span></a>
            <a href="#" class="btn-user"><i class="fas fa-bell"></i> <span class="hide-mobile">Notificaciones</span></a>
            <a href="#" class="btn-user"><i class="fas fa-sign-out-alt"></i> <span class="hide-mobile">Salir</span></a>
        </div>
    </div>

    <!-- Menú de navegación horizontal (corregido) -->
    <nav class="menu-navegacion" aria-label="Navegación principal">
        <a href="#" class="nav-link"><i class="fas fa-tag"></i> Precios</a>
        <a href="#" class="nav-link"><i class="fas fa-credit-card"></i> Pagos</a>
        <a href="#" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="#" class="nav-link activo"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="#" class="nav-link"><i class="fas fa-file-invoice"></i> Facturación</a>
        <a href="#" class="nav-link"><i class="fas fa-chart-bar"></i> Reportes</a>
    </nav>

    </header>

    <div class="opciones">
        <a href="<?= base_url('inventario/producto') ?>" class="botonesopciones">
             <img src="<?= base_url('img/Entrada.jpeg') ?>">
            <span>Entrada</span>
        </a>

        <a href="javascript:void(0)" class="botonesopciones" data-toggle="modal" data-target="#modalMerma">
             <img src="<?= base_url('img/Merma.jpeg') ?>">
            <span>Merma</span>
        </a>

        <a href="<?= base_url('productos') ?>" class="botonesopciones">
             <img src="<?= base_url('img/Producto.jpeg') ?>">
            <span>Productos</span>
        </a>
    </div>

    <div class="tablas">
        <div class="cardtabla">
            <h3>Registro de Entrada</h3>
            <table>
                <thead><tr><th>Producto</th><th>Cant.</th><th>Fecha</th></tr></thead>
                <tbody>
                    <?php if(!empty($entradas)): foreach($entradas as $e): ?>
                        <tr><td><?= $e['producto'] ?></td><td><?= $e['cantidad'] ?></td><td><?= $e['fecha'] ?></td></tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="3">No hay registros</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="cardtabla">
            <h3>Registro de Merma</h3>
            <table>
                <thead><tr><th>Producto</th><th>Cant.</th><th>Motivo</th></tr></thead>
                <tbody>
                    <?php if(!empty($mermas)): foreach($mermas as $m): ?>
                        <tr><td><?= $m['producto'] ?></td><td><?= $m['cantidad'] ?></td><td><?= $m['motivo'] ?></td></tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="3">No hay registros</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<div class="cardtabla">
    <h3>Control de Lotes y Caducidad</h3>

    <table>
        <thead>
            <tr>
                <th>Lote</th>
                <th>Producto</th>
                <th>Ubicación</th>
                <th>Stock</th>
                <th>Fecha Entrada</th>
                <th>Caducidad</th>
                <th>Estado</th>
            </tr>
        </thead>

        <tbody>

            <tr>
                <td>L001</td>
                <td>Banano</td>
                <td>Bodega 1</td>
                <td>50</td>
                <td>2026-03-10</td>
                <td>2026-03-20</td>
                <td>Disponible</td>
            </tr>

            <tr>
                <td>L002</td>
                <td>Tomate</td>
                <td>Bodega 2</td>
                <td>30</td>
                <td>2026-03-08</td>
                <td>2026-03-15</td>
                <td>Por vencer</td>
            </tr>

        </tbody>
    </table>
</div>

    
</div> <div class="modal fade" id="modalMerma" tabindex="-1" role="dialog" aria-labelledby="mermaTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="mermaTitle">Registrar Nueva Merma</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="formMerma">
        <div class="modal-body">
            <div class="form-group">
                <label><b>Seleccionar Producto:</b></label>
                <select name="id_producto" id="selectProducto" class="form-control" style="width: 100%" required>
                    <option value="">Buscar producto...</option>
                    <?php if(!empty($lista_productos)): ?>
                        <?php foreach($lista_productos as $lp): ?>
                            <option value="<?= $lp['id_producto'] ?>"><?= $lp['nombre'] ?> (ID: <?= $lp['id_producto'] ?>)</option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label><b>Cantidad:</b></label>
                <input type="number" step="0.01" name="cantidad" class="form-control" placeholder="0.00" required>
            </div>

            <div class="form-group">
                <label><b>Motivo / Descripción:</b></label>
                <textarea name="descripcion" class="form-control" rows="3" placeholder="¿Por qué es merma?" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Guardar Registro</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    
    // Inicializar Select2 cuando se abre el modal
    $('#modalMerma').on('shown.bs.modal', function () {
        $('#selectProducto').select2({
            dropdownParent: $('#modalMerma'),
            placeholder: "Escribe el nombre del producto...",
            allowClear: true
        });
    });

    // Enviar formulario por AJAX
    $('#formMerma').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= base_url('merma/guardar') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'JSON',
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true).text('Guardando...');
            },
            success: function(res) {
                if(res.status == 'success') {
                    alert('¡Merma registrada correctamente!');
                    $('#modalMerma').modal('hide');
                    location.reload(); // Recarga para ver los cambios en la tabla
                } else {
                    alert('Error: ' + (res.msg || 'No se pudo guardar'));
                    $('button[type="submit"]').prop('disabled', false).text('Guardar Registro');
                }
            },
            error: function() {
                alert('Ocurrió un error crítico en el servidor.');
                $('button[type="submit"]').prop('disabled', false).text('Guardar Registro');
            }
        });
    });

    // BUSCADOR EN TABLAS DE INVENTARIO
    $('#buscarProducto').on('input', function() {
        const filtro = $(this).val().toLowerCase().trim();
        
        // Iteramos todas las filas de todas las tablas en la página (Entrada, Merma, Lotes)
        $('.cardtabla tbody tr').each(function() {
            const textoFila = $(this).text().toLowerCase();
            
            if (textoFila.includes(filtro)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>

</body>
</html>