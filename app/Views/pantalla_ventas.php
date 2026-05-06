<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>FRUVER · Control de Pedidos</title>

     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/ventasestilo.css') ?>">
    
</head>
<style>
    
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

        /* Estilos adicionales para la sección unificada */
        .seccion-unificada {
            background: var(--white);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            padding: 1.8rem;
            margin-bottom: 2rem;
        }

        .grid-2-columnas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .divider-vertical {
            border-left: 2px solid var(--gray-border);
            padding-left: 2rem;
        }

        @media (max-width: 768px) {
            .grid-2-columnas {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            .divider-vertical {
                border-left: none;
                padding-left: 0;
                border-top: 2px solid var(--gray-border);
                padding-top: 1.5rem;
                margin-top: 0.5rem;
            }
        }

        .fila-flex {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-top: 0.5rem;
        }

        .fila-flex .form-group {
            flex: 1;
            min-width: 180px;
        }

        .btn-guardar-pedido {
            margin-top: 1.8rem;
            text-align: right;
            border-top: 1px solid var(--gray-border);
            padding-top: 1.5rem;
        }

        .subtitulo-seccion {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-green);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light-green);
        }
        /* Modal de éxito */
.modal-exito {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease;
}

.modal-exito .modal-contenido {
    background: white;
    border-radius: 20px;
    max-width: 450px;
    width: 90%;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    animation: slideUp 0.3s ease;
}

.modal-exito .icono-exito {
    font-size: 4rem;
    color: #1d4a27;
    margin-bottom: 1rem;
}

.modal-exito h3 {
    color: #1d4a27;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.modal-exito .detalle-pedido {
    background: #e8f3e6;
    border-radius: 12px;
    padding: 1rem;
    margin: 1rem 0;
    text-align: left;
    font-size: 0.9rem;
}

