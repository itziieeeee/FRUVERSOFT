<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Menú Administrativo</title>
    <link rel="stylesheet" href="<?= base_url('css/menusolo.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <style>
        /* ===== RESET Y BASE ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', 'Segoe UI', system-ui, -apple-system, sans-serif;
    background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
    min-height: 100vh;
    color: #1e293b;
}

.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px 32px;
}

/* ===== BARRA SUPERIOR ===== */
.top-bar {
    background: linear-gradient(135deg, #1e5631 0%, #2d6a3b 100%);
    border-radius: 28px;
    padding: 12px 28px;
    margin-bottom: 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

.logo-img {
    height: 75px;
    width: auto;
    object-fit: contain;
}




.admin-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.98);
    padding: 8px 20px;
    border-radius: 60px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease;
    text-decoration: none; /* Elimina el subrayado */
}

.admin-badge:hover {
    transform: translateY(-2px);
}

.admin-badge i {
    font-size: 1.4rem;
    color: #1e5631;
}

.admin-badge span {
    font-weight: 600;
    font-size: 0.9rem;
    color: #1e5631;
}

/* Búsqueda */
.search-wrapper {
    flex: 1;
    max-width: 360px;
    position: relative;
}

.search-wrapper input {
    width: 100%;
    padding: 12px 50px 12px 20px;
    border: none;
    border-radius: 60px;
    font-size: 0.9rem;
    background: rgba(255, 255, 255, 0.98);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.search-wrapper input:focus {
    outline: none;
    box-shadow: 0 0 0 4px rgba(241, 107, 26, 0.3);
}

.search-wrapper button {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: #f16b1a;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-wrapper button:hover {
    background: #e05a0c;
    transform: translateY(-50%) scale(1.08);
}

/* Botones de acción */
.action-buttons {
    display: flex;
    gap: 12px;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 60px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: white;
}

.action-btn:hover {
    background: #f16b1a;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(241, 107, 26, 0.35);
}

.logout-btn:hover {
    background: #dc2626;
}

/* Sección de bienvenida */
.welcome-section {
    margin-bottom: 32px;
    padding-left: 8px;
}

h1 {
    font-size: 2.2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #1e5631 0%, #f16b1a 100%);
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
    margin-bottom: 8px;
}

.welcome-subtitle {
    font-size: 1rem;
    color: #64748b;
    font-weight: 500;
}

/* ===== MÓDULOS ===== */
.modules-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
    margin-top: 16px;
}

.module-card {
    background: white;
    border-radius: 28px;
    padding: 28px 20px 24px;
    text-align: center;
    text-decoration: none;
    transition: all 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(0, 0, 0, 0.04);
    position: relative;
    overflow: hidden;
}

.module-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #1e5631, #f16b1a);
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.module-card:hover::before {
    transform: scaleX(1);
}

.module-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 35px rgba(0, 0, 0, 0.12);
}

.module-img {
    width: 100%;
    max-width: 100px;
    height: auto;
    margin-bottom: 16px;
    transition: all 0.4s ease;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.08));
}

.module-card:hover .module-img {
    transform: scale(1.08);
}

.module-title {
    display: block;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 6px;
    transition: color 0.3s ease;
}



.module-card:hover .module-title {
    color: #f16b1a;
}


/* Animación */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.module-card {
    animation: fadeInUp 0.5s ease backwards;
}

.module-card:nth-child(1) { animation-delay: 0.05s; }
.module-card:nth-child(2) { animation-delay: 0.1s; }
.module-card:nth-child(3) { animation-delay: 0.15s; }
.module-card:nth-child(4) { animation-delay: 0.2s; }
.module-card:nth-child(5) { animation-delay: 0.25s; }
.module-card:nth-child(6) { animation-delay: 0.3s; }

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .dashboard-container { padding: 20px 24px; }
    .modules-grid { gap: 22px; }
    h1 { font-size: 1.9rem; }
}

@media (max-width: 900px) {
    .modules-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .dashboard-container { padding: 16px; }
    
    .top-bar {
        flex-direction: column;
        padding: 20px;
        gap: 14px;
    }
    
    .logo-area { justify-content: center; }
    .search-wrapper { max-width: 100%; order: 3; }
    .action-buttons { justify-content: center; }
    
    h1 { font-size: 1.7rem; text-align: center; }
    .welcome-subtitle { text-align: center; }
}

