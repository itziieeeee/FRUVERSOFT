<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVER - Inventario</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= base_url('css/inventarioestilo.css') ?>">

    <style>
        /* Estilos para el modal personalizado de Entrada */
        .modal-overlay-custom {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(20, 35, 20, 0.7); backdrop-filter: blur(6px);
            display: flex; align-items: center; justify-content: center;
            z-index: 2000; visibility: hidden; opacity: 0; transition: all 0.3s;
        }
        .modal-overlay-custom.active { visibility: visible !important; opacity: 1 !important; }
        .modal-container-custom {
            background: #fff; border-radius: 2rem; width: 90%; max-width: 550px;
            box-shadow: 0 30px 50px rgba(0,0,0,0.4); overflow: hidden;
        }
        
        .select2-container--open {
        z-index: 9999 !important;
}

        .select2-container {
        width: 100% !important;
}


        /* Ajustes de Scroll y Fondo */
        html, body {
            overflow-y: auto !important;
            height: auto !important;
            min-height: 100vh;
        }
        .fondo {
            overflow: visible !important;
            display: block !important; 
            height: auto !important;
        }
        .modal-open { overflow: auto !important; padding-right: 0 !important; }

        /* Imágenes de botones */
        .opciones .botonesopciones img {
            width: 190px !important;
            height: 190px !important;
            object-fit: cover;
        }
        .opciones {
            display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;
        }

        /* Corrección Select2 */
        .select2-container { z-index: 3000 !important; }
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            padding: 5px !important;
            border: 1px solid #ced4da !important;
        }
    </style>
</head>

<body>
<div class="fondo">
    <header>
        <div class="barra-superior">
            <div class="logo-area">
                <img src="<?= base_url('img/LOGO1.png') ?>">
            </div>
            <div class="buscador">
                <input type="text" placeholder="Buscar producto...">
                <button type="button"><i class="fas fa-search"></i></button>
            </div>
            <div class="user-actions">
                <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> Admin</a>
                <a href="menusolo" class="btn-user"><i class="fas fa-sign-out-alt"></i> Regresar</a>
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

    <?php if (session()->getFlashdata('mensaje')): ?>
        <div class="alert alert-success text-center"><?= session()->getFlashdata('mensaje') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="opciones">
        <a href="javascript:void(0);" class="botonesopciones" id="btnAbrirEntrada">
            <img src="<?= base_url('img/Entrada.jpeg') ?>">
            <span>Entrada</span>
        </a>

        <a href="javascript:void(0)" class="botonesopciones" data-toggle="modal" data-target="#modalMerma">
            <img src="<?= base_url('img/Merma.jpeg') ?>">
            <span>Merma</span>
        </a>

        <a href="<?= base_url('existencias') ?>" class="botonesopciones">
            <img src="<?= base_url('img/existencias.png') ?>">
            <span>Existencias</span>
        </a>
    </div>

    <div class="container mt-4">
        <h4>Stock actual</h4>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Producto</th>
                    <th>Total</th>
                    <th>Merma</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($existencias)): ?>
                    <?php foreach ($existencias as $e): ?>
                        <tr>
                            <td><?= $e['nombre'] ?></td>
                            <td><?= $e['e_total'] ?></td>
                            <td><?= $e['e_merma'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center">No hay datos en el inventario</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="productModal" class="modal-overlay-custom">
    <div class="modal-container-custom">
        <div class="modal-header d-flex justify-content-between p-3 border-bottom">
            <h4 class="mb-0">Registrar Entrada</h4>
            <button type="button" class="close" id="closeModalBtn">&times;</button>
        </div>
        <div class="modal-body p-4">
            <form id="productForm" method="POST" action="<?= base_url('confirmar-entrada') ?>">
                <div class="form-group">
                    <label>Producto</label>
                    <select name="id_producto" id="selectEntrada" class="form-control mi-buscador" style="width: 100%;">
                        <option value="">Seleccione un producto...</option>
                        <?php foreach ($productos as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= $p['nombre'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Unidad Medida</label>
                        <select name="unidad_compra" class="form-control">
                            <option value="caja">Caja</option>
                            <option value="mazo">Mazo</option>
                            <option value="arpilla">Arpilla</option>
                            <option value="tonelada">Tonelada</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Cantidad</label>
                        <input type="number" step="0.001" name="cantidad" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-block mt-3">Guardar Entrada</button>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMerma" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Registrar Merma</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form method="POST" action="<?= base_url('merma/guardar') ?>">
            <div class="form-group">
                <label>Producto a Mermar</label>
                <select name="id_producto" id="selectMerma" class="form-control mi-buscador" required style="width: 100%;">
                    <option value="">Buscar producto...</option>
                    <?php if (!empty($productos_merma)): ?>
                        <?php foreach ($productos_merma as $pm): ?>
                            <option value="<?= $pm['id_p'] ?>">
                                <?= $pm['nombre'] ?> - (Disponible: <?= $pm['e_total'] ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Cantidad a retirar</label>
                <input type="number" step="0.001" name="cantidad" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Motivo</label>
                <textarea name="motivo" class="form-control" rows="3" placeholder="Ej: Producto golpeado o caducado"></textarea>
            </div>
            <button type="submit" class="btn btn-danger btn-block">Confirmar Merma</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // 1. Lógica para el modal personalizado de ENTRADA
        const modalEntrada = $('#productModal');

        $('#btnAbrirEntrada').on('click', function(e) {
            e.preventDefault();
            modalEntrada.addClass('active');
            // Inicializar Select2 al abrir
            $('#selectEntrada').select2({
                placeholder: "Seleccione un producto...",
                dropdownParent: modalEntrada
            });
        });

        $('#closeModalBtn').on('click', function() {
            modalEntrada.removeClass('active');
        });

        // Cerrar al hacer clic fuera
        $(window).on('click', function(event) {
            if ($(event.target).is(modalEntrada)) {
                modalEntrada.removeClass('active');
            }
        });

        // 2. Lógica para el modal de MERMA (Bootstrap)
        $('#modalMerma').on('shown.bs.modal', function () {
            $('#selectMerma').select2({
                placeholder: "Buscar producto...",
                dropdownParent: $('#modalMerma')
            });
        });

        $('#modalMerma').on('hidden.bs.modal', function () {
            $('#selectMerma').select2('destroy');
        });
    });
</script>

</body>
</html>