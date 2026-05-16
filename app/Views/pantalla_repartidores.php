<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVER · Gestión de Repartidores</title>
       <link rel="stylesheet" href="<?= base_url('css/repartidores.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>

<header>
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo">
        </div>
        <div class="user-actions">
            <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> Admin</a>
            <a href="#" class="btn-user"><i class="fas fa-bell"></i> Notificaciones</a>
            <a href="<?= base_url('menusolo') ?>" class="btn-user"><i class="fas fa-sign-out-alt"></i> Regresar</a>
        </div>
    </div>
</header>
<div id="zona-alertas" style="position:fixed;top:70px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;"></div>
<nav class="menu-navegacion">
    <div class="nav-links">
        <a href="pantalla_ventas" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="pantalla_pedidos" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="<?= base_url('pantalla_inventario') ?>" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="pantalla_clientes" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="pantalla_repartidores" class="nav-link activo"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="pantalla_productos" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </div>
</nav>

<div class="main-container">
    <div class="header-acciones">
        <div class="titulo-seccion">
            <i class="fa-solid fa-dolly"></i>
            <span>Gestión de Repartidores</span>
        </div>
        <button class="btn-registrar" onclick="abrirModal()">
            <i class="fas fa-plus"></i> Nuevo Repartidor
        </button>
    </div>

    <div class="tabla-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Foto</th>
                    <th>Nombre Completo</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Notas</th>
                    <th>Estado</th>
                    <th>Pedidos</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
    <tbody>
    <?php if(!empty($repartidores)): ?>
        <?php foreach($repartidores as $r): ?>
        <?php
            $pedidos  = $pedidosPorRepartidor[$r['id']] ?? [];
            $asignado = count($pedidos) > 0;
        ?>
        <tr>
            <td><strong>#<?= $r['id'] ?></strong></td>
            <td>
                <?php if(!empty($r['foto'])): ?>
                    <img src="<?= base_url('uploads/repartidores/' . $r['foto']) ?>"
                         alt="foto"
                         style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid #e0e0e0;">
                <?php else: ?>
                    <div style="width:42px;height:42px;border-radius:50%;background:#d1e6cf;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;color:#1d4a27;">
                        <?= strtoupper(substr($r['nombre'],0,1) . substr($r['ap_p'],0,1)) ?>
                    </div>
                <?php endif; ?>
            </td>
            <td><?= $r['nombre'] . ' ' . $r['ap_p'] . ' ' . $r['ap_m'] ?></td>
            <td><?= $r['tel'] ?></td>
            <td><?= $r['direccion'] ?? 'N/A' ?></td>
            <td><small><?= $r['notas'] ?? '-' ?></small></td>

            <!-- ESTADO -->
            <td>
                <?php if($asignado): ?>
                    <span style="background:#d4edda;color:#155724;padding:4px 12px;
                                 border-radius:20px;font-size:0.78rem;font-weight:600;">
                        ● Asignado
                    </span>
                <?php else: ?>
                    <span style="background:#f8d7da;color:#721c24;padding:4px 12px;
                                 border-radius:20px;font-size:0.78rem;font-weight:600;">
                        ● Libre
                    </span>
                <?php endif; ?>
            </td>

            <!-- PEDIDOS -->
            <td>
                <?php if($asignado): ?>
                    <button onclick="verPedidos(<?= $r['id'] ?>)"
                        style="background:none;border:1px solid #1d4a27;color:#1d4a27;
                               border-radius:20px;padding:4px 12px;cursor:pointer;font-size:0.8rem;">
                        <i class="fas fa-box"></i> <?= count($pedidos) ?> pedido(s)
                    </button>
                <?php else: ?>
                    <span style="color:#bbb;font-size:0.85rem;">Sin pedidos</span>
                <?php endif; ?>
            </td>

            <!-- EDITAR -->
            <td>
                <button style="color:var(--primary-green);border:none;background:none;cursor:pointer;"
                    onclick="abrirEditar(
                        <?= $r['id'] ?>,
                        '<?= $r['nombre'] ?>',
                        '<?= $r['ap_p'] ?>',
                        '<?= $r['ap_m'] ?>',
                        '<?= $r['tel'] ?>',
                        '<?= $r['direccion'] ?? '' ?>',
                        '<?= $r['notas'] ?? '' ?>'
                    )">
                    <i class="fas fa-edit"></i>
                </button>
            </td>

            <!-- ELIMINAR -->
            <td>
                <button style="color:#c0392b;border:none;background:none;cursor:pointer;"
                    onclick="eliminarRepartidor(<?= $r['id'] ?>, '<?= $r['nombre'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="10" style="text-align:center;padding:2rem;">No hay repartidores registrados.</td></tr>
    <?php endif; ?>
