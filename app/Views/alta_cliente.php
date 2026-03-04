<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/usuario_estilo.css') ?>">
    <title>FRUVER - Registro de Cliente</title>
</head>
<body style="background: #eef3e9;"> <div style="max-width: 800px; margin: 30px auto; font-family: sans-serif;">
    <div style="background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden;">
        
        <div style="background: #1d4a27; color: white; padding: 20px; display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-user-plus" style="font-size: 1.5rem;"></i>
            <h2 style="margin: 0; font-size: 1.4rem;">Registro de Nuevo Cliente</h2>
        </div>

      <form action="<?= base_url('alta_clientes/guardar') ?>" method="post">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                
                <div style="grid-column: span 2;">
                    <label style="display: block; color: #4d6b53; font-weight: 600; margin-bottom: 8px;">Nombre(s)</label>
                    <input type="text" name="nombre" required style="width: 100%; padding: 12px; border: 1px solid #cde0ca; border-radius: 10px; outline: none;">
                </div>

                <div>
                    <label style="display: block; color: #4d6b53; font-weight: 600; margin-bottom: 8px;">Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" required style="width: 100%; padding: 12px; border: 1px solid #cde0ca; border-radius: 10px;">
                </div>
                <div>
                    <label style="display: block; color: #4d6b53; font-weight: 600; margin-bottom: 8px;">Apellido Materno</label>
                    <input type="text" name="apellido_materno" required style="width: 100%; padding: 12px; border: 1px solid #cde0ca; border-radius: 10px;">
                </div>

                <div>
                    <label style="display: block; color: #4d6b53; font-weight: 600; margin-bottom: 8px;">RFC</label>
                    <input type="text" name="rfc" maxlength="13" placeholder="ABCD123456XYZ" style="width: 100%; padding: 12px; border: 1px solid #cde0ca; border-radius: 10px; text-transform: uppercase;">
                </div>

                <div>
                    <label style="display: block; color: #4d6b53; font-weight: 600; margin-bottom: 8px;">Tipo de Cliente</label>
                    <select name="tipo_cliente" style="width: 100%; padding: 12px; border: 1px solid #cde0ca; border-radius: 10px; background: white;">
                        <option value="Mayoreo">Mayoreo (Crédito)</option>
                        <option value="Menudeo">Menudeo (Contado)</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end;">
                <a href="<?= base_url('pantalla_clientes') ?>" style="text-decoration: none; color: #666; padding: 12px 25px; font-weight: 600;">Cancelar</a>
                <button type="submit" style="background: #f16b1a; color: white; border: none; padding: 12px 35px; border-radius: 30px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 10px rgba(241,107,26,0.3);">
                    Registrar Cliente
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>