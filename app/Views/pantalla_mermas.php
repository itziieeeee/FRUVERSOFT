<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVER · Gestión de Mermas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-green: #1d4a27;
            --primary-orange: #f16e1f;
            --primary-orange-dark: #d35400;
            --primary-orange-soft: #f39c12;
            --light-green: #e8f3e6;
            --gray-border: #e0e0e0;
            --text-dark: #333;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --radius-md: 12px;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #f5f7fa;
            padding-bottom: 80px;
        }

        /* HEADER */
        .barra-superior {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 2rem;
            background: linear-gradient(90deg, var(--primary-green) 0%, #2a5e35 100%);
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .logo-area img { height: 80px; object-fit: contain; }

        .user-actions { display: flex; gap: 0.8rem; }
        .btn-user {
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            padding: 0.5rem 1.2rem;
            background: rgba(255,255,255,0.15);
            border-radius: 50px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-user:hover { background: rgba(255,255,255,0.3); transform: translateY(-1px); }

        .menu-navegacion {
            background: white;
            padding: 0.5rem 1.5rem;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            border-bottom: 1px solid var(--gray-border);
            box-shadow: var(--shadow-sm);
            flex-wrap: wrap;
        }
        .nav-link {
            padding: 0.9rem 1.2rem;
            color: var(--text-dark);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: var(--transition);
            border-radius: 40px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .nav-link:hover { background: #fef3ed; }
        .nav-link.activo {
            color: var(--primary-orange);
            background: rgba(243, 156, 18, 0.08);
            border-bottom: 3px solid var(--primary-orange);
        }

        /* MAIN */
        .main-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .titulo-seccion {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.8rem;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-green);
            border-left: 4px solid var(--primary-orange);
            padding-left: 15px;
        }
        .titulo-seccion i { font-size: 1.5rem; color: var(--primary-orange); }

        /* ===== FORMULARIO INLINE MEJORADO ===== */
        .form-merma-inline {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 2fr 1fr;
            gap: 20px;
            align-items: end;
            background: white;
            padding: 1.8rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
            border: 1px solid var(--gray-border);
        }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { 
            font-weight: 600; 
            color: var(--primary-green); 
            font-size: 0.85rem;
            letter-spacing: 0.3px;
        }
        .form-group input, .form-group select { 
            padding: 10px 12px; 
            border: 1.5px solid var(--gray-border); 
            border-radius: 10px; 
            outline: none;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
        }
        .form-group input:focus, .form-group select:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(241, 110, 31, 0.1);
        }
        .btn-registrar {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-orange-dark) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-registrar:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(241, 110, 31, 0.3);
        }

        /* TABLA ESTILO MODERNO */
        .tabla-card {
            background: white;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-border);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead tr {
            background: #f8f9fa;
            border-bottom: 2px solid var(--gray-border);
        }
        th {
            text-align: left;
            padding: 1rem 1.2rem;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--primary-green);
        }
        td {
            padding: 1rem 1.2rem;
            border-bottom: 1px solid var(--gray-border);
            font-size: 0.9rem;
        }
        .badge-cantidad {
            background: #fdeaea;
            color: #c0392b;
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-block;
        }

        /* GRÁFICO */
        .chart-container {
            background: white;
            border-radius: var(--radius-md);
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-border);
        }
        .chart-container h3 {
            text-align: center;
            color: var(--primary-green);
            margin-bottom: 1.2rem;
            font-size: 1.1rem;
            font-weight: 700;
        }

        /* SELECT2 PERSONALIZADO */
        .select2-container--default .select2-selection--single {
            border: 1.5px solid var(--gray-border);
            border-radius: 10px;
            height: 42px;
            padding: 5px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 32px;
            color: var(--text-dark);
        }

        @media (max-width: 1000px) {
            .form-merma-inline {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            .btn-registrar { height: auto; padding: 12px; }
        }

        /* ===== BUSCADOR MEJORADO ===== */
        .buscador-wrapper {
            position: relative;
            flex: 1;
        }
        .buscador-input {
            width: 100%;
            padding: 10px 12px 10px 36px;
            border: 1.5px solid var(--gray-border);
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s ease;
        }
        
                .dropdown-sugerencias {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1.5px solid var(--gray-border);
            border-top: none;
            border-radius: 0 0 10px 10px;
            max-height: 220px;
            overflow-y: auto;
            z-index: 9999;
            display: none;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .sugerencia-item {
            padding: 10px 14px;
            cursor: pointer;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f5f5f5;
            transition: background 0.15s;
        }
        .sugerencia-item:hover, .sugerencia-item.activo {
            background: #fff5ee;
        }
        .sugerencia-item:last-child { border-bottom: none; }
        .sugerencia-stock {
            margin-left: auto;
            font-size: 0.78rem;
            background: #f0faf3;
            color: #1d4a27;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 600;
        }
        .sugerencia-empty {
            padding: 14px;
            color: #999;
            font-size: 0.85rem;
            text-align: center;
        }

        /* FILTROS */
        .filtros-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filtro-btn {
            padding: 6px 16px;
            border-radius: 50px;
            border: 1.5px solid var(--gray-border);
            background: white;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: #555;
        }
        .filtro-btn:hover, .filtro-btn.activo {
            background: var(--primary-orange);
            color: white;
            border-color: var(--primary-orange);
        }
        .filtro-btn.activo-verde {
            background: var(--primary-green);
            color: white;
            border-color: var(--primary-green);
        }
        .buscador-tabla-wrapper {
            position: relative;
            margin-bottom: 10px;
        }
        .buscador-tabla-input {
            width: 100%;
            padding: 9px 12px 9px 36px;
            border: 1.5px solid var(--gray-border);
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 0.88rem;
            outline: none;
            transition: all 0.3s;
        }
        .buscador-tabla-input:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(29,74,39,0.08);
        }
    </style>
</head>
<body>

<header>
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo">
        </div>
        <div class="user-actions">
            <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> Admin</a>
            <a href="<?= base_url('menusolo') ?>" class="btn-user"><i class="fas fa-sign-out-alt"></i> Regresar</a>
        </div>
    </div>
</header>

<nav class="menu-navegacion">
    <div class="nav-links">
        <a href="<?= base_url('pantalla_ventas') ?>" class="nav-link"><i class="fas fa-tag"></i> Ventas</a>
        <a href="<?= base_url('pantalla_pedidos') ?>" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
        <a href="<?= base_url('inventario') ?>" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
        <a href="<?= base_url('mermas') ?>" class="nav-link activo"><i class="fas fa-trash-can"></i> Mermas</a>
        <a href="<?= base_url('pantalla_clientes') ?>" class="nav-link"><i class="fa-solid fa-users"></i> Clientes</a>
        <a href="<?= base_url('pantalla_repartidores') ?>" class="nav-link"><i class="fa-solid fa-dolly"></i> Repartidores</a>
        <a href="<?= base_url('pantalla_productos') ?>" class="nav-link"><i class="fa-solid fa-apple-whole"></i> Productos</a>
    </div>
</nav>

<div class="main-container">
    <div class="titulo-seccion">
        <i class="fas fa-trash-can"></i>
        <span>Registro de Mermas</span>
    </div>

    <!-- FORMULARIO CON BUSCADOR MEJORADO (SIN ICONO DE MANZANA) -->
    <form action="<?= base_url('merma/guardar') ?>" method="POST" class="form-merma-inline">
        
        <!-- CAMPO OCULTO que guarda el id_producto seleccionado -->
        <input type="hidden" name="id_producto" id="input_id_producto">

        <div class="form-group">
            <label><i class="fas fa-apple-alt"></i> Producto</label>
            <div class="buscador-wrapper">
                <input 
                    type="text" 
                    id="buscador_producto" 
                    class="buscador-input"
                    placeholder="Escribe para buscar..."
                    autocomplete="off"
                    required>
                <div class="dropdown-sugerencias" id="dropdown_sugerencias"></div>
            </div>
        </div>

        <div class="form-group">
            <label><i class="fas fa-weight-hanging"></i> Cantidad</label>
            <input type="number" name="cantidad" min="0.01" step="0.01" placeholder="0.00" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-cubes"></i> Unidad</label>
            <select name="unidad_venta" required>
                <option value="" disabled selected hidden>Selecciona</option>
                <option value="Caja">Caja</option>
                <option value="Kilo">Kilo</option>
                <option value="Domo">Domo</option>
                <option value="Mazo">Mazo</option>
                <option value="Arpilla">Arpilla</option>
                <option value="Ramo">Ramo</option>
                <option value="Pieza">Pieza</option>
                <option value="Hortalizas">Hortalizas</option>
            </select>
        </div>

        <div class="form-group">
            <label><i class="fas fa-comment"></i> Motivo</label>
            <input type="text" name="motivo" placeholder="Ej. Golpeado, caducado..." required>
        </div>

        <button type="submit" class="btn-registrar">
            <i class="fas fa-save"></i> Registrar Merma
        </button>
    </form>

    <!-- HISTORIAL CON FILTROS Y BUSCADOR -->
    <div class="titulo-seccion" style="margin-bottom: 15px; font-size: 1.1rem;">
        <i class="fas fa-history"></i>
        <span>Historial de Mermas Recientes</span>
    </div>

    <!-- BARRA DE FILTROS -->
    <div class="filtros-bar">
        <span style="font-size:0.82rem; color:#666; font-weight:600;">Filtrar:</span>
        <button type="button" class="filtro-btn activo" onclick="filtrar('todos', this)">
            <i class="fas fa-list"></i> Todos
        </button>
        
        <span style="color:#ddd; margin: 0 4px;">|</span>
        <span style="font-size:0.82rem; color:#666; font-weight:600;">Orden:</span>
        <button type="button" class="filtro-btn" onclick="ordenar('az', this)">
            <i class="fas fa-sort-alpha-down"></i> A → Z
        </button>
        <button type="button" class="filtro-btn" onclick="ordenar('za', this)">
            <i class="fas fa-sort-alpha-up"></i> Z → A
        </button>

        <!-- Buscador en historial -->
        <div class="buscador-tabla-wrapper" style="margin-left: auto; width: 220px; margin-bottom:0;">
            <i class="fas fa-search" style="position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#999; font-size:0.82rem;"></i>
            <input type="text" class="buscador-tabla-input" id="buscadorHistorial" placeholder="Buscar en historial...">
        </div>
    </div>

    <div class="tabla-card">
        <table>
            <thead>
                <tr>
                    <th>Fecha Registro</th>
                    <th>Producto</th>
                    <th>Cantidad Mermada</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody id="tablaHistorial">
                <?php if(!empty($historial_mermas)): ?>
                    <?php foreach($historial_mermas as $hm): ?>
                        <tr class="fila-merma" 
                            data-nombre="<?= strtolower($hm['nombre']) ?>"
                            data-categoria="<?= $hm['categoria'] ?? '' ?>">
                            <td><?= date('d/m/Y H:i', strtotime($hm['fecha'])) ?></td>
                            <td><strong><?= $hm['nombre'] ?></strong></td>
                            <td><span class="badge-cantidad"><?= $hm['cantidad'] ?></span></td>
                            <td><?= $hm['motivo'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr id="filaVacia">
                        <td colspan="4" style="text-align:center; padding:2rem; color:#999;">
                            <i class="fas fa-box-open"></i> No hay mermas registradas.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div id="sinResultados" style="display:none; text-align:center; padding:2rem; color:#999;">
            <i class="fas fa-search"></i> No se encontraron resultados.
        </div>
    </div>

    <!-- Tu gráfica igual -->
    <?php if (!empty($grafica_labels)): ?>
    <div class="chart-container">
        <h3><i class="fas fa-chart-line"></i> Comparativo: Entradas vs Mermas</h3>
        <canvas id="graficaEntradaMerma"></canvas>
    </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

// ============================================================
// BUSCADOR CON DROPDOWN EN EL FORMULARIO (SIN ICONO DE MANZANA)
// ============================================================
const productos = <?= json_encode($productos_merma) ?>;
const inputBuscador   = document.getElementById('buscador_producto');
const inputIdProducto = document.getElementById('input_id_producto');
const dropdown        = document.getElementById('dropdown_sugerencias');
let indiceActivo = -1;

inputBuscador.addEventListener('input', function() {
    const texto = this.value.toLowerCase().trim();
    inputIdProducto.value = ''; // limpiar selección al escribir
    indiceActivo = -1;

    if (texto.length === 0) {
        dropdown.style.display = 'none';
        return;
    }

    const filtrados = productos.filter(p =>
        p.nombre.toLowerCase().includes(texto)
    );

    if (filtrados.length === 0) {
        dropdown.innerHTML = '<div class="sugerencia-empty"><i class="fas fa-search"></i> Sin resultados</div>';
    } else {
        // Renderizar sugerencias SIN el ícono de manzana
        dropdown.innerHTML = filtrados.map((p, i) => `
            <div class="sugerencia-item" 
                 data-id="${p.id_p}" 
                 data-nombre="${p.nombre}"
                 data-index="${i}">
                <span>${resaltarTexto(p.nombre, texto)}</span>
                <span class="sugerencia-stock">Stock: ${p.e_total}</span>
            </div>
        `).join('');
    }

    dropdown.style.display = 'block';
});

// Resalta el texto buscado
function resaltarTexto(nombre, texto) {
    const regex = new RegExp(`(${texto})`, 'gi');
    return nombre.replace(regex, '<strong style="color:var(--primary-orange)">$1</strong>');
}

// Click en sugerencia
dropdown.addEventListener('click', function(e) {
    const item = e.target.closest('.sugerencia-item');
    if (!item) return;
    seleccionarProducto(item.dataset.id, item.dataset.nombre);
});

function seleccionarProducto(id, nombre) {
    inputBuscador.value   = nombre;
    inputIdProducto.value = id;
    dropdown.style.display = 'none';
    indiceActivo = -1;
}

// Navegación con teclado ↑ ↓ Enter
inputBuscador.addEventListener('keydown', function(e) {
    const items = dropdown.querySelectorAll('.sugerencia-item');
    if (!items.length) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        indiceActivo = Math.min(indiceActivo + 1, items.length - 1);
        actualizarActivo(items);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        indiceActivo = Math.max(indiceActivo - 1, 0);
        actualizarActivo(items);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (indiceActivo >= 0 && items[indiceActivo]) {
            seleccionarProducto(items[indiceActivo].dataset.id, items[indiceActivo].dataset.nombre);
        }
    } else if (e.key === 'Escape') {
        dropdown.style.display = 'none';
    }
});

function actualizarActivo(items) {
    items.forEach(i => i.classList.remove('activo'));
    if (indiceActivo >= 0) {
        items[indiceActivo].classList.add('activo');
        items[indiceActivo].scrollIntoView({ block: 'nearest' });
    }
}

// Cerrar dropdown al hacer click fuera
document.addEventListener('click', function(e) {
    if (!inputBuscador.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

// ============================================================
// FILTROS Y BUSCADOR DEL HISTORIAL
// ============================================================
let filtroActual  = 'todos';
let ordenActual   = null;
let busquedaActual = '';

function filtrar(categoria, btn) {
    filtroActual = categoria;
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    aplicarFiltros();
}

function ordenar(tipo, btn) {
    ordenActual = tipo;
    // Solo resaltar botones de orden
    document.querySelectorAll('.filtro-btn').forEach(b => {
        if (b.onclick && (b.getAttribute('onclick').includes('ordenar'))) {
            b.classList.remove('activo');
        }
    });
    btn.classList.add('activo');
    aplicarFiltros();
}

document.getElementById('buscadorHistorial').addEventListener('input', function() {
    busquedaActual = this.value.toLowerCase().trim();
    aplicarFiltros();
});

function aplicarFiltros() {
    const filas = Array.from(document.querySelectorAll('.fila-merma'));
    let visibles = filas;

    // Filtro por categoría
    if (filtroActual !== 'todos') {
        visibles = visibles.filter(f => f.dataset.categoria === filtroActual);
    }

    // Filtro por búsqueda
    if (busquedaActual) {
        visibles = visibles.filter(f => f.dataset.nombre.includes(busquedaActual));
    }

    // Ocultar todas
    filas.forEach(f => f.style.display = 'none');

    // Ordenar
    if (ordenActual === 'az') {
        visibles.sort((a, b) => a.dataset.nombre.localeCompare(b.dataset.nombre));
    } else if (ordenActual === 'za') {
        visibles.sort((a, b) => b.dataset.nombre.localeCompare(a.dataset.nombre));
    }

    // Mostrar y reordenar en DOM
    const tbody = document.getElementById('tablaHistorial');
    visibles.forEach(f => {
        f.style.display = '';
        tbody.appendChild(f); // reordena visualmente
    });

    // Mensaje sin resultados
    document.getElementById('sinResultados').style.display = visibles.length === 0 ? 'block' : 'none';
}

// ============================================================
// GRÁFICA (igual que antes)
// ============================================================
<?php if (!empty($grafica_labels)): ?>
const labels   = <?= json_encode($grafica_labels) ?>;
const entradas = <?= json_encode($grafica_entradas) ?>;
const mermas   = <?= json_encode($grafica_mermas) ?>;

const ctx = document.getElementById('graficaEntradaMerma').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: 'Entradas', data: entradas, backgroundColor: 'rgba(29,74,39,0.7)', borderColor: '#1d4a27', borderWidth: 1, borderRadius: 8 },
            { label: 'Merma',   data: mermas,   backgroundColor: 'rgba(241,110,31,0.7)', borderColor: '#f16e1f', borderWidth: 1, borderRadius: 8 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
    }
});
<?php endif; ?>
</script>
</body>
</html>