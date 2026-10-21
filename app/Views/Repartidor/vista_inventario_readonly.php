<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    
    :root { --primary-green: #1d4a27; --primary-orange: #f16b1a; --gray-light: #f8f9fa; --gray-border: #e0e0e0; }
    body { font-family: 'Inter', sans-serif; background: #f5f7fa; margin: 0; padding: 0; }
    .barra-superior { display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 2rem; background: var(--primary-green); }
    .barra-superior img { height: 60px; }
    .btn-user { color: white; text-decoration: none; padding: 0.5rem 1.2rem; background: rgba(255,255,255,0.15); border-radius: 50px; font-size: 0.85rem; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; }
    .menu-navegacion { background: white; padding: 0.8rem; display: flex; justify-content: center; gap: 1rem; border-bottom: 1px solid var(--gray-border); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .nav-link { padding: 0.6rem 1.2rem; color: #333; text-decoration: none; font-weight: 600; border-radius: 40px; font-size: 0.9rem; }
    .nav-link.activo { color: var(--primary-orange); background: rgba(241, 107, 26, 0.08); }
    .contenido-principal { margin: 2rem; background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .productos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1.5rem; }
    .producto-card { border: 1px solid var(--gray-border); border-radius: 1rem; padding: 1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.04); }
    .producto-nombre { font-size: 1.1rem; font-weight: 700; color: var(--primary-green); margin-bottom: 0.5rem; }
    .producto-descripcion { font-size: 0.8rem; color: #666; background: #fafbfd; padding: 8px; border-radius: 8px; margin-bottom: 1rem; }
    .inventario-panel { background: var(--gray-light); border-radius: 0.8rem; padding: 0.8rem; border-left: 3px solid var(--primary-orange); }
    .inventario-item { display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px; }
    .disponible { color: #1e7e34; font-weight: 700; font-size: 1rem; }
    .buscador-rapido { width: 100%; padding: 12px; border: 2px solid var(--gray-border); border-radius: 10px; font-size: 1rem; margin-bottom: 1rem; outline: none; }
    .buscador-rapido:focus { border-color: var(--primary-orange); }
</style>
</head>
<body>

<header class="barra-superior">
    <div class="logo-area">
        <img src="<?= base_url('img/LOGO1.png') ?>" alt="FRUVER">
    </div>
    <div class="user-actions">
        <a href="<?= base_url('repartidor/panel') ?>" class="btn-user"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
    </div>
</header>

<nav class="menu-navegacion">
    <a href="<?= base_url('repartidor/panel') ?>" class="nav-link"><i class="fas fa-home"></i> Inicio</a>
    <a href="<?= base_url('repartidor/pedidos') ?>" class="nav-link"><i class="fas fa-truck"></i> Mis Pedidos</a>
    <a href="<?= base_url('repartidor/inventario') ?>" class="nav-link activo"><i class="fas fa-boxes"></i> Inventario</a>
</nav>

<div class="contenido-principal">
    <h2 style="color: #1d4a27; margin-bottom: 1rem;"><i class="fas fa-boxes"></i> Consulta de Inventario</h2>
    
    <input type="text" id="buscador" class="buscador-rapido" placeholder="Escribe para filtrar productos rápidamente...">

    <div class="productos-grid" id="gridProductos">
        <?php foreach($productos as $p): 
            $existencias_totales    = $p['existencias_totales']    ?? 0;
            $existencias_bloqueadas = $p['existencias_bloqueadas'] ?? 0;
            $existencias_venta      = $existencias_totales - $existencias_bloqueadas;
        ?>
        <div class="producto-card">
            <div class="producto-nombre"><?= htmlspecialchars($p['nombre']) ?></div>
            <div class="producto-descripcion"><?= htmlspecialchars($p['descripcion'] ?? 'Sin descripción') ?></div>
            <div class="inventario-panel">
                <div class="inventario-item">
                    <span><i class="fas fa-check-circle" style="color: #1d4a27;"></i> Disponibles para venta:</span>
                    <span class="disponible"><?= $existencias_venta ?> <?= htmlspecialchars($p['unidad_venta'] ?? 'Unidad') ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    // Buscador rápido
    document.getElementById('buscador').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        const cards = document.querySelectorAll('.producto-card');
        cards.forEach(card => {
            const nombre = card.querySelector('.producto-nombre').textContent.toLowerCase();
            card.style.display = nombre.includes(term) ? 'block' : 'none';
        });
    });
</script>
</body>
</html>