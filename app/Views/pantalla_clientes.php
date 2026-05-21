<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    
    <link rel="stylesheet" href="<?= base_url('css/clienteestilo.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Gestión de Clientes</title>
</head>
<body>

<header class="barra-superior">
    <div class="logo-area">
        <img src="<?= base_url('img/LOGO1.png') ?>" alt="Fruver Logo">
    </div>
    <div class="buscador-header-wrap">
            <input type="text" id="busc"placeholder="Buscar cliente por nombre..." autocomplete="off">
        </div>
    
        <div class="sugerencias" id="sugerencias"></div>
    </div>
    <div class="user-actions-wrap">
        <a href="#" class="btn-user-header"><i class="fas fa-user-shield"></i> Admin</a>
        <a href="<?= site_url('menusolo') ?>" class="btn-user-header"><i class="fas fa-sign-out-alt"></i> Regresar</a>
    </div>
</header>

 <nav class="menu-navegacion">
        <a href="pantalla_ventas" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="pantalla_pedidos" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="inventario" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="pantalla_clientes" class="nav-link activo"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="pantalla_repartidores" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="pantalla_productos" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </nav>

<main class="main-container">
    <div class="header-acciones">
        <h2><i class="fas fa-users" style="color:#f16b1a;"></i> Directorio de Clientes</h2>
        <!-- espacio visual -->
    </div>
    <div class="grid-clientes" id="grid">
        <?php foreach($lista_clientes as $c): ?>
            <div class="cliente-card"
                 data-nombre="<?= strtolower($c['nombre'] . ' ' . ($c['apellido_paterno'] ?? '') . ' ' . ($c['apellido_materno'] ?? '')) ?>"
                 data-id="<?= $c['id_cliente'] ?>">
                <span class="badge-tipo <?= $c['tipo_cliente'] ?>"><?= $c['tipo_cliente'] ?></span>
                <div class="card-info">
                    <h3><?= htmlspecialchars($c['nombre']) ?> <?= htmlspecialchars($c['apellido_paterno'] ?? '') ?></h3>
                    <p><i class="fas fa-phone-alt"></i> <?= $c['tel'] ?? '—' ?></p>
                    <p><i class="fas fa-id-card"></i> <?= $c['rfc'] ?? 'S/N RFC' ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?= $c['estado'] ?? 'No registrado' ?></p>
                </div>
                <div class="card-btns">
                    <button class="btn btn-edit" onclick="abrirEditar(<?= $c['id_cliente'] ?>)">
                        <i class="fas fa-pen"></i> Editar
                    </button>
                    <button class="btn btn-del" onclick="confirmarEliminar(<?= $c['id_cliente'] ?>, '<?= addslashes(htmlspecialchars($c['nombre'])) ?>')">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- MODAL EDITAR CLIENTE (Premium) -->
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
                    <div class="form-group"><label>Nombre</label><input type="text" name="nombre" id="inp-nom" placeholder="Nombre"></div>
                    <div class="form-group"><label>Apellido Paterno</label><input type="text" name="ap" id="inp-ap" placeholder="Apellido Paterno"></div>
                    <div class="form-group"><label>Apellido Materno</label><input type="text" name="am" id="inp-am" placeholder="Apellido Materno"></div>
                    <div class="form-group"><label>Teléfono</label><input type="text" name="tel" id="inp-tel" placeholder="Teléfono"></div>
                    <div class="form-group"><label>RFC</label><input type="text" name="rfc" id="inp-rfc" placeholder="RFC"></div>
                    <div class="form-group"><label>Estado</label><input type="text" name="estado" id="inp-est" placeholder="Estado"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="cerrarEditar()"><i class="fas fa-times"></i> Cancelar</button>
            <button class="btn-save-modal" onclick="guardarCambios()"><i class="fas fa-save"></i> Guardar cambios</button>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR ELIMINACIÓN -->
<div id="modalConfirmar" class="modal-overlay">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header">
            <h3><i class="fas fa-trash-alt"></i> Eliminar cliente</h3>
            <button class="modal-close" onclick="cerrarConfirmar()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-confirm-body">
            <div class="icono-alerta"><i class="fas fa-exclamation-triangle"></i></div>
            <p>¿Estás seguro de eliminar a</p>
            <p class="nombre-cliente" id="confirm-nombre"></p>
            <small>Esta acción es irreversible.</small>
            <div class="confirm-btns" style="display:flex; gap:12px; justify-content:center; margin-top:1.5rem;">
                <button class="btn-confirm-del" id="btnConfirmarEliminar" style="background:#dc2626; border:none; padding:10px 25px; border-radius:50px; color:white; font-weight:bold;"><i class="fas fa-trash-alt"></i> Sí, eliminar</button>
            <button class="btn-cancel-modal" onclick="cerrarConfirmar()">Cancelar</button>
                
            </div>
        </div>
    </div>
</div>

<div id="toast"><i id="toast-icon" class="fas fa-check-circle"></i> <span id="toast-msg"></span></div>

<script>
const BASE = '<?= base_url() ?>';