@media (max-width: 580px) {
    .modules-grid {
        grid-template-columns: 1fr;
        max-width: 320px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .module-card {
        display: flex;
        align-items: center;
        gap: 20px;
        text-align: left;
        padding: 16px 20px;
    }
    
    .module-img { max-width: 60px; margin-bottom: 0; }
    .module-title { margin-bottom: 4px; }
    .card-icon-wrapper { flex-shrink: 0; }
}

@media (max-width: 480px) {
    .action-buttons { flex-direction: column; width: 100%; }
    .action-btn { justify-content: center; width: 100%; }
    .logo-img { height: 120px; }
    h1 { font-size: 1.5rem; }
}

/* Scrollbar */
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 10px; }
::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #1e5631, #2d6a3b); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #f16b1a; }

/* ===== RESUMEN DE HOY ===== */
.resumen-hoy { margin-bottom: 36px; }
.resumen-cab { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; padding-left: 8px; }
.resumen-cab h2 { font-size: 1.25rem; font-weight: 700; color: #1e5631; display: flex; align-items: center; gap: 10px; }
.resumen-cab h2 i { color: #f16b1a; }
.resumen-cab small { color: #64748b; font-size: 0.78rem; }
.resumen-kpis { display: grid; grid-template-columns: repeat(5, 1fr); gap: 18px; }
.rk {
    background: #fff; border-radius: 22px; padding: 18px 18px 16px;
    text-decoration: none; color: inherit;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(0, 0, 0, 0.04); border-top: 5px solid #1e5631;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex; flex-direction: column; gap: 4px;
}
.rk:hover { transform: translateY(-5px); box-shadow: 0 16px 28px rgba(0, 0, 0, 0.12); }
.rk.alerta  { border-top-color: #f59e0b; }
.rk.peligro { border-top-color: #dc2626; }
.rk.neutro  { border-top-color: #94a3b8; }
.rk .rk-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.rk .rk-t { font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; }
.rk .rk-i { width: 34px; height: 34px; border-radius: 50%; background: rgba(241, 107, 26, 0.12); color: #f16b1a; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0; }
.rk .rk-v { font-size: 1.55rem; font-weight: 700; line-height: 1.2; color: #1e293b; }
.rk .rk-v.pos { color: #15803d; }
.rk .rk-v.neg { color: #b91c1c; }
.rk .rk-s { font-size: 0.76rem; color: #64748b; }
.rk .rk-s b { color: #dc2626; }
.rk .rk-tag { align-self: flex-start; margin-top: 6px; padding: 3px 10px; border-radius: 50px; font-size: 0.68rem; font-weight: 700; background: #fef3c7; color: #b45309; }
@media (max-width: 1200px) { .resumen-kpis { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px)  { .resumen-kpis { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px)  { .resumen-kpis { grid-template-columns: 1fr; } }
    </style>

<div class="dashboard-container">
    <!-- BARRA SUPERIOR MODERNA -->
    <div class="top-bar">
        <img src="<?= base_url('img/LOGO1.png') ?>" class="logo-img" alt="Logo">
        
        <div class="logo-area">        
            <div class="admin-badge">
                <i class="fa-solid fa-circle-user"></i> 
                <span>Administrador</span>
            </div>
            <?= view('partials/admin_alertas') ?>
        </div>
            <a href="<?= base_url('admin/avisos') ?>" class="admin-badge">
            <i class="fas fa-bullhorn"></i> <span>Avisos</span>
        </a>
            
        </a>
   
        <div class="search-wrapper">
            <input type="text" placeholder="Buscar módulo, producto o cliente...">
            <button aria-label="Buscar"><i class="fas fa-search"></i></button>
        </div>

        <div class="action-buttons">
            
        
            <a href="<?= base_url('logout') ?>" class="action-btn logout-btn">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>
    </div>

    <div class="welcome-section">
        <h2>¡Bienvenido de vuelta!</h2>
       
    </div>

    <!-- RESUMEN DE HOY -->
    <section class="resumen-hoy" id="resumenHoy">
        <div class="resumen-cab">
            <h2><i class="fas fa-gauge-high"></i> Resumen de hoy</h2>
            <small id="resumenHora"></small>
        </div>
        <div class="resumen-kpis" id="resumenKpis"></div>
    </section>

    <!-- MÓDULOS PRINCIPALES -->
    <div class="modules-grid">
        <a href="<?= base_url('pantalla_inventario') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/INVENTARIO1.png') ?>" alt="Inventario" class="module-img">
            </div>
            <span class="module-title">Inventario</span>
        </a>

        <a href="pantalla_ventas" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/VENTA.png') ?>" alt="Ventas" class="module-img">
            </div>
            <span class="module-title">Ventas</span>
        </a>

        <a href="pantalla_pedidos" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/PEDIDOS.png') ?>" alt="Pedidos" class="module-img">
            </div>
            <span class="module-title">Pedidos</span>
        </a>

        <a href="<?= base_url('pantalla_clientes') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/CLIENTES.png') ?>" alt="Clientes" class="module-img">
            </div>
            <span class="module-title">Clientes</span>
        </a>

        <a href="pantalla_repartidores" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/REPARTIDORES.png') ?>" alt="Repartidores" class="module-img">
            </div>
            <span class="module-title">Repartidores</span>
        </a>

        <a href="pantalla_productos" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/PRODUCTOS.png') ?>" alt="Productos" class="module-img">
            </div>
            <span class="module-title">Productos</span>
            
        </a>

        <a href="<?= base_url('costos') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/REPORTES.png') ?>" alt="Costos y ganancias" class="module-img">
            </div>
            <span class="module-title">Costos y ganancias</span>
        </a>

        <a href="<?= base_url('cobranza') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/PAGOS1.png') ?>" alt="Cobranza de créditos" class="module-img">
            </div>
            <span class="module-title">Cobranza de créditos</span>
        </a>

        <a href="<?= base_url('corte') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/PRECIOS1.png') ?>" alt="Corte de caja" class="module-img">
            </div>
            <span class="module-title">Corte de caja</span>
        </a>

        <a href="<?= base_url('devoluciones') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/PEDIDOSPE.png') ?>" alt="Devoluciones" class="module-img">
            </div>
            <span class="module-title">Devoluciones</span>
        </a>

        <a href="<?= base_url('auditoria') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/FAC.png') ?>" alt="Historial de cambios" class="module-img">
            </div>
            <span class="module-title">Historial de cambios</span>
        </a>

        <a href="<?= base_url('seguimiento') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/RUTAS.png') ?>" alt="Seguimiento repartidor" class="module-img">
            </div>
            <span class="module-title">Seguimiento repartidor</span>
        </a>

        <a href="<?= base_url('reportes') ?>" class="module-card">
            <div class="card-icon-wrapper">
                <img src="<?= base_url('img/REPORTES.png') ?>" alt="Reportes de ventas" class="module-img">
            </div>
            <span class="module-title">Reportes de ventas</span>
        </a>
    </div>
</div>

<script>
(function () {
    // Los datos de la primera carga vienen del servidor; después se refrescan solos cada 30 segundos
    let D = <?= json_encode($resumen ?? null, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const URL_DATOS = "<?= base_url('dashboard/datos') ?>";
    const U = {
        corte:     "<?= base_url('corte') ?>",
        cobranza:  "<?= base_url('cobranza') ?>",
        alertas:   "<?= base_url('alertas') ?>",
        salidas:   "<?= base_url('salidas') ?>"
    };

    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const dinero = n => (n < 0 ? '-' : '') + '$' + Math.abs(Number(n || 0)).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const plural = (n, uno, varios) => n + ' ' + (n === 1 ? uno : varios);

    function tarjeta(o) {
        return `<a class="rk ${o.tono || ''}" href="${o.href}">
            <div class="rk-top"><span class="rk-t">${o.titulo}</span><span class="rk-i"><i class="fas ${o.icono}"></i></span></div>
            <div class="rk-v ${o.claseValor || ''}">${o.valor}</div>
            <div class="rk-s">${o.sub}</div>
            ${o.etiqueta ? `<span class="rk-tag">${o.etiqueta}</span>` : ''}
        </a>`;
    }
    const noDisponible = (titulo, icono, href) =>
        tarjeta({titulo, icono, href, valor: '—', sub: 'No disponible por ahora', tono: 'neutro'});

    function pintarTarjetas() {
        const v = D && D.ventas, c = D && D.por_cobrar, k = D && D.criticos, e = D && D.entregas, t = D && D.corte;
        const html = [];

        html.push(v ? tarjeta({
            titulo: 'Ventas de hoy', icono: 'fa-sack-dollar', href: U.corte, valor: dinero(v.total),
            sub: `${plural(v.n, 'pedido', 'pedidos')} · cobrado ${dinero(v.cobrado)}`
        }) : noDisponible('Ventas de hoy', 'fa-sack-dollar', U.corte));

        html.push(c ? tarjeta({
            titulo: 'Por cobrar', icono: 'fa-hand-holding-dollar', href: U.cobranza, valor: dinero(c.total),
            tono: c.vencido > 0 ? 'peligro' : '',
            sub: c.vencido > 0 ? `<b>${dinero(c.vencido)}</b> vencido · ${plural(c.vencidos, 'cliente', 'clientes')}` : `${plural(c.clientes, 'cliente', 'clientes')} con saldo, nada vencido`
        }) : noDisponible('Por cobrar', 'fa-hand-holding-dollar', U.cobranza));

        html.push(k ? tarjeta({
            titulo: 'Productos críticos', icono: 'fa-triangle-exclamation', href: U.alertas, valor: k.criticos,
            tono: k.criticos > 0 ? 'peligro' : (k.atencion > 0 ? 'alerta' : ''),
            sub: (k.urgentes.length ? esc(k.urgentes.join(', ')) + ' · ' : '') + `${k.atencion} en atención`
        }) : noDisponible('Productos críticos', 'fa-triangle-exclamation', U.alertas));

        html.push(e ? tarjeta({
            titulo: 'Entregas en curso', icono: 'fa-truck-fast', href: U.salidas, valor: e.pendientes,
            tono: e.sin_salir > 0 ? 'alerta' : '',
            sub: `${plural(e.en_ruta, 'repartidor en ruta', 'repartidores en ruta')} · ${e.entregadas} entregadas hoy` + (e.fallidas ? ` · <b>${e.fallidas} no entregadas</b>` : ''),
            etiqueta: e.sin_salir > 0 ? `${plural(e.sin_salir, 'pedido listo sin salir', 'pedidos listos sin salir')}` : ''
        }) : noDisponible('Entregas en curso', 'fa-truck-fast', U.salidas));

        if (t) {
            const u = t.ultimo;
            const dif = u ? u.diferencia : 0;
            html.push(tarjeta({
                titulo: 'Último corte', icono: 'fa-cash-register', href: U.corte,
                valor: u ? (dif > 0 ? '+' : '') + dinero(dif) : 'Sin cortes',
                claseValor: u ? (Math.abs(dif) < 0.005 ? 'pos' : (dif < 0 ? 'neg' : '')) : '',
                tono: u && Math.abs(dif) >= 0.005 ? (dif < 0 ? 'peligro' : 'alerta') : (u ? '' : 'neutro'),
                sub: u ? `${u.fecha} · ${Math.abs(dif) < 0.005 ? 'cuadró' : (dif < 0 ? 'faltó dinero' : 'sobró dinero')} (esperado ${dinero(u.esperado)})` : 'Todavía no se ha guardado ninguno',
                etiqueta: t.hoy_guardado ? '' : 'Hoy aún sin corte'
            }));
        } else {
            html.push(noDisponible('Último corte', 'fa-cash-register', U.corte));
        }

        document.getElementById('resumenKpis').innerHTML = html.join('');
        document.getElementById('resumenHora').textContent = D && D.generado ? 'Actualizado a las ' + D.generado : '';
    }

    async function refrescar() {
        if (document.hidden) return;
        try {
            const r = await fetch(URL_DATOS, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (!r.ok) return;
            const j = await r.json();
            if (j && j.success) { D = j.data; pintarTarjetas(); }
        } catch (e) { /* sin conexión o sesión vencida: se queda lo último que se mostró */ }
    }

    pintarTarjetas();
    setInterval(refrescar, 30000);
})();
</script>

</body>
</html>