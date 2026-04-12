<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVER · Gestión de Repartidores</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        
        :root {
            --primary-green: #1d4a27;
            --primary-orange: #f16b1a;
            --light-green: #e8f3e6;
            --gray-border: #e0e0e0;
            --text-dark: #333;
            --white: #ffffff;
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --radius-md: 12px;
        }

        body { font-family: 'Inter', sans-serif; background: #f5f7fa; margin: 0; }

        
        .barra-superior { display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 2rem; background: linear-gradient(90deg, var(--primary-green) 0%, #2a5e35 100%); color: white; }
        .logo-area img { height: 60px; }
        .btn-user { color: white; text-decoration: none; padding: 0.5rem 1rem; background: rgba(255,255,255,0.15); border-radius: 50px; font-size: 0.8rem; }

        .menu-navegacion { background: white; padding: 0.5rem; display: flex; justify-content: center; border-bottom: 1px solid var(--gray-border); gap: 10px; }
        .nav-link { padding: 0.7rem 1rem; color: var(--text-dark); text-decoration: none; font-weight: 600; font-size: 0.85rem; border-radius: 40px; }
        .nav-link.activo { color: var(--primary-orange); background: rgba(241, 107, 26, 0.08); }

        /* esto es para el contenido */
        .main-container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        
        .header-acciones {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
        }

        .titulo-seccion { display: flex; align-items: center; gap: 12px; font-size: 1.4rem; color: var(--primary-green); font-weight: 700; }
        
        /* boton de registrar */
        .btn-registrar {
            background: var(--primary-orange);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        .btn-registrar:hover { transform: translateY(-2px); filter: brightness(1.1); }

        /* esta es la tabla*/
        .tabla-card { background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-md); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        thead { background: var(--primary-green); color: white; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--gray-border); }
        tbody tr:hover { background-color: var(--light-green); }

        /* la ventana modal del registro*/
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center; justify-content: center;
        }
        .modal-content {
            background: white; padding: 2rem; border-radius: var(--radius-md);
            width: 90%; max-width: 500px; position: relative;
        }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .form-group { margin-bottom: 1rem; display: flex; flex-direction: column; }
        .form-group.full { grid-column: span 2; }
        label { font-size: 0.8rem; font-weight: 600; margin-bottom: 4px; }
        input { padding: 0.6rem; border: 1px solid var(--gray-border); border-radius: 6px; }
        {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #eef3e9;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #1a2e1f;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .barra-superior {
            background: #1d4a27;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            box-shadow: 0 4px 12px rgba(0,30,0,0.2);
            flex-shrink: 0;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo-area img {
            width: 110px;
            filter: brightness(1.1);
        }

        .buscador {
            background: white;
            border-radius: 40px;
            padding: 3px 3px 3px 18px;
            display: flex;
            align-items: center;
            flex: 0 1 300px;
            max-width: 350px;
        }
        .buscador input {
            border: none;
            padding: 8px 0;
            width: 100%;
            outline: none;
            font-size: 0.9rem;
        }
        .buscador button {
            background: #f16b1a;
            border: none;
            border-radius: 40px;
            width: 38px;
            height: 38px;
            color: white;
            cursor: pointer;
            flex-shrink: 0;
        }

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
            transition: 0.2s;
            white-space: nowrap;
        }
        .btn-user:hover {
            background: white;
            color: #1d4a27;
        }

        /* --- este es el menu de navegacion --- */
        .menu-navegacion {
            background-color: #ffffff;
            padding: 0 24px;
            border-bottom: 1px solid #dde8d8;
            flex-shrink: 0;
        }

        .nav-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        .nav-link {
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            border-radius: 0;
            padding: 14px 24px;
            text-decoration: none;
            font-weight: 600;
            color: #3a3a3a;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: all 0.2s ease;
            box-shadow: none;
        }

        .nav-link i {
            color: #2d7a3a;
            font-size: 1rem;
        }

        .nav-link:hover {
            color: #1d4a27;
            border-bottom: 3px solid #f16b1a;
            transform: none;
            background: none;
            box-shadow: none;
        }

        .nav-link.activo {
            color: #1d4a27;
            background: none;
            border-bottom: 3px solid #f16b1a;
            font-weight: 700;
        }

        .nav-link.activo i {
            color: #2d7a3a;
        }

        .tarjeta {
            display: grid;
            grid-template-columns: 1.2fr 1.3fr 1.3fr;
            gap: 36px;
            padding: 0 14px 10px 20px;
            height: calc(90vh - 140px);
            overflow: hidden;
            margin-top: 20px;
        }

        .card {
            background: white;
            border-radius: 28px;
            padding: 18px 16px;
            box-shadow: 0 8px 22px rgba(40, 70, 40, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(120, 160, 120, 0.2);
        }

        .cabezacard {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            flex-shrink: 0;
        }
        .cabezacard i {
            font-size: 1.6rem;
            color: #f16b1a;
            background: #fff1e0;
            padding: 8px;
            border-radius: 16px;
        }
        .cabezacard h2 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1d4a27;
        }
        .cabezacard .fondo {
            background: #d1e6cf;
            margin-left: auto;
            padding: 5px 12px;
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #1a4a1a;
        }

        .scroll-area {
            overflow-y: auto;
            padding-right: 6px;
            flex: 1;
            min-height: 0;
        }

        .minit {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .minit th {
            text-align: left;
            padding: 8px 4px 4px 4px;
            color: #2d6e3b;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #cde0ca;
        }
        .minit td {
            padding: 10px 4px;
            border-bottom: 1px solid #e2eedf;
        }
        .minit tr:last-child td {
            border-bottom: none;
        }

        .scroll-area::-webkit-scrollbar {
            width: 6px;
        }
        .scroll-area::-webkit-scrollbar-thumb {
            background: #b8d4b0;
            border-radius: 10px;
        }

        @media (max-width: 1100px) {
            .tarjeta {
                grid-template-columns: 1fr;
                height: auto;
                overflow: auto;
            }
            body { overflow: auto; height: auto; }
        }
    </style>
</head>
<body>

<div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo" width="140">
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
            <a href="<?=base_url('pantalla_inventario')?>" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
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
                        <td><?= $r['nombre'] . ' ' . $r['ap_p'] . ' ' . $r['ap_m'] ?></td>
                        <td><?= $r['tel'] ?></td>
                        <td><?= $r['direccion'] ?? 'N/A' ?></td>
                        <td><small><?= $r['notas'] ?? '-' ?></small></td>
                        <td>
                       <button 
        style="color:var(--primary-green); border:none; background:none; cursor:pointer;"
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
    <button 
        style="color:#c0392b; border:none; background:none; cursor:pointer;"
        onclick="eliminarRepartidor(<?= $r['id'] ?>, '<?= $r['nombre'] ?>')">
        <i class="fas fa-trash"></i>
    </button>
</td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:2rem;">No hay repartidores registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modalRepartidor" class="modal">
    <div class="modal-content">
        <h3 style="margin-top:0; color:var(--primary-green)">Registrar Repartidor</h3>
        <form id="formRepartidor">
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
            </div>
            <div style="display:flex; gap:10px; margin-top:1rem;">
                <button type="submit" class="btn-registrar" style="flex:1; justify-content:center;">Guardar</button>
                <button type="button" onclick="cerrarModal()" style="flex:1; background:#ccc; border:none; border-radius:50px; cursor:pointer;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditar" class="modal">
    <div class="modal-content">
        <h3 style="margin-top:0; color:var(--primary-green)">Editar Repartidor</h3>
        <form id="formEditar">
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
            </div>
            <div style="display:flex; gap:10px; margin-top:1rem;">
                <button type="submit" class="btn-registrar" style="flex:1; justify-content:center;">
                    Guardar cambios
                </button>
                <button type="button" onclick="cerrarEditar()" 
                    style="flex:1; background:#ccc; border:none; border-radius:50px; cursor:pointer;">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>



<script>
    const modal = document.getElementById('modalRepartidor');

    function abrirModal() { modal.style.display = 'flex'; }
    function cerrarModal() { modal.style.display = 'none'; }

    // aqui enviamos los datos
    document.getElementById('formRepartidor').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch("<?= base_url('FRUVER/guardarrepartidor') ?>", {
            method: "POST",
            body: formData
        })
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

function cerrarEditar() {
    modalEditar.style.display = 'none';
}

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

    fetch(`<?= base_url('FRUVER/editarrepartidor') ?>/${id}`, {
        method: 'POST',
        body: formData
    })
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

    fetch(`<?= base_url('FRUVER/eliminarrepartidor') ?>/${id}`, {
        method: 'POST',
        body: formData
    })
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
</script>

</body>
</html>