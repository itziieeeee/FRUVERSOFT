<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<title>Inventario</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#f0f4f8;
    font-family:'Segoe UI',Roboto,system-ui,sans-serif;
}

.fondo{
    max-width:1700px;
    margin:20px auto;
    padding:0 25px;
}
/* ===== MENU SUPERIOR ===== */
.boton:hover{
    background:#fff;
    color:#2d5a27;
    transform:translateY(-3px);
}

.buscador{
    background:#fff;
    border-radius:50px;
    padding:5px 5px 5px 20px;
    display:flex;
    align-items:center;
    min-width:280px;
    box-shadow:0 5px 15px rgba(0,0,0,.1);
}

.buscador input{
    border:none;
    outline:none;
    flex:1;
    padding:12px 0;
    font-size:15px;
}

.buscador button{
    background:#2d5a27;
    border:none;
    border-radius:50%;
    width:45px;
    height:45px;
    color:#fff;
    cursor:pointer;
    transition:.3s;
}

.buscador button:hover{
    background:#f16b1a;
    transform:scale(1.05);
}

/* ===== OPCIONES ===== */
.opciones{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));
    gap:30px;
    margin-top:40px;
}

.botonesopciones{
    background: linear-gradient(145deg, #ffffff, #f3f3f3);
    border-radius: 30px;
    padding: 40px 10px;       
    text-decoration: none;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align:center;
    gap: 30px;
    min-height: 280px;         
    width: 100%;
    box-shadow: 0 20px 40px rgba(0,0,0,.08);
    transition: .3s ease;
}

.botonesopciones:hover{
    transform:translateY(-12px) scale(1.03);
    box-shadow:0 20px 40px rgba(0,0,0,.15);
}

.botonesopciones img{
    width:240px;
    height:240px;
    object-fit:contain;
    transition:.3s;
}

.botonesopciones:hover img{
    transform:scale(1.1);
}

.botonesopciones span{
    font-weight:700;
    color:#1e3a2f;
    font-size:26px;     
    background:#e8f5e9;
    padding:15px 40px;   
    border-radius:50px;
}

.marriba{
    background:#1d4a27;
    padding:10px 30px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    border-radius:12px;
    box-shadow:0 6px 18px rgba(0,0,0,0.15);
    margin-bottom:40px;
}

.logo-area{
    display:flex;
    align-items:center;
    gap:15px;
}

.logo-area img{
    width:110px;
}

.logo-area h1{
    color:white;
    font-size:1.5rem;
    font-weight:500;
    border-left:2px solid rgba(255,255,255,.3);
    padding-left:16px;
}

.action-buttons{
    display:flex;
    gap:6px;
}

.btnmenu{
    background:rgba(255,255,255,.1);
    border:1px solid rgba(255,255,255,.2);
    color:white;
    padding:8px 18px;
    border-radius:40px;
    font-weight:500;
    font-size:.9rem;
    display:flex;
    align-items:center;
    gap:8px;
    text-decoration:none;
}

.btnmenu:hover{
    background:white;
    color:#1d4a27;
}

.tablas{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(320px,1fr));
    gap:30px;
    margin-top:40px;
}

