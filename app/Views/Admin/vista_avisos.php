<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVERSOFT · Gestión de Avisos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #1d4a27;
            --primary-orange: #f16b1a;
            --light-green: #e8f3e6;
        }
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .navbar-fruver { background-color: var(--primary-green); color: white; padding: 1rem 2rem; }
        .card-fruver { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .btn-fruver { background-color: var(--primary-orange); color: white; font-weight: 600; }
        .btn-fruver:hover { background-color: #d55a12; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-fruver d-flex justify-content-between align-items-center">
    <a href="<?= base_url('menusolo') ?>" class="text-white text-decoration-none fw-bold fs-5">
        <i class="fas fa-arrow-left me-2"></i> Volver al Menú Admin
    </a>
    <span><i class="fas fa-bullhorn me-2"></i> Gestión de Avisos y Anuncios</span>
</nav>

<div class="container py-5">
    
    <?php if(session()->has('mensaje')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session('mensaje') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Formulario para crear aviso -->
        <div class="col-lg-4">
            <div class="card card-fruver p-4">
                <h4 class="fw-bold mb-3" style="color: var(--primary-green);">
                    <i class="fas fa-plus-circle"></i> Nuevo Aviso
                </h4>
                <form action="<?= base_url('admin/avisos/guardar') ?>" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título del aviso</label>
                        <input type="text" name="titulo" class="form-control" placeholder="Ej: Mantenimiento de plataforma" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mensaje / Asunto</label>
                        <textarea name="mensaje" class="form-control" rows="4" placeholder="Escribe el contenido del anuncio..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rol Destino</label>
                        <select name="rol_destino" class="form-select" required>
                            <option value="cliente">Clientes</option>
                            <option value="repartidor">Repartidores</option>
                            <option value="todos">Todos los usuarios</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Duración (en días)</label>
                        <input type="number" name="dias_duracion" class="form-control" min="1" value="7" placeholder="Ej: 7 días">
                        <div class="form-text">Días que el aviso permanecerá visible.</div>
                    </div>

                    <button type="submit" class="btn btn-fruver w-100 py-2">
                        <i class="fas fa-paper-plane"></i> Publicar Aviso
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabla de avisos publicados -->
        <div class="col-lg-8">
            <div class="card card-fruver p-4">
                <h4 class="fw-bold mb-3" style="color: var(--primary-green);">
                    <i class="fas fa-list-alt"></i> Avisos Publicados
                </h4>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Título / Mensaje</th>
                                <th>Destino</th>
                                <th>Expiración</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($avisos)): ?>
                                <?php foreach($avisos as $aviso): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($aviso['titulo']) ?></strong>
                                            <p class="text-muted small mb-0"><?= esc($aviso['mensaje']) ?></p>
                                        </td>
                                        <td>
                                            <span class="badge bg-success text-uppercase"><?= esc($aviso['rol_destino']) ?></span>
                                        </td>
                                        <td>
                                            <?= $aviso['fecha_expiracion'] ? esc($aviso['fecha_expiracion']) : 'Permanente' ?>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('admin/avisos/eliminar/' . $aviso['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Estás seguro de eliminar este aviso?')">
                                                <i class="fas fa-trash-alt"></i> Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No hay avisos publicados todavía.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
