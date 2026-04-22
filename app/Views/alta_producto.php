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

    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo">
        </div>
        <div class="buscador">
            <input type="text" placeholder="Buscar...">
            <button><i class="fas fa-search"></i></button>
        </div>
        <div class="user-actions">
            <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> <span>Admin</span></a>
            <a href="#" class="btn-user"><i class="fas fa-bell"></i> <span>Notificaciones</span></a>
            <a href="<?= base_url('menusolo') ?>" class="btn-user"><i class="fas fa-sign-out-alt"></i> <span>Regresar</span></a>
        </div>
    </div>

    <nav class="menu-navegacion">
        <div class="nav-links">
            <a href="pantalla_ventas" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
            <a href="pantalla_pedidos" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
            <a href="<?= base_url('pantalla_inventario') ?>" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="pantalla_clientes" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
            <a href="pantalla_repartidores" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
            <a href="pantalla_productos" class="nav-link activo"><i class="fa-solid fa-apple-whole"></i> Productos</a>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="contenedor-principal">

        <!-- Formulario -->
        <div class="card-form">
            <div class="cabezacard">
                <i class="fas fa-clipboard-list"></i>
                <h2>Nuevo Producto</h2>
            </div>

            <!-- UN SOLO form, action apunta al método guardar del controlador -->
            <form action="<?= base_url('producto/guardar') ?>" method="POST" enctype="multipart/form-data" id="formProducto" novalidate>
                <?= csrf_field() ?>

                <div class="form-grid">

                    <!-- Nombre del producto -->
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
                               aria-required="true"
                               value="<?= old('nombre') ?>">
                        <div class="mensaje-error" id="error-nombre"></div>
                    </div>

                    <!-- Descripción -->
                    <div class="campo campo-full">
                        <label for="descripcion"><i class="fas fa-align-left"></i> Descripción</label>
                        <textarea id="descripcion" name="descripcion"
                                  placeholder="Características adicionales, variedad, presentación, origen..."
                                  maxlength="500"><?= old('descripcion') ?></textarea>
                    </div>

                    <!-- Imagen del producto -->
                    <div class="campo campo-full">
                        <label for="foto">
                            <i class="fas fa-image"></i> Imagen del producto
                            <span class="requerido">*</span>
                        </label>
                        <input type="file" id="foto" name="foto" accept="image/jpeg, image/png, image/jpg" required>
                        <small class="hint">Formatos permitidos: JPG, PNG, JPEG. Máx: 2MB</small>
                        <div class="mensaje-error" id="error-foto"></div>
                    </div>

                    <!-- Vista previa de imagen -->
                    <div class="campo campo-full">
                        <img id="preview" src="" alt="Vista previa" style="max-width:150px; display:none; margin-top:10px;">
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
        // Vista previa de imagen
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('preview');

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        });

        // Limpiar preview al resetear
        document.getElementById('btnLimpiar').addEventListener('click', function() {
            document.getElementById('nombre').classList.remove('error');
            document.getElementById('error-nombre').textContent = '';
            document.getElementById('preview').style.display = 'none';
            document.getElementById('preview').src = '';
        });

        // Validación del formulario antes de enviar
        document.getElementById('formProducto').addEventListener('submit', function(e) {
            let isValid = true;

            const nombre = document.getElementById('nombre');
            const errorNombre = document.getElementById('error-nombre');
            const foto = document.getElementById('foto');
            const errorFoto = document.getElementById('error-foto');

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

            // Validar imagen
            if (!foto.files || foto.files.length === 0) {
                errorFoto.textContent = 'Debes seleccionar una imagen para el producto';
                isValid = false;
            } else {
                const archivo = foto.files[0];
                const tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg'];
                const maxSize = 2 * 1024 * 1024; // 2MB

                if (!tiposPermitidos.includes(archivo.type)) {
                    errorFoto.textContent = 'Solo se permiten imágenes JPG o PNG';
                    isValid = false;
                } else if (archivo.size > maxSize) {
                    errorFoto.textContent = 'La imagen no debe superar los 2MB';
                    isValid = false;
                } else {
                    errorFoto.textContent = '';
                }
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Prevenir envío con Enter en inputs de texto
        document.querySelectorAll('input[type="text"]').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        });
    </script>

</body>
</html>