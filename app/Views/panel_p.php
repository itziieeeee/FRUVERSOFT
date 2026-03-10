<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Fruversoft - Panel Vendedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-store"></i> Fruversoft: Buscador de Productos</h4>
        </div>
        <div class="card-body">
            
            <form action="<?= base_url('buscar_producto') ?>" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="query" class="form-control form-control-lg" 
                           placeholder="Escribe el nombre del producto (ej. Manzana)..." 
                           value="<?= $termino ?? '' ?>">
                    <button class="btn btn-success" type="submit">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>

            <hr>

            <?php if (!empty($productos)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Existencia</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td><strong><?= $p['nombre'] ?></strong></td>
                                    <td>$<?= number_format($p['precio'], 2) ?></td>
                                    <td><?= $p['stock'] ?> pz/kg</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Vender</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (isset($termino) && $termino != ""): ?>
                <div class="alert alert-warning text-center">
                    No se encontraron productos que coincidan con "<strong><?= $termino ?></strong>"
                </div>
            <?php else: ?>
                <p class="text-muted text-center">Ingresa un nombre arriba para empezar a buscar.</p>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>