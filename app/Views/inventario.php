<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario - FruverSoft</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('CSS/inventarioestilo.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
            <input type="text" placeholder="Buscar producto, código o descripción...">
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

    <!-- Modal Mejorado para Registrar Merma -->
<div class="modal fade" id="modalMerma" tabindex="-1" role="dialog" aria-labelledby="mermaTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="mermaTitle">
          <i class="fas fa-trash-alt mr-2"></i>Registrar Nueva Merma
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="formMerma" class="needs-validation" novalidate>
        <div class="modal-body">
          <!-- Alerta de errores -->
          <div id="mermaAlert" class="alert alert-danger d-none" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span id="alertMessage"></span>
          </div>

          <div class="form-group">
            <label for="selectProducto" class="font-weight-bold">
              <i class="fas fa-box mr-1"></i>Producto <span class="text-danger">*</span>
            </label>
            <select name="id_producto" id="selectProducto" class="form-control" style="width: 100%" required>
              <option value="">Buscar producto por nombre o ID...</option>
              <?php if(!empty($lista_productos)): ?>
                <?php foreach($lista_productos as $lp): ?>
                  <option value="<?= $lp['id_producto'] ?>" 
                          data-stock="<?= $lp['stock_actual'] ?? 0 ?>"
                          data-nombre="<?= esc($lp['nombre']) ?>">
                    <?= esc($lp['nombre']) ?> (ID: <?= $lp['id_producto'] ?>) - Stock: <?= $lp['stock_actual'] ?? 0 ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
            <small class="form-text text-muted stock-info" id="stockInfo"></small>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="cantidad" class="font-weight-bold">
                  <i class="fas fa-sort-amount-up mr-1"></i>Cantidad <span class="text-danger">*</span>
                </label>
                <input type="number" 
                       step="0.01" 
                       min="0.01" 
                       name="cantidad" 
                       id="cantidad"
                       class="form-control" 
                       placeholder="0.00" 
                       required>
                <div class="invalid-feedback">
                  Ingrese una cantidad válida mayor a 0
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="tipo_merma" class="font-weight-bold">
                  <i class="fas fa-tag mr-1"></i>Tipo de Merma
                </label>
                <select name="tipo_merma" id="tipo_merma" class="form-control">
                  <option value="caducidad">Caducidad</option>
                  <option value="dañado">Producto Dañado</option>
                  <option value="robo">Robo/Hurto</option>
                  <option value="error_inventario">Error de Inventario</option>
                  <option value="calidad">Problemas de Calidad</option>
                  <option value="otro">Otro</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="descripcion" class="font-weight-bold">
              <i class="fas fa-align-left mr-1"></i>Motivo / Descripción <span class="text-danger">*</span>
            </label>
            <textarea name="descripcion" 
                      id="descripcion"
                      class="form-control" 
                      rows="4" 
                      placeholder="Describa detalladamente el motivo de la merma..."
                      required></textarea>
            <div class="invalid-feedback">
              Por favor, describa el motivo de la merma
            </div>
            <small class="text-muted">
              <span id="charCount">0</span>/500 caracteres
            </small>
          </div>

          <!-- Información adicional opcional -->
          <div class="form-check mb-2">
            <input type="checkbox" class="form-check-input" name="ajustar_stock" id="ajustarStock" checked>
            <label class="form-check-label" for="ajustarStock">
              Ajustar stock automáticamente
            </label>
          </div>

          <div class="form-group" id="responsableGroup">
            <label for="responsable" class="font-weight-bold">
              <i class="fas fa-user mr-1"></i>Responsable
            </label>
            <input type="text" 
                   name="responsable" 
                   id="responsable"
                   class="form-control" 
                   value="<?= session()->get('usuario_nombre') ?? '' ?>" 
                   placeholder="Nombre del responsable">
          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancelar
          </button>
          <button type="submit" class="btn btn-danger" id="btnGuardar">
            <i class="fas fa-save mr-1"></i>Guardar Registro
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  let selectedProductStock = 0;
  
  // Inicializar Select2 con mejoras
  $('#modalMerma').on('shown.bs.modal', function () {
    $('#selectProducto').select2({
      dropdownParent: $('#modalMerma'),
      placeholder: "Buscar producto por nombre o ID...",
      allowClear: true,
      width: '100%',
      language: {
        noResults: function() {
          return "No se encontraron productos";
        },
        searching: function() {
          return "Buscando...";
        }
      }
    });
    
    // Resetear formulario al abrir
    $('#formMerma')[0].reset();
    $('#formMerma').removeClass('was-validated');
    $('#mermaAlert').addClass('d-none');
    $('#btnGuardar').prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Guardar Registro');
    $('#charCount').text('0');
  });

  // Mostrar stock disponible al seleccionar producto
  $('#selectProducto').on('change', function() {
    const selected = $(this).find(':selected');
    const stock = selected.data('stock') || 0;
    const nombre = selected.data('nombre') || '';
    
    selectedProductStock = stock;
    
    if (stock !== undefined) {
      $('#stockInfo').html(`
        <i class="fas fa-info-circle text-info mr-1"></i>
        Stock actual de <strong>${nombre}</strong>: ${stock} unidades
      `);
      
      // Validar cantidad máxima
      $('#cantidad').attr('max', stock);
    } else {
      $('#stockInfo').html('');
      $('#cantidad').removeAttr('max');
    }
  });

  // Validar cantidad contra stock disponible
  $('#cantidad').on('input', function() {
    const cantidad = parseFloat($(this).val()) || 0;
    
    if (selectedProductStock > 0 && cantidad > selectedProductStock) {
      $(this).addClass('is-invalid');
      $(this).siblings('.invalid-feedback').text('La cantidad no puede ser mayor al stock disponible (' + selectedProductStock + ')');
    } else {
      $(this).removeClass('is-invalid');
    }
  });

  // Contador de caracteres para descripción
  $('#descripcion').on('input', function() {
    const count = $(this).val().length;
    $('#charCount').text(count);
    
    if (count > 500) {
      $(this).val($(this).val().substring(0, 500));
      $('#charCount').text(500);
    }
  });

  // Validación y envío del formulario
  $('#formMerma').on('submit', function(e) {
    e.preventDefault();
    
    // Validación del lado del cliente
    if (!this.checkValidity()) {
      e.stopPropagation();
      $(this).addClass('was-validated');
      return;
    }
    
    // Validar cantidad contra stock
    const cantidad = parseFloat($('#cantidad').val());
    if (selectedProductStock > 0 && cantidad > selectedProductStock) {
      showAlert('La cantidad no puede ser mayor al stock disponible (' + selectedProductStock + ')', 'danger');
      return;
    }
    
    // Preparar datos del formulario
    const formData = $(this).serializeArray();
    formData.push({
      name: 'fecha_registro',
      value: new Date().toISOString().slice(0, 19).replace('T', ' ')
    });
    
    // Enviar por AJAX
    $.ajax({
      url: '<?= base_url('merma/guardar') ?>',
      type: 'POST',
      data: $.param(formData),
      dataType: 'JSON',
      beforeSend: function() {
        $('#btnGuardar').prop('disabled', true)
                       .html('<i class="fas fa-spinner fa-spin mr-1"></i>Guardando...');
        $('#mermaAlert').addClass('d-none');
      },
      success: function(res) {
        if(res.status == 'success') {
          // Mostrar mensaje de éxito con SweetAlert si está disponible
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'success',
              title: '¡Éxito!',
              text: 'Merma registrada correctamente',
              timer: 2000,
              showConfirmButton: false
            }).then(() => {
              $('#modalMerma').modal('hide');
              location.reload();
            });
          } else {
            alert('¡Merma registrada correctamente!');
            $('#modalMerma').modal('hide');
            location.reload();
          }
        } else {
          showAlert(res.msg || 'Error al guardar la merma', 'danger');
          $('#btnGuardar').prop('disabled', false)
                         .html('<i class="fas fa-save mr-1"></i>Guardar Registro');
        }
      },
      error: function(xhr, status, error) {
        console.error('Error:', error);
        console.error('Response:', xhr.responseText);
        
        let errorMsg = 'Ocurrió un error en el servidor';
        try {
          const response = JSON.parse(xhr.responseText);
          errorMsg = response.message || errorMsg;
        } catch(e) {
          // Si no es JSON, mostrar error genérico
        }
        
        showAlert(errorMsg, 'danger');
        $('#btnGuardar').prop('disabled', false)
                       .html('<i class="fas fa-save mr-1"></i>Guardar Registro');
      }
    });
  });

  // Función para mostrar alertas
  function showAlert(message, type = 'danger') {
    $('#alertMessage').text(message);
    $('#mermaAlert').removeClass('d-none alert-success alert-danger alert-warning')
                   .addClass('alert-' + type);
    
    // Auto-ocultar después de 5 segundos
    setTimeout(function() {
      $('#mermaAlert').addClass('d-none');
    }, 5000);
  }

  // Limpiar al cerrar el modal
  $('#modalMerma').on('hidden.bs.modal', function() {
    $('#selectProducto').val('').trigger('change');
    $('#stockInfo').html('');
    $('#charCount').text('0');
    $('#mermaAlert').addClass('d-none');
  });
});
</script>

<style>
/* Estilos adicionales para mejorar la apariencia */
.modal-header.bg-danger {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

.select2-container--default .select2-selection--single {
  height: calc(1.5em + 0.75rem + 2px);
  padding: 0.375rem 0.75rem;
  border: 1px solid #ced4da;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
  height: calc(1.5em + 0.75rem + 2px);
}

.stock-info {
  margin-top: 0.25rem;
  font-size: 0.875em;
}

#charCount {
  font-weight: bold;
}

/* Validación personalizada */
.was-validated .form-control:invalid ~ .stock-info {
  color: #dc3545;
}
</style>

</body>
</html>