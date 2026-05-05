<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Clientes</title>
    <style>
        :root {
            --verde: #1d4a27;
            --naranja: #f16b1a;
            --naranja-claro: #fff5f0;
            --fondo: #f0f2f5;
            --blanco: #ffffff;
            --gris-borde: #eee;
        }

        body { font-family: 'Segoe UI', sans-serif; background-color: var(--fondo); margin: 0; overflow: hidden; }

        header { background: var(--verde); padding: 15px 30px; display: flex; align-items: center; justify-content: space-between; }
        .logo-area img { height: 45px; }

        nav.menu-navegacion {
            background: var(--blanco);
            padding: 10px 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
            border-bottom: 1px solid var(--gris-borde);
            align-items: center;
        }

        .nav-item {
            text-decoration: none;
            color: #444;
            padding: 10px 15px;
            border-radius: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: 0.3s;
            border: 2px solid transparent;
            flex: 1;
            min-width: 150px;
            max-width: 180px;
            font-size: 14px;
            white-space: nowrap;
        }

        .nav-item i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .nav-item:hover {
            background: var(--naranja-claro);
            color: var(--naranja);
        }

        .nav-item.activo {
            background: var(--naranja-claro);
            color: var(--naranja);
            border: 2px solid var(--naranja);
        }
        
        .main-container { display: flex; height: calc(100vh - 140px); padding: 20px; gap: 20px; }

        .sidebar {
            width: 350px;
            background: var(--blanco);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .sidebar-header { padding: 20px; border-bottom: 1px solid #eee; }
        .buscar-box {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            outline: none;
        }
        .buscar-box:focus { border-color: var(--naranja); }

        .lista-scroll { flex: 1; overflow-y: auto; }
        .tipo-label { padding: 15px 20px 5px; font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px; }

        .cliente-card {
            margin: 5px 15px;
            padding: 15px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: 0.2s;
        }

        .cliente-card:hover { background: #f8f8f8; }
        .cliente-card.selected { background: var(--verde); color: white; }

        .panel-detalle {
            flex: 1;
            background: var(--blanco);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            overflow-y: auto;
        }

        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px; }
        .info-tile {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 15px;
            border-left: 5px solid var(--verde);
        }
        .info-tile span { display: block; color: #888; font-size: 13px; margin-bottom: 5px; }
        .info-tile strong { font-size: 16px; color: #333; }

        .tabla-fruver { width: 100%; border-collapse: collapse; margin-top: 25px; }
        .tabla-fruver th { background: #f0f0f0; padding: 12px; text-align: left; color: #666; font-size: 14px; }
        .tabla-fruver td { padding: 15px 12px; border-bottom: 1px solid #eee; font-size: 15px; }

        .placeholder { height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #bbb; }
    </style>
</head>
<body>

<header>
    <div class="logo-area"><img src="<?= base_url('img/LOGO1.png') ?>"></div>
</header>

<nav class="menu-navegacion">
    <a href="<?= site_url('pantalla_ventas') ?>" class="nav-item">
        <i class="fas fa-tag"></i> <span>Ventas</span></a>
    <a href="<?= site_url('pantalla_pedidos') ?>" class="nav-item">
        <i class="fas fa-truck"></i> <span>Pedidos</span></a>
    <a href="<?= site_url('pantalla_inventario') ?>" class="nav-item">
        <i class="fas fa-boxes"></i> <span>Inventario</span></a>
    <a href="<?= site_url('pantalla_clientes') ?>" class="nav-item activo">
        <i class="fas fa-users"></i> <span>Clientes</span></a>
    <a href="<?= site_url('pantalla_repartidores') ?>" class="nav-item">
        <i class="fas fa-motorcycle"></i> <span>Repartidores</span></a>
    <a href="<?= site_url('pantalla_productos') ?>" class="nav-item">
        <i class="fas fa-apple-alt"></i> <span>Productos</span></a>
</nav>
<div class="main-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:15px;">
                <h2 style="margin:0; color:var(--verde);">Clientes</h2>
                <a href="<?= base_url('pantalla_rcliente') ?>" style="color:var(--naranja); text-decoration:none; font-weight:bold;">+ Nuevo</a>
            </div>
            <input type="text" class="buscar-box" id="busc" placeholder="Buscar por nombre..." onkeyup="filtrar()">
        </div>

        <div class="lista-scroll">
            <div class="tipo-label">Mayoreo</div>
            <?php foreach($lista_clientes as $c): if($c['tipo_cliente'] == 'mayoreo'): ?>
                <div class="cliente-card" onclick="verCliente(<?= $c['id_cliente'] ?>, this)">
                    <strong><?= $c['nombre'] ?></strong>
                    <i class="fas fa-chevron-right"></i>
                </div>
            <?php endif; endforeach; ?>

            <div class="tipo-label">Menudeo</div>
            <?php foreach($lista_clientes as $c): if($c['tipo_cliente'] == 'menudeo'): ?>
                <div class="cliente-card" onclick="verCliente(<?= $c['id_cliente'] ?>, this)">
                    <strong><?= $c['nombre'] ?></strong>
                    <i class="fas fa-chevron-right"></i>
                </div>
            <?php endif; endforeach; ?>
        </div>
    </aside>

    <main class="panel-detalle">
        <div id="vacio" class="placeholder">
            <i class="fas fa-address-book fa-4x"></i>
            <p>Selecciona un cliente para ver información</p>
        </div>

        <div id="contenido" style="display:none;">
            <h1 id="det-nom" style="color:var(--verde); margin:0;"></h1>
            
            <div class="info-grid">
                <div class="info-tile"><span>RFC</span><strong id="det-rfc">-</strong></div>
                <div class="info-tile"><span>Teléfono</span><strong id="det-con">-</strong></div>
                <div class="info-tile"><span>Dirección</span><strong id="det-dir">-</strong></div>
            </div>

            <h3 style="margin-top:40px;"><i class="fas fa-shopping-basket"></i> Últimas Compras</h3>
            <table class="tabla-fruver">
                <thead>
                    <tr><th>Fecha</th><th>Monto</th><th>Acción</th></tr>
                </thead>
                <tbody id="det-hist"></tbody>
            </table>
        </div>
    </main>
</div>

<script>

function verCliente(id, el) {
    document.querySelectorAll('.cliente-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');

    
    fetch("/FRUVERSOFT/public/index.php/clientes/detalle/" + id)
    .then(r => {
        if (!r.ok) throw new Error("Error " + r.status);
        return r.json();
    })
    .then(data => {
        if (!data.cliente) return;
        
        document.getElementById('vacio').style.display = 'none';
        document.getElementById('contenido').style.display = 'block';

        const nom = data.cliente.nombre + " " + (data.cliente.apellido_paterno || "") + " " + (data.cliente.apellido_materno || "");
        document.getElementById('det-nom').innerText = nom;
        document.getElementById('det-rfc').innerText = data.cliente.rfc || 'N/A';
        document.getElementById('det-con').innerText = data.cliente.tel || 'N/A';
       const dir = [
    data.cliente.calle,
    data.cliente.numero,
    data.cliente.colonia,
    data.cliente.municipio,
    data.cliente.estado
].filter(Boolean).join(', ');

document.getElementById('det-dir').innerText = dir || 'N/A';

        let h = "";
        if (!data.historial || data.historial.length === 0) {
            h = "<tr><td colspan='3' style='text-align:center;'>Sin historial</td></tr>";
        } else {
            data.historial.forEach(row => {
                h += `<tr><td>${row.fecha || '---'}</td><td>$${row.total || '0.00'}</td><td>Ver</td></tr>`;
            });
        }
        document.getElementById('det-hist').innerHTML = h;
    })
    .catch(err => {
        console.error(err);
        alert("No se pudo conectar. Revisa la consola con F12.");
    });
}
</script>

<script>
function filtrar() {
    let filtro = document.getElementById('busc').value.toLowerCase();
    let tarjetas = document.querySelectorAll('.cliente-card');

    tarjetas.forEach(card => {
        let nombre = card.querySelector('strong').innerText.toLowerCase();

        if (nombre.includes(filtro)) {
            card.style.display = "flex";
        } else {
            card.style.display = "none";
        }
    });
}
</script>
</body>
</html>