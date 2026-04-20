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
                    <th>Editar</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($repartidores)): ?>
                    <?php foreach($repartidores as $r): ?>
                    <tr>
                        <td><strong>#<?= $r['id'] ?></strong></td>
                        <td>
                            <?php if(!empty($r['foto'])): ?>
                                <img src="<?= base_url('uploads/repartidores/' . $r['foto']) ?>"
                                     alt="foto"
                                     style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid #e0e0e0;">
                            <?php else: ?>
                                <div style="width:42px; height:42px; border-radius:50%; background:#d1e6cf; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.85rem; color:#1d4a27;">
                                    <?= strtoupper(substr($r['nombre'],0,1) . substr($r['ap_p'],0,1)) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?= $r['nombre'] . ' ' . $r['ap_p'] . ' ' . $r['ap_m'] ?></td>
                        <td><?= $r['tel'] ?></td>
                        <td><?= $r['direccion'] ?? 'N/A' ?></td>
                        <td><small><?= $r['notas'] ?? '-' ?></small></td>
                        <td>
                            <button style="color:var(--primary-green); border:none; background:none; cursor:pointer;"
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
                        <td>
                            <button style="color:#c0392b; border:none; background:none; cursor:pointer;"
                                onclick="eliminarRepartidor(<?= $r['id'] ?>, '<?= $r['nombre'] ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center; padding:2rem;">No hay repartidores registrados.</td></tr>
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
                    alert("¡Repartidor guardado con éxito!");
                    window.location.href = "<?= base_url('pantalla_repartidores') ?>";
                } else {
                    alert("Error al guardar");
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
                    alert('¡Repartidor actualizado con éxito!');
                    window.location.reload();
                } else {
                    alert('Error al actualizar.');
                }
            });
    });

    function eliminarRepartidor(id, nombre) {
        if (!confirm(`¿Seguro que deseas eliminar a ${nombre}?`)) return;
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        fetch(`<?= base_url('FRUVER/eliminarrepartidor') ?>/${id}`, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Repartidor eliminado.');
                    window.location.reload();
                } else {
                    alert('Error al eliminar.');
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

</body>
</html>