<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FRUVER · Cambiar contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#f1f5f9}.card{max-width:480px;margin:8vh auto;border:0;border-radius:18px;box-shadow:0 10px 30px #0001}.btn-fruver{background:#1d5631;color:#fff}.btn-fruver:hover{background:#143d22;color:#fff}</style>
</head>
<body>
<div class="card p-4">
    <h2 class="text-center" style="color:#1d5631">Cambia tu contraseña</h2>
    <p class="text-muted text-center">Por seguridad, cambia la contraseña temporal antes de entrar a la app.</p>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <form method="post" action="<?= base_url('cuenta/cambiar-password') ?>">
        <?= csrf_field() ?>
        <label class="form-label">Contraseña actual</label>
        <input class="form-control mb-3" type="password" name="password_actual" required>
        <label class="form-label">Nueva contraseña</label>
        <input class="form-control mb-3" type="password" name="password_nueva" minlength="8" required>
        <label class="form-label">Confirmar nueva contraseña</label>
        <input class="form-control mb-3" type="password" name="password_confirmacion" minlength="8" required>
        <button class="btn btn-fruver w-100" type="submit">Guardar nueva contraseña</button>
    </form>
</div>
</body>
</html>
