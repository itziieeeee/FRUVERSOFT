<!DOCTYPE html>
<html lang="en">
<head>
    <!--ESTA ES LA PANTALLA DE CLIENTES -->
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Clientes</title>
    <style>
        * {
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

        /* --- CABECERO SUPERIOR (AHORA MÁS COMPACTO) --- */
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
        .logo-area h1 {
            color: white;
            font-size: 1.5rem;
            font-weight: 500;
            border-left: 2px solid rgba(255,255,255,0.3);
            padding-left: 16px;
            margin: 0;
            display: none; /* Oculto para no repetir, lo pondremos arriba de las tarjetas */
        }

        /* Buscador más compacto */
        .buscador {
            background: white;
            border-radius: 40px;
            padding: 3px 3px 3px 18px;
            display: flex;
            align-items: center;
            flex: 0 1 300px; /* Tamaño fijo pero puede encogerse */
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

        /* Acciones de usuario (Admin, Notif, Salir) */
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
    gap: 12px;
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

        /* Estilo para el link activo (opcional, por si quieres marcar "Clientes" más tarde) */
        .nav-link.activo {
            background: #1d4a27;
            color: white;
            border-color: #1d4a27;
        }
        .nav-link.activo i {
            color: white;
        }

        /* Título de la sección (Clientes) */
        .titulo-seccion {
            padding: 16px 24px 8px 24px;
            font-size: 1.8rem;
            font-weight: 600;
            color: #1d4a27;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .titulo-seccion i {
            color: #f16b1a;
            background: #fff1e0;
            padding: 8px;
            border-radius: 16px;
            font-size: 1.6rem;
        }

        /* Contenedor de las 3 tarjetas o tablas */
        .tarjeta {
            display: grid;
            grid-template-columns: 1.2fr 1.3fr 1.3fr;
            gap: 36px;
            padding: 0 14px 10px 20px; /* Ajuste de padding */
            height: calc(90vh - 140px); /* Ajuste de altura para el nuevo menú */
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
        
        /* Ocultar tarjetas de detalle al inicio */
        #tarjetaDatos, #tarjetaHistorial {
            display: none;
        }

        /* Hover sobre filas iterables de clientes */
        .fila-cliente {
            cursor: pointer;
            transition: background 0.2s;
        }
        .fila-cliente:hover {
            background: #eef5ec;
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

        .fondototal {
            background: #f16b1a10;
            color: #b84500;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.8rem;
            border: 1px solid #f16b1a60;
            white-space: nowrap;
        }

        .tag-cliente {
            background: #1d4a27;
            color: white;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .info-cliente-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 12px;
            background: #f5faf4;
            padding: 12px;
            border-radius: 20px;
            margin-bottom: 16px;
            font-size: 0.85rem;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 0.7rem;
            color: #4d6b53;
            text-transform: uppercase;
        }
        .info-value {
            font-weight: 600;
            color: #1d3a24;
            word-break: break-word;
        }

        .tipoc {
            display: flex;
            gap: 8px;
            margin: 12px 0;
        }
        .tipoc button {
            flex: 1;
            background: #eef5ec;
            border: 1.5px solid #bad2b4;
            border-radius: 30px;
            padding: 8px 6px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #1a4a27;
            cursor: pointer;
        }
        .tipoc button.activo {
            background: #1d4a27;
            border-color: #1d4a27;
            color: white;
        }

        .historiallist {
            display: grid;
            grid-template-columns: 70px 60px 50px 1fr 70px 80px;
            gap: 4px;
            padding: 8px 0;
            border-bottom: 1px dashed #c5ddc0;
            font-size: 0.75rem;
            align-items: center;
        }
        .historial-header {
            font-weight: 700;
            color: #20612e;
            border-bottom: 2px solid #b1d2aa;
            padding-bottom: 6px;
            margin-bottom: 4px;
        }

        .status {
            padding: 3px 8px;
            border-radius: 40px;
            text-align: center;
            font-weight: 600;
            font-size: 0.7rem;
        }
        .status.entregado { background: #daf1da; color: #156b2c; }
        .status.enviado { background: #ffe5cc; color: #b65000; }
        .status.pendiente { background: #fff0c0; color: #866e1c; }

        .credito-mini {
            background: #eef6ec;
            border-radius: 20px;
            padding: 14px;
            margin-top: 12px;
        }
        .nlimite {
            font-weight: 800;
            font-size: 1.4rem;
            color: #1d632d;
        }
        .barra-credito {
            background: #cfdecb;
            border-radius: 30px;
            height: 24px;
            margin: 10px 0;
            display: flex;
            overflow: hidden;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .barrautilizada {
            background: #1f8b4c;
            color: white;
            display: flex;
            align-items: center;
            padding-left: 12px;
        }
        .barradisponible {
            background: #fed7b0;
            color: #633f00;
            display: flex;
            align-items: center;
            padding-left: 12px;
        }
        .li {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .botonclienten {
            background: #f16b1a;
            color: white;
            border: none;
            border-radius: 40px;
            padding: 8px 14px;
            font-weight: 600;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
            text-decoration: none;
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
    <!-- Barra superior con logo, buscador y acciones de usuario -->
    <div class="barra-superior">
        <div class="logo-area">
            <img src="<?= base_url('img/LOGO1.png') ?>" alt="Logo" width="140">
            <!-- El título "Clientes" lo movemos arriba de las tarjetas -->
        </div>

        <div class="buscador">
            <input type="text" id="inputBuscador" placeholder="Buscar cliente, pedido...">
            <button><i class="fas fa-search"></i></button>
        </div>

        <div class="user-actions">
            <a href="#" class="btn-user"><i class="fas fa-user-shield"></i> <span class="hide-mobile">Admin</span></a>
            <a href="#" class="btn-user"><i class="fas fa-bell"></i> <span class="hide-mobile">Notificaciones</span></a>
            <a href="<?= base_url('menusolo') ?>" class="btn-user"><i class="fas fa-sign-out-alt"></i> <span class="hide-mobile">Regresar</span></a>
        </div>
    </div>

    <!-- NUEVO: Menú de navegación principal (6 botones) -->
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

    <!-- Título de la sección actual 
    <div class="titulo-seccion">
        <i class="fas fa-users"></i> Clientes
    </div>-->

    <!-- Contenedor de las 3 tarjetas (sin cambios en su contenido) -->
    <div class="tarjeta">

        <!-- parte uno clientes de los 2 tipos -->
        <div class="card">
            <div class="cabezacard">
                <i class="fas fa-users"></i>
                <h2>Clientes</h2>
                <span class="fondo">
                    <?= isset($clientes) ? count($clientes) : 0 ?> registros
                </span>
                <a href="<?= base_url('pantalla_rcliente') ?>" class="botonclienten">
    <i class="fas fa-plus-circle"></i> Nuevo
</a>
            </div>
            <div class="scroll-area">
                <!-- Mayoreo -->
                <div style="margin-bottom: 12px;">
                    <h3 style="font-size:0.9rem; color:#22662c; margin-bottom:4px;">Mayoreo</h3>
                    <table class="minit">
                        <thead><tr><th>ID</th><th>Cliente</th><th>Etiqueta</th></tr></thead>
                        <tbody>
                            <!--Se quitaron los datos por defecto-->
                            <?php if (isset($clientes)): ?>
                                <?php foreach ($clientes as $c): ?>
                                    <?php if (strtolower($c['tipo_cliente']) == 'mayoreo' || $c['tipo_cliente'] == ''): ?>
                                    <tr class="fila-cliente" data-id="<?= $c['id_cliente'] ?>">
                                        <td>#<?= $c['id_cliente'] ?></td>
                                        <td><?= $c['nombre'] ?> <?= $c['apellido_paterno'] ?> <?= $c['apellido_materno'] ?></td>
                                        <td><span class="fondototal">Mayoreo</span></td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3">No hay clientes de mayoreo registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Menudeo -->
                <div>
                    <h3 style="font-size:0.9rem; color:#22662c; margin:8px 0 4px;">Menudeo</h3>
                    <table class="minit">
                        <thead><tr><th>ID</th><th>Cliente</th><th>Etiqueta</th></tr></thead>
                        <tbody>
                            <?php if (isset($clientes)): ?>
                                <?php foreach ($clientes as $c): ?>
                                    <?php if (strtolower($c['tipo_cliente']) == 'menudeo'): ?>
                                    <tr class="fila-cliente" data-id="<?= $c['id_cliente'] ?>">
                                        <td>#<?= $c['id_cliente'] ?></td>
                                        <td><?= $c['nombre'] ?> <?= $c['apellido_paterno'] ?> <?= $c['apellido_materno'] ?></td>
                                        <td><span class="fondototal">Menudeo</span></td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3">No hay clientes de menudeo registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- DATOS DEL CLIENTE (Oculto inicialmente, se muestra con AJAX) -->
        <div class="card" id="tarjetaDatos">
            <div class="cabezacard">
                <i class="fas fa-id-card"></i>
                <h2>Datos del cliente</h2>
            </div>
            <div class="scroll-area">
                <div class="info-cliente-grid">
                    <div class="info-item"><span class="info-label">Nombre</span><span class="info-value" id="dtNombre">---</span></div>
                    <div class="info-item"><span class="info-label">RFC</span><span class="info-value" id="dtRFC">---</span></div>
                    <div class="info-item"><span class="info-label">Tipo</span><span class="info-value" id="dtTipo">---</span></div>
                    <!-- Nota: El modelo actual no trae dirección ni correo, rellenamos con placeholder mientras se agregan esas columnas -->
                    <div class="info-item"><span class="info-label">Contacto</span><span class="info-value">Sin asignar</span></div>
                </div>

                <div style="margin:8px 0 12px; display:flex; align-items:center; gap:10px;">
                    <span class="tag-cliente"><i class="fas fa-store"></i> Crédito / Mayoreo</span>
                    <span style="font-size:0.7rem; background:#f3f9f0; padding:4px 10px; border-radius:30px;">límite $50k</span>
                </div>

                <div class="tipoc">
                    <button class="activo"><i class="fas fa-check-circle"></i> Contado/Menudeo</button>
                    <button><i class="fas fa-credit-card"></i> Crédito/Mayoreo</button>
                </div>

                <div class="credito-mini">
                    <div class="li">
                        <span>Límite autorizado</span>
                        <span class="nlimite">$50,000</span>
                    </div>
                    <div class="barra-credito">
                        <div class="barrautilizada" style="width:25%;">$12.5k</div>
                        <div class="barradisponible" style="width:75%;">$37.5k</div>
                    </div>
                    <div class="li">
                        <span><i class="fas fa-circle" style="color:#1f8b4c;"></i> Usado: $12,500</span>
                        <span><i class="fas fa-circle" style="color:#f5a35c;"></i> Disponible: $37,500</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- historial -->
        <div class="card" id="tarjetaHistorial">
            <div class="cabezacard">
                <i class="fas fa-history"></i>
                <h2>Historial de compras</h2>
                <span class="fondo" id="historialConteo">0 movimientos</span>
            </div>
            <div class="scroll-area">
                <div class="historiallist historial-header">
                    <div>Ref</div><div>Fecha</div><div style="grid-column: span 2;">Estatus</div><div>Monto</div><div>Ver</div>
                </div>

                <div id="contenedorHistorial">
                    <!-- Los resultados de AJAX irán aquí -->
                    <div style="text-align:center; padding: 20px; font-size: 0.8rem; color: #666;">
                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                    </div>
                </div>

                <div style="margin-top:16px; background:#fcf9f0; border-radius:16px; padding:10px; font-size:0.8rem;">
                    <i class="fas fa-info-circle" style="color:#f16b1a;"></i> Última compra: 23/01/26 · $11,500
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT DEL BUSCADOR y AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('inputBuscador');
            // Seleccionar todas las filas de clientes excepto los encabezados ('th')
            const filasClientes = document.querySelectorAll('.minit tbody tr.fila-cliente');

            // 1. FUNCION DEL BUSCADOR RÁPIDO
            if(input) {
                input.addEventListener('input', function() {
                    const filtro = this.value.toLowerCase().trim();
                    let filasVisibles = 0;
                    let ultimaFilaVisible = null;

                    filasClientes.forEach(fila => {
                        const textoFila = fila.textContent.toLowerCase();
                        if (textoFila.includes(filtro)) {
                            fila.style.display = '';
                            filasVisibles++;
                            ultimaFilaVisible = fila;
                        } else {
                            fila.style.display = 'none';
                        }
                    });

                    // Si hay filtro escrito y quedó exactamente 1 cliente visible, lo auto-seleccionamos
                    if (filtro.length > 0 && filasVisibles === 1 && ultimaFilaVisible) {
                         $(ultimaFilaVisible).trigger('click');
                    }
                });
            }

            // 2. FUNCIÓN AJAX MAESTRO-DETALLE (Al hacer clic en un cliente)
            $('.fila-cliente').on('click', function() {
                const idCliente = $(this).data('id');
                
                // Efecto visual de selección en la lista
                $('.fila-cliente').css('border-left', 'none').css('background', '');
                $(this).css('border-left', '4px solid #f16b1a').css('background', '#eef5ec');

                // Mostrar tarjetas de detalle que estaban ocultas
                $('#tarjetaDatos').show();
                $('#tarjetaHistorial').show();

                // Petición AJAX al servidor CodeIgniter
                $.ajax({
                    url: '<?= base_url('clientes/get_detalle') ?>',
                    type: 'POST',
                    data: { id_cliente: idCliente },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'success') {
                            const c = response.cliente;
                            const pedidos = response.pedidos;

                            // Actualizar Tarjeta 2: Datos
                            $('#dtNombre').text(c.nombre + ' ' + (c.apellido_paterno || '') + ' ' + (c.apellido_materno || ''));
                            $('#dtRFC').text(c.rfc ? c.rfc : 'N/A');
                            $('#dtTipo').text(c.tipo_cliente ? c.tipo_cliente : 'N/A');

                            // Actualizar Tarjeta 3: Historial
                            let htmlHistorial = '';
                            if(pedidos.length > 0) {
                                $('#historialConteo').text(pedidos.length + ' movimientos');
                                
                                pedidos.forEach(p => {
                                    // Determinar badge según estado
                                    let badge = 'pendiente';
                                    if(p.estatus == 'Entregado') badge = 'entregado';
                                    else if(p.estatus == 'Enviado') badge = 'enviado';

                                    htmlHistorial += `
                                    <div class="historiallist">
                                        <div>#${p.id_pedido}</div>
                                        <div>${p.fecha.split(' ')[0]}</div>
                                        <div style="grid-column: span 2;"><span class="status ${badge}">${p.estatus || 'Pendiente'}</span></div>
                                        <div style="font-weight:bold; color:#1d632d;">$${p.total}</div>
                                        <div><a href="#" style="color:#f16b1a;"><i class="fas fa-eye"></i></a></div>
                                    </div>`;
                                });
                            } else {
                                $('#historialConteo').text('0 movimientos');
                                htmlHistorial = '<div style="padding:15px; text-align:center; font-size:0.85rem;">Este cliente aún no tiene pedidos registrados en el sistema.</div>';
                            }
                            
                            $('#contenedorHistorial').html(htmlHistorial);
                        } else {
                            alert('Error: ' + response.msg);
                        }
                    },
                    error: function() {
                        alert('Error al conectar con el servidor.');
                    }
                });
            });
        });
    </script>