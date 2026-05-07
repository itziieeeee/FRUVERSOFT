<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Gestión de Clientes</title>
    <style>
        :root {
            --verde: #1d4a27;
            --naranja: #f16b1a;
            --naranja-claro: #fff5f0;
            --fondo: #f0f2f5;
            --blanco: #ffffff;
            --gris-borde: #eee;
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 30px rgba(0,0,0,0.2);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--fondo); }

        header {
            background: linear-gradient(90deg, var(--verde) 0%, #2a5e35 100%);
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo-area img { height: 45px; }

        nav.menu-navegacion {
            background: var(--blanco);
            padding: 10px 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
            border-bottom: 1px solid var(--gris-borde);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .nav-item {
            text-decoration: none;
            color: #444;
            padding: 10px 15px;
            border-radius: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
            font-size: 14px;
        }
        .nav-item:hover { color: var(--naranja); background: var(--naranja-claro); }
        .nav-item.activo { background: var(--naranja-claro); color: var(--naranja); border: 2px solid var(--naranja); }

        .container { padding: 30px; max-width: 1400px; margin: 0 auto; }

        .header-acciones {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .buscar-wrapper { position: relative; width: 400px; }
        .buscar-box {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 2px solid #ddd;
            border-radius: 12px;
            outline: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .buscar-box:focus { border-color: var(--naranja); }

        /* Dropdown sugerencias */
        .sugerencias {
            position: absolute;
            top: 110%;
            left: 0;
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            z-index: 999;
            display: none;
            max-height: 250px;
            overflow-y: auto;
        }
        .sugerencia-item {
            padding: 10px 15px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f0f0f0;
            transition: 0.2s;
        }
        .sugerencia-item:hover { background: var(--naranja-claro); }
        .sugerencia-item i { color: var(--naranja); }

        .grid-clientes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .cliente-card {
            background: var(--blanco);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
            border: 1px solid transparent;
            position: relative;
        }
        .cliente-card:hover { transform: translateY(-5px); border-color: var(--naranja); }

        .badge-tipo {
            position: absolute;
            top: 20px; right: 20px;
            font-size: 11px; font-weight: bold;
            padding: 5px 12px; border-radius: 20px;
            text-transform: uppercase;
        }
        .mayoreo { background: #e8f5e9; color: var(--verde); }
        .menudeo { background: #fff3e0; color: var(--naranja); }

        .card-info h3 { margin: 0 0 15px 0; color: var(--verde); font-size: 20px; }
        .card-info p { margin: 8px 0; color: #666; font-size: 14px; display: flex; align-items: center; gap: 10px; }

        .card-btns { display: flex; gap: 10px; margin-top: 20px; }
        .btn {
            flex: 1; padding: 10px; border-radius: 10px; border: none;
            cursor: pointer; font-weight: bold;
            display: flex; align-items: center; justify-content: center;
            gap: 5px; transition: 0.2s; font-size: 13px;
        }
        .btn-edit { background: #f0f2f5; color: #444; }
        .btn-edit:hover { background: #e0e0e0; }
        .btn-del { background: #fff0f0; color: #d9534f; }
        .btn-del:hover { background: #ffdada; }
        .btn-add {
            background: var(--naranja); color: white;
            text-decoration: none; padding: 12px 25px;
            border-radius: 10px; font-weight: bold;
            display: inline-flex; align-items: center; gap: 8px;
            transition: 0.2s;
        }
        .btn-add:hover { background: #d95e10; }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-box {
            background: white;
            border-radius: 20px;
            width: min(550px, 92vw);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        .modal-header {
            background: var(--verde);
            padding: 1.2rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 { color: white; margin: 0; display: flex; align-items: center; gap: 10px; }
        .modal-header h3 i { color: var(--naranja); }
        .modal-close {
            background: rgba(255,255,255,0.15); border: none; color: white;
            width: 32px; height: 32px; border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: 0.2s; font-size: 1rem;
        }
        .modal-close:hover { background: rgba(255,255,255,0.3); transform: rotate(90deg); }
        .modal-body { padding: 1.8rem; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 12px; color: #888; font-weight: bold; text-transform: uppercase; }
        .form-group input {
            padding: 10px 12px; border: 1.5px solid #ddd;
            border-radius: 10px; outline: none; font-size: 14px;
            transition: 0.2s;
        }
        .form-group input:focus { border-color: var(--naranja); box-shadow: 0 0 0 3px rgba(241,107,26,0.1); }
        .modal-footer {
            display: flex; gap: 10px; justify-content: flex-end;
            padding: 1rem 1.8rem 1.8rem;
        }
        .btn-cancel-modal {
            padding: 10px 20px; border-radius: 40px;
            background: white; border: 1.5px solid var(--verde);
            color: var(--verde); font-weight: 600; cursor: pointer;
        }
        .btn-save-modal {
            padding: 10px 25px; border-radius: 40px;
            background: var(--naranja); border: none;
            color: white; font-weight: 700; cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px;
            transition: 0.2s;
        }
        .btn-save-modal:hover { background: #d95e10; }

        /* MODAL CONFIRMAR */
        .modal-confirm-body {
            padding: 2rem;
            text-align: center;
        }
        .modal-confirm-body i { font-size: 3rem; color: var(--naranja); display: block; margin-bottom: 1rem; }
        .modal-confirm-body p { color: #333; margin-bottom: 0.4rem; }
        .modal-confirm-body .nombre-cliente { font-size: 1.1rem; font-weight: 700; color: #dc2626; }
        .modal-confirm-body small { color: #888; font-size: 0.8rem; }
        .confirm-btns { display: flex; gap: 10px; justify-content: center; margin-top: 1.5rem; }
        .btn-confirm-del {
            padding: 10px 25px; border-radius: 40px;
            background: #dc2626; border: none;
            color: white; font-weight: 700; cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px;
        }

        /* TOAST */
        #toast {
            position: fixed; bottom: 2rem; right: 2rem;
            padding: 1rem 1.5rem; border-radius: 12px;
            font-size: 0.9rem; font-weight: 600; color: white;
            z-index: 99999; display: none; align-items: center; gap: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            min-width: 260px;
        }
        @keyframes slideInToast {
            from { transform: translateX(100px); opacity: 0; }
            to   { transform: translateX(0);     opacity: 1; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo-area"><img src="<?= base_url('img/LOGO1.png') ?>"></div>
</header>

<nav class="menu-navegacion">
    <a href="<?= site_url('pantalla_ventas') ?>"      class="nav-item"><i class="fas fa-tag"></i> Ventas</a>
    <a href="<?= site_url('pantalla_pedidos') ?>"     class="nav-item"><i class="fas fa-truck"></i> Pedidos</a>
    <a href="<?= site_url('pantalla_inventario') ?>"  class="nav-item"><i class="fas fa-boxes"></i> Inventario</a>
    <a href="<?= site_url('pantalla_clientes') ?>"    class="nav-item activo"><i class="fas fa-users"></i> Clientes</a>
    <a href="<?= site_url('pantalla_repartidores') ?>" class="nav-item"><i class="fas fa-motorcycle"></i> Repartidores</a>
    <a href="<?= site_url('pantalla_productos') ?>"   class="nav-item"><i class="fas fa-apple-alt"></i> Productos</a>
</nav>

<div class="container">
    <div class="header-acciones">
        <h2 style="color:var(--verde); margin:0;">Directorio de Clientes</h2>
        <div style="display:flex; gap:15px; align-items:center;">
            <div class="buscar-wrapper">
                <input type="text" class="buscar-box" id="busc"
                    placeholder="Buscar cliente..."
                    onkeyup="filtrarYSugerir()"
                    autocomplete="off">
                <i class="fas fa-search" style="position:absolute; right:15px; top:14px; color:#ccc;"></i>
                <div class="sugerencias" id="sugerencias"></div>
            </div>
            <a href="<?= base_url('pantalla_rcliente') ?>" class="btn-add">
                <i class="fas fa-plus"></i> Nuevo
            </a>
        </div>
    </div>

    <div class="grid-clientes" id="grid">
        <?php foreach($lista_clientes as $c): ?>
            <div class="cliente-card"
                 data-nombre="<?= strtolower($c['nombre'] . ' ' . ($c['apellido_paterno'] ?? '') . ' ' . ($c['apellido_materno'] ?? '')) ?>">
                <span class="badge-tipo <?= $c['tipo_cliente'] ?>"><?= $c['tipo_cliente'] ?></span>
                <div class="card-info">
                    <h3><?= htmlspecialchars($c['nombre']) ?> <?= htmlspecialchars($c['apellido_paterno'] ?? '') ?></h3>
                    <p><i class="fas fa-phone-alt"></i> <?= $c['tel'] ?? 'N/A' ?></p>
                    <p><i class="fas fa-id-card"></i> <?= $c['rfc'] ?? 'S/N RFC' ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?= $c['estado'] ?? 'México' ?></p>
                </div>
                <div class="card-btns">
                    <button class="btn btn-edit" onclick="abrirEditar(<?= $c['id_cliente'] ?>)">
                        <i class="fas fa-pen"></i> Editar
                    </button>
                    <button class="btn btn-del" onclick="confirmarEliminar(<?= $c['id_cliente'] ?>, '<?= addslashes(htmlspecialchars($c['nombre'])) ?>')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- MODAL EDITAR -->
<div id="modalEdit" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Editar Cliente</h3>
            <button class="modal-close" onclick="cerrarEditar()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="form-cliente">
                <input type="hidden" name="id_cliente" id="det-id">
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nombre</label>
                        <input type="text" name="nombre" id="inp-nom" placeholder="Nombre">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Apellido Paterno</label>
                        <input type="text" name="ap" id="inp-ap" placeholder="Apellido Paterno">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Apellido Materno</label>
                        <input type="text" name="am" id="inp-am" placeholder="Apellido Materno">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Teléfono</label>
                        <input type="text" name="tel" id="inp-tel" placeholder="Teléfono">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> RFC</label>
                        <input type="text" name="rfc" id="inp-rfc" placeholder="RFC">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Estado</label>
                        <input type="text" name="estado" id="inp-est" placeholder="Estado">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="cerrarEditar()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button class="btn-save-modal" onclick="guardarCambios()">
                <i class="fas fa-save"></i> Guardar cambios
            </button>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR ELIMINAR -->
<div id="modalConfirmar" class="modal-overlay">
    <div class="modal-box" style="max-width:400px;">
        <div class="modal-header" style="background:#dc2626;">
            <h3><i class="fas fa-trash-alt"></i> Confirmar eliminación</h3>
            <button class="modal-close" onclick="cerrarConfirmar()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-confirm-body">
            <i class="fas fa-exclamation-triangle"></i>
            <p>¿Eliminar al cliente?</p>
            <p class="nombre-cliente" id="confirm-nombre"></p>
            <small>Esta acción no se puede deshacer.</small>
            <div class="confirm-btns">
                <button class="btn-cancel-modal" onclick="cerrarConfirmar()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button class="btn-confirm-del" id="btnConfirmarEliminar">
                    <i class="fas fa-trash-alt"></i> Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- TOAST -->
<div id="toast">
    <i id="toast-icon" class="fas fa-check-circle"></i>
    <span id="toast-msg"></span>
</div>

<script>
const BASE = '<?= base_url() ?>';

// ===================== TOAST =====================
function showToast(mensaje, tipo = 'success') {
    const toast = document.getElementById('toast');
    const msg   = document.getElementById('toast-msg');
    const icon  = document.getElementById('toast-icon');
    msg.textContent = mensaje;
    if (tipo === 'success') {
        toast.style.background = '#1d4a27';
        icon.className = 'fas fa-check-circle';
    } else if (tipo === 'error') {
        toast.style.background = '#dc2626';
        icon.className = 'fas fa-times-circle';
    } else {
        toast.style.background = '#f16b1a';
        icon.className = 'fas fa-exclamation-circle';
    }
    toast.style.animation = 'slideInToast 0.3s ease';
    toast.style.display = 'flex';
    setTimeout(() => { toast.style.display = 'none'; }, 3500);
}

// ===================== BUSCADOR EN TIEMPO REAL =====================
function filtrarYSugerir() {
    const texto     = document.getElementById('busc').value.toLowerCase().trim();
    const cards     = document.querySelectorAll('.cliente-card');
    const sugs      = document.getElementById('sugerencias');
    sugs.innerHTML  = '';

    if (texto.length === 0) {
        cards.forEach(c => c.style.display = '');
        sugs.style.display = 'none';
        return;
    }

    let coincidencias = 0;
    cards.forEach(card => {
        const nombre = card.dataset.nombre;
        if (nombre.includes(texto)) {
            card.style.display = '';
            coincidencias++;
            // Agregar sugerencia
            if (coincidencias <= 5) {
                const nombreMostrar = card.querySelector('h3').textContent;
                const item = document.createElement('div');
                item.className = 'sugerencia-item';
                item.innerHTML = `<i class="fas fa-user"></i> ${nombreMostrar}`;
                item.onclick = () => {
                    document.getElementById('busc').value = nombreMostrar;
                    sugs.style.display = 'none';
                    cards.forEach(c => c.style.display = 'none');
                    card.style.display = '';
                };
                sugs.appendChild(item);
            }
        } else {
            card.style.display = 'none';
        }
    });

    sugs.style.display = coincidencias > 0 ? 'block' : 'none';
}

// Cerrar sugerencias al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.buscar-wrapper')) {
        document.getElementById('sugerencias').style.display = 'none';
    }
});

// ===================== EDITAR =====================
function abrirEditar(id) {
    fetch(`${BASE}clientes/detalle/${id}`)
    .then(r => r.json())
    .then(data => {
        if (data.cliente) {
            const c = data.cliente;
            document.getElementById('det-id').value  = c.id_cliente;
            document.getElementById('inp-nom').value = c.nombre;
            document.getElementById('inp-ap').value  = c.apellido_paterno;
            document.getElementById('inp-am').value  = c.apellido_materno;
            document.getElementById('inp-tel').value = c.tel;
            document.getElementById('inp-rfc').value = c.rfc;
            document.getElementById('inp-est').value = c.estado ?? '';
            document.getElementById('modalEdit').style.display = 'flex';
        } else {
            showToast('No se encontraron datos del cliente', 'error');
        }
    })
    .catch(() => showToast('Error de conexión', 'error'));
}

function cerrarEditar() {
    document.getElementById('modalEdit').style.display = 'none';
}

function guardarCambios() {
    const formData = new FormData(document.getElementById('form-cliente'));
    fetch(`${BASE}clientes/actualizar`, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            cerrarEditar();
            showToast('Cliente actualizado correctamente', 'success');
            setTimeout(() => location.reload(), 1800);
        } else {
            showToast('Error: ' + res.msg, 'error');
        }
    })
    .catch(() => showToast('Error de conexión al guardar', 'error'));
}

// ===================== ELIMINAR =====================
function confirmarEliminar(id, nombre) {
    document.getElementById('confirm-nombre').textContent = nombre;
    document.getElementById('modalConfirmar').style.display = 'flex';
    document.getElementById('btnConfirmarEliminar').onclick = function() {
        cerrarConfirmar();
        fetch(`${BASE}clientes/eliminar/${id}`, { method: 'POST' })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                showToast('Cliente eliminado correctamente', 'success');
                setTimeout(() => location.reload(), 1800);
            } else {
                showToast('Error: ' + res.msg, 'error');
            }
        })
        .catch(() => showToast('Error de conexión al eliminar', 'error'));
    };
}

function cerrarConfirmar() {
    document.getElementById('modalConfirmar').style.display = 'none';
}

// Cerrar modales al hacer clic fuera
document.getElementById('modalEdit').addEventListener('click', function(e) {
    if (e.target === this) cerrarEditar();
});
document.getElementById('modalConfirmar').addEventListener('click', function(e) {
    if (e.target === this) cerrarConfirmar();
});
</script>
</body>
</html>