</tbody>
        </table>
    </div>
</div>

<!-- Modal: Nuevo Repartidor -->
<div id="modalRepartidor" class="modal">
    <div class="modal-content">
        <h3 style="margin-top:0; color:var(--primary-green)">Registrar Repartidor</h3>
        <form id="formRepartidor" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="tel" required>
                </div>
                <div class="form-group">
                    <label>Apellido Paterno</label>
                    <input type="text" name="ap_p" required>
                </div>
                <div class="form-group">
                    <label>Apellido Materno</label>
                    <input type="text" name="ap_m">
                </div>
                <div class="form-group full">
                    <label>Dirección</label>
                    <input type="text" name="direccion">
                </div>
                <div class="form-group full">
                    <label>Notas</label>
                    <input type="text" name="notas">
                </div>
                <div class="form-group full">
                    <label>Foto</label>
                    <input type="file" name="foto" accept="image/*" onchange="previsualizarFoto(this, 'preview-nuevo')">
                    <img id="preview-nuevo" src="" alt=""
                         style="display:none; margin-top:8px; width:64px; height:64px; border-radius:50%; object-fit:cover; border:2px solid #e0e0e0;">
                </div>
            </div>
            <div style="display:flex; gap:10px; margin-top:1rem;">
                <button type="submit" class="btn-registrar" style="flex:1; justify-content:center;">Guardar</button>
                <button type="button" onclick="cerrarModal()" style="flex:1; background:#ccc; border:none; border-radius:50px; cursor:pointer;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar Repartidor -->
<div id="modalEditar" class="modal">
    <div class="modal-content">
        <h3 style="margin-top:0; color:var(--primary-green)">Editar Repartidor</h3>
        <form id="formEditar" enctype="multipart/form-data">
            <input type="hidden" id="edit-id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" id="edit-nombre" required>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" id="edit-tel" required>
                </div>
                <div class="form-group">
                    <label>Apellido Paterno</label>
                    <input type="text" id="edit-ap_p" required>
                </div>
                <div class="form-group">
                    <label>Apellido Materno</label>
                    <input type="text" id="edit-ap_m">
                </div>
                <div class="form-group full">
                    <label>Dirección</label>
                    <input type="text" id="edit-direccion">
                </div>
                <div class="form-group full">
                    <label>Notas</label>
                    <input type="text" id="edit-notas">
                </div>
                <div class="form-group full">
                    <label>Foto (dejar vacío para no cambiar)</label>
                    <input type="file" id="edit-foto" name="foto" accept="image/*" onchange="previsualizarFoto(this, 'preview-editar')">
                    <img id="preview-editar" src="" alt=""
                         style="display:none; margin-top:8px; width:64px; height:64px; border-radius:50%; object-fit:cover; border:2px solid #e0e0e0;">
                </div>
            </div>
            <div style="display:flex; gap:10px; margin-top:1rem;">
                <button type="submit" class="btn-registrar" style="flex:1; justify-content:center;">Guardar cambios</button>
                <button type="button" onclick="cerrarEditar()" style="flex:1; background:#ccc; border:none; border-radius:50px; cursor:pointer;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function mostrarAlerta(mensaje, tipo = 'success') {
    const zona = document.getElementById('zona-alertas');
    const div = document.createElement('div');
    div.className = 'alert-msg alert-' + tipo;
    div.innerHTML = `<i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${mensaje}`;
    zona.appendChild(div);
    setTimeout(() => div.remove(), 3500);
}

    const modal = document.getElementById('modalRepartidor');

    function abrirModal() { modal.style.display = 'flex'; }
    function cerrarModal() { modal.style.display = 'none'; }

    document.getElementById('formRepartidor').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch("<?= base_url('FRUVER/guardarrepartidor') ?>", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    cerrarModal();
                    mostrarAlerta('¡Repartidor guardado con éxito!', 'success');
                    setTimeout(() => window.location.href = "<?= base_url('pantalla_repartidores') ?>", 1500);
                } else {
                    mostrarAlerta('Error al guardar el repartidor.', 'error');
                }
            });
    });

    const modalEditar = document.getElementById('modalEditar');

    function abrirEditar(id, nombre, ap_p, ap_m, tel, direccion, notas) {
        document.getElementById('edit-id').value        = id;
        document.getElementById('edit-nombre').value    = nombre;
        document.getElementById('edit-ap_p').value      = ap_p;
        document.getElementById('edit-ap_m').value      = ap_m;
        document.getElementById('edit-tel').value       = tel;
        document.getElementById('edit-direccion').value = direccion;
        document.getElementById('edit-notas').value     = notas;
        modalEditar.style.display = 'flex';
    }

    function cerrarEditar() { modalEditar.style.display = 'none'; }

    document.getElementById('formEditar').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        const formData = new FormData();
        formData.append('nombre',    document.getElementById('edit-nombre').value);
        formData.append('ap_p',      document.getElementById('edit-ap_p').value);
        formData.append('ap_m',      document.getElementById('edit-ap_m').value);
        formData.append('tel',       document.getElementById('edit-tel').value);
        formData.append('direccion', document.getElementById('edit-direccion').value);
        formData.append('notas',     document.getElementById('edit-notas').value);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        const fotoInput = document.getElementById('edit-foto');
        if (fotoInput.files[0]) formData.append('foto', fotoInput.files[0]);

        fetch(`<?= base_url('FRUVER/editarrepartidor') ?>/${id}`, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cerrarEditar();
                    mostrarAlerta('¡Repartidor actualizado con éxito!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    mostrarAlerta('Error al actualizar el repartidor.', 'error');
                }
            });
    });

    function eliminarRepartidor(id, nombre) {
        const pedidos = pedidosPorRepartidor[id] || [];
        
        if (pedidos.length > 0) {
            mostrarAlerta(`No puedes eliminar a ${nombre}: tiene ${pedidos.length} pedido(s) asignado(s). Primero reasígnalos.`, 'error');
            return;
        }

        if (!confirm(`¿Seguro que deseas eliminar a ${nombre}?`)) return;

        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        fetch(`<?= base_url('FRUVER/eliminarrepartidor') ?>/${id}`, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Repartidor eliminado correctamente.', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    mostrarAlerta('Error al eliminar el repartidor.', 'error');
                }
            });
    }

    function previsualizarFoto(input, previewId) {
        const preview = document.getElementById(previewId);
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }
</script>
<!-- Modal Pedidos -->
<div id="modalPedidos" class="modal">
    <div class="modal-content" style="max-width:520px;">
        <h3 style="margin-top:0;color:var(--primary-green)">
            <i class="fas fa-box"></i> Pedidos del Repartidor
        </h3>
        <div id="listaPedidos"></div>
        <button onclick="document.getElementById('modalPedidos').style.display='none'"
            style="margin-top:1rem;width:100%;background:#ccc;border:none;
                   border-radius:50px;padding:10px;cursor:pointer;">
            Cerrar
        </button>
    </div>
