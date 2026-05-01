<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>FRUVER · Registrar Cliente</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }

        body {
            background: rgba(30, 50, 30, 0.45);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
        }

        .modal-content {
            background: white;
            width: 600px;
            max-width: 100%;
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            background: linear-gradient(to right, #1a4324, #2d5a33);
            color: white;
            padding: 22px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s;
        }
        .close-modal:hover { transform: scale(1.1); }

        .modal-body {
            padding: 30px 35px;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        /* ── Alerta de errores ── */
        .alerta-errores {
            background: #fff0f0;
            border: 1.5px solid #f5c6cb;
            color: #c0392b;
            padding: 14px 18px;
            border-radius: 14px;
            font-size: 14px;
            line-height: 1.7;
        }

        /* ── Separador de sección ── */
        .seccion {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.3px;
            color: #2d5a33;
        }
        .seccion::after {
            content: '';
            flex: 1;
            height: 1.5px;
            background: #e4f0e6;
        }

        /* ── Grids ── */
        .modal-grid   { display: grid; grid-template-columns: 1fr 1fr;     gap: 18px; }
        .modal-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; }
        .col-2 { grid-column: span 2; }

        /* ── Campos ── */
        .campo label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 0.9rem;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 8px;
        }
        .campo label i { color: #2d5a33; font-size: 0.9rem; }

        .campo input,
        .campo select {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid #e0e0e0;
            border-radius: 12px;
            font-size: 0.95rem;
            outline: none;
            color: #333;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .campo input::placeholder { color: #b8b8b8; }
        .campo input:focus,
        .campo select:focus {
            border-color: #2d5a33;
            box-shadow: 0 0 0 3px rgba(45, 90, 51, 0.1);
        }

        /* ── Botón guardar ── */
        .btn-save-modal {
            background: #2d5a33;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 8px;
            box-shadow: 0 4px 14px rgba(45, 90, 51, 0.35);
            transition: background 0.2s;
            width: 100%;
        }
        .btn-save-modal:hover { background: #1a4324; }

        @media (max-width: 560px) {
            .modal-grid, .modal-grid-3 { grid-template-columns: 1fr; }
            .col-2 { grid-column: span 1; }
        }
    </style>
</head>
<body>

<div class="modal-content">

    <!-- Header -->
    <div class="modal-header">
        <div class="header-title">
            <i class="fas fa-user-plus"></i>
            <span>Registrar Nuevo Cliente</span>
        </div>
        <a href="<?= base_url('pantalla_clientes') ?>" class="close-modal" title="Cancelar">
            <i class="fas fa-times"></i>
        </a>
    </div>

    <!-- Form -->
    <form action="<?= base_url('clientes/registrar') ?>" method="post" class="modal-body">
        <?= csrf_field() ?>

        <!-- Errores de validación del servidor -->
        <?php if (!empty($errores)): ?>
            <div class="alerta-errores">
                <?php foreach ($errores as $e): ?>
                    <div>• <?= esc($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ══ DATOS PERSONALES ══ -->
        <div class="seccion"><i class="fas fa-user"></i> Datos Personales</div>

        <div class="campo">
            <label><i class="fas fa-id-card"></i> Nombre(s) *</label>
            <input type="text" name="nombre"
                   placeholder="Escribe el nombre del cliente..."
                   value="<?= esc($old_input['nombre'] ?? '') ?>"
                   required>
        </div>

        <div class="modal-grid">
            <div class="campo">
                <label>Apellido Paterno *</label>
                <input type="text" name="apellido_paterno"
                       placeholder="Ej: Hernández"
                       value="<?= esc($old_input['apellido_paterno'] ?? '') ?>"
                       required>
            </div>
            <div class="campo">
                <label>Apellido Materno</label>
                <input type="text" name="apellido_materno"
                       placeholder="Ej: García"
                       value="<?= esc($old_input['apellido_materno'] ?? '') ?>">
            </div>
        </div>

        <div class="modal-grid">
            <div class="campo">
                <label><i class="fas fa-qrcode"></i> RFC</label>
                <input type="text" name="rfc" maxlength="13"
                       placeholder="ABCD123456XYZ"
                       value="<?= esc($old_input['rfc'] ?? '') ?>"
                       oninput="this.value = this.value.toUpperCase()">
            </div>
            <div class="campo">
                <label><i class="fas fa-phone"></i> Teléfono</label>
                <input type="text" name="tel" maxlength="12"
                       placeholder="Ej: 2223489010"
                       value="<?= esc($old_input['tel'] ?? '') ?>">
            </div>
        </div>

        <div class="campo" style="max-width:48%;">
            <label><i class="fas fa-users"></i> Tipo de Cliente *</label>
            <select name="tipo_cliente" required>
                <option value="">Seleccionar...</option>
                <option value="mayoreo" <?= (($old_input['tipo_cliente'] ?? '') === 'mayoreo') ? 'selected' : '' ?>>Mayoreo</option>
                <option value="menudeo" <?= (($old_input['tipo_cliente'] ?? '') === 'menudeo') ? 'selected' : '' ?>>Menudeo</option>
            </select>
        </div>

        <!-- ══ DIRECCIÓN ══ -->
        <div class="seccion"><i class="fas fa-map-marker-alt"></i> Dirección</div>

        <div class="modal-grid-3">
            <div class="campo col-2">
                <label><i class="fas fa-road"></i> Calle *</label>
                <input type="text" name="calle"
                       placeholder="Nombre de la calle"
                       value="<?= esc($old_input['calle'] ?? '') ?>"
                       required>
            </div>
            <div class="campo">
                <label>Número *</label>
                <input type="text" name="numero"
                       placeholder="Ej: 13"
                       value="<?= esc($old_input['numero'] ?? '') ?>"
                       required>
            </div>
        </div>

        <div class="modal-grid-3">
            <div class="campo">
                <label>Colonia *</label>
                <input type="text" name="colonia"
                       placeholder="Ej: Bravo"
                       value="<?= esc($old_input['colonia'] ?? '') ?>"
                       required>
            </div>
            <div class="campo">
                <label>Municipio</label>
                <input type="text" name="municipio"
                       placeholder="Ej: Veracruz"
                       value="<?= esc($old_input['municipio'] ?? '') ?>">
            </div>
            <div class="campo">
                <label>Estado *</label>
                <input type="text" name="estado"
                       placeholder="Ej: Veracruz"
                       value="<?= esc($old_input['estado'] ?? '') ?>"
                       required>
            </div>
        </div>

        <!-- Botón submit -->
        <button type="submit" class="btn-save-modal">
            <i class="fas fa-save"></i> Guardar Registro de Cliente
        </button>

    </form>
</div>

</body>
</html>