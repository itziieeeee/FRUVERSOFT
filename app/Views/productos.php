<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

/*menu*/

.menu-navegacion {
background-color:#f5faf4;
padding:12px 24px;
border-bottom:1px solid #cde0ca;
box-shadow:0 2px 5px rgba(0,0,0,0.05);
}

.nav-links{
display:flex;
align-items:center;
justify-content:space-around;
gap:12px;
flex-wrap:wrap;
max-width:1200px;
margin:0 auto;
}

.nav-link{
background:white;
border-radius:40px;
padding:10px 20px;
text-decoration:none;
font-weight:600;
color:#1e3a2f;
font-size:0.95rem;
border:1.5px solid transparent;
transition:all 0.2s ease;
box-shadow:0 2px 6px rgba(0,40,0,0.05);
display:inline-flex;
align-items:center;
gap:6px;
}

.nav-link i{
color:#f16b1a;
font-size:1rem;
}

.nav-link:hover{
transform:translateY(-2px);
border-color:#f16b1a;
box-shadow:0 6px 12px rgba(0,0,0,0.08);
color:#1d4a27;
}

.nav-link.activo{
background:#1d4a27;
color:white;
border-color:#1d4a27;
}

.nav-link.activo i{
color:white;
}

/*contenedor*/

.layout{
padding:30px;
}

/*barra buscador*/

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

/*grid productos*/

.productos-grid{
display:grid;
grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
gap:20px;
}

/*tarjeta*/

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

.btn-comprar{
background:#2e7d32;
color:white;
}

</style>
</head>

<body>

<!-- menu -->
<nav class="menu-navegacion">
<div class="nav-links">

<a href="#" class="nav-link"><i class="fas fa-tag"></i> Precios</a>
<a href="#" class="nav-link"><i class="fas fa-credit-card"></i> Pagos</a>
<a href="#" class="nav-link"><i class="fas fa-truck"></i> Pedidos</a>
<a href="#" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
<a href="#" class="nav-link"><i class="fas fa-file-invoice"></i> Facturación</a>
<a href="#" class="nav-link"><i class="fas fa-chart-bar"></i> Reportes</a>

</div>
</nav>

<!-- contenido -->

<div class="layout">

<div class="barra-resultados">

<input type="text" class="select-orden" placeholder="Buscar producto...">

<select class="select-orden">
<option>Nombre A-Z</option>
<option>Precio menor</option>
<option>Precio mayor</option>
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

renderProductos(productos);

</script>

</body>
</html>