<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>FRUVER · Control de Pedidos</title>
    <!-- Fuentes e iconos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/ventasestilo.css') ?>">
    
</head>
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
    <!-- SECCIÓN 1: Nueva venta / pedido -->
    <div class="seccion">
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

        <h3><i class="fa-solid fa-apple-alt"></i> Registrar Productos</h3>
        <form class="form-producto" id="formAgregarProducto">
            <div class="form-group">
                <label>Producto</label>
                <select id="selectProducto" name="id_producto" required>
                    <option value="">Seleccione producto...</option>
                    <?php foreach($productos as $prod): ?>
                        <option value="<?= $prod['id']; ?>" data-precio="<?= $prod['precio_sugerido']; ?>" data-unidad="<?= $prod['unidad_compra'] ?? ''; ?>">
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

        <div class="tabla-container">
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

    <!-- SECCIÓN 2: Gestión de estado del pedido -->
    <div class="seccion">
        <h2><i class="fa-solid fa-truck-fast"></i> Gestión del Pedido</h2>
        <form class="form-estado" id="formEstado">
          <div class="fila-flex">

    <div class="form-group">
        <label>Tipo de venta:</label>
        <select id="selectTipoVenta" name="tipo_venta">
            <option value="contado">Contado</option>
            <option value="credito">Crédito</option>
        </select>
    </div>

    <form class="form-estado" id="formEstado">
        <div class="form-group">
            <label>Cambiar estado a:</label>
            <select id="selectEstadoGlobal" name="estado_actual" required>
                <option value="Pedido">Pedido - Solicitado</option>
                <option value="Pedido confirmado">Pedido confirmado</option>
                <option value="Pedido en transito">Pedido en tránsito</option>
                <option value="Venta confirmada">Venta confirmada</option>
                <option value="Pedido pagado">Pedido pagado</option>
                <option value="Pedido cancelado">Pedido cancelado</option>
            </select>
        </div>
        <button type="submit" class="btn-primary"><i class="fas fa-sync-alt"></i> Actualizar Pedido</button>
    </form>

</div>
            
        </form>
    </div>
</div>

<script>



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
        alert("Agrega al menos un producto antes de guardar el pedido.");
        return;
    }
    const productos = [];
    let hayError = false;
    filas.forEach(fila => {
        const id_producto = fila.getAttribute('data-id');
        const unidad = fila.cells[1]?.innerText || "";
        const cantidad = parseFloat(fila.querySelector('.cantidad-valor')?.innerText) || 0;
        const precio = parseFloat(fila.querySelector('.precio-unitario')?.value) || 0;
        const subtotal = parseFloat(fila.querySelector('.subtotal-editable')?.value) || 0;
        const total = cantidad * precio;
        if (!id_producto || !unidad || cantidad <= 0) hayError = true;
        productos.push({
            id_producto: id_producto,
            unidad: unidad,
            cantidad: cantidad,
            precio_venta: precio,
            subtotal: subtotal,
            total: total
        });
    });
    if (hayError) {
        alert("Error: algunos productos tienen datos incompletos.");
        return;
    }
    const datosEnvio = {
        id_pedido: 1,
        tipo_venta: document.getElementById('selectTipoVenta').value,
        productos: productos
    };
    try {
        const respuesta = await fetch('<?= base_url("pedido/guardar_productos_pedido") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosEnvio)
        });
        const resultado = await respuesta.json();
        alert(resultado.message);
        if(resultado.status === 'success'){
            location.reload();
        }
    } catch(error) {
        console.error(error);
        alert("Error al guardar el pedido. Revisa conexión.");
    }
}

// Búsqueda en tabla de pedidos
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