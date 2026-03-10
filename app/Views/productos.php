<!DOCTYPE html>

<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<title>FRUVER · Inventario</title>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{
font-family:Arial, Helvetica, sans-serif;
background:#f4f6f4;
}

/* ===== BARRA SUPERIOR ===== */

.marriba{
background:#1d4a27;
padding:10px 24px;
display:flex;
align-items:center;
justify-content:space-between;
flex-wrap:wrap;
gap:15px;
}

.logo-area h1{
color:white;
font-size:22px;
}

/* ===== MENU ===== */

.menu-dashboard{
display:flex;
gap:10px;
flex-wrap:wrap;
}

.menu-dashboard a{
background:rgba(255,255,255,0.12);
border:1px solid rgba(255,255,255,0.25);
color:white;
padding:8px 18px;
border-radius:40px;
text-decoration:none;
font-weight:600;
font-size:14px;
}

.menu-dashboard a:hover{
background:white;
color:#1d4a27;
}

.activo{
background:white !important;
color:#1d4a27 !important;
}

/* ===== BOTONES DERECHA ===== */

.action-buttons{
display:flex;
gap:8px;
}

.btnmenu{
background:rgba(255,255,255,0.1);
border:1px solid rgba(255,255,255,0.2);
color:white;
padding:8px 14px;
border-radius:40px;
text-decoration:none;
}

/* ===== CONTENEDOR ===== */

.layout{
padding:30px;
}

/* ===== BUSCADOR ===== */

.barra-resultados{
display:flex;
justify-content:space-between;
margin-bottom:20px;
flex-wrap:wrap;
gap:10px;
}

.select-orden{
padding:8px;
border-radius:8px;
border:1px solid #ccc;
}

.btn-nuevo-producto{
background:#f57c00;
color:white;
padding:8px 16px;
border-radius:8px;
text-decoration:none;
}

/* ===== GRID PRODUCTOS ===== */

.productos-grid{
display:grid;
grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
gap:20px;
}

/* ===== TARJETA PRODUCTO ===== */

.producto-card{
background:white;
border-radius:12px;
padding:15px;
box-shadow:0 4px 10px rgba(0,0,0,0.1);
text-align:center;
}

.producto-img{
font-size:60px;
margin-bottom:10px;
}

.producto-nombre{
font-weight:bold;
}

.producto-descripcion{
font-size:13px;
color:#666;
margin-bottom:10px;
}

.precio-venta{
font-size:18px;
font-weight:bold;
margin-bottom:10px;
}

.producto-acciones{
display:flex;
gap:6px;
}

.btn-accion{
flex:1;
padding:6px;
border:none;
border-radius:6px;
cursor:pointer;
}

.btn-editar{
background:#e8f5e9;
}
.menu-navegacion {
    background-color: #f5faf4;
    padding: 12px 24px;
    border-bottom: 1px solid #cde0ca;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    flex-shrink: 0;
}

.nav-links {
    display: flex;
    align-items: center;
    justify-content: space-around;
    gap: 12px;
    flex-wrap: wrap;
    max-width: 1200px;
    margin: 0 auto;
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

.nav-link.activo {
    background: #1d4a27;
    color: white;
    border-color: #1d4a27;
}

.nav-link.activo i {
    color: white;
}
.btn-comprar{
background:#2e7d32;
color:white;
}

</style>

</head>

<body>

<!-- BARRA SUPERIOR -->

 <nav class="menu-navegacion">
        <div class="nav-links">
            <a href="#" class="nav-link"><i class="fas fa-tag"></i> Precios</a>
            <a href="#" class="nav-link"><i class="fas fa-credit-card"></i> Pagos</a>
            <a href="#" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
            <a href="<?=base_url('pantalla_inventario')?>" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="#" class="nav-link"><i class="fas fa-file-invoice"></i> Facturación</a>
            <a href="#" class="nav-link"><i class="fas fa-chart-bar"></i> Reportes</a>
            <!-- El enlace a "Clientes" se omite porque ya estamos en esa sección -->
        </div>
    </nav>

<!-- CONTENIDO -->

<div class="layout">

<div class="barra-resultados">

<input type="text" id="buscarInput" class="select-orden" placeholder="Buscar producto..." oninput="buscarProductos()">

<select class="select-orden" onchange="ordenarProductos(this.value)">
<option value="nombre">Nombre A-Z</option>
<option value="precio-asc">Precio menor</option>
<option value="precio-desc">Precio mayor</option>
</select>

<a href="#" class="btn-nuevo-producto">
<i class="fas fa-plus"></i> Nuevo producto
</a>

</div>

<div class="productos-grid" id="productosGrid"></div>

</div>

<script>

const productos=[
{nombre:"Tomate",descripcion:"Tomate rojo",emoji:"🍅",precio:22},
{nombre:"Fresa",descripcion:"Fresa fresca",emoji:"🍓",precio:65},
{nombre:"Sandía",descripcion:"Sandía dulce",emoji:"🍉",precio:15}
];

function renderProductos(lista){

const grid=document.getElementById("productosGrid");

grid.innerHTML=lista.map(p=>`

<div class="producto-card">

<div class="producto-img">${p.emoji}</div>

<div class="producto-nombre">${p.nombre}</div>

<div class="producto-descripcion">${p.descripcion}</div>

<div class="precio-venta">$${p.precio}</div>

<div class="producto-acciones">

<button class="btn-accion btn-editar">Editar</button>

<button class="btn-accion btn-comprar">Entrada</button>

</div>

</div>

`).join("");

}

function buscarProductos(){

const texto=document.getElementById("buscarInput").value.toLowerCase();

const filtrados=productos.filter(p=>p.nombre.toLowerCase().includes(texto));

renderProductos(filtrados);

}

function ordenarProductos(tipo){

let lista=[...productos];

if(tipo==="precio-asc") lista.sort((a,b)=>a.precio-b.precio);

if(tipo==="precio-desc") lista.sort((a,b)=>b.precio-a.precio);

if(tipo==="nombre") lista.sort((a,b)=>a.nombre.localeCompare(b.nombre));

renderProductos(lista);

}

renderProductos(productos);

</script>

</body>
</html>
