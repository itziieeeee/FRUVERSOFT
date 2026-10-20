<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - FRUVERSOFT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card-login {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .btn-fruver {
            background-color: #f39c12;
            color: #ffffff;
            font-weight: bold;
        }
        .btn-fruver:hover {
            background-color: #d68910;
            color: #ffffff;
        }
        .title-fruver {
            color: #2d572c;
            font-weight: bold;
        }
    </style>
</head>
<body class="d-flex align-items-center vh-100">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4 col-sm-8">
            <div class="card card-login border-0 p-3">
                <div class="card-body">
                    <h3 class="text-center title-fruver mb-1">FRUVERSOFT</h3>
                    <p class="text-center text-muted mb-4"><small>Acceso al Sistema</small></p>

                    <!-- Mensajes de alerta -->
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger py-2 mb-3 text-center">
                            <small><?= session()->getFlashdata('error') ?></small>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('mensaje')): ?>
                        <div class="alert alert-success py-2 mb-3 text-center">
                            <small><?= session()->getFlashdata('mensaje') ?></small>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('login/ingresar') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="correo" class="form-label text-secondary"><small>Correo Electrónico</small></label>
                            <input type="email" name="correo" id="correo" class="form-control" required placeholder="ejemplo@correo.com">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label text-secondary"><small>Contraseña</small></label>
                            <input type="password" name="password" id="password" class="form-control" required placeholder="******">
                        </div>
                        <button type="submit" class="btn btn-fruver w-100 mt-2 py-2">Ingresar</button>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">¿No tienes cuenta? <a href="<?= base_url('registro') ?>" class="text-decoration-none fw-bold" style="color: #2d572c;">Regístrate aquí</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
