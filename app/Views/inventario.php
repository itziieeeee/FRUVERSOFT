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
        /* Estilos base para que el modal "Premium" luzca bien con el resto */
        .modal-overlay-custom {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(20, 35, 20, 0.7); backdrop-filter: blur(6px);
            display: flex; align-items: center; justify-content: center;
            z-index: 2000; visibility: hidden; opacity: 0; transition: all 0.3s;
        }
        .modal-overlay-custom.active { visibility: visible; opacity: 1; }
        .modal-container-custom {
            background: #fff; border-radius: 2rem; width: 90%; max-width: 550px;
            box-shadow: 0 30px 50px rgba(0,0,0,0.4); overflow: hidden;
        }
        .form-group label { font-weight: 600; color: #2a4b2d; }
        .unidad-help { background: #eef5ea; padding: 0.5rem; border-radius: 10px; font-size: 0.8rem; }
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

       <!-- Menú de navegación opciones -->

    <nav class="menu-navegacion" aria-label="Navegación principal">

        <a href="pantalla_ventas" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>

        <a href="pantalla_pedidos" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>

        <a href="inventario" class="nav-link activo"><i class="fas fa-boxes"></i> Inventario</a>

        <a href="pantalla_clientes" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>

        <a href="pantalla_repartidores" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>

          <a href="pantalla_productos" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>

    </nav>
    </header>

    <div class="opciones">
        <a href="javascript:void(0);" class="botonesopciones" id="btnAbrirEntrada">
            <img src="<?= base_url('img/Entrada.jpeg') ?>">
            <span>Entrada</span>
        </a>

        <a href="javascript:void(0)" class="botonesopciones" data-toggle="modal" data-target="#modalMerma">
            <img src="<?= base_url('img/Merma.jpeg') ?>">
            <span>Merma</span>
        </a>

        <a href="<?= base_url('productos') ?>" class="botonesopciones">
            <img src="<?= base_url('img/Producto.jpeg') ?>">
            <span>Existencias</span>
        </a>
    </div>

   
<div id="productModal" class="modal-overlay-custom">
    <div class="modal-container-custom">
        <div class="modal-header d-flex justify-content-between p-3 border-bottom">
            <h4 class="mb-0"> Registrar entrada</h4>
            <button class="close" id="closeModalBtn">&times;</button>
        </div>
        <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
            <form id="productForm">
                <div class="form-group">
                    <label>Producto</label>
                    <input type="text" id="productName" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Unidad Medida</label>
                        <select id="unitMeasure" class="form-control">
                            <option value="caja">Caja</option>
                            <option value="mazo">Mazo</option>
                            <option value="arpilla">Arpilla</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Cantidad</label>
                        <input type="number" id="quantityReceived" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Fecha Entrada</label>
                    <input type="date" id="entryDate" class="form-control" required>
                </div>
                <div class="form-group">
                    <label> Expiración</label>
                    <input type="date" id="expirationDate" class="form-control" readonly style="background:#f8f9fa">
                </div>
                <button type="submit" class="btn btn-success btn-block mt-3">Guardar</button>
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
        <form>
            <div class="form-group">
                <label>Producto</label>
                <select class="form-control select2" style="width: 100%">
                    <option>Seleccionar...</option>
                </select>
            </div>
            <div class="form-group">
                <label>Motivo</label>
                <textarea class="form-control" rows="3"></textarea>
            </div>
            <button type="button" class="btn btn-danger btn-block">Registrar Merma</button>
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
        $('.select2').select2(); // Inicializa select2 para merma

        const modalEntrada = document.getElementById('productModal');
        
        // Abrir Modal Entrada
        $('#btnAbrirEntrada').on('click', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('entryDate').value = today;
            updateExpiration();
            modalEntrada.classList.add('active');
        });

        // Cerrar Modal Entrada
        $('#closeModalBtn').on('click', function() {
            modalEntrada.classList.remove('active');
        });

        // Cálculo de fecha de expiración (+5 días)
        $('#entryDate').on('change', updateExpiration);

        function updateExpiration() {
            let date = new Date($('#entryDate').val());
            if(!isNaN(date.getTime())){
                date.setDate(date.getDate() + 5);
                document.getElementById('expirationDate').value = date.toISOString().split('T')[0];
            }
        }

        // Manejo del formulario Entrada
        $('#productForm').on('submit', function(e) {
            e.preventDefault();
            alert("¡Producto registrado en el sistema!");
            modalEntrada.classList.remove('active');
        });
    });
</script>
</body>
</html>