.modal-exito .detalle-pedido p {
    margin: 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-exito .btn-cerrar {
    background: linear-gradient(105deg, #f16b1a, #e05a0c);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 40px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    margin-top: 1rem;
    transition: transform 0.2s;
}

.modal-exito .btn-cerrar:hover {
    transform: scale(1.02);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>
<body>

<div class="barra-superior">
    <div class="logo-area">
        <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo FRUVER" width="140">
    </div>
    <div class="user-actions">
        <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> <span>Admin</span></a>
        <a href="#" class="btn-user"><i class="fas fa-bell"></i> <span>Notificaciones</span></a>
        <a href="<?= base_url('menusolo') ?>" class="btn-user"><i class="fas fa-sign-out-alt"></i> <span>Regresar</span></a>
    </div>
</div>

<nav class="menu-navegacion">
    <div class="nav-links">
        <a href="<?= base_url('pantalla_ventas') ?>" class="nav-link activo"><i class="fas fa-tag"></i> Ventas</a>
        <a href="<?= base_url('pantalla_pedidos') ?>" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="<?= base_url('pantalla_inventario') ?>" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="<?= base_url('pantalla_clientes') ?>" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="<?= base_url('pantalla_repartidores') ?>" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="<?= base_url('pantalla_productos') ?>" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </div>
</nav>

<div class="container">
    <!-- SECCIÓN UNIFICADA: Nueva venta / pedido + Gestión de estado -->
    <div class="seccion-unificada">
        <h2><i class="fa-solid fa-cart-plus"></i> Nuevo Pedido</h2>
        
        <div class="form-group" style="margin-bottom: 24px;">
            <label> Cliente</label>
            <select id="selectCliente" name="id_cliente" required style="max-width: 320px;">
                <option value="">Seleccione...</option>
                <option value="0">Público general</option>
                <?php foreach($clientes as $cliente): ?>
                    <option value="<?= $cliente['id_cliente']; ?>">
                        <?= $cliente['nombre'] . ' ' . ($cliente['apellido_paterno'] ?? '') . ' ' . ($cliente['apellido_materno'] ?? ''); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid-2-columnas">
            <!-- Columna izquierda: Registrar Productos -->
            <div>
                <div class="subtitulo-seccion"><i class="fa-solid fa-apple-alt"></i> Registrar Productos</div>
                <form class="form-producto" id="formAgregarProducto">
                    <div class="form-group">
                        <label>Producto</label>
                        <select id="selectProducto" name="id_producto" required>
                            <option value="">Seleccione producto...</option>
                            <?php foreach($productos as $prod): ?>
                                <option value="<?= $prod['id']; ?>" data-precio="<?= $prod['precio_sugerido']; ?>" data-unidad="<?= $prod['unidad_venta'] ?? ''; ?>">
                                    <?= $prod['nombre']; ?>
                                </option> 
                            <?php endforeach; ?>
                        </select>
                        <div id="hintUnidad" class="hint-unidad"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Unidad de venta</label>
                        <select id="selectUnidad" name="unidad_venta" required>
                            <option value="">Seleccione unidad...</option>
                            <?php if(!empty($unidades)): ?>
                                <?php foreach($unidades as $unidad): ?>
                                    <option value="<?= $unidad; ?>"><?= ucfirst($unidad); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Cantidad</label>
                        <input type="number" step="0.01" min="0.01" name="cantidad" placeholder="Ej: 2.5" required id="inputCantidad">
                    </div>
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-circle-plus"></i> Agregar Producto</button>
                </form>
            </div>

            <!-- Columna derecha: Gestión del Pedido (Tipo venta + Entrega) -->
            <div class="divider-vertical">
                <div class="subtitulo-seccion"><i class="fa-solid fa-truck-fast"></i> Gestión del Pedido</div>
                <form id="formEstado">
                    <div class="fila-flex">
                        <div class="form-group">
                            <label>Tipo de venta:</label>
                            <select id="selectTipoVenta" name="tipo_venta" class="select-gestion">
                                <option value="contado">Contado</option>
                                <option value="credito">Crédito</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fa-solid fa-dolly" style="color:#1d4a27;"></i> Entrega:</label>
                            <select id="selectEntrega" name="tipo_entrega" class="select-gestion">
                                <option value="tienda">Tienda</option>
                                <option value="domicilio">Domicilio</option>
                            </select>
                        </div>

                        <!-- Campo adicional para repartidor (se muestra solo si entrega es domicilio) -->
                        <div class="form-group" id="grupoRepartidor" style="display: none;">
                            <label><i class="fa-solid fa-motorcycle"></i> Repartidor:</label>
                            <select id="selectRepartidor" name="id_repartidor">
                                <option value="">Seleccione repartidor...</option>
                               <?php foreach($repartidores as $repartidor): ?>
    <option value="<?= $repartidor['id']; ?>">
        <?= $repartidor['nombre'] . ' ' . $repartidor['ap_p']; ?>
    </option>
<?php endforeach; ?>
                                
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de productos en este pedido -->
        <div class="tabla-container" style="margin-top: 2rem;">
            <h3><i class="fa-solid fa-list"></i> Productos en este pedido</h3>
            <table class="tabla-productos" id="tablaProductos">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Unidad</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbodyProductos">
                    <!-- filas dinámicas -->
                </tbody>
            </table>
        </div>
        
        <div class="btn-guardar-pedido">
            <button onclick="enviarPedido()" class="btn-primary" style="background: linear-gradient(105deg, #f16b1a, #e05a0c);">
                <i class="fa-regular fa-floppy-disk"></i> Guardar Pedido
            </button>
        </div>
    </div>
</div>

<script>

// Mostrar/ocultar campo repartidor según tipo de entrega
document.getElementById('selectEntrega').addEventListener('change', function() {
    const grupoRepartidor = document.getElementById('grupoRepartidor');
    if (this.value === 'domicilio') {
        grupoRepartidor.style.display = 'block';
    } else {
        grupoRepartidor.style.display = 'none';
        // Limpiar selección de repartidor si se oculta
        document.getElementById('selectRepartidor').value = '';
    }
});

// AGREGAR PRODUCTO
document.getElementById('formAgregarProducto').addEventListener('submit', function(e) {
    e.preventDefault();

    const selectProd = document.getElementById('selectProducto');
    const selectedOption = selectProd.options[selectProd.selectedIndex];
    const productoNombre = selectedOption.text;
    const productoId = selectProd.value;
    const precio = parseFloat(selectedOption.dataset.precio) || 0;
    const unidad = document.getElementById('selectUnidad').value;
    const cantidad = parseFloat(document.getElementById('inputCantidad').value);

    if (!productoId || !unidad || !cantidad || isNaN(cantidad) || cantidad <= 0) {
        alert("Completa todos los campos (producto, unidad y cantidad válida).");
        return;
    }

    const tbody = document.getElementById('tbodyProductos');
    const total = cantidad * precio;

    const nuevaFila = document.createElement('tr');
    nuevaFila.setAttribute('data-id', productoId);

    nuevaFila.innerHTML = `
        <td style="font-weight:500;">${productoNombre}</td>
        <td>${unidad}</td>
        <td class="cantidad-valor">${cantidad}</td>
        <td>
            <input type="number" step="0.01" value="${precio.toFixed(2)}" class="input-precio-editable precio-unitario">
        </td>
        <td>
            <input type="number" step="0.01" value="${total.toFixed(2)}" class="input-subtotal-editable subtotal-editable">
        </td>
        <td class="total-texto total-fila">$${total.toFixed(2)}</td>
        <td class="accion-botones">
            <button type="button" class="btn-danger-icon" onclick="eliminarFila(this)"><i class="fas fa-trash-alt"></i></button>
        </td>
    `;

    tbody.appendChild(nuevaFila);
    document.getElementById('formAgregarProducto').reset();
    document.getElementById('hintUnidad').innerText = '';
    recalcularTotalesFilas();
});

function recalcularTotalesFilas() {
    document.querySelectorAll('#tbodyProductos tr').forEach(fila => {
        const cantidad = parseFloat(fila.querySelector('.cantidad-valor')?.innerText) || 0;
        const precioInput = fila.querySelector('.precio-unitario');
        const subtotalInput = fila.querySelector('.subtotal-editable');
        const totalCelda = fila.querySelector('.total-fila');

        if (precioInput && subtotalInput && totalCelda) {
            const precio = parseFloat(precioInput.value) || 0;
            let totalCalculado = cantidad * precio;
            subtotalInput.value = totalCalculado.toFixed(2);
            totalCelda.innerText = `$${totalCalculado.toFixed(2)}`;
        }
    });
}

document.addEventListener('input', function(e) {
    if (e.target.classList.contains('precio-unitario') || e.target.classList.contains('subtotal-editable')) {
        const fila = e.target.closest('tr');
        const cantidad = parseFloat(fila.querySelector('.cantidad-valor')?.innerText) || 0;
        const precio = parseFloat(fila.querySelector('.precio-unitario')?.value) || 0;
        let total = cantidad * precio;
        fila.querySelector('.subtotal-editable').value = total.toFixed(2);
        fila.querySelector('.total-fila').innerText = `$${total.toFixed(2)}`;
    }
});

function eliminarFila(boton) {
    boton.closest('tr').remove();
}

// hint unidad al seleccionar producto
document.getElementById('selectProducto').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const unidadDefault = selected.dataset.unidad;
    const hintDiv = document.getElementById('hintUnidad');
    if (unidadDefault && unidadDefault.trim() !== "") {
        hintDiv.innerText = `Unidad sugerida: ${unidadDefault}`;
    } else {
        hintDiv.innerText = "";
    }
});

// =====================
// ENVIAR PEDIDO (fetch)
// =====================
async function enviarPedido() {
    const filas = document.querySelectorAll('#tbodyProductos tr');
    if (filas.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Carrito vacío',
            text: 'Agrega al menos un producto antes de guardar el pedido.',
            confirmButtonColor: '#f16b1a'
        });
        return;
    }

    const idCliente = document.getElementById('selectCliente').value;
    if (!idCliente) {
        Swal.fire({
            icon: 'warning',
            title: 'Cliente no seleccionado',
            text: 'Selecciona un cliente para continuar.',
            confirmButtonColor: '#f16b1a'
        });
        return;
    }

    const tipoVenta = document.getElementById('selectTipoVenta').value;
    const tipoEntrega = document.getElementById('selectEntrega').value;
    let idRepartidor = null;
    
    if (tipoEntrega === 'domicilio') {
        idRepartidor = document.getElementById('selectRepartidor').value;
        if (!idRepartidor) {
            Swal.fire({
                icon: 'warning',
                title: 'Repartidor requerido',
                text: 'Para envío a domicilio, selecciona un repartidor.',
                confirmButtonColor: '#f16b1a'
            });
            return;
        }
    }

    const productos = [];
    let hayError = false;
    filas.forEach(fila => {
        const id_producto = fila.getAttribute('data-id');
        const unidad = fila.cells[1]?.innerText || "";
        const cantidad = parseFloat(fila.querySelector('.cantidad-valor')?.innerText) || 0;
        const precio = parseFloat(fila.querySelector('.precio-unitario')?.value) || 0;
        if (!id_producto || !unidad || cantidad <= 0) hayError = true;
        productos.push({
            id_producto: id_producto,
            unidad: unidad,
            cantidad: cantidad,
            precio_venta: precio
        });
    });

    if (hayError) {
        Swal.fire({
            icon: 'error',
            title: 'Error en productos',
            text: 'Algunos productos tienen datos incompletos.',
            confirmButtonColor: '#f16b1a'
        });
        return;
    }

    // Mostrar loading
    Swal.fire({
        title: 'Guardando pedido...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const datosEnvio = {
        id_cliente: idCliente,
        tipo_venta: tipoVenta,
        tipo_entrega: tipoEntrega,
        id_repartidor: idRepartidor,
        productos: productos
    };

    try {
        const respuesta = await fetch('<?= base_url("pedido/guardar_productos_pedido") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosEnvio)
        });
        const resultado = await respuesta.json();
        
        if (resultado.status === 'success') {
    const d = resultado.data;

    // Línea de stock: confirmado o sin stock
    const lineaStock = d.auto_confirmado
        ? `<p><i class="fas fa-check-circle" style="color:#1d4a27;"></i> <strong>Stock:</strong> <span style="color:#1d4a27; font-weight:700;">Confirmado automáticamente </span></p>`
        : `<p><i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i> <strong>Stock:</strong> <span style="color:#f59e0b;">Inventario insuficiente — pedido en espera </span></p>`;

    Swal.fire({
        icon: 'success',
        title: '¡Pedido guardado!',
        html: `
            <div style="text-align:left; background:#e8f3e6; border-radius:12px; padding:1rem; margin-top:1rem;">
                <p><i class="fas fa-receipt" style="color:#1d4a27;"></i> <strong>Folio:</strong> ${d.folio}</p>
                <p><i class="fas fa-user" style="color:#1d4a27;"></i> <strong>Cliente:</strong> ${d.cliente}</p>
                <p><i class="fas fa-tag" style="color:#1d4a27;"></i> <strong>Tipo de venta:</strong> ${d.tipo_venta === 'credito' ? 'Crédito' : 'Contado'}</p>
                <p><i class="fas fa-truck" style="color:#1d4a27;"></i> <strong>Entrega:</strong> ${d.tipo_entrega === 'domicilio' ? 'Domicilio' : 'En tienda'}</p>
                ${d.repartidor ? `<p><i class="fas fa-motorcycle" style="color:#1d4a27;"></i> <strong>Repartidor:</strong> ${d.repartidor}</p>` : ''}
                <p><i class="fas fa-dollar-sign" style="color:#1d4a27;"></i> <strong>Total:</strong> $${parseFloat(d.total).toFixed(2)}</p>
                ${lineaStock}
            </div>
        `,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#f16b1a',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) location.reload();
    });
}
    else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resultado.message,
                confirmButtonColor: '#f16b1a'
            });
        }
    } catch(error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo guardar el pedido. Revisa tu conexión.',
            confirmButtonColor: '#f16b1a'
        });
    }
}
// Búsqueda en tabla de pedidos (si existe en la página)
document.getElementById('btnBuscarPedido')?.addEventListener('click', function() {
    const busqueda = document.getElementById('inputBuscarPedido').value.toLowerCase();
    const filas = document.querySelectorAll('#tablaPedidosBody tr');
    filas.forEach(fila => {
        const texto = fila.innerText.toLowerCase();
        if (texto.includes(busqueda)) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
});
document.getElementById('inputBuscarPedido')?.addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('btnBuscarPedido')?.click();
    }
});
</script>
</body>
</html>