.cardtabla{
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.cardtabla h3{
    margin-bottom:15px;
    color:#1e3a2f;
    border-bottom:3px solid #2d5a27;
    padding-bottom:8px;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    background:#e8f5e9;
    padding:10px;
    text-align:left;
}

table td{
    padding:10px;
    border-bottom:1px solid #eee;
}
/* --- NUEVO MENÚ DE NAVEGACIÓN (6 BOTONES) --- */
        .menu-navegacion {
            background-color: #f5faf4; /* Un color muy suave que contrasta con el verde fuerte */
            padding: 12px 24px;
            border-bottom: 1px solid #cde0ca;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            flex-shrink: 0;
        }

        .nav-links {
    display: flex;
    align-items: center;
    justify-content: space-around; /* Distribuye el espacio equitativamente */
    gap: 7px;
    flex-wrap: wrap;
    max-width: 1200px; /* Limita el ancho máximo */
    margin: 0 auto; /* Centra el contenedor en la página */
}

        .nav-link {
            background: white;
            border-radius: 40px;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: 600;
            color: #1e3a2f;
            font-size: 0.95rem;
            border: 1.5px solid transparent;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(0,40,0,0.05);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link i {
            color: #f16b1a;
            font-size: 1rem;
        }

        .nav-link:hover {
            transform: translateY(-2px);
            border-color: #f16b1a;
            box-shadow: 0 6px 12px rgba(0,0,0,0.08);
            color: #1d4a27;
        }
</style>
</head>

<body>
<div class="fondo">

<header>
<div class="marriba">

    <div class="logo-area">
        <img src="img/LOGO1.png" alt="Logo">
        <h1>Inventario</h1>
    </div>

    <div class="action-buttons">
        <a href="#" class="btnmenu">
            <i class="fas fa-user-shield"></i> Admin
        </a>

        <a href="#" class="btnmenu">
            <i class="fas fa-bell"></i> Notificaciones
        </a>

        <a href="<?= base_url('menusolo') ?>" class="btnmenu">
            <i class="fas fa-sign-out-alt"></i> Regresar
        </a>
    </div>

    <div class="buscador">
        <input type="text" placeholder="Buscar...">
        <button>
            <i class="fas fa-search"></i>
        </button>
    </div>
   

</div>
<!-- NUEVO: Menú de navegación principal (6 botones) -->
    <nav class="menu-navegacion">
        <div class="nav-links">
            <a href="#" class="nav-link"><i class="fas fa-tag"></i> Precios</a>
            <a href="#" class="nav-link"><i class="fas fa-credit-card"></i> Pagos</a>
            <a href="#" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
            <a href="<?=base_url('pantalla_inventario')?>" class="nav-link"><i class="fas fa-boxes"></i> Clientes</a>
            <a href="#" class="nav-link"><i class="fas fa-file-invoice"></i> Facturación</a>
            <a href="#" class="nav-link"><i class="fas fa-chart-bar"></i> Reportes</a>
            <!-- El enlace a "Clientes" se omite porque ya estamos en esa sección -->
        </div>
    </nav>
</header>
<div class="opciones">

    <a href="<?= base_url('inventario/entrada') ?>" class="botonesopciones">
         <img src="<?= base_url('img/Entrada.jpeg') ?>">
        <span>Entrada</span>
    </a>

    <a href="<?= base_url('inventario/merma') ?>" class="botonesopciones" id="openModal">
         <img src="<?= base_url('img/Merma.jpeg') ?>">
        <span>Merma</span>
    </a>
<div id="myModal" class="modal">
  <div class="modal-content">
    <span class="close-btn">&times;</span>
    <h2>Título del Modal</h2>
    <p>Este es un mensaje dentro del modal creado con JS.</p>
  </div>
</div>

    <a href="<?= base_url('inventario/producto') ?>" class="botonesopciones">
         <img src="<?= base_url('img/Producto.jpeg') ?>">
        <span>Productos</span>
    </a>

</div>


<div class="tablas">

<div class="cardtabla">
<h3>Registro de Entrada</h3>
<table>
<thead>
<tr>
<th>Producto</th>
<th>Cantidad</th>
<th>Fecha</th>
</tr>
</thead>

<tbody>
<?php if(!empty($entradas)): ?>
    <?php foreach($entradas as $e): ?>
    <tr>
        <td><?= $e['producto'] ?></td>
        <td><?= $e['cantidad'] ?></td>
        <td><?= $e['fecha'] ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr>
<td colspan="3">No hay registros</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>


<div class="cardtabla">
<h3>Registro de Merma</h3>
<table>
<thead>
<tr>
<th>Producto</th>
<th>Cantidad</th>
<th>Motivo</th>
</tr>
</thead>

<tbody>
<?php if(!empty($mermas)): ?>
    <?php foreach($mermas as $m): ?>
    <tr>
        <td><?= $m['producto'] ?></td>
        <td><?= $m['cantidad'] ?></td>
        <td><?= $m['motivo'] ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr>
<td colspan="3">No hay registros</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>


<div class="cardtabla">
<h3>Lista de Productos</h3>
<table>

<thead>
<tr>
<th>Producto</th>
<th>Precio</th>
<th>Stock</th>
</tr>
</thead>

<tbody>
<?php if(!empty($productos)): ?>

    <?php foreach($productos as $p): ?>
    <tr>
        <td><?= $p['producto'] ?></td>
        <td><?= '$' . $p['precio'] ?></td>
        <td><?= $p['stock'] ?></td>
    </tr>
    <?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="3">No hay productos</td>
</tr>

<?php endif; ?>
</tbody>

</table>
</div>
<script src="js/inventario.js"></script>
</html>