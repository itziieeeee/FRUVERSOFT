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
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
            --radius-md: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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

        .user-actions { display: flex; gap: 0.8rem; align-items: center; }

        /* ===== BOTONES DINÁMICOS (ADMIN Y REGRESAR) ===== */
        .btn-user {
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            padding: 0.55rem 1.3rem;
            background: rgba(255,255,255,0.15);
            border-radius: 50px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(6px);
        }
        .btn-user::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
            transition: left 0.6s ease;
        }
        .btn-user:hover::before { left: 100%; }
        .btn-user:hover {
            background: rgba(255,255,255,0.28);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.25);
        }
        .btn-user:active { transform: translateY(0); }

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
        .nav-link:hover { background: #fef3ed; transform: translateY(-1px); }
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
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
            height: 42px;
            background: white;
            color: var(--text-dark);
        }
        .form-group input:focus, .form-group select:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(241, 110, 31, 0.1);
            transform: translateY(-1px);
        }

        /* ===== CAMPO MOTIVO HOMOGENEIZADO ===== */
        .input-motivo {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--gray-border);
            border-radius: 10px;
            outline: none;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
            height: 42px;
            background: white;
            color: var(--text-dark);
            font-size: 0.9rem;
        }
        .input-motivo::placeholder {
            color: #aaa;
        }
        .input-motivo:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(241, 110, 31, 0.1);
            transform: translateY(-1px);
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
            position: relative;
            overflow: hidden;
        }
        .btn-registrar::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }
        .btn-registrar:hover::before { left: 100%; }
        .btn-registrar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(241, 110, 31, 0.4);
        }
        .btn-registrar:active { transform: translateY(0); }

        /* ===== TABLA ESTILO MODERNO Y DINÁMICO ===== */
        .tabla-card {
            background: white;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-border);
            transition: var(--transition);
        }
        .tabla-card:hover {
            box-shadow: var(--shadow-md);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead tr {
            background: linear-gradient(90deg, #f8f9fa 0%, #f0f4f1 100%);
            border-bottom: 2px solid var(--gray-border);
        }
        th {
            text-align: left;
            padding: 1rem 1.2rem;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--primary-green);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 1rem 1.2rem;
            border-bottom: 1px solid var(--gray-border);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        /* Filas dinámicas con animación de entrada y hover */
        .fila-merma {
            animation: fadeInRow 0.4s ease forwards;
            opacity: 0;
        }
        @keyframes fadeInRow {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .fila-merma:nth-child(1) { animation-delay: 0.05s; }
        .fila-merma:nth-child(2) { animation-delay: 0.1s; }
        .fila-merma:nth-child(3) { animation-delay: 0.15s; }
        .fila-merma:nth-child(4) { animation-delay: 0.2s; }
        .fila-merma:nth-child(5) { animation-delay: 0.25s; }

        .fila-merma:hover {
            background: #fef9f5;
            transform: scale(1.005);
            box-shadow: 0 2px 12px rgba(241, 110, 31, 0.08);
        }
        .fila-merma:hover td {
            color: var(--primary-green);
        }

        .badge-cantidad {
            background: linear-gradient(135deg, #fdeaea 0%, #fbd5d5 100%);
            color: #c0392b;
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-block;
            transition: var(--transition);
            border: 1px solid rgba(192, 57, 43, 0.1);
        }
        .fila-merma:hover .badge-cantidad {
            transform: scale(1.08);
            box-shadow: 0 2px 8px rgba(192, 57, 43, 0.2);
        }

        /* ===== GRÁFICO DINÁMICO ===== */
        .chart-container {
            background: white;
            border-radius: var(--radius-md);
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-border);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-green), var(--primary-orange), var(--primary-green));
            background-size: 200% 100%;
            animation: gradientMove 4s linear infinite;
        }
        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }
        .chart-container:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        .chart-container h3 {
            text-align: center;
            color: var(--primary-green);
            margin-bottom: 1.2rem;
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .chart-container h3 i {
            color: var(--primary-orange);
            animation: pulseIcon 2s ease-in-out infinite;
        }
        @keyframes pulseIcon {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }
        #graficaEntradaMerma {
            max-height: 380px;
        }

        /* SELECT2 PERSONALIZADO */
        .select2-container--default .select2-selection--single {
            border: 1.5px solid var(--gray-border);
            border-radius: 10px;
            height: 42px;
            padding: 5px;
            transition: var(--transition);
        }
        .select2-container--default .select2-selection--single:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(241, 110, 31, 0.1);
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
            height: 42px;
        }
        .buscador-input:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(241, 110, 31, 0.1);
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
            position: relative;
            overflow: hidden;
        }
        .filtro-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(241,110,31,0.15), transparent);
            transition: left 0.5s;
        }
        .filtro-btn:hover::before { left: 100%; }
        .filtro-btn:hover, .filtro-btn.activo {
            background: var(--primary-orange);
            color: white;
            border-color: var(--primary-orange);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(241, 110, 31, 0.25);
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

<?= view('partials/nav_admin') ?>

<div class="main-container">
    <div class="titulo-seccion">
        <i class="fas fa-trash-can"></i>
        <span>Registro de Mermas</span>
    </div>

    <!-- FORMULARIO CON BUSCADOR MEJORADO -->
    <form action="<?= base_url('merma/guardar') ?>" method="POST" class="form-merma-inline">
        
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
            <label><i class="fas fa-comment-dots"></i> Motivo</label>
            <input type="text" name="motivo" list="motivos" class="input-motivo" placeholder="Ej. Caducado, golpeado..." required>
            <datalist id="motivos">
                <option value="Madurez">
                <option value="Golpeado o dañado">
                <option value="Caducado">
                <option value="Error de empaque">
                <option value="Extravío">
            </datalist>
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

    <!-- GRÁFICA DINÁMICA -->
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
// BUSCADOR CON DROPDOWN EN EL FORMULARIO
// ============================================================
const productos = <?= json_encode($productos_merma) ?>;
const inputBuscador   = document.getElementById('buscador_producto');
const inputIdProducto = document.getElementById('input_id_producto');
const dropdown        = document.getElementById('dropdown_sugerencias');
let indiceActivo = -1;

inputBuscador.addEventListener('input', function() {
    const texto = this.value.toLowerCase().trim();
    inputIdProducto.value = '';
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

function resaltarTexto(nombre, texto) {
    const regex = new RegExp(`(${texto})`, 'gi');
    return nombre.replace(regex, '<strong style="color:var(--primary-orange)">$1</strong>');
}

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

    if (filtroActual !== 'todos') {
        visibles = visibles.filter(f => f.dataset.categoria === filtroActual);
    }

    if (busquedaActual) {
        visibles = visibles.filter(f => f.dataset.nombre.includes(busquedaActual));
    }

    filas.forEach(f => f.style.display = 'none');

    if (ordenActual === 'az') {
        visibles.sort((a, b) => a.dataset.nombre.localeCompare(b.dataset.nombre));
    } else if (ordenActual === 'za') {
        visibles.sort((a, b) => b.dataset.nombre.localeCompare(a.dataset.nombre));
    }

    const tbody = document.getElementById('tablaHistorial');
    visibles.forEach(f => {
        f.style.display = '';
        tbody.appendChild(f);
    });

    document.getElementById('sinResultados').style.display = visibles.length === 0 ? 'block' : 'none';
}

// ============================================================
// GRÁFICA DINÁMICA
// ============================================================
<?php if (!empty($grafica_labels)): ?>
const labels   = <?= json_encode($grafica_labels) ?>;
const entradas = <?= json_encode($grafica_entradas) ?>;
const mermas   = <?= json_encode($grafica_mermas) ?>;

const ctx = document.getElementById('graficaEntradaMerma').getContext('2d');

// Crear gradientes para las barras
const gradientEntradas = ctx.createLinearGradient(0, 0, 0, 400);
gradientEntradas.addColorStop(0, 'rgba(29, 74, 39, 0.9)');
gradientEntradas.addColorStop(0.5, 'rgba(42, 94, 53, 0.7)');
gradientEntradas.addColorStop(1, 'rgba(29, 74, 39, 0.3)');

const gradientMermas = ctx.createLinearGradient(0, 0, 0, 400);
gradientMermas.addColorStop(0, 'rgba(241, 110, 31, 0.9)');
gradientMermas.addColorStop(0.5, 'rgba(211, 84, 0, 0.7)');
gradientMermas.addColorStop(1, 'rgba(241, 110, 31, 0.3)');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'Entradas',
                data: entradas,
                backgroundColor: gradientEntradas,
                borderColor: '#1d4a27',
                borderWidth: 2,
                borderRadius: 10,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(29, 74, 39, 1)',
                hoverBorderWidth: 3
            },
            {
                label: 'Merma',
                data: mermas,
                backgroundColor: gradientMermas,
                borderColor: '#f16e1f',
                borderWidth: 2,
                borderRadius: 10,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(241, 110, 31, 1)',
                hoverBorderWidth: 3
            }
        ]
    },
    options: {
        responsive: true,
        animation: {
            duration: 1200,
            easing: 'easeOutQuart'
        },
        interaction: {
            mode: 'index',
            intersect: false
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    pointStyle: 'rectRounded',
                    padding: 20,
                    font: {
                        size: 13,
                        weight: '600',
                        family: 'Inter'
                    },
                    color: '#333'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(29, 74, 39, 0.95)',
                titleFont: { size: 14, weight: '700', family: 'Inter' },
                bodyFont: { size: 13, family: 'Inter' },
                padding: 12,
                cornerRadius: 10,
                displayColors: true,
                boxPadding: 6,
                callbacks: {
                    label: function(context) {
                        return ` ${context.dataset.label}: ${context.parsed.y.toLocaleString()}`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.05)',
                    drawBorder: false
                },
                ticks: {
                    font: { family: 'Inter', size: 12 },
                    color: '#666'
                }
            },
            x: {
                grid: { display: false },
                ticks: {
                    font: { family: 'Inter', size: 12, weight: '500' },
                    color: '#555'
                }
            }
        }
    }
});
<?php endif; ?>
</script>
</body>
</html>