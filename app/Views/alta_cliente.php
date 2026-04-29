<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }

        /* Fondo translúcido con desenfoque exacto */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(30, 50, 30, 0.45); 
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        /* Contenedor con bordes muy redondeados y sombra suave */
        .modal-content {
            background: white;
            width: 580px;
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.25);
        }

        /* Cabecera con el verde degradado sutil de tu imagen */
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
            font-size: 1.25rem; 
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

        .modal-body { padding: 35px; display: flex; flex-direction: column; gap: 20px; }
        .modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }

        /* Etiquetas en negrita con el icono pegado */
        .campo label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .campo label i { font-size: 1rem; }

        /* Inputs con el borde gris claro y sombra interna sutil */
        .campo input, .select-modal {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            outline: none;
            color: #444;
            background-color: #ffffff;
            transition: border-color 0.2s;
        }

        .campo input::placeholder { color: #b0b0b0; }

        .campo input:focus { border-color: #2d5a33; }

        /* Botón Guardar - Verde Fruver exacto */
        .btn-save-modal {
            background: #2d5a33;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.05rem;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 15px;
            box-shadow: 0 4px 12px rgba(45, 90, 51, 0.3);
        }

        .btn-save-modal:hover { background: #1a4324; }
    </style>
</head>
<body>

    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div class="header-title">
                    <i class="fas fa-user-plus"></i>
                    <span>Registrar Nuevo Cliente</span>
                </div>
                <a href="<?= base_url('pantalla_clientes') ?>" class="close-modal">
                    <i class="fas fa-times"></i>
                </a>
            </div>

            <form action="<?= base_url('guardar_cliente') ?>" method="post" class="modal-body">
                
                <div class="campo">
                    <label><i class="fas fa-user"></i> Nombre(s) *</label>
                    <input type="text" name="nombre" placeholder="Escribe el nombre del cliente..." required>
                </div>

                <div class="modal-grid">
                    <div class="campo">
                        <label>Apellido Paterno *</label>
                        <input type="text" name="apellido_paterno" placeholder="Ej: Hernández" required>
                    </div>
                    <div class="campo">
                        <label>Apellido Materno</label>
                        <input type="text" name="apellido_materno" placeholder="Ej: García">
                    </div>
                </div>

                <div class="modal-grid">
                    <div class="campo">
                        <label><i class="fas fa-qrcode"></i> RFC</label>
                        <input type="text" name="rfc" placeholder="ABCD123456XYZ" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="campo">
                        <label>Tipo de Cliente *</label>
                        <select name="tipo_cliente" class="select-modal">
                            <option value="mayoreo">Mayoreo</option>
                            <option value="menudeo">Menudeo</option>
                        </select>
                    </div>
                </div>

                <div class="modal-grid">
                    <div class="campo">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" placeholder="Ej: 55 1234 5678">
                    </div>
                    <div class="campo">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="cliente@correo.com">
                    </div>
                </div>

                <button type="submit" class="btn-save-modal">
                    <i class="fas fa-save"></i> Guardar Registro de Cliente
                </button>
            </form>
        </div>
    </div>

</body>

</html>