function showToast(msg, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toast-icon');
    const span = document.getElementById('toast-msg');
    span.innerText = msg;
    if (type === 'success') {
        toast.style.background = '#1d4a27';
        icon.className = 'fas fa-check-circle';
    } else if (type === 'error') {
        toast.style.background = '#c13b1b';
        icon.className = 'fas fa-times-circle';
    } else {
        toast.style.background = '#f16b1a';
        icon.className = 'fas fa-exclamation-circle';
    }
    toast.style.display = 'flex';
    setTimeout(() => { toast.style.display = 'none'; }, 3300);
}

// BUSCADOR Y SUGERENCIAS
function filtrarYSugerir() {
    const texto = document.getElementById('busc').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.cliente-card');
    const sugerenciasDiv = document.getElementById('sugerencias');
    sugerenciasDiv.innerHTML = '';
    if (texto === '') {
        cards.forEach(c => c.style.display = '');
        sugerenciasDiv.style.display = 'none';
        return;
    }
    let coincide = false;
    let count = 0;
    cards.forEach(card => {
        const nombreData = card.dataset.nombre;
        const matches = nombreData.includes(texto);
        card.style.display = matches ? '' : 'none';
        if (matches && count < 6) {
            coincide = true;
            const nombreMostrar = card.querySelector('h3').innerText;
            const item = document.createElement('div');
            item.className = 'sugerencia-item';
            item.innerHTML = `<i class="fas fa-user-circle"></i> ${nombreMostrar}`;
            item.onclick = (e) => {
                document.getElementById('busc').value = nombreMostrar;
                sugerenciasDiv.style.display = 'none';
                cards.forEach(c => c.style.display = 'none');
                card.style.display = '';
            };
            sugerenciasDiv.appendChild(item);
            count++;
        }
    });
    sugerenciasDiv.style.display = coincide ? 'block' : 'none';
}
document.getElementById('busc').addEventListener('keyup', filtrarYSugerir);
document.addEventListener('click', (e) => {
    if (!e.target.closest('.buscador-header-wrap')) {
        document.getElementById('sugerencias').style.display = 'none';
    }
});

// CRUD EDITAR
function abrirEditar(id) {
    fetch(`${BASE}clientes/detalle/${id}`)
    .then(r => r.json())
    .then(data => {
        if (data.cliente) {
            const c = data.cliente;
            document.getElementById('det-id').value = c.id_cliente;
            document.getElementById('inp-nom').value = c.nombre || '';
            document.getElementById('inp-ap').value = c.apellido_paterno || '';
            document.getElementById('inp-am').value = c.apellido_materno || '';
            document.getElementById('inp-tel').value = c.tel || '';
            document.getElementById('inp-rfc').value = c.rfc || '';
            document.getElementById('inp-est').value = c.estado || '';
            document.getElementById('modalEdit').style.display = 'flex';
        } else showToast('No se encontraron datos', 'error');
    }).catch(() => showToast('Error al cargar detalles', 'error'));
}
function cerrarEditar() { document.getElementById('modalEdit').style.display = 'none'; }
function guardarCambios() {
    const formData = new FormData(document.getElementById('form-cliente'));
    fetch(`${BASE}clientes/actualizar`, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            cerrarEditar();
            showToast('Cliente actualizado correctamente', 'success');
            setTimeout(() => location.reload(), 1500);
        } else showToast('Error: ' + (res.msg || 'desconocido'), 'error');
    }).catch(() => showToast('Error de conexión', 'error'));
}

// ELIMINAR CONFIRMACIÓN
let pendingDeleteId = null;
function confirmarEliminar(id, nombre) {
    pendingDeleteId = id;
    document.getElementById('confirm-nombre').innerText = nombre;
    document.getElementById('modalConfirmar').style.display = 'flex';
    const btnConfirm = document.getElementById('btnConfirmarEliminar');
    btnConfirm.onclick = () => {
        cerrarConfirmar();
        fetch(`${BASE}clientes/eliminar/${pendingDeleteId}`, { method: 'POST' })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                showToast('Cliente eliminado', 'success');
                setTimeout(() => location.reload(), 1400);
            } else showToast('Error al eliminar', 'error');
        }).catch(() => showToast('Error de conexión', 'error'));
    };
}
function cerrarConfirmar() { document.getElementById('modalConfirmar').style.display = 'none'; }

// Cerrar modales con overlay click
window.onclick = (e) => {
    if (e.target === document.getElementById('modalEdit')) cerrarEditar();
    if (e.target === document.getElementById('modalConfirmar')) cerrarConfirmar();
};

// flash mensajes session
window.addEventListener('DOMContentLoaded', () => {
    <?php if (session()->getFlashdata('flash_success')): ?>
        showToast('<?= session()->getFlashdata('flash_success') ?>', 'success');
    <?php endif; ?>
    <?php if (session()->getFlashdata('flash_error')): ?>
        showToast('<?= session()->getFlashdata('flash_error') ?>', 'error');
    <?php endif; ?>
});
</script>

<a href="<?= base_url('pantalla_rcliente') ?>" class="btn-flotante-clientes">
    <i class="fas fa-plus-circle"></i>
    <span>Nuevo Cliente</span>
</a>

</body>
</html>