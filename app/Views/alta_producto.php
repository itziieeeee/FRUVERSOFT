<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
     <link rel="stylesheet" href="<?= base_url('CSS/altaproducto.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Nuevo Producto</title>
   
</head>
<body>
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

    <!-- Contenido principal -->
    <main class="contenedor-principal">
       

        <!-- Formulario  -->
        <div class="card-form">
            <div class="cabezacard">
                <i class="fas fa-clipboard-list"></i>
                <h2>Nuevo Producto</h2>
            </div>

            <form action="<?= base_url('producto/guardar') ?>" method="POST" id="formProducto" novalidate>
                <div class="form-grid">
                    <!-- Nombre del producto (requerido) -->
                    <div class="campo campo-full">
                        <label for="nombre">
                            <i class="fas fa-tag"></i> 
                            Nombre del producto 
                            <span class="requerido" title="Campo requerido">*</span>
                        </label>
                        <input type="text" id="nombre" name="nombre" 
                               placeholder="Ej. Tomate Saladet, Fresa, Sandía..." 
                               required 
                               maxlength="100"
                               aria-required="true">
                        <div class="mensaje-error" id="error-nombre"></div>
                    </div>

                    <!-- Descripción -->
                    <div class="campo campo-full">
                        <label for="descripcion"><i class="fas fa-align-left"></i> Descripción</label>
                        <textarea id="descripcion" name="descripcion" 
                                  placeholder="Características adicionales, variedad, presentación, origen..."
                                  maxlength="500"></textarea>
                    </div>

                    <!-- Unidad de compra -->
                    <div class="campo">
                        <label><i class="fas fa-truck-loading"></i> Unidad de compra *</label>
                        <div class="unidades-grid" role="group" aria-label="Unidad de compra">
                            <button type="button" class="unidad-btn activo" data-unidad="caja" data-tipo="compra" onclick="seleccionarUnidad(this, 'compra')">
                                <i class="fas fa-box"></i> Caja
                            </button>
                            <button type="button" class="unidad-btn" data-unidad="kilo" data-tipo="compra" onclick="seleccionarUnidad(this, 'compra')">
                                <i class="fas fa-weight"></i> Kilo
                            </button>
                            <button type="button" class="unidad-btn" data-unidad="tonelada" data-tipo="compra" onclick="seleccionarUnidad(this, 'compra')">
                                <i class="fas fa-cubes"></i> Tonelada
                            </button>
                        </div>
                        <input type="hidden" name="unidad_compra" id="unidad_compra" value="caja" required>
                    </div>

                    <!-- Unidad de venta -->
                    <div class="campo">
                        <label><i class="fas fa-shopping-cart"></i> Unidad de venta *</label>
                        <div class="unidades-grid" role="group" aria-label="Unidad de venta">
                            <button type="button" class="unidad-btn" data-unidad="caja" data-tipo="venta" onclick="seleccionarUnidad(this, 'venta')">
                                <i class="fas fa-box"></i> Caja
                            </button>
                            <button type="button" class="unidad-btn activo" data-unidad="kilo" data-tipo="venta" onclick="seleccionarUnidad(this, 'venta')">
                                <i class="fas fa-weight"></i> Kilo
                            </button>
                            <button type="button" class="unidad-btn" data-unidad="pieza" data-tipo="venta" onclick="seleccionarUnidad(this, 'venta')">
                                <i class="fa-solid fa-chess-bishop"></i> Pieza
                            </button>
                            <button type="button" class="unidad-btn" data-unidad="charola" data-tipo="venta" onclick="seleccionarUnidad(this, 'venta')">
                                <i class="fa-solid fa-inbox"></i> Charola
                            </button>
                        </div>
                        <input type="hidden" name="unidad_venta" id="unidad_venta" value="kilo" required>
                    </div>

                    <!-- Stock inicial -->
                    <div class="campo">
                        <label for="stock"><i class="fa-solid fa-box"></i> Stock inicial</label>
                        <input type="number" id="stock" name="stock" step="0.001" min="0" placeholder="0.00">
                    </div>

                    <!-- Precio compra -->
                    <div class="campo">
                        <label for="precio_compra"><i class="fa-regular fa-money-bill-1"></i> Precio compra</label>
                        <input type="number" id="precio_compra" name="precio_compra" step="0.01" min="0" placeholder="$0.00">
                    </div>

                    <!-- Precio venta -->
                    <div class="campo">
                        <label for="precio_venta"><i class="fa-solid fa-bag-shopping"></i> Precio venta</label>
                        <input type="number" id="precio_venta" name="precio_venta" step="0.01" min="0" placeholder="$0.00">
                    </div>

                    <!-- Categoría -->
                    <div class="campo">
                        <label for="categoria"><i class="fas fa-folder"></i> Categoría</label>
                        <select id="categoria" name="categoria">
                            <option value="">Seleccionar categoría...</option>
                            <option value="frutas">Frutas</option>
                            <option value="verduras"> Verduras</option>
                        </select>
                    </div>


                    <!-- Acciones -->
                    <div class="form-actions">
                        <button type="reset" class="btn-secundario" id="btnLimpiar">
                            <i class="fas fa-undo-alt"></i> Limpiar
                        </button>
                        <button type="submit" class="btn-primario" id="btnGuardar">
                            <i class="fas fa-save"></i> Guardar producto
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Función para seleccionar unidades (mejorada)
        function seleccionarUnidad(btn, tipo) {
            const contenedor = btn.parentElement;
            const botones = contenedor.querySelectorAll('.unidad-btn');
            
            botones.forEach(b => b.classList.remove('activo'));
            btn.classList.add('activo');
            
            // Obtener la unidad del atributo data
            const unidad = btn.dataset.unidad;
            
            if (tipo === 'compra') {
                document.getElementById('unidad_compra').value = unidad;
            } else if (tipo === 'venta') {
                document.getElementById('unidad_venta').value = unidad;
            }
        }

        // Validación del formulario
        document.getElementById('formProducto').addEventListener('submit', function(e) {
            let isValid = true;
            const nombre = document.getElementById('nombre');
            const errorNombre = document.getElementById('error-nombre');
            
            // Validar nombre
            if (!nombre.value.trim()) {
                nombre.classList.add('error');
                errorNombre.textContent = 'El nombre del producto es obligatorio';
                isValid = false;
            } else if (nombre.value.trim().length < 3) {
                nombre.classList.add('error');
                errorNombre.textContent = 'El nombre debe tener al menos 3 caracteres';
                isValid = false;
            } else {
                nombre.classList.remove('error');
                errorNombre.textContent = '';
            }
            
            // Validar unidades (están por defecto, pero verificamos)
            if (!document.getElementById('unidad_compra').value) {
                alert('Debe seleccionar una unidad de compra');
                isValid = false;
            }
            
            if (!document.getElementById('unidad_venta').value) {
                alert('Debe seleccionar una unidad de venta');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });

        // Limpiar errores al resetear
        document.getElementById('btnLimpiar').addEventListener('click', function() {
            document.getElementById('nombre').classList.remove('error');
            document.getElementById('error-nombre').textContent = '';
        });

        // Prevenir envío con Enter en campos específicos
        const inputs = document.querySelectorAll('input:not([type="submit"])');
        inputs.forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>