</div>

<script>
const pedidosPorRepartidor = <?= json_encode($pedidosPorRepartidor) ?>;

function verPedidos(idRepartidor) {
    const pedidos = pedidosPorRepartidor[idRepartidor] || [];
    let html = `<table style="width:100%;border-collapse:collapse;font-size:0.87rem;">
        <thead>
            <tr style="background:#f0f8f0;">
                <th style="padding:8px;text-align:left;">#Pedido</th>
                <th style="padding:8px;text-align:left;">Cliente</th>
                <th style="padding:8px;text-align:left;">Dirección</th>
                <th style="padding:8px;text-align:left;">Total</th>
                <th style="padding:8px;text-align:left;">Estado</th>
            </tr>
        </thead><tbody>`;

    pedidos.forEach(p => {
        const colores = {
            'tránsito': '#e67e22',
            'pagado':   '#27ae60',
            'crédito':  '#2980b9',
        };
        let color = '#555';
        for (const [clave, val] of Object.entries(colores)) {
            if (p.estado_actual?.toLowerCase().includes(clave)) { color = val; break; }
        }

        // Armar dirección completa
        const direccion = (p.calle && p.numero)
            ? `${p.calle} #${p.numero}, Col. ${p.colonia ?? ''}, ${p.municipio ?? ''}`
            : '<span style="color:#bbb;">Sin dirección</span>';

        const cliente = (p.cliente_nombre)
            ? `${p.cliente_nombre} ${p.cliente_ap ?? ''}`
            : '<span style="color:#bbb;">Sin cliente</span>';

        html += `<tr style="border-bottom:1px solid #eee;">
            <td style="padding:8px;"><strong>#${p.id}</strong></td>
            <td style="padding:8px;">${cliente}</td>
            <td style="padding:8px;font-size:0.82rem;color:#444;">${direccion}</td>
            <td style="padding:8px;">$${parseFloat(p.total).toFixed(2)}</td>
            <td style="padding:8px;color:${color};font-weight:600;">${p.estado_actual ?? '-'}</td>
        </tr>`;
    });

    html += '</tbody></table>';
    document.getElementById('listaPedidos').innerHTML = html;
    document.getElementById('modalPedidos').style.display = 'flex';
}
</script>
</body